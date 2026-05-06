<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
</head>
<body style="margin: 0; padding: 0; background-color: #1a4d2e; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding: 60px 0;">
                <!-- Main Card -->
                <table role="presentation" width="100%" style="max-width: 500px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding: 48px; text-align: center;">
                            <!-- Header -->
                            <div style="margin-bottom: 32px;">
                                <h2 style="color: #1a4d2e; font-size: 28px; margin: 0; font-weight: 700; letter-spacing: -0.5px;">Verifikasi Akun</h2>
                            </div>
                            
                            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 32px 0;">
                                Anda menerima email ini karena Anda telah meminta kode verifikasi untuk masuk ke akun <strong>QurbanHub</strong> Anda.
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #f7fafc; border: 1px solid #edf2f7; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
                                <p style="color: #718096; font-size: 13px; font-weight: 600; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 1px;">Kode OTP Anda</p>
                                <div style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #1a4d2e; font-family: 'Courier New', Courier, monospace;">
                                    {{ $otpCode }}
                                </div>
                            </div>

                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 32px 0;">
                                Kode ini berlaku selama <strong>15 menit</strong>. Untuk keamanan, jangan berikan kode ini kepada siapapun termasuk pihak yang mengaku sebagai QurbanHub.
                            </p>

                            <div style="border-top: 1px solid #edf2f7; padding-top: 24px;">
                                <p style="color: #718096; font-size: 13px; margin: 0;">
                                    Jika Anda tidak merasa meminta kode ini, abaikan email ini atau hubungi tim dukungan kami.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Footer Navigation with Icons -->
                <div style="margin-top: 32px; text-align: center;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;">
                        <tr>
                            <td style="padding: 0 15px;">
                                <a href="https://wa.me/yournumber" target="_blank" style="text-decoration: none; color: #c9a227;">
                                    <img src="https://img.icons8.com/ios-filled/32/c9a227/whatsapp.png" width="24" height="24" alt="WhatsApp" style="display: block; border: 0;">
                                </a>
                            </td>
                            <td style="padding: 0 15px;">
                                <a href="mailto:support@qurbanhub.com" style="text-decoration: none; color: #c9a227;">
                                    <img src="https://img.icons8.com/ios-filled/32/c9a227/email.png" width="24" height="24" alt="Email" style="display: block; border: 0;">
                                </a>
                            </td>
                            <td style="padding: 0 15px;">
                                <a href="https://qurbanhub.com" target="_blank" style="text-decoration: none; color: #c9a227;">
                                    <img src="https://img.icons8.com/ios-filled/32/c9a227/globe.png" width="24" height="24" alt="Website" style="display: block; border: 0;">
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>

                <p style="color: #ffffff; font-size: 11px; opacity: 0.5; margin-top: 32px; text-transform: uppercase; letter-spacing: 1px;">
                    &copy; 2026 QurbanHub. Seluruh hak cipta dilindungi.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
