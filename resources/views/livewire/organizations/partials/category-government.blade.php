{{-- Government Agency-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">- Government-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Government Level <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.government_level" class="select select-bordered">
                <option value="">Select Government Level</option>
                <option value="NATIONAL">National Government</option>
                <option value="REGIONAL">Regional Government</option>
                <option value="DISTRICT">District Local Government</option>
                <option value="MUNICIPAL">Municipal Council</option>
                <option value="TOWN_COUNCIL">Town Council</option>
                <option value="SUB_COUNTY">Sub County</option>
                <option value="PARISH">Parish</option>
                <option value="VILLAGE">Village</option>
                <option value="STATUTORY">Statutory Body</option>
                <option value="AUTHORITY">Government Authority</option>
            </select>
            @error('categoryDetails.government_level') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Ministry/Department <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.ministry_department" class="select select-bordered">
                <option value="">Select Ministry/Department</option>
                <option value="AGRICULTURE">Ministry of Agriculture</option>
                <option value="EDUCATION">Ministry of Education</option>
                <option value="HEALTH">Ministry of Health</option>
                <option value="FINANCE">Ministry of Finance</option>
                <option value="DEFENSE">Ministry of Defense</option>
                <option value="INTERNAL_AFFAIRS">Ministry of Internal Affairs</option>
                <option value="FOREIGN_AFFAIRS">Ministry of Foreign Affairs</option>
                <option value="JUSTICE">Ministry of Justice</option>
                <option value="TRADE">Ministry of Trade & Industry</option>
                <option value="TRANSPORT">Ministry of Transport</option>
                <option value="ENERGY">Ministry of Energy</option>
                <option value="WATER">Ministry of Water & Environment</option>
                <option value="LOCAL_GOVERNMENT">Ministry of Local Government</option>
                <option value="GENDER">Ministry of Gender & Social Development</option>
                <option value="YOUTH">Ministry of Youth & Sports</option>
                <option value="ICT">Ministry of ICT</option>
                <option value="TOURISM">Ministry of Tourism</option>
                <option value="SECURITY">Security Services</option>
                <option value="JUDICIARY">Judiciary</option>
                <option value="PARLIAMENT">Parliament</option>
                <option value="ELECTORAL">Electoral Commission</option>
                <option value="OTHER">Other</option>
            </select>
            @error('categoryDetails.ministry_department') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Head of Institution</span>
            </label>
            <input type="text" wire:model="categoryDetails.head_of_institution" class="input input-bordered"
                   placeholder="Minister, Permanent Secretary, Director, etc.">
            @error('categoryDetails.head_of_institution') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Institution Code</span>
            </label>
            <input type="text" wire:model="categoryDetails.institution_code" class="input input-bordered"
                   placeholder="Official government institution code">
            @error('categoryDetails.institution_code') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Budget Code</span>
            </label>
            <input type="text" wire:model="categoryDetails.budget_code" class="input input-bordered"
                   placeholder="Government budget allocation code">
            @error('categoryDetails.budget_code') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Annual Budget (UGX)</span>
            </label>
            <select wire:model="categoryDetails.annual_budget" class="select select-bordered">
                <option value="">Select Budget Range</option>
                <option value="0-100M">0 - 100 Million</option>
                <option value="100M-500M">100 - 500 Million</option>
                <option value="500M-1B">500 Million - 1 Billion</option>
                <option value="1B-5B">1 - 5 Billion</option>
                <option value="5B-10B">5 - 10 Billion</option>
                <option value="10B-50B">10 - 50 Billion</option>
                <option value="50B+">50 Billion+</option>
            </select>
            @error('categoryDetails.annual_budget') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Staff</span>
            </label>
            <select wire:model="categoryDetails.staff_count" class="select select-bordered">
                <option value="">Select Staff Range</option>
                <option value="1-50">1-50 staff</option>
                <option value="51-100">51-100 staff</option>
                <option value="101-500">101-500 staff</option>
                <option value="501-1000">501-1000 staff</option>
                <option value="1001-5000">1001-5000 staff</option>
                <option value="5000+">5000+ staff</option>
            </select>
            @error('categoryDetails.staff_count') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Establishment Date</span>
            </label>
            <input type="date" wire:model="categoryDetails.establishment_date" class="input input-bordered">
            @error('categoryDetails.establishment_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Core Functions --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Core Government Functions</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $functions = [
                    'policy_formulation' => 'Policy Formulation',
                    'regulation' => 'Regulation & Oversight',
                    'service_delivery' => 'Public Service Delivery',
                    'law_enforcement' => 'Law Enforcement',
                    'revenue_collection' => 'Revenue Collection',
                    'infrastructure' => 'Infrastructure Development',
                    'social_services' => 'Social Services',
                    'health_services' => 'Health Services',
                    'education_services' => 'Education Services',
                    'security' => 'Security Services',
                    'justice' => 'Justice Administration',
                    'licensing' => 'Licensing & Permits',
                    'planning' => 'Development Planning',
                    'monitoring' => 'Monitoring & Evaluation',
                    'research' => 'Research & Development',
                    'international_relations' => 'International Relations'
                ];
            @endphp

            @foreach($functions as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.core_functions" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.core_functions') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Geographic Coverage --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Geographic Coverage/Jurisdiction</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $coverage = [
                    'national' => 'National Coverage',
                    'central_region' => 'Central Region',
                    'eastern_region' => 'Eastern Region',
                    'northern_region' => 'Northern Region',
                    'western_region' => 'Western Region',
                    'kampala' => 'Kampala',
                    'multiple_districts' => 'Multiple Districts',
                    'single_district' => 'Single District',
                    'urban_areas' => 'Urban Areas',
                    'rural_areas' => 'Rural Areas',
                    'border_areas' => 'Border Areas',
                    'international' => 'International Scope'
                ];
            @endphp

            @foreach($coverage as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.geographic_coverage" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.geographic_coverage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Additional Government Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Reporting Institution</span>
            </label>
            <input type="text" wire:model="categoryDetails.reporting_institution" class="input input-bordered"
                   placeholder="Institution this entity reports to">
            @error('categoryDetails.reporting_institution') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Funding Source</span>
            </label>
            <select wire:model="categoryDetails.funding_source" class="select select-bordered">
                <option value="">Select Funding Source</option>
                <option value="GOVERNMENT_BUDGET">Government Budget</option>
                <option value="DONOR_FUNDED">Donor Funded</option>
                <option value="SELF_FUNDED">Self Funded</option>
                <option value="MIXED_FUNDING">Mixed Funding</option>
                <option value="PUBLIC_PRIVATE">Public-Private Partnership</option>
            </select>
            @error('categoryDetails.funding_source') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Mandate and Objectives --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Institutional Mandate</span>
        </label>
        <textarea wire:model="categoryDetails.institutional_mandate" class="textarea textarea-bordered h-24"
                  placeholder="Describe the official mandate, mission and objectives of this government institution"></textarea>
        @error('categoryDetails.institutional_mandate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Key Performance Indicators</span>
        </label>
        <textarea wire:model="categoryDetails.key_performance_indicators" class="textarea textarea-bordered h-20"
                  placeholder="List key performance indicators and targets for this institution"></textarea>
        @error('categoryDetails.key_performance_indicators') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
