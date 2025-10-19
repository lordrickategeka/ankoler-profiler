<div>
    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Export Persons</h3>
                    <p class="mt-1 text-sm text-gray-500">Export person data with customizable filters and formats</p>
                </div>
                <div class="text-sm text-gray-500">
                    @if(!empty($exportStats))
                        <span class="font-medium">{{ number_format($exportStats['total_persons'] ?? 0) }}</span> 
                        person(s) to export
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Organization Selection (Super Admin only) -->
            @if($isSuperAdmin && !empty($availableOrganisations))
                <div class="mb-6">
                    <label for="organisation" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Organization
                    </label>
                    <select wire:model.live="selectedOrganisationId" 
                            id="organisation" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Choose an organization...</option>
                        @foreach($availableOrganisations as $org)
                            <option value="{{ $org['id'] }}">{{ $org['legal_name'] }}</option>
                        @endforeach
                    </select>
                    @error('selectedOrganisationId')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Export Format Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Export Format</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none {{ $exportFormat === 'xlsx' ? 'border-indigo-600 ring-2 ring-indigo-600' : 'border-gray-300' }}">
                        <input type="radio" wire:model.live="exportFormat" value="xlsx" class="sr-only" />
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">Excel (.xlsx)</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">
                                    Rich formatting, styles, multiple sheets
                                </span>
                            </span>
                        </span>
                    </label>
                    <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none {{ $exportFormat === 'csv' ? 'border-indigo-600 ring-2 ring-indigo-600' : 'border-gray-300' }}">
                        <input type="radio" wire:model.live="exportFormat" value="csv" class="sr-only" />
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">CSV (.csv)</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">
                                    Simple format, universal compatibility
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Field Selection -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-medium text-gray-700">Fields to Export</label>
                    <div class="flex space-x-2">
                        <button type="button" wire:click="selectAllFields" 
                                class="text-xs text-indigo-600 hover:text-indigo-800">
                            Select All
                        </button>
                        <span class="text-xs text-gray-400">|</span>
                        <button type="button" wire:click="selectDefaultFields" 
                                class="text-xs text-indigo-600 hover:text-indigo-800">
                            Default Only
                        </button>
                    </div>
                </div>
                
                @if(!empty($availableFieldOptions))
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto border border-gray-200 rounded-md p-3">
                        @foreach($availableFieldOptions as $fieldKey => $field)
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.live="includeFields" 
                                       value="{{ $fieldKey }}" 
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="ml-2 text-sm text-gray-700">
                                    {{ $field['label'] }}
                                    @if(isset($field['default']) && $field['default'])
                                        <span class="text-green-600 text-xs ml-1">(Default)</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                
                @error('includeFields')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Filters Section -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-medium text-gray-700">Filters</label>
                    <div class="flex space-x-2">
                        <button type="button" wire:click="toggleAdvancedFilters" 
                                class="text-xs text-indigo-600 hover:text-indigo-800">
                            {{ $showAdvancedFilters ? 'Hide Advanced' : 'Show Advanced' }}
                        </button>
                        @if(!empty($filters))
                            <span class="text-xs text-gray-400">|</span>
                            <button type="button" wire:click="clearFilters" 
                                    class="text-xs text-red-600 hover:text-red-800">
                                Clear All
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Basic Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Role Type Filter -->
                    @if(isset($availableFilterOptions['role_types']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role Type</label>
                            <select wire:model.live="filters.role_type" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Types</option>
                                @foreach($availableFilterOptions['role_types'] as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Gender Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select wire:model.live="filters.gender" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">Prefer not to say</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model.live="filters.status" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters -->
                @if($showAdvancedFilters)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Age Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Age From</label>
                                <input type="number" 
                                       wire:model.live="filters.age_from" 
                                       min="0" max="120" 
                                       placeholder="Min age"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                @error('filters.age_from')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Age To</label>
                                <input type="number" 
                                       wire:model.live="filters.age_to" 
                                       min="0" max="120" 
                                       placeholder="Max age"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                @error('filters.age_to')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Location Filters -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" 
                                       wire:model.live="filters.city" 
                                       placeholder="Filter by city"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                <input type="text" 
                                       wire:model.live="filters.district" 
                                       placeholder="Filter by district"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Export Stats -->
            @if(!empty($exportStats) && $exportStats['total_persons'] > 0)
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Export Preview</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Total Records:</span>
                            <span class="font-medium text-gray-900 ml-1">{{ number_format($exportStats['total_persons']) }}</span>
                        </div>
                        @if(isset($exportStats['by_gender']))
                            <div>
                                <span class="text-gray-500">Male:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ number_format($exportStats['by_gender']['male'] ?? 0) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Female:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ number_format($exportStats['by_gender']['female'] ?? 0) }}</span>
                            </div>
                        @endif
                        @if(isset($exportStats['estimated_size']))
                            <div>
                                <span class="text-gray-500">Est. Size:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ $exportStats['estimated_size'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    @if(session()->has('message'))
                        <div class="text-green-600">
                            {{ session('message') }}
                        </div>
                    @endif
                    @error('export')
                        <div class="text-red-600">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset
                    </button>

                    <button type="button"
                            wire:click="exportPersons"
                            wire:loading.attr="disabled"
                            wire:target="exportPersons"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        
                        <span wire:loading.remove wire:target="exportPersons">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export {{ ucfirst($exportFormat) }}
                        </span>
                        
                        <span wire:loading wire:target="exportPersons">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>