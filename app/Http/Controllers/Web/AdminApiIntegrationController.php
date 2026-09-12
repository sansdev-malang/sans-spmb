<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\ApiIntegrationLog;
use App\Models\SpmbUnit;
use App\Services\ApiIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminApiIntegrationController extends Controller
{
    protected ApiIntegrationService $service;

    public function __construct(ApiIntegrationService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Utama Integrasi API & Aplikasi
     */
    public function index(Request $request): View
    {
        $clients = ApiClient::withCount('logs')->latest('id')->get();
        $units = SpmbUnit::all();

        $logQuery = ApiIntegrationLog::with('client')->latest('id');

        if ($request->filled('log_type')) {
            $logQuery->where('type', $request->query('log_type'));
        }

        if ($request->filled('status_filter')) {
            if ($request->query('status_filter') === 'success') {
                $logQuery->whereBetween('status_code', [200, 299]);
            } elseif ($request->query('status_filter') === 'error') {
                $logQuery->where(function ($q) {
                    $q->whereNull('status_code')->orWhere('status_code', '>=', 400);
                });
            }
        }

        $logs = $logQuery->paginate(15)->appends(['tab' => 'logs'])->withQueryString();

        // Statistik Dashboard Integrasi
        $stats = [
            'total_clients' => $clients->count(),
            'active_clients' => $clients->where('is_active', true)->count(),
            'total_inbound_today' => ApiIntegrationLog::where('type', 'inbound_pull')->whereDate('created_at', today())->count(),
            'total_webhooks_today' => ApiIntegrationLog::where('type', 'outbound_webhook')->whereDate('created_at', today())->count(),
        ];

        // Kelompok Data yang Tersedia (Dinamis dari SPMB)
        $availableFields = ApiClient::availableDataFields();

        return view('admin.settings-api-integrations', compact('clients', 'units', 'logs', 'stats', 'availableFields'));
    }

    /**
     * Simpan Koneksi Aplikasi Baru
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('client_id')) {
            $existingId = $request->input('client_id');
            $existingClient = ApiClient::find($existingId);
            if ($existingClient) {
                return $this->update($existingId, $request);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'allowed_units' => 'required|array|min:1',
            'allowed_units.*' => 'string',
            'allowed_statuses' => 'required|array|min:1',
            'allowed_statuses.*' => 'string',
            'allowed_fields' => 'required|array|min:1',
            'allowed_fields.*' => 'string',
            'webhook_url' => 'nullable|url|max:255',
            'webhook_events' => 'nullable|array',
            'webhook_events.*' => 'string',
        ]);

        $creds = ApiClient::generateCredentials();
        $clientKey = 'client_' . Str::slug($validated['name']) . '_' . Str::lower(Str::random(6));

        $client = ApiClient::create([
            'name' => $validated['name'],
            'client_key' => $clientKey,
            'api_token_hash' => $creds['token_hash'],
            'token_preview' => $creds['token_preview'],
            'allowed_units' => $validated['allowed_units'],
            'allowed_statuses' => $validated['allowed_statuses'],
            'allowed_fields' => $validated['allowed_fields'],
            'webhook_url' => $validated['webhook_url'] ?: null,
            'webhook_secret' => $creds['webhook_secret'],
            'webhook_events' => $validated['webhook_events'] ?: ['candidate.verified'],
            'is_active' => true,
            'description' => $validated['description'] ?? null,
        ]);

        $tab = $request->input('active_tab', 'clients');

        // Simpan token plaintext ke session flash agar muncul di modal 'Salin Token' sekali saja
        return redirect()->route('admin.api-integrations', ['tab' => $tab])
            ->with('new_client_token', [
                'name' => $client->name,
                'token' => $creds['plain_token'],
                'webhook_secret' => $creds['webhook_secret'],
            ])
            ->with('success', "Koneksi aplikasi '{$client->name}' berhasil dibuat!");
    }

    /**
     * Perbarui Pengaturan Koneksi Aplikasi
     */
    public function update($id, Request $request): RedirectResponse
    {
        $client = ApiClient::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'allowed_units' => 'required|array|min:1',
            'allowed_units.*' => 'string',
            'allowed_statuses' => 'required|array|min:1',
            'allowed_statuses.*' => 'string',
            'allowed_fields' => 'required|array|min:1',
            'allowed_fields.*' => 'string',
            'webhook_url' => 'nullable|url|max:255',
            'webhook_events' => 'nullable|array',
            'webhook_events.*' => 'string',
            'is_active' => 'nullable|boolean',
        ]);

        $client->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'allowed_units' => $validated['allowed_units'],
            'allowed_statuses' => $validated['allowed_statuses'],
            'allowed_fields' => $validated['allowed_fields'],
            'webhook_url' => $validated['webhook_url'] ?: null,
            'webhook_events' => $validated['webhook_events'] ?: [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $tab = $request->input('active_tab', 'clients');

        return redirect()->route('admin.api-integrations', ['tab' => $tab])
            ->with('success', "Pengaturan koneksi '{$client->name}' berhasil diperbarui!");
    }

    /**
     * Toggle Status Aktif / Nonaktif
     */
    public function toggle($id, Request $request): RedirectResponse
    {
        $client = ApiClient::findOrFail($id);
        $client->is_active = !$client->is_active;
        $client->save();

        $statusText = $client->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $tab = $request->input('active_tab', $request->query('tab', 'clients'));

        return redirect()->route('admin.api-integrations', ['tab' => $tab])
            ->with('success', "Koneksi '{$client->name}' berhasil {$statusText}.");
    }

    /**
     * Regenerasi Token Akses & Webhook Secret
     */
    public function regenerateToken($id, Request $request): RedirectResponse
    {
        $client = ApiClient::findOrFail($id);
        $creds = ApiClient::generateCredentials();

        $client->api_token_hash = $creds['token_hash'];
        $client->token_preview = $creds['token_preview'];
        $client->webhook_secret = $creds['webhook_secret'];
        $client->save();

        $tab = $request->input('active_tab', $request->query('tab', 'clients'));

        return redirect()->route('admin.api-integrations', ['tab' => $tab])
            ->with('new_client_token', [
                'name' => $client->name,
                'token' => $creds['plain_token'],
                'webhook_secret' => $creds['webhook_secret'],
            ])
            ->with('success', "Token API baru untuk '{$client->name}' berhasil digenerate!");
    }

    /**
     * Hapus Koneksi Aplikasi
     */
    public function destroy($id, Request $request): RedirectResponse
    {
        $client = ApiClient::findOrFail($id);
        $name = $client->name;
        $client->delete();

        $tab = $request->input('active_tab', $request->query('tab', 'clients'));

        return redirect()->route('admin.api-integrations', ['tab' => $tab])
            ->with('success', "Koneksi aplikasi '{$name}' telah dihapus.");
    }

    /**
     * Test Webhook Ping (AJAX Endpoint)
     */
    public function testWebhook($id): JsonResponse
    {
        $client = ApiClient::findOrFail($id);
        $result = $this->service->testWebhookPing($client);

        return response()->json($result);
    }

    /**
     * Bersihkan Log Integrasi
     */
    public function clearLogs(Request $request): RedirectResponse
    {
        ApiIntegrationLog::truncate();
        $tab = $request->input('active_tab', 'logs');

        return redirect()->route('admin.api-integrations', ['tab' => $tab])
            ->with('success', "Seluruh log riwayat integrasi berhasil dibersihkan.");
    }
}
