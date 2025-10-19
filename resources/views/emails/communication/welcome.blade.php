@extends('emails.layouts.base')

@section('title', 'Welcome to {{ $organization_name }}')
@section('email-title', 'Welcome to Our Organization!')

@section('greeting')
    Dear {{full_name}},
@endsection

@section('content')
    <p>We are delighted to welcome you to <strong>{{organization_name}}</strong>! Your profile has been successfully created in our system.</p>

    @if(isset($temporary_password))
    <div class="highlight-box">
        <h3 style="margin-top: 0; color: #d97706;">🔑 Your Account Details</h3>
        <div class="credential-item">
            <span class="credential-label">Email:</span>
            <span class="credential-value">{{email_address}}</span>
        </div>
        <div class="credential-item">
            <span class="credential-label">Temporary Password:</span>
            <span class="credential-value">{{temporary_password}}</span>
        </div>
    </div>

    <div class="alert alert-warning">
        <div class="alert-title">🔒 Important Security Notice</div>
        <ul style="margin: 10px 0;">
            <li>Please change your password immediately after logging in</li>
            <li>Your temporary password will expire in 7 days</li>
            <li>Do not share your credentials with anyone</li>
        </ul>
    </div>
    @endif

    <h3>📋 Next Steps</h3>
    <ol>
        <li>Log in to your account using the credentials above</li>
        <li>Complete your profile information</li>
        <li>Explore the available features and tools</li>
        <li>Contact your administrator if you need assistance</li>
    </ol>

    <h3>🏢 Organization Information</h3>
    <div class="info-box">
        <p><strong>Organization:</strong> {{organization_name}}</p>
        @if(isset($role_title))
        <p><strong>Your Role:</strong> {{role_title}}</p>
        @endif
        <p><strong>Person ID:</strong> {{person_id}}</p>
    </div>

    <p>We're excited to have you as part of our community and look forward to working with you!</p>
@endsection

@section('action-buttons')
    @if(isset($login_url))
    <a href="{{login_url}}" class="btn btn-primary">🚀 Access Your Account</a>
    @endif
@endsection