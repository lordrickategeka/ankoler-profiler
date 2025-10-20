{{-- resources/views/persons/search.blade.php --}}
@extends('layouts.app')

@section('title', 'Person Search')

@section('content')
<!-- Header -->
{{-- <header class="gradient-bg pattern-overlay shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-md">
                    <i class="fas fa-users text-primary-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">Person Search</h1>
                    <p class="text-blue-100 text-sm">Find and manage person records</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <button class="glass-effect text-white px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-20 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span class="hidden sm:inline">Export</span>
                </button>
                @if(Route::has('persons.create'))
                    <a href="{{ route('persons.create') }}" class="bg-white text-primary-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center space-x-2 shadow-md">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Add Person</span>
                    </a>
                @else
                    <button onclick="alert('Add Person route not configured yet')" class="bg-white text-primary-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center space-x-2 shadow-md">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Add Person</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</header> --}}

<div class="container mx-auto px-4 py-6">
    <!-- Quick Stats Bar -->
    {{-- <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm hover-lift">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Persons</p>
                    <p class="text-xl font-bold text-gray-900">
                        @if(class_exists('App\Models\Person'))
                            {{ \App\Models\Person::count() }}
                        @else
                            2,847
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm hover-lift">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-xl font-bold text-gray-900">
                        @if(class_exists('App\Models\Person'))
                            {{ \App\Models\Person::where('status', 'active')->count() }}
                        @else
                            2,643
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm hover-lift">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-xl font-bold text-gray-900">
                        @if(class_exists('App\Models\Person'))
                            {{ \App\Models\Person::whereMonth('created_at', now()->month)->count() }}
                        @else
                            156
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm hover-lift">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Found</p>
                    <p class="text-xl font-bold text-gray-900" id="search-count">847</p>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Main Search Section -->
    <div class="grid grid-cols-2 MD:grid-cols-4 gap-4 mb-6">
        <!-- Results Section -->
        <div class="lg:col-span-3">
            <!-- Results List Container -->
            <div id="resultsContainer" class="space-y-4">
                @if(class_exists('App\Livewire\PersonSearch'))
                    @livewire('person-search')
                @else
                    <!-- Sample Result Items (Static for now) -->
                    @include('persons.search-results')
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick View Modal (Hidden by default) -->
<div id="quickViewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Person Details</h3>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeQuickView()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6" id="modalContent">
                <!-- Modal content will be loaded here -->
                <p class="text-gray-600">Loading person details...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentView = 'list';
let searchFilters = {
    search: '',
    searchType: 'global',
    status: ['active'],
    gender: '',
    ageFrom: '',
    ageTo: '',
    city: '',
    district: '',
    country: ''
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    setupEventListeners();
});

function initializeSearch() {
    // Search functionality
    const searchInput = document.getElementById('quickSearch');
    const searchCount = document.getElementById('search-count');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            // Simulate search count update
            const value = this.value.length;
            const newCount = Math.max(0, 847 - (value * 23));
            searchCount.textContent = newCount;
            document.getElementById('resultsCount').textContent = newCount + ' found';
        }, 300));
    }
    
    // Animate elements on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fade-in 0.5s ease-in-out';
            }
        });
    });
    
    document.querySelectorAll('.result-item').forEach(item => {
        observer.observe(item);
    });
}

function setupEventListeners() {
    // Search type change
    document.getElementById('searchType').addEventListener('change', function() {
        searchFilters.searchType = this.value;
        performSearch();
    });
    
    // Status checkboxes
    document.querySelectorAll('input[name="status"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                if (!searchFilters.status.includes(this.value)) {
                    searchFilters.status.push(this.value);
                }
            } else {
                searchFilters.status = searchFilters.status.filter(s => s !== this.value);
            }
            performSearch();
        });
    });
    
    // Gender radio buttons
    document.querySelectorAll('input[name="gender"]').forEach(radio => {
        radio.addEventListener('change', function() {
            searchFilters.gender = this.value;
            performSearch();
        });
    });
    
    // Age range inputs
    document.getElementById('ageFrom').addEventListener('input', debounce(function() {
        searchFilters.ageFrom = this.value;
        performSearch();
    }, 500));
    
    document.getElementById('ageTo').addEventListener('input', debounce(function() {
        searchFilters.ageTo = this.value;
        performSearch();
    }, 500));
}

function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    const chevron = document.getElementById('advancedChevron');
    
    if (advancedFilters.classList.contains('hidden')) {
        advancedFilters.classList.remove('hidden');
        chevron.classList.remove('fa-chevron-down');
        chevron.classList.add('fa-chevron-up');
    } else {
        advancedFilters.classList.add('hidden');
        chevron.classList.remove('fa-chevron-up');
        chevron.classList.add('fa-chevron-down');
    }
}

function clearAllFilters() {
    // Reset form elements
    document.getElementById('quickSearch').value = '';
    document.getElementById('searchType').value = 'global';
    document.querySelectorAll('input[name="status"]').forEach(cb => {
        cb.checked = cb.value === 'active';
    });
    document.querySelector('input[name="gender"][value=""]').checked = true;
    document.getElementById('ageFrom').value = '';
    document.getElementById('ageTo').value = '';
    document.getElementById('city').value = '';
    document.getElementById('district').value = '';
    document.getElementById('country').value = '';
    
    // Reset search filters
    searchFilters = {
        search: '',
        searchType: 'global',
        status: ['active'],
        gender: '',
        ageFrom: '',
        ageTo: '',
        city: '',
        district: '',
        country: ''
    };
    
    performSearch();
}

function setView(view) {
    currentView = view;
    const listBtn = document.getElementById('listViewBtn');
    const gridBtn = document.getElementById('gridViewBtn');
    
    if (view === 'list') {
        listBtn.className = 'bg-white shadow-sm px-3 py-1 rounded-md text-sm font-medium text-gray-700';
        gridBtn.className = 'px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700';
    } else {
        gridBtn.className = 'bg-white shadow-sm px-3 py-1 rounded-md text-sm font-medium text-gray-700';
        listBtn.className = 'px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700';
    }
    
    // Update results display
    updateResultsDisplay();
}

function performSearch() {
    // This would typically make an AJAX call to your Laravel backend
    console.log('Performing search with filters:', searchFilters);
    
    // Simulate search delay
    showLoadingState();
    
    setTimeout(() => {
        hideLoadingState();
        updateResultsDisplay();
    }, 500);
}

function updateResultsDisplay() {
    // This would update the results based on current view and filters
    console.log('Updating results display for view:', currentView);
}

function showLoadingState() {
    const container = document.getElementById('resultsContainer');
    container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="text-gray-500 mt-2">Searching...</p></div>';
}

function hideLoadingState() {
    // This would restore the actual results
    console.log('Search completed');
}

function exportSelected() {
    // Get selected items
    const selected = document.querySelectorAll('input[type="checkbox"]:checked').length;
    if (selected > 0) {
        alert(`Exporting ${selected} selected persons...`);
    } else {
        alert('Please select persons to export.');
    }
}

function openQuickView(personId) {
    document.getElementById('quickViewModal').classList.remove('hidden');
    // Load person data via AJAX
    console.log('Loading person:', personId);
}

function closeQuickView() {
    document.getElementById('quickViewModal').classList.add('hidden');
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush