<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FarmType;
use App\Models\User; // Don't forget to import the User model
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // For logging errors
use Illuminate\Support\Str; // For UUID if needed for dummy user, though HasUuids handles it

class FarmTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Farm Types using Gemini API...');

     

        // --- Define the prompt and expected schema for Gemini ---
        $prompt = "As an expert in agricultural classification, list 4 broad categories of farming types relevant to Ghana and general global agriculture. For each, provide a concise name and a brief description. Focus on categories like 'Crop Farming', 'Livestock', 'Aquaculture', and 'Agroforestry'. Ensure the output is a JSON array of objects, with each object having 'name' and 'description' properties.";

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

        // --- Make the API call to Gemini ---
        try {
            // Note: __app_id and __firebase_config are provided by the Canvas environment at runtime
            // For a pure Laravel seeder, you'd typically get your API key from .env
            // We'll simulate the empty API key Canvas provides and assume the runtime handles it.
            $apiKey = env('GEMINI_API_KEY', ''); // Get API key from .env or leave empty for Canvas runtime

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => $generationConfig,
            ]);

            // Check for API errors
            if ($response->failed()) {
                $errorMessage = 'Gemini API call failed: ' . $response->status() . ' - ' . $response->body();
                Log::error($errorMessage);
                $this->command->error($errorMessage);
                return; // Stop seeding if API call fails
            }

            $result = $response->json();

            // Extract the text part which contains the JSON string
            $jsonString = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (is_null($jsonString)) {
                $errorMessage = 'Gemini API response missing content.';
                Log::error($errorMessage . json_encode($result));
                $this->command->error($errorMessage);
                return;
            }

            // Parse the JSON string
            $farmTypesData = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMessage = 'Failed to decode JSON from Gemini API: ' . json_last_error_msg();
                Log::error($errorMessage . ' Raw JSON: ' . $jsonString);
                $this->command->error($errorMessage);
                return;
            }

            // --- Seed the database ---
            $seededCount = 0;
            foreach ($farmTypesData as $data) {
                // Add default metadata and audit fields
                $data['meta_data'] = json_encode(['source' => 'Gemini AI', 'prompt_hash' => md5($prompt)]); // Convert to JSON string
                $data['is_active'] = true;

                FarmType::firstOrCreate(
                    ['name' => $data['name']], // Use 'name' for uniqueness check
                    $data
                );
                $seededCount++;
            }

            $this->command->info("Successfully seeded {$seededCount} Farm Types from Gemini API.");

        } catch (\Exception $e) {
            $errorMessage = 'An error occurred during FarmType seeding: ' . $e->getMessage();
            Log::error($errorMessage . ' Stack: ' . $e->getTraceAsString());
            $this->command->error($errorMessage);
        }
    }
}