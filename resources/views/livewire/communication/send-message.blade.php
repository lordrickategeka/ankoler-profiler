<div>
    <!-- Progress Bar -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Send Communication</h3>
                <div class="text-sm text-gray-500">
                    Step {{ $currentStep }} of {{ $totalSteps }}
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex items-center">
                    @foreach($step_names as $index => $stepName)
                        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium
                                {{ ($index + 1) <= $currentStep ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="ml-2 text-sm font-medium {{ ($index + 1) <= $currentStep ? 'text-indigo-600' : 'text-gray-500' }}">
                                {{ $stepName }}
                            </div>
                            @if(!$loop->last)
                                <div class="flex-1 h-1 mx-4 {{ ($index + 1) < $currentStep ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Step Content -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">

            <!-- STEP 1: RECIPIENT SELECTION -->
            @if($currentStep === 1)
                <div class="space-y-6">
                    <!-- Super Admin Notice -->
                    @if(auth()->user() && auth()->user()->hasRole('Super Admin'))
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                            <div class="flex">
                                <i class="fas fa-crown text-blue-500 mr-2 mt-1"></i>
                                <p class="text-sm text-blue-700">
                                    <strong>Super Admin Access:</strong> You can search and communicate with persons from all organizations.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Select Recipients</h4>

                        <!-- Selection Mode Tabs -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button wire:click="setSelectionMode('search')"
                                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $selectionMode === 'search' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                                    <i class="fas fa-search mr-2"></i> Search Persons
                                </button>
                                <button wire:click="setSelectionMode('filter_profile')"
                                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $selectionMode === 'filter_profile' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                                    <i class="fas fa-filter mr-2"></i> Filter Profiles
                                </button>
                                <button wire:click="setSelectionMode('all')"
                                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $selectionMode === 'all' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                                    <i class="fas fa-users mr-2"></i> All Persons
                                </button>
                            </nav>
                        </div>

                        <!-- Search Mode -->
                        @if($selectionMode === 'search')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Search for persons</label>
                                    <input wire:model.live.debounce.300ms="person_search"
                                           type="text"
                                           placeholder="Type name to search..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <!-- Search Results -->
                                @if(!empty($search_results))
                                    <div class="border border-gray-200 rounded-lg divide-y divide-gray-200 max-h-96 overflow-y-auto">
                                        @foreach($search_results as $person)
                                            <div class="p-4 hover:bg-gray-50 cursor-pointer" wire:click="togglePersonSelection({{ $person['id'] }})">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" {{ in_array($person['id'], $selected_persons) ? 'checked' : '' }}
                                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ $person['given_name'] }} {{ $person['family_name'] }}
                                                            </p>
                                                            <p class="text-sm text-gray-500">
                                                                @if(isset($person['email_addresses'][0]))
                                                                    <i class="fas fa-envelope mr-1"></i> {{ $person['email_addresses'][0]['email'] }}
                                                                @endif
                                                                @if(isset($person['phones'][0]))
                                                                    <i class="fas fa-phone ml-3 mr-1"></i> {{ $person['phones'][0]['number'] }}
                                                                @endif
                                                            </p>
                                                            @if(auth()->user() && auth()->user()->hasRole('Super Admin') && isset($person['organization_name']))
                                                                <p class="text-xs text-blue-600 font-medium">
                                                                    <i class="fas fa-building mr-1"></i> {{ $person['organization_name'] }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        @if(isset($person['affiliations'][0]))
                                                            <p class="text-sm text-gray-900">{{ $person['affiliations'][0]['role_title'] ?? 'Member' }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Selected Persons Count -->
                                @if(!empty($selected_persons))
                                    <div class="bg-indigo-50 border border-indigo-200 rounded-md p-3">
                                        <p class="text-sm text-indigo-700">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            {{ count($selected_persons) }} person(s) selected
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Filter Profile Mode -->
                        @if($selectionMode === 'filter_profile')
                            <div class="space-y-4">
                                @if(count($available_filter_profiles) > 0)
                                    <div class="flex items-center justify-between mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Select a Filter Profile</label>
                                        <a href="{{ route('communication.filter-profiles') }}"
                                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Create New
                                        </a>
                                    </div>
                                    <div>
                                        <div class="space-y-3">
                                            @foreach($available_filter_profiles as $profile)
                                                <div class="border rounded-lg p-4 cursor-pointer transition-colors {{ $selected_filter_profile_id == $profile['id'] ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                                                     wire:click="selectFilterProfile({{ $profile['id'] }})">
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex items-start">
                                                            <input type="radio" name="filter_profile"
                                                                   {{ $selected_filter_profile_id == $profile['id'] ? 'checked' : '' }}
                                                                   class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 mt-1">
                                                            <div class="ml-3">
                                                                <h5 class="font-medium text-gray-900">{{ $profile['name'] }}</h5>
                                                                @if($profile['description'])
                                                                    <p class="text-sm text-gray-500 mt-1">{{ $profile['description'] }}</p>
                                                                @endif
                                                                <div class="flex items-center mt-2 space-x-4">
                                                                    <span class="text-sm text-indigo-600 font-medium">
                                                                        ~{{ number_format($profile['estimated_count']) }} matches
                                                                    </span>
                                                                    @if($profile['is_shared'])
                                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                            Shared
                                                                        </span>
                                                                    @endif
                                                                    <span class="text-xs text-gray-500">
                                                                        Used {{ $profile['usage_count'] }} times
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if($selected_filter_profile_id)
                                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                            <div class="flex">
                                                <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                                                <div>
                                                    <h5 class="text-sm font-medium text-green-800">Filter Profile Selected</h5>
                                                    <p class="text-sm text-green-700 mt-1">
                                                        This will send the message to approximately {{ number_format($filter_profile_preview_count) }} person(s) matching your selected filter criteria.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-8">
                                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-filter text-gray-400 text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Filter Profiles Available</h3>
                                        <p class="text-gray-500 mb-4">Create filter profiles to quickly target specific groups of people.</p>
                                        <a href="{{ route('communication.filter-profiles') }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Create Filter Profile
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- All Persons Mode -->
                        @if($selectionMode === 'all')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-1"></i>
                                    <div>
                                        <h5 class="text-sm font-medium text-yellow-800">Send to All Persons</h5>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            @if(auth()->user() && auth()->user()->hasRole('Super Admin'))
                                                This will send the message to all {{ $filter_preview_count }} persons across ALL organizations.
                                                <span class="font-semibold text-blue-700">(Super Admin Access)</span>
                                            @else
                                                This will send the message to all {{ $filter_preview_count }} persons in your organization.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- STEP 2: CHANNEL SELECTION -->
            @if($currentStep === 2)
                <div class="space-y-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Choose Communication Channels</h4>
                        <p class="text-sm text-gray-600 mb-6">Select one or more channels to send your message through.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Email Channel -->
                            <div class="border-2 rounded-lg p-4 cursor-pointer transition-colors {{ in_array('email', $selected_channels) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                                 wire:click="toggleChannel('email')">
                                <div class="flex items-center">
                                    <input type="checkbox" {{ in_array('email', $selected_channels) ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    <div class="ml-3">
                                        <i class="fas fa-envelope text-2xl {{ in_array('email', $selected_channels) ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5 class="font-medium text-gray-900">Email</h5>
                                    <p class="text-sm text-gray-500">Send formatted emails with subject lines</p>
                                </div>
                            </div>

                            <!-- SMS Channel -->
                            <div class="border-2 rounded-lg p-4 cursor-pointer transition-colors {{ in_array('sms', $selected_channels) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} {{ !$this->isSmsChannelAvailable() ? 'opacity-60' : '' }}"
                                 wire:click="toggleChannel('sms')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <input type="checkbox" {{ in_array('sms', $selected_channels) ? 'checked' : '' }}
                                               {{ !$this->isSmsChannelAvailable() ? 'disabled' : '' }}
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        <div class="ml-3">
                                            <i class="fas fa-sms text-2xl {{ in_array('sms', $selected_channels) ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                                        </div>
                                    </div>
                                    <div class="text-xs">
                                        @if($this->isSmsChannelAvailable())
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle text-xs mr-1"></i>
                                                Ready
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-circle text-xs mr-1"></i>
                                                Not Available
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5 class="font-medium text-gray-900">SMS</h5>
                                    <p class="text-sm text-gray-500">
                                        @if($this->isSmsChannelAvailable())
                                            Send text messages to mobile phones (via Africa's Talking)
                                        @else
                                            SMS service not configured or unavailable
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- WhatsApp Channel -->
                            <div class="border-2 rounded-lg p-4 cursor-pointer transition-colors {{ in_array('whatsapp', $selected_channels) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                                 wire:click="toggleChannel('whatsapp')">
                                <div class="flex items-center">
                                    <input type="checkbox" {{ in_array('whatsapp', $selected_channels) ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    <div class="ml-3">
                                        <i class="fab fa-whatsapp text-2xl {{ in_array('whatsapp', $selected_channels) ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5 class="font-medium text-gray-900">WhatsApp</h5>
                                    <p class="text-sm text-gray-500">Send messages via WhatsApp Business</p>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Channels Summary -->
                        @if(!empty($selected_channels))
                            <div class="mt-6 bg-indigo-50 border border-indigo-200 rounded-md p-3">
                                <p class="text-sm text-indigo-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Selected channels: {{ implode(', ', array_map('ucfirst', $selected_channels)) }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- STEP 3: MESSAGE COMPOSITION -->
            @if($currentStep === 3)
                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Compose Your Message</h4>

                    <!-- Email Subject (if email is selected) -->
                    @if(in_array('email', $selected_channels))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Subject</label>
                            <input wire:model="subject"
                                   type="text"
                                   placeholder="Enter email subject..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    @endif

                    <!-- Message Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message Content</label>
                        <textarea wire:model="message_content"
                                  rows="6"
                                  placeholder="Type your message here..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        <p class="mt-1 text-sm text-gray-500">{{ strlen($message_content) }} characters</p>
                    </div>

                    <!-- Channel Preview -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h5 class="font-medium text-gray-900 mb-3">Preview</h5>
                        <div class="space-y-3">
                            @foreach($selected_channels as $channel)
                                <div class="border border-gray-200 rounded bg-white p-3">
                                    <div class="flex items-center mb-2">
                                        @if($channel === 'email')
                                            <i class="fas fa-envelope text-gray-500 mr-2"></i>
                                            <span class="text-sm font-medium">Email</span>
                                        @elseif($channel === 'sms')
                                            <i class="fas fa-sms text-gray-500 mr-2"></i>
                                            <span class="text-sm font-medium">SMS</span>
                                        @elseif($channel === 'whatsapp')
                                            <i class="fab fa-whatsapp text-gray-500 mr-2"></i>
                                            <span class="text-sm font-medium">WhatsApp</span>
                                        @endif
                                    </div>
                                    @if($channel === 'email' && $subject)
                                        <p class="text-sm font-medium text-gray-900 mb-1">Subject: {{ $subject }}</p>
                                    @endif
                                    <p class="text-sm text-gray-700">{{ $message_content ?: 'No message content yet...' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Send Button -->
                    <div class="flex justify-center">
                        <button wire:click="sendMessage"
                                wire:loading.attr="disabled"
                                class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="sendMessage">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Send Messages
                            </span>
                            <span wire:loading wire:target="sendMessage">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Preparing to Send...
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between">
            <button wire:click="previousStep"
                    @if($currentStep === 1) disabled @endif
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-arrow-left mr-2"></i> Previous
            </button>

            @if($currentStep < 3)
                <button wire:click="nextStep"
                        class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
            @endif
        </div>
    </div>

    <!-- Enhanced Progress Modal -->
    @if($show_progress_modal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:poll.500ms>
            <div class="relative top-10 mx-auto p-6 border max-w-lg shadow-lg rounded-md bg-white">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        @if($sending_status === 'sending')
                            <i class="fas fa-paper-plane text-indigo-600 mr-2 animate-bounce"></i>
                            Sending Messages...
                        @elseif($sending_status === 'completed')
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            Messages Sent!
                        @else
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                            Sending Failed
                        @endif
                    </h3>

                    @if($sending_status === 'sending')
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4 shadow-inner">
                            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-3 rounded-full transition-all duration-500 ease-out relative overflow-hidden"
                                 style="width: {{ $sending_progress }}%">
                                <!-- Animated shine effect -->
                                <div class="absolute top-0 left-0 h-full w-6 bg-gradient-to-r from-transparent via-white to-transparent opacity-30 animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Progress Statistics -->
                        <div class="flex justify-between text-sm text-gray-600 mb-4">
                            <span>{{ $sending_progress }}% complete</span>
                            <span>{{ $sent_messages + $failed_messages }} / {{ $total_messages }}</span>
                        </div>

                        <!-- Current Activity -->
                        @if($current_recipient)
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-arrow-right mr-1"></i>
                                    Sending via <strong>{{ $current_channel }}</strong> to <strong>{{ $current_recipient }}</strong>
                                </p>
                            </div>
                        @endif

                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="bg-green-50 border border-green-200 rounded-md p-2">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-green-600">{{ $sent_messages }}</div>
                                    <div class="text-xs text-green-700">Sent</div>
                                </div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-md p-2">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-red-600">{{ $failed_messages }}</div>
                                    <div class="text-xs text-red-700">Failed</div>
                                </div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-2">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-600">{{ $total_messages - $sent_messages - $failed_messages }}</div>
                                    <div class="text-xs text-gray-700">Remaining</div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Log -->
                        @if(!empty($progress_details) && count($progress_details) > 0)
                            <div class="text-left max-h-32 overflow-y-auto bg-gray-50 rounded-md p-3">
                                <h5 class="text-xs font-medium text-gray-700 mb-2">Recent Activity:</h5>
                                @foreach(array_slice(array_reverse($progress_details), 0, 5) as $detail)
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <div class="flex items-center">
                                            @if($detail['status'] === 'success')
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                            @else
                                                <i class="fas fa-times text-red-500 mr-1"></i>
                                            @endif
                                            <span class="truncate">{{ $detail['recipient'] }} ({{ ucfirst($detail['channel']) }})</span>
                                        </div>
                                        <span class="text-gray-500">{{ $detail['time'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    @if($sending_status === 'completed')
                        <!-- Success Summary -->
                        <div class="text-green-600 mb-4">
                            <i class="fas fa-check-circle text-4xl"></i>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $sent_messages }}</div>
                                    <div class="text-green-700">Successfully Sent</div>
                                </div>
                                @if($failed_messages > 0)
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-red-600">{{ $failed_messages }}</div>
                                        <div class="text-red-700">Failed</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-3 justify-center">
                            <button wire:click="resetComponent" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Send Another Message
                            </button>
                            <button wire:click="closeProgressModal" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                                Close
                            </button>
                        </div>
                    @endif

                    @if($sending_status === 'failed')
                        <div class="text-red-600 mb-4">
                            <i class="fas fa-exclamation-circle text-4xl"></i>
                        </div>
                        <p class="text-red-700 mb-4">An error occurred while sending messages.</p>
                        <button wire:click="closeProgressModal" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Close
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
