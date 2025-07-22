<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FarmType;
use App\Models\SubFarmType;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubFarmTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Sub Farm Types using Gemini API...');

        // Ensure an admin user exists for audit fields
    

        $farmTypes = FarmType::all();
        $totalSeededCount = 0;

        foreach ($farmTypes as $farmType) {
            $this->command->line("Generating Sub Farm Types for FarmType: " . $farmType->name);

            $prompt = "As an expert in agricultural classification, list all specific sub-categories (sub-farm types) for the main farming type: '{$farmType->name}'. For each, provide a concise name and a brief description. Ensure the output is a JSON array of objects, with each object having 'name' and 'description' properties. Examples for 'Crop Farming' might include 'Grains', 'Vegetables', 'Fruits', 'Root Crops'. Examples for 'Livestock' might include 'Poultry', 'Cattle', 'Small Ruminants'.";

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
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => $generationConfig,
                ]);

                if ($response->failed()) {
                    $errorMessage = 'Gemini API call failed for ' . $farmType->name . ': ' . $response->status() . ' - ' . $response->body();
                    Log::error($errorMessage);
                    $this->command->error($errorMessage);
                    continue; // Continue to the next FarmType even if one fails
                }

                $result = $response->json();
                $jsonString = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (is_null($jsonString)) {
                    $errorMessage = 'Gemini API response missing content for ' . $farmType->name . '.';
                    Log::error($errorMessage . json_encode($result));
                    $this->command->error($errorMessage);
                    continue;
                }

                $subFarmTypesData = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorMessage = 'Failed to decode JSON from Gemini API for ' . $farmType->name . ': ' . json_last_error_msg();
                    Log::error($errorMessage . ' Raw JSON: ' . $jsonString);
                    $this->command->error($errorMessage);
                    continue;
                }

                $seededForFarmTypeCount = 0;
                foreach ($subFarmTypesData as $data) {
                    // Add default metadata and audit fields
                    $data['farm_type_id'] = $farmType->id; // Assign the ID of the current farm type
                    $data['meta_data'] = json_encode(['source' => 'Gemini AI', 'prompt_hash' => md5($prompt)]);
                    $data['is_active'] = true;
                 

                    SubFarmType::firstOrCreate(
                        ['farm_type_id' => $farmType->id, 'name' => $data['name']], // Unique constraint on farm_type_id and name
                        $data
                    );
                    $seededForFarmTypeCount++;
                    $totalSeededCount++;
                }
                $this->command->info("   Seeded {$seededForFarmTypeCount} Sub Farm Types for {$farmType->name}.");

            } catch (\Exception $e) {
                $errorMessage = 'An error occurred during SubFarmType seeding for ' . $farmType->name . ': ' . $e->getMessage();
                Log::error($errorMessage . ' Stack: ' . $e->getTraceAsString());
                $this->command->error($errorMessage);
            }
        }

        $this->command->info("Total Sub Farm Types seeded: {$totalSeededCount}.");
    }
}