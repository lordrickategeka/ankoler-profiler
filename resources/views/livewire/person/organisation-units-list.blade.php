<div>
    <h2 class="text-2xl font-bold mb-4">Organization Units</h2>
    @if (session()->has('message'))
        <div class="alert alert-success mb-4">
            {{ session('message') }}
        </div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($units as $unit)
            <div class="card bg-base-100 shadow-md border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg font-semibold mb-1">{{ $unit->name }}</h3>
                    <p class="text-base-content/70 text-sm mb-2">{{ $unit->description }}</p>
                    <div class="text-xs text-base-content/50 mb-1">Code: {{ $unit->code }}</div>
                    <div class="text-xs text-base-content/50 mb-3">Active: {{ $unit->is_active ? 'Yes' : 'No' }}</div>
                    <div class="flex gap-2">
                        <button class="btn btn-primary btn-xs" wire:click.prevent="applyToJoin({{ $unit->id }})">Apply to Join</button>
                        <a href="{{ route('organization-units.index', ['unit' => $unit->id]) }}" class="btn btn-outline btn-xs">View Details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-base-content/60">No Organization units found.</div>
        @endforelse
    </div>
</div>
