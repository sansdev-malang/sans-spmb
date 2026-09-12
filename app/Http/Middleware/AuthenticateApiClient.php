<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Models\ApiIntegrationLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiClient
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');
        $plainToken = null;

        if (str_starts_with($authHeader, 'Bearer ')) {
            $plainToken = trim(substr($authHeader, 7));
        } elseif ($request->has('api_token')) {
            $plainToken = trim($request->query('api_token'));
        }

        if (empty($plainToken)) {
            $this->logUnauthorized($request, 'Missing Authorization Bearer token header.');
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Header Authorization: Bearer <API_TOKEN> wajib disertakan.',
            ], 401);
        }

        $tokenHash = hash('sha256', $plainToken);
        $client = ApiClient::where('api_token_hash', $tokenHash)->first();

        if (!$client) {
            $this->logUnauthorized($request, 'Invalid API Token hash mismatch.');
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Token API tidak valid atau telah dicabut.',
            ], 401);
        }

        if (!$client->is_active) {
            $this->logUnauthorized($request, 'API Client is deactivated.', $client);
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. Akses API untuk aplikasi ini sedang dinonaktifkan oleh Administrator.',
            ], 403);
        }

        // Update timestamp penggunaan terakhir
        $client->updateQuietly(['last_used_at' => now()]);

        // Pasang client ke request attributes agar dapat diakses oleh Controller
        $request->attributes->set('api_client', $client);

        return $next($request);
    }

    protected function logUnauthorized(Request $request, string $reason, ?ApiClient $client = null): void
    {
        ApiIntegrationLog::create([
            'api_client_id' => $client?->id,
            'client_name' => $client?->name ?: 'Unknown Caller',
            'type' => 'inbound_pull',
            'endpoint_or_url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => 401,
            'ip_address' => $request->ip(),
            'request_payload' => $request->query(),
            'response_payload' => ['error' => $reason],
            'error_message' => $reason,
            'duration_ms' => 0,
        ]);
    }
}
