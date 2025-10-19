@extends('emails.layouts.base')

@section('title', 'Data Export Ready')
@section('email-title', 'Your Export is Ready')

@section('greeting')
    Hello {{full_name}},
@endsection

@section('content')
    <div class="alert alert-success">
        <div class="alert-title">✅ Export Completed</div>
        <p>Your requested data export has been successfully generated and is ready for download.</p>
    </div>

    <h3>📊 Export Details</h3>
    <div class="info-box">
        <p><strong>Export Type:</strong> {{export_type}}</p>
        <p><strong>File Format:</strong> {{file_format}}</p>
        <p><strong>Generated:</strong> {{generation_datetime}}</p>
        <p><strong>File Size:</strong> {{file_size}}</p>
        @if(isset($record_count))
        <p><strong>Total Records:</strong> {{record_count}}</p>
        @endif
    </div>

    @if(isset($export_filters))
    <h3>🔍 Applied Filters</h3>
    <p>{{export_filters}}</p>
    @endif

    <div class="alert alert-warning">
        <div class="alert-title">⏰ Download Information</div>
        <ul style="margin: 10px 0;">
            <li>This download link will expire in 7 days</li>
            <li>The file will be automatically deleted after expiration</li>
            <li>Please download your file as soon as possible</li>
        </ul>
    </div>

    @if(isset($data_description))
    <h3>📋 Data Contents</h3>
    <p>{{data_description}}</p>
    @endif

    <div class="info-box">
        <p><strong>Requested by:</strong> {{full_name}} ({{email_address}})</p>
        <p><strong>Organization:</strong> {{organization_name}}</p>
        <p><strong>Request ID:</strong> {{request_id}}</p>
    </div>

    <p>Thank you for using our data export service!</p>
@endsection

@section('action-buttons')
    @if(isset($download_url))
    <a href="{{download_url}}" class="btn btn-primary">📥 Download File</a>
    @endif
    @if(isset($view_exports_url))
    <a href="{{view_exports_url}}" class="btn btn-secondary">📋 View All Exports</a>
    @endif
@endsection