<div class="h-[calc(100vh-8rem)] overflow-y-auto" wire:key="person-list-component">
    <div class="p-8">

        {{-- Search and Filters --}}
        {{-- <div class="bg-white rounded-lg shadow p-4 mb-4"> --}}
        {{-- <div class="flex justify-between items-center mb-3"> --}}
        {{-- <div class="flex items-center">
                    <h2 class="text-base font-semibold">Search</h2>
                    Loading indicator for filter operations
                    <div wire:loading wire:target="filters,dynamicFilters,updatedFilters,updatedDynamicFilters"
                        class="ml-3">
                        <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div> --}}

        {{-- <button wire:click="toggleAdvancedFilters" type="button"
                    class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none">
                    {{ $showAdvancedFilters ? 'Hide Advanced Filters' : 'Show Advanced Filters' }}
                </button> --}}
        {{-- </div> --}}

        {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.500ms.debounce.300ms="filters.search"
                            placeholder="Search by name, phone, or email..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <div wire:loading wire:target="filters.search" class="absolute right-3 top-2">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Classification</label>
                    <div class="relative">
                        <select wire:model.live.debounce.500ms="filters.classification"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Classifications</option>
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                        <div wire:loading wire:target="filters.classification" class="absolute right-8 top-2">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                @if ($isSuperAdmin)
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Organization</label>
                        <div class="relative">
                            <select wire:model.live.debounce.500ms="filters.organisation_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">All Organizations</option>
                                @foreach ($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->display_name ?? $org->legal_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div wire:loading wire:target="filters.organisation_id" class="absolute right-8 top-2">
                                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($showAdvancedFilters)
                <div class="border-t pt-3 mt-3 relative">
                    <div wire:loading wire:target="filters.gender,filters.age_range,filters.status,filters.date_range"
                        class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 rounded-md">
                        <div class="flex items-center space-x-2">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-sm text-blue-600">Applying filters...</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                            <select wire:model.live.debounce.500ms="filters.gender"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">All Genders</option>
                                @foreach ($genderOptions as $gender)
                                    <option value="{{ $gender }}">{{ ucfirst(str_replace('_', ' ', $gender)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Age Range</label>
                            <select wire:model.live.debounce.500ms="filters.age_range"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">All Ages</option>
                                @foreach ($ageRanges as $range)
                                    <option value="{{ $range }}">{{ $range }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model.live.debounce.500ms="filters.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">All Statuses</option>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Created From</label>
                            <input type="date" wire:model.live.debounce.500ms="filters.date_range.start"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Created To</label>
                            <input type="date" wire:model.live.debounce.500ms="filters.date_range.end"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>

                        @foreach ($filterConfigurations as $config)
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 mb-1">{{ ucfirst(str_replace('_', ' ', $config->field_name)) }}</label>
                                @if ($config->field_type === 'select')
                                    <select wire:model.live.debounce.500ms="dynamicFilters.{{ $config->field_name }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                        <option value="">All Options</option>
                                        @foreach ($config->options as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($config->field_type === 'date')
                                    <input type="date"
                                        wire:model.live.debounce.500ms="dynamicFilters.{{ $config->field_name }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @elseif($config->field_type === 'number')
                                    <input type="number"
                                        wire:model.live.debounce.500ms="dynamicFilters.{{ $config->field_name }}"
                                        placeholder="Enter {{ str_replace('_', ' ', $config->field_name) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @else
                                    <input type="text"
                                        wire:model.live.debounce.500ms="dynamicFilters.{{ $config->field_name }}"
                                        placeholder="Enter {{ str_replace('_', ' ', $config->field_name) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div wire:loading wire:target="dynamicFilters" class="mt-2">
                        <div class="flex items-center text-blue-600 text-sm">
                            <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Applying dynamic filters...
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex justify-between items-center mt-3 pt-3 border-t">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        @php
                            $activeFilters =
                                collect($filters)
                                    ->filter(function ($value, $key) {
                                        if ($key === 'date_range') {
                                            return !empty($value['start']) || !empty($value['end']);
                                        }
                                        return !empty($value);
                                    })
                                    ->count() + collect($dynamicFilters)->filter()->count();
                        @endphp
                        <span wire:loading.remove wire:target="resetFilters">
                            {{ $activeFilters }} filter(s) active
                        </span>
                        <span wire:loading wire:target="resetFilters" class="text-blue-600 animate-pulse">
                            Resetting filters...
                        </span>
                    </div>

                    @if ($activeFilters > 0)
                        <div class="flex flex-wrap gap-1" wire:loading.remove wire:target="resetFilters">
                            @if (!empty($filters['search']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Search: "{{ Str::limit($filters['search'], 15) }}"
                                </span>
                            @endif
                            @if (!empty($filters['classification']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $filters['classification'] }}
                                </span>
                            @endif
                            @if (!empty($filters['organisation_id']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Organization
                                </span>
                            @endif
                            @if (!empty($filters['gender']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                    {{ ucfirst($filters['gender']) }}
                                </span>
                            @endif
                            @if (!empty($filters['age_range']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Age: {{ $filters['age_range'] }}
                                </span>
                            @endif
                            @if (!empty($filters['status']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ ucfirst($filters['status']) }}
                                </span>
                            @endif
                            @if (!empty($filters['date_range']['start']) || !empty($filters['date_range']['end']))
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Date Range
                                </span>
                            @endif
                            @foreach ($dynamicFilters as $field => $value)
                                @if (!empty($value))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ ucfirst(str_replace('_', ' ', $field)) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="hidden" id="resetFiltersLoader">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>

                <button onclick="quickResetFilters()" type="button" id="resetFiltersBtn"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50 flex items-center focus:outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Reset All Filters
                </button>
            </div> --}}
        {{-- </div> --}}

        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <div wire:loading wire:target="filters,dynamicFilters,updatedFilters,updatedDynamicFilters"
                        class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2 animate-pulse" title="Searching..."></div>
                        <p class="text-gray-600 animate-pulse">
                            <span class="font-semibold text-blue-600">Searching...</span> Please wait
                        </p>
                    </div>

                    <div wire:loading.remove wire:target="filters,dynamicFilters,updatedFilters,updatedDynamicFilters"
                        class="flex items-center">
                        @if ($persons->count() > 0)
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2" title="Results found"></div>
                            <p class="text-gray-600">
                                Showing <span class="font-semibold text-gray-900">{{ $persons->count() }}</span>
                                of <span class="font-semibold text-gray-900">{{ $persons->total() }}</span> persons
                            </p>
                        @else
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2" title="No results"></div>
                            <p class="text-gray-600">No persons found</p>
                        @endif
                    </div>
                </div>

                <div wire:loading.remove wire:target="filters,dynamicFilters" class="text-xs text-gray-400">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Ready
                </div>
                <div wire:loading wire:target="filters,dynamicFilters" class="text-xs text-blue-600 animate-pulse">
                    <svg class="animate-spin w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Applying filters...
                </div>
            </div>
            <div class="flex space-x-2">
                {{-- Export Button --}}
                @can('export-org-persons')
                    <a href="{{ route('persons.export') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </a>
                @endcan

                {{-- Import Button --}}
                @can('import-org-persons')
                    <a href="{{ route('persons.import') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                        Import
                    </a>
                @endcan

                {{-- Add New Person Button --}}
                <a href="{{ route('person-search') }}" style="background-color:#01fea1"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white hover:bg-[#01bafe]">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-white">Search Person</span>
                </a>
                <a href="{{ route('persons.create') }}" style="background-color:#01bafe"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white hover:bg-[#01bafe]">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-white">Add New Person</span>
                </a>
            </div>
        </div>

        {{-- Persons Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden relative">
            {{-- Loading Overlay for Filter Results --}}
            <div wire:loading wire:target="filters,dynamicFilters,updatedFilters,updatedDynamicFilters"
                class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-20">
                <div class="text-center">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <div class="text-sm text-gray-600 font-medium">Loading filtered results...</div>
                    <div class="text-xs text-gray-400 mt-1">Please wait while we search the database</div>
                </div>
            </div>

            @if ($persons->count() > 0)
                <div class="overflow-x-auto" wire:key="persons-table">
                    <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                        <thead class="bg-gray-50 text-[11px]">
                            <tr>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">#
                                </th>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Person</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Contact Information</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Affiliations</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Classifications</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Location</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-[11px]">
                            @foreach ($persons as $index => $person)
                                <tr class="hover:bg-gray-50">
                                    {{-- Person Info --}}
                                    <td class="px-1.5 py-1 whitespace-nowrap">{{ $persons->firstItem() + $index }}
                                    </td>
                                    <td class="px-1.5 py-1 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center relative">
                                                    <span
                                                        class="text-blue-600 font-medium text-sm">{{ substr($person->given_name, 0, 1) }}{{ substr($person->family_name, 0, 1) }}</span>

                                                    {{-- Profile completeness indicator --}}
                                                    @php
                                                        $completeness = 0;
                                                        if ($person->phones->count() > 0) {
                                                            $completeness += 25;
                                                        }
                                                        if ($person->emailAddresses->count() > 0) {
                                                            $completeness += 25;
                                                        }
                                                        if (
                                                            $person->affiliations->where('status', 'active')->count() >
                                                            0
                                                        ) {
                                                            $completeness += 25;
                                                        }
                                                        if (
                                                            !empty($person->classification)
                                                        ) {
                                                            $completeness += 25;
                                                        }

                                                        $color =
                                                            $completeness >= 75
                                                                ? 'green'
                                                                : ($completeness >= 50
                                                                    ? 'yellow'
                                                                    : 'red');
                                                    @endphp
                                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white
                                                        {{ $color === 'green' ? 'bg-green-500' : ($color === 'yellow' ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                        title="Profile {{ $completeness }}% complete">
                                                        <div
                                                            class="w-full h-full rounded-full flex items-center justify-center">
                                                            @if ($completeness >= 75)
                                                                <svg class="w-2.5 h-2.5 text-white"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                            @elseif($completeness >= 50)
                                                                <svg class="w-2.5 h-2.5 text-white"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                            @else
                                                                <svg class="w-2.5 h-2.5 text-white"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $person->full_name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $person->person_id }}</div>
                                                @if ($person->date_of_birth)
                                                    <div class="text-xs text-gray-400">Born:
                                                        @php
                                                            $dob = $person->date_of_birth;
                                                            if (is_string($dob)) {
                                                                try {
                                                                    $dob = \Carbon\Carbon::parse($dob);
                                                                } catch (Exception $e) {
                                                                    $dob = null;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $dob ? $dob->format('M j, Y') : 'N/A' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Contact Info --}}
                                    <td class="px-1.5 py-1 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <div>
                                                <span class="font-semibold text-gray-600 text-xs">Phone:</span>
                                                @if ($person->phones->count() > 0)
                                                    <span class="text-xs text-gray-700">{{ $person->phones->pluck('number')->join(', ') }}</span>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">No phone</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-600 text-xs">Email:</span>
                                                @if ($person->emailAddresses->count() > 0)
                                                    <span class="text-xs text-gray-700">{{ $person->emailAddresses->pluck('email')->join(', ') }}</span>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">No email</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Affiliations --}}
                                    <td class="px-1.5 py-1">
                                        <div class="text-sm text-gray-900">
                                            @php
                                                $activeAffiliations = $person->affiliations->where('status', 'active');
                                                $totalAffiliations = $person->affiliations->count();
                                            @endphp
                                            @forelse($activeAffiliations as $affiliation)
                                                <div class="mb-1 flex items-start">
                                                    <div class="flex items-center mr-2">
                                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"
                                                            title="Active affiliation"></div>
                                                    </div>
                                                    <div class="flex-1">
                                                        <span
                                                            class="font-medium">{{ $affiliation->organisation->display_name ?? $affiliation->organisation->legal_name }}</span>

                                                        @if ($affiliation->role_title)
                                                            <div class="text-xs text-gray-400">
                                                                {{ $affiliation->role_title }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-gray-400 rounded-full mr-1"
                                                        title="No active affiliations"></div>
                                                    <span class="text-gray-400 italic">No active affiliations</span>
                                                </div>
                                            @endforelse

                                            @if ($totalAffiliations > $activeAffiliations->count())
                                                <div class="text-xs text-orange-600 mt-1">
                                                    {{ $totalAffiliations - $activeAffiliations->count() }} inactive
                                                    affiliation(s)
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    {{-- Classifications --}}
                                    <td class="px-1.5 py-1 whitespace-nowrap">
                                        {{ $affiliation->role_type }}
                                        {{-- @if ($person->classification)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($person->classification as $class)
                                                    @php
                                                        $badgeColor = match (strtoupper($class)) {
                                                            'STAFF' => 'bg-blue-100 text-blue-800',
                                                            'STUDENT' => 'bg-green-100 text-green-800',
                                                            'PARENT' => 'bg-purple-100 text-purple-800',
                                                            'VISITOR' => 'bg-orange-100 text-orange-800',
                                                            'ALUMNI' => 'bg-gray-100 text-gray-800',
                                                            default => 'bg-indigo-100 text-indigo-800',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                                        <div class="w-1.5 h-1.5 bg-current rounded-full mr-1"></div>
                                                        {{ $class }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1"
                                                    title="No classification assigned"></div>
                                                <span class="text-gray-400 italic text-xs">No classification</span>
                                            </div>
                                        @endif --}}
                                    </td>
                                    <td class="px-1.5 py-1 whitespace-nowrap">
                                        @if ($person->country || $person->city)
                                            <div class="flex flex-wrap gap-1">
                                                <div>{{ $person->city }}</div>
                                                @if ($person->district)
                                                    <div class="text-gray-500">{{ $person->district }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1"
                                                    title="No classification assigned"></div>
                                                <span class="text-gray-400 italic text-xs">No Location</span>
                                            </div>
                                        @endif
                                    </td>
                                    {{-- Actions --}}
                                    <td class="px-1.5 py-1 whitespace-nowrap text-right font-medium">
                                        <div x-data="{ open: false }" class="relative flex justify-end"
                                            @click.away="open = false">
                                            <button @click="open = !open" type="button"
                                                class="p-1 rounded-full hover:bg-gray-200 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <circle cx="10" cy="4" r="1.5" />
                                                    <circle cx="10" cy="10" r="1.5" />
                                                    <circle cx="10" cy="16" r="1.5" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute right-0 mt-8 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
                                                style="display: none;">
                                                <div class="py-1">
                                                    <button
                                                        wire:click="$dispatch('show-person-profile', { personId: {{ $person->id }} })"
                                                        @click="open = false" type="button"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                            </path>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                            </path>
                                                        </svg>
                                                        View Profile
                                                    </button>
                                                    <a href="{{ route('persons.create', ['edit' => $person->id]) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" />
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <button type="button" @click="open = false"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Add Affiliation
                                                    </button>
                                                    @can('delete-persons')
                                                        <button wire:click="confirmDelete({{ $person->id }})"
                                                            @click="open = false" type="button"
                                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-red-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                            Delete
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                @if ($persons->hasPages())
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200" wire:key="pagination">
                        {{ $persons->links() }}
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No persons found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @php
                            $hasActiveFilters =
                                collect($filters)
                                    ->filter(function ($value, $key) {
                                        if ($key === 'date_range') {
                                            return !empty($value['start']) || !empty($value['end']);
                                        }
                                        return !empty($value);
                                    })
                                    ->count() +
                                    collect($dynamicFilters)->filter()->count() >
                                0;
                        @endphp
                        @if ($hasActiveFilters)
                            Try adjusting your search criteria or filters.
                        @else
                            Get started by adding a new person.
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('persons.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Add New Person
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Include Profile View Modal --}}
    @livewire('person.profile-view')

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete">
                </div>

                <!-- Center the modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete Person
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this person? This action cannot be undone. All
                                    associated data including phone numbers, email addresses, and affiliations will be
                                    permanently removed.
                                </p>
                                @if ($personToDelete)
                                    <div class="mt-3 p-3 bg-red-50 rounded-md border border-red-200">
                                        <p class="text-sm font-medium text-gray-900">{{ $personToDelete->full_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">ID: {{ $personToDelete->person_id }}</p>
                                        @if ($personToDelete->phones->count() > 0 || $personToDelete->emailAddresses->count() > 0)
                                            <div class="mt-2 text-xs text-gray-600">
                                                <p>This will also delete:</p>
                                                <ul class="list-disc list-inside mt-1">
                                                    @if ($personToDelete->phones->count() > 0)
                                                        <li>{{ $personToDelete->phones->count() }} phone number(s)</li>
                                                    @endif
                                                    @if ($personToDelete->emailAddresses->count() > 0)
                                                        <li>{{ $personToDelete->emailAddresses->count() }} email
                                                            address(es)</li>
                                                    @endif
                                                    @if ($personToDelete->affiliations->count() > 0)
                                                        <li>{{ $personToDelete->affiliations->count() }} affiliation(s)
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-3 p-3 bg-yellow-50 rounded-md border border-yellow-200">
                                        <p class="text-sm text-yellow-800">Loading person details...</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                        <button wire:click="deletePerson" type="button" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="deletePerson">Delete</span>
                            <span wire:loading wire:target="deletePerson" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Deleting...
                            </span>
                        </button>
                        <button wire:click="cancelDelete" type="button" wire:loading.attr="disabled"
                            wire:target="deletePerson"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    @push('scripts')
        <script>
            // Listen for filters-reset event
            window.addEventListener('filters-reset', () => {
                // Show success toast
                console.log('Filters reset successfully');
            });

            // Quick reset function for instant feedback
            //     function quickResetFilters() {
            //         // Show loading state immediately
            //         const button = event.target.closest('button');
            //         const originalContent = button.innerHTML;
            //         button.disabled = true;
            //         button.innerHTML = `
    //     <svg class="animate-spin w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24">
    //         <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    //         <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    //     </svg>
    //     Resetting...
    // `;

            function quickResetFilters() {
                const resetBtn = document.getElementById('resetFiltersBtn');
                const loader = document.getElementById('resetFiltersLoader');

                // Disable the button and show loader
                resetBtn.disabled = true;
                loader.classList.remove('hidden');

                // Call the Livewire method to reset filters
                @this.resetFilters().then(() => {
                    // Re-enable the button and hide loader after reset
                    resetBtn.disabled = false;
                    loader.classList.add('hidden');
                });
            }

            // Clear all form inputs instantly for better UX
            document.querySelectorAll('input[type="text"], input[type="date"], select').forEach(input => {
                if (input.hasAttribute('wire:model.live.debounce.500ms') || input.hasAttribute('wire:model')) {
                    input.value = '';
                }
            });

            // Let Livewire handle the actual reset
            @this.resetFilters().then(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
            });
            }
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                // Re-initialize Alpine components after Livewire updates
                Livewire.hook('morph.updated', ({
                    el,
                    component
                }) => {
                    // Re-scan for Alpine components
                    if (window.Alpine) {
                        Alpine.initTree(el);
                    }
                });

                // Handle dropdown state persistence
                let openDropdowns = new Set();

                Livewire.hook('morph.updating', ({
                    el,
                    toEl,
                    childrenOnly,
                    skip
                }) => {
                    // Save open dropdown states
                    el.querySelectorAll('[x-data*="open"]').forEach((dropdown, index) => {
                        const alpine = Alpine.$data(dropdown);
                        if (alpine && alpine.open) {
                            openDropdowns.add(index);
                        }
                    });
                });

                Livewire.hook('morph.updated', ({
                    el,
                    component
                }) => {
                    // Restore dropdown states
                    setTimeout(() => {
                        el.querySelectorAll('[x-data*="open"]').forEach((dropdown, index) => {
                            if (openDropdowns.has(index)) {
                                const alpine = Alpine.$data(dropdown);
                                if (alpine) {
                                    alpine.open = false; // Close them to prevent issues
                                }
                            }
                        });
                        openDropdowns.clear();
                    }, 50);
                });
            });

            // Alternative approach: Use vanilla JavaScript for dropdowns
            function toggleDropdown(personId) {
                const dropdown = document.querySelector(`[data-dropdown="${personId}"]`);
                const isOpen = dropdown.style.display === 'block';

                // Close all dropdowns first
                document.querySelectorAll('[data-dropdown]').forEach(d => {
                    d.style.display = 'none';
                });

                // Toggle the clicked one
                if (!isOpen) {
                    dropdown.style.display = 'block';
                }
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('[data-dropdown-trigger]')) {
                    document.querySelectorAll('[data-dropdown]').forEach(d => {
                        d.style.display = 'none';
                    });
                }
            });
        </script>
    @endpush
</div>
