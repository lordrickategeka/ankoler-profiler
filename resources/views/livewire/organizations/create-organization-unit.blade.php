<div class="max-w-xl mx-auto mt-10 bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Create Organization Unit</h2>
    @if(session()->has('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif
    <form wire:submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model="name" class="input input-bordered w-full" required placeholder="Unit Name">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Code <span class="text-red-500">*</span></label>
            <input type="text" wire:model="code" class="input input-bordered w-full" required placeholder="Unit Code">
            @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea wire:model="description" class="input input-bordered w-full" rows="2" placeholder="Description"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Parent Unit</label>
            <select wire:model="parent_unit_id" class="input input-bordered w-full">
                <option value="">None</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
            @error('parent_unit_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model="is_active" id="is_active" class="checkbox">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            @error('is_active') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('organization-units.index') }}" class="btn" style="background-color: #00ADED; color: #fff;">Cancel</a>
            <button type="submit" class="btn" style="background-color: #00ADED; color: #fff;">Create</button>
        </div>
    </form>
</div>
