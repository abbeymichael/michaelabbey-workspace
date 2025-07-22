<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubFarmType;
use App\Models\SpecificCropOrAnimal;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpecificCropOrAnimalSeeder extends Seeder
{
    // Configuration for API Retries
    protected int $maxApiRetries = 5;
    protected int $initialRetryDelaySeconds = 5; // Initial delay if no Retry-After header

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Specific Crops/Animals using Gemini API with retry logic...');

        // Ensure an admin user exists for audit fields
    

        $subFarmTypes = SubFarmType::all();
        $totalSeededCount = 0;

        foreach ($subFarmTypes as $subFarmType) {
            $this->command->line("Generating Specific Crops/Animals for SubFarmType: " . $subFarmType->name);

            $prompt = "As an expert in Ghanaian agriculture, list 3-7 specific crop varieties or animal breeds (e.g., 'Maize', 'Layers', 'Tilapia', 'Cabbage', 'Goat', 'Cocoa') that are commonly farmed and highly relevant in Ghana, specifically falling under the '{$subFarmType->name}' sub-farm type. For each, provide a concise name and a brief description. If there's specific context, common regions, or special varieties relevant to Ghana, include it in a 'ghana_relevance_notes' field (this field is optional and only include if truly relevant for that item). Ensure the output is a JSON array of objects, with each object having 'name', 'description', and potentially 'ghana_relevance_notes' properties.";

            $generationConfig = [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'description' => ['type' => 'STRING'],
                            'ghana_relevance_notes' => ['type' => 'STRING'],
                        ],
                        'propertyOrdering' => ['name', 'description', 'ghana_relevance_notes'],
                    ],
                ],
            ];

            $attempt = 0;
            $apiCallSuccessful = false;
            $specificItemsData = [];

            while ($attempt < $this->maxApiRetries && !$apiCallSuccessful) {
                $attempt++;
                try {
                    $apiKey = env('GEMINI_API_KEY', '');

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                        'contents' => [
                            ['role' => 'user', 'parts' => [['text' => $prompt]]],
                        ],
                        'generationConfig' => $generationConfig,
                    ]);

                    if ($response->successful()) {
                        $result = $response->json();
                        $jsonString = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

                        if (is_null($jsonString)) {
                            $errorMessage = 'Gemini API response missing content for ' . $subFarmType->name . ' (Attempt ' . $attempt . ').';
                            Log::warning($errorMessage . json_encode($result));
                            $this->command->warn($errorMessage);
                            // This isn't a rate limit, but potentially a bad response, so retry as a general error
                            $delay = $this->initialRetryDelaySeconds * pow(2, $attempt - 1);
                            $this->command->line("   Retrying in {$delay} seconds...");
                            sleep($delay);
                            continue;
                        }

                        $specificItemsData = json_decode($jsonString, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $errorMessage = 'Failed to decode JSON from Gemini API for ' . $subFarmType->name . ' (Attempt ' . $attempt . '): ' . json_last_error_msg();
                            Log::warning($errorMessage . ' Raw JSON: ' . $jsonString);
                            $this->command->warn($errorMessage);
                            $delay = $this->initialRetryDelaySeconds * pow(2, $attempt - 1);
                            $this->command->line("   Retrying in {$delay} seconds...");
                            sleep($delay);
                            continue;
                        }

                        $apiCallSuccessful = true; // API call succeeded and data parsed
                    } elseif ($response->status() === 429) { // Too Many Requests
                        $retryAfter = $response->header('Retry-After');
                        $delay = $retryAfter ? (int)$retryAfter : ($this->initialRetryDelaySeconds * pow(2, $attempt - 1));

                        $this->command->warn("   Rate limit hit for {$subFarmType->name}. Retrying in {$delay} seconds (Attempt {$attempt}/{$this->maxApiRetries}).");
                        Log::warning("Rate limit hit for {$subFarmType->name}. Retrying in {$delay}s.");
                        sleep($delay);
                    } else { // Other API errors (4xx, 5xx)
                        $errorMessage = 'Gemini API call failed for ' . $subFarmType->name . ' (Attempt ' . $attempt . '): ' . $response->status() . ' - ' . $response->body();
                        Log::error($errorMessage);
                        $this->command->error($errorMessage);
                        $delay = $this->initialRetryDelaySeconds * pow(2, $attempt - 1); // Exponential backoff for other errors
                        $this->command->line("   Retrying in {$delay} seconds...");
                        sleep($delay);
                    }
                } catch (\Exception $e) {
                    $errorMessage = 'An exception occurred during API call for ' . $subFarmType->name . ' (Attempt ' . $attempt . '): ' . $e->getMessage();
                    Log::error($errorMessage . ' Stack: ' . $e->getTraceAsString());
                    $this->command->error($errorMessage);
                    $delay = $this->initialRetryDelaySeconds * pow(2, $attempt - 1);
                    $this->command->line("   Retrying in {$delay} seconds...");
                    sleep($delay);
                }
            }

            if (!$apiCallSuccessful) {
                $this->command->error("   Failed to get valid data for {$subFarmType->name} after {$this->maxApiRetries} attempts. Skipping this SubFarmType.");
                continue; // Move to the next SubFarmType if API call persistently fails
            }

            // --- Seed the database with the obtained data ---
            $seededForSubFarmTypeCount = 0;
            foreach ($specificItemsData as $data) {
                $metaData = [
                    'source' => 'Gemini AI',
                    'prompt_hash' => md5($prompt),
                ];

                if (isset($data['ghana_relevance_notes'])) {
                    $metaData['ghana_relevance_notes'] = $data['ghana_relevance_notes'];
                    unset($data['ghana_relevance_notes']);
                }

                $data['sub_farm_type_id'] = $subFarmType->id;
                $data['meta_data'] = json_encode($metaData);
                $data['is_active'] = true;


                SpecificCropOrAnimal::firstOrCreate(
                    ['sub_farm_type_id' => $subFarmType->id, 'name' => $data['name']],
                    $data
                );
                $seededForSubFarmTypeCount++;
                $totalSeededCount++;
            }
            $this->command->info("   Seeded {$seededForSubFarmTypeCount} Specific Crops/Animals for {$subFarmType->name}.");
        }

        $this->command->info("Total Specific Crops/Animals seeded: {$totalSeededCount}.");
    }
}