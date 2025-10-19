{{-- NGO-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">NGO Type <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.ngo_type" class="select select-bordered">
                <option value="">Select NGO Type</option>
                <option value="INTERNATIONAL">International NGO</option>
                <option value="NATIONAL">National NGO</option>
                <option value="LOCAL">Local NGO</option>
                <option value="COMMUNITY_BASED">Community-Based Organization</option>
                <option value="FAITH_BASED">Faith-Based Organization</option>
                <option value="ADVOCACY">Advocacy Organization</option>
                <option value="SERVICE_DELIVERY">Service Delivery NGO</option>
                <option value="RESEARCH">Research Organization</option>
                <option value="NETWORK">Network/Umbrella Organization</option>
            </select>
            @error('categoryDetails.ngo_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Primary Focus Area <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.primary_focus_area" class="select select-bordered">
                <option value="">Select Focus Area</option>
                <option value="HEALTH">Health & Medical</option>
                <option value="EDUCATION">Education</option>
                <option value="POVERTY_ALLEVIATION">Poverty Alleviation</option>
                <option value="AGRICULTURE">Agriculture & Food Security</option>
                <option value="ENVIRONMENT">Environment & Conservation</option>
                <option value="HUMAN_RIGHTS">Human Rights</option>
                <option value="WOMEN_EMPOWERMENT">Women's Empowerment</option>
                <option value="CHILD_PROTECTION">Child Protection</option>
                <option value="DISABILITY">Disability Support</option>
                <option value="ELDERLY_CARE">Elderly Care</option>
                <option value="WATER_SANITATION">Water & Sanitation</option>
                <option value="GOVERNANCE">Governance & Democracy</option>
                <option value="EMERGENCY_RELIEF">Emergency Relief</option>
                <option value="ECONOMIC_DEVELOPMENT">Economic Development</option>
                <option value="YOUTH_DEVELOPMENT">Youth Development</option>
                <option value="COMMUNITY_DEVELOPMENT">Community Development</option>
            </select>
            @error('categoryDetails.primary_focus_area') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Executive Director/CEO</span>
            </label>
            <input type="text" wire:model="categoryDetails.executive_director" class="input input-bordered"
                   placeholder="Executive Director name">
            @error('categoryDetails.executive_director') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Program Manager</span>
            </label>
            <input type="text" wire:model="categoryDetails.program_manager" class="input input-bordered"
                   placeholder="Program Manager name">
            @error('categoryDetails.program_manager') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Target Beneficiaries</span>
            </label>
            <input type="number" wire:model="categoryDetails.target_beneficiaries" class="input input-bordered"
                   placeholder="Number of people served" min="0">
            @error('categoryDetails.target_beneficiaries') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Staff Size</span>
            </label>
            <select wire:model="categoryDetails.staff_size" class="select select-bordered">
                <option value="">Select Staff Size</option>
                <option value="1-5">1-5 staff</option>
                <option value="6-10">6-10 staff</option>
                <option value="11-25">11-25 staff</option>
                <option value="26-50">26-50 staff</option>
                <option value="51-100">51-100 staff</option>
                <option value="100+">100+ staff</option>
            </select>
            @error('categoryDetails.staff_size') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Volunteer Count</span>
            </label>
            <input type="number" wire:model="categoryDetails.volunteer_count" class="input input-bordered"
                   placeholder="Number of active volunteers" min="0">
            @error('categoryDetails.volunteer_count') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Annual Budget (UGX)</span>
            </label>
            <select wire:model="categoryDetails.annual_budget" class="select select-bordered">
                <option value="">Select Budget Range</option>
                <option value="0-10M">0 - 10 Million</option>
                <option value="10M-50M">10 - 50 Million</option>
                <option value="50M-100M">50 - 100 Million</option>
                <option value="100M-500M">100 - 500 Million</option>
                <option value="500M-1B">500 Million - 1 Billion</option>
                <option value="1B+">1 Billion+</option>
            </select>
            @error('categoryDetails.annual_budget') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Program Areas --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Program Areas</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $programs = [
                    'health_programs' => 'Health Programs',
                    'education_programs' => 'Education Programs',
                    'livelihood_programs' => 'Livelihood Programs',
                    'capacity_building' => 'Capacity Building',
                    'advocacy_campaigns' => 'Advocacy Campaigns',
                    'research_studies' => 'Research Studies',
                    'emergency_response' => 'Emergency Response',
                    'community_mobilization' => 'Community Mobilization',
                    'infrastructure_development' => 'Infrastructure Development',
                    'microfinance' => 'Microfinance',
                    'skills_training' => 'Skills Training',
                    'awareness_campaigns' => 'Awareness Campaigns',
                    'policy_advocacy' => 'Policy Advocacy',
                    'monitoring_evaluation' => 'Monitoring & Evaluation',
                    'networking' => 'Networking & Partnerships',
                    'resource_mobilization' => 'Resource Mobilization'
                ];
            @endphp

            @foreach($programs as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.program_areas" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.program_areas') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Target Groups --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Target Groups/Beneficiaries</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $targetGroups = [
                    'children' => 'Children',
                    'youth' => 'Youth',
                    'women' => 'Women',
                    'elderly' => 'Elderly',
                    'men' => 'Men',
                    'people_with_disabilities' => 'People with Disabilities',
                    'refugees' => 'Refugees',
                    'internally_displaced' => 'Internally Displaced Persons',
                    'rural_communities' => 'Rural Communities',
                    'urban_poor' => 'Urban Poor',
                    'farmers' => 'Farmers',
                    'students' => 'Students',
                    'healthcare_workers' => 'Healthcare Workers',
                    'government_officials' => 'Government Officials',
                    'civil_society' => 'Civil Society Organizations',
                    'private_sector' => 'Private Sector'
                ];
            @endphp

            @foreach($targetGroups as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.target_groups" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.target_groups') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Funding Sources --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Funding Sources</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $fundingSources = [
                    'government_grants' => 'Government Grants',
                    'international_donors' => 'International Donors',
                    'bilateral_donors' => 'Bilateral Donors',
                    'multilateral_donors' => 'Multilateral Donors',
                    'foundations' => 'Private Foundations',
                    'corporate_sponsorship' => 'Corporate Sponsorship',
                    'individual_donations' => 'Individual Donations',
                    'membership_fees' => 'Membership Fees',
                    'fundraising_events' => 'Fundraising Events',
                    'service_fees' => 'Service Fees',
                    'investment_income' => 'Investment Income',
                    'crowdfunding' => 'Crowdfunding'
                ];
            @endphp

            @foreach($fundingSources as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.funding_sources" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.funding_sources') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Geographic Coverage --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Geographic Coverage</span>
            </label>
            <select wire:model="categoryDetails.geographic_coverage" class="select select-bordered">
                <option value="">Select Coverage</option>
                <option value="VILLAGE">Village Level</option>
                <option value="PARISH">Parish Level</option>
                <option value="SUB_COUNTY">Sub County Level</option>
                <option value="DISTRICT">District Level</option>
                <option value="REGIONAL">Regional Level</option>
                <option value="NATIONAL">National Level</option>
                <option value="INTERNATIONAL">International</option>
            </select>
            @error('categoryDetails.geographic_coverage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Parent/Network Organization</span>
            </label>
            <input type="text" wire:model="categoryDetails.parent_organization" class="input input-bordered"
                   placeholder="Parent or network organization (if applicable)">
            @error('categoryDetails.parent_organization') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Mission and Impact --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Mission Statement</span>
        </label>
        <textarea wire:model="categoryDetails.mission_statement" class="textarea textarea-bordered h-24"
                  placeholder="Organization's mission statement and core objectives"></textarea>
        @error('categoryDetails.mission_statement') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Key Achievements & Impact</span>
        </label>
        <textarea wire:model="categoryDetails.key_achievements" class="textarea textarea-bordered h-20"
                  placeholder="Major achievements, impact metrics, success stories, awards, recognition"></textarea>
        @error('categoryDetails.key_achievements') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
