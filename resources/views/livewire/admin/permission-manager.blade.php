<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Permission Management</h2>
            <p class="text-gray-600">Manage system permissions</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="btn btn-primary"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Permission
        </button>
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
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search permissions..."
                        class="input input-bordered w-full"
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Guard</th>
                            <th>Description</th>
                            <th>Roles Count</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $permission->name }}</div>
                                </td>
                                <td>
                                    <div class="badge badge-outline">{{ $permission->guard_name }}</div>
                                </td>
                                <td>
                                    <div class="max-w-xs truncate">
                                        {{ $permission->description ?? 'No description' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="badge badge-info">
                                        {{ $permission->roles()->count() }} roles
                                    </div>
                                </td>
                                <td>{{ $permission->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button
                                            wire:click="openEditModal({{ $permission->id }})"
                                            class="btn btn-sm btn-ghost"
                                            title="Edit"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="openDeleteModal({{ $permission->id }})"
                                            class="btn btn-sm btn-ghost text-error"
                                            title="Delete"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <p>No permissions found</p>
                                        <p class="text-sm">Create your first permission to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>

    <!-- Create Permission Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Create New Permission</h3>

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text">Permission Name *</span>
                    </label>
                    <input
                        wire:model="name"
                        type="text"
                        placeholder="e.g., manage-users"
                        class="input input-bordered w-full @error('name') input-error @enderror"
                    >
                    @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text">Guard Name *</span>
                    </label>
                    <select wire:model="guardName" class="select select-bordered w-full @error('guardName') select-error @enderror">
                        <option value="web">web</option>
                        <option value="api">api</option>
                    </select>
                    @error('guardName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-6">
                    <label class="label">
                        <span class="label-text">Description</span>
                    </label>
                    <textarea
                        wire:model="description"
                        placeholder="Optional description of what this permission allows"
                        class="textarea textarea-bordered @error('description') textarea-error @enderror"
                        rows="3"
                    ></textarea>
                    @error('description') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancel</button>
                    <button wire:click="createPermission" class="btn btn-primary">Create Permission</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Permission Modal -->
    @if($showEditModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Edit Permission</h3>

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text">Permission Name *</span>
                    </label>
                    <input
                        wire:model="name"
                        type="text"
                        placeholder="e.g., manage-users"
                        class="input input-bordered w-full @error('name') input-error @enderror"
                    >
                    @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text">Guard Name *</span>
                    </label>
                    <select wire:model="guardName" class="select select-bordered w-full @error('guardName') select-error @enderror">
                        <option value="web">web</option>
                        <option value="api">api</option>
                    </select>
                    @error('guardName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-6">
                    <label class="label">
                        <span class="label-text">Description</span>
                    </label>
                    <textarea
                        wire:model="description"
                        placeholder="Optional description of what this permission allows"
                        class="textarea textarea-bordered @error('description') textarea-error @enderror"
                        rows="3"
                    ></textarea>
                    @error('description') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancel</button>
                    <button wire:click="updatePermission" class="btn btn-primary">Update Permission</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg text-error mb-4">Confirm Deletion</h3>
                <p class="py-4">Are you sure you want to delete this permission? This action cannot be undone.</p>
                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancel</button>
                    <button wire:click="deletePermission" class="btn btn-error">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
