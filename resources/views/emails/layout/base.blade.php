<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Scandere AI Store' }}</title>
    <style type="text/css">
        /* Reset styles */
        body {
            margin: 0;
            padding: 0;
            min-width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            display: block;
        }

        /* Main container */
        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 30px 40px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .email-header .logo {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
        }

        /* Content */
        .email-content {
            padding: 40px;
            color: #1f2937;
        }

        .email-content h2 {
            margin: 0 0 20px 0;
            color: #111827;
            font-size: 24px;
            font-weight: 600;
        }

        .email-content p {
            margin: 0 0 16px 0;
            color: #4b5563;
            line-height: 1.6;
        }

        /* Button */
        .email-button {
            display: inline-block;
            padding: 14px 32px;
            margin: 20px 0;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: transform 0.2s;
        }

        .email-button:hover {
            transform: translateY(-2px);
        }

        /* Table styles */
        .info-table {
            width: 100%;
            margin: 20px 0;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .info-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table .label {
            font-weight: 600;
            color: #374151;
            width: 40%;
        }

        .info-table .value {
            color: #6b7280;
        }

        /* Divider */
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }

        /* Alert boxes */
        .alert {
            padding: 16px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .alert-info {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }

        .alert-success {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }

        /* Footer */
        .email-footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .email-footer p {
            margin: 0 0 10px 0;
            color: #6b7280;
            font-size: 14px;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }

        .social-links a:hover {
            color: #6366f1;
        }

        .footer-links {
            margin: 15px 0;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            margin: 0 10px;
            font-size: 13px;
        }

        .footer-links a:hover {
            color: #6366f1;
            text-decoration: underline;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 0;
            }

            .email-header,
            .email-content,
            .email-footer {
                padding: 20px !important;
            }

            .email-header h1 {
                font-size: 24px !important;
            }

            .email-content h2 {
                font-size: 20px !important;
            }

            .email-button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box;
            }

            .info-table .label {
                width: 35% !important;
                font-size: 14px;
            }

            .info-table .value {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-container" width="600" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <a href="{{ config('app.frontend_url') }}" class="logo">Scandere AI</a>
                            @isset($headerTitle)
                                <h1>{{ $headerTitle }}</h1>
                            @endisset
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="email-content">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p><strong>Scandere AI Store</strong></p>
                            <p>Premium AI Tools & Digital Products</p>

                            <div class="social-links">
                                <a href="{{ config('app.frontend_url') }}">Website</a>
                                <a href="{{ config('app.frontend_url') }}/contact">Contact</a>
                                <a href="{{ config('app.frontend_url') }}/support">Support</a>
                            </div>

                            <div class="footer-links">
                                <a href="{{ config('app.frontend_url') }}/privacy">Privacy Policy</a>
                                <a href="{{ config('app.frontend_url') }}/terms">Terms of Service</a>
                                @isset($unsubscribeUrl)
                                    <a href="{{ $unsubscribeUrl }}">Unsubscribe</a>
                                @endisset
                            </div>

                            <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
                                &copy; {{ date('Y') }} Scandere AI Store. All rights reserved.
                            </p>

                            @isset($footerNote)
                                <p style="margin-top: 15px; font-size: 12px; color: #6b7280;">
                                    {!! $footerNote !!}
                                </p>
                            @endisset
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
