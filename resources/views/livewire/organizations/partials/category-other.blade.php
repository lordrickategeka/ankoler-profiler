{{-- Other Organization Type Details --}}
{{-- Other Organization-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Organization Sub-Type <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.organization_sub_type" class="select select-bordered">
                <option value="">Select Sub-Type</option>
                <option value="PROFESSIONAL_ASSOCIATION">Professional Association</option>
                <option value="TRADE_UNION">Trade Union</option>
                <option value="COOPERATIVE_UNION">Cooperative Union</option>
                <option value="CULTURAL_ORGANIZATION">Cultural Organization</option>
                <option value="SPORTS_CLUB">Sports Club</option>
                <option value="SOCIAL_CLUB">Social Club</option>
                <option value="RESEARCH_INSTITUTE">Research Institute</option>
                <option value="THINK_TANK">Think Tank</option>
                <option value="MEDIA_ORGANIZATION">Media Organization</option>
                <option value="ARTS_ORGANIZATION">Arts Organization</option>
                <option value="ENVIRONMENTAL_GROUP">Environmental Group</option>
                <option value="COMMUNITY_GROUP">Community Group</option>
                <option value="ADVOCACY_GROUP">Advocacy Group</option>
                <option value="FOUNDATION">Foundation/Trust</option>
                <option value="INTERNATIONAL_ORG">International Organization</option>
                <option value="MULTILATERAL_ORG">Multilateral Organization</option>
                <option value="DIPLOMATIC_MISSION">Diplomatic Mission</option>
                <option value="OTHER">Other</option>
            </select>
            @error('categoryDetails.organization_sub_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Primary Sector/Industry</span>
            </label>
            <select wire:model="categoryDetails.primary_sector" class="select select-bordered">
                <option value="">Select Sector</option>
                <option value="AGRICULTURE">Agriculture</option>
                <option value="TECHNOLOGY">Technology</option>
                <option value="FINANCE">Finance</option>
                <option value="HEALTHCARE">Healthcare</option>
                <option value="EDUCATION">Education</option>
                <option value="MANUFACTURING">Manufacturing</option>
                <option value="SERVICES">Services</option>
                <option value="RETAIL">Retail</option>
                <option value="CONSTRUCTION">Construction</option>
                <option value="TRANSPORT">Transport</option>
                <option value="TOURISM">Tourism</option>
                <option value="MEDIA">Media</option>
                <option value="ARTS_CULTURE">Arts & Culture</option>
                <option value="SPORTS">Sports</option>
                <option value="ENVIRONMENT">Environment</option>
                <option value="RESEARCH">Research</option>
                <option value="ADVOCACY">Advocacy</option>
                <option value="INTERNATIONAL_RELATIONS">International Relations</option>
                <option value="MULTIPLE">Multiple Sectors</option>
            </select>
            @error('categoryDetails.primary_sector') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Head/Leader Title</span>
            </label>
            <input type="text" wire:model="categoryDetails.leader_title" class="input input-bordered"
                   placeholder="e.g., President, Chairman, Director, Secretary General">
            @error('categoryDetails.leader_title') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Head/Leader Name</span>
            </label>
            <input type="text" wire:model="categoryDetails.leader_name" class="input input-bordered"
                   placeholder="Name of the organization leader">
            @error('categoryDetails.leader_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Membership Type</span>
            </label>
            <select wire:model="categoryDetails.membership_type" class="select select-bordered">
                <option value="">Select Membership Type</option>
                <option value="OPEN">Open Membership</option>
                <option value="RESTRICTED">Restricted Membership</option>
                <option value="INVITATION_ONLY">Invitation Only</option>
                <option value="PROFESSIONAL">Professional Qualification Required</option>
                <option value="GEOGRAPHIC">Geographic Restriction</option>
                <option value="NO_MEMBERSHIP">No Membership Structure</option>
            </select>
            @error('categoryDetails.membership_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Number of Members</span>
            </label>
            <input type="number" wire:model="categoryDetails.member_count" class="input input-bordered"
                   placeholder="Total number of members" min="0">
            @error('categoryDetails.member_count') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Organization Size</span>
            </label>
            <select wire:model="categoryDetails.organization_size" class="select select-bordered">
                <option value="">Select Size</option>
                <option value="SMALL">Small (1-20 people)</option>
                <option value="MEDIUM">Medium (21-100 people)</option>
                <option value="LARGE">Large (101-500 people)</option>
                <option value="VERY_LARGE">Very Large (500+ people)</option>
            </select>
            @error('categoryDetails.organization_size') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Operating Budget Range (UGX)</span>
            </label>
            <select wire:model="categoryDetails.budget_range" class="select select-bordered">
                <option value="">Select Budget Range</option>
                <option value="0-1M">0 - 1 Million</option>
                <option value="1M-10M">1 - 10 Million</option>
                <option value="10M-50M">10 - 50 Million</option>
                <option value="50M-100M">50 - 100 Million</option>
                <option value="100M-500M">100 - 500 Million</option>
                <option value="500M+">500 Million+</option>
            </select>
            @error('categoryDetails.budget_range') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Core Activities --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Core Activities & Functions</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $activities = [
                    'advocacy' => 'Advocacy & Lobbying',
                    'research' => 'Research & Studies',
                    'policy_development' => 'Policy Development',
                    'capacity_building' => 'Capacity Building',
                    'training' => 'Training & Education',
                    'networking' => 'Networking & Partnerships',
                    'resource_mobilization' => 'Resource Mobilization',
                    'information_sharing' => 'Information Sharing',
                    'coordination' => 'Coordination & Collaboration',
                    'standard_setting' => 'Standard Setting',
                    'certification' => 'Certification Services',
                    'monitoring' => 'Monitoring & Evaluation',
                    'representation' => 'Member Representation',
                    'service_provision' => 'Service Provision',
                    'event_organization' => 'Event Organization',
                    'publication' => 'Publications',
                    'awards_recognition' => 'Awards & Recognition',
                    'fundraising' => 'Fundraising'
                ];
            @endphp

            @foreach($activities as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.core_activities" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.core_activities') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Target Audience --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Target Audience/Beneficiaries</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $audiences = [
                    'general_public' => 'General Public',
                    'professionals' => 'Professionals',
                    'students' => 'Students',
                    'researchers' => 'Researchers',
                    'policy_makers' => 'Policy Makers',
                    'government_officials' => 'Government Officials',
                    'business_community' => 'Business Community',
                    'civil_society' => 'Civil Society',
                    'international_community' => 'International Community',
                    'media' => 'Media',
                    'academia' => 'Academia',
                    'donors' => 'Donors & Funders',
                    'youth' => 'Youth',
                    'women' => 'Women',
                    'marginalized_groups' => 'Marginalized Groups',
                    'rural_communities' => 'Rural Communities',
                    'urban_communities' => 'Urban Communities',
                    'diaspora' => 'Diaspora Communities'
                ];
            @endphp

            @foreach($audiences as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.target_audience" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.target_audience') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Additional Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Legal Status</span>
            </label>
            <select wire:model="categoryDetails.legal_status" class="select select-bordered">
                <option value="">Select Legal Status</option>
                <option value="REGISTERED">Registered Organization</option>
                <option value="INCORPORATED">Incorporated</option>
                <option value="UNREGISTERED">Unregistered Group</option>
                <option value="INTERNATIONAL">International Legal Status</option>
                <option value="GOVERNMENT_RECOGNIZED">Government Recognized</option>
            </select>
            @error('categoryDetails.legal_status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Funding Sources</span>
            </label>
            <select wire:model="categoryDetails.funding_sources" class="select select-bordered" multiple>
                <option value="MEMBERSHIP_FEES">Membership Fees</option>
                <option value="DONATIONS">Donations</option>
                <option value="GRANTS">Grants</option>
                <option value="GOVERNMENT_FUNDING">Government Funding</option>
                <option value="CORPORATE_SPONSORSHIP">Corporate Sponsorship</option>
                <option value="FUNDRAISING_EVENTS">Fundraising Events</option>
                <option value="SERVICE_FEES">Service Fees</option>
                <option value="INVESTMENTS">Investment Income</option>
                <option value="PARTNERSHIPS">Partnership Revenue</option>
            </select>
            @error('categoryDetails.funding_sources') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Mission and Objectives --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Mission & Objectives</span>
        </label>
        <textarea wire:model="categoryDetails.mission_objectives" class="textarea textarea-bordered h-24"
                  placeholder="Describe the organization's mission, vision, and key objectives"></textarea>
        @error('categoryDetails.mission_objectives') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Key Programs & Services</span>
        </label>
        <textarea wire:model="categoryDetails.key_programs" class="textarea textarea-bordered h-20"
                  placeholder="List the main programs, services, or initiatives offered by the organization"></textarea>
        @error('categoryDetails.key_programs') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Notable Achievements</span>
        </label>
        <textarea wire:model="categoryDetails.achievements" class="textarea textarea-bordered h-20"
                  placeholder="Major achievements, awards, recognition, impact metrics, or success stories"></textarea>
        @error('categoryDetails.achievements') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
