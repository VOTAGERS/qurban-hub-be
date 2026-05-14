<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'DM Sans', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #4a4843;
            margin: 0;
            padding: 0;
            background-color: #f9f8f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            background: #7A1B2E;
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-family: 'Playfair Display', serif;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #7A1B2E;
            font-size: 20px;
            margin-top: 0;
        }
        .order-box {
            background: #fcf5f6;
            border: 1px solid #e8e6df;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
            background: #fdfcf9;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #7A1B2E;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ILM Qurban</h1>
            <p>Your Qurban Worship, Made Easier</p>
        </div>
        <div class="content">
            <h2>Jazakumullah Khairan Katsiran, {{ $order->user->first_name }}!</h2>
            <p>Thank you for entrusting your Qurban worship through <strong>ILM Qurban</strong>. Your payment has been received and your order is currently being processed.</p>
            
            <div class="order-box">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td width="130" style="width: 130px; padding: 10px 0; color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e8e6df;">Order Code</td>
                        <td width="20" style="width: 20px; padding: 10px 0; color: #888; border-bottom: 1px solid #e8e6df;">:</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #7A1B2E; font-size: 15px; border-bottom: 1px solid #e8e6df;">#{{ $order->order_code }}</td>
                    </tr>
                    <tr>
                        <td width="130" style="width: 130px; padding: 10px 0; color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e8e6df;">Product</td>
                        <td width="20" style="width: 20px; padding: 10px 0; color: #888; border-bottom: 1px solid #e8e6df;">:</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #7A1B2E; font-size: 15px; border-bottom: 1px solid #e8e6df;">{{ $order->productWoo->name ?? 'Qurban Package' }}</td>
                    </tr>
                    <tr>
                        <td width="130" style="width: 130px; padding: 10px 0; color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e8e6df;">Quantity</td>
                        <td width="20" style="width: 20px; padding: 10px 0; color: #888; border-bottom: 1px solid #e8e6df;">:</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #7A1B2E; font-size: 15px; border-bottom: 1px solid #e8e6df;">{{ $order->quantity }} Head/Share</td>
                    </tr>
                    <tr>
                        <td width="130" style="width: 130px; padding: 10px 0; color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Total Payment</td>
                        <td width="20" style="width: 20px; padding: 10px 0; color: #888;">:</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #7A1B2E; font-size: 15px;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            <p style="margin-top: 30px;">May this Qurban worship bring blessings and ongoing rewards for you and your family. Ameen.</p>            
            <p>Warm regards,<br><strong>The QurbanHub Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} QurbanHub Indonesia. All rights reserved.</p>
            <p>If you have any questions, please contact us via WhatsApp or Email support.</p>
        </div>
    </div>
</body>
</html>