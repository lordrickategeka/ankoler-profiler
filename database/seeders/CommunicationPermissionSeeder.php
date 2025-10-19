<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CommunicationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create communication permissions
        $permissions = [
            'view-communications',
            'send-communications',
            'manage-communications',
            'send-bulk-communications',
            'view-communication-analytics',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // Assign permissions to existing roles
        $this->assignPermissionsToRoles();

        echo "Communication permissions created and assigned successfully!\n";
    }

    /**
     * Assign communication permissions to existing roles
     */
    private function assignPermissionsToRoles()
    {
        $rolePermissions = [
            'Super Admin' => [
                'view-communications',
                'send-communications',
                'manage-communications',
                'send-bulk-communications',
                'view-communication-analytics'
            ],
            'Organisation Admin' => [
                'view-communications',
                'send-communications',
                'send-bulk-communications',
                'view-communication-analytics'
            ],
            'Department Manager' => [
                'view-communications',
                'send-communications',
                'view-communication-analytics'
            ],
            'Data Entry Clerk' => [
                'view-communications',
                'send-communications'
            ],
            'Compliance Officer' => [
                'view-communications',
                'view-communication-analytics'
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($permissions as $permission) {
                    $role->givePermissionTo($permission);
                }
                echo "Assigned communication permissions to {$roleName}\n";
            } else {
                echo "Role {$roleName} not found\n";
            }
        }
    }
}
