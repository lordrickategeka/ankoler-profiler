{{-- Hospital-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Hospital Type <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.hospital_type" class="select select-bordered">
                <option value="">Select Hospital Type</option>
                <option value="PUBLIC">Public Hospital</option>
                <option value="PRIVATE">Private Hospital</option>
                <option value="TEACHING">Teaching Hospital</option>
                <option value="SPECIALIST">Specialist Hospital</option>
                <option value="REFERRAL">Referral Hospital</option>
                <option value="COMMUNITY">Community Hospital</option>
            </select>
            @error('categoryDetails.hospital_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Bed Capacity <span class="text-red-500">*</span></span>
            </label>
            <input type="number" wire:model="categoryDetails.bed_capacity" class="input input-bordered"
                   placeholder="Number of beds" min="1">
            @error('categoryDetails.bed_capacity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Level of Care</span>
            </label>
            <select wire:model="categoryDetails.level_of_care" class="select select-bordered">
                <option value="">Select Level</option>
                <option value="PRIMARY">Primary Care</option>
                <option value="SECONDARY">Secondary Care</option>
                <option value="TERTIARY">Tertiary Care</option>
                <option value="QUATERNARY">Quaternary Care</option>
            </select>
            @error('categoryDetails.level_of_care') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Emergency Services</span>
            </label>
            <select wire:model="categoryDetails.emergency_services" class="select select-bordered">
                <option value="0">No Emergency Services</option>
                <option value="1">Emergency Services Available</option>
            </select>
            @error('categoryDetails.emergency_services') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Medical Director Name</span>
            </label>
            <input type="text" wire:model="categoryDetails.medical_director" class="input input-bordered"
                   placeholder="Dr. John Doe">
            @error('categoryDetails.medical_director') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Chief Medical Officer</span>
            </label>
            <input type="text" wire:model="categoryDetails.chief_medical_officer" class="input input-bordered"
                   placeholder="Dr. Jane Smith">
            @error('categoryDetails.chief_medical_officer') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Specialized Departments --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Specialized Departments</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @php
                $departments = [
                    'cardiology' => 'Cardiology',
                    'neurology' => 'Neurology',
                    'orthopedics' => 'Orthopedics',
                    'pediatrics' => 'Pediatrics',
                    'maternity' => 'Maternity',
                    'surgery' => 'Surgery',
                    'radiology' => 'Radiology',
                    'laboratory' => 'Laboratory',
                    'pharmacy' => 'Pharmacy',
                    'icu' => 'ICU',
                    'oncology' => 'Oncology',
                    'psychiatry' => 'Psychiatry'
                ];
            @endphp

            @foreach($departments as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.departments" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.departments') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Additional Hospital Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Doctors</span>
            </label>
            <input type="number" wire:model="categoryDetails.number_of_doctors" class="input input-bordered"
                   placeholder="Total doctors" min="0">
            @error('categoryDetails.number_of_doctors') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Nurses</span>
            </label>
            <input type="number" wire:model="categoryDetails.number_of_nurses" class="input input-bordered"
                   placeholder="Total nurses" min="0">
            @error('categoryDetails.number_of_nurses') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Specialists</span>
            </label>
            <input type="number" wire:model="categoryDetails.number_of_specialists" class="input input-bordered"
                   placeholder="Total specialists" min="0">
            @error('categoryDetails.number_of_specialists') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Operating Theaters</span>
            </label>
            <input type="number" wire:model="categoryDetails.operating_theaters" class="input input-bordered"
                   placeholder="Number of operating theaters" min="0">
            @error('categoryDetails.operating_theaters') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Certifications --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Certifications & Accreditations</span>
        </label>
        <textarea wire:model="categoryDetails.certifications" class="textarea textarea-bordered h-20"
                  placeholder="List any medical certifications, quality accreditations, or international standards compliance"></textarea>
        @error('categoryDetails.certifications') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
