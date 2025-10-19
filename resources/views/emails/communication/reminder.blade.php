@extends('emails.layouts.base')

@section('title', 'Reminder: {{reminder_title}}')
@section('email-title', 'Friendly Reminder')

@section('greeting')
    Hello {{full_name}},
@endsection

@section('content')
    <p>This is a friendly reminder regarding: <strong>{{reminder_title}}</strong></p>

    <div class="highlight-box">
        <h3 style="margin-top: 0;">📅 Reminder Details</h3>
        <p><strong>Subject:</strong> {{reminder_subject}}</p>
        @if(isset($due_date))
        <p><strong>Due Date:</strong> {{due_date}}</p>
        @endif
        @if(isset($priority))
        <p><strong>Priority:</strong> {{priority}}</p>
        @endif
    </div>

    <p>{{reminder_message}}</p>

    @if(isset($checklist))
    <h3>✅ Things to Remember</h3>
    <ul>
        @foreach($checklist as $item)
        <li>{{$item}}</li>
        @endforeach
    </ul>
    @endif

    <div class="alert alert-info">
        <div class="alert-title">💡 Need Help?</div>
        <p>If you have any questions or need assistance, please don't hesitate to contact your administrator or our support team.</p>
    </div>

    <div class="info-box">
        <p><strong>Your Role:</strong> {{role_title}}</p>
        <p><strong>Organization:</strong> {{organization_name}}</p>
        <p><strong>Contact:</strong> {{email_address}}</p>
    </div>
@endsection

@section('action-buttons')
    @if(isset($action_url))
    <a href="{{action_url}}" class="btn btn-primary">{{action_button_text}}</a>
    @endif
    @if(isset($secondary_action_url))
    <a href="{{secondary_action_url}}" class="btn btn-secondary">{{secondary_button_text}}</a>
    @endif
@endsection