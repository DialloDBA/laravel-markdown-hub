<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AiProvider;
use App\Models\SubscriptionPlan;
use App\Models\AdminSetting;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@markdownhub.com'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('Admin@1234!'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // ── Demo AI providers ────────────────────────────────────────────────────
        AiProvider::updateOrCreate(['slug' => 'openai'], [
            'name'       => 'OpenAI',
            'slug'       => 'openai',
            'icon'       => '🤖',
            'base_url'   => 'https://api.openai.com/v1',
            'api_key'    => '',
            'models'     => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'is_enabled' => false,
            'sort_order' => 1,
        ]);

        AiProvider::updateOrCreate(['slug' => 'groq'], [
            'name'       => 'Groq',
            'slug'       => 'groq',
            'icon'       => '⚡',
            'base_url'   => 'https://api.groq.com/openai/v1',
            'api_key'    => '',
            'models'     => ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768'],
            'is_enabled' => false,
            'sort_order' => 2,
        ]);

        AiProvider::updateOrCreate(['slug' => 'mistral'], [
            'name'       => 'Mistral AI',
            'slug'       => 'mistral',
            'icon'       => '🌊',
            'base_url'   => 'https://api.mistral.ai/v1',
            'api_key'    => '',
            'models'     => ['mistral-large-latest', 'mistral-small-latest', 'open-mistral-7b'],
            'is_enabled' => false,
            'sort_order' => 3,
        ]);

        // ── Subscription plans ────────────────────────────────────────────────────
        SubscriptionPlan::updateOrCreate(['slug' => 'free'], [
            'name'          => 'Free',
            'slug'          => 'free',
            'price'         => 0,
            'currency'      => 'EUR',
            'billing_cycle' => 'monthly',
            'features'      => [
                'Jusqu\'à 5 fichiers README',
                'Export PDF',
                'Éditeur Markdown',
            ],
            'limits'        => ['readme_files' => 5, 'ai_requests_per_day' => 0],
            'is_active'     => true,
            'is_featured'   => false,
        ]);

        SubscriptionPlan::updateOrCreate(['slug' => 'pro'], [
            'name'          => 'Pro',
            'slug'          => 'pro',
            'price'         => 9.99,
            'currency'      => 'EUR',
            'billing_cycle' => 'monthly',
            'features'      => [
                'Fichiers README illimités',
                'Export PDF illimité',
                'Assistant IA (100 req/jour)',
                'Dossiers & organisation',
                'Support prioritaire',
            ],
            'limits'        => ['readme_files' => -1, 'ai_requests_per_day' => 100],
            'is_active'     => true,
            'is_featured'   => true,
        ]);

        // ── Default admin settings ────────────────────────────────────────────────
        AdminSetting::set('site_name',    'MarkdownHub', 'general');
        AdminSetting::set('site_tagline', 'Professional README Management', 'general');
        AdminSetting::set('allow_registration', '1', 'general');
    }
}
