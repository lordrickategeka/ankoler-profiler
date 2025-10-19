{{-- School-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">School Type <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.school_type" class="select select-bordered">
                <option value="">Select School Type</option>
                <option value="PRIMARY">Primary School</option>
                <option value="SECONDARY">Secondary School</option>
                <option value="UNIVERSITY">University</option>
                <option value="COLLEGE">College</option>
                <option value="VOCATIONAL">Vocational Institute</option>
                <option value="NURSERY">Nursery School</option>
                <option value="SPECIAL_NEEDS">Special Needs School</option>
            </select>
            @error('categoryDetails.school_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Education Level</span>
            </label>
            <select wire:model="categoryDetails.education_level" class="select select-bordered">
                <option value="">Select Level</option>
                <option value="PRE_PRIMARY">Pre-Primary</option>
                <option value="PRIMARY">Primary</option>
                <option value="SECONDARY">Secondary (O-Level)</option>
                <option value="ADVANCED">Advanced (A-Level)</option>
                <option value="TERTIARY">Tertiary/Higher</option>
                <option value="VOCATIONAL">Vocational/Technical</option>
            </select>
            @error('categoryDetails.education_level') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Student Capacity <span class="text-red-500">*</span></span>
            </label>
            <input type="number" wire:model="categoryDetails.student_capacity" class="input input-bordered"
                   placeholder="Maximum student enrollment" min="1">
            @error('categoryDetails.student_capacity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Current Enrollment</span>
            </label>
            <input type="number" wire:model="categoryDetails.current_enrollment" class="input input-bordered"
                   placeholder="Current number of students" min="0">
            @error('categoryDetails.current_enrollment') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Teachers</span>
            </label>
            <input type="number" wire:model="categoryDetails.number_of_teachers" class="input input-bordered"
                   placeholder="Total teaching staff" min="0">
            @error('categoryDetails.number_of_teachers') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Classrooms</span>
            </label>
            <input type="number" wire:model="categoryDetails.number_of_classrooms" class="input input-bordered"
                   placeholder="Total classrooms" min="0">
            @error('categoryDetails.number_of_classrooms') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Principal/Head Name</span>
            </label>
            <input type="text" wire:model="categoryDetails.principal_name" class="input input-bordered"
                   placeholder="Principal or Head Teacher name">
            @error('categoryDetails.principal_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Academic Year Start</span>
            </label>
            <select wire:model="categoryDetails.academic_year_start" class="select select-bordered">
                <option value="">Select Month</option>
                <option value="JANUARY">January</option>
                <option value="FEBRUARY">February</option>
                <option value="MARCH">March</option>
                <option value="APRIL">April</option>
                <option value="MAY">May</option>
                <option value="JUNE">June</option>
                <option value="JULY">July</option>
                <option value="AUGUST">August</option>
                <option value="SEPTEMBER">September</option>
                <option value="OCTOBER">October</option>
                <option value="NOVEMBER">November</option>
                <option value="DECEMBER">December</option>
            </select>
            @error('categoryDetails.academic_year_start') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Facilities Available --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Available Facilities</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @php
                $facilities = [
                    'library' => 'Library',
                    'laboratory' => 'Laboratory',
                    'computer_lab' => 'Computer Lab',
                    'sports_ground' => 'Sports Ground',
                    'dormitory' => 'Dormitory',
                    'cafeteria' => 'Cafeteria',
                    'medical_center' => 'Medical Center',
                    'auditorium' => 'Auditorium',
                    'workshop' => 'Workshop',
                    'art_studio' => 'Art Studio',
                    'music_room' => 'Music Room',
                    'swimming_pool' => 'Swimming Pool'
                ];
            @endphp

            @foreach($facilities as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.facilities" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.facilities') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Academic Programs --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Academic Programs/Subjects Offered</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $programs = [
                    'sciences' => 'Sciences',
                    'mathematics' => 'Mathematics',
                    'languages' => 'Languages',
                    'social_studies' => 'Social Studies',
                    'arts' => 'Arts',
                    'business_studies' => 'Business Studies',
                    'technical_subjects' => 'Technical Subjects',
                    'computer_studies' => 'Computer Studies',
                    'agriculture' => 'Agriculture',
                    'home_economics' => 'Home Economics',
                    'music' => 'Music',
                    'physical_education' => 'Physical Education'
                ];
            @endphp

            @foreach($programs as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.academic_programs" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.academic_programs') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Additional Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">School Fee Range (Annual)</span>
            </label>
            <input type="text" wire:model="categoryDetails.fee_range" class="input input-bordered"
                   placeholder="e.g., UGX 500,000 - 2,000,000">
            @error('categoryDetails.fee_range') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Language of Instruction</span>
            </label>
            <select wire:model="categoryDetails.language_of_instruction" class="select select-bordered">
                <option value="">Select Language</option>
                <option value="ENGLISH">English</option>
                <option value="LUGANDA">Luganda</option>
                <option value="SWAHILI">Swahili</option>
                <option value="FRENCH">French</option>
                <option value="MULTILINGUAL">Multilingual</option>
            </select>
            @error('categoryDetails.language_of_instruction') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Accreditation --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Accreditation & Affiliations</span>
        </label>
        <textarea wire:model="categoryDetails.accreditation_details" class="textarea textarea-bordered h-20"
                  placeholder="Ministry of Education registration, university affiliations, international accreditations, etc."></textarea>
        @error('categoryDetails.accreditation_details') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
