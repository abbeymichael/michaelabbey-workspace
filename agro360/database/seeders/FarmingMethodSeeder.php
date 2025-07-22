<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubFarmType;
use App\Models\FarmingMethod;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FarmingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Farming Methods and their associations with Sub Farm Types using Gemini API...');

        // Ensure an admin user exists for audit fields
      

        $subFarmTypes = SubFarmType::all();
        $totalFarmingMethodsSeeded = 0;
        $totalAssociationsMade = 0;

        foreach ($subFarmTypes as $subFarmType) {
            $this->command->line("Generating Farming Methods for SubFarmType: " . $subFarmType->name);

            // Prompt to generate relevant farming methods for the specific sub-farm type
            $prompt = "As an expert in Ghanaian agriculture, list 3-6 common and highly relevant farming type for the '{$subFarmType->name}' sub-farm type. For each, provide a concise name and a brief description. Focus on distinct methods (e.g., 'Crop Rotation', 'Drip Irrigation', 'Zero Tillage', 'Intensive Grazing', 'Cage Culture'). Ensure the output is a JSON array of objects, with each object having 'name' and 'description' properties.";

            $generationConfig = [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'description' => ['type' => 'STRING'],
                        ],
                        'propertyOrdering' => ['name', 'description'],
                    ],
                ],
            ];

            try {
                $apiKey = env('GEMINI_API_KEY', '');

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => $generationConfig,
                ]);

                if ($response->failed()) {
                    $errorMessage = 'Gemini API call failed for ' . $subFarmType->name . ': ' . $response->status() . ' - ' . $response->body();
                    Log::error($errorMessage);
                    $this->command->error($errorMessage);
                    continue; // Continue to the next SubFarmType
                }

                $result = $response->json();
                $jsonString = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (is_null($jsonString)) {
                    $errorMessage = 'Gemini API response missing content for ' . $subFarmType->name . '.';
                    Log::error($errorMessage . json_encode($result));
                    $this->command->error($errorMessage);
                    continue;
                }

                $farmingMethodsData = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorMessage = 'Failed to decode JSON from Gemini API for ' . $subFarmType->name . ': ' . json_last_error_msg();
                    Log::error($errorMessage . ' Raw JSON: ' . $jsonString);
                    $this->command->error($errorMessage);
                    continue;
                }

                $associatedMethodIds = [];
                foreach ($farmingMethodsData as $data) {
                    $metaData = [
                        'source' => 'Gemini AI',
                        'prompt_hash' => md5($prompt),
                    ];

                    // Prepare data for insertion into farming_methods table
                    $methodData = [
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'meta_data' => json_encode($metaData),
                    ];

                    // FirstOrCreate the FarmingMethod
                    $farmingMethod = FarmingMethod::firstOrCreate(
                        ['name' => $methodData['name']], // Unique constraint on 'name'
                        $methodData
                    );

                    $totalFarmingMethodsSeeded++;
                    $associatedMethodIds[] = $farmingMethod->id; // Collect IDs for association
                }

                // Attach the FarmingMethods to the current SubFarmType
                // syncWithoutDetaching ensures existing relationships are kept, new ones added
                $subFarmType->farmingMethods()->syncWithoutDetaching($associatedMethodIds);
                $totalAssociationsMade += count($associatedMethodIds);

                $this->command->info("   Seeded and associated " . count($associatedMethodIds) . " Farming Methods for {$subFarmType->name}.");

            } catch (\Exception $e) {
                $errorMessage = 'An error occurred during FarmingMethod seeding for ' . $subFarmType->name . ': ' . $e->getMessage();
                Log::error($errorMessage . ' Stack: ' . $e->getTraceAsString());
                $this->command->error($errorMessage);
            }
        }

        $this->command->info("Total unique Farming Methods seeded: {$totalFarmingMethodsSeeded}.");
        $this->command->info("Total SubFarmType-FarmingMethod associations made: {$totalAssociationsMade}.");
    }
}