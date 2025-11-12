<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Services\PersonDeduplicationService;
use App\Models\Person;
use App\Models\Organisation;
use App\Models\PersonAffiliation;
use App\Models\Phone;
use App\Models\EmailAddress;
use App\Models\PersonIdentifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\HandlesSweetAlerts;

class CreatePerson extends Component
{
    use HandlesSweetAlerts;
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // COMPONENT STATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    // Form data
    public $form = [
        'given_name' => '',
        'middle_name' => '',
        'family_name' => '',
        'date_of_birth' => '',
        'gender' => '',
        'phone' => '',
        'email' => '',
        'national_id' => '',
        'address' => '',
        'city' => '',
        'district' => '',
        'country' => 'Uganda',
        'role_type' => 'STAFF', // Default role
        'role_title' => '',
        'site' => '',
        'start_date' => '',
    ];

    // Domain-specific record fields
    public $domainRecord = [
        // Staff fields
        'staff_number' => '',
        'payroll_id' => '',
        'employment_type' => '',
        'grade' => '',
        'contract_start' => '',
        'contract_end' => '',
        'supervisor_id' => '',

        // Student fields
        'student_number' => '',
        'enrollment_date' => '',
        'graduation_date' => '',
        'current_class' => '',
        'guardian_name' => '',
        'guardian_phone' => '',
        'guardian_email' => '',

        // Patient fields
        'patient_number' => '',
        'medical_record_number' => '',
        'primary_physician_id' => '',
        'primary_care_unit_id' => '',
        'allergies' => '',
        'chronic_conditions' => '',

        // SACCO Member fields - using correct field names
        'membership_number' => '',
        'join_date' => '',
        'share_capital' => '',
        'savings_account_ref' => '',

        // Parish Member fields - using correct field names
        'member_number' => '',
        'baptism_date' => '',
        'communion_status' => '',
    ];

    // Duplicate detection
    public $duplicates;
    public $showDuplicateWarning = false;
    public $selectedDuplicate = null;
    public $duplicateAction = null; // 'link', 'create_new', 'view_profile'

    // Organization context
    public $currentOrganisation = null;
    public $availableRoles = [];
    public $currentAffiliationRoles = []; // Roles for current affiliation organization

    // Super Admin organization selection
    public $isSuperAdmin = false;
    public $selectedOrganisationId = null;
    public $availableOrganisations = [];
    public $isOrganizationLocked = false;
    public $selectedOrganisationName = '';

    // Multiple Affiliations Management
    public $affiliations = [];
    public $currentAffiliation = [
        'organisation_id' => '',
        'role_type' => 'STAFF',
        'role_title' => '',
        'site' => '',
        'start_date' => '',
        'domain_record' => []
    ];
    public $editingAffiliationIndex = null;

    // UI state
    public $currentStep = 'basic_info'; // basic_info, duplicate_check, affiliation_details, confirm
    public $isLoading = false;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // TOAST NOTIFICATION METHODS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    /**
     * Dispatch a success toast notification
     */
    protected function showSuccessToast($message)
    {
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Dispatch an error toast notification
     */
    protected function showErrorToast($message)
    {
        $this->dispatch('show-toast', [
            'type' => 'error',
            'message' => $message
        ]);
    }

    /**
     * Dispatch an info toast notification
     */
    protected function showInfoToast($message)
    {
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => $message
        ]);
    }

    /**
     * Get domain record fields based on role type
     */
    public function getDomainRecordFields()
    {
        $roleType = $this->form['role_type'];

        switch ($roleType) {
            case 'STAFF':
                return [
                    'staff_number' => 'Staff Number',
                    'payroll_id' => 'Payroll ID',
                    'employment_type' => 'Employment Type',
                    'grade' => 'Grade/Level',
                    'contract_start' => 'Contract Start Date',
                    'contract_end' => 'Contract End Date',
                ];

            case 'STUDENT':
                return [
                    'student_number' => 'Student Number',
                    'enrollment_date' => 'Enrollment Date',
                    'current_class' => 'Current Class/Grade',
                    'guardian_name' => 'Guardian Name',
                    'guardian_phone' => 'Guardian Phone',
                    'guardian_email' => 'Guardian Email',
                ];

            case 'PATIENT':
                return [
                    'patient_number' => 'Patient Number',
                    'medical_record_number' => 'Medical Record Number',
                    'allergies' => 'Known Allergies',
                    'chronic_conditions' => 'Chronic Conditions',
                ];

            case 'SACCO_MEMBER':
            case 'MEMBER': // Map MEMBER to SACCO_MEMBER for SACCO organizations
                return [
                    'membership_number' => 'Membership Number',
                    'join_date' => 'Join Date',
                    'share_capital' => 'Share Capital',
                    'savings_account_ref' => 'Savings Account Reference',
                ];

            case 'PARISH_MEMBER':
            case 'PARISHIONER': // Map PARISHIONER to PARISH_MEMBER
                return [
                    'member_number' => 'Member Number',
                    'baptism_date' => 'Baptism Date',
                    'communion_status' => 'Communion Status',
                ];

            default:
                return [];
        }
    }

    /**
     * Get domain record data (actual values) based on role type
     */
    private function getDomainRecordData($roleType)
    {
        $data = [];

        switch ($roleType) {
            case 'STAFF':
                $data = [
                    'staff_number' => $this->domainRecord['staff_number'] ?? '',
                    'payroll_id' => $this->domainRecord['payroll_id'] ?? '',
                    'employment_type' => $this->domainRecord['employment_type'] ?? '',
                    'grade' => $this->domainRecord['grade'] ?? '',
                    'contract_start' => $this->domainRecord['contract_start'] ?? '',
                    'contract_end' => $this->domainRecord['contract_end'] ?? '',
                ];
                break;

            case 'STUDENT':
                $data = [
                    'student_number' => $this->domainRecord['student_number'] ?? '',
                    'enrollment_date' => $this->domainRecord['enrollment_date'] ?? '',
                    'current_class' => $this->domainRecord['current_class'] ?? '',
                    'guardian_name' => $this->domainRecord['guardian_name'] ?? '',
                    'guardian_phone' => $this->domainRecord['guardian_phone'] ?? '',
                    'guardian_email' => $this->domainRecord['guardian_email'] ?? '',
                ];
                break;

            case 'PATIENT':
                $data = [
                    'patient_number' => $this->domainRecord['patient_number'] ?? '',
                    'medical_record_number' => $this->domainRecord['medical_record_number'] ?? '',
                    'allergies' => $this->domainRecord['allergies'] ?? '',
                    'chronic_conditions' => $this->domainRecord['chronic_conditions'] ?? '',
                ];
                break;

            case 'MEMBER':
                $data = [
                    'membership_number' => $this->domainRecord['membership_number'] ?? '',
                    'join_date' => $this->domainRecord['join_date'] ?? '',
                    'share_capital' => $this->domainRecord['share_capital'] ?? '',
                    'savings_account_ref' => $this->domainRecord['savings_account_ref'] ?? '',
                ];
                break;

            case 'PARISHIONER':
                $data = [
                    'member_number' => $this->domainRecord['member_number'] ?? '',
                    'baptism_date' => $this->domainRecord['baptism_date'] ?? '',
                    'communion_status' => $this->domainRecord['communion_status'] ?? '',
                ];
                break;
        }

        // Filter out empty values
        return array_filter($data, function($value) {
            return !empty($value);
        });
    }

    /**
     * Check if current role type has domain records
     */
    public function getHasDomainRecordsProperty()
    {
        return !empty($this->getDomainRecordFields());
    }

    /**
     * Test method to demonstrate toast functionality
     */
    public function testToast($type = 'success')
    {
        switch ($type) {
            case 'success':
                $this->showSuccessToast('✅ This is a success toast message!');
                break;
            case 'error':
                $this->showErrorToast('❌ This is an error toast message!');
                break;
            case 'info':
                $this->showInfoToast('ℹ️ This is an info toast message!');
                break;
        }
    }

    /**
     * Debug method to test if Livewire is working
     */
    public function testLivewire()
    {
        $this->showSuccessToast('Livewire is working! Current step: ' . $this->currentStep);
        Log::info('CreatePerson: testLivewire called', [
            'current_step' => $this->currentStep,
            'form_data' => $this->form
        ]);
    }

    /**
     * Test error logging system
     */
    public function testErrorLogging()
    {
        try {
            Log::info('CreatePerson: Testing error logging system');
            $this->showInfoToast('📝 Testing error logging...');

            // Simulate an error
            throw new \Exception('This is a test error for debugging purposes');

        } catch (\Exception $e) {
            Log::error('CreatePerson: Test error caught successfully', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showErrorToast('🚨 Test error logged successfully: ' . $e->getMessage());
        }
    }

    /**
     * Custom validation with toast notifications
     */
    protected function validateWithToast()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Show first validation error as toast
            $firstError = collect($e->errors())->flatten()->first();
            if ($firstError) {
                $this->showErrorToast('Validation Error: ' . $firstError);
            }
            throw $e;
        }
    }

    public function mount()
    {
        // Initialize duplicates as empty collection
        $this->duplicates = collect();

        // Check if current user has permissions (cleaner than role checks)
        $user = Auth::user();
        $this->isSuperAdmin = false;
        if ($user) {
            // Check if user has all organizational persons permission (Super Admin level)
            $this->isSuperAdmin = $user->can('can_view_all_organisational_persons');
        }

        if ($this->isSuperAdmin) {
            // Super Admin can select any organization
            $this->loadAvailableOrganisations();
            // Set default to current session organization or first available
            $this->selectedOrganisationId = session('current_organization_id') ?? (count($this->availableOrganisations) > 0 ? $this->availableOrganisations[0]['id'] : null);
        } else {
            // For non-Super Admin users, automatically set their current organization
            $currentOrganization = user_current_organization();
            if ($currentOrganization) {
                $this->selectedOrganisationId = $currentOrganization->id;
                        // Assign 'Person' role
                        $user->assignRole('Person');
                $this->selectedOrganisationName = $currentOrganization->display_name ?? $currentOrganization->legal_name;
                $this->isOrganizationLocked = true;
            }
        }

        // Set current organization from user context
        $this->currentOrganisation = $this->getCurrentUserOrganisation();

        // Set available roles based on organization category
        $this->setAvailableRolesForOrganization();

        // Set default start date to today
        $this->form['start_date'] = now()->format('Y-m-d');

        // Initialize affiliations array
        $this->initializeAffiliations();

        // For non-Super Admin users, automatically add their organization affiliation
        if (!$this->isSuperAdmin && $this->selectedOrganisationId) {
            $currentOrganization = user_current_organization();
            if ($currentOrganization) {
                $defaultRoleType = $this->getDefaultRoleTypeForOrganization($currentOrganization);

                $this->currentAffiliation = [
                    'organisation_id' => $this->selectedOrganisationId,
                    'role_type' => $defaultRoleType,
                    'role_title' => $this->getDefaultRoleTitleForType($defaultRoleType),
                    'site' => '',
                    'start_date' => now()->format('Y-m-d'),
                    'domain_record' => []
                ];
            }
        }

        // Initialize current affiliation roles
        $this->currentAffiliationRoles = $this->getAvailableRolesForOrganization($this->currentAffiliation['organisation_id'] ?? null);
    }

    /**
     * Get default role type based on organization category
     */
    private function getDefaultRoleTypeForOrganization($organization)
    {
        return match($organization->category) {
            'hospital' => 'STAFF',
            'school' => 'STAFF',
            'sacco' => 'MEMBER',
            'parish' => 'PARISHIONER',
            default => 'STAFF'
        };
    }

    /**
     * Get default role title for a role type
     */
    private function getDefaultRoleTitleForType($roleType)
    {
        return match($roleType) {
            'STAFF' => 'Staff Member',
            'MEMBER' => 'Member',
            'PARISHIONER' => 'Parish Member',
            'STUDENT' => 'Student',
            'PATIENT' => 'Patient',
            default => 'Staff Member'
        };
    }

    /**
     * Update available roles when organization changes in current affiliation
     */
    public function updatedCurrentAffiliationOrganisationId($organizationId)
    {
        $this->currentAffiliationRoles = $this->getAvailableRolesForOrganization($organizationId);

        // Reset role type if it's not available in the new organization
        if ($this->currentAffiliation['role_type'] && !array_key_exists($this->currentAffiliation['role_type'], $this->currentAffiliationRoles)) {
            $this->currentAffiliation['role_type'] = '';
            $this->currentAffiliation['role_title'] = '';
        }

        // Set default role type if none selected
        if (!$this->currentAffiliation['role_type'] && !empty($this->currentAffiliationRoles)) {
            $organization = Organisation::find($organizationId);
            if ($organization) {
                $defaultRoleType = $this->getDefaultRoleTypeForOrganization($organization);
                if (array_key_exists($defaultRoleType, $this->currentAffiliationRoles)) {
                    $this->currentAffiliation['role_type'] = $defaultRoleType;
                    $this->currentAffiliation['role_title'] = $this->getDefaultRoleTitleForType($defaultRoleType);
                }
            }
        }
    }

    /**
     * Initialize affiliations management
     */
    private function initializeAffiliations()
    {
        // Initialize current affiliation with default organization
        $defaultOrgId = $this->isSuperAdmin
            ? $this->selectedOrganisationId
            : ($this->currentOrganisation ? $this->currentOrganisation->id : null);

        $this->currentAffiliation = [
            'organisation_id' => $defaultOrgId,
            'role_type' => 'STAFF',
            'role_title' => '',
            'site' => '',
            'start_date' => now()->format('Y-m-d'),
            'domain_record' => []
        ];

        // Start with empty affiliations array
        $this->affiliations = [];
    }

    /**
     * Get the deduplication service instance
     */
    protected function getDeduplicationService()
    {
        return app(PersonDeduplicationService::class);
    }

    /**
     * Get role label for a specific role type and organization
     */
    public function getRoleLabelForOrganization($roleType, $organizationId)
    {
        // For Super Admin, get all available roles
        if ($this->isSuperAdmin) {
            $allRoles = [
                'STAFF' => 'Staff Member',
                'STUDENT' => 'Student',
                'PATIENT' => 'Patient',
                'MEMBER' => 'Member',
                'PARISHIONER' => 'Parishioner',
                'CUSTOMER' => 'Customer',
                'VENDOR' => 'Vendor',
                'VOLUNTEER' => 'Volunteer',
                'GUARDIAN' => 'Parent/Guardian',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'ALUMNI' => 'Alumni',
            ];
            return $allRoles[$roleType] ?? $roleType;
        }

        // For regular users, get organization-specific roles
        $roles = $this->getAvailableRolesForOrganization($organizationId);
        return $roles[$roleType] ?? $roleType;
    }

    /**
     * Get available role types for a specific organization
     */
    private function getAvailableRolesForOrganization($organizationId)
    {
        // Super Admin users can see all role types regardless of organization
        if ($this->isSuperAdmin) {
            return [
                'STAFF' => 'Staff Member',
                'STUDENT' => 'Student',
                'PATIENT' => 'Patient',
                'MEMBER' => 'Member',
                'PARISHIONER' => 'Parishioner',
                'CUSTOMER' => 'Customer',
                'VENDOR' => 'Vendor',
                'VOLUNTEER' => 'Volunteer',
                'GUARDIAN' => 'Parent/Guardian',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'ALUMNI' => 'Alumni',
            ];
        }

        if (!$organizationId) {
            return [
                'STAFF' => 'Staff Member',
                'MEMBER' => 'Member',
                'VOLUNTEER' => 'Volunteer',
            ];
        }

        $organization = Organisation::find($organizationId);
        if (!$organization) {
            return [
                'STAFF' => 'Staff Member',
                'MEMBER' => 'Member',
                'VOLUNTEER' => 'Volunteer',
            ];
        }

        // Super organizations can see all available role types
        if ($organization->is_super) {
            return [
                'STAFF' => 'Staff Member',
                'STUDENT' => 'Student',
                'PATIENT' => 'Patient',
                'MEMBER' => 'Member',
                'PARISHIONER' => 'Parishioner',
                'CUSTOMER' => 'Customer',
                'VENDOR' => 'Vendor',
                'VOLUNTEER' => 'Volunteer',
                'GUARDIAN' => 'Parent/Guardian',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'ALUMNI' => 'Alumni',
            ];
        }

        // Map organization categories to appropriate role types
        return match($organization->category) {
            'hospital' => [
                'STAFF' => 'Staff Member',        // Doctors, Nurses, Admin
                'PATIENT' => 'Patient',           // Hospital patients
                'VOLUNTEER' => 'Volunteer',       // Community volunteers
                'CONSULTANT' => 'Consultant',     // External consultants
                'VENDOR' => 'Vendor',            // Suppliers, contractors
            ],
            'school' => [
                'STAFF' => 'Staff Member',        // Teachers, Admin
                'STUDENT' => 'Student',           // Enrolled students
                'ALUMNI' => 'Alumni',             // Former students
                'GUARDIAN' => 'Parent/Guardian',  // Student parents
                'VOLUNTEER' => 'Volunteer',       // Community volunteers
                'CONSULTANT' => 'Consultant',     // External consultants
            ],
            'sacco' => [
                'STAFF' => 'Staff Member',        // Employees
                'MEMBER' => 'SACCO Member',       // Financial members
                'BOARD_MEMBER' => 'Board Member', // Management committee
                'CONSULTANT' => 'Consultant',     // External advisors
                'VENDOR' => 'Vendor',            // Service providers
            ],
            'parish' => [
                'STAFF' => 'Staff Member',        // Clergy, admin
                'PARISHIONER' => 'Parishioner',   // Church members
                'VOLUNTEER' => 'Volunteer',       // Church volunteers
                'BOARD_MEMBER' => 'Council Member', // Parish council
                'CONSULTANT' => 'Consultant',     // External advisors
            ],
            'corporate' => [
                'STAFF' => 'Employee',
                'CUSTOMER' => 'Customer',
                'VENDOR' => 'Vendor',
                'CONSULTANT' => 'Consultant',
                'BOARD_MEMBER' => 'Board Member',
            ],
            'government' => [
                'STAFF' => 'Civil Servant',
                'CUSTOMER' => 'Citizen/Client',
                'CONSULTANT' => 'Consultant',
                'VENDOR' => 'Contractor',
            ],
            'ngo' => [
                'STAFF' => 'Staff Member',
                'VOLUNTEER' => 'Volunteer',
                'MEMBER' => 'Member',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'CUSTOMER' => 'Beneficiary',
            ],
            default => [
                'STAFF' => 'Staff Member',
                'MEMBER' => 'Member',
                'CUSTOMER' => 'Customer',
                'VOLUNTEER' => 'Volunteer',
                'CONSULTANT' => 'Consultant',
            ]
        };
    }

    /**
     * Set available roles based on organization category
     */
    private function setAvailableRolesForOrganization()
    {
        if (!$this->currentOrganisation) {
            // Default roles if no organization context
            $this->availableRoles = [
                'STAFF' => 'Staff Member',
                'MEMBER' => 'Member',
                'VOLUNTEER' => 'Volunteer',
            ];
            return;
        }

        // Super organizations can see all available role types
        if ($this->currentOrganisation->is_super) {
            $this->availableRoles = [
                'STAFF' => 'Staff Member',
                'STUDENT' => 'Student',
                'PATIENT' => 'Patient',
                'MEMBER' => 'Member',
                'PARISHIONER' => 'Parishioner',
                'CUSTOMER' => 'Customer',
                'VENDOR' => 'Vendor',
                'VOLUNTEER' => 'Volunteer',
                'GUARDIAN' => 'Parent/Guardian',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'ALUMNI' => 'Alumni',
            ];
            return;
        }

        // Map organization categories to appropriate role types
        $this->availableRoles = match($this->currentOrganisation->category) {
            'hospital' => [
                'STAFF' => 'Staff Member',
                'PATIENT' => 'Patient',
                'VOLUNTEER' => 'Volunteer',
                'CONSULTANT' => 'Consultant',
                'VENDOR' => 'Vendor',
            ],
            'school' => [
                'STAFF' => 'Staff Member',
                'STUDENT' => 'Student',
                'ALUMNI' => 'Alumni',
                'GUARDIAN' => 'Parent/Guardian',
                'VOLUNTEER' => 'Volunteer',
                'CONSULTANT' => 'Consultant',
            ],
            'sacco' => [
                'STAFF' => 'Staff Member',
                'MEMBER' => 'SACCO Member',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'VENDOR' => 'Vendor',
            ],
            'parish' => [
                'STAFF' => 'Staff Member',
                'PARISHIONER' => 'Parishioner',
                'VOLUNTEER' => 'Volunteer',
                'BOARD_MEMBER' => 'Council Member',
                'CONSULTANT' => 'Consultant',
            ],
            'corporate' => [
                'STAFF' => 'Employee',
                'CUSTOMER' => 'Customer',
                'VENDOR' => 'Vendor',
                'CONSULTANT' => 'Consultant',
                'BOARD_MEMBER' => 'Board Member',
            ],
            'government' => [
                'STAFF' => 'Civil Servant',
                'CUSTOMER' => 'Citizen/Client',
                'CONSULTANT' => 'Consultant',
                'VENDOR' => 'Contractor',
            ],
            'ngo' => [
                'STAFF' => 'Staff Member',
                'VOLUNTEER' => 'Volunteer',
                'MEMBER' => 'Member',
                'BOARD_MEMBER' => 'Board Member',
                'CONSULTANT' => 'Consultant',
                'CUSTOMER' => 'Beneficiary',
            ],
            default => [
                'STAFF' => 'Staff Member',
                'MEMBER' => 'Member',
                'CUSTOMER' => 'Customer',
                'VOLUNTEER' => 'Volunteer',
                'CONSULTANT' => 'Consultant',
            ]
        };
    }

    /**
     * Refresh organization context and available roles
     * Call this method when organization context changes
     */
    public function refreshOrganizationContext()
    {
        $this->currentOrganisation = $this->getCurrentUserOrganisation();
        $this->setAvailableRolesForOrganization();

        // Reset role selection if current selection is no longer available
        if ($this->form['role_type'] && !isset($this->availableRoles[$this->form['role_type']])) {
            $this->form['role_type'] = '';
        }
    }

    /**
     * Load available organizations for Super Admin
     */
    private function loadAvailableOrganisations()
    {
        $this->availableOrganisations = Organisation::orderBy('legal_name')->get()->toArray();
    }

    /**
     * Handle organization selection change for Super Admin
     */
    public function updatedSelectedOrganisationId()
    {
        if ($this->isSuperAdmin && $this->selectedOrganisationId) {
            $this->currentOrganisation = Organisation::find($this->selectedOrganisationId);
            $this->setAvailableRolesForOrganization();

            // Reset role selection when organization changes
            $this->form['role_type'] = '';
        }
    }    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // REAL-TIME DUPLICATE CHECKING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    /**
     * Trigger after name + DOB entered
     */
    public function updated($field)
    {
        if (in_array($field, ['form.given_name', 'form.family_name', 'form.date_of_birth'])) {
            $this->checkDuplicatesIfSufficientData();
        }
    }

    /**
     * Trigger after phone entered
     */
    public function updatedFormPhone()
    {
        // Validate the phone field
        $this->validateOnly('form.phone');

        if (!empty($this->form['phone'])) {
            $this->checkDuplicates();
        }
    }

    /**
     * Trigger after email entered
     */
    public function updatedFormEmail()
    {
        // Validate the email field
        $this->validateOnly('form.email');

        if (!empty($this->form['email'])) {
            $this->checkDuplicates();
        }
    }

    /**
     * Trigger after national ID entered
     */
    public function updatedFormNationalId()
    {
        // Validate the national ID field
        $this->validateOnly('form.national_id');

        if (!empty($this->form['national_id'])) {
            $this->checkDuplicates();
        }
    }

    /**
     * Trigger after given name entered
     */
    public function updatedFormGivenName()
    {
        $this->validateOnly('form.given_name');
    }

    /**
     * Trigger after family name entered
     */
    public function updatedFormFamilyName()
    {
        $this->validateOnly('form.family_name');
    }

    /**
     * Check for duplicates if we have sufficient data
     */
    private function checkDuplicatesIfSufficientData()
    {
        // Only check if we have name + DOB
        if (!empty($this->form['given_name']) &&
            !empty($this->form['family_name']) &&
            !empty($this->form['date_of_birth'])) {
            $this->checkDuplicates();
        }
    }

    /**
     * Main duplicate checking method
     */
    public function checkDuplicates()
    {
        $this->isLoading = true;

        try {
            $duplicatesCollection = $this->getDeduplicationService()->findPotentialDuplicates($this->form);
            $this->duplicates = $duplicatesCollection;

            // Show warning if high or medium confidence matches found
            $this->showDuplicateWarning = $duplicatesCollection->filter(function($match) {
                return in_array($match['confidence'], ['high', 'medium']) && $match['similarity'] > 75;
            })->isNotEmpty();

        } catch (\Exception $e) {
            $this->showErrorToast('Error checking for duplicates: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DUPLICATE HANDLING ACTIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    /**
     * User chooses to link to existing person
     */
    public function linkToExisting($personId)
    {
        $this->selectedDuplicate = $this->duplicates->firstWhere('person.id', $personId);
        $this->duplicateAction = 'link';
        $this->currentStep = 'affiliation_details';
        $this->showDuplicateWarning = false;
    }

    /**
     * User chooses to view full profile first
     */
    public function viewProfile($personId)
    {
        $this->selectedDuplicate = $this->duplicates->firstWhere('person.id', $personId);
        $this->duplicateAction = 'view_profile';

        // Emit event to open modal with person details
        $this->dispatch('show-person-profile', personId: $personId);
    }

    /**
     * User chooses to create as new person
     */
    public function createAsNew()
    {
        $this->duplicateAction = 'create_new';
        $this->currentStep = 'affiliation_details';
        $this->showDuplicateWarning = false;
    }

    /**
     * User dismisses duplicate warning
     */
    public function dismissDuplicateWarning()
    {
        $this->showDuplicateWarning = false;
        $this->duplicates = collect();
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // FORM SUBMISSION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    /**
     * Final form submission
     */
    public function submit()
    {
        $this->showInfoToast('🚀 Starting form submission...');
        $this->isLoading = true;

        try {
            // Log form data for debugging
            Log::info('CreatePerson: Form submission started', [
                'form_data' => $this->form,
                'domain_record' => $this->domainRecord,
                'current_step' => $this->currentStep,
                'duplicate_action' => $this->duplicateAction
            ]);

            // Validate the form
            $this->showInfoToast('📋 Validating form...');
            $this->validateWithToast();
            $this->showSuccessToast('✅ Validation passed!');

            // Validate affiliations
            if (empty($this->affiliations)) {
                $this->isLoading = false;
                $this->showErrorToast('Please add at least one affiliation for this person.');
                $this->currentStep = 'affiliation_details';
                return;
            }

            $this->showInfoToast('🔗 Validating affiliations...');
            $this->validateAffiliations();
            $this->showSuccessToast('✅ Affiliations validated!');

            // Final duplicate check before submission
            if ($this->duplicateAction !== 'create_new') {
                $this->showInfoToast('🔍 Checking for duplicates...');
                $finalDuplicateCheck = $this->getDeduplicationService()->findPotentialDuplicates($this->form);

                $highConfidenceMatches = $finalDuplicateCheck->filter(function($match) {
                    return $match['confidence'] === 'high';
                });

                if ($highConfidenceMatches->isNotEmpty() && $this->duplicateAction !== 'link') {
                    $this->duplicates = $finalDuplicateCheck;
                    $this->showDuplicateWarning = true;
                    $this->currentStep = 'basic_info';
                    $this->isLoading = false;
                    $this->showErrorToast('⚠️ High confidence duplicates found - please review');
                    return;
                }
            }

            $this->showInfoToast('💾 Creating person record...');

            DB::transaction(function () {
                if ($this->duplicateAction === 'link' && $this->selectedDuplicate) {
                    Log::info('CreatePerson: Linking to existing person', ['selected_duplicate' => $this->selectedDuplicate]);
                    $this->linkToExistingPerson();
                } else {
                    Log::info('CreatePerson: Creating new person');
                    $this->createNewPerson();
                }
            });

            $this->isLoading = false;
            Log::info('CreatePerson: Form submission completed successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isLoading = false;
            $this->handleValidationErrors($e, 'CreatePerson: Form submission validation');
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->handleFormError($e, 'CreatePerson: Form submission', 'Failed to create person. Please try again.');
        }
    }

    /**
     * Create new person and affiliation
     */
    private function createNewPerson()
    {
        try {
            Log::info('CreatePerson: Starting person creation', ['form_data' => $this->form]);


            // Generate a temporary password
            $temporaryPassword = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%'), 0, 10);

            // Generate person_id using IdGenerator helper
            $personId = \App\Helpers\IdGenerator::generatePersonId();
            $person_global_identifier = \App\Helpers\IdGenerator::generateGlobalIdentifier();


            // Create person (without password field)
            $person = Person::create([
                'person_id' => $personId,
                'global_identifier' => $person_global_identifier,
                'national_id' => $this->form['national_id'] ?: null,
                'given_name' => $this->form['given_name'],
                'middle_name' => $this->form['middle_name'] ?: null,
                'family_name' => $this->form['family_name'],
                'date_of_birth' => $this->form['date_of_birth'] ?: null,
                'gender' => $this->form['gender'] ?: null,
                'address' => $this->form['address'] ?: null,
                'city' => $this->form['city'] ?: null,
                'district' => $this->form['district'] ?: null,
                'country' => $this->form['country'],
                'classification' => json_encode([$this->form['role_type']]),
                'created_by' => Auth::id(),
                'password' => $temporaryPassword,
            ]);

            Log::info('CreatePerson: Person created successfully', ['person_id' => $person->id]);

            // Create a User for this person
            $user = \App\Models\User::create([
                'name' => $person->full_name ?? ($person->given_name . ' ' . $person->family_name),
                'email' => $this->form['email'],
                'password' => bcrypt($temporaryPassword),
                'person_id' => $person->id,
                'organisation_id' => $this->affiliations[0]['organisation_id'] ?? null,
            ]);

            // Assign 'Person' role to the user
            if ($user) {
                $user->assignRole('Person');
                Log::info('CreatePerson: Assigned Person role to user', ['user_id' => $user->id]);
            }
            Log::info('CreatePerson: User created for person', ['user_id' => $user->id, 'person_id' => $person->id]);

            // Send notification to person about profiling, with temp password (email will go to the email address provided)

            $this->createContactInformation($person);
            Log::info('CreatePerson: Contact information created');

            $this->createMultipleAffiliations($person);
            Log::info('CreatePerson: All affiliations created');
            \App\Services\NotificationService::notifyProfiled($person, $temporaryPassword);
            Log::info('CreatePerson: Notification sent to person about profiling');

            // Get first organization name for success message
            $firstOrgName = 'Unknown Organization';
            if (!empty($this->affiliations)) {
                $firstOrgId = $this->affiliations[0]['organisation_id'];
                Log::info('CreatePerson: Looking up organization for success message', [
                    'organisation_id' => $firstOrgId
                ]);
                $firstOrg = Organisation::find($firstOrgId);
                if ($firstOrg) {
                    $firstOrgName = $firstOrg->display_name ?? $firstOrg->legal_name ?? $firstOrg->name ?? 'Unnamed Organization';
                    Log::info('CreatePerson: Found organization', [
                        'organisation_id' => $firstOrgId,
                        'organisation_name' => $firstOrgName
                    ]);
                } else {
                    Log::warning('CreatePerson: Organization not found', [
                        'organisation_id' => $firstOrgId
                    ]);
                }
            }

            $affiliationCount = count($this->affiliations);
            $affiliationText = $affiliationCount > 1 ? "{$affiliationCount} affiliations" : "1 affiliation";

            // Store success message in session for display on person list page
            session()->flash('success', "✅ {$person->full_name} successfully registered with {$affiliationText} at {$firstOrgName}" . ($affiliationCount > 1 ? ' and others' : '') . "!");

            // Redirect to person list page
            return $this->redirect('/persons/all', navigate: true);

        } catch (\Exception $e) {
            Log::error('CreatePerson: Error in createNewPerson', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->form
            ]);
            throw $e;
        }
    }

    /**
     * Link to existing person and create new affiliations
     */
    private function linkToExistingPerson()
    {
        $existingPerson = $this->selectedDuplicate['person'];

        // Check for existing affiliations to prevent duplicates
        foreach ($this->affiliations as $affiliation) {
            if ($existingPerson->hasAffiliationWith($affiliation['organisation_id'], $affiliation['role_type'])) {
                $org = Organisation::find($affiliation['organisation_id']);
                $orgName = 'Unknown Organization';
                if ($org) {
                    $orgName = $org->display_name ?? $org->legal_name ?? $org->name ?? 'Unnamed Organization';
                }
                Log::warning('CreatePerson: Duplicate affiliation detected for existing person', [
                    'person_id' => $existingPerson->id,
                    'organisation_id' => $affiliation['organisation_id'],
                    'role_type' => $affiliation['role_type'],
                    'organisation_name' => $orgName
                ]);
                throw new \Exception("This person is already affiliated with {$orgName} as {$affiliation['role_type']}.");
            }
        }

        // Add new classifications for all roles
        foreach ($this->affiliations as $affiliation) {
            $existingPerson->addClassification($affiliation['role_type']);
        }

        // Create all new affiliations
        $this->createMultipleAffiliations($existingPerson);

        // Update contact info if provided and different
        $this->updateContactInformation($existingPerson);

        // Get first organization name for success message
        $firstOrgName = 'Unknown Organization';
        if (!empty($this->affiliations)) {
            $firstOrgId = $this->affiliations[0]['organisation_id'];
            Log::info('CreatePerson: Looking up organization for existing person success message', [
                'person_id' => $existingPerson->id,
                'organisation_id' => $firstOrgId
            ]);
            $firstOrg = Organisation::find($firstOrgId);
            if ($firstOrg) {
                $firstOrgName = $firstOrg->display_name ?? $firstOrg->legal_name ?? $firstOrg->name ?? 'Unnamed Organization';
                Log::info('CreatePerson: Found organization for existing person', [
                    'organisation_id' => $firstOrgId,
                    'organisation_name' => $firstOrgName
                ]);
            } else {
                Log::warning('CreatePerson: Organization not found for existing person', [
                    'organisation_id' => $firstOrgId
                ]);
            }
        }

        $affiliationCount = count($this->affiliations);
        $affiliationText = $affiliationCount > 1 ? "{$affiliationCount} affiliations" : "1 affiliation";

        session()->flash('success', "✅ {$existingPerson->full_name} successfully linked with {$affiliationText} at {$firstOrgName}" . ($affiliationCount > 1 ? ' and others' : '') . "! This person already existed in the system.");

        $this->resetForm();

        return redirect()->route('persons.all');
    }

    /**
     * Create contact information for person
     */
    private function createContactInformation(Person $person)
    {
        // Create phone
        if (!empty($this->form['phone'])) {
            Phone::create([
                'person_id' => $person->id,
                'number' => $this->form['phone'],
                'type' => 'mobile',
                'is_primary' => true,
                'created_by' => Auth::id(),
                // Do NOT set phone_id here; let the model auto-generate it
            ]);
        }

        // Create email
        if (!empty($this->form['email'])) {
            EmailAddress::create([
                'person_id' => $person->id,
                'email' => $this->form['email'],
                'type' => 'personal',
                'is_primary' => true,
                'created_by' => Auth::id(),
            ]);
        }

        // Create national ID
        if (!empty($this->form['national_id'])) {
            PersonIdentifier::create([
                'person_id' => $person->id,
                'type' => 'national_id',
                'identifier' => $this->form['national_id'],
                'issuing_authority' => 'NIRA',
                'created_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Update contact information for existing person
     */
    private function updateContactInformation(Person $person)
    {
        // Add phone if new and different
        if (!empty($this->form['phone'])) {
            $existingPhone = $person->phones()->where('number', $this->form['phone'])->first();
            if (!$existingPhone) {
                Phone::create([
                    'person_id' => $person->id,
                    'number' => $this->form['phone'],
                    'type' => 'mobile',
                    'is_primary' => false, // Don't override existing primary
                    'created_by' => Auth::id(),
                    // Do NOT set phone_id here; let the model auto-generate it
                ]);
            }
        }

        // Add email if new and different
        if (!empty($this->form['email'])) {
            $existingEmail = $person->emailAddresses()->where('email', strtolower($this->form['email']))->first();
            if (!$existingEmail) {
                EmailAddress::create([
                    'person_id' => $person->id,
                    'email' => $this->form['email'],
                    'type' => 'personal',
                    'is_primary' => false, // Don't override existing primary
                    'created_by' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Create person affiliation
     */
    private function createAffiliation(Person $person)
    {
        // Determine which organization to use
        $organizationId = $this->isSuperAdmin && $this->selectedOrganisationId
            ? $this->selectedOrganisationId
            : $this->currentOrganisation->id;

        $affiliation = PersonAffiliation::create([
            'person_id' => $person->id,
            'organisation_id' => $organizationId,
            'site' => $this->form['site'] ?: null,
            'role_type' => $this->form['role_type'],
            'role_title' => $this->form['role_title'] ?: null,
            'start_date' => $this->form['start_date'],
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        // Create domain-specific record if applicable
        $this->createDomainRecord($affiliation);
    }

    /**
     * Create multiple affiliations for a person
     */
    private function createMultipleAffiliations(Person $person)
    {
        if (empty($this->affiliations)) {
            Log::warning('CreatePerson: No affiliations to create');
            return;
        }

        foreach ($this->affiliations as $affiliationData) {
            try {
                Log::info('CreatePerson: Creating affiliation', [
                    'person_id' => $person->id,
                    'affiliation_data' => $affiliationData
                ]);

                $affiliation = PersonAffiliation::create([
                    'person_id' => $person->id,
                    'organisation_id' => $affiliationData['organisation_id'],
                    'site' => $affiliationData['site'] ?: null,
                    'role_type' => $affiliationData['role_type'],
                    'role_title' => $affiliationData['role_title'] ?: null,
                    'start_date' => $affiliationData['start_date'],
                    'status' => 'active',
                    'created_by' => Auth::id(),
                ]);

                // Create domain-specific record if applicable
                if (isset($affiliationData['domain_record']) && !empty($affiliationData['domain_record'])) {
                    $this->createDomainRecordFromData($affiliation, $affiliationData['domain_record'], $affiliationData['role_type']);
                }

                Log::info('CreatePerson: Affiliation created successfully', [
                    'affiliation_id' => $affiliation->id,
                    'role_type' => $affiliationData['role_type'],
                    'organisation_id' => $affiliationData['organisation_id']
                ]);

            } catch (\Exception $e) {
                Log::error('CreatePerson: Error creating affiliation', [
                    'error' => $e->getMessage(),
                    'affiliation_data' => $affiliationData,
                    'person_id' => $person->id
                ]);
                throw new \Exception("Failed to create affiliation for {$affiliationData['role_type']}: " . $e->getMessage());
            }
        }
    }

    /**
     * Create domain-specific record based on role type
     */
    private function createDomainRecord(PersonAffiliation $affiliation)
    {
        try {
            $roleType = $this->form['role_type'];
            Log::info('CreatePerson: Creating domain record', [
                'role_type' => $roleType,
                'affiliation_id' => $affiliation->id,
                'domain_record_data' => $this->domainRecord
            ]);

            switch ($roleType) {
                case 'STAFF':
                    $this->createStaffRecord($affiliation);
                    break;

                case 'STUDENT':
                    $this->createStudentRecord($affiliation);
                    break;

                case 'PATIENT':
                    $this->createPatientRecord($affiliation);
                    break;

                case 'SACCO_MEMBER':
                case 'MEMBER': // Map MEMBER to SACCO_MEMBER for SACCO organizations
                    $this->createSaccoMemberRecord($affiliation);
                    break;

                case 'PARISH_MEMBER':
                case 'PARISHIONER': // Map PARISHIONER to PARISH_MEMBER
                    $this->createParishMemberRecord($affiliation);
                    break;

                default:
                    Log::info('CreatePerson: No domain record needed for role type', ['role_type' => $roleType]);
                    break;
            }

            Log::info('CreatePerson: Domain record created successfully', ['role_type' => $roleType]);

        } catch (\Exception $e) {
            Log::error('CreatePerson: Error creating domain record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_type' => $this->form['role_type'],
                'domain_record_data' => $this->domainRecord
            ]);
            throw $e;
        }
    }

    /**
     * Create domain-specific record from affiliation data
     */
    private function createDomainRecordFromData(PersonAffiliation $affiliation, array $domainData, string $roleType)
    {
        try {
            Log::info('CreatePerson: Creating domain record from data', [
                'role_type' => $roleType,
                'affiliation_id' => $affiliation->id,
                'domain_data' => $domainData
            ]);

            switch ($roleType) {
                case 'STAFF':
                    $this->createStaffRecordFromData($affiliation, $domainData);
                    break;

                case 'STUDENT':
                    $this->createStudentRecordFromData($affiliation, $domainData);
                    break;

                case 'PATIENT':
                    $this->createPatientRecordFromData($affiliation, $domainData);
                    break;

                case 'MEMBER':
                    $this->createSaccoMemberRecordFromData($affiliation, $domainData);
                    break;

                case 'PARISHIONER':
                    $this->createParishMemberRecordFromData($affiliation, $domainData);
                    break;

                default:
                    Log::info('CreatePerson: No domain record needed for role type', ['role_type' => $roleType]);
                    break;
            }

            Log::info('CreatePerson: Domain record created successfully from data', ['role_type' => $roleType]);

        } catch (\Exception $e) {
            Log::error('CreatePerson: Error creating domain record from data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_type' => $roleType,
                'domain_data' => $domainData
            ]);
            throw $e;
        }
    }

    /**
     * Create staff record
     */
    private function createStaffRecord(PersonAffiliation $affiliation)
    {
        try {
            $staffData = array_filter([
                'affiliation_id' => $affiliation->id,
                'staff_number' => $this->domainRecord['staff_number'] ?: null,
                'payroll_id' => $this->domainRecord['payroll_id'] ?: null,
                'employment_type' => $this->domainRecord['employment_type'] ?: null,
                'grade' => $this->domainRecord['grade'] ?: null,
                'contract_start' => $this->domainRecord['contract_start'] ?: null,
                'contract_end' => $this->domainRecord['contract_end'] ?: null,
            ]);

            Log::info('CreatePerson: Creating staff record', ['staff_data' => $staffData]);

            if (!empty($staffData)) {
                $record = \App\Models\StaffRecord::create($staffData);
                Log::info('CreatePerson: Staff record created', ['record_id' => $record->id]);
            } else {
                Log::info('CreatePerson: No staff data to create record');
            }
        } catch (\Exception $e) {
            Log::error('CreatePerson: Error creating staff record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'staff_data' => $staffData ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Create student record
     */
    private function createStudentRecord(PersonAffiliation $affiliation)
    {
        $guardianContact = [];
        if ($this->domainRecord['guardian_name']) {
            $guardianContact['name'] = $this->domainRecord['guardian_name'];
        }
        if ($this->domainRecord['guardian_phone']) {
            $guardianContact['phone'] = $this->domainRecord['guardian_phone'];
        }
        if ($this->domainRecord['guardian_email']) {
            $guardianContact['email'] = $this->domainRecord['guardian_email'];
        }

        $studentData = array_filter([
            'affiliation_id' => $affiliation->id,
            'student_number' => $this->domainRecord['student_number'] ?: null,
            'enrollment_date' => $this->domainRecord['enrollment_date'] ?: null,
            'current_class' => $this->domainRecord['current_class'] ?: null,
            'guardian_contact' => !empty($guardianContact) ? $guardianContact : null,
        ]);

        if (!empty($studentData)) {
            \App\Models\StudentRecord::create($studentData);
        }
    }

    /**
     * Create patient record
     */
    private function createPatientRecord(PersonAffiliation $affiliation)
    {
        try {
            $patientData = array_filter([
                'affiliation_id' => $affiliation->id,
                'patient_number' => $this->domainRecord['patient_number'] ?: null,
                'medical_record_number' => $this->domainRecord['medical_record_number'] ?: null,
                'allergies' => $this->domainRecord['allergies'] ?: null,
                'chronic_conditions' => $this->domainRecord['chronic_conditions'] ?: null,
            ]);

            // Only add physician and care unit IDs if they are valid integers
            if (!empty($this->domainRecord['primary_physician_id']) && is_numeric($this->domainRecord['primary_physician_id'])) {
                $patientData['primary_physician_id'] = (int) $this->domainRecord['primary_physician_id'];
            }

            if (!empty($this->domainRecord['primary_care_unit_id']) && is_numeric($this->domainRecord['primary_care_unit_id'])) {
                $patientData['primary_care_unit_id'] = (int) $this->domainRecord['primary_care_unit_id'];
            }

            Log::info('CreatePerson: Creating patient record', ['patient_data' => $patientData]);

            if (!empty($patientData)) {
                $record = \App\Models\PatientRecord::create($patientData);
                Log::info('CreatePerson: Patient record created', ['record_id' => $record->id]);
            } else {
                Log::info('CreatePerson: No patient data to create record');
            }
        } catch (\Exception $e) {
            Log::error('CreatePerson: Error creating patient record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'patient_data' => $patientData ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Create SACCO member record
     */
    private function createSaccoMemberRecord(PersonAffiliation $affiliation)
    {
        $saccoData = array_filter([
            'affiliation_id' => $affiliation->id,
            'membership_number' => $this->domainRecord['membership_number'] ?: null,
            'join_date' => $this->domainRecord['join_date'] ?: null,
            'share_capital' => $this->domainRecord['share_capital'] ?: null,
            'savings_account_ref' => $this->domainRecord['savings_account_ref'] ?: null,
        ]);

        if (!empty($saccoData)) {
            \App\Models\SaccoMemberRecord::create($saccoData);
        }
    }

    /**
     * Create parish member record
     */
    private function createParishMemberRecord(PersonAffiliation $affiliation)
    {
        $parishData = array_filter([
            'affiliation_id' => $affiliation->id,
            'member_number' => $this->domainRecord['member_number'] ?: null,
            'baptism_date' => $this->domainRecord['baptism_date'] ?: null,
            'communion_status' => $this->domainRecord['communion_status'] ?: null,
        ]);

        if (!empty($parishData)) {
            \App\Models\ParishMemberRecord::create($parishData);
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DOMAIN RECORD CREATION FROM DATA
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    /**
     * Create staff record from data
     */
    private function createStaffRecordFromData(PersonAffiliation $affiliation, array $data)
    {
        $staffData = array_filter([
            'affiliation_id' => $affiliation->id,
            'staff_number' => $data['staff_number'] ?? null,
            'payroll_id' => $data['payroll_id'] ?? null,
            'employment_type' => $data['employment_type'] ?? null,
            'grade' => $data['grade'] ?? null,
            'contract_start' => $data['contract_start'] ?? null,
            'contract_end' => $data['contract_end'] ?? null,
        ]);

        if (!empty($staffData)) {
            \App\Models\StaffRecord::create($staffData);
        }
    }

    /**
     * Create student record from data
     */
    private function createStudentRecordFromData(PersonAffiliation $affiliation, array $data)
    {
        $studentData = array_filter([
            'affiliation_id' => $affiliation->id,
            'student_number' => $data['student_number'] ?? null,
            'enrollment_date' => $data['enrollment_date'] ?? null,
            'graduation_date' => $data['graduation_date'] ?? null,
            'current_class' => $data['current_class'] ?? null,
            'guardian_name' => $data['guardian_name'] ?? null,
            'guardian_phone' => $data['guardian_phone'] ?? null,
            'guardian_email' => $data['guardian_email'] ?? null,
        ]);

        if (!empty($studentData)) {
            \App\Models\StudentRecord::create($studentData);
        }
    }

    /**
     * Create patient record from data
     */
    private function createPatientRecordFromData(PersonAffiliation $affiliation, array $data)
    {
        $patientData = array_filter([
            'affiliation_id' => $affiliation->id,
            'patient_number' => $data['patient_number'] ?? null,
            'medical_record_number' => $data['medical_record_number'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'chronic_conditions' => $data['chronic_conditions'] ?? null,
        ]);

        if (!empty($patientData)) {
            \App\Models\PatientRecord::create($patientData);
        }
    }

    /**
     * Create SACCO member record from data
     */
    private function createSaccoMemberRecordFromData(PersonAffiliation $affiliation, array $data)
    {
        $saccoData = array_filter([
            'affiliation_id' => $affiliation->id,
            'membership_number' => $data['membership_number'] ?? null,
            'join_date' => $data['join_date'] ?? null,
            'share_capital' => $data['share_capital'] ?? null,
            'savings_account_ref' => $data['savings_account_ref'] ?? null,
        ]);

        if (!empty($saccoData)) {
            \App\Models\SaccoMemberRecord::create($saccoData);
        }
    }

    /**
     * Create parish member record from data
     */
    private function createParishMemberRecordFromData(PersonAffiliation $affiliation, array $data)
    {
        $parishData = array_filter([
            'affiliation_id' => $affiliation->id,
            'member_number' => $data['member_number'] ?? null,
            'baptism_date' => $data['baptism_date'] ?? null,
            'communion_status' => $data['communion_status'] ?? null,
        ]);

        if (!empty($parishData)) {
            \App\Models\ParishMemberRecord::create($parishData);
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // VALIDATION & UTILITIES
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    protected function rules()
    {
        $rules = [
            'form.given_name' => 'required|string|max:255',
            'form.family_name' => 'required|string|max:255',
            'form.middle_name' => 'nullable|string|max:255',
            'form.date_of_birth' => 'nullable|date|before:today',
            'form.gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'form.phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'form.email' => 'nullable|email|max:255|unique:email_addresses,email',
            'form.national_id' => 'nullable|string|max:50',
            'form.address' => 'nullable|string|max:500',
            'form.city' => 'nullable|string|max:255',
            'form.district' => 'nullable|string|max:255',
            'form.country' => 'required|string|max:255',
            'form.role_type' => ['required', Rule::in(array_keys($this->availableRoles))],
            'form.role_title' => 'nullable|string|max:255',
            'form.site' => 'nullable|string|max:255',
            'form.start_date' => 'required|date',
        ];

        // Add organization validation for Super Admin
        if ($this->isSuperAdmin) {
            $rules['selectedOrganisationId'] = 'required|exists:organisations,id';
        }

        // Add domain-specific validation rules
        $this->addDomainRecordValidation($rules);

        return $rules;
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'form.given_name.required' => 'Please enter the given name.',
            'form.family_name.required' => 'Please enter the family name.',
            'form.email.email' => 'Please enter a valid email address (e.g., user@example.com).',
            'form.email.unique' => 'This email address is already registered in the system.',
            'form.phone.regex' => 'Please enter a valid phone number format.',
            'form.date_of_birth.before' => 'Date of birth must be before today.',
            'form.start_date.required' => 'Please select a start date.',
            'form.role_type.required' => 'Please select a role type.',
            'selectedOrganisationId.required' => 'Please select an organization.',
        ];
    }

    /**
     * Add domain-specific validation rules
     */
    private function addDomainRecordValidation(array &$rules)
    {
        $roleType = $this->form['role_type'];

        switch ($roleType) {
            case 'STAFF':
                $rules['domainRecord.staff_number'] = 'nullable|string|max:50';
                $rules['domainRecord.payroll_id'] = 'nullable|string|max:50';
                $rules['domainRecord.employment_type'] = 'nullable|string|max:50';
                $rules['domainRecord.grade'] = 'nullable|string|max:50';
                $rules['domainRecord.contract_start'] = 'nullable|date';
                $rules['domainRecord.contract_end'] = 'nullable|date|after:domainRecord.contract_start';
                break;

            case 'STUDENT':
                $rules['domainRecord.student_number'] = 'nullable|string|max:50';
                $rules['domainRecord.enrollment_date'] = 'nullable|date';
                $rules['domainRecord.current_class'] = 'nullable|string|max:100';
                $rules['domainRecord.guardian_name'] = 'nullable|string|max:255';
                $rules['domainRecord.guardian_phone'] = 'nullable|string|max:20';
                $rules['domainRecord.guardian_email'] = 'nullable|email|max:255';
                break;

            case 'PATIENT':
                $rules['domainRecord.patient_number'] = 'nullable|string|max:50';
                $rules['domainRecord.medical_record_number'] = 'nullable|string|max:50';
                $rules['domainRecord.allergies'] = 'nullable|string|max:500';
                $rules['domainRecord.chronic_conditions'] = 'nullable|string|max:500';
                break;

            case 'SACCO_MEMBER':
            case 'MEMBER': // Map MEMBER to SACCO_MEMBER for SACCO organizations
                $rules['domainRecord.membership_number'] = 'nullable|string|max:50';
                $rules['domainRecord.join_date'] = 'nullable|date';
                $rules['domainRecord.share_capital'] = 'nullable|numeric|min:0';
                $rules['domainRecord.savings_account_ref'] = 'nullable|string|max:50';
                break;

            case 'PARISH_MEMBER':
            case 'PARISHIONER': // Map PARISHIONER to PARISH_MEMBER
                $rules['domainRecord.member_number'] = 'nullable|string|max:50';
                $rules['domainRecord.baptism_date'] = 'nullable|date';
                $rules['domainRecord.communion_status'] = 'nullable|string|max:50';
                break;
        }
    }

    private function getCurrentUserOrganisation()
    {
        // If Super Admin has selected an organization, use that
        if ($this->isSuperAdmin && $this->selectedOrganisationId) {
            return Organisation::find($this->selectedOrganisationId);
        }

        // Get user's primary organization from session (organization switcher)
        $currentOrgId = session('current_organization_id');

        if ($currentOrgId) {
            $org = Organisation::find($currentOrgId);
            if ($org) {
                return $org;
            }
        }

        // Fallback to user's primary organization if available
        $user = Auth::user();
        if ($user && $user->organisation_id) {
            return Organisation::find($user->organisation_id);
        }

        // Last fallback - get the first organization available
        return Organisation::first();
    }    private function resetForm()
    {
        $this->form = [
            'given_name' => '',
            'middle_name' => '',
            'family_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'phone' => '',
            'email' => '',
            'national_id' => '',
            'address' => '',
            'city' => '',
            'district' => '',
            'country' => 'Uganda',
            'role_type' => 'STAFF',
            'role_title' => '',
            'site' => '',
            'start_date' => now()->format('Y-m-d'),
        ];

        $this->duplicates = collect();
        $this->showDuplicateWarning = false;
        $this->selectedDuplicate = null;
        $this->duplicateAction = null;
        $this->currentStep = 'basic_info';
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MULTIPLE AFFILIATIONS MANAGEMENT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    /**
     * Add current affiliation to the list
     */
    public function addAffiliation()
    {
        $this->validateCurrentAffiliation();

        // Check role-based organization restrictions
        if (!$this->canAccessOrganization($this->currentAffiliation['organisation_id'])) {
            $this->showErrorToast('You do not have permission to create affiliations for this organization.');
            return;
        }

        // Check for duplicate affiliation (same org + role)
        foreach ($this->affiliations as $index => $affiliation) {
            if ($affiliation['organisation_id'] == $this->currentAffiliation['organisation_id'] &&
                $affiliation['role_type'] == $this->currentAffiliation['role_type']) {
                $this->showErrorToast('This person already has this role in this organization.');
                return;
            }
        }

        // Add domain record fields based on role type
        $this->currentAffiliation['domain_record'] = $this->getDomainRecordData($this->currentAffiliation['role_type']);

        if ($this->editingAffiliationIndex !== null) {
            // Update existing affiliation
            $this->affiliations[$this->editingAffiliationIndex] = $this->currentAffiliation;
            $this->editingAffiliationIndex = null;
            $this->showSuccessToast('Affiliation updated successfully.');
        } else {
            // Add new affiliation
            $this->affiliations[] = $this->currentAffiliation;
            $this->showSuccessToast('Affiliation added successfully.');
        }

        $this->resetCurrentAffiliation();
    }

    /**
     * Edit an existing affiliation
     */
    public function editAffiliation($index)
    {
        if (isset($this->affiliations[$index])) {
            $this->currentAffiliation = $this->affiliations[$index];
            $this->editingAffiliationIndex = $index;

            // Load domain record fields into the domainRecord array
            if (isset($this->currentAffiliation['domain_record'])) {
                $this->domainRecord = array_merge($this->domainRecord, $this->currentAffiliation['domain_record']);
            }
        }
    }

    /**
     * Remove an affiliation from the list
     */
    public function removeAffiliation($index)
    {
        if (isset($this->affiliations[$index])) {
            unset($this->affiliations[$index]);
            $this->affiliations = array_values($this->affiliations); // Re-index array
            $this->showSuccessToast('Affiliation removed successfully.');
        }

        // Reset if we were editing this affiliation
        if ($this->editingAffiliationIndex === $index) {
            $this->resetCurrentAffiliation();
        }
    }

    /**
     * Reset current affiliation form
     */
    public function resetCurrentAffiliation()
    {
        $defaultOrgId = $this->isSuperAdmin
            ? $this->selectedOrganisationId
            : ($this->currentOrganisation ? $this->currentOrganisation->id : null);

        $this->currentAffiliation = [
            'organisation_id' => $defaultOrgId,
            'role_type' => 'STAFF',
            'role_title' => '',
            'site' => '',
            'start_date' => now()->format('Y-m-d'),
            'domain_record' => []
        ];

        $this->editingAffiliationIndex = null;

        // Reset domain record fields
        $this->resetDomainRecord();
    }

    /**
     * Check if current user can access a specific organization
     */
    private function canAccessOrganization($organizationId)
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Super Admin can access any organization
        if ($this->isSuperAdmin) {
            return true;
        }

        // Organization Admin can only access their own organization
        try {
            if (method_exists($user, 'hasRole')) {
                $isOrgAdmin = $user->hasRole('Organization Admin');
            } elseif (method_exists($user, 'roles')) {
                $isOrgAdmin = $user->roles()->where('name', 'Organization Admin')->exists();
            } else {
                $isOrgAdmin = false;
            }

            if ($isOrgAdmin) {
                // Check if the organization matches user's organization
                return $this->currentOrganisation && $this->currentOrganisation->id == $organizationId;
            }

            // Other roles might have different restrictions
            return true;

        } catch (\Exception $e) {
            Log::error('Error checking organization access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate current affiliation before adding
     */
    private function validateCurrentAffiliation()
    {
        $rules = [
            'currentAffiliation.organisation_id' => 'required|exists:organisations,id',
            'currentAffiliation.role_type' => 'required|string',
            'currentAffiliation.start_date' => 'required|date',
        ];

        $this->validate($rules, [
            'currentAffiliation.organisation_id.required' => 'Please select an organization.',
            'currentAffiliation.organisation_id.exists' => 'Selected organization is invalid.',
            'currentAffiliation.role_type.required' => 'Please select a role type.',
            'currentAffiliation.start_date.required' => 'Please provide a start date.',
            'currentAffiliation.start_date.date' => 'Please provide a valid start date.',
        ]);
    }

    /**
     * Validate all affiliations before submission
     */
    private function validateAffiliations()
    {
        if (empty($this->affiliations)) {
            throw new \Exception('At least one affiliation is required.');
        }

        foreach ($this->affiliations as $index => $affiliation) {
            if (empty($affiliation['organisation_id'])) {
                throw new \Exception("Affiliation #" . ($index + 1) . " is missing organization selection.");
            }

            if (empty($affiliation['role_type'])) {
                throw new \Exception("Affiliation #" . ($index + 1) . " is missing role type selection.");
            }

            if (empty($affiliation['start_date'])) {
                throw new \Exception("Affiliation #" . ($index + 1) . " is missing start date.");
            }

            // Validate organization exists
            if (!Organisation::find($affiliation['organisation_id'])) {
                throw new \Exception("Affiliation #" . ($index + 1) . " has invalid organization.");
            }

            // Check role-based access permissions
            if (!$this->canAccessOrganization($affiliation['organisation_id'])) {
                throw new \Exception("You do not have permission to create affiliations for organization in affiliation #" . ($index + 1) . ".");
            }
        }
    }

    /**
     * Get organizations available to current user
     */
    public function getAvailableOrganizations()
    {
        if ($this->isSuperAdmin) {
            return $this->availableOrganisations;
        }

        // Organization Admin and others can only see their organization
        if ($this->currentOrganisation) {
            return [['id' => $this->currentOrganisation->id, 'name' => $this->currentOrganisation->display_name ?? $this->currentOrganisation->legal_name]];
        }

        return [];
    }

    /**
     * Reset domain record fields
     */
    private function resetDomainRecord()
    {
        $this->domainRecord = [
            // Staff fields
            'staff_number' => '',
            'payroll_id' => '',
            'employment_type' => '',
            'grade' => '',
            'contract_start' => '',
            'contract_end' => '',
            'supervisor_id' => '',

            // Student fields
            'student_number' => '',
            'enrollment_date' => '',
            'graduation_date' => '',
            'current_class' => '',
            'guardian_name' => '',
            'guardian_phone' => '',
            'guardian_email' => '',

            // Patient fields
            'patient_number' => '',
            'medical_record_number' => '',
            'primary_physician_id' => '',
            'primary_care_unit_id' => '',
            'allergies' => '',
            'chronic_conditions' => '',

            // SACCO Member fields
            'membership_number' => '',
            'join_date' => '',
            'share_capital' => '',
            'savings_account_ref' => '',

            // Parish Member fields
            'member_number' => '',
            'baptism_date' => '',
            'communion_status' => '',
        ];
    }

    public function render()
    {
        return view('livewire.person.create-person');
    }
}
