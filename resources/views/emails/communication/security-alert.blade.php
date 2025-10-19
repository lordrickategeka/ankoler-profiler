@extends('emails.layouts.base')

@section('title', 'Account Alert')
@section('email-title', 'Security Alert')

@section('greeting')
    Hello {{full_name}},
@endsection

@section('content')
    <div class="alert alert-warning">
        <div class="alert-title">🔐 Security Alert</div>
        <p>We detected some important activity on your account that requires your attention.</p>
    </div>

    <h3>📊 Activity Details</h3>
    <div class="info-box">
        <p><strong>Activity Type:</strong> {{activity_type}}</p>
        <p><strong>Date & Time:</strong> {{activity_datetime}}</p>
        <p><strong>IP Address:</strong> {{ip_address}}</p>
        @if(isset($device_info))
        <p><strong>Device:</strong> {{device_info}}</p>
        @endif
        @if(isset($location))
        <p><strong>Location:</strong> {{location}}</p>
        @endif
    </div>

    @if(isset($security_action_taken))
    <div class="alert alert-info">
        <div class="alert-title">🛡️ Security Actions Taken</div>
        <p>{{security_action_taken}}</p>
    </div>
    @endif

    <h3>🔒 Recommended Actions</h3>
    <ul>
        <li>Review your recent account activity</li>
        <li>Change your password if you don't recognize this activity</li>
        <li>Enable two-factor authentication if not already active</li>
        <li>Log out of all devices and log back in</li>
        <li>Contact support if you suspect unauthorized access</li>
    </ul>

    <div class="alert alert-danger">
        <div class="alert-title">⚠️ If This Wasn't You</div>
        <p>If you did not perform this activity, your account may be compromised. Please take immediate action to secure your account.</p>
    </div>

    <div class="info-box">
        <p><strong>Account:</strong> {{email_address}}</p>
        <p><strong>Organization:</strong> {{organization_name}}</p>
        <p><strong>Person ID:</strong> {{person_id}}</p>
    </div>
@endsection

@section('action-buttons')
    @if(isset($secure_account_url))
    <a href="{{secure_account_url}}" class="btn btn-primary">🔒 Secure My Account</a>
    @endif
    @if(isset($contact_support_url))
    <a href="{{contact_support_url}}" class="btn btn-secondary">📞 Contact Support</a>
    @endif
@endsection