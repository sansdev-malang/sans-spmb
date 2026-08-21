<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SpmbPaymentChannel;
use App\Services\WinpayService;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            // Winpay
            'winpay_mode' => Setting::get('winpay_mode', 'simulator'),
            'winpay_prod_merchant_id' => Setting::get('winpay_prod_merchant_id', ''),
            'winpay_prod_client_key' => Setting::get('winpay_prod_client_key', ''),
            'winpay_prod_client_secret' => Setting::get('winpay_prod_client_secret', ''),
            'winpay_prod_private_key' => Setting::get('winpay_prod_private_key', ''),
            'winpay_prod_public_key' => Setting::get('winpay_prod_public_key', ''),
            'winpay_sandbox_merchant_id' => Setting::get('winpay_sandbox_merchant_id', ''),
            'winpay_sandbox_client_key' => Setting::get('winpay_sandbox_client_key', ''),
            'winpay_sandbox_client_secret' => Setting::get('winpay_sandbox_client_secret', ''),
            'winpay_sandbox_private_key' => Setting::get('winpay_sandbox_private_key', ''),
            'winpay_sandbox_public_key' => Setting::get('winpay_sandbox_public_key', ''),
            'winpay_merchant_id' => Setting::get('winpay_merchant_id', 'MOCK_MERCHANT_ID'),
            'winpay_client_key' => Setting::get('winpay_client_key', 'MOCK_CLIENT_KEY'),
            'winpay_client_secret' => Setting::get('winpay_client_secret', 'MOCK_CLIENT_SECRET'),
            'winpay_private_key' => Setting::get('winpay_private_key', ''),
            'winpay_public_key' => Setting::get('winpay_public_key', ''),
            
            // BNI SNAP QRIS MPM
            'bni_mode' => Setting::get('bni_mode', 'simulator'),
            'bni_prod_merchant_id' => Setting::get('bni_prod_merchant_id', ''),
            'bni_prod_terminal_id' => Setting::get('bni_prod_terminal_id', ''),
            'bni_prod_client_id' => Setting::get('bni_prod_client_id', ''),
            'bni_prod_client_secret' => Setting::get('bni_prod_client_secret', ''),
            'bni_prod_private_key' => Setting::get('bni_prod_private_key', ''),
            'bni_sandbox_merchant_id' => Setting::get('bni_sandbox_merchant_id', ''),
            'bni_sandbox_terminal_id' => Setting::get('bni_sandbox_terminal_id', ''),
            'bni_sandbox_client_id' => Setting::get('bni_sandbox_client_id', ''),
            'bni_sandbox_client_secret' => Setting::get('bni_sandbox_client_secret', ''),
            'bni_sandbox_private_key' => Setting::get('bni_sandbox_private_key', ''),
            'bni_simulator_merchant_id' => Setting::get('bni_simulator_merchant_id', 'MOCK_BNI_MID'),
            'bni_simulator_terminal_id' => Setting::get('bni_simulator_terminal_id', 'MOCK_BNI_TID'),
            'bni_simulator_client_id' => Setting::get('bni_simulator_client_id', 'MOCK_BNI_CLIENT'),
            'bni_simulator_client_secret' => Setting::get('bni_simulator_client_secret', 'MOCK_BNI_SECRET'),
            'bni_simulator_private_key' => Setting::get('bni_simulator_private_key', ''),
        ];

        $channels = SpmbPaymentChannel::orderBy('type')->orderBy('name')->get();

        return view('admin.settings', compact('settings', 'channels'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'winpay_mode' => 'required|in:simulator,sandbox,production',
            'bni_mode' => 'required|in:simulator,sandbox,production',
            
            // Production
            'winpay_prod_merchant_id' => 'nullable|string',
            'winpay_prod_client_key' => 'nullable|string',
            'winpay_prod_client_secret' => 'nullable|string',
            'winpay_prod_private_key' => 'nullable|string',
            'winpay_prod_public_key' => 'nullable|string',
            
            'bni_prod_merchant_id' => 'nullable|string',
            'bni_prod_terminal_id' => 'nullable|string',
            'bni_prod_client_id' => 'nullable|string',
            'bni_prod_client_secret' => 'nullable|string',
            'bni_prod_private_key' => 'nullable|string',
            
            // Sandbox
            'winpay_sandbox_merchant_id' => 'nullable|string',
            'winpay_sandbox_client_key' => 'nullable|string',
            'winpay_sandbox_client_secret' => 'nullable|string',
            'winpay_sandbox_private_key' => 'nullable|string',
            'winpay_sandbox_public_key' => 'nullable|string',
            
            'bni_sandbox_merchant_id' => 'nullable|string',
            'bni_sandbox_terminal_id' => 'nullable|string',
            'bni_sandbox_client_id' => 'nullable|string',
            'bni_sandbox_client_secret' => 'nullable|string',
            'bni_sandbox_private_key' => 'nullable|string',
            
            // Simulator
            'winpay_merchant_id' => 'nullable|string',
            'winpay_client_key' => 'nullable|string',
            'winpay_client_secret' => 'nullable|string',
            'winpay_private_key' => 'nullable|string',
            'winpay_public_key' => 'nullable|string',
            
            'bni_simulator_merchant_id' => 'nullable|string',
            'bni_simulator_terminal_id' => 'nullable|string',
            'bni_simulator_client_id' => 'nullable|string',
            'bni_simulator_client_secret' => 'nullable|string',
            'bni_simulator_private_key' => 'nullable|string',
        ]);

        Setting::set('winpay_mode', $request->winpay_mode);
        Setting::set('bni_mode', $request->bni_mode);
        
        // Production Settings
        Setting::set('winpay_prod_merchant_id', $request->winpay_prod_merchant_id ?? '');
        Setting::set('winpay_prod_client_key', $request->winpay_prod_client_key ?? '');
        Setting::set('winpay_prod_client_secret', $request->winpay_prod_client_secret ?? '');
        Setting::set('winpay_prod_private_key', $request->winpay_prod_private_key ?? '');
        Setting::set('winpay_prod_public_key', $request->winpay_prod_public_key ?? '');

        Setting::set('bni_prod_merchant_id', $request->bni_prod_merchant_id ?? '');
        Setting::set('bni_prod_terminal_id', $request->bni_prod_terminal_id ?? '');
        Setting::set('bni_prod_client_id', $request->bni_prod_client_id ?? '');
        Setting::set('bni_prod_client_secret', $request->bni_prod_client_secret ?? '');
        Setting::set('bni_prod_private_key', $request->bni_prod_private_key ?? '');

        // Sandbox Settings
        Setting::set('winpay_sandbox_merchant_id', $request->winpay_sandbox_merchant_id ?? '');
        Setting::set('winpay_sandbox_client_key', $request->winpay_sandbox_client_key ?? '');
        Setting::set('winpay_sandbox_client_secret', $request->winpay_sandbox_client_secret ?? '');
        Setting::set('winpay_sandbox_private_key', $request->winpay_sandbox_private_key ?? '');
        Setting::set('winpay_sandbox_public_key', $request->winpay_sandbox_public_key ?? '');

        Setting::set('bni_sandbox_merchant_id', $request->bni_sandbox_merchant_id ?? '');
        Setting::set('bni_sandbox_terminal_id', $request->bni_sandbox_terminal_id ?? '');
        Setting::set('bni_sandbox_client_id', $request->bni_sandbox_client_id ?? '');
        Setting::set('bni_sandbox_client_secret', $request->bni_sandbox_client_secret ?? '');
        Setting::set('bni_sandbox_private_key', $request->bni_sandbox_private_key ?? '');

        // Simulator Settings
        Setting::set('winpay_merchant_id', $request->winpay_merchant_id ?? '');
        Setting::set('winpay_client_key', $request->winpay_client_key ?? '');
        Setting::set('winpay_client_secret', $request->winpay_client_secret ?? '');
        Setting::set('winpay_private_key', $request->winpay_private_key ?? '');
        Setting::set('winpay_public_key', $request->winpay_public_key ?? '');

        Setting::set('bni_simulator_merchant_id', $request->bni_simulator_merchant_id ?? '');
        Setting::set('bni_simulator_terminal_id', $request->bni_simulator_terminal_id ?? '');
        Setting::set('bni_simulator_client_id', $request->bni_simulator_client_id ?? '');
        Setting::set('bni_simulator_client_secret', $request->bni_simulator_client_secret ?? '');
        Setting::set('bni_simulator_private_key', $request->bni_simulator_private_key ?? '');

        return redirect()->back()->with('success', 'Konfigurasi Payment Gateway berhasil diperbarui.');
    }

    public function toggleChannel($id)
    {
        $channel = SpmbPaymentChannel::findOrFail($id);
        $channel->update([
            'is_active' => !$channel->is_active
        ]);

        return redirect()->back()->with('success', 'Status channel ' . $channel->name . ' berhasil diperbarui.');
    }

    public function syncChannels(WinpayService $winpayService)
    {
        try {
            $externalChannels = $winpayService->getPaymentMethods();
            $activeCodes = [];

            foreach ($externalChannels as $ext) {
                $activeCodes[] = $ext['code'];

                SpmbPaymentChannel::updateOrCreate(
                    ['code' => $ext['code']],
                    [
                        'name' => $ext['name'],
                        'type' => $ext['type']
                    ]
                );
            }

            // Sync: Mark any channels that are no longer in external list as inactive
            SpmbPaymentChannel::whereNotIn('code', $activeCodes)->update(['is_active' => false]);

            return redirect()->back()->with('success', 'Metode pembayaran Winpay berhasil disinkronkan otomatis dengan database.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi channel Winpay: ' . $e->getMessage());
        }
    }
}
