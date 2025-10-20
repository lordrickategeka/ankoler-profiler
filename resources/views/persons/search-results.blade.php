{{-- resources/views/persons/search-results.blade.php --}}

<!-- Individual Result Item -->
<div class="bg-white rounded-xl shadow-sm result-item">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <input type="checkbox" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                
                <!-- Profile Image/Avatar -->
                <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    JD
                </div>
                
                <!-- Person Details -->
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">John Doe</h3>
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">Patient</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-id-card mr-2 text-gray-400"></i>
                            <span>PRS-000123</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2 text-gray-400"></i>
                            <span>+256 123 456 789</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <span>john.doe@email.com</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-birthday-cake mr-2 text-gray-400"></i>
                            <span>32 years • Male</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                            <span>Kampala, Uganda</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-building mr-2 text-gray-400"></i>
                            <span>Kampala Hospital</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button class="text-gray-400 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Quick View" onclick="openQuickView('123')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="text-gray-400 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="More Options">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Second Result Item -->
<div class="bg-white rounded-xl shadow-sm result-item">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <input type="checkbox" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                
                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    SK
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">Sarah Kimani</h3>
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                        <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">Staff</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-id-card mr-2 text-gray-400"></i>
                            <span>PRS-000456</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2 text-gray-400"></i>
                            <span>+256 987 654 321</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <span>sarah.k@hospital.com</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-birthday-cake mr-2 text-gray-400"></i>
                            <span>28 years • Female</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                            <span>Entebbe, Uganda</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-building mr-2 text-gray-400"></i>
                            <span>Kampala Hospital</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <button class="text-gray-400 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Quick View" onclick="openQuickView('456')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="text-gray-400 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="More Options">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Third Result Item -->
<div class="bg-white rounded-xl shadow-sm result-item">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <input type="checkbox" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                
                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    MK
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">Moses Kiprotich</h3>
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                        <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs font-medium">Student</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-id-card mr-2 text-gray-400"></i>
                            <span>PRS-000789</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2 text-gray-400"></i>
                            <span>+256 555 123 456</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <span>moses.k@university.ac.ke</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-birthday-cake mr-2 text-gray-400"></i>
                            <span>22 years • Male</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                            <span>Eldoret, Kenya</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-building mr-2 text-gray-400"></i>
                            <span>University College</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <button class="text-gray-400 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Quick View" onclick="openQuickView('789')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="text-gray-400 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="More Options">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Load More / Pagination -->
<div class="bg-white rounded-xl shadow-sm p-6 text-center">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-600">Showing 1-20 of 847 results</p>
        
        <div class="flex items-center space-x-2">
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50" disabled>
                Previous
            </button>
            <button class="px-3 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">1</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">2</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">3</button>
            <span class="px-2 text-gray-500">...</span>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">43</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                Next
            </button>
        </div>
    </div>
</div>