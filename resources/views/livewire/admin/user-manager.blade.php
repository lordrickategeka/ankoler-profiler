<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
            <p class="text-gray-600">Manage system users and their role assignments</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <div class="flex gap-4">
                <div class="form-control flex-1">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Search users by name or email..." class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <select wire:model.live="roleFilter" class="select select-bordered">
                        <option value="all">All Roles</option>
                        @foreach ($allRoles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <select wire:model.live="organizationFilter" class="select select-bordered">
                        <option value="all">All Organizations</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->display_name ?? $org->legal_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Organization</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-12 h-12">
                                                @if ($user->profile_photo_url)
                                                    <img src="{{ $user->profile_photo_url }}"
                                                        alt="{{ $user->name }}" />
                                                @else
                                                    <div
                                                        class="bg-neutral text-neutral-content flex items-center justify-center w-12 h-12">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $user->name }}</div>
                                            @if ($user->current_team_id)
                                                <div class="text-sm opacity-50">Team ID: {{ $user->current_team_id }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $user->email }}</div>
                                    @if ($user->email_verified_at)
                                        <div class="badge badge-success badge-sm">Verified</div>
                                    @else
                                        <div class="badge badge-warning badge-sm">Unverified</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->Organization)
                                        <div class="font-medium">
                                            {{ $user->Organization->display_name ?? $user->Organization->legal_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $user->Organization->code }}</div>
                                    @else
                                        <div class="text-gray-400 italic">No organization</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <div class="badge badge-outline badge-sm">{{ $role->name }}</div>
                                        @empty
                                            <div class="text-gray-500 text-sm">No roles assigned</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <div class="badge badge-success">Active</div>
                                    @else
                                        <div class="badge badge-error">Inactive</div>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button wire:click="openRolesModal({{ $user->id }})"
                                            class="btn btn-sm btn-ghost text-info" title="Manage Roles">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 5 15.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button wire:click="openOrganizationModal({{ $user->id }})"
                                            class="btn btn-sm btn-ghost text-primary"
                                            title="Manage Organization - User ID: {{ $user->id }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                </path>
                                            </svg>
                                        </button>
                                        <button wire:click="toggleUserStatus({{ $user->id }})"
                                            class="btn btn-sm btn-ghost {{ $user->email_verified_at ? 'text-warning' : 'text-success' }}"
                                            title="{{ $user->email_verified_at ? 'Deactivate' : 'Activate' }}">
                                            @if ($user->email_verified_at)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636">
                                                    </path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @endif
                                        </button>
                                        <button wire:click="openDeleteModal({{ $user->id }})"
                                            class="btn btn-sm btn-ghost text-error" title="Delete User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <p>No users found</p>
                                        <p class="text-sm">Try adjusting your search or filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Manage Roles Modal -->
    @if ($showRolesModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl">
                <h3 class="font-bold text-lg mb-4">Manage User Roles</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                    @foreach ($allRoles as $role)
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start">
                                <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}"
                                    class="checkbox checkbox-primary mr-3">
                                <div>
                                    <span class="label-text font-medium">{{ $role->name }}</span>
                                    <br><span class="label-text-alt text-gray-500">{{ $role->guard_name }}
                                        guard</span>
                                    @if ($role->permissions->count() > 0)
                                        <br><span
                                            class="label-text-alt text-blue-500">{{ $role->permissions->count() }}
                                            permissions</span>
                                    @endif
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancel</button>
                    <button wire:click="updateUserRoles" class="btn btn-primary">Update Roles</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg text-error mb-4">Confirm User Deletion</h3>
                <p class="py-4">Are you sure you want to delete this user? This action cannot be undone and will
                    remove all user data.</p>
                <div class="alert alert-warning">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    <span>This will permanently delete the user and all associated data.</span>
                </div>
                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancel</button>
                    <button wire:click="deleteUser" class="btn btn-error">Delete User</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Manage Organization Modal -->
    @if ($showOrganizationModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Manage User Organization</h3>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Organization</span>
                    </label>
                    <select wire:model="selectedOrganization" class="select select-bordered w-full">
                        <option value="">No Organization</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}">
                                {{ $org->display_name ?? $org->legal_name }}
                                @if ($org->code)
                                    ({{ $org->code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancel</button>
                    <button wire:click="updateUserOrganization" class="btn btn-primary">Update Organization</button>
                </div>
            </div>
        </div>
    @endif
</div>
