@extends('emails.layouts.base')

@section('title', 'Meeting Invitation')
@section('email-title', 'You\'re Invited to a Meeting')

@section('greeting')
    Dear {{full_name}},
@endsection

@section('content')
    <p>You have been invited to attend the following meeting:</p>

    <div class="highlight-box">
        <h3 style="margin-top: 0;">📅 Meeting Details</h3>
        <div class="credential-item">
            <span class="credential-label">Title:</span>
            <span style="color: #1f2937; font-weight: 600;">{{meeting_title}}</span>
        </div>
        <div class="credential-item">
            <span class="credential-label">Date & Time:</span>
            <span style="color: #1f2937; font-weight: 600;">{{meeting_datetime}}</span>
        </div>
        @if(isset($meeting_location))
        <div class="credential-item">
            <span class="credential-label">Location:</span>
            <span style="color: #1f2937; font-weight: 600;">{{meeting_location}}</span>
        </div>
        @endif
        @if(isset($meeting_link))
        <div class="credential-item">
            <span class="credential-label">Online Link:</span>
            <a href="{{meeting_link}}" style="color: #f97316; font-weight: 600;">Join Meeting</a>
        </div>
        @endif
        <div class="credential-item">
            <span class="credential-label">Organizer:</span>
            <span style="color: #1f2937; font-weight: 600;">{{organizer_name}}</span>
        </div>
    </div>

    @if(isset($meeting_agenda))
    <h3>📋 Agenda</h3>
    <p>{{meeting_agenda}}</p>
    @endif

    @if(isset($preparation_notes))
    <div class="alert alert-info">
        <div class="alert-title">📝 Preparation Notes</div>
        <p>{{preparation_notes}}</p>
    </div>
    @endif

    @if(isset($attendees_list))
    <h3>👥 Expected Attendees</h3>
    <p>{{attendees_list}}</p>
    @endif

    <div class="info-box">
        <p><strong>Your Role:</strong> {{role_title}}</p>
        <p><strong>Organization:</strong> {{organization_name}}</p>
        @if(isset($meeting_duration))
        <p><strong>Duration:</strong> {{meeting_duration}}</p>
        @endif
    </div>

    <p>Please confirm your attendance by clicking one of the buttons below.</p>
@endsection

@section('action-buttons')
    @if(isset($accept_url))
    <a href="{{accept_url}}" class="btn btn-primary">✅ Accept Invitation</a>
    @endif
    @if(isset($decline_url))
    <a href="{{decline_url}}" class="btn btn-secondary">❌ Decline Invitation</a>
    @endif
@endsection