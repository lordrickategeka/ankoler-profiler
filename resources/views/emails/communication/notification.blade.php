@extends('emails.layouts.base')

@section('title', 'System Notification')
@section('email-title', '{{notification_title}}')

@section('greeting')
    Dear {{full_name}},
@endsection

@section('content')
    <p>{{notification_message}}</p>

    @if(isset($notification_type))
        @if($notification_type == 'info')
            <div class="alert alert-info">
                <div class="alert-title">ℹ️ Information</div>
                <p>{{notification_details}}</p>
            </div>
        @elseif($notification_type == 'warning')
            <div class="alert alert-warning">
                <div class="alert-title">⚠️ Warning</div>
                <p>{{notification_details}}</p>
            </div>
        @elseif($notification_type == 'success')
            <div class="alert alert-success">
                <div class="alert-title">✅ Success</div>
                <p>{{notification_details}}</p>
            </div>
        @elseif($notification_type == 'danger')
            <div class="alert alert-danger">
                <div class="alert-title">🚨 Important</div>
                <p>{{notification_details}}</p>
            </div>
        @endif
    @endif

    @if(isset($action_required))
    <h3>📋 Action Required</h3>
    <p>{{action_instructions}}</p>
    @endif

    <div class="info-box">
        <p><strong>Date:</strong> {{current_datetime}}</p>
        <p><strong>Organization:</strong> {{organization_name}}</p>
        @if(isset($reference_number))
        <p><strong>Reference:</strong> {{reference_number}}</p>
        @endif
    </div>
@endsection

@section('action-buttons')
    @if(isset($action_url))
    <a href="{{action_url}}" class="btn btn-primary">{{action_button_text}}</a>
    @endif
@endsection