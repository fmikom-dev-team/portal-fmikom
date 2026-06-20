<?php

namespace Database\Seeders;

use App\Models\Pipes\PipeProvider;
use Illuminate\Database\Seeder;

class PipeProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Google Workspace',
                'slug' => 'google',
                'description' => 'Connect to Google Workspace to sync users, groups, and calendar events.',
                'oauth_type' => 'oauth2',
                'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url' => 'https://oauth2.googleapis.com/token',
                'api_base_url' => 'https://www.googleapis.com',
                'status' => 'enabled',
                'logo_url' => '/images/providers/google.svg',
                'supported_features' => ['sync', 'webhooks'],
            ],
            [
                'name' => 'Slack',
                'slug' => 'slack',
                'description' => 'Sync Slack channels, messages, and user presence.',
                'oauth_type' => 'oauth2',
                'auth_url' => 'https://slack.com/oauth/v2/authorize',
                'token_url' => 'https://slack.com/api/oauth.v2.access',
                'api_base_url' => 'https://slack.com/api',
                'status' => 'enabled',
                'logo_url' => '/images/providers/slack.svg',
                'supported_features' => ['sync', 'webhooks', 'actions'],
            ],
            [
                'name' => 'GitHub',
                'slug' => 'github',
                'description' => 'Automate GitHub repositories, issues, and pull requests.',
                'oauth_type' => 'oauth2',
                'auth_url' => 'https://github.com/login/oauth/authorize',
                'token_url' => 'https://github.com/login/oauth/access_token',
                'api_base_url' => 'https://api.github.com',
                'status' => 'enabled',
                'logo_url' => '/images/providers/github.svg',
                'supported_features' => ['sync', 'webhooks'],
            ],
            [
                'name' => 'Notion',
                'slug' => 'notion',
                'description' => 'Sync Notion databases and pages.',
                'oauth_type' => 'oauth2',
                'auth_url' => 'https://api.notion.com/v1/oauth/authorize',
                'token_url' => 'https://api.notion.com/v1/oauth/token',
                'api_base_url' => 'https://api.notion.com/v1',
                'status' => 'enabled',
                'logo_url' => '/images/providers/notion.svg',
                'supported_features' => ['sync'],
            ],
            [
                'name' => 'Salesforce',
                'slug' => 'salesforce',
                'description' => 'Sync CRM data, contacts, and opportunities.',
                'oauth_type' => 'oauth2',
                'auth_url' => 'https://login.salesforce.com/services/oauth2/authorize',
                'token_url' => 'https://login.salesforce.com/services/oauth2/token',
                'api_base_url' => 'https://api.salesforce.com',
                'status' => 'disabled',
                'logo_url' => '/images/providers/salesforce.svg',
                'supported_features' => ['sync', 'webhooks', 'actions'],
            ],
        ];

        foreach ($providers as $provider) {
            PipeProvider::updateOrCreate(['slug' => $provider['slug']], $provider);
        }
    }
}
