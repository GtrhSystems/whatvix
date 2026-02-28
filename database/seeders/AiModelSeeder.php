<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            [
                'code' => 'gpt-4.1',
                'name' => 'GPT 4.1',
                'provider' => 'OpenAI'
            ],
            [
                'code' => 'gpt-4',
                'name' => 'GPT 4',
                'provider' => 'OpenAI'
            ],
            [
                'code' => 'claude-3.5-sonnet',
                'name' => 'Claude 3.5 Sonnet',
                'provider' => 'Anthropic'
            ],
            [
                'code' => 'claude-3-haiku',
                'name' => 'Claude 3 Haiku',
                'provider' => 'Anthropic'
            ],
            [
                'code' => 'gemini-2.0-flash',
                'name' => 'Gemini 2.0 Flash',
                'provider' => 'gemini'
            ],
        ];

        foreach ($models as $model) {
            \App\Models\AiModel::create([
                'code' => $model['code'],
                'name' => $model['name'],
                'provider' => $model['provider'],
                'max_token' => 2000
            ]);
        }
    }
}
