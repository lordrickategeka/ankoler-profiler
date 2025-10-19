<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $organization->legal_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f97316;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 10px;
        }
        .organization-name {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        .welcome-message {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
        }
        .credentials-box {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .credential-item {
            margin: 10px 0;
            font-size: 16px;
        }
        .credential-label {
            font-weight: bold;
            color: #666;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #d97706;
            background-color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-left: 10px;
        }
        .login-button {
            display: inline-block;
            background-color: #f97316;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .login-button:hover {
            background-color: #ea580c;
        }
        .security-notice {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-title {
            color: #dc2626;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .instructions {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .instructions-title {
            color: #0284c7;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #666;
            font-size: 14px;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🏢 Ankole Profiler</div>
            <div class="organization-name">{{ $organization->legal_name }}</div>
            <div class="welcome-message">Welcome to the System!</div>
        </div>

        <p>Dear {{ $user->name }},</p>

        <p>Congratulations! You have been assigned as a <strong>System Administrator</strong> for <strong>{{ $organization->legal_name }}</strong>. This email contains your login credentials and important information to get you started.</p>

        <div class="credentials-box">
            <h3 style="margin-top: 0; color: #d97706;">🔑 Your Login Credentials</h3>
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $user->email }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Temporary Password:</span>
                <span class="credential-value">{{ $temporaryPassword }}</span>
            </div>
            <div style="margin-top: 15px;">
                <a href="{{ $loginUrl }}" class="login-button">🚀 Login to System</a>
            </div>
        </div>

        <div class="security-notice">
            <div class="security-title">🔒 Important Security Notice</div>
            <ul>
                <li><strong>Change your password immediately</strong> after your first login</li>
                <li>Do not share your login credentials with anyone</li>
                <li>This temporary password will expire in 7 days</li>
                <li>Always log out when you're done using the system</li>
                <li>Use a strong, unique password that you don't use elsewhere</li>
            </ul>
        </div>

        <div class="instructions">
            <div class="instructions-title">📋 Getting Started Instructions</div>
            <ol>
                <li>Click the "Login to System" button above or visit: <strong>{{ $loginUrl }}</strong></li>
                <li>Enter your email and the temporary password provided</li>
                <li>You will be prompted to change your password on first login</li>
                <li>Complete your profile setup and review organization settings</li>
                <li>Familiarize yourself with the admin dashboard and available features</li>
            </ol>
        </div>

        <h3>📊 Organization Details</h3>
        <ul>
            <li><strong>Organization:</strong> {{ $organization->legal_name }}</li>
            <li><strong>Category:</strong> {{ ucfirst($organization->category) }}</li>
            <li><strong>Registration Number:</strong> {{ $organization->registration_number }}</li>
            <li><strong>Contact Email:</strong> {{ $organization->contact_email }}</li>
            @if($organization->website_url)
            <li><strong>Website:</strong> {{ $organization->website_url }}</li>
            @endif
        </ul>

        <h3>🛠️ Your Administrator Responsibilities</h3>
        <p>As a System Administrator, you have access to:</p>
        <ul>
            <li>Organization management and settings</li>
            <li>User management and role assignments</li>
            <li>Data management and reporting</li>
            <li>System configuration and security settings</li>
            <li>Analytics and dashboard insights</li>
        </ul>

        <div style="background-color: #f9fafb; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p><strong>Need Help?</strong></p>
            <p>If you have any questions or need assistance, please contact our support team or refer to the system documentation available in the admin panel.</p>
        </div>

        <p>We're excited to have you on board and look forward to your contribution to {{ $organization->legal_name }}!</p>

        <p>Best regards,<br>
        <strong>Ankole Profiler Team</strong></p>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>© {{ date('Y') }} Ankole Profiler. All rights reserved.</p>
            <p>Login URL: {{ $loginUrl }}</p>
        </div>
    </div>
</body>
</html>
