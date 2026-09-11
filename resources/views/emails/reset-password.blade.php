<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password Akun SPMB</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; padding: 12px !important; }
            .card-body { padding: 24px 18px !important; }
            .btn-action { width: 100% !important; display: block !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9;">
    <!-- Outer Table -->
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 40px 0;">
        <tr>
            <td align="center" style="padding: 0 16px;">
                
                <!-- Main Container -->
                <table class="email-container" role="presentation" width="580" border="0" cellspacing="0" cellpadding="0" style="width: 580px; max-width: 580px; margin: 0 auto;">
                    
                    <!-- Top Logo & Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    @if(!empty($schoolLogo))
                                        <td align="center" style="padding-bottom: 8px;">
                                            <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" style="max-height: 48px; width: auto; display: block;">
                                        </td>
                                    @else
                                        <td align="center" style="padding-bottom: 8px;">
                                            <div style="background-color: {{ $primaryColor ?? '#0D3B2C' }}; color: {{ $secondaryColor ?? '#ffc107' }}; width: 44px; height: 44px; line-height: 44px; text-align: center; border-radius: 12px; font-weight: 900; font-size: 22px; margin: 0 auto;">
                                                S
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                <tr>
                                    <td align="center" style="text-align: center;">
                                        <div style="font-size: 16px; font-weight: 800; color: #0f172a; letter-spacing: -0.2px;">
                                            {{ $schoolName ?? 'Sekolah Anak Saleh' }}
                                        </div>
                                        @if(!empty($schoolTagline))
                                            <div style="font-size: 11px; font-weight: 600; color: #64748b; margin-top: 2px;">
                                                {{ $schoolTagline }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Card Body -->
                    <tr>
                        <td>
                            <table class="card-body" role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05); padding: 36px 32px; text-align: left;">
                                
                                <!-- Card Badge -->
                                <tr>
                                    <td style="padding-bottom: 16px;">
                                        <div style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; padding: 4px 12px; font-size: 11px; font-weight: 700; color: {{ $primaryColor ?? '#0D3B2C' }};">
                                            🔒 Layanan Keamanan Akun
                                        </div>
                                    </td>
                                </tr>

                                <!-- Greeting & Title -->
                                <tr>
                                    <td style="padding-bottom: 12px;">
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                            Atur Ulang Password Akun Anda
                                        </h1>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-size: 13px; color: #475569; line-height: 1.6; padding-bottom: 20px;">
                                        <p style="margin: 0 0 12px 0;">Assalamu'alaikum Warahmatullahi Wabarakatuh,</p>
                                        <p style="margin: 0 0 12px 0;">
                                            Halo <strong>{{ $user->name ?? 'Calon Wali Murid' }}</strong>, kami menerima permintaan untuk mereset kata sandi pada akun SPMB Online Anda di <strong>{{ $schoolName }}</strong>.
                                        </p>
                                        <p style="margin: 0;">
                                            Silakan klik tombol di bawah ini untuk melanjutkan pembuatan kata sandi yang baru:
                                        </p>
                                    </td>
                                </tr>

                                <!-- Call to Action Button -->
                                <tr>
                                    <td align="center" style="padding: 10px 0 26px 0;">
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" style="background-color: {{ $primaryColor ?? '#0D3B2C' }}; border-radius: 12px; box-shadow: 0 4px 12px rgba(13, 59, 44, 0.25);">
                                                    <a href="{{ $resetUrl }}" target="_blank" class="btn-action" style="display: inline-block; padding: 14px 32px; font-size: 13px; font-weight: 800; color: #ffffff; text-decoration: none; border-radius: 12px; letter-spacing: 0.3px; text-transform: uppercase;">
                                                        Buat Password Baru &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Security Notice Box -->
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-left: 4px solid {{ $primaryColor ?? '#0D3B2C' }}; border-radius: 8px; padding: 14px 16px;">
                                            <tr>
                                                <td style="font-size: 12px; color: #64748b; line-height: 1.5;">
                                                    ⏳ <strong>Informasi Keamanan:</strong>
                                                    <ul style="margin: 6px 0 0 0; padding-left: 18px;">
                                                        <li>Tautan reset password di atas hanya berlaku selama <strong>60 menit</strong>.</li>
                                                        <li>Jika Anda tidak pernah meminta penggantian password, abaikan email ini. Akun Anda tetap aman.</li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Fallback Link -->
                                <tr>
                                    <td style="border-top: 1px solid #e2e8f0; padding-top: 18px; font-size: 11px; color: #94a3b8; line-height: 1.5;">
                                        Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke browser web Anda:<br>
                                        <a href="{{ $resetUrl }}" style="color: {{ $primaryColor ?? '#0D3B2C' }}; word-break: break-all; text-decoration: underline;">
                                            {{ $resetUrl }}
                                        </a>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                    <!-- Footer Note -->
                    <tr>
                        <td align="center" style="padding-top: 24px; text-align: center;">
                            <p style="margin: 0 0 6px 0; font-size: 11px; color: #94a3b8; font-weight: 600;">
                                Butuh bantuan? Hubungi panitia SPMB via WhatsApp di <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp ?? '081234567890') }}" style="color: {{ $primaryColor ?? '#0D3B2C' }}; text-decoration: none; font-weight: 700;">{{ $whatsapp ?? 'Panitia SPMB' }}</a>
                            </p>
                            <p style="margin: 0; font-size: 10px; color: #cbd5e1;">
                                &copy; {{ date('Y') }} {{ $schoolName ?? 'Sekolah Anak Saleh' }}. Seluruh hak cipta dilindungi undang-undang.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
