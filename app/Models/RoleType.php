<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Permission;

class RoleType extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get affiliations with this role type
     */
    public function affiliations()
    {
        return $this->hasMany(PersonAffiliation::class, 'role_type', 'code');
    }

    /**
     * Get permissions associated with this role type
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_type_permissions', 'role_type_id', 'permission_id');
    }

    /**
     * Scope to get only active role types
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get role by code
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Check if this role type has any active affiliations
     */
    public function hasActiveAffiliations()
    {
        return $this->affiliations()
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Get count of active affiliations
     */
    public function activeAffiliationsCount()
    {
        return $this->affiliations()
                    ->where('status', 'active')
                    ->count();
    }

    /**
     * Get domain record table name for this role type
     * Used to determine which domain-specific table to use
     */
    public function getDomainTableAttribute()
    {
        return match($this->code) {
            'STAFF' => 'staff_record',
            'STUDENT' => 'student_record',
            'PATIENT' => 'patient_record',
            'MEMBER' => 'sacco_member_record',
            'PARISHIONER' => 'parish_member_record',
            'CUSTOMER' => null,
            'VENDOR' => null,
            'VOLUNTEER' => null,
            'GUARDIAN' => null,
            default => null,
        };
    }

    /**
     * Check if this role type has a domain-specific table
     */
    public function hasDomainTable()
    {
        return !is_null($this->domain_table);
    }

    /**
     * Get the model class for domain-specific records
     */
    public function getDomainModelAttribute()
    {
        return match($this->code) {
            'STAFF' => \App\Models\StaffRecord::class,
            'STUDENT' => \App\Models\StudentRecord::class,
            'PATIENT' => \App\Models\PatientRecord::class,
            'MEMBER' => \App\Models\SaccoMemberRecord::class,
            'PARISHIONER' => \App\Models\ParishMemberRecord::class,
            default => null,
        };
    }

    /**
     * Assign permission to this role type
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission && !$this->hasPermissionTo($permission)) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    /**
     * Remove permission from this role type
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }

    /**
     * Check if role type has specific permission
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }

        return $this->permissions()->where('id', $permission->id)->exists();
    }

    /**
     * Sync permissions for this role type
     */
    public function syncPermissions($permissions)
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                $perm = Permission::where('name', $permission)->first();
                return $perm ? $perm->id : null;
            }
            return is_object($permission) ? $permission->id : $permission;
        })->filter()->toArray();

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * Get all permission names for this role type
     */
    public function getPermissionNames()
    {
        return $this->permissions()->pluck('name')->toArray();
    }
}
