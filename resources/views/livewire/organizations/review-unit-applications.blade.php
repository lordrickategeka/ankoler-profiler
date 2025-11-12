<div class="max-w-4xl mx-auto mt-10 bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Pending Unit Membership Applications</h2>
    <div class="overflow-x-auto">
        <form wire:submit.prevent>
        <div class="mb-3 flex gap-2">
            <button type="button" class="btn btn-success btn-sm" wire:click="bulkApprove" @if(empty($selectedIds)) disabled @endif>Bulk Approve</button>
            <button type="button" class="btn btn-error btn-sm" wire:click="bulkReject" @if(empty($selectedIds)) disabled @endif>Bulk Reject</button>
        </div>
        <table class="table w-full">
            <thead>
                <tr>
                    <th><input type="checkbox" wire:model="selectAll" wire:click="$set('selectedIds', $selectAll ? [] : $applications->pluck('id')->toArray())"></th>
                    <th>Applicant</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($applications) && count($applications))
                    @foreach($applications as $app)
                        @php
                            $person = \App\Models\Person::find($app->person_id);
                            $unit = \App\Models\OrganizationUnit::find($app->unit_id);
                        @endphp
                        <tr>
                            <td><input type="checkbox" wire:model="selectedIds" value="{{ $app->id }}"></td>
                            <td>{{ $person?->full_name ?? 'N/A' }}</td>
                            <td>{{ $unit?->name ?? 'N/A' }}</td>
                            <td>
                                @if($app->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($app->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($app->status === 'rejected')
                                    <span class="badge badge-error">Rejected</span>
                                @else
                                    <span class="badge">{{ ucfirst($app->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-success btn-xs" wire:click="approve({{ $app->id }})">Approve</button>
                                <button class="btn btn-error btn-xs" wire:click="reject({{ $app->id }})">Reject</button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100%" class="text-center">No applications found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        </form>
    </div>
    @if($selectedApplication)
        @php
            $person = \App\Models\Person::find($selectedApplication->person_id ?? null);
            $unit = \App\Models\OrganizationUnit::find($selectedApplication->unit_id ?? null);
        @endphp
        <div class="mt-6 p-4 border rounded bg-gray-50">
            <h3 class="font-bold mb-2">Application Details</h3>
            <div><strong>Applicant:</strong> {{ $person?->full_name ?? 'N/A' }}</div>
            <div><strong>Unit:</strong> {{ $unit?->name ?? 'N/A' }}</div>
            <div><strong>Status:</strong> {{ ucfirst($selectedApplication->status) }}</div>
            <div class="mt-2">
                <button class="btn btn-success btn-sm" wire:click="approve({{ $selectedApplication->id }})">Approve</button>
                <button class="btn btn-error btn-sm" wire:click="reject({{ $selectedApplication->id }})">Reject</button>
            </div>
        </div>
    @endif
</div>
