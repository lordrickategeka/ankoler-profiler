<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class WorkflowTable extends Component
{
    public $workflows = [];
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function mount()
    {
        $this->loadWorkflows();
    }

    public function loadWorkflows()
    {
        // Sample data - replace with actual database query
        $this->workflows = [
            [
                'id' => 1,
                'name' => 'Demo workflow',
                'icon' => 'twilio',
                'color' => 'bg-red-500',
                'status' => 'Active',
                'fleet' => 'Fleet',
                'customer' => 'Backwoods',
                'greystanes' => 'Greystanes',
                'location' => 'NSW',
                'type' => 'AC'
            ],
            [
                'id' => 2,
                'name' => 'Demo workflow',
                'icon' => 'mailchimp',
                'color' => 'bg-yellow-500',
                'status' => 'Active',
                'fleet' => 'Fleet',
                'customer' => 'Backwoods',
                'greystanes' => 'Greystanes',
                'location' => 'NSW',
                'type' => 'AC'
            ],
            [
                'id' => 3,
                'name' => 'Demo workflow',
                'icon' => 'drive',
                'color' => 'bg-blue-600',
                'status' => 'Active',
                'fleet' => 'Fleet',
                'customer' => 'Backwoods',
                'greystanes' => 'Greystanes',
                'location' => 'NSW',
                'type' => 'AC'
            ],
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Add sorting logic here
    }

    public function addNew()
    {
        // Redirect to create workflow page
        $this->dispatch('open-workflow-modal');
    }

    public function editWorkflow($id)
    {
        $this->dispatch('edit-workflow', workflowId: $id);
    }

    public function duplicateWorkflow($id)
    {
        // Handle duplication
        session()->flash('message', 'Workflow duplicated successfully');
        $this->loadWorkflows();
    }

    public function viewDetails($id)
    {
        $this->dispatch('view-workflow-details', workflowId: $id);
    }

    public function deleteWorkflow($id)
    {
        // Handle deletion with confirmation
        $this->dispatch('confirm-delete', workflowId: $id);
    }
    
    public function render()
    {
        return view('livewire.dashboard.workflow-table');
    }
}
