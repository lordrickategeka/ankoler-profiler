<?php

namespace App\Livewire\Organizations;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReviewUnitApplications extends Component
{
    // Removed unnecessary $applications property
    public $selectedApplication = null;
    public $selectedIds = [];

    // No need to fetch applications in mount; will be done in render()

    public function selectApplication($id)
    {
        $this->selectedApplication = DB::table('organization_unit_applications')
            ->where('id', $id)
            ->first();
    }

    public function approve($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !\Illuminate\Support\Facades\Gate::allows('approve-unit-membership')) {
            abort(403, 'You do not have permission to approve unit memberships.');
        }
        $application = DB::table('organization_unit_applications')->where('id', $id)->first();
        if ($application && $application->status === 'pending') {
            // Create PersonAffiliation
            \App\Models\PersonAffiliation::create([
                'person_id' => $application->person_id,
                'organization_id' => $application->organization_id,
                'role_type' => 'MEMBER',
                'status' => 'active',
                'domain_record_type' => 'unit',
                'domain_record_id' => $application->unit_id,
                'created_by' => $user->id,
                'start_date' => now(),
            ]);
            // Update application status
            DB::table('organization_unit_applications')->where('id', $id)->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user->id,
                'updated_at' => now(),
            ]);
        }
    $this->selectedApplication = null;
    }

    public function reject($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !\Illuminate\Support\Facades\Gate::allows('approve-unit-membership')) {
            abort(403, 'You do not have permission to reject unit memberships.');
        }
        $application = DB::table('organization_unit_applications')->where('id', $id)->first();
        if ($application && $application->status === 'pending') {
            DB::table('organization_unit_applications')->where('id', $id)->update([
                'status' => 'rejected',
                'updated_at' => now(),
            ]);
        }
    $this->selectedApplication = null;
    }

    public function bulkApprove()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !\Illuminate\Support\Facades\Gate::allows('bulk-approve-unit-membership')) {
            abort(403, 'You do not have permission to bulk approve unit memberships.');
        }
        $applications = DB::table('organization_unit_applications')
            ->whereIn('id', $this->selectedIds)
            ->where('status', 'pending')
            ->get();
        foreach ($applications as $application) {
            \App\Models\PersonAffiliation::create([
                'person_id' => $application->person_id,
                'organization_id' => $application->organization_id,
                'role_type' => 'MEMBER',
                'status' => 'active',
                'domain_record_type' => 'unit',
                'domain_record_id' => $application->unit_id,
                'created_by' => $user->id,
                'start_date' => now(),
            ]);
            DB::table('organization_unit_applications')->where('id', $application->id)->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user->id,
                'updated_at' => now(),
            ]);
        }
    $this->selectedIds = [];
    }

    public function bulkReject()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !\Illuminate\Support\Facades\Gate::allows('bulk-approve-unit-membership')) {
            abort(403, 'You do not have permission to bulk reject unit memberships.');
        }
        $applications = DB::table('organization_unit_applications')
            ->whereIn('id', $this->selectedIds)
            ->where('status', 'pending')
            ->get();
        foreach ($applications as $application) {
            DB::table('organization_unit_applications')->where('id', $application->id)->update([
                'status' => 'rejected',
                'updated_at' => now(),
            ]);
        }
    $this->selectedIds = [];
    }

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $orgId = $user && $user->organization_id ? $user->organization_id : null;
        $query = DB::table('organization_unit_applications')
            ->where('status', 'pending');
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }
        $applications = $query->get();
        return view('livewire.organizations.review-unit-applications', [
            'applications' => $applications
        ]);
    }
}
