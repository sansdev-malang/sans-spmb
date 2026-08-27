<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\Setting;

class PaymentGatewayController extends Controller
{
    /**
     * Display CRUD list of payment gateways.
     */
    public function index()
    {
        $gateways = PaymentGateway::latest()->paginate(10);
        return view('admin.payment_gateways.index', compact('gateways'));
    }

    /**
     * Store a newly created payment gateway.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways,code',
            'settings_schema_raw' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'gateway_create');
        }

        try {
            $schema = json_decode($request->input('settings_schema_raw'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Format JSON Skema tidak valid.');
            }

            PaymentGateway::create([
                'name' => $request->input('name'),
                'code' => strtolower($request->input('code')),
                'is_active' => $request->has('is_active'),
                'settings_schema' => $schema
            ]);

            return redirect()->route('admin.payment-gateways.index')
                ->with('success', 'Payment Gateway berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['settings_schema_raw' => $e->getMessage()])
                ->withInput()
                ->with('failed_modal', 'gateway_create');
        }
    }

    /**
     * Update the specified payment gateway.
     */
    public function update(Request $request, $id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways,code,' . $id,
            'settings_schema_raw' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'gateway_edit_' . $id);
        }

        try {
            $schema = json_decode($request->input('settings_schema_raw'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Format JSON Skema tidak valid.');
            }

            $gateway->update([
                'name' => $request->input('name'),
                'code' => strtolower($request->input('code')),
                'is_active' => $request->has('is_active'),
                'settings_schema' => $schema
            ]);

            return redirect()->route('admin.payment-gateways.index')
                ->with('success', 'Payment Gateway berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['settings_schema_raw' => $e->getMessage()])
                ->withInput()
                ->with('failed_modal', 'gateway_edit_' . $id);
        }
    }

    /**
     * Delete a payment gateway.
     */
    public function destroy($id)
    {
        $gateway = PaymentGateway::findOrFail($id);
        
        // Prevent deleting seeded defaults for stability
        if (in_array($gateway->code, ['winpay', 'bni'])) {
            return back()->with('error', 'Payment gateway bawaan sistem tidak boleh dihapus.');
        }

        $gateway->delete();
        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment Gateway berhasil dihapus.');
    }

    /**
     * Display configuration form for a specific gateway.
     */
    public function settings(Request $request, $code)
    {
        $gateway = PaymentGateway::where('code', $code)->firstOrFail();

        $settings = [];
        $environments = ['simulator', 'sandbox', 'production'];
        foreach ($environments as $env) {
            foreach ($gateway->settings_schema as $field) {
                $keyName = $field['key'];
                $settingKey = $this->getSettingKey($gateway->code, $env, $keyName);
                $settings[$env][$keyName] = Setting::get($settingKey, '');
            }
        }

        $modeKey = $gateway->code . '_mode';
        $gatewayMode = Setting::get($modeKey, 'simulator');
        $activeTab = $request->get('tab', $gatewayMode);

        return view('admin.payment_gateways.settings', compact('gateway', 'settings', 'gatewayMode', 'activeTab'));
    }

    /**
     * Save configuration values for a specific gateway.
     */
    public function saveSettings(Request $request, $code)
    {
        $gateway = PaymentGateway::where('code', $code)->firstOrFail();

        $request->validate([
            'mode' => 'required|in:simulator,sandbox,production'
        ]);

        // Save mode
        Setting::set($gateway->code . '_mode', $request->input('mode'));

        // Save keys
        $environments = ['simulator', 'sandbox', 'production'];
        foreach ($environments as $env) {
            foreach ($gateway->settings_schema as $field) {
                $keyName = $field['key'];
                $settingKey = $this->getSettingKey($gateway->code, $env, $keyName);
                $value = $request->input("settings.{$env}.{$keyName}");
                Setting::set($settingKey, $value ?? '');
            }
        }

        $activeTab = $request->input('active_tab', 'simulator');
        return redirect()->route('admin.payment-gateways.settings', ['code' => $gateway->code, 'tab' => $activeTab])
            ->with('success', "Konfigurasi {$gateway->name} berhasil disimpan.");
    }

    /**
     * Helper to resolve Setting model key based on environment and code.
     */
    private function getSettingKey($code, $env, $key)
    {
        if ($env === 'simulator') {
            // Keep compatibility with seeded Winpay simulator keys (e.g. winpay_merchant_id)
            if ($code === 'winpay') {
                return "winpay_{$key}";
            }
            return "{$code}_simulator_{$key}";
        }
        return "{$code}_{$env}_{$key}";
    }
}
