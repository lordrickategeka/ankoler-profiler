<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Import Persons</h2>
            <p class="text-gray-600">
                @if ($isSuperAdmin)
                    Bulk import persons into selected organization
                @else
                    Bulk import persons and automatically affiliate them to
                    {{ $currentOrganisation->display_name ?? ($currentOrganisation->legal_name ?? 'your organization') }}
                @endif
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <button wire:click="downloadTemplate" class="btn btn-outline btn-success">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Download Excel Template
            </button>
            @if ($currentOrganisation)
                <span class="text-xs text-base-content/60">
                    {{ ucfirst($currentOrganisation->category) }} template for
                    {{ $currentOrganisation->display_name ?? $currentOrganisation->legal_name }}
                </span>
            @endif
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

    <!-- Import Validation Errors -->
    @error('import')
        <div class="alert alert-error mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="font-bold">Import Error</h4>
                <pre class="text-sm whitespace-pre-wrap">{{ $message }}</pre>
            </div>
        </div>
    @enderror

    <!-- Import Configuration -->
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">Import Configuration</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Organization Selection (Super Admin only) -->
                @if ($isSuperAdmin)
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Target Organization <span
                                    class="text-red-500">*</span></span>
                        </label>
                        <select wire:model.live="selectedOrganisationId" class="select select-bordered">
                            <option value="">Select Organization</option>
                            @foreach ($availableOrganisations as $org)
                                <option value="{{ $org['id'] }}">{{ $org['display_name'] ?? $org['legal_name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedOrganisationId')
                            <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @else
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Target Organization</span>
                        </label>
                        <input type="text"
                            value="{{ $currentOrganisation->display_name ?? ($currentOrganisation->legal_name ?? 'Current Organization') }}"
                            class="input input-bordered" readonly>
                        <div class="alert alert-info mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm">All imported persons will be automatically affiliated to your organization. Use the role_type column in your Excel file to specify individual roles, or leave empty to use the fallback role type.</span>
                        </div>
                    </div>
                @endif

                <!-- Default Role Type -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Fallback Role Type</span>
                    </label>
                    <select wire:model="defaultRoleType" class="select select-bordered">
                        @foreach ($availableRoles as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="label">
                        <span class="label-text-alt">Used only when role_type column is empty or missing in Excel</span>
                    </div>
                    @error('defaultRoleType')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Import Options -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Duplicate Handling</span>
                    </label>
                    <div class="space-y-2">
                        <label class="label cursor-pointer">
                            <span class="label-text">Skip duplicates (keep existing records)</span>
                            <input type="checkbox" wire:model="skipDuplicates" class="checkbox checkbox-primary">
                        </label>
                        <label class="label cursor-pointer">
                            <span class="label-text">Update existing records with new data</span>
                            <input type="checkbox" wire:model="updateExisting" class="checkbox checkbox-primary">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Upload -->
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">Upload File</h3>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Select CSV or Excel File <span
                            class="text-red-500">*</span></span>
                </label>
                <input type="file" wire:model="importFile" accept=".csv,.xlsx,.xls"
                    class="file-input file-input-bordered file-input-primary w-full">
                @error('importFile')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                @enderror

                <div class="label">
                    <span class="label-text-alt">Supported formats: CSV, Excel (.xlsx, .xls). Max size: 10MB</span>
                </div>
            </div>

            @if ($importFile)
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-blue-900">{{ $importFile->getClientOriginalName() }}</p>
                            <p class="text-sm text-blue-700">{{ number_format($importFile->getSize() / 1024, 1) }} KB
                            </p>
                        </div>
                        <button wire:click="resetImport" class="btn btn-sm btn-ghost text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Loading Preview Indicator -->
                <div wire:loading wire:target="importFile" class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex items-center">
                        <span class="loading loading-spinner loading-sm text-yellow-600 mr-3"></span>
                        <div>
                            <p class="font-medium text-yellow-800">Loading file preview...</p>
                            <p class="text-sm text-yellow-700">Analyzing your file and validating data structure</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- File Preview -->
    @if ($showPreview || $importFile)
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">File Preview</h3>

                <!-- Loading state for preview generation -->
                <div wire:loading wire:target="importFile" class="text-center py-8">
                    <div class="flex flex-col items-center justify-center space-y-4">
                        <span class="loading loading-ring loading-lg text-primary"></span>
                        <div class="text-center">
                            <p class="font-medium text-gray-700">Generating preview...</p>
                            <p class="text-sm text-gray-500">This may take a moment for large files</p>
                        </div>
                    </div>
                </div>

                <!-- Preview content (hidden while loading) -->
                <div wire:loading.remove wire:target="importFile">
                    @if (!empty($validationErrors))
                        <div class="alert alert-warning mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                            <div>
                                <h4 class="font-bold">Validation Issues Found:</h4>
                                <ul class="list-disc list-inside">
                                    @foreach ($validationErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (!empty($previewData))
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_slice($previewData, 0, 10) as $index => $row)
                                        <tr>
                                            <td>{{ $index + 3 }}</td> <!-- Updated to account for header + description rows -->
                                            <td>
                                                {{ $row['given_name'] ?? '' }} {{ $row['family_name'] ?? '' }}
                                            </td>
                                            <td>{{ $row['email'] ?? '' }}</td>
                                            <td>{{ $row['phone'] ?? '' }}</td>
                                            <td>{{ $row['role_type'] ?? $defaultRoleType }}</td>
                                            <td>
                                                @if (isset($row['_validation_errors']) && !empty($row['_validation_errors']))
                                                    <div class="badge badge-error">Errors</div>
                                                @else
                                                    <div class="badge badge-success">Valid</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (count($previewData) > 10)
                            <p class="text-sm text-gray-500 mt-2">
                                Showing first 10 rows. Total rows: {{ count($previewData) }}
                            </p>
                        @endif

                        <div class="card-actions justify-end mt-4">
                            <button wire:click="resetImport" class="btn btn-ghost">Cancel</button>
                            <button wire:click="importPersons" class="btn btn-primary"
                                @if ($isProcessing || !empty($validationErrors)) disabled @endif>
                                @if ($isProcessing)
                                    <span class="loading loading-spinner loading-sm"></span>
                                    Processing...
                                @else
                                    Import {{ count($previewData) }} Persons
                                @endif
                            </button>
                        </div>
                    @elseif ($showPreview)
                        <div class="alert alert-warning">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                            <div>
                                <h4 class="font-bold">No data found</h4>
                                <p>The file appears to be empty or contains only headers. Please check your file and try again.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Import Results -->
    @if ($showResults && ($importResults || !empty($validationErrors)))
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">Import Results</h3>

                <!-- Validation Errors Section -->
                @if (!empty($validationErrors) && empty($importResults))
                    <div class="alert alert-error mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-bold">Import Failed Due to Validation Errors</h4>
                            <p>Please fix the following errors in your file and try again:</p>
                        </div>
                    </div>

                    <div class="collapse collapse-arrow bg-red-50 border border-red-200">
                        <input type="checkbox" checked />
                        <div class="collapse-title text-xl font-medium text-red-800">
                            View All Validation Errors ({{ count($validationErrors) }})
                        </div>
                        <div class="collapse-content">
                            <div class="overflow-x-auto">
                                <table class="table table-zebra w-full">
                                    <thead>
                                        <tr>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($validationErrors as $error)
                                            <tr>
                                                <td class="font-mono text-sm">{{ $error }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Summary (only show if import actually ran) -->
                @if ($importResults)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="stat bg-green-50 rounded-lg">
                            <div class="stat-title text-green-700">Successful</div>
                            <div class="stat-value text-green-900">{{ $importResults['summary']['success'] ?? 0 }}</div>
                        </div>
                        <div class="stat bg-yellow-50 rounded-lg">
                            <div class="stat-title text-yellow-700">Skipped</div>
                            <div class="stat-value text-yellow-900">{{ $importResults['summary']['skipped'] ?? 0 }}</div>
                        </div>
                        <div class="stat bg-red-50 rounded-lg">
                            <div class="stat-title text-red-700">Failed</div>
                            <div class="stat-value text-red-900">{{ $importResults['summary']['failed'] ?? 0 }}</div>
                        </div>
                        <div class="stat bg-blue-50 rounded-lg">
                            <div class="stat-title text-blue-700">Total</div>
                            <div class="stat-value text-blue-900">{{ $importResults['summary']['total'] ?? 0 }}</div>
                        </div>
                    </div>

                    <!-- Detailed Results -->
                    @if (!empty($importResults['details']))
                        <div class="collapse collapse-arrow bg-base-200">
                            <input type="checkbox" />
                            <div class="collapse-title text-xl font-medium">
                                View Detailed Results
                            </div>
                            <div class="collapse-content">
                                <div class="overflow-x-auto">
                                    <table class="table table-zebra w-full">
                                        <thead>
                                            <tr>
                                                <th>Row</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($importResults['details'] as $detail)
                                                <tr>
                                                    <td>{{ $detail['row'] ?? '' }}</td>
                                                    <td>{{ $detail['name'] ?? '' }}</td>
                                                    <td>
                                                        <div
                                                            class="badge
                                                            @if ($detail['status'] === 'success') badge-success
                                                            @elseif($detail['status'] === 'skipped') badge-warning
                                                            @else badge-error @endif">
                                                            {{ ucfirst($detail['status']) }}
                                                        </div>
                                                    </td>
                                                    <td>{{ $detail['message'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="card-actions justify-end mt-4">
                    <button wire:click="resetImport" class="btn btn-primary">Import Another File</button>
                    <a href="{{ route('persons.all') }}" class="btn btn-success">View All Persons</a>
                </div>
            </div>
        </div>
    @endif

    <!-- Processing Overlay -->
    @if ($isProcessing)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 flex items-center">
                <span class="loading loading-spinner loading-lg text-primary mr-4"></span>
                <div>
                    <p class="font-medium">Processing import...</p>
                    <p class="text-sm text-gray-500">Please wait while we process your file</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Template Information -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">
                Template Information
                @if ($currentOrganisation)
                    <span class="badge badge-primary ml-2">{{ ucfirst($currentOrganisation->category) }}
                        Organization</span>
                @endif
            </h3>

            <div class="alert alert-info mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="font-bold">Required Columns:</h4>
                    <p><strong>given_name, family_name</strong> - Minimum required fields for all imports</p>

                    <h4 class="font-bold mt-3">Base Optional Columns:</h4>
                    <p>middle_name, date_of_birth, gender, phone, email, national_id, address, city, district, country,
                        role_title, start_date</p>

                    <h4 class="font-bold mt-3">Role Type Column:</h4>
                    <p><strong>role_type</strong> - Specify the role for each person in your Excel file.
                    @if ($currentOrganisation)
                        Valid options for {{ $currentOrganisation->category }} organizations:
                        <span class="font-mono text-sm bg-base-200 px-2 py-1 rounded">{{ implode(', ', array_keys($availableRoles)) }}</span>
                    @endif
                    If not specified, defaults to <span class="font-mono text-sm bg-base-200 px-2 py-1 rounded">{{ $defaultRoleType }}</span>.</p>

                    @if ($currentOrganisation)
                        <h4 class="font-bold mt-3">{{ ucfirst($currentOrganisation->category) }}-Specific Columns:
                        </h4>
                        @switch($currentOrganisation->category)
                            @case('hospital')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="font-semibold text-blue-600">For Patients:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>patient_number</li>
                                            <li>medical_record_number</li>
                                            <li>allergies</li>
                                            <li>chronic_conditions</li>
                                            <li>emergency_contact_name</li>
                                            <li>emergency_contact_phone</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-600">For Medical Staff:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>employee_number</li>
                                            <li>department</li>
                                            <li>position</li>
                                            <li>license_number</li>
                                            <li>specialization</li>
                                        </ul>
                                    </div>
                                </div>
                            @break

                            @case('school')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="font-semibold text-blue-600">For Students:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>student_number</li>
                                            <li>enrollment_date</li>
                                            <li>current_class</li>
                                            <li>guardian_name</li>
                                            <li>guardian_phone</li>
                                            <li>guardian_email</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-600">For Teaching Staff:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>employee_number</li>
                                            <li>department</li>
                                            <li>position</li>
                                            <li>teaching_subjects</li>
                                            <li>qualifications</li>
                                        </ul>
                                    </div>
                                </div>
                            @break

                            @case('sacco')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="font-semibold text-blue-600">For Members:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>membership_number</li>
                                            <li>join_date</li>
                                            <li>share_capital</li>
                                            <li>savings_account_ref</li>
                                            <li>next_of_kin_name</li>
                                            <li>next_of_kin_phone</li>
                                            <li>occupation</li>
                                            <li>monthly_income</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-600">For Staff:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>employee_number</li>
                                            <li>position</li>
                                        </ul>
                                    </div>
                                </div>
                            @break

                            @case('parish')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="font-semibold text-blue-600">For Members:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>member_number</li>
                                            <li>baptism_date</li>
                                            <li>confirmation_date</li>
                                            <li>church_group</li>
                                            <li>marital_status</li>
                                            <li>spouse_name</li>
                                            <li>children_count</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-600">For Clergy/Staff:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            <li>position</li>
                                            <li>ordination_date</li>
                                        </ul>
                                    </div>
                                </div>
                            @break

                            @default
                                <div class="mt-2">
                                    <p class="font-semibold text-green-600">For Employees:</p>
                                    <ul class="list-disc list-inside text-sm">
                                        <li>employee_number, department, position</li>
                                        <li>hire_date, salary, supervisor_name</li>
                                        <li>work_location</li>
                                    </ul>
                                </div>
                        @endswitch
                    @endif

                    <h4 class="font-bold mt-4">Important Notes:</h4>
                    <ul class="list-disc list-inside">
                        <li>Date format: YYYY-MM-DD (e.g., 1990-05-15)</li>
                        <li>Gender: male, female, other, prefer_not_to_say</li>
                        <li>Phone: Include country code (e.g., +256701234567)</li>
                        <li>Role type will default to "{{ $availableRoles[$defaultRoleType] ?? 'Staff Member' }}" if
                            not specified</li>
                        <li>All persons will be affiliated with
                            {{ $currentOrganisation->display_name ?? ($currentOrganisation->legal_name ?? 'current organization') }}
                        </li>
                        <li><strong>Download the template above to get a file with sample data and proper column headers
                                for your organization type</strong></li>
                    </ul>
                </div>
            </div>

            @if ($currentOrganisation)
                <div class="alert alert-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-bold">Customized Template Available</h4>
                        <p>The template download will include all relevant fields for a
                            <strong>{{ ucfirst($currentOrganisation->category) }}</strong> organization, with sample
                            data and field descriptions.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
