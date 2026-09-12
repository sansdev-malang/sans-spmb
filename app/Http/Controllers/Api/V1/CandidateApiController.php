<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\ApiIntegrationLog;
use App\Models\Registration;
use App\Services\ApiIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateApiController extends Controller
{
    protected ApiIntegrationService $service;

    public function __construct(ApiIntegrationService $service)
    {
        $this->service = $service;
    }

    /**
     * Mengambil daftar data pendaftar calon murid (disaring sesuai izin client)
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        /** @var ApiClient $client */
        $client = $request->attributes->get('api_client');

        $query = Registration::with(['user', 'unit', 'period', 'wave', 'classProgram', 'payments']);

        // 1. Filter Unit sesuai Izin Client (Security Sandbox)
        $allowedUnits = $client->allowed_units ?: ['all'];
        if (!in_array('all', $allowedUnits)) {
            $query->whereHas('unit', function ($q) use ($allowedUnits) {
                $q->whereIn('code', array_map('strtoupper', $allowedUnits))
                  ->orWhereIn('code', array_map('strtolower', $allowedUnits));
            });
        }

        // 2. Filter Status sesuai Izin Client (Default: hanya yang diterima/verified jika dibatasi)
        $allowedStatuses = $client->allowed_statuses ?: ['verified', 'accepted'];
        if (!in_array('all', $allowedStatuses)) {
            $effectiveStatuses = [];
            foreach ($allowedStatuses as $st) {
                if ($st === 'verified') {
                    $effectiveStatuses = array_merge($effectiveStatuses, ['verified', 'taaruf_completed', 'agreement_signed', 'completed', 'accepted']);
                } elseif ($st === 'submitted') {
                    $effectiveStatuses = array_merge($effectiveStatuses, ['submitted']);
                } else {
                    $effectiveStatuses[] = $st;
                }
            }
            $query->whereIn('registration_status', array_unique($effectiveStatuses));
        }

        // 3. User Query Parameters
        if ($request->filled('unit')) {
            $reqUnit = strtolower(trim($request->query('unit')));
            if ($client->canAccessUnit($reqUnit)) {
                $query->whereHas('unit', function($q) use ($reqUnit) {
                    $q->where('code', strtoupper($reqUnit))->orWhere('code', strtolower($reqUnit));
                });
            }
        }

        if ($request->filled('status')) {
            $reqStatus = strtolower(trim($request->query('status')));
            if (in_array('all', $allowedStatuses) || in_array($reqStatus, $allowedStatuses)) {
                $query->where('registration_status', $reqStatus);
            }
        }

        if ($request->filled('period')) {
            $periodParam = trim($request->query('period'));
            $query->whereHas('period', fn($q) => $q->where('year', $periodParam)->orWhere('id', $periodParam));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('candidate_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $perPage = min((int) $request->query('per_page', 50), 200);
        $paginated = $query->latest('id')->paginate($perPage);

        // Format data dinamis sesuai izin checklist field
        $transformed = $paginated->getCollection()->map(function ($reg) use ($client) {
            return $this->service->serializeCandidate($reg, $client);
        });

        $responsePayload = [
            'status' => 'success',
            'client' => [
                'name' => $client->name,
                'scopes' => [
                    'units' => $client->allowed_units,
                    'fields' => $client->allowed_fields,
                ]
            ],
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
            'data' => $transformed,
        ];

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Catat ke log audit integrasi
        ApiIntegrationLog::create([
            'api_client_id' => $client->id,
            'client_name' => $client->name,
            'type' => 'inbound_pull',
            'endpoint_or_url' => $request->fullUrl(),
            'method' => 'GET',
            'status_code' => 200,
            'ip_address' => $request->ip(),
            'request_payload' => $request->query(),
            'response_payload' => [
                'total_records' => $paginated->total(),
                'page' => $paginated->currentPage(),
            ],
            'duration_ms' => $durationMs,
        ]);

        return response()->json($responsePayload, 200);
    }

    /**
     * Mengambil detail satu data pendaftar calon murid
     */
    public function show($id, Request $request): JsonResponse
    {
        $startTime = microtime(true);
        /** @var ApiClient $client */
        $client = $request->attributes->get('api_client');

        $reg = Registration::with(['user', 'unit', 'period', 'wave', 'classProgram', 'payments'])
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('registration_no', $id);
            })
            ->first();

        if (!$reg) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pendaftar tidak ditemukan.',
            ], 404);
        }

        $unitCode = $reg->unit->code ?? null;
        if (!$client->canAccessUnit($unitCode)) {
            return response()->json([
                'status' => 'error',
                'message' => "Aplikasi Anda tidak memiliki izin untuk mengakses data jenjang unit [{$unitCode}].",
            ], 403);
        }

        $transformed = $this->service->serializeCandidate($reg, $client);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        ApiIntegrationLog::create([
            'api_client_id' => $client->id,
            'client_name' => $client->name,
            'type' => 'inbound_pull',
            'endpoint_or_url' => $request->fullUrl(),
            'method' => 'GET',
            'status_code' => 200,
            'ip_address' => $request->ip(),
            'request_payload' => ['id' => $id],
            'response_payload' => ['registration_no' => $reg->registration_no],
            'duration_ms' => $durationMs,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $transformed,
        ], 200);
    }
}
