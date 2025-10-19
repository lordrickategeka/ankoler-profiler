@extends('emails.layouts.base')

@section('title', 'Password Reset Request')
@section('email-title', 'Reset Your Password')

@section('greeting')
    Hello {{full_name}},
@endsection

@section('content')
    <p>We received a request to reset the password for your account associated with <strong>{{email_address}}</strong>.</p>

    <div class="alert alert-info">
        <div class="alert-title">🔐 Password Reset Instructions</div>
        <p>Click the button below to create a new password. This link will expire in 60 minutes for security purposes.</p>
    </div>

    <p>If you did not request this password reset, please ignore this email. Your account remains secure.</p>

    <h3>🛡️ Security Tips</h3>
    <ul>
        <li>Choose a strong password with at least 8 characters</li>
        <li>Include uppercase and lowercase letters, numbers, and symbols</li>
        <li>Don't reuse passwords from other accounts</li>
        <li>Consider using a password manager</li>
    </ul>

    <div class="info-box">
        <p><strong>Account:</strong> {{email_address}}</p>
        <p><strong>Request Time:</strong> {{current_datetime}}</p>
        <p><strong>Organization:</strong> {{organization_name}}</p>
    </div>
@endsection

@section('action-buttons')
    <a href="{{reset_url}}" class="btn btn-primary">🔄 Reset Password</a>
@endsection