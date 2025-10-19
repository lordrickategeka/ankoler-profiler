<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        // Drop existing views if they exist
        DB::statement('DROP VIEW IF EXISTS person_complete_profile');
        DB::statement('DROP VIEW IF EXISTS organization_network');
        DB::statement('DROP VIEW IF EXISTS relationship_network_analysis');
        DB::statement('DROP VIEW IF EXISTS cross_org_impact_analysis');
        DB::statement('DROP VIEW IF EXISTS communication_target_analysis');

        // 1. Person Complete Profile View
        DB::statement("
            CREATE VIEW person_complete_profile AS
            SELECT
                p.id,
                p.person_id,
                p.global_identifier,
                CONCAT(p.given_name, ' ', COALESCE(p.middle_name, ''), ' ', p.family_name) as full_name,
                p.given_name,
                p.middle_name,
                p.family_name,
                p.date_of_birth,
                p.gender,
                p.city,
                p.district,
                p.country,
                p.status as person_status,
                GROUP_CONCAT(DISTINCT CONCAT(o.legal_name, ' (', pa.role_type, ')') ORDER BY pa.start_date DESC SEPARATOR '; ') as all_roles,
                COUNT(DISTINCT pa.organisation_id) as organization_count,
                COUNT(DISTINCT CASE WHEN pa.status = 'active' THEN pa.organisation_id END) as active_organization_count,
                GROUP_CONCAT(DISTINCT ph.number ORDER BY ph.is_primary DESC SEPARATOR ', ') as phone_numbers,
                GROUP_CONCAT(DISTINCT ea.email ORDER BY ea.is_primary DESC SEPARATOR ', ') as email_addresses,
                GROUP_CONCAT(DISTINCT CONCAT(
                    CASE
                        WHEN pr.person_a_id = p.id THEN CONCAT(p2.given_name, ' ', p2.family_name, ' (', pr.relationship_type, ')')
                        ELSE CONCAT(p1.given_name, ' ', p1.family_name, ' (', pr.relationship_type, ')')
                    END
                ) SEPARATOR '; ') as family_relationships,
                COUNT(DISTINCT pr.id) as relationship_count,
                COUNT(DISTINCT cor.id) as cross_org_connections,
                MAX(pa.updated_at) as last_affiliation_update,
                p.created_at as person_created_at,
                p.updated_at as person_updated_at
            FROM persons p
            LEFT JOIN person_affiliations pa ON p.id = pa.person_id AND pa.status IN ('active', 'inactive')
            LEFT JOIN organisations o ON pa.organisation_id = o.id
            LEFT JOIN phones ph ON p.id = ph.person_id AND ph.status = 'active'
            LEFT JOIN email_addresses ea ON p.id = ea.person_id AND ea.status = 'active'
            LEFT JOIN person_relationships pr ON (p.id = pr.person_a_id OR p.id = pr.person_b_id) AND pr.status = 'active'
            LEFT JOIN persons p1 ON pr.person_a_id = p1.id
            LEFT JOIN persons p2 ON pr.person_b_id = p2.id
            LEFT JOIN cross_org_relationships cor ON p.id = cor.person_id AND cor.status = 'active'
            WHERE p.status = 'active'
            GROUP BY p.id
        ");

        // 2. Organization Network View
        DB::statement("
            CREATE VIEW organization_network AS
            SELECT
                o.id as organization_id,
                o.legal_name,
                o.category,
                COUNT(DISTINCT pa.person_id) as total_persons,
                COUNT(DISTINCT CASE WHEN pa.status = 'active' THEN pa.person_id END) as active_persons,
                COUNT(DISTINCT CASE WHEN pa.role_type = 'STAFF' THEN pa.person_id END) as staff_count,
                COUNT(DISTINCT CASE WHEN pa.role_type = 'STUDENT' THEN pa.person_id END) as student_count,
                COUNT(DISTINCT CASE WHEN pa.role_type = 'PATIENT' THEN pa.person_id END) as patient_count,
                COUNT(DISTINCT CASE WHEN pa.role_type = 'MEMBER' THEN pa.person_id END) as member_count,
                COUNT(DISTINCT CASE WHEN pa.role_type NOT IN ('STAFF', 'STUDENT', 'PATIENT', 'MEMBER') THEN pa.person_id END) as other_roles_count,
                COUNT(DISTINCT cor.id) as outbound_connections,
                COUNT(DISTINCT cor2.id) as inbound_connections,
                COUNT(DISTINCT pr.id) as internal_family_connections,
                AVG(cor.impact_score) as avg_connection_strength,
                COUNT(DISTINCT cor.person_id) as unique_connected_persons,
                o.created_at,
                o.updated_at
            FROM organisations o
            LEFT JOIN person_affiliations pa ON o.id = pa.organisation_id
            LEFT JOIN cross_org_relationships cor ON pa.id = cor.primary_affiliation_id AND cor.status = 'active'
            LEFT JOIN cross_org_relationships cor2 ON pa.id = cor2.secondary_affiliation_id AND cor2.status = 'active'
            LEFT JOIN person_relationships pr ON (pa.person_id = pr.person_a_id OR pa.person_id = pr.person_b_id) AND pr.status = 'active'
            WHERE o.is_active = 1
            GROUP BY o.id
        ");

        // 3. Relationship Network Analysis View
        DB::statement("
            CREATE VIEW relationship_network_analysis AS
            SELECT
                pr.id as relationship_id,
                pr.relationship_id as relationship_code,
                pr.relationship_type,
                p1.person_id as person_a_code,
                CONCAT(p1.given_name, ' ', p1.family_name) as person_a_name,
                p1.city as person_a_city,
                p1.district as person_a_district,
                p2.person_id as person_b_code,
                CONCAT(p2.given_name, ' ', p2.family_name) as person_b_name,
                p2.city as person_b_city,
                p2.district as person_b_district,
                pr.confidence_score,
                pr.verification_status,
                pr.discovery_method,
                pr.status as relationship_status,
                GROUP_CONCAT(DISTINCT CONCAT(o1.legal_name, ' (', pa1.role_type, ')') SEPARATOR '; ') as person_a_organizations,
                GROUP_CONCAT(DISTINCT CONCAT(o2.legal_name, ' (', pa2.role_type, ')') SEPARATOR '; ') as person_b_organizations,
                COUNT(DISTINCT CASE WHEN pa1.organisation_id = pa2.organisation_id THEN pa1.organisation_id END) as shared_organizations,
                CASE
                    WHEN p1.city = p2.city AND p1.district = p2.district THEN 'Same Location'
                    WHEN p1.district = p2.district THEN 'Same District'
                    WHEN p1.country = p2.country THEN 'Same Country'
                    ELSE 'Different Locations'
                END as geographic_proximity,
                pr.created_at as relationship_created,
                pr.updated_at as relationship_updated
            FROM person_relationships pr
            JOIN persons p1 ON pr.person_a_id = p1.id
            JOIN persons p2 ON pr.person_b_id = p2.id
            LEFT JOIN person_affiliations pa1 ON p1.id = pa1.person_id AND pa1.status = 'active'
            LEFT JOIN person_affiliations pa2 ON p2.id = pa2.person_id AND pa2.status = 'active'
            LEFT JOIN organisations o1 ON pa1.organisation_id = o1.id
            LEFT JOIN organisations o2 ON pa2.organisation_id = o2.id
            WHERE pr.status = 'active'
            GROUP BY pr.id
        ");

        // 4. Cross-Organization Impact View
        DB::statement("
            CREATE VIEW cross_org_impact_analysis AS
            SELECT
                cor.id as cross_relationship_id,
                cor.cross_relationship_id as cross_relationship_code,
                p.person_id as person_code,
                CONCAT(p.given_name, ' ', p.family_name) as person_name,
                o1.legal_name as primary_organization,
                o1.category as primary_org_category,
                pa1.role_type as primary_role,
                pa1.start_date as primary_start_date,
                o2.legal_name as secondary_organization,
                o2.category as secondary_org_category,
                pa2.role_type as secondary_role,
                pa2.start_date as secondary_start_date,
                cor.relationship_context,
                cor.relationship_strength,
                cor.impact_score,
                cor.discovery_method,
                cor.verified,
                DATEDIFF(COALESCE(pa2.start_date, pa1.start_date), pa1.start_date) as role_time_gap_days,
                CASE
                    WHEN o1.parent_organization_id = o2.parent_organization_id THEN 'Same Diocese'
                    WHEN o1.id = o2.parent_organization_id OR o2.id = o1.parent_organization_id THEN 'Parent-Child Orgs'
                    ELSE 'Different Diocese'
                END as organizational_relationship,
                cor.discovered_date,
                cor.verified_at
            FROM cross_org_relationships cor
            JOIN persons p ON cor.person_id = p.id
            JOIN person_affiliations pa1 ON cor.primary_affiliation_id = pa1.id
            JOIN person_affiliations pa2 ON cor.secondary_affiliation_id = pa2.id
            JOIN organisations o1 ON pa1.organisation_id = o1.id
            JOIN organisations o2 ON pa2.organisation_id = o2.id
            WHERE cor.status = 'active'
        ");

        // 5. Communication Target Analysis View
        DB::statement("
            CREATE VIEW communication_target_analysis AS
            SELECT
                p.id as person_id,
                p.person_id as person_code,
                CONCAT(p.given_name, ' ', p.family_name) as full_name,
                ph.number as primary_phone,
                ea.email as primary_email,
                GROUP_CONCAT(DISTINCT CONCAT(o.legal_name, ':', pa.role_type) SEPARATOR '|') as targeting_roles,
                COUNT(DISTINCT CASE WHEN pr.relationship_type IN ('parent_child', 'spouse', 'guardian_ward') THEN pr.id END) as family_reach_count,
                p.city,
                p.district,
                p.country,
                CASE WHEN pa.role_type LIKE '%STUDENT%' THEN 1 ELSE 0 END as is_student_segment,
                CASE WHEN pa.role_type LIKE '%STAFF%' THEN 1 ELSE 0 END as is_staff_segment,
                CASE WHEN pa.role_type LIKE '%PATIENT%' THEN 1 ELSE 0 END as is_patient_segment,
                CASE WHEN pa.role_type LIKE '%MEMBER%' THEN 1 ELSE 0 END as is_member_segment,
                CASE WHEN COUNT(DISTINCT pa.organisation_id) > 1 THEN 1 ELSE 0 END as is_multi_org,
                CASE WHEN COUNT(DISTINCT pa.role_type) > 1 THEN 1 ELSE 0 END as is_multi_role,
                'email' as preferred_channel,
                p.updated_at as last_profile_update
            FROM persons p
            LEFT JOIN phones ph ON p.id = ph.person_id AND ph.is_primary = 1 AND ph.status = 'active'
            LEFT JOIN email_addresses ea ON p.id = ea.person_id AND ea.is_primary = 1 AND ea.status = 'active'
            LEFT JOIN person_affiliations pa ON p.id = pa.person_id AND pa.status = 'active'
            LEFT JOIN organisations o ON pa.organisation_id = o.id
            LEFT JOIN person_relationships pr ON (p.id = pr.person_a_id OR p.id = pr.person_b_id) AND pr.status = 'active'
            WHERE p.status = 'active'
            GROUP BY p.id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS person_complete_profile');
        DB::statement('DROP VIEW IF EXISTS organization_network');
        DB::statement('DROP VIEW IF EXISTS relationship_network_analysis');
        DB::statement('DROP VIEW IF EXISTS cross_org_impact_analysis');
        DB::statement('DROP VIEW IF EXISTS communication_target_analysis');
    }
};
