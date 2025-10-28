<?php

namespace App\Livewire\Person;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    use WithPagination;

    public $recentActivities = [];

    public function mount()
    {
        $this->recentActivities = $this->getRecentActivities();
    }

    public function render()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(10);
        return view('livewire.person.notifications', [
            'notifications' => $notifications,
            'recentActivities' => $this->recentActivities,
        ]);
    }

    private function getRecentActivities()
    {
        $activities = [];
        $user = Auth::user();
        $orgId = $user->organisation_id;

        // Recent person registrations in user's org
        $recentPersons = \App\Models\Person::with('affiliations.organisation')
            ->whereHas('affiliations', function($q) use ($orgId) {
                $q->where('organisation_id', $orgId);
            })
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentPersons as $person) {
            $affiliation = $person->affiliations->first();
            $orgName = $affiliation && $affiliation->organisation ? ($affiliation->organisation->display_name ?: $affiliation->organisation->legal_name) : 'Unknown Organization';
            $activities[] = [
                'type' => 'person',
                'title' => 'New person "' . $person->full_name . '" registered',
                'description' => 'Complete profile with organizational affiliation to ' . $orgName,
                'time' => $person->created_at->diffForHumans(),
                'badge' => 'Person',
                'badge_color' => 'success',
                'icon' => 'user-group',
            ];
        }

        // Recent affiliations in user's org
        $recentAffiliations = \App\Models\PersonAffiliation::with(['person', 'organisation'])
            ->where('organisation_id', $orgId)
            ->where('status', 'active')
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentAffiliations as $affiliation) {
            $orgDisplayName = $affiliation->organisation ? ($affiliation->organisation->display_name ?: $affiliation->organisation->legal_name) : 'Unknown Organization';
            $activities[] = [
                'type' => 'affiliation',
                'title' => 'New affiliation verified',
                'description' => $affiliation->person->full_name . ' affiliated with ' . $orgDisplayName,
                'time' => $affiliation->created_at->diffForHumans(),
                'badge' => 'Affiliation',
                'badge_color' => 'secondary',
                'icon' => 'link',
            ];
        }

        // Sort by time (most recent first)
        usort($activities, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });

        return array_slice($activities, 0, 6);
    }
}
