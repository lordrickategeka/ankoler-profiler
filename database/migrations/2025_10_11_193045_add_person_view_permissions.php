<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the new permissions
        $permissions = [
            'can_view_Organizational_persons' => 'Can view persons from their organization',
            'can_view_all_Organizational_persons' => 'Can view persons from all organizations',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // Assign permissions to roles
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo([
                'can_view_Organizational_persons',
                'can_view_all_Organizational_persons'
            ]);
        }

        // Give organizational viewing permission to other roles that should see persons
        $organizationRoles = [
            'Organization Admin',
            'Organization Manager',
            'Staff',
            'Manager'
        ];

        foreach ($organizationRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo('can_view_Organizational_persons');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permissions
        Permission::where('name', 'can_view_Organizational_persons')->delete();
        Permission::where('name', 'can_view_all_Organizational_persons')->delete();
    }
};
