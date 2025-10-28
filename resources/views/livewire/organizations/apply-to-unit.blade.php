<div class="my-4">
    @if (session()->has('success'))
        <div class="toast toast-top toast-end">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="toast toast-top toast-end">
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @auth
        @if ($isMember)
            <span class="badge badge-success">Already a Member</span>
        @elseif ($hasPendingApplication)
            <span class="badge badge-warning">Application Pending</span>
        @else
            <button wire:click="apply" class="btn btn-primary btn-sm">Apply to Join</button>
        @endif
    @else
        <span class="text-gray-500">Please log in to apply.</span>
    @endauth
</div>
