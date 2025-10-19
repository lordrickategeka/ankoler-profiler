<?php

namespace App\Livewire\Organizations;

use App\Models\Organisation;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $showFilters = false;
    public $statusFilter = '';
    public $categoryFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->categoryFilter = '';
        $this->sortField = 'legal_name';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function getOrganizationsProperty()
    {
        return Organisation::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->active();
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($this->statusFilter === 'verified') {
                    $query->verified();
                } elseif ($this->statusFilter === 'trial') {
                    $query->where('is_trial', true);
                }
            })
            ->when($this->categoryFilter, function ($query) {
                $query->byCategory($this->categoryFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        return [
            'hospital' => 'Hospital/Health Facility',
            'school' => 'School/Educational Institution',
            'sacco' => 'SACCO/Financial Cooperative',
            'parish' => 'Parish/Religious Organization',
            'corporate' => 'Corporate/Business',
            'government' => 'Government Agency',
            'ngo' => 'NGO/Non-Profit',
            'other' => 'Other'
        ];
    }

    public function render()
    {
        return view('livewire.organizations.index', [
            'organizations' => $this->organizations,
            'categories' => $this->categories,
        ]);
    }
}
