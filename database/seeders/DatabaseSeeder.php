<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Configuration, ChangeRequest, AuditLog, ConfigVersion};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo Users (5 roles) ──────────────────────────
        $admin = User::create([
            'name'      => 'Alex Admin',
            'email'     => 'admin@configvault.com',
            'password'  => Hash::make('password'),
            'role'      => 'Admin',
            'is_active' => true,
        ]);
        $dev = User::create([
            'name'      => 'Dana Developer',
            'email'     => 'dev@configvault.com',
            'password'  => Hash::make('password'),
            'role'      => 'Developer',
            'is_active' => true,
        ]);
        $reviewer = User::create([
            'name'      => 'Riley Reviewer',
            'email'     => 'reviewer@configvault.com',
            'password'  => Hash::make('password'),
            'role'      => 'Reviewer',
            'is_active' => true,
        ]);
        $approver = User::create([
            'name'      => 'Sam Approver',
            'email'     => 'approver@configvault.com',
            'password'  => Hash::make('password'),
            'role'      => 'Approver',
            'is_active' => true,
        ]);
        User::create([
            'name'      => 'Aiden Auditor',
            'email'     => 'auditor@configvault.com',
            'password'  => Hash::make('password'),
            'role'      => 'Auditor',
            'is_active' => true,
        ]);

        // ── Demo Configurations ───────────────────────────
        $configs = [
            [
                'name'        => 'database.production.env',
                'description' => 'Production database connection parameters',
                'type'        => 'env',
                'environment' => 'production',
                'status'      => 'active',
                'version'     => '2.1.0',
                'content'     => "APP_ENV=production\nAPP_DEBUG=false\nDB_HOST=db-prod.internal\nDB_PORT=5432\nDB_DATABASE=app_prod\nDB_USERNAME=app_user\nDB_PASSWORD=\${vault:database/prod#password}\nREDIS_HOST=redis-prod.internal",
            ],
            [
                'name'        => 'app.staging.yaml',
                'description' => 'Staging environment application config',
                'type'        => 'yaml',
                'environment' => 'staging',
                'status'      => 'active',
                'version'     => '1.3.0',
                'content'     => "app:\n  name: ConfigVault\n  env: staging\n  debug: true\n  url: https://staging.configvault.com\n\ndatabase:\n  host: db-staging.internal\n  port: 5432\n  name: app_staging\n  password: \${vault:database/staging#password}",
            ],
            [
                'name'        => 'nginx.production.conf',
                'description' => 'Nginx reverse proxy configuration for production',
                'type'        => 'ini',
                'environment' => 'production',
                'status'      => 'draft',
                'version'     => '1.0.0',
                'content'     => "server {\n    listen 443 ssl;\n    server_name configvault.com;\n    ssl_certificate /etc/ssl/certs/configvault.crt;\n    ssl_certificate_key /etc/ssl/private/configvault.key;\n    location / {\n        proxy_pass http://app:8000;\n    }\n}",
            ],
            [
                'name'        => 'redis.development.json',
                'description' => 'Redis cache configuration for development',
                'type'        => 'json',
                'environment' => 'development',
                'status'      => 'active',
                'version'     => '1.1.0',
                'content'     => "{\n  \"host\": \"localhost\",\n  \"port\": 6379,\n  \"database\": 0,\n  \"password\": null,\n  \"timeout\": 3.0,\n  \"retry_interval\": 100\n}",
            ],
            [
                'name'        => 'mail.production.env',
                'description' => 'SendGrid email configuration for production',
                'type'        => 'env',
                'environment' => 'production',
                'status'      => 'active',
                'version'     => '1.0.0',
                'content'     => "MAIL_MAILER=sendgrid\nMAIL_FROM_ADDRESS=noreply@configvault.com\nMAIL_FROM_NAME=ConfigVault\nSENDGRID_API_KEY=\${vault:mail/prod#sendgrid_key}",
            ],
        ];

        foreach ($configs as $i => $data) {
            $config = Configuration::create([
                ...$data,
                'created_by'       => $dev->id,
                'last_modified_by' => $dev->id,
            ]);

            ConfigVersion::create([
                'configuration_id' => $config->id,
                'user_id'          => $dev->id,
                'version_number'   => $config->version,
                'content'          => $config->content,
                'change_reason'    => 'Initial configuration commit',
            ]);
        }

        // ── Demo Change Requests ──────────────────────────
        $config1 = Configuration::first();
        $config2 = Configuration::skip(1)->first();
        $config3 = Configuration::skip(2)->first();

        // Fully deployed request
        $req1 = ChangeRequest::create([
            'configuration_id'  => $config1->id,
            'submitted_by'      => $dev->id,
            'reviewer_id'       => $reviewer->id,
            'approver_id'       => $approver->id,
            'change_reason'     => 'Upgrade database connection pool size from 10 to 25. Impact: improved throughput. Rollback: revert pool_size to 10.',
            'review_notes'      => 'Reviewed and tested in staging. Approved for production.',
            'approval_notes'    => 'Final approved after load testing. Deployment authorized.',
            'status'            => 'deployed',
            'reviewer_approved' => true,
            'approver_approved' => true,
            'mfa_verified'      => true,
            'deployed_at'       => now()->subHours(3),
        ]);

        // Pending review
        ChangeRequest::create([
            'configuration_id' => $config2->id,
            'submitted_by'     => $dev->id,
            'change_reason'    => 'Update staging app URL to new domain. Impact: low. Rollback: revert URL field.',
            'status'           => 'pending',
        ]);

        // Reviewer approved, awaiting approver
        ChangeRequest::create([
            'configuration_id'  => $config3->id,
            'submitted_by'      => $dev->id,
            'reviewer_id'       => $reviewer->id,
            'change_reason'     => 'Update Nginx SSL cipher suite to TLS 1.3 only. Impact: security improvement. Rollback: restore TLS 1.2 fallback.',
            'review_notes'      => 'Security team reviewed. Cipher changes are valid. Passed to Approver.',
            'status'            => 'reviewing',
            'reviewer_approved' => true,
        ]);

        // ── Demo Audit Logs ───────────────────────────────
        $logs = [
            [$admin->id,    'auth',       'User authenticated successfully',                         'low'],
            [$dev->id,      'auth',       'User authenticated successfully',                         'low'],
            [$dev->id,      'config',     'Created configuration: database.production.env',          'medium'],
            [$dev->id,      'config',     'Created configuration: app.staging.yaml',                 'medium'],
            [$dev->id,      'approval',   'Submitted change request #1 for database.production.env', 'medium'],
            [$reviewer->id, 'approval',   'Reviewer approved change request #1',                     'medium'],
            [$approver->id, 'approval',   'Approver approved and triggered deployment for #1',       'high'],
            [$approver->id, 'deployment', 'Automated deployment executed for: database.production.env','high'],
            [$dev->id,      'config',     'Created configuration: nginx.production.conf',            'medium'],
            [$dev->id,      'approval',   'Submitted change request #3 for nginx.production.conf',   'medium'],
            [$reviewer->id, 'approval',   'Reviewer approved change request #3',                     'medium'],
            [$admin->id,    'rbac',       'Admin created new user: Aiden Auditor with role Auditor', 'high'],
            [$admin->id,    'auth',       'Failed login attempt for: unknown@example.com',           'high'],
        ];

        $lastHash = '0';
        foreach ($logs as [$userId, $type, $action, $severity]) {
            $entry = AuditLog::create([
                'user_id'    => $userId,
                'event_type' => $type,
                'action'     => $action,
                'severity'   => $severity,
                'ip_address' => '192.168.1.'.rand(1,50),
                'user_agent' => 'Mozilla/5.0 (ConfigVault Demo)',
                'created_at' => now()->subMinutes(rand(5, 480)),
                'log_hash'   => '',
            ]);
            $lastHash = hash('sha256', $lastHash . $entry->id . $action . $entry->created_at->toISOString());
            $entry->update(['log_hash' => $lastHash]);
        }

        $this->command->info('✅ ConfigVault demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo accounts (all use password: "password"):');
        $this->command->info('  admin@configvault.com    → Admin');
        $this->command->info('  dev@configvault.com      → Developer');
        $this->command->info('  reviewer@configvault.com → Reviewer');
        $this->command->info('  approver@configvault.com → Approver');
        $this->command->info('  auditor@configvault.com  → Auditor');
    }
}
