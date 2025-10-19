<?php

namespace App\Livewire\Communication;

use App\Models\CommunicationMessage;
use App\Services\Communication\CommunicationManager;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MessageHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $channel_filter = '';
    public string $status_filter = '';
    public string $date_from = '';
    public string $date_to = '';
    public int $per_page = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'channel_filter' => ['except' => ''],
        'status_filter' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedChannelFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->channel_filter = '';
        $this->status_filter = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->resetPage();
    }

    public function getMessages()
    {
        $query = CommunicationMessage::query()
            ->with(['recipientPerson', 'sentByUser'])
            ->where('organisation_id', Auth::user()->organisation_id)
            ->orderBy('created_at', 'desc');

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('recipient_identifier', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%')
                  ->orWhereHas('recipientPerson', function ($personQuery) {
                      $personQuery->whereRaw("CONCAT(given_name, ' ', family_name) LIKE ?", ['%' . $this->search . '%']);
                  });
            });
        }

        // Apply channel filter
        if (!empty($this->channel_filter)) {
            $query->where('channel', $this->channel_filter);
        }

        // Apply status filter
        if (!empty($this->status_filter)) {
            $query->where('status', $this->status_filter);
        }

        // Apply date filters
        if (!empty($this->date_from)) {
            $query->where('created_at', '>=', $this->date_from);
        }

        if (!empty($this->date_to)) {
            $query->where('created_at', '<=', $this->date_to . ' 23:59:59');
        }

        return $query->paginate($this->per_page);
    }

    public function getChannelStats()
    {
        $communicationManager = app(CommunicationManager::class);
        return $communicationManager->getOrganisationStats(Auth::user()->organisation_id, 'month');
    }

    public function render()
    {
        return view('livewire.communication.message-history', [
            'messages' => $this->getMessages(),
            'stats' => $this->getChannelStats(),
            'available_channels' => ['email', 'sms', 'whatsapp'],
            'available_statuses' => [
                'pending' => 'Pending',
                'sent' => 'Sent',
                'delivered' => 'Delivered',
                'read' => 'Read',
                'failed' => 'Failed',
                'bounced' => 'Bounced',
                'rejected' => 'Rejected',
            ],
        ]);
    }
}
