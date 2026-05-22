<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubtaskType;

class SubtaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retainerTypes = ['Static Post', 'Carousel', 'Reels'];
        $campaignTypes = [
            'Radio script', 'Text field', 'Upload file', 'KV', 'Presentation',
            'Video script', 'Ideation/Brainstorm', 'Review', 'Client meeting', 'Internal meeting'
        ];

        foreach ($retainerTypes as $type) {
            SubtaskType::updateOrCreate(
                ['name' => $type, 'workflow_type' => 'retainer']
            );
        }

        foreach ($campaignTypes as $type) {
            SubtaskType::updateOrCreate(
                ['name' => $type, 'workflow_type' => 'campaign']
            );
        }
    }
}
