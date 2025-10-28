
<div>
    <h1 class="text-2xl font-bold mb-6">My Notifications</h1>

    <!-- Top 3 Cards Row -->
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <!-- Recent Activities Card -->
        <div class="flex-1 card bg-base-100 shadow border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-blue-100 text-blue-600 rounded-full p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    <span class="font-semibold text-base">Recent Activities</span>
                </div>
                <ul class="space-y-2 text-sm text-base-content/80">
                    @forelse($recentActivities as $activity)
                        <li class="flex items-start gap-2">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-1"></span>
                            <div>
                                <div class="font-semibold">{{ $activity['title'] }}</div>
                                <div class="text-xs text-base-content/60">{{ $activity['description'] }}</div>
                                <div class="text-xs text-base-content/40">{{ $activity['time'] }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="text-base-content/40">No recent activities</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <!-- Ongoing Events Card -->
        <div class="flex-1 card bg-base-100 shadow border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-purple-100 text-purple-600 rounded-full p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z" /></svg>
                    </span>
                    <span class="font-semibold text-base">Ongoing Events</span>
                </div>
                <ul class="space-y-2 text-sm text-base-content/80">
                    @foreach($ongoingEvents ?? [] as $event)
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                            <span>{{ $event }}</span>
                        </li>
                    @endforeach
                    @if(empty($ongoingEvents) || count($ongoingEvents) === 0)
                        <li class="text-base-content/40">No ongoing events</li>
                    @endif
                </ul>
            </div>
        </div>
        <!-- System Notifications Card -->
        <div class="flex-1 card bg-base-100 shadow border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-green-100 text-green-600 rounded-full p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
                    </span>
                    <span class="font-semibold text-base">System Notifications</span>
                </div>
                <ul class="space-y-2 text-sm text-base-content/80">
                    @foreach($systemNotifications ?? [] as $sysnote)
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                            <span>{{ $sysnote }}</span>
                        </li>
                    @endforeach
                    @if(empty($systemNotifications) || count($systemNotifications) === 0)
                        <li class="text-base-content/40">No system notifications</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    @if($notifications->count())
        <div class="space-y-4 max-h-[32rem] overflow-y-auto pr-2" style="max-height: calc(5 * 6rem);">
            @foreach($notifications as $i => $notification)
                @if($i < 5)
                <div class="card bg-base-100 shadow border border-base-300">
                    <div class="card-body p-4 flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-{{ $notification->read_at ? 'neutral' : 'accent' }}">
                                {{ $notification->read_at ? 'Read' : 'Unread' }}
                            </span>
                            <span class="text-xs text-base-content/60">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="font-semibold text-base-content">{{ $notification->data['title'] ?? 'Notification' }}</div>
                        <div class="text-base-content/70 text-sm">{{ $notification->data['body'] ?? '' }}</div>
                        @if(isset($notification->data['action_url']))
                            <a href="{{ $notification->data['action_url'] }}" class="btn btn-sm btn-accent mt-2">View Details</a>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    @else

    @endif
</div>
