<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiIntegrationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'api_client_id',
        'client_name',
        'type',
        'endpoint_or_url',
        'method',
        'status_code',
        'ip_address',
        'request_payload',
        'response_payload',
        'error_message',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
