<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'school_name', 'value' => 'Sekolah Anak Saleh'],
            ['key' => 'portal_hero_title', 'value' => 'Membangun Generasi Cerdas, Sholeh, dan Berakhlak Mulia.'],
            ['key' => 'portal_hero_description', 'value' => 'Bergabunglah bersama Sekolah Anak Saleh. Kami menyajikan kurikulum yang mengintegrasikan nilai-nilai Islam dengan pendidikan modern untuk menyiapkan pemimpin masa depan.'],
            ['key' => 'winpay_mode', 'value' => 'simulator'],
            ['key' => 'winpay_merchant_id', 'value' => 'MOCK_MERCHANT_ID'],
            ['key' => 'winpay_client_key', 'value' => 'MOCK_CLIENT_KEY'],
            ['key' => 'winpay_client_secret', 'value' => 'MOCK_CLIENT_SECRET'],
            ['key' => 'winpay_sandbox_merchant_id', 'value' => '171001519'],
            ['key' => 'winpay_sandbox_client_key', 'value' => 'f754edbc-dc93-49e6-986a-1e9e18158938'],
            ['key' => 'winpay_sandbox_client_secret', 'value' => 'SANDBOX_CLIENT_SECRET'],
            ['key' => 'winpay_sandbox_private_key', 'value' => "-----BEGIN PRIVATE KEY-----\n" .
                "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDQHqtVMsLLWxY8\n" .
                "7hPUHbPxzmniCUjjp8fctjPvqOH1CZZWTeBKN4ag0jiFy0/wYhFvEYS4JhdTuNoc\n" .
                "vuJmffHKJuvMCu3C6g7FxlBYobtQWKmBng1lVJ1Iyz6NrewNVXANmfycFdXuCH6M\n" .
                "RQ6vudS5H5QCdWtu3pBkkaHSJaWzy0B6KUKfKT25kJ9Ncsv2tjT1H3NlqkxQvp8Y\n" .
                "cYsHMiL15jPnP1v+pa82O6u/WBXFxKJ1DaybG8dy60cuDEpN6M1DwrziemSmBRrw\n" .
                "b1I4LOe5duBR3r6JGW3qPkgtFp2O/PXGRJHBfd8CxJzhTLrSF+1prBJ454oywq92\n" .
                "zqXESRnhAgMBAAECggEAVicUAtlYBOmIe6GMiMbg+izZ7Q2t5DvMwwOT3VZ6bz7Q\n" .
                "QprLSb3Rl9peNpiS124pTGKin7548pn3hGXKf+YMBQR2oQk3InRUuC9fjEkrKtgB\n" .
                "F1yPrA5Ka9ti4jCIon5nO+IuTYjGfdp7VGKz8S+KrTWyxg/IcOVmPZOBuuYFwbaW\n" .
                "tPb6Fil3beijrPzB0yrP0hF82CxqbLY3Tg0BOZiXrFNFjmyckcfTERYIaUBhTuAX\n" .
                "sPceBX2bGt6Wu50Agv0LbDc66jIZvsS11hoKu1TSo8yOkvZoL+9LKOW84pCFJDX0\n" .
                "6U70Gwy7wfBpdKf+WOCs/Fr/zQKbgVujfLMA06JUZwKBgQD4XvoG1dc4pwbyrfs1\n" .
                "xxBmM7B0ASxSqHJ448wvKEAyb1ME6wKTBXGjDNYbdFLVX+vpTEtHeSHkMu+hR1yF\n" .
                "jYsRfltSu/vJT2z3VPd/3h2iDx5owwcGcPP45Ad/iLhmIl+0gbaxMkBpqJCF+rRs\n" .
                "+iKn5soxNHwP1YbhRuPDo9kZZwKBgQDWgy8hFwvKNKnKKWzjREFOqPZpt+flWXYR\n" .
                "yuaUVhaq4eYzJ33TA71nEP+ZvtaEoisnHAqHxtvXhCc1m7Cj5jlHCHwQiQcG3YEf\n" .
                "hyTBlbcGXIJQKpGdsq760F8zfrfpGy3ClrZCo7ywViiQaQ0bCRQAevOLB4/RFVoR\n" .
                "n+qHefF9dwKBgQDsSv+4LQ27Gj0j+J38xcw2T4racptGcHenx6FkY/jfgsYK8cLb\n" .
                "ONyp8PZp3DtKQR3iMPGVqAq0XjlYyNmfPdBG7l3X0nxzQ5s5m550Ck9K9PNLW/B9\n" .
                "Ek0qR1dS4DH/CUjgJGA5KMPbQcFtldy9qSP7dTh7o6E8NztBa/4ZDPLolQKBgQDB\n" .
                "tVLg0bvezDGrEj929xL2YlOqYd0x2dhp9szDlP4BL989wGK6I71sjggSoSd8PCk1\n" .
                "tve3ZpbthjQWD9KyHtsITxwhnmvPAkVw4AwMGBNf1jgDBn3aZxnl+jaN/Nc81EM9\n" .
                "XfWWNd/VaOhWh9bC3C7IxD6bBKgVSe+8zKjvz+mHvwKBgB61mcNFAGd0EJPKDMbN\n" .
                "BhlXjwUv81EJ37quz26sLFm2SOMIm09zB/TIbWbqpGWjV46LWYZgePbhl2XhHtGP\n" .
                "TTDX5e5Qi6zAtUOOF1rPV9B8sWTmyLDkqOMGurIoLV0goItbLIbj1usf/sAmCHIz\n" .
                "HXAZEuYXVTbkUdedO9K0jqRc\n" .
                "-----END PRIVATE KEY-----"],
            ['key' => 'spmb_qrcode_url', 'value' => 'https://spmb.sans.sch.id'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
