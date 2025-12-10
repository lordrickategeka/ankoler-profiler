<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            // COMMON FIELDS - Basic Information
            $table->string('legal_name')->unique();
            $table->string('display_name')->nullable();
            $table->string('code', 20)->unique();
            $table->enum('organization_type', ['super', 'branch', 'HOLDING', 'SUBSIDIARY', 'STANDALONE'])->default('branch');
            $table->boolean('is_super')->default(false);
            $table->foreignId('parent_organization_id')->nullable()->constrained('organizations')->onDelete('set null');
            $table->string('registration_number')->nullable();
            $table->string('tax_identification_number')->unique()->nullable();
            $table->string('country_of_registration', 3)->default('UGA'); // ISO country codes
            $table->date('date_established')->nullable();
            $table->text('logo_path')->nullable();
            $table->text('website_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Organization Category
            $table->enum('category', [
                'hospital',
                'school',
                'sacco',
                'parish',
                'corporate',
                'government',
                'ngo',
                'other'
            ]);

            // Primary Address
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country', 3)->default('UGA');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Regulatory & Compliance
            $table->string('regulatory_body')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_issue_date')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->enum('accreditation_status', ['PENDING', 'ACCREDITED', 'EXPIRED', 'NOT_APPLICABLE'])->default('NOT_APPLICABLE');
            $table->json('compliance_certifications')->nullable(); // ISO, etc.

            // Contact Persons
            $table->string('primary_contact_name');
            $table->string('primary_contact_title')->nullable();
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone');
            $table->string('secondary_contact_name')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('secondary_contact_phone')->nullable();

            // Financial Information
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('swift_bic_code')->nullable();
            $table->char('default_currency', 3)->default('UGX'); // ISO currency codes
            $table->tinyInteger('fiscal_year_start_month')->default(1); // 1 = January

            // System Configuration
            $table->string('timezone', 50)->default('Africa/Kampala');
            $table->string('default_language', 10)->default('en');
            $table->json('working_days')->nullable(); // ['monday', 'tuesday', etc.]
            $table->time('operating_hours_start')->nullable();
            $table->time('operating_hours_end')->nullable();

            // Category-Specific JSON Fields
            $table->json('hospital_details')->nullable();
            /*
            Example hospital_details structure:
            {
                "hospital_type": "general_hospital|specialized_hospital|clinic|health_center|referral_hospital|teaching_hospital",
                "ownership_type": "government|private|faith_based|ngo|corporate",
                "bed_capacity": 150,
                "operating_theaters": 3,
                "has_emergency_department": true,
                "has_icu": true,
                "has_laboratory": true,
                "has_pharmacy": true,
                "has_radiology": true,
                "has_ambulance": true,
                "medical_license_number": "ML123456",
                "moh_registration_number": "MOH789",
                "nhis_accreditation": true,
                "insurance_providers": ["AAR", "APA", "NHIS"],
                "specializations": ["pediatrics", "surgery", "internal_medicine"],
                "accreditations": ["JCI", "ISO_9001"],
                "emergency_hotline": "+256123456789",
                "fire_safety_certificate": "FS2023001",
                "fire_safety_expiry": "2025-12-31"
            }
            */

            $table->json('school_details')->nullable();
            /*
            Example school_details structure:
            {
                "school_type": "pre_primary|primary|secondary|combined|vocational|special_needs|international",
                "school_level": "early_years|primary|o_level|a_level|combined",
                "ownership": "government|private|faith_based|ngo|community",
                "gender_composition": "co_educational|boys_only|girls_only",
                "boarding_type": "day_school|boarding_school|mixed",
                "curriculum": "national|cambridge|ib|american|other",
                "student_capacity": 800,
                "current_enrollment": 654,
                "number_of_classrooms": 24,
                "number_of_teachers": 45,
                "teacher_student_ratio": "1:15",
                "moe_registration_number": "MOE2023001",
                "uneb_center_number": "U0123",
                "last_inspection_date": "2023-09-15",
                "facilities": {
                    "has_library": true,
                    "has_computer_lab": true,
                    "has_science_labs": true,
                    "has_sports_facilities": true,
                    "has_canteen": true,
                    "has_medical_room": true,
                    "has_transport": true,
                    "has_dormitories": true
                },
                "academic_calendar": {
                    "structure": "3_terms|2_semesters|4_quarters",
                    "term1_start": "2025-02-01",
                    "term1_end": "2025-05-15",
                    "term2_start": "2025-06-10",
                    "term2_end": "2025-09-20",
                    "term3_start": "2025-10-05",
                    "term3_end": "2025-12-15"
                }
            }
            */

            $table->json('sacco_details')->nullable();
            /*
            Example sacco_details structure:
            {
                "sacco_type": "community|employee|parish|farmers|teachers|market_vendors|women|youth",
                "membership_type": "open|closed",
                "bond_of_association": "employees|church_members|farmers|teachers|vendors",
                "registration_authority": "ministry_of_trade|district_commercial|cooperative_dept|central_bank",
                "tier_level": "tier_1|tier_2|tier_3|tier_4",
                "central_bank_license": "CBL2023001",
                "date_of_first_registration": "2020-03-15",
                "certificate_of_incorporation": "CI2020001",
                "minimum_share_capital": 100000,
                "share_value": 10000,
                "minimum_shares": 10,
                "maximum_shares": 1000,
                "entrance_fee": 20000,
                "registration_fee": 10000,
                "current_total_members": 245,
                "active_members": 220,
                "total_share_capital": 24500000,
                "total_savings": 180000000,
                "total_loans_outstanding": 150000000,
                "savings_products": ["ordinary", "fixed_deposit", "children", "retirement"],
                "loan_products": ["development", "emergency", "school_fees", "business", "agricultural"],
                "interest_rates": {
                    "savings_rate": 8.5,
                    "loan_rate": 18.0,
                    "penalty_rate": 5.0,
                    "processing_fee": 2.0
                },
                "loan_terms": {
                    "minimum_amount": 50000,
                    "maximum_amount": 5000000,
                    "minimum_period_months": 3,
                    "maximum_period_months": 60,
                    "loan_to_share_ratio": "3:1"
                },
                "services": {
                    "number_of_branches": 3,
                    "mobile_money_integration": true,
                    "mobile_money_providers": ["MTN", "Airtel"],
                    "agency_banking": false,
                    "atm_services": false,
                    "online_banking": true,
                    "core_banking_system": "Craft Silicon"
                },
                "governance": {
                    "board_size": 7,
                    "election_frequency_years": 3,
                    "agm_month": "March",
                    "audit_firm": "KPMG Uganda",
                    "last_audit_date": "2024-06-30"
                }
            }
            */

            $table->json('parish_details')->nullable();
            /*
            Example parish_details structure:
            {
                "denomination": "catholic|anglican|protestant|pentecostal|sda|muslim|other",
                "church_type": "parish|cathedral|diocese_hq|mission_station|outstation",
                "archdiocese_diocese": "Kampala Archdiocese",
                "mother_church": "St. Mary's Cathedral",
                "patron_saint": "St. Francis of Assisi",
                "registered_members": 1200,
                "active_members": 800,
                "sub_parishes": 5,
                "outstations": 8
            }
            */

            $table->json('corporate_details')->nullable();
            /*
            Example corporate_details structure:
            {
                "company_type": "limited|plc|partnership|sole_proprietorship",
                "industry_sector": "manufacturing|services|retail|technology|agriculture",
                "number_of_employees": 150,
                "annual_turnover": 5000000000,
                "business_activities": ["software_development", "consulting", "training"],
                "certifications": ["ISO_9001", "ISO_27001"]
            }
            */

            // Multi-Site Support
            $table->boolean('is_multi_site')->default(false);
            $table->boolean('is_head_office')->default(true);

            // Integration & System Settings
            $table->json('integration_settings')->nullable();
            /*
            Example integration_settings structure:
            {
                "hrms": {
                    "system_name": "BambooHR",
                    "api_endpoint": "https://api.bamboohr.com",
                    "enabled": true
                },
                "accounting": {
                    "system_name": "QuickBooks",
                    "api_endpoint": "https://api.quickbooks.com",
                    "enabled": true
                },
                "mobile_money": {
                    "mtn_enabled": true,
                    "airtel_enabled": true,
                    "api_keys_configured": true
                },
                "sms_gateway": {
                    "provider": "Twilio",
                    "enabled": true
                },
                "email_service": {
                    "provider": "SendGrid",
                    "enabled": true
                }
            }
            */

            // Subscription & Billing (if SaaS model)
            $table->enum('subscription_plan', ['free_trial', 'basic', 'professional', 'enterprise'])->default('free_trial');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->boolean('is_trial')->default(true);
            $table->integer('trial_days_remaining')->default(30);

            // Document Storage
            $table->json('documents')->nullable();
            /*
            Example documents structure:
            {
                "certificate_of_incorporation": "path/to/file.pdf",
                "tax_clearance": "path/to/file.pdf",
                "professional_license": "path/to/file.pdf",
                "accreditation_certificate": "path/to/file.pdf",
                "board_resolution": "path/to/file.pdf",
                "memorandum_articles": "path/to/file.pdf",
                "latest_audit_report": "path/to/file.pdf"
            }
            */

            // Audit & Tracking
            $table->json('metadata')->nullable(); // Additional flexible data storage
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['category', 'is_active']);
            $table->index(['country', 'district']);
            $table->index(['license_expiry_date']);
            $table->index(['subscription_plan', 'subscription_end_date']);
            $table->index(['created_at']);
            $table->index(['parent_organization_id']);
        });

        // Create organization_sites table for multi-site support
        Schema::create('organization_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('site_name');
            $table->string('site_code', 20)->unique();
            $table->enum('site_type', ['branch', 'campus', 'ward', 'department', 'clinic', 'office']);
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country', 3)->default('UGA');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('site_contact_name')->nullable();
            $table->string('site_contact_phone')->nullable();
            $table->string('site_contact_email')->nullable();
            $table->time('operating_hours_start')->nullable();
            $table->time('operating_hours_end')->nullable();
            $table->json('services_available')->nullable(); // What services are available at this site
            $table->json('site_specific_details')->nullable(); // Site-specific data
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['site_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_sites');
        Schema::dropIfExists('organizations');
    }
};
