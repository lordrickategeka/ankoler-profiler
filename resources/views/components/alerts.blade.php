@props(['success' => session('success'), 'error' => session('error'), 'errorReason' => session('error_reason'), 'info' => session('info')])

@if ($success)
    <div class="alert alert-success mb-6">
        <p class="text-sm font-medium">{{ $success }}</p>
    </div>
@endif

@if ($error)
    <div class="alert alert-error mb-6">
        <p class="text-sm font-medium">{{ $error }}</p>
        @if ($errorReason)
            <p class="text-sm text-gray-600">Reason: {{ $errorReason }}</p>
        @endif
    </div>
@endif

@if ($info)
    <div class="alert alert-info mb-6">
        <p class="text-sm font-medium">{{ $info }}</p>
    </div>
@endif
