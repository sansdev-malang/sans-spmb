<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpmbPaymentChannel;
use App\Models\PaymentGateway;
use App\Services\WinpayService;
use Illuminate\Support\Facades\Storage;

class PaymentChannelController extends Controller
{
    /**
     * Display a listing of the resource with tabbed gateways.
     */
    public function index(Request $request)
    {
        // Get active payment gateways with channel count
        $gateways = PaymentGateway::where('is_active', true)->withCount('paymentChannels')->get();

        // Get active tab (gateway code)
        $activeTab = $request->get('tab');
        if (!$activeTab && $gateways->isNotEmpty()) {
            $activeTab = $gateways->first()->code;
        }

        // Get the active gateway model
        $activeGateway = $gateways->where('code', $activeTab)->first();

        // Build channel query for selected gateway
        $channelsQuery = SpmbPaymentChannel::query();
        if ($activeGateway) {
            $channelsQuery->where('payment_gateway_id', $activeGateway->id);
        } else {
            $channelsQuery->whereNull('payment_gateway_id');
        }

        // Filter: Search
        if ($request->filled('search')) {
            $search = $request->search;
            $channelsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter: Type
        if ($request->filled('type')) {
            $channelsQuery->where('type', $request->type);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $status = $request->status === 'active' ? 1 : 0;
            $channelsQuery->where('is_active', $status);
        }

        $channels = $channelsQuery->orderBy('type')->orderBy('name')->get();

        return view('admin.payment_channels.index', compact('gateways', 'channels', 'activeTab', 'activeGateway'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:spmb_payment_channels,code',
            'type' => 'required|string|max:50',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $gateway = PaymentGateway::findOrFail($request->payment_gateway_id);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('payment-logos', 'public');
        }

        SpmbPaymentChannel::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => strtolower($request->type),
            'logo' => $logoPath,
            'payment_gateway_id' => $request->payment_gateway_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.payment-channels.index', ['tab' => $gateway->code])
            ->with('success', 'Channel pembayaran baru berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $channel = SpmbPaymentChannel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:spmb_payment_channels,code,' . $id,
            'type' => 'required|string|max:50',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $gateway = PaymentGateway::findOrFail($request->payment_gateway_id);

        $data = [
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => strtolower($request->type),
            'payment_gateway_id' => $request->payment_gateway_id,
            'is_active' => $request->has('is_active')
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($channel->logo) {
                Storage::disk('public')->delete($channel->logo);
            }
            $data['logo'] = $request->file('logo')->store('payment-logos', 'public');
        }

        $channel->update($data);

        return redirect()->route('admin.payment-channels.index', ['tab' => $gateway->code])
            ->with('success', 'Channel pembayaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $channel = SpmbPaymentChannel::findOrFail($id);
        $gatewayCode = $channel->gateway->code ?? '';
        $channelName = $channel->name;

        // Delete logo if exists
        if ($channel->logo) {
            Storage::disk('public')->delete($channel->logo);
        }

        $channel->delete();

        return redirect()->route('admin.payment-channels.index', ['tab' => $gatewayCode])
            ->with('success', 'Channel ' . $channelName . ' berhasil dihapus.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggle($id)
    {
        $channel = SpmbPaymentChannel::findOrFail($id);
        $channel->update([
            'is_active' => !$channel->is_active
        ]);

        return redirect()->back()->with('success', 'Status channel ' . $channel->name . ' berhasil diperbarui.');
    }

    /**
     * Automatically sync channels from Winpay API.
     */
    public function sync(WinpayService $winpayService)
    {
        try {
            $winpayGateway = PaymentGateway::where('code', 'winpay')->first();
            if (!$winpayGateway) {
                return redirect()->back()->with('error', 'Gateway Winpay tidak aktif atau tidak ditemukan.');
            }

            $externalChannels = $winpayService->getPaymentMethods();
            $activeCodes = [];

            foreach ($externalChannels as $ext) {
                $activeCodes[] = $ext['code'];

                // Map Winpay type to local types ('Virtual Account' -> 'va', 'QR Code Payment' -> 'qris', etc.)
                $type = 'va';
                $lowerType = strtolower($ext['type']);
                if (str_contains($lowerType, 'qr')) {
                    $type = 'qris';
                } elseif (str_contains($lowerType, 'wallet')) {
                    $type = 'ewallet';
                } elseif (str_contains($lowerType, 'retail')) {
                    $type = 'retail';
                }

                SpmbPaymentChannel::updateOrCreate(
                    [
                        'code' => $ext['code'],
                        'payment_gateway_id' => $winpayGateway->id
                    ],
                    [
                        'name' => $ext['name'],
                        'type' => $type,
                        'is_active' => true
                    ]
                );
            }

            return redirect()->route('admin.payment-channels.index', ['tab' => 'winpay'])
                ->with('success', 'Metode pembayaran Winpay berhasil disinkronkan otomatis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
        }
    }
}
