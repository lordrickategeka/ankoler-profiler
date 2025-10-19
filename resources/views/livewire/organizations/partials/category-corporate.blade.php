{{-- Corporate-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Company Type <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.company_type" class="select select-bordered">
                <option value="">Select Company Type</option>
                <option value="LIMITED_LIABILITY">Limited Liability Company</option>
                <option value="PUBLIC_LIMITED">Public Limited Company</option>
                <option value="PRIVATE_LIMITED">Private Limited Company</option>
                <option value="PARTNERSHIP">Partnership</option>
                <option value="SOLE_PROPRIETORSHIP">Sole Proprietorship</option>
                <option value="COOPERATIVE">Cooperative</option>
                <option value="HOLDING_COMPANY">Holding Company</option>
                <option value="SUBSIDIARY">Subsidiary</option>
            </select>
            @error('categoryDetails.company_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Industry Sector <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.industry_sector" class="select select-bordered">
                <option value="">Select Industry</option>
                <option value="AGRICULTURE">Agriculture & Farming</option>
                <option value="MANUFACTURING">Manufacturing</option>
                <option value="TECHNOLOGY">Technology & IT</option>
                <option value="FINANCE">Finance & Banking</option>
                <option value="HEALTHCARE">Healthcare</option>
                <option value="EDUCATION">Education</option>
                <option value="RETAIL">Retail & Trade</option>
                <option value="CONSTRUCTION">Construction</option>
                <option value="TRANSPORT">Transport & Logistics</option>
                <option value="TELECOMMUNICATIONS">Telecommunications</option>
                <option value="ENERGY">Energy & Utilities</option>
                <option value="TOURISM">Tourism & Hospitality</option>
                <option value="MEDIA">Media & Entertainment</option>
                <option value="CONSULTING">Consulting Services</option>
                <option value="REAL_ESTATE">Real Estate</option>
                <option value="OTHER">Other</option>
            </select>
            @error('categoryDetails.industry_sector') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Business Registration Type</span>
            </label>
            <select wire:model="categoryDetails.business_registration_type" class="select select-bordered">
                <option value="">Select Registration</option>
                <option value="URSB">URSB Registered</option>
                <option value="URA">URA Registered</option>
                <option value="NSSF">NSSF Registered</option>
                <option value="LOCAL_GOVERNMENT">Local Government License</option>
                <option value="INTERNATIONAL">International Registration</option>
            </select>
            @error('categoryDetails.business_registration_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Employees</span>
            </label>
            <select wire:model="categoryDetails.number_of_employees" class="select select-bordered">
                <option value="">Select Employee Range</option>
                <option value="1-10">1-10 employees</option>
                <option value="11-50">11-50 employees</option>
                <option value="51-100">51-100 employees</option>
                <option value="101-250">101-250 employees</option>
                <option value="251-500">251-500 employees</option>
                <option value="501-1000">501-1000 employees</option>
                <option value="1000+">1000+ employees</option>
            </select>
            @error('categoryDetails.number_of_employees') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Annual Revenue Range (UGX)</span>
            </label>
            <select wire:model="categoryDetails.annual_revenue_range" class="select select-bordered">
                <option value="">Select Revenue Range</option>
                <option value="0-10M">0 - 10 Million</option>
                <option value="10M-50M">10 - 50 Million</option>
                <option value="50M-100M">50 - 100 Million</option>
                <option value="100M-500M">100 - 500 Million</option>
                <option value="500M-1B">500 Million - 1 Billion</option>
                <option value="1B-5B">1 - 5 Billion</option>
                <option value="5B+">5 Billion+</option>
            </select>
            @error('categoryDetails.annual_revenue_range') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">CEO/Managing Director</span>
            </label>
            <input type="text" wire:model="categoryDetails.ceo_name" class="input input-bordered"
                   placeholder="Chief Executive Officer name">
            @error('categoryDetails.ceo_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Company Secretary</span>
            </label>
            <input type="text" wire:model="categoryDetails.company_secretary" class="input input-bordered"
                   placeholder="Company Secretary name">
            @error('categoryDetails.company_secretary') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Parent Company</span>
            </label>
            <input type="text" wire:model="categoryDetails.parent_company" class="input input-bordered"
                   placeholder="Parent/holding company (if applicable)">
            @error('categoryDetails.parent_company') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Core Business Activities --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Core Business Activities</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $activities = [
                    'manufacturing' => 'Manufacturing',
                    'distribution' => 'Distribution',
                    'retail_sales' => 'Retail Sales',
                    'wholesale' => 'Wholesale',
                    'import_export' => 'Import/Export',
                    'services' => 'Service Provision',
                    'consulting' => 'Consulting',
                    'research_development' => 'Research & Development',
                    'software_development' => 'Software Development',
                    'marketing' => 'Marketing & Advertising',
                    'logistics' => 'Logistics',
                    'property_management' => 'Property Management',
                    'financial_services' => 'Financial Services',
                    'healthcare_services' => 'Healthcare Services',
                    'education_training' => 'Education & Training',
                    'agriculture' => 'Agricultural Activities'
                ];
            @endphp

            @foreach($activities as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.business_activities" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.business_activities') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Markets Served --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Markets Served</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $markets = [
                    'local' => 'Local Market',
                    'national' => 'National Market',
                    'regional' => 'Regional (East Africa)',
                    'continental' => 'Continental (Africa)',
                    'international' => 'International',
                    'b2b' => 'Business to Business (B2B)',
                    'b2c' => 'Business to Consumer (B2C)',
                    'government' => 'Government Sector',
                    'private_sector' => 'Private Sector',
                    'ngo_sector' => 'NGO Sector'
                ];
            @endphp

            @foreach($markets as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.markets_served" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.markets_served') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Certifications and Compliance --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Stock Exchange Listing</span>
            </label>
            <select wire:model="categoryDetails.stock_exchange" class="select select-bordered">
                <option value="">Not Listed</option>
                <option value="USE">Uganda Securities Exchange</option>
                <option value="NSE">Nairobi Securities Exchange</option>
                <option value="DSE">Dar es Salaam Stock Exchange</option>
                <option value="LSE">London Stock Exchange</option>
                <option value="NYSE">New York Stock Exchange</option>
                <option value="OTHER">Other Exchange</option>
            </select>
            @error('categoryDetails.stock_exchange') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Credit Rating</span>
            </label>
            <select wire:model="categoryDetails.credit_rating" class="select select-bordered">
                <option value="">Not Rated</option>
                <option value="AAA">AAA (Highest)</option>
                <option value="AA">AA (Very High)</option>
                <option value="A">A (High)</option>
                <option value="BBB">BBB (Good)</option>
                <option value="BB">BB (Speculative)</option>
                <option value="B">B (Highly Speculative)</option>
                <option value="CCC">CCC (Extremely Speculative)</option>
                <option value="D">D (Default)</option>
            </select>
            @error('categoryDetails.credit_rating') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Additional Company Information --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Company Mission & Vision</span>
        </label>
        <textarea wire:model="categoryDetails.mission_vision" class="textarea textarea-bordered h-24"
                  placeholder="Brief description of company mission, vision, and core values"></textarea>
        @error('categoryDetails.mission_vision') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Major Products/Services</span>
        </label>
        <textarea wire:model="categoryDetails.products_services" class="textarea textarea-bordered h-20"
                  placeholder="List major products or services offered by the company"></textarea>
        @error('categoryDetails.products_services') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
