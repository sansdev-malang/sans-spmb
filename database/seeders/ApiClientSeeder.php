<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApiClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Aplikasi SANS PAUD',
                'client_key' => 'sans-paud',
                'plain_token' => 'spmb_live_paud_' . Str::random(32),
                'allowed_units' => ['paud'],
                'allowed_statuses' => ['verified'],
                'allowed_fields' => ['program', 'bio', 'address', 'parents', 'guardian', 'documents'],
                'webhook_url' => 'http://sans-paud.test/api/spmb-webhook',
                'webhook_secret' => 'whsec_paud_' . Str::random(24),
                'webhook_events' => ['candidate.verified'],
                'description' => 'Integrasi penarikan dan sinkronisasi data calon murid baru untuk unit PAUD Terpadu Anak Saleh.',
                'is_active' => true,
            ],
            [
                'name' => 'Aplikasi SANS SD',
                'client_key' => 'sans-sd',
                'plain_token' => 'spmb_live_sd_' . Str::random(32),
                'allowed_units' => ['sd'],
                'allowed_statuses' => ['verified'],
                'allowed_fields' => ['program', 'bio', 'address', 'parents', 'guardian', 'documents'],
                'webhook_url' => 'http://sans-sd.test/api/spmb-webhook',
                'webhook_secret' => 'whsec_sd_' . Str::random(24),
                'webhook_events' => ['candidate.verified'],
                'description' => 'Integrasi penarikan dan sinkronisasi data calon murid baru untuk unit SD Anak Saleh.',
                'is_active' => true,
            ],
            [
                'name' => 'Aplikasi SANS SMP',
                'client_key' => 'sans-smp',
                'plain_token' => 'spmb_live_smp_' . Str::random(32),
                'allowed_units' => ['smp'],
                'allowed_statuses' => ['verified'],
                'allowed_fields' => ['program', 'bio', 'address', 'parents', 'guardian', 'documents'],
                'webhook_url' => 'http://sans-smp.test/api/spmb-webhook',
                'webhook_secret' => 'whsec_smp_' . Str::random(24),
                'webhook_events' => ['candidate.verified'],
                'description' => 'Integrasi penarikan dan sinkronisasi data calon murid baru untuk unit SMP Anak Saleh.',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $cData) {
            $plainToken = $cData['plain_token'];
            unset($cData['plain_token']);

            $existing = ApiClient::where('client_key', $cData['client_key'])->first();
            if (!$existing) {
                $cData['api_token_hash'] = hash('sha256', $plainToken);
                $cData['token_preview'] = substr($plainToken, 0, 12) . '...' . substr($plainToken, -4);
                ApiClient::create($cData);
            } else {
                $existing->update([
                    'name' => $cData['name'],
                    'allowed_units' => $cData['allowed_units'],
                    'allowed_statuses' => $cData['allowed_statuses'],
                    'allowed_fields' => $cData['allowed_fields'],
                    'webhook_url' => $cData['webhook_url'],
                    'description' => $cData['description'],
                    'webhook_events' => $cData['webhook_events'],
                ]);
            }
        }
    }
}
