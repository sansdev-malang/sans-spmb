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

            // Payment Fees Schema Settings
            'fee_bni_va' => Setting::get('fee_bni_va', '1500'),
            'fee_bni_qris' => Setting::get('fee_bni_qris', '0.7'),
            'fee_winpay_va' => Setting::get('fee_winpay_va', '4500'),
        ];

        $channels = SpmbPaymentChannel::orderBy('type')->orderBy('name')->get();
        $gateways = \App\Models\PaymentGateway::where('is_active', true)->get();

        $gatewayFees = [];
        foreach ($gateways as $gw) {
            $lowerName = strtolower($gw->name);
            $lowerCode = strtolower($gw->code);

            if ($gw->code === 'winpay') {
                $key = 'fee_winpay_va';
                $gatewayFees[$gw->code] = [
                    'gateway_name' => $gw->name,
                    'fields' => [
                        [
                            'key' => $key,
                            'type' => 'va',
                            'label' => 'Biaya Flat Transaksi (Rp)',
                            'desc' => 'Biaya flat transaksi untuk semua metode/channel pembayaran Winpay.',
                            'value' => Setting::get($key, '4500'),
                        ]
                    ]
                ];
            } elseif ($gw->code === 'bni_va') {
                $key = 'fee_bni_va';
                $gatewayFees[$gw->code] = [
                    'gateway_name' => $gw->name,
                    'fields' => [
                        [
                            'key' => $key,
                            'type' => 'va',
                            'label' => 'Biaya Virtual Account BNI (Rp)',
                            'desc' => 'Biaya flat transaksi Virtual Account BNI Host-to-Host.',
                            'value' => Setting::get($key, '1500'),
                        ]
                    ]
                ];
            } elseif ($gw->code === 'qris_bni' || $gw->code === 'bni_qris' || $gw->code === 'bni') {
                $key = 'fee_bni_qris';
                $gatewayFees[$gw->code] = [
                    'gateway_name' => $gw->name,
                    'fields' => [
                        [
                            'key' => $key,
                            'type' => 'qris',
                            'label' => 'MDR QRIS BNI (%)',
                            'desc' => 'Tarif MDR persentase untuk channel QRIS BNI.',
                            'value' => Setting::get($key, '0.7'),
                        ]
                    ]
                ];
            } else {
                // Fallback for new gateways
                $isVa = (str_contains($lowerName, 'virtual') || str_contains($lowerName, 'va') || str_contains($lowerCode, 'va'));
                $isQris = (str_contains($lowerName, 'qris') || str_contains($lowerCode, 'qris'));

                $fields = [];
                if ($isQris) {
                    $key = "fee_{$gw->code}_qris";
                    $fields[] = [
                        'key' => $key,
                        'type' => 'qris',
                        'label' => "MDR QRIS {$gw->name} (%)",
                        'desc' => "Tarif MDR persentase untuk channel QRIS {$gw->name}.",
                        'value' => Setting::get($key, '0.7'),
                    ];
                } else {
                    $key = "fee_{$gw->code}_va";
                    $fields[] = [
                        'key' => $key,
                        'type' => 'va',
                        'label' => "Biaya Flat Transaksi {$gw->name} (Rp)",
                        'desc' => "Biaya flat transaksi untuk {$gw->name}.",
                        'value' => Setting::get($key, '4500'),
                    ];
                }

                $gatewayFees[$gw->code] = [
                    'gateway_name' => $gw->name,
                    'fields' => $fields
                ];
            }
        }

        return view('admin.settings', compact('settings', 'channels', 'gatewayFees'));
    }

    public static function getFeeSettingKey($gatewayCode, $type)
    {
        if ($type === 'va') {
            if ($gatewayCode === 'winpay') {
                return 'fee_winpay_va';
            }
            if ($gatewayCode === 'bni_va' || $gatewayCode === 'bni') {
                return 'fee_bni_va';
            }
            return "fee_{$gatewayCode}_va";
        } else {
            if ($gatewayCode === 'qris_bni' || $gatewayCode === 'bni_qris' || $gatewayCode === 'bni') {
                return 'fee_bni_qris';
            }
            return "fee_{$gatewayCode}_qris";
        }
    }

    public function update(Request $request)
    {
        $gateways = \App\Models\PaymentGateway::where('is_active', true)->get();
        $validationRules = [
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
        ];

        $feeKeys = [];
        foreach ($gateways as $gw) {
            $lowerName = strtolower($gw->name);
            $lowerCode = strtolower($gw->code);

            if ($gw->code === 'winpay') {
                $key = 'fee_winpay_va';
                $validationRules[$key] = 'required|numeric|min:0';
                $feeKeys[] = $key;
            } elseif ($gw->code === 'bni_va') {
                $key = 'fee_bni_va';
                $validationRules[$key] = 'required|numeric|min:0';
                $feeKeys[] = $key;
            } elseif ($gw->code === 'qris_bni' || $gw->code === 'bni_qris' || $gw->code === 'bni') {
                $key = 'fee_bni_qris';
                $validationRules[$key] = 'required|numeric|min:0';
                $feeKeys[] = $key;
            } else {
                $isVa = (str_contains($lowerName, 'virtual') || str_contains($lowerName, 'va') || str_contains($lowerCode, 'va'));
                $isQris = (str_contains($lowerName, 'qris') || str_contains($lowerCode, 'qris'));

                if ($isQris) {
                    $key = "fee_{$gw->code}_qris";
                    $validationRules[$key] = 'required|numeric|min:0';
                    $feeKeys[] = $key;
                } else {
                    $key = "fee_{$gw->code}_va";
                    $validationRules[$key] = 'required|numeric|min:0';
                    $feeKeys[] = $key;
                }
            }
        }

        $request->validate($validationRules);

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

        // Save Fees Settings
        foreach ($feeKeys as $key) {
            Setting::set($key, $request->input($key));
        }

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

    public function uiSettings()
    {
        $units = \App\Models\SpmbUnit::all();

        $settings = [
            'school_name' => Setting::get('school_name', 'Sekolah Anak Saleh'),
            'school_tagline' => Setting::get('school_tagline', 'Yayasan Pendidikan Anak Saleh'),
            'school_logo_url' => Setting::get('school_logo_url', ''),
            'school_favicon_url' => Setting::get('school_favicon_url', ''),
            'portal_hero_title' => Setting::get('portal_hero_title', 'Membangun Generasi Cerdas, Sholeh, dan Berakhlak Mulia.'),
            'portal_hero_description' => Setting::get('portal_hero_description', 'Bergabunglah bersama Sekolah Anak Saleh. Kami menyajikan kurikulum yang mengintegrasikan nilai-nilai Islam dengan pendidikan modern untuk menyiapkan pemimpin masa depan.'),
            'portal_primary_color' => Setting::get('portal_primary_color', '#0D3B2C'),
            'portal_secondary_color' => Setting::get('portal_secondary_color', '#ffc107'),
            'portal_layout_mode' => Setting::get('portal_layout_mode', 'light'),
            'school_hero_images' => Setting::get('school_hero_images', '[]'),
            'footer_contact_url' => Setting::get('footer_contact_url', '#'),
            'footer_privacy_url' => Setting::get('footer_privacy_url', '#'),
            'footer_terms_url' => Setting::get('footer_terms_url', '#'),
            'footer_faq_url' => Setting::get('footer_faq_url', '#'),
            'footer_copyright_text' => Setting::get('footer_copyright_text', '© 2026 {SchoolName}. All rights reserved.'),
        ];

        foreach ($units as $unit) {
            $code = strtolower($unit->code);
            $settings['unit_' . $code . '_desc'] = Setting::get('unit_' . $code . '_desc', $this->getDefaultUnitDesc($code));
            $settings['unit_' . $code . '_content'] = Setting::get('unit_' . $code . '_content', $this->getDefaultUnitContent($code));
            $settings['unit_' . $code . '_features'] = Setting::get('unit_' . $code . '_features', $this->getDefaultUnitFeatures($code));
            $settings['unit_' . $code . '_requirements'] = Setting::get('unit_' . $code . '_requirements', $this->getDefaultUnitRequirements($code));
            $settings['unit_' . $code . '_flow'] = Setting::get('unit_' . $code . '_flow', $this->getDefaultUnitFlow($code));
            $settings['unit_' . $code . '_brochure_url'] = Setting::get('unit_' . $code . '_brochure_url', '');
            $settings['unit_' . $code . '_attachment_url'] = Setting::get('unit_' . $code . '_attachment_url', '');
        }

        return view('admin.settings-ui', compact('settings', 'units'));
    }

    private function getDefaultUnitDesc($code)
    {
        if ($code === 'paud') {
            return 'Pondasi karakter unggul melalui pendekatan bermain sambil belajar, menanamkan nilai-nilai dasar Islam sejak dini.';
        } elseif ($code === 'sd') {
            return 'Membentuk habituasi ibadah, penguasaan literasi & numerasi, serta pengembangan minat bakat siswa.';
        } elseif ($code === 'smp') {
            return 'Mempersiapkan remaja yang mandiri, kritis, dan memiliki pemahaman agama yang kokoh untuk menghadapi tantangan global.';
        }
        return '';
    }

    private function getDefaultUnitContent($code)
    {
        if ($code === 'paud') {
            return 'Sentra Bermain, Kelompok Bermain A (Usia 3-4 Tahun), Kelompok Bermain B (Usia 4-5 Tahun)';
        } elseif ($code === 'sd') {
            return 'Kelas Reguler (Kelas 1-6), Bilingual Program (Kelas 1-3), Program Akselerasi (Khusus)';
        } elseif ($code === 'smp') {
            return 'Kelas Akademik Unggulan, Program Tahfidz Intensif, Ekstrakurikuler Wajib & Pilihan';
        }
        return '';
    }

    private function getDefaultUnitFeatures($code)
    {
        if ($code === 'paud') {
            return 'Usia 3-6 Tahun, Sentra Bermain Interaktif, Pembiasaan Adab Harian';
        } elseif ($code === 'sd') {
            return 'Tahfidz Juz 30, Bilingual Program, Kunjungan Edukasi Berkala';
        } elseif ($code === 'smp') {
            return 'Leadership Camp, Coding & Robotic, Bina Karakter Remaja Muslim';
        }
        return '';
    }

    private function getDefaultUnitRequirements($code)
    {
        if ($code === 'paud') {
            return 'Mengisi Formulir Pendaftaran, Fotokopi Akta Kelahiran & KK, Pasfoto 3x4 (2 lembar)';
        } elseif ($code === 'sd') {
            return 'Mengisi Formulir Pendaftaran, Fotokopi Akta Lahir & KK, Fotokopi KTP Orang Tua, Surat Keterangan dari TK Asal';
        } elseif ($code === 'smp') {
            return 'Mengisi Formulir Pendaftaran, Fotokopi Akta Lahir & KK, Rapor SD Kelas 4-6, Fotokopi Ijazah SD (bisa menyusul)';
        }
        return '';
    }

    private function getDefaultUnitFlow($code)
    {
        if ($code === 'paud') {
            return '1. Mengisi Formulir Pendaftaran Awal, 2. Melakukan Pembayaran Seleksi Masuk, 3. Mengikuti Observasi & Wawancara Wali, 4. Pengumuman & Registrasi Ulang';
        } elseif ($code === 'sd') {
            return '1. Registrasi Instan & Pembuatan Akun, 2. Pengisian Formulir Lengkap & Upload Berkas, 3. Uji Kesiapan Belajar & Wawancara Orang Tua, 4. Hasil Seleksi & Pelunasan Biaya Masuk';
        } elseif ($code === 'smp') {
            return '1. Pendaftaran Online / Mandiri, 2. Pelunasan Biaya Seleksi, 3. Ujian Tes Akademik & Baca Al-Qur\'an, 4. Wawancara Siswa & Orang Tua, 5. Pengumuman Kelulusan';
        }
        return '';
    }

    public function saveUiSettings(Request $request)
    {
        $units = \App\Models\SpmbUnit::all();
        $rules = [
            'school_name' => 'required|string|max:255',
            'school_tagline' => 'nullable|string|max:255',
            'portal_hero_title' => 'required|string|max:255',
            'portal_hero_description' => 'required|string',
            'portal_primary_color' => 'required|string|max:20',
            'portal_secondary_color' => 'required|string|max:20',
            'portal_layout_mode' => 'required|in:light,dark',
            'school_logo' => 'nullable|image|max:2048',
            'school_favicon' => 'nullable|file|max:2048',
            'school_hero_images.*' => 'image|max:3072',
            'footer_contact_url' => 'nullable|string|max:255',
            'footer_privacy_url' => 'nullable|string|max:255',
            'footer_terms_url' => 'nullable|string|max:255',
            'footer_faq_url' => 'nullable|string|max:255',
            'footer_copyright_text' => 'nullable|string|max:255',
        ];

        foreach ($units as $unit) {
            $code = strtolower($unit->code);
            $rules['unit_' . $code . '_desc'] = 'required|string';
            $rules['unit_' . $code . '_content'] = 'required|string';
            $rules['unit_' . $code . '_features'] = 'required|string';
            $rules['unit_' . $code . '_requirements'] = 'required|string';
            $rules['unit_' . $code . '_flow'] = 'required|string';
            $rules['unit_' . $code . '_brochure'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096';
            $rules['unit_' . $code . '_attachment'] = 'nullable|file|mimes:pdf,zip,doc,docx,xls,xlsx|max:5120';
        }

        $request->validate($rules);

        Setting::set('school_name', $request->school_name);
        Setting::set('school_tagline', $request->school_tagline);
        Setting::set('portal_hero_title', $request->portal_hero_title);
        Setting::set('portal_hero_description', $request->portal_hero_description);
        Setting::set('portal_primary_color', $request->portal_primary_color);
        Setting::set('portal_secondary_color', $request->portal_secondary_color);
        Setting::set('portal_layout_mode', $request->portal_layout_mode);
        Setting::set('footer_contact_url', $request->footer_contact_url ?? '#');
        Setting::set('footer_privacy_url', $request->footer_privacy_url ?? '#');
        Setting::set('footer_terms_url', $request->footer_terms_url ?? '#');
        Setting::set('footer_faq_url', $request->footer_faq_url ?? '#');
        Setting::set('footer_copyright_text', $request->footer_copyright_text ?? '© 2026 {SchoolName}. All rights reserved.');

        // Process units dynamically
        foreach ($units as $unit) {
            $code = strtolower($unit->code);
            Setting::set('unit_' . $code . '_desc', $request->input('unit_' . $code . '_desc', ''));
            Setting::set('unit_' . $code . '_content', $request->input('unit_' . $code . '_content', ''));
            Setting::set('unit_' . $code . '_features', $request->input('unit_' . $code . '_features', ''));
            Setting::set('unit_' . $code . '_requirements', $request->input('unit_' . $code . '_requirements', ''));
            Setting::set('unit_' . $code . '_flow', $request->input('unit_' . $code . '_flow', ''));

            // Process brochure upload
            if ($request->hasFile('unit_' . $code . '_brochure')) {
                $path = $request->file('unit_' . $code . '_brochure')->store('documents', 'public');
                Setting::set('unit_' . $code . '_brochure_url', \Illuminate\Support\Facades\Storage::url($path));
            }

            // Process attachment upload
            if ($request->hasFile('unit_' . $code . '_attachment')) {
                $path = $request->file('unit_' . $code . '_attachment')->store('documents', 'public');
                Setting::set('unit_' . $code . '_attachment_url', \Illuminate\Support\Facades\Storage::url($path));
            }

            // Process deletions
            if ($request->input('delete_unit_' . $code . '_brochure') == '1') {
                Setting::set('unit_' . $code . '_brochure_url', '');
            }
            if ($request->input('delete_unit_' . $code . '_attachment') == '1') {
                Setting::set('unit_' . $code . '_attachment_url', '');
            }
        }

        // Process logo upload
        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('branding', 'public');
            Setting::set('school_logo_url', \Illuminate\Support\Facades\Storage::url($path));
        }

        // Process favicon upload
        if ($request->hasFile('school_favicon')) {
            $path = $request->file('school_favicon')->store('branding', 'public');
            Setting::set('school_favicon_url', \Illuminate\Support\Facades\Storage::url($path));
        }

        // Process deleted hero images
        if ($request->has('delete_hero_images')) {
            $currentHeroUrls = json_decode(Setting::get('school_hero_images', '[]'), true) ?: [];
            $remainingUrls = array_diff($currentHeroUrls, $request->delete_hero_images);
            Setting::set('school_hero_images', json_encode(array_values($remainingUrls)));
        }

        // Process new hero images uploads
        if ($request->hasFile('school_hero_images')) {
            $heroUrls = json_decode(Setting::get('school_hero_images', '[]'), true) ?: [];
            foreach ($request->file('school_hero_images') as $file) {
                $path = $file->store('branding', 'public');
                $heroUrls[] = \Illuminate\Support\Facades\Storage::url($path);
            }
            Setting::set('school_hero_images', json_encode($heroUrls));
        }

        return redirect()->back()->with('success', 'Pengaturan tampilan UI pendaftaran berhasil disimpan!');
    }
}
