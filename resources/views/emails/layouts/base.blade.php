<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ankole Profiler')</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Base styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        /* Container */
        .email-wrapper {
            background-color: #f8fafc;
            padding: 20px 0;
            min-height: 100vh;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .brand-logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .brand-tagline {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .email-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        /* Content */
        .email-content {
            padding: 40px 30px;
        }

        .email-greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }

        .email-body {
            font-size: 16px;
            line-height: 1.7;
            color: #374151;
        }

        .email-body p {
            margin-bottom: 16px;
        }

        .email-body h3 {
            color: #1f2937;
            margin: 25px 0 15px 0;
            font-size: 18px;
        }

        .email-body ul, .email-body ol {
            margin: 15px 0;
            padding-left: 25px;
        }

        .email-body li {
            margin-bottom: 8px;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #f97316;
            color: white;
        }

        .btn-primary:hover {
            background-color: #ea580c;
            color: white;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
            color: white;
        }

        /* Alert boxes */
        .alert {
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border-left: 4px solid;
        }

        .alert-info {
            background-color: #f0f9ff;
            border-left-color: #0ea5e9;
            color: #0c4a6e;
        }

        .alert-warning {
            background-color: #fffbeb;
            border-left-color: #f59e0b;
            color: #92400e;
        }

        .alert-success {
            background-color: #f0fdf4;
            border-left-color: #22c55e;
            color: #166534;
        }

        .alert-danger {
            background-color: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
        }

        .alert-title {
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Info boxes */
        .info-box {
            background-color: #f8fafc;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .highlight-box {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        /* Credentials styling */
        .credential-item {
            margin: 12px 0;
            font-size: 16px;
        }

        .credential-label {
            font-weight: 600;
            color: #6b7280;
        }

        .credential-value {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #dc2626;
            background-color: #f9fafb;
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid #e5e7eb;
        }

        /* Footer */
        .email-footer {
            background-color: #f8fafc;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        .footer-links {
            margin: 15px 0;
        }

        .footer-links a {
            color: #f97316;
            text-decoration: none;
            margin: 0 15px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            
            .email-header,
            .email-content,
            .email-footer {
                padding: 20px;
            }
            
            .brand-logo {
                font-size: 24px;
            }
            
            .email-title {
                font-size: 20px;
            }

            .btn {
                display: block;
                width: 100%;
                max-width: 280px;
                margin: 20px auto;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #1f2937;
            }
            
            .email-content {
                color: #e5e7eb;
            }
            
            .email-greeting,
            .email-body h3 {
                color: #f3f4f6;
            }
            
            .email-body {
                color: #d1d5db;
            }
            
            .email-footer {
                background-color: #111827;
                color: #9ca3af;
            }
            
            .info-box {
                background-color: #374151;
                border-color: #4b5563;
            }
            
            .credential-value {
                background-color: #374151;
                border-color: #4b5563;
                color: #fbbf24;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="brand-logo">
                    🏢 Ankole Profiler
                </div>
                <div class="brand-tagline">
                    Professional Data Management System
                </div>
                @hasSection('custom-header')
                    @yield('custom-header')
                @else
                    <h1 class="email-title">@yield('email-title', 'System Notification')</h1>
                @endif
            </div>

            <!-- Content -->
            <div class="email-content">
                @hasSection('greeting')
                    <div class="email-greeting">
                        @yield('greeting')
                    </div>
                @endif

                <div class="email-body">
                    @yield('content')
                </div>

                @hasSection('action-buttons')
                    <div style="text-align: center; margin: 30px 0;">
                        @yield('action-buttons')
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="email-footer">
                @hasSection('custom-footer')
                    @yield('custom-footer')
                @else
                    <p><strong>Ankole Profiler Team</strong></p>
                    
                    <div class="footer-links">
                        @if(isset($supportUrl))
                            <a href="{{ $supportUrl }}">Support</a>
                        @endif
                        @if(isset($unsubscribeUrl))
                            <a href="{{ $unsubscribeUrl }}">Unsubscribe</a>
                        @endif
                        @if(isset($privacyUrl))
                            <a href="{{ $privacyUrl }}">Privacy Policy</a>
                        @endif
                    </div>

                    <p>© {{ date('Y') }} Ankole Profiler. All rights reserved.</p>
                    <p style="margin-top: 10px; font-size: 12px;">
                        This is an automated email. Please do not reply to this message.
                    </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>