<div>
    <div class="p-4">
        <input type="checkbox" wire:model="selectPage" class="checkbox" />
        <span class="label-text ml-2">Select page</span>
        </label>
    </div>
    <button wire:click="toggleSelectAll" class="btn btn-outline btn-sm">Toggle Select All</button>


    <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-primary btn-sm m-1">Bulk Actions</label>
        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
            <li><a wire:click.prevent="openSendMessage">Send Message</a></li>
            <!-- add more bulk actions here -->
        </ul>
    </div>


<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" wire:model="selectPage" class="checkbox" />
                </th>


                @foreach ($columns as $col)
                    <th class="cursor-pointer" wire:click="sortBy('{{ $col }}')">
                        {{ ucfirst(str_replace(['_', '.'], [' ', ' '], $col)) }}
                        @if ($sortField === $col)
                            <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                @endforeach


                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr class="hover">
                    <td>
                        <input type="checkbox" wire:model="selected" value="{{ $row->id }}" class="checkbox" />
                    </td>


                    @foreach ($columns as $col)
                        <td>
                            @if (Str::contains($col, '.'))
                                @php
                                    [$relation, $field] = explode('.', $col, 2);
                                @endphp
                                {{ optional($row->{$relation})->{$field} }}
                            @else
                                {{ $row->{$col} }}
                            @endif
                        </td>
                    @endforeach


                    <td>
                        <div class="flex gap-2">
                            <button wire:click="toggleExpand('{{ $row->id }}')"
                                class="btn btn-ghost btn-sm">{{ $expandedId === (string) $row->id ? 'Collapse' : 'Expand' }}</button>
                        </div>
                    </td>
                </tr>


                @if ($expandedId === (string) $row->id)
                    <tr>
                        <td colspan="{{ count($columns) + 2 }}">
                            <div class="p-4 bg-base-200 rounded-lg">
                                {{-- Expanded content goes here --}}
                                <h3 class="font-bold text-lg mb-2">Details for {{ $row->id }}</h3>
                                <p>Additional information about this record can be displayed here.</p>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 2 }}" class="text-center">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

