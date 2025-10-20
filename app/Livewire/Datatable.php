<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;


class Datatable extends Component
{
    use WithPagination;


    // Required: fully-qualified model class, e.g. App\\Models\\Person
    public string $model;


    // Optional: columns to show (array of column keys). Use dotted notation for relations.
    public array $columns = [];


    // Optional: filters config - an array of [field => ['label' => '', 'type' => 'select|text', 'options' => callable|null]]
    public array $filtersConfig = [];


    // Relations to eager-load when fetching rows and for expanded view
    public array $with = [];

    // UI / state
    public string $search = '';
    public array $filters = [];
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public array $selected = [];
    public bool $selectPage = false;
    public bool $selectAll = false;
    public ?string $expandedId = null;


    // Bulk message payload
    public ?string $bulkMessage = null;


    protected $queryString = ['search', 'sortField', 'sortDirection', 'perPage'];


    public function mount(string $model, array $columns = [], array $filtersConfig = [], array $with = [])
    {
        // Validate model
        if (!class_exists($model)) {
            throw new \Exception("Model class {$model} not found.");
        }


        $this->model = $model;
        $this->columns = $columns ?: ['id'];
        $this->filtersConfig = $filtersConfig;
        $this->with = $with;
        // Initialize filter keys
        foreach ($filtersConfig as $key => $cfg) {
            $this->filters[$key] = $cfg['default'] ?? '';
        }
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function updatingPerPage()
    {
        $this->resetPage();
    }


    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selected = $this->rows->pluck('id')->map(fn($v) => (string) $v)->toArray();
        } else {
            $this->selected = [];
            $this->selectAll = false;
        }
    }


    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;
        if ($this->selectAll) {
            // select all ids for the current filter set (careful with very large tables)
            $this->selected = $this->model::query()->when($this->search, fn($q) => $this->applySearch($q))
                ->when(true, fn($q) => $this->applyFilters($q))
                ->pluck('id')->map(fn($v) => (string) $v)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        foreach (array_keys($this->filters) as $k) {
            $this->filters[$k] = '';
        }
        $this->resetPage();
    }


    protected function applySearch($query)
    {
        if (!$this->search) return;


        $s = trim($this->search);


        // Default search across name, id, phone, email if they exist
        $query->where(function ($q) use ($s) {
            $model = new $this->model;
            $columns = ['name', 'person_id', 'phone', 'email'];


            foreach ($columns as $col) {
                if (in_array($col, $model->getFillable()) || $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $col)) {
                    $q->orWhere($col, 'like', "%{$s}%");
                }
            }
        });
    }
    protected function applyFilters($query)
    {
        foreach ($this->filters as $field => $value) {
            if ($value === '' || $value === null) continue;


            // Support nested / relation filters using dot notation
            if (Str::contains($field, '.')) {
                [$relation, $col] = explode('.', $field, 2);
                $query->whereHas($relation, fn($q) => $q->where($col, $value));
            } else {
                $query->where($field, $value);
            }
        }
    }


    // Computed property: rows
    public function getRowsProperty()
    {
        $query = $this->model::query();


        if (!empty($this->with)) {
            $query->with($this->with);
        }


        if ($this->search) {
            $this->applySearch($query);
        }


        $this->applyFilters($query);


        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);
        return $query->paginate($this->perPage);
    }


    public function toggleExpand($id)
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }


    // Bulk action: open send message modal
    public function openSendMessage()
    {
        if (empty($this->selected)) {
            $this->dispatchBrowserEvent('notify', ['type' => 'warning', 'message' => 'No records selected']);
            return;
        }


        $this->bulkMessage = null;
        $this->dispatchBrowserEvent('open-send-message-modal');
    }


    // Perform send message - default behavior: dispatch a job or send notifications
    public function sendBulkMessage()
    {
        $this->validate(['bulkMessage' => 'required|string|min:2']);


        $ids = $this->selected;
        $modelClass = $this->model;
        $receivers = $modelClass::whereIn('id', $ids)->get();


        // TODO: Replace with your notification job / mailer
        foreach ($receivers as $r) {
            // Example: if model is User and has email
            if (isset($r->email) && $r->email) {
                // \Mail::to($r->email)->queue(new \App\Mail\BulkMessageMail($this->bulkMessage, $r));
            }


            // Alternatively dispatch a job: SendMessageJob::dispatch($r, $this->bulkMessage);
        }


        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Message queued for sending']);
        $this->dispatchBrowserEvent('close-send-message-modal');


        // Reset selection
        $this->selected = [];
        $this->selectAll = false;
        $this->selectPage = false;
    }


    public function render()
    {
        return view('livewire.datatable', [
            'rows' => $this->rows,
        ]);
    }
}
