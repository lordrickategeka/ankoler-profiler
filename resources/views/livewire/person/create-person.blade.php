<div>
    {{-- Header Section --}}
    <div
        class="flex items-center justify-between {{ $currentStep ? 'bg-gradient-to-r from-primary/5 to-transparent p-4 rounded-lg border border-primary/10' : '' }}">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Person') }}
            </h2>
            <p class="text-gray-600 text-sm mt-1">Step
                {{ array_search($currentStep, ['basic_info', 'contact_address', 'document_info', 'affiliation_details']) + 1 }}
                of 4:
                @switch($currentStep)
                    @case('basic_info')
                        Personal Information
                    @break

                    @case('contact_address')
                        Contact & Address Details
                    @break

                    @case('document_info')
                        Document & Identifiers
                    @break

                        @case('affiliation_details')
                            Organization Affiliations
                        @break

                    @endswitch
            </p>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('persons') }}" class="btn btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Persons
            </a>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Tab Navigation --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="flex overflow-x-auto">
                        @php
                            $steps = ['basic_info', 'contact_address', 'document_info', 'affiliation_details'];
                            $stepLabels = ['Personal', 'Contact & Address', 'Documents', 'Affiliations'];
                            $currentStepIndex = array_search($currentStep, $steps);
                        @endphp

                        @foreach ($steps as $index => $step)
                            <button type="button" wire:click="$set('currentStep', '{{ $step }}')"
                                class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap {{ $currentStep === $step
                                    ? 'text-primary border-primary bg-primary/5'
                                    : ($index < $currentStepIndex
                                        ? 'text-gray-600 border-gray-300 hover:text-gray-800 hover:border-gray-400'
                                        : 'text-gray-400 border-gray-200 cursor-not-allowed') }}"
                                {{ $index > $currentStepIndex ? 'disabled' : '' }}>
                                <div class="flex items-center justify-center gap-2">
                                    <span
                                        class="w-6 h-6 rounded-full text-xs flex items-center justify-center {{ $currentStep === $step
                                            ? 'bg-primary text-white'
                                            : ($index < $currentStepIndex
                                                ? 'bg-green-100 text-green-600'
                                                : 'bg-gray-100 text-gray-400') }}">
                                        @if ($index < $currentStepIndex)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </span>
                                    <span class="text-xs sm:text-sm">
                                        {{ $stepLabels[$index] }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Toast Container --}}
            <div id="toast-container" class="toast toast-top toast-end z-50"></div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form wire:submit="submit">
                    {{-- Error Summary Section --}}
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 mx-6 mt-6 rounded-r-lg">
                            <div>
                                    <h3 class="text-sm font-medium text-red-800">
                                        Please correct the following {{ $errors->count() > 1 ? 'errors' : 'error' }}:
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li class="flex items-start">
                                                    {{-- <svg class="w-3 h-3 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg> --}}
                                                    {{ $error }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="p-2">
                        {{-- Step 1: Personal Information --}}
                        @if ($currentStep === 'basic_info')
                            <div class="space-y-2">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Personal Information</h3>
                                    <p class="text-gray-600 text-sm mb-4">Provide the basic personal details for this
                                        person.</p>
                                </div>

                                {{-- Organization Info Alert for non-Super Admin users --}}
                                @if($isOrganizationLocked && $selectedOrganisationName)
                                    <div class="alert alert-info mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>This person will be automatically affiliated with <strong>{{ $selectedOrganisationName }}</strong> when created.</span>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Given Name <span
                                                    class="text-red-500">*</span></span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" wire:model.blur="form.given_name"
                                                class="input input-bordered input-sm w-full pr-10 @error('form.given_name') input-error border-red-500 @enderror"
                                                placeholder="Enter given name">
                                            @error('form.given_name')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    {{-- <svg class="h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg> --}}
                                                </div>
                                            @enderror
                                        </div>
                                        @error('form.given_name')
                                            <div class="flex items-center mt-1">
                                                <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-red-600 text-xs font-medium">{{ $message }}</span>
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Middle Name</span>
                                        </label>
                                        <input type="text" wire:model="form.middle_name"
                                            class="input input-bordered input-sm" placeholder="Enter middle name">
                                        @error('form.middle_name')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Family Name <span
                                                    class="text-red-500">*</span></span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" wire:model.blur="form.family_name"
                                                class="input input-bordered input-sm w-full pr-10 @error('form.family_name') input-error border-red-500 @enderror"
                                                placeholder="Enter family name">
                                            @error('form.family_name')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    {{-- <svg class="h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg> --}}
                                                </div>
                                            @enderror
                                        </div>
                                        @error('form.family_name')
                                            <div class="flex items-center mt-1">
                                                <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-red-600 text-xs font-medium">{{ $message }}</span>
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Date of Birth</span>
                                        </label>
                                        <input type="date" wire:model.live="form.date_of_birth"
                                            class="input input-bordered input-sm">
                                        @error('form.date_of_birth')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Gender</span>
                                        </label>
                                        <select wire:model="form.gender" class="select select-bordered select-sm">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                            <option value="prefer_not_to_say">Prefer Not to Say</option>
                                        </select>
                                        @error('form.gender')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Contact & Address Information --}}
                        @if ($currentStep === 'contact_address')
                            <div class="space-y-4">
                                {{-- Contact Information Section --}}
                                <div>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Contact Information</h3>
                                    <p class="text-gray-600 text-sm mb-4">Provide contact details and identification
                                        information.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Phone Number</span>
                                        </label>
                                        <div class="relative">
                                            <input type="tel" wire:model.blur="form.phone"
                                                class="input input-bordered input-sm w-full pr-10 @error('form.phone') input-error border-red-500 @enderror"
                                                placeholder="+256 700 123 456">
                                            @error('form.phone')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    {{-- <svg class="h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg> --}}
                                                </div>
                                            @enderror
                                        </div>
                                        @error('form.phone')
                                            <div class="flex items-center mt-1">
                                                <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-red-600 text-xs font-medium">{{ $message }}</span>
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Email Address</span>
                                        </label>
                                        <div class="relative">
                                            <input type="email" wire:model.blur="form.email"
                                                class="input input-bordered input-sm w-full pr-10 @error('form.email') input-error border-red-500 @enderror"
                                                placeholder="jane.doe@email.com">
                                            @error('form.email')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    {{-- <svg class="h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg> --}}
                                                </div>
                                            @enderror
                                        </div>
                                        @error('form.email')
                                            <div class="flex items-center mt-1">
                                                {{-- <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg> --}}
                                                <span class="text-red-600 text-xs font-medium">{{ $message }}</span>
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">National ID</span>
                                        </label>
                                        <input type="text" wire:model.live.debounce.500ms="form.national_id"
                                            class="input input-bordered input-sm" placeholder="CM950320123456XYZ">
                                        @error('form.national_id')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Address Information Section --}}
                                <div class="mt-6">
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Address Information</h3>
                                    <p class="text-gray-600 text-sm mb-4">Provide the residential address and location
                                        details.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-control md:col-span-3">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Street Address</span>
                                        </label>
                                        <textarea wire:model="form.address" class="textarea textarea-bordered textarea-sm h-16"
                                            placeholder="Street address, building, apartment"></textarea>
                                        @error('form.address')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">City</span>
                                        </label>
                                        <input type="text" wire:model="form.city"
                                            class="input input-bordered input-sm" placeholder="City or town">
                                        @error('form.city')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">District</span>
                                        </label>
                                        <input type="text" wire:model="form.district"
                                            class="input input-bordered input-sm" placeholder="District or region">
                                        @error('form.district')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Country <span
                                                    class="text-red-500">*</span></span>
                                        </label>
                                        <select wire:model="form.country" class="select select-bordered select-sm">
                                            <option value="UGA">Uganda</option>
                                            <option value="KEN">Kenya</option>
                                            <option value="TZA">Tanzania</option>
                                            <option value="RWA">Rwanda</option>
                                            <option value="USA">United States</option>
                                            <option value="GBR">United Kingdom</option>
                                        </select>
                                        @error('form.country')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 3: Document Information --}}
                        @if ($currentStep === 'document_info')
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Document & Identifiers</h3>
                                    <p class="text-gray-600 text-sm mb-4">Provide additional identification documents
                                        and certificates.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Passport Number</span>
                                        </label>
                                        <input type="text" wire:model="form.passport_number"
                                            class="input input-bordered input-sm" placeholder="Passport number">
                                        @error('form.passport_number')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Driver's License</span>
                                        </label>
                                        <input type="text" wire:model="form.drivers_license"
                                            class="input input-bordered input-sm"
                                            placeholder="Driver's license number">
                                        @error('form.drivers_license')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Professional License</span>
                                        </label>
                                        <input type="text" wire:model="form.professional_license"
                                            class="input input-bordered input-sm"
                                            placeholder="Professional license number">
                                        @error('form.professional_license')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 4: Multiple Affiliations Management --}}
                        @if ($currentStep === 'affiliation_details' && !$showDuplicateWarning)
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Organization Affiliations</h3>
                                    <p class="text-gray-600 text-sm mb-4">Define the person's roles and relationships with organizations. You can add multiple affiliations.</p>
                                </div>

                                {{-- Existing Affiliations List --}}
                                @if (!empty($affiliations))
                                    <div class="space-y-3">
                                        <h4 class="text-sm font-medium text-gray-700">Added Affiliations ({{ count($affiliations) }})</h4>

                                        @foreach ($affiliations as $index => $affiliation)
                                            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-sm font-medium text-green-800">
                                                            @php
                                                                $org = $availableOrganisations ? collect($availableOrganisations)->firstWhere('id', $affiliation['organisation_id']) : null;
                                                                echo $org ? ($org['display_name'] ?? $org['legal_name']) : 'Unknown Organization';
                                                            @endphp
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                                                            {{ $this->getRoleLabelForOrganization($affiliation['role_type'], $affiliation['organisation_id']) }}
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-green-600">
                                                        @if ($affiliation['role_title'])
                                                            Title: {{ $affiliation['role_title'] }} •
                                                        @endif
                                                        Start: {{ \Carbon\Carbon::parse($affiliation['start_date'])->format('M d, Y') }}
                                                        @if ($affiliation['site'])
                                                            • Site: {{ $affiliation['site'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" wire:click="editAffiliation({{ $index }})"
                                                        class="btn btn-xs btn-outline btn-primary">
                                                        Edit
                                                    </button>
                                                    <button type="button" wire:click="removeAffiliation({{ $index }})"
                                                        class="btn btn-xs btn-outline btn-error">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Add New Affiliation Form --}}
                                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">
                                        {{ $editingAffiliationIndex !== null ? 'Edit Affiliation' : 'Add New Affiliation' }}
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {{-- Organization Selection --}}
                                        <div class="form-control">
                                            <label class="label pb-1">
                                                <span class="label-text text-sm font-medium">Organization <span class="text-red-500">*</span></span>
                                            </label>
                                            @if ($isSuperAdmin)
                                                <div class="text-xs text-purple-600 mb-1">
                                                    🌟 Super Admin - All organizations available
                                                </div>
                                                <select wire:model.live="currentAffiliation.organisation_id" class="select select-bordered select-sm">
                                                    <option value="">Select Organization</option>
                                                    @if (isset($availableOrganisations))
                                                        @foreach ($availableOrganisations as $org)
                                                            <option value="{{ $org['id'] }}">
                                                                {{ $org['display_name'] ?? $org['legal_name'] }}
                                                                @if ($org['is_super'])
                                                                    (Super)
                                                                @endif
                                                                - {{ ucfirst($org['category']) }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @else
                                                @if($isOrganizationLocked)
                                                    <div class="text-xs text-info mb-1">
                                                        📍 Persons will be automatically affiliated with your organization
                                                    </div>
                                                    <input type="text"
                                                           value="{{ $selectedOrganisationName }}"
                                                           class="input input-bordered input-sm bg-base-200"
                                                           readonly>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Organization is pre-selected based on your access
                                                    </div>
                                                @else
                                                    <div class="text-xs text-blue-600 mb-1">
                                                        Organization Admin - Restricted to your organization
                                                    </div>
                                                    @php $userOrgs = $this->getAvailableOrganizations(); @endphp
                                                    <select wire:model.live="currentAffiliation.organisation_id" class="select select-bordered select-sm">
                                                        <option value="">Select Organization</option>
                                                        @foreach ($userOrgs as $org)
                                                            <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            @endif
                                            @error('currentAffiliation.organisation_id')
                                                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Role Type --}}
                                        <div class="form-control">
                                            <label class="label pb-1">
                                                <span class="label-text text-sm font-medium">Role Type <span class="text-red-500">*</span></span>
                                            </label>
                                            @if($isSuperAdmin)
                                                <div class="text-xs text-purple-600 mb-1">
                                                    🌟 Super Admin - All role types available
                                                </div>
                                            @elseif($currentAffiliation['organisation_id'])
                                                @php
                                                    $selectedOrg = collect($availableOrganisations ?? [])->firstWhere('id', $currentAffiliation['organisation_id']);
                                                    $orgCategory = $selectedOrg['category'] ?? 'general';
                                                @endphp
                                                <div class="text-xs text-blue-600 mb-1">
                                                    📋 Showing {{ ucfirst($orgCategory) }} specific roles
                                                </div>
                                            @endif
                                            <select wire:model.live="currentAffiliation.role_type" class="select select-bordered select-sm">
                                                <option value="">Select Role Type</option>
                                                @if (isset($currentAffiliationRoles))
                                                    @foreach ($currentAffiliationRoles as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('currentAffiliation.role_type')
                                                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Role Title --}}
                                        <div class="form-control">
                                            <label class="label pb-1">
                                                <span class="label-text text-sm font-medium">Role Title</span>
                                            </label>
                                            <input type="text" wire:model="currentAffiliation.role_title"
                                                class="input input-bordered input-sm"
                                                placeholder="e.g., Senior Nurse, Manager">
                                            @error('currentAffiliation.role_title')
                                                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Site --}}
                                        <div class="form-control">
                                            <label class="label pb-1">
                                                <span class="label-text text-sm font-medium">Site/Location</span>
                                            </label>
                                            <input type="text" wire:model="currentAffiliation.site"
                                                class="input input-bordered input-sm"
                                                placeholder="e.g., Main Campus, Branch Office">
                                            @error('currentAffiliation.site')
                                                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Start Date --}}
                                        <div class="form-control">
                                            <label class="label pb-1">
                                                <span class="label-text text-sm font-medium">Start Date <span class="text-red-500">*</span></span>
                                            </label>
                                            <input type="date" wire:model="currentAffiliation.start_date"
                                                class="input input-bordered input-sm">
                                            @error('currentAffiliation.start_date')
                                                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Role-Specific Fields --}}
                                    @if ($currentAffiliation['role_type'])
                                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <h5 class="text-sm font-medium text-blue-800 mb-3">
                                                {{ ucwords(str_replace('_', ' ', $currentAffiliation['role_type'])) }} Specific Information
                                            </h5>
                                            <div class="text-xs text-blue-600 mb-3">
                                                Optional: Add role-specific details for this affiliation.
                                            </div>

                                            {{-- Include role-specific fields based on role type --}}
                                            @switch($currentAffiliation['role_type'])
                                                @case('STAFF')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Staff Number</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.staff_number"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Staff ID/Number">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Payroll ID</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.payroll_id"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Payroll Identifier">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Employment Type</span>
                                                            </label>
                                                            <select wire:model="domainRecord.employment_type" class="select select-bordered select-xs">
                                                                <option value="">Select Type</option>
                                                                <option value="permanent">Permanent</option>
                                                                <option value="contract">Contract</option>
                                                                <option value="casual">Casual</option>
                                                                <option value="intern">Intern</option>
                                                                <option value="temporary">Temporary</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Grade/Level</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.grade"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Employee Grade/Level">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Contract Start Date</span>
                                                            </label>
                                                            <input type="date" wire:model="domainRecord.contract_start"
                                                                class="input input-bordered input-xs">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Contract End Date</span>
                                                            </label>
                                                            <input type="date" wire:model="domainRecord.contract_end"
                                                                class="input input-bordered input-xs">
                                                        </div>
                                                    </div>
                                                @break

                                                @case('STUDENT')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Student Number</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.student_number"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Student ID/Number">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Enrollment Date</span>
                                                            </label>
                                                            <input type="date" wire:model="domainRecord.enrollment_date"
                                                                class="input input-bordered input-xs">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Current Class/Grade</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.current_class"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Class/Grade Level">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Guardian Name</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.guardian_name"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Parent/Guardian Name">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Guardian Phone</span>
                                                            </label>
                                                            <input type="tel" wire:model="domainRecord.guardian_phone"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Guardian Phone Number">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Guardian Email</span>
                                                            </label>
                                                            <input type="email" wire:model="domainRecord.guardian_email"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Guardian Email Address">
                                                        </div>
                                                    </div>
                                                @break

                                                @case('PATIENT')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Patient Number</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.patient_number"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Patient ID/Number">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Medical Record Number</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.medical_record_number"
                                                                class="input input-bordered input-xs"
                                                                placeholder="MRN">
                                                        </div>
                                                        <div class="form-control md:col-span-2">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Known Allergies</span>
                                                            </label>
                                                            <textarea wire:model="domainRecord.allergies"
                                                                class="textarea textarea-bordered textarea-xs h-16"
                                                                placeholder="List any known allergies..."></textarea>
                                                        </div>
                                                        <div class="form-control md:col-span-2">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Chronic Conditions</span>
                                                            </label>
                                                            <textarea wire:model="domainRecord.chronic_conditions"
                                                                class="textarea textarea-bordered textarea-xs h-16"
                                                                placeholder="List any chronic conditions..."></textarea>
                                                        </div>
                                                    </div>
                                                @break

                                                @case('MEMBER')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Membership Number</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.membership_number"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Member ID/Number">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Join Date</span>
                                                            </label>
                                                            <input type="date" wire:model="domainRecord.join_date"
                                                                class="input input-bordered input-xs">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Share Capital</span>
                                                            </label>
                                                            <input type="number" wire:model="domainRecord.share_capital"
                                                                class="input input-bordered input-xs"
                                                                placeholder="0.00" step="0.01" min="0">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Savings Account Reference</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.savings_account_ref"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Savings Account Number">
                                                        </div>
                                                    </div>
                                                @break

                                                @case('PARISHIONER')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Member Number</span>
                                                            </label>
                                                            <input type="text" wire:model="domainRecord.member_number"
                                                                class="input input-bordered input-xs"
                                                                placeholder="Parish Member Number">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Baptism Date</span>
                                                            </label>
                                                            <input type="date" wire:model="domainRecord.baptism_date"
                                                                class="input input-bordered input-xs">
                                                        </div>
                                                        <div class="form-control">
                                                            <label class="label pb-1">
                                                                <span class="label-text text-xs">Communion Status</span>
                                                            </label>
                                                            <select wire:model="domainRecord.communion_status" class="select select-bordered select-xs">
                                                                <option value="">Select Status</option>
                                                                <option value="first_communion">First Communion</option>
                                                                <option value="confirmed">Confirmed</option>
                                                                <option value="not_applicable">Not Applicable</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @break
                                            @endswitch
                                        </div>
                                    @endif

                                    {{-- Add/Update Affiliation Button --}}
                                    <div class="flex justify-end mt-4">
                                        @if ($editingAffiliationIndex !== null)
                                            <div class="flex gap-2">
                                                <button type="button" wire:click="resetCurrentAffiliation"
                                                    class="btn btn-sm btn-outline">
                                                    Cancel
                                                </button>
                                                <button type="button" wire:click="addAffiliation"
                                                    class="btn btn-sm btn-primary"
                                                    wire:loading.attr="disabled"
                                                    wire:target="addAffiliation">
                                                    <span wire:loading.remove wire:target="addAffiliation">Update Affiliation</span>
                                                    <span wire:loading wire:target="addAffiliation" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Updating...
                                                    </span>
                                                </button>
                                            </div>
                                        @else
                                            <button type="button" wire:click="addAffiliation"
                                                class="btn btn-sm btn-primary"
                                                wire:loading.attr="disabled"
                                                wire:target="addAffiliation">
                                                <span wire:loading.remove wire:target="addAffiliation">Add Affiliation</span>
                                                <span wire:loading wire:target="addAffiliation" class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Adding...
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Validation Message --}}
                                @if (empty($affiliations))
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-amber-800 text-sm">
                                                Please add at least one affiliation for this person.
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>

                    {{-- Navigation Buttons --}}
                    <div class="px-4 py-3 bg-gray-50 border-t flex justify-between">
                        <div>
                            @php
                                $steps = ['basic_info', 'contact_address', 'document_info', 'affiliation_details'];
                                $currentStepIndex = array_search($currentStep, $steps);
                            @endphp

                            @if ($currentStepIndex > 0)
                                <button type="button"
                                    wire:click="$set('currentStep', '{{ $steps[$currentStepIndex - 1] }}')"
                                    class="btn btn-ghost">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Previous
                                </button>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            @if ($currentStepIndex < count($steps) - 1)
                                @if ($currentStep === 'affiliation_details')
                                    {{-- Special validation for affiliations step --}}
                                    @if (empty($affiliations))
                                        <button type="button" disabled
                                            class="btn btn-primary btn-disabled tooltip tooltip-left"
                                            data-tip="Please add at least one affiliation before proceeding">
                                            Next
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button"
                                            wire:click="$set('currentStep', '{{ $steps[$currentStepIndex + 1] }}')"
                                            class="btn btn-primary">
                                            Next
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    @endif
                                @else
                                    <button type="button"
                                        wire:click="$set('currentStep', '{{ $steps[$currentStepIndex + 1] }}')"
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled"
                                        wire:target="$set">
                                        <span wire:loading wire:target="$set">
                                            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                        <span wire:loading.remove wire:target="$set">
                                            Next
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </span>
                                    </button>
                                @endif
                            @else
                                <button type="button" wire:click="submit"
                                    class="btn btn-success"
                                    wire:loading.attr="disabled"
                                    wire:target="submit"
                                    @if ($isLoading) disabled @endif>
                                    @if ($isLoading)
                                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Creating Person...
                                    @else
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Create Person
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Duplicate Warning Modal --}}
            @if ($showDuplicateWarning && isset($duplicates) && $duplicates->isNotEmpty())
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        ⚠️ Similar Person Found ({{ $duplicates->first()['similarity'] ?? '0' }}%
                                        Match)
                                    </h3>
                                </div>
                            </div>

                            @foreach ($duplicates->take(3) as $duplicate)
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                                👤 {{ $duplicate['person']->full_name }}
                                            </h4>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                                <div>
                                                    <p><strong>Born:</strong>
                                                        {{ $duplicate['person']->date_of_birth?->format('F j, Y') ?: 'Not specified' }}
                                                    </p>
                                                    <p><strong>Phone:</strong>
                                                        {{ $duplicate['person']->primaryPhone()?->number ?: 'Not specified' }}
                                                    </p>
                                                    <p><strong>Email:</strong>
                                                        {{ $duplicate['person']->primaryEmail()?->email ?: 'Not specified' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p><strong>National ID:</strong>
                                                        {{ $duplicate['person']->nationalId()?->identifier ?: 'Not specified' }}
                                                    </p>
                                                    <p><strong>Address:</strong>
                                                        {{ $duplicate['person']->address ?: 'Not specified' }}</p>
                                                    <p><strong>Match Type:</strong>
                                                        {{ ucfirst(str_replace('_', ' ', $duplicate['match_type'] ?? '')) }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <h5 class="font-medium text-gray-800 mb-2">🏢 Current Affiliations:
                                                </h5>
                                                @if (isset($duplicate['person']->activeAffiliations))
                                                    @forelse($duplicate['person']->activeAffiliations as $affiliation)
                                                        <div class="bg-white rounded p-3 mb-2">
                                                            <p class="font-medium">
                                                                {{ $affiliation->organisation->name ?? 'Unknown Organization' }}
                                                            </p>
                                                            <p class="text-sm text-gray-600">
                                                                {{ $affiliation->role_type }}
                                                                @if ($affiliation->role_title)
                                                                    - {{ $affiliation->role_title }}
                                                                @endif
                                                            </p>
                                                            <p class="text-sm text-gray-500">
                                                                Started:
                                                                {{ $affiliation->start_date->format('M j, Y') }} |
                                                                Status: {{ ucfirst($affiliation->status) }}
                                                            </p>
                                                        </div>
                                                    @empty
                                                        <p class="text-gray-500 italic">No active affiliations</p>
                                                    @endforelse
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex flex-wrap gap-2 mt-4">
                                        <button wire:click="linkToExisting({{ $duplicate['person']->id }})"
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                                            wire:loading.attr="disabled"
                                            wire:target="linkToExisting">
                                            <span wire:loading.remove wire:target="linkToExisting">
                                                🔗 Link to Existing Person
                                            </span>
                                            <span wire:loading wire:target="linkToExisting" class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Linking...
                                            </span>
                                        </button>
                                        <button wire:click="viewProfile({{ $duplicate['person']->id }})"
                                            class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                                            👤 View Full Profile First
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Modal Actions --}}
                            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">
                                <button wire:click="createAsNew"
                                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
                                    wire:loading.attr="disabled"
                                    wire:target="createAsNew">
                                    <span wire:loading.remove wire:target="createAsNew">
                                        ✨ Create as New Person
                                    </span>
                                    <span wire:loading wire:target="createAsNew" class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                </button>
                                <button wire:click="dismissDuplicateWarning"
                                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                                    ❌ Cancel
                                </button>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <strong>What would you like to do?</strong><br>
                                    <strong>Link to Existing:</strong> Reuse existing person record and add new
                                    affiliation to
                                    {{ $currentOrganisation->display_name ?? ($currentOrganisation->legal_name ?? 'your organization' ?? 'your organization') }}<br>
                                    <strong>View Profile:</strong> See complete details before deciding<br>
                                    <strong>Create as New:</strong> This is a different person with similar details
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Loading Overlay --}}
            @if (isset($isLoading) && $isLoading)
                <div class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-40">
                    <div class="bg-white rounded-lg p-6 flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Checking for duplicates...
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', function() {
        // Listen for toast events from Livewire
        Livewire.on('show-toast', function(data) {
            showToast(data[0].type, data[0].message);
        });
    });

    function showToast(type, message) {
        const toastContainer = document.getElementById('toast-container');

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${getAlertClass(type)} shadow-lg mb-2 animate-fade-in`;

        // Toast content
        toast.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                ${getToastIcon(type)}
            </div>
            <div class="ml-3 flex-1">
                <span class="text-sm font-medium">${message}</span>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button type="button" class="btn btn-sm btn-ghost btn-square" onclick="dismissToast(this)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;

        // Add to container
        toastContainer.appendChild(toast);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            dismissToast(toast.querySelector('button'));
        }, 5000);
    }

    function getAlertClass(type) {
        switch (type) {
            case 'success':
                return 'success';
            case 'error':
                return 'error';
            case 'info':
                return 'info';
            case 'warning':
                return 'warning';
            default:
                return 'info';
        }
    }

    function getToastIcon(type) {
        switch (type) {
            case 'success':
                return `<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>`;
            case 'error':
                return `<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>`;
            case 'info':
                return `<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>`;
            case 'warning':
                return `<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>`;
            default:
                return `<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>`;
        }
    }

    function dismissToast(button) {
        const toast = button.closest('.alert');
        if (toast) {
            toast.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';

            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }
</script>
@endpush

@push('styles')
<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateX(100%);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
</style>
@endpush
