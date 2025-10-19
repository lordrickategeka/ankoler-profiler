{{-- SACCO-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">SACCO Type <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.sacco_type" class="select select-bordered">
                <option value="">Select SACCO Type</option>
                <option value="COMMUNITY">Community-based SACCO</option>
                <option value="WORKPLACE">Workplace SACCO</option>
                <option value="FARMERS">Farmers SACCO</option>
                <option value="TRADERS">Traders SACCO</option>
                <option value="TRANSPORT">Transport SACCO</option>
                <option value="WOMEN">Women's SACCO</option>
                <option value="YOUTH">Youth SACCO</option>
                <option value="GENERAL">General SACCO</option>
            </select>
            @error('categoryDetails.sacco_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Member Capacity <span class="text-red-500">*</span></span>
            </label>
            <input type="number" wire:model="categoryDetails.member_capacity" class="input input-bordered"
                   placeholder="Maximum number of members" min="1">
            @error('categoryDetails.member_capacity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Current Membership</span>
            </label>
            <input type="number" wire:model="categoryDetails.current_membership" class="input input-bordered"
                   placeholder="Current number of members" min="0">
            @error('categoryDetails.current_membership') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Minimum Share Capital</span>
            </label>
            <input type="number" wire:model="categoryDetails.minimum_share_capital" class="input input-bordered"
                   placeholder="Minimum amount in UGX" min="0">
            @error('categoryDetails.minimum_share_capital') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Share Price (UGX)</span>
            </label>
            <input type="number" wire:model="categoryDetails.share_price" class="input input-bordered"
                   placeholder="Price per share in UGX" min="0">
            @error('categoryDetails.share_price') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Maximum Loan Amount</span>
            </label>
            <input type="number" wire:model="categoryDetails.maximum_loan_amount" class="input input-bordered"
                   placeholder="Maximum loan in UGX" min="0">
            @error('categoryDetails.maximum_loan_amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Interest Rate (%)</span>
            </label>
            <input type="number" step="0.1" wire:model="categoryDetails.interest_rate" class="input input-bordered"
                   placeholder="Annual interest rate" min="0" max="100">
            @error('categoryDetails.interest_rate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Manager Name</span>
            </label>
            <input type="text" wire:model="categoryDetails.manager_name" class="input input-bordered"
                   placeholder="SACCO Manager name">
            @error('categoryDetails.manager_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Services Offered --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Services Offered</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $services = [
                    'savings' => 'Savings Accounts',
                    'loans' => 'Credit/Loans',
                    'insurance' => 'Insurance Services',
                    'money_transfer' => 'Money Transfer',
                    'mobile_banking' => 'Mobile Banking',
                    'atm_services' => 'ATM Services',
                    'investment' => 'Investment Services',
                    'financial_literacy' => 'Financial Literacy Training',
                    'group_lending' => 'Group Lending',
                    'business_loans' => 'Business Loans',
                    'emergency_loans' => 'Emergency Loans',
                    'asset_financing' => 'Asset Financing'
                ];
            @endphp

            @foreach($services as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.services_offered" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.services_offered') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Membership Requirements --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Membership Requirements</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $requirements = [
                    'id_card' => 'National ID Card',
                    'passport_photo' => 'Passport Photos',
                    'proof_of_income' => 'Proof of Income',
                    'bank_statement' => 'Bank Statement',
                    'recommendation_letter' => 'Recommendation Letter',
                    'residence_proof' => 'Proof of Residence',
                    'employer_letter' => 'Employer Letter',
                    'guarantor' => 'Guarantor Required',
                    'membership_fee' => 'Membership Fee',
                    'introduction_fee' => 'Introduction Fee'
                ];
            @endphp

            @foreach($requirements as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.membership_requirements" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.membership_requirements') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Additional Financial Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Total Assets (UGX)</span>
            </label>
            <input type="number" wire:model="categoryDetails.total_assets" class="input input-bordered"
                   placeholder="Total SACCO assets" min="0">
            @error('categoryDetails.total_assets') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Loan Repayment Period</span>
            </label>
            <select wire:model="categoryDetails.loan_repayment_period" class="select select-bordered">
                <option value="">Select Period</option>
                <option value="1_MONTH">1 Month</option>
                <option value="3_MONTHS">3 Months</option>
                <option value="6_MONTHS">6 Months</option>
                <option value="1_YEAR">1 Year</option>
                <option value="2_YEARS">2 Years</option>
                <option value="3_YEARS">3 Years</option>
                <option value="5_YEARS">5 Years</option>
                <option value="FLEXIBLE">Flexible Terms</option>
            </select>
            @error('categoryDetails.loan_repayment_period') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Meeting Frequency</span>
            </label>
            <select wire:model="categoryDetails.meeting_frequency" class="select select-bordered">
                <option value="">Select Frequency</option>
                <option value="WEEKLY">Weekly</option>
                <option value="MONTHLY">Monthly</option>
                <option value="QUARTERLY">Quarterly</option>
                <option value="ANNUALLY">Annually</option>
            </select>
            @error('categoryDetails.meeting_frequency') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Dividend Rate (%)</span>
            </label>
            <input type="number" step="0.1" wire:model="categoryDetails.dividend_rate" class="input input-bordered"
                   placeholder="Annual dividend rate" min="0" max="100">
            @error('categoryDetails.dividend_rate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Regulatory Information --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Regulatory Compliance & Certifications</span>
        </label>
        <textarea wire:model="categoryDetails.regulatory_compliance" class="textarea textarea-bordered h-20"
                  placeholder="UMRA registration, AMFIU compliance, insurance coverage, audit reports, etc."></textarea>
        @error('categoryDetails.regulatory_compliance') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
