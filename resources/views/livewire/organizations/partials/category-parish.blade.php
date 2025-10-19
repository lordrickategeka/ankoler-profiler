{{-- Parish-Specific Details --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Denomination <span class="text-red-500">*</span></span>
            </label>
            <select wire:model="categoryDetails.denomination" class="select select-bordered">
                <option value="">Select Denomination</option>
                <option value="CATHOLIC">Catholic</option>
                <option value="ANGLICAN">Anglican</option>
                <option value="PENTECOSTAL">Pentecostal</option>
                <option value="ORTHODOX">Orthodox</option>
                <option value="BAPTIST">Baptist</option>
                <option value="METHODIST">Methodist</option>
                <option value="PRESBYTERIAN">Presbyterian</option>
                <option value="LUTHERAN">Lutheran</option>
                <option value="ADVENTIST">Seventh Day Adventist</option>
                <option value="EVANGELICAL">Evangelical</option>
                <option value="ISLAMIC">Islamic</option>
                <option value="OTHER">Other</option>
            </select>
            @error('categoryDetails.denomination') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Parish Type</span>
            </label>
            <select wire:model="categoryDetails.parish_type" class="select select-bordered">
                <option value="">Select Type</option>
                <option value="URBAN">Urban Parish</option>
                <option value="RURAL">Rural Parish</option>
                <option value="SUBURBAN">Suburban Parish</option>
                <option value="MISSION">Mission Station</option>
                <option value="CATHEDRAL">Cathedral Parish</option>
                <option value="SHRINE">Shrine</option>
            </select>
            @error('categoryDetails.parish_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Parish Priest/Pastor Name</span>
            </label>
            <input type="text" wire:model="categoryDetails.priest_name" class="input input-bordered"
                   placeholder="Rev./Pastor/Imam name">
            @error('categoryDetails.priest_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Assistant Priest/Pastor</span>
            </label>
            <input type="text" wire:model="categoryDetails.assistant_priest" class="input input-bordered"
                   placeholder="Assistant clergy name">
            @error('categoryDetails.assistant_priest') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Parish Patron Saint</span>
            </label>
            <input type="text" wire:model="categoryDetails.patron_saint" class="input input-bordered"
                   placeholder="e.g., St. Joseph, St. Mary">
            @error('categoryDetails.patron_saint') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Congregation Size</span>
            </label>
            <input type="number" wire:model="categoryDetails.congregation_size" class="input input-bordered"
                   placeholder="Number of registered members" min="0">
            @error('categoryDetails.congregation_size') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Church Capacity</span>
            </label>
            <input type="number" wire:model="categoryDetails.church_capacity" class="input input-bordered"
                   placeholder="Seating capacity of church" min="0">
            @error('categoryDetails.church_capacity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Diocese/Region</span>
            </label>
            <input type="text" wire:model="categoryDetails.diocese" class="input input-bordered"
                   placeholder="Diocese or regional authority">
            @error('categoryDetails.diocese') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Service Times --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Service Schedule</h5>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Sunday Service Times</span>
                </label>
                <input type="text" wire:model="categoryDetails.sunday_service_times" class="input input-bordered"
                       placeholder="e.g., 7:00 AM, 9:00 AM, 11:00 AM">
                @error('categoryDetails.sunday_service_times') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Weekday Service Times</span>
                </label>
                <input type="text" wire:model="categoryDetails.weekday_service_times" class="input input-bordered"
                       placeholder="e.g., Monday 6:00 PM, Wednesday 6:00 PM">
                @error('categoryDetails.weekday_service_times') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- Ministries and Programs --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Ministries & Programs</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $ministries = [
                    'youth_ministry' => 'Youth Ministry',
                    'women_ministry' => 'Women\'s Ministry',
                    'men_ministry' => 'Men\'s Ministry',
                    'children_ministry' => 'Children\'s Ministry',
                    'choir' => 'Choir Ministry',
                    'prayer_group' => 'Prayer Groups',
                    'bible_study' => 'Bible Study',
                    'sunday_school' => 'Sunday School',
                    'counseling' => 'Counseling Services',
                    'community_outreach' => 'Community Outreach',
                    'charity_work' => 'Charity Work',
                    'education' => 'Educational Programs',
                    'health_ministry' => 'Health Ministry',
                    'marriage_ministry' => 'Marriage Ministry'
                ];
            @endphp

            @foreach($ministries as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.ministries" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.ministries') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Facilities --}}
    <div>
        <h5 class="font-medium text-gray-900 mb-4">Church Facilities</h5>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $facilities = [
                    'main_church' => 'Main Church Building',
                    'parish_hall' => 'Parish Hall',
                    'rectory' => 'Rectory/Parsonage',
                    'school' => 'Parish School',
                    'clinic' => 'Health Clinic',
                    'cemetery' => 'Cemetery',
                    'parking' => 'Parking Area',
                    'kitchen' => 'Kitchen Facilities',
                    'conference_room' => 'Conference Room',
                    'library' => 'Library',
                    'playground' => 'Playground',
                    'guest_house' => 'Guest House'
                ];
            @endphp

            @foreach($facilities as $key => $label)
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" wire:model="categoryDetails.facilities" value="{{ $key }}" class="checkbox checkbox-sm">
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('categoryDetails.facilities') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Special Events --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Annual Feast Day</span>
            </label>
            <input type="date" wire:model="categoryDetails.feast_day" class="input input-bordered">
            @error('categoryDetails.feast_day') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Languages Used in Service</span>
            </label>
            <select wire:model="categoryDetails.service_languages" class="select select-bordered" multiple>
                <option value="ENGLISH">English</option>
                <option value="LUGANDA">Luganda</option>
                <option value="RUNYANKOLE">Runyankole</option>
                <option value="LUGISU">Lugisu</option>
                <option value="SWAHILI">Swahili</option>
                <option value="FRENCH">French</option>
                <option value="ARABIC">Arabic</option>
            </select>
            @error('categoryDetails.service_languages') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Community Impact --}}
    <div class="form-control">
        <label class="label">
            <span class="label-text font-medium">Community Impact & Outreach Programs</span>
        </label>
        <textarea wire:model="categoryDetails.community_impact" class="textarea textarea-bordered h-24"
                  placeholder="Describe the parish's community involvement, charity work, educational programs, health initiatives, etc."></textarea>
        @error('categoryDetails.community_impact') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
