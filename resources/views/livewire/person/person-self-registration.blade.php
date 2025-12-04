
<div>
    {{-- Header Section --}}
    <div class="flex items-center justify-between bg-gradient-to-r from-primary/5 to-transparent p-4 rounded-lg border border-primary/10">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Person Self Registration') }}
            </h2>
            <p class="text-gray-600 text-sm mt-1">Step
                {{ array_search($currentStep, ['basic_info', 'contact_info', 'document_info', 'affiliation_info']) + 1 }}
                of 4:
                @switch($currentStep)
                    @case('basic_info')
                        Personal Information
                    @break
                    @case('contact_info')
                        Contact Information
                    @break
                    @case('document_info')
                        Document & Identifiers
                    @break
                    @case('affiliation_info')
                        Organization Affiliations
                    @break
                @endswitch
            </p>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Tab Navigation --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="flex overflow-x-auto">
                        @php
                            $steps = ['basic_info', 'contact_info', 'document_info', 'affiliation_info'];
                            $stepLabels = ['Personal', 'Contact', 'Documents', 'Affiliations'];
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
                <form wire:submit.prevent="submit">
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
                                                {{ $error }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="p-2">
                        {{-- Step 1: Personal Information --}}
                        @if ($currentStep === 'basic_info')
                            <div class="space-y-2">
                                <h3 class="text-base font-medium text-gray-900 mb-2">Personal Information</h3>
                                <p class="text-gray-600 text-sm mb-4">Provide your basic personal details.</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Given Name <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="text" wire:model="form.given_name" class="input input-bordered input-sm w-full" placeholder="Enter given name">
                                        @error('form.given_name')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Middle Name</span>
                                        </label>
                                        <input type="text" wire:model="form.middle_name" class="input input-bordered input-sm w-full" placeholder="Enter middle name">
                                        @error('form.middle_name')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Family Name <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="text" wire:model="form.family_name" class="input input-bordered input-sm w-full" placeholder="Enter family name">
                                        @error('form.family_name')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Date of Birth</span>
                                        </label>
                                        <input type="date" wire:model="form.date_of_birth" class="input input-bordered input-sm w-full">
                                        @error('form.date_of_birth')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Gender</span>
                                        </label>
                                        <select wire:model="form.gender" class="select select-bordered select-sm w-full">
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        @error('form.gender')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Contact Information --}}
                        @if ($currentStep === 'contact_info')
                            <div class="space-y-2">
                                <h3 class="text-base font-medium text-gray-900 mb-2">Contact Information</h3>
                                <p class="text-gray-600 text-sm mb-4">Provide your contact and address details.</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Phone Number</span>
                                        </label>
                                        <input type="text" wire:model="form.phone" class="input input-bordered input-sm w-full" placeholder="Enter phone number">
                                        @error('form.phone')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Email Address</span>
                                        </label>
                                        <input type="email" wire:model="form.email" class="input input-bordered input-sm w-full" placeholder="Enter email address">
                                        @error('form.email')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control md:col-span-3">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Address</span>
                                        </label>
                                        <textarea wire:model="form.address" class="textarea textarea-bordered textarea-sm w-full h-16" placeholder="Street address, building, apartment"></textarea>
                                        @error('form.address')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Country</span>
                                        </label>
                                        <input type="text" wire:model="form.country" class="input input-bordered input-sm w-full" placeholder="Country">
                                        @error('form.country')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">District</span>
                                        </label>
                                        <input type="text" wire:model="form.district" class="input input-bordered input-sm w-full" placeholder="District">
                                        @error('form.district')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">City</span>
                                        </label>
                                        <input type="text" wire:model="form.city" class="input input-bordered input-sm w-full" placeholder="City">
                                        @error('form.city')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 3: Document & Identifiers --}}
                        @if ($currentStep === 'document_info')
                            <div class="space-y-2">
                                <h3 class="text-base font-medium text-gray-900 mb-2">Document & Identifiers</h3>
                                <p class="text-gray-600 text-sm mb-4">Upload your identification documents.</p>
                                @foreach($documents as $index => $doc)
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2 items-center">
                                        <div>
                                            <select wire:model="documents.{{ $index }}.type" class="input input-bordered input-sm w-full" required>
                                                <option value="">Select Document Type</option>
                                                <option value="Passport">Passport</option>
                                                <option value="Driver's License">Driver's License</option>
                                                <option value="National ID">National ID</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input type="file" wire:model="documents.{{ $index }}.file" class="input input-bordered input-sm w-full" required>
                                        </div>
                                        <div>
                                            <button type="button" wire:click="removeDocument({{ $index }})" class="btn btn-error btn-xs">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" wire:click="addDocument" class="btn btn-primary btn-sm">Add Another Document</button>
                            </div>
                        @endif

                        {{-- Step 4: Organization Affiliations --}}
                        @if ($currentStep === 'affiliation_info')
                            <div class="space-y-2">
                                <h3 class="text-base font-medium text-gray-900 mb-2">Organization Affiliations</h3>
                                <p class="text-gray-600 text-sm mb-4">Select your organization and role.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Organization <span class="text-red-500">*</span></span>
                                        </label>
                                        <select wire:model="form.organisation_id" class="input input-bordered input-sm w-full" required>
                                            <option value="">Select Organization</option>
                                            @foreach($availableOrganisations as $org)
                                                <option value="{{ $org->id }}">{{ $org->display_name ?? $org->legal_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.organisation_id')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label pb-1">
                                            <span class="label-text text-sm font-medium">Role Title <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="text" wire:model="form.role_title" class="input input-bordered input-sm w-full" placeholder="Role Title" required>
                                        @error('form.role_title')
                                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Navigation Buttons --}}
                    <div class="px-4 py-3 bg-gray-50 border-t flex justify-between">
                        @php
                            $steps = ['basic_info', 'contact_info', 'document_info', 'affiliation_info'];
                            $currentStepIndex = array_search($currentStep, $steps);
                        @endphp
                        <div>
                            @if ($currentStepIndex > 0)
                                <button type="button" wire:click="$set('currentStep', '{{ $steps[$currentStepIndex - 1] }}')" class="btn btn-ghost">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Previous
                                </button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            @if ($currentStepIndex < count($steps) - 1)
                                <button type="button" wire:click="$set('currentStep', '{{ $steps[$currentStepIndex + 1] }}')" class="btn btn-primary">
                                    Next
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            @else
                                <button type="submit" class="btn btn-success">
                                    Submit Registration
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            @if(session()->has('success'))
                <div class="alert alert-success mt-4">{{ session('success') }}</div>
            @endif
        </div>
    </div>
</div>
