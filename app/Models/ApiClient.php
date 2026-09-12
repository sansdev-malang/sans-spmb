<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    protected $fillable = [
        'name',
        'client_key',
        'api_token_hash',
        'token_preview',
        'allowed_units',
        'allowed_statuses',
        'allowed_fields',
        'webhook_url',
        'webhook_secret',
        'webhook_events',
        'is_active',
        'last_used_at',
        'description',
    ];

    protected $casts = [
        'allowed_units' => 'array',
        'allowed_statuses' => 'array',
        'allowed_fields' => 'array',
        'webhook_events' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relationship with Integration Logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ApiIntegrationLog::class, 'api_client_id')->latest('id');
    }

    /**
     * Generate a new plaintext API token and save its hash
     *
     * @return string Plaintext token (shown only once)
     */
    public function setPlainTokenAttribute(string $plainToken): void
    {
        $this->attributes['api_token_hash'] = hash('sha256', $plainToken);
        $this->attributes['token_preview'] = substr($plainToken, 0, 12) . '...' . substr($plainToken, -4);
    }

    /**
     * Helper to create a new token pair
     *
     * @return array ['plain_token' => string, 'secret' => string]
     */
    public static function generateCredentials(): array
    {
        $plainToken = 'spmb_live_' . Str::random(40);
        $secret = 'whsec_' . Str::random(32);

        return [
            'plain_token' => $plainToken,
            'token_hash' => hash('sha256', $plainToken),
            'token_preview' => substr($plainToken, 0, 12) . '...' . substr($plainToken, -4),
            'webhook_secret' => $secret,
        ];
    }

    /**
     * Mengambil daftar kelompok data pendaftar SPMB secara dinamis
     * Mengikuti konfigurasi tahapan/step formulir pendaftaran SPMB di database
     *
     * @return array
     */
    public static function availableDataFields(): array
    {
        $fields = [];
        
        try {
            $steps = SpmbFormStep::orderBy('order')->get();
        } catch (\Throwable $e) {
            $steps = collect();
        }

        if ($steps->isNotEmpty()) {
            foreach ($steps as $step) {
                $key = match ((int) $step->id) {
                    1 => 'program',
                    2 => 'bio',
                    3 => 'address',
                    4 => 'parents',
                    5 => 'guardian',
                    6 => 'documents',
                    default => 'step_' . $step->id,
                };

                $icon = match ($key) {
                    'program' => 'layers',
                    'bio' => 'user',
                    'address' => 'map-pin',
                    'parents' => 'users',
                    'guardian' => 'shield',
                    'documents' => 'file-text',
                    default => 'folder',
                };

                $fields[$key] = [
                    'key' => $key,
                    'step_id' => $step->id,
                    'label' => $step->title,
                    'icon' => $icon,
                    'is_payment' => false,
                ];
            }
        } else {
            $fields = [
                'bio' => ['key' => 'bio', 'label' => 'Biodata Calon Murid', 'icon' => 'user', 'is_payment' => false],
                'parents' => ['key' => 'parents', 'label' => 'Data Orang Tua & Wali', 'icon' => 'users', 'is_payment' => false],
                'address' => ['key' => 'address', 'label' => 'Tempat Tinggal / Alamat', 'icon' => 'map-pin', 'is_payment' => false],
                'documents' => ['key' => 'documents', 'label' => 'Data Lampiran & Dokumen', 'icon' => 'file-text', 'is_payment' => false],
            ];
        }

        // Kelompok Data Keuangan / Pembayaran SPMB
        $fields['payments'] = [
            'key' => 'payments',
            'step_id' => null,
            'label' => 'Data Pembayaran & Keuangan',
            'icon' => 'credit-card',
            'is_payment' => true,
        ];

        return $fields;
    }

    /**
     * Check if client can access a given unit
     */
    public function canAccessUnit(?string $unitCode): bool
    {
        if (empty($unitCode)) return true;
        $units = $this->allowed_units ?: ['all'];
        if (in_array('all', $units)) return true;

        $normalized = strtolower(trim($unitCode));
        return in_array($normalized, array_map('strtolower', $units));
    }

    /**
     * Check if client can access a given field group
     */
    public function canAccessField(string $fieldGroup): bool
    {
        $fields = $this->allowed_fields ?: ['bio', 'parents'];
        if (in_array('*', $fields) || in_array('all', $fields)) return true;
        if (in_array($fieldGroup, $fields)) return true;

        // Backward compatibility mappings
        if ($fieldGroup === 'address' && in_array('bio', $fields)) return true;
        if ($fieldGroup === 'school_origin' && (in_array('bio', $fields) || in_array('school_origin', $fields))) return true;
        if ($fieldGroup === 'guardian' && in_array('parents', $fields)) return true;

        return false;
    }

    /**
     * Check if client is subscribed to a webhook event
     */
    public function isSubscribedToEvent(string $event): bool
    {
        if (empty($this->webhook_url) || !$this->is_active) return false;
        $events = $this->webhook_events ?: [];
        return in_array($event, $events) || in_array('*', $events);
    }
}
