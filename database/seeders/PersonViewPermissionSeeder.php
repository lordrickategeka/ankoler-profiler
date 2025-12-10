<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PersonViewPermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ensure permissions exist
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
        $rolePermissions = [
            'Super Admin' => [
                'can_view_Organizational_persons',
                'can_view_all_Organizational_persons'
            ],
            'Organization Admin' => [
                'can_view_Organizational_persons'
            ],
            'Organization Admin' => [
                'can_view_Organizational_persons'
            ],
            'Organization Manager' => [
                'can_view_Organizational_persons'
            ],
            'Staff' => [
                'can_view_Organizational_persons'
            ],
            'Manager' => [
                'can_view_Organizational_persons'
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($permissions as $permission) {
                    $role->givePermissionTo($permission);
                }
                echo "Assigned permissions to {$roleName}\n";
            } else {
                echo "Role {$roleName} not found\n";
            }
        }
    }
}
