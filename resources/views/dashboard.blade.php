<x-layout title="Dashboard">
    <div class="flex flex-col gap-8">

        {{-- Header --}}
        <div class="flex justify-between items-end">
            <div>
                <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                    <span x-show="$store.theme.halloween" x-cloak class="select-none text-xs">🎃</span>
                    <span x-show="$store.theme.christmas" x-cloak class="select-none text-xs">🎄</span>
                    Workspace Overview
                    <span x-show="$store.theme.halloween" x-cloak class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-orange-500/10 text-orange-500 border border-orange-500/20 backdrop-blur-sm">
                        <span class="w-1 h-1 rounded-full bg-[#F97316]"></span>
                        Spooky Season
                    </span>
                    <span x-show="$store.theme.christmas" x-cloak class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 backdrop-blur-sm">
                        <span class="w-1 h-1 rounded-full bg-[#10B981]"></span>
                        Holiday Season
                    </span>
                </p>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-[#F4F0F8] tracking-tight flex items-center gap-2">
                    <span>Good {{ now()->timezone('Asia/Colombo')->hour < 12 ? 'morning' : (now()->timezone('Asia/Colombo')->hour < 17 ? 'afternoon' : 'evening') }}, {{ ucfirst(explode(' ', auth()->user()->name)[0]) }}</span>
                    <span x-show="$store.theme.halloween" x-cloak class="text-xl select-none opacity-80 inline-block hover:scale-110 transition-transform cursor-default" title="Spooky Halloween">🦇</span>
                    <span x-show="$store.theme.christmas" x-cloak class="text-xl select-none opacity-80 inline-block hover:scale-110 transition-transform cursor-default" title="Merry Christmas">🎅</span>
                </h1>
            </div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider px-3.5 py-1.5 rounded-lg bg-gray-100/90 dark:bg-slate-800/90 border border-gray-200/90 dark:border-white/10 shadow-xs">{{ now()->format('l, M j') }}</span>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card stat-assigned rounded-2xl p-6 card-shadow flex flex-col justify-between min-h-[132px] transition-all hover:shadow-md">
                <p class="stat-label mb-2">Assigned</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $deliverables->count() }}</p>
                <p class="stat-subtext mt-2">deliverables</p>
            </div>
            <div class="stat-card stat-completed rounded-2xl p-6 card-shadow flex flex-col justify-between min-h-[132px] transition-all hover:shadow-md">
                <p class="stat-label mb-2">Completed</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $deliverables->where('status', 'Done')->count() }}</p>
                <p class="stat-subtext mt-2">closed out</p>
            </div>
            <div class="stat-card stat-pending rounded-2xl p-6 card-shadow flex flex-col justify-between min-h-[132px] transition-all hover:shadow-md">
                <p class="stat-label mb-2">Pending</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $deliverables->where('status', '!=', 'Done')->count() }}</p>
                <p class="stat-subtext mt-2">in progress</p>
            </div>
            <div class="stat-card stat-brands rounded-2xl p-6 card-shadow flex flex-col justify-between min-h-[132px] transition-all hover:shadow-md">
                <p class="stat-label mb-2">Brands</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $brandCount }}</p>
                <p class="stat-subtext mt-2">active workspaces</p>
            </div>
        </div>

        <style>
            .stat-card {
                border-width: 1px;
                border-style: solid;
                position: relative;
            }
            /* Assigned Card */
            .stat-assigned {
                background-color: #ffffff;
                border-color: #e2e8f0;
                border-top: 2px solid #6366f1;
            }
            .stat-assigned .stat-label {
                font-size: 11px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .stat-assigned .stat-subtext {
                font-size: 11px;
                font-weight: 500;
                color: #64748b;
            }

            /* Completed Card */
            .stat-completed {
                background-color: #ffffff;
                border-color: #e2e8f0;
                border-top: 2px solid #10b981;
            }
            .stat-completed .stat-label {
                font-size: 11px;
                font-weight: 700;
                color: #059669;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .stat-completed .stat-subtext {
                font-size: 11px;
                font-weight: 500;
                color: #059669;
            }

            /* Pending Card */
            .stat-pending {
                background-color: #ffffff;
                border-color: #e2e8f0;
                border-top: 2px solid #f59e0b;
            }
            .stat-pending .stat-label {
                font-size: 11px;
                font-weight: 700;
                color: #d97706;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .stat-pending .stat-subtext {
                font-size: 11px;
                font-weight: 500;
                color: #d97706;
            }

            /* Brands Card */
            .stat-brands {
                background-color: #ffffff;
                border-color: #e2e8f0;
                border-top: 2px solid #3b82f6;
            }
            .stat-brands .stat-label {
                font-size: 11px;
                font-weight: 700;
                color: #2563eb;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .stat-brands .stat-subtext {
                font-size: 11px;
                font-weight: 500;
                color: #2563eb;
            }

            /* Standard Dark Mode Overrides */
            .dark .stat-assigned,
            .dark .stat-completed,
            .dark .stat-pending,
            .dark .stat-brands {
                background-color: #111827;
                border-color: rgba(255, 255, 255, 0.08);
            }
            .dark .stat-assigned {
                border-top: 2px solid #818cf8;
            }
            .dark .stat-completed {
                border-top: 2px solid #34d399;
            }
            .dark .stat-pending {
                border-top: 2px solid #fbbf24;
            }
            .dark .stat-brands {
                border-top: 2px solid #60a5fa;
            }
            .dark .stat-assigned .stat-label,
            .dark .stat-assigned .stat-subtext {
                color: #a5b4fc;
            }
            .dark .stat-completed .stat-label,
            .dark .stat-completed .stat-subtext {
                color: #6ee7b7;
            }
            .dark .stat-pending .stat-label,
            .dark .stat-pending .stat-subtext {
                color: #fde68a;
            }
            .dark .stat-brands .stat-label,
            .dark .stat-brands .stat-subtext {
                color: #93c5fd;
            }

            /* ========================================================
               MODERN LUXURY SEASONAL KPI CARDS (Linear / Vercel style)
               ======================================================== */
            /* Dark Mode: Semi-translucent Glassmorphic Dark Card + 1.5px Gradient Line Stroke */
            html.dark.halloween .stat-card {
                background-color: rgba(22, 19, 31, 0.75) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                border-top: none !important;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
                position: relative;
                overflow: hidden;
            }
            html.dark.halloween .stat-card:hover {
                transform: translateY(-2px);
                border-color: rgba(249, 115, 22, 0.35) !important;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(249, 115, 22, 0.15) !important;
            }
            html.dark.halloween .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1.5px;
                pointer-events: none;
            }
            html.dark.halloween .stat-assigned::before {
                background: linear-gradient(90deg, #8B5CF6 0%, rgba(139, 92, 246, 0.3) 40%, transparent 100%) !important;
            }
            html.dark.halloween .stat-completed::before {
                background: linear-gradient(90deg, #10B981 0%, rgba(16, 185, 129, 0.3) 40%, transparent 100%) !important;
            }
            html.dark.halloween .stat-pending::before {
                background: linear-gradient(90deg, #F59E0B 0%, rgba(245, 158, 11, 0.3) 40%, transparent 100%) !important;
            }
            html.dark.halloween .stat-brands::before {
                background: linear-gradient(90deg, #F97316 0%, rgba(249, 115, 22, 0.3) 40%, transparent 100%) !important;
            }
            html.dark.halloween .stat-card .stat-label {
                letter-spacing: 0.08em !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                color: #8C8494 !important;
                text-transform: uppercase !important;
            }
            html.dark.halloween .stat-card p.text-3xl {
                font-size: 32px !important;
                font-weight: 700 !important;
                color: #F4F0F8 !important;
                letter-spacing: -0.02em !important;
            }
            html.dark.halloween .stat-card .stat-subtext {
                font-size: 11px !important;
                font-weight: 500 !important;
                color: #6B6275 !important;
            }

            /* Light Mode ("Warm Obsidian & Linen"): Pure White Surface + Sleek 1.5px Gradient Stroke */
            html.halloween:not(.dark) .stat-card {
                background-color: #FFFFFF !important;
                border: 1px solid #EAE6DF !important;
                border-top: none !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 8px 24px rgba(0,0,0,0.04) !important;
                position: relative;
                overflow: hidden;
            }
            html.halloween:not(.dark) .stat-card:hover {
                transform: translateY(-2px);
                border-color: #C2410C !important;
                box-shadow: 0 4px 20px rgba(194, 65, 12, 0.08) !important;
            }
            html.halloween:not(.dark) .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1.5px;
                pointer-events: none;
            }
            html.halloween:not(.dark) .stat-assigned::before {
                background: linear-gradient(90deg, #581C87 0%, rgba(88, 28, 135, 0.3) 40%, transparent 100%) !important;
            }
            html.halloween:not(.dark) .stat-completed::before {
                background: linear-gradient(90deg, #059669 0%, rgba(5, 150, 105, 0.3) 40%, transparent 100%) !important;
            }
            html.halloween:not(.dark) .stat-pending::before {
                background: linear-gradient(90deg, #D97706 0%, rgba(217, 119, 6, 0.3) 40%, transparent 100%) !important;
            }
            html.halloween:not(.dark) .stat-brands::before {
                background: linear-gradient(90deg, #C2410C 0%, rgba(194, 65, 12, 0.3) 40%, transparent 100%) !important;
            }
            html.halloween:not(.dark) .stat-card .stat-label {
                letter-spacing: 0.08em !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                color: #8C829B !important;
                text-transform: uppercase !important;
            }
            html.halloween:not(.dark) .stat-card p.text-3xl {
                font-size: 32px !important;
                font-weight: 700 !important;
                color: #121118 !important;
                letter-spacing: -0.02em !important;
            }
            html.halloween:not(.dark) .stat-card .stat-subtext {
                font-size: 11px !important;
                font-weight: 500 !important;
                color: #8C8494 !important;
            }

            /* Themed Empty State Animations */
            .ghost-float {
                animation: ghostFloat 3.8s ease-in-out infinite alternate;
            }
            @keyframes ghostFloat {
                0% { transform: translateY(0px) rotate(-2deg); }
                50% { transform: translateY(-10px) rotate(3deg); }
                100% { transform: translateY(2px) rotate(-1deg); }
            }

            .gift-bounce {
                animation: giftBounce 2.5s ease-in-out infinite;
                transform-origin: bottom center;
            }
            @keyframes giftBounce {
                0%, 100% { transform: translateY(0) scale(1); }
                30% { transform: translateY(-8px) scale(1.05) rotate(2deg); }
                50% { transform: translateY(0) scale(0.98); }
                70% { transform: translateY(-4px) scale(1.02) rotate(-1deg); }
            }
        </style>

        {{-- Brand quick-access --}}
        @if($brands->isNotEmpty())
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Brand Directory</h2>
                <a href="{{ route('brands.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">View All →</a>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($brands as $brand)
                @php
                    $user = auth()->user();
                    $isAssigned = $user->isAdmin() || 
                        $user->role === 'Operations Manager' ||
                        $brand->created_by === $user->id || 
                        $brand->members->contains('id', $user->id);
                @endphp
                @if($isAssigned)
                <a href="{{ route('brands.show', $brand) }}"
                   class="brand-pill flex items-center gap-2.5 px-3 py-2 bg-white dark:bg-[#111827] border border-gray-200 dark:border-white/10 rounded-xl hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-xs transition-all group">
                @else
                <div onclick="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'You are not assigned to this brand.', type: 'warning' } }))"
                   class="brand-pill flex items-center gap-2.5 px-3 py-2 bg-white dark:bg-[#111827] border border-gray-200 dark:border-white/10 rounded-xl opacity-60 cursor-pointer transition-all group">
                @endif
                    <div class="brand-icon-box w-6 h-6 rounded-md bg-gray-100/90 dark:bg-slate-800/90 border border-gray-200/80 dark:border-white/10 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($brand->logo_url)
                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="w-5 h-5 object-contain">
                        @else
                            <span class="text-[10px] font-extrabold text-gray-500">{{ substr($brand->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <span class="text-[12px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 whitespace-nowrap">{{ $brand->name }}</span>
                @if($isAssigned)
                </a>
                @else
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Main content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            {{-- Deliverables list --}}
            <div class="lg:col-span-2 flex flex-col gap-6">

                @php
                    $allActiveTasks = $deliverables->where('status', '!=', 'Done')->sortByDesc(function($t) {
                        return $t->created_at ? $t->created_at->timestamp : $t->id;
                    });
                    $groupTasks = function($tasks) {
                        $grouped = [];
                        foreach($tasks as $t) {
                            $key = 'single_' . $t->id;
                            $grouped[$key] = [
                                'is_batch' => false,
                                'parent' => $t->parent,
                                'project' => $t->project,
                                'priority' => $t->priority,
                                'deadline' => $t->deadline,
                                'subtasks' => [$t]
                            ];
                        }
                        uasort($grouped, function($a, $b) {
                            $timeA = $a['subtasks'][0]->created_at ? $a['subtasks'][0]->created_at->timestamp : $a['subtasks'][0]->id;
                            $timeB = $b['subtasks'][0]->created_at ? $b['subtasks'][0]->created_at->timestamp : $b['subtasks'][0]->id;
                            return $timeB <=> $timeA;
                        });
                        return $grouped;
                    };

                    $isOverdue = function($t) {
                        if (empty($t->deadline)) return false;
                        return \Carbon\Carbon::parse($t->deadline)->startOfDay()->lt(now()->startOfDay());
                    };

                    $overdueTasks    = $allActiveTasks->filter(fn($t) => $isOverdue($t));
                    $nonOverdueTasks = $allActiveTasks->filter(fn($t) => !$isOverdue($t));

                    $groupedOverdue = $groupTasks($overdueTasks);
                    $groupedActive  = $groupTasks($nonOverdueTasks);
                @endphp

                {{-- Active Deliverables Section --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest flex items-center gap-1.5">
                            Active Deliverables
                            @if(count($groupedActive) > 0)
                                <span class="bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 px-1.5 py-0.5 text-[9px] font-extrabold rounded-full">{{ count($groupedActive) }}</span>
                            @endif
                        </p>
                    </div>
                    @forelse($groupedActive as $group)
                        <x-dashboard-deliverable-card :group="$group" />
                    @empty
                        <div x-show="!$store.theme.halloween && !$store.theme.christmas" class="bg-white dark:bg-[#111827] rounded-xl py-6 px-5 text-center border border-dashed border-gray-200 dark:border-white/10 card-shadow flex flex-col items-center justify-center">
                            <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-2.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">No active deliverables assigned to you.</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-sm">You are all caught up! When tasks are assigned to your stage, they will appear here.</p>
                            @can('create-deliverable')
                            <div class="mt-3.5">
                                <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    View Projects & Tasks
                                </a>
                            </div>
                            @endcan
                        </div>

                        {{-- Halloween Themed Animated Empty State --}}
                        <div x-show="$store.theme.halloween" x-cloak class="halloween-empty-state rounded-2xl py-6 px-5 text-center border border-dashed border-orange-500/30 dark:border-orange-500/25 relative overflow-hidden shadow-sm backdrop-blur-md"
                             style="background: radial-gradient(circle at 50% 20%, rgba(249, 115, 22, 0.08), transparent 70%);">
                            {{-- Cobweb in top-right corner --}}
                            <div class="absolute top-0 right-0 w-16 h-16 opacity-25 pointer-events-none">
                                <svg viewBox="0 0 50 50" class="w-full h-full stroke-orange-400 fill-none" stroke-width="0.8">
                                    <line x1="50" y1="0" x2="0" y2="0"/>
                                    <line x1="50" y1="0" x2="15" y2="35"/>
                                    <line x1="50" y1="0" x2="35" y2="15"/>
                                    <line x1="50" y1="0" x2="50" y2="50"/>
                                    <path d="M 50,15 Q 35,15 35,0" />
                                    <path d="M 50,30 Q 25,25 20,0" />
                                    <path d="M 50,45 Q 10,35 5,0" />
                                </svg>
                            </div>

                            {{-- Floating Animated Ghost --}}
                            <div class="ghost-float w-14 h-14 mx-auto mb-2 flex items-center justify-center filter drop-shadow-[0_8px_16px_rgba(249,115,22,0.3)]">
                                <svg width="50" height="50" viewBox="0 0 64 64" fill="none">
                                    <!-- Ghost Body -->
                                    <path d="M 32 6 C 20 6 14 16 14 28 C 14 38 10 48 10 54 C 10 57 14 56 18 53 C 22 50 26 55 30 52 C 34 49 38 54 42 51 C 46 48 50 54 54 54 C 54 48 50 38 50 28 C 50 16 44 6 32 6 Z"
                                          fill="#FFFFFF" class="dark:fill-[#F4F0F8]" stroke="#F97316" stroke-width="1.5"/>
                                    <!-- Eyes -->
                                    <ellipse cx="26" cy="24" rx="3" ry="4" fill="#0B0A0F"/>
                                    <ellipse cx="38" cy="24" rx="3" ry="4" fill="#0B0A0F"/>
                                    <circle cx="27" cy="22.5" r="1" fill="#FFFFFF"/>
                                    <circle cx="39" cy="22.5" r="1" fill="#FFFFFF"/>
                                    <!-- Cute O-shaped Mouth -->
                                    <ellipse cx="32" cy="33" rx="2.5" ry="3.5" fill="#0B0A0F"/>
                                    <!-- Blush Cheeks -->
                                    <circle cx="21" cy="28" r="2.5" fill="#F97316" opacity="0.35"/>
                                    <circle cx="43" cy="28" r="2.5" fill="#F97316" opacity="0.35"/>
                                </svg>
                            </div>
                            <p class="text-base font-bold text-[#121118] dark:text-[#F4F0F8]">Spookily Quiet... No active deliverables</p>
                            <p class="text-xs text-[#524E59] dark:text-[#A198AC] mt-0.5 max-w-sm mx-auto">All deliverables are resting peacefully. New tasks will haunt this board when assigned.</p>
                            @can('create-deliverable')
                            <div class="mt-3">
                                <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#F97316] hover:bg-[#EA580C] text-[#0B0A0F] shadow-xs transition-colors">
                                    View Projects & Tasks
                                </a>
                            </div>
                            @endcan
                        </div>

                        {{-- Christmas Themed Animated Empty State --}}
                        <div x-show="$store.theme.christmas" x-cloak class="christmas-empty-state rounded-2xl py-6 px-5 text-center border border-dashed border-emerald-500/30 dark:border-emerald-500/25 relative overflow-hidden shadow-sm backdrop-blur-md"
                             style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.08), transparent 70%);">
                            {{-- Holiday Ribbon Sparkles --}}
                            <div class="gift-bounce w-14 h-14 mx-auto mb-2 flex items-center justify-center filter drop-shadow-[0_8px_16px_rgba(239,68,68,0.35)]">
                                <svg width="50" height="50" viewBox="0 0 64 64" fill="none">
                                    <!-- Gift Box Base -->
                                    <rect x="12" y="24" width="40" height="32" rx="4" fill="#DC2626" class="dark:fill-[#EF4444]" stroke="#F87171" stroke-width="1.2"/>
                                    <!-- Box Lid -->
                                    <rect x="9" y="18" width="46" height="10" rx="3" fill="#B91C1C" class="dark:fill-[#DC2626]" stroke="#F87171" stroke-width="1.2"/>
                                    <!-- Vertical Gold Ribbon -->
                                    <rect x="28" y="18" width="8" height="38" fill="#FBBF24" stroke="#F59E0B" stroke-width="0.8"/>
                                    <!-- Horizontal Gold Ribbon -->
                                    <rect x="12" y="36" width="40" height="8" fill="#FBBF24" stroke="#F59E0B" stroke-width="0.8"/>
                                    <!-- Festive Ribbon Bow -->
                                    <path d="M 24 18 C 16 8 26 6 32 18 C 38 6 48 8 40 18 Z" fill="#FDE68A" stroke="#F59E0B" stroke-width="1"/>
                                    <circle cx="32" cy="18" r="3" fill="#F59E0B"/>
                                    <!-- Holly accent on box -->
                                    <circle cx="44" cy="22" r="2" fill="#10B981"/>
                                    <circle cx="47" cy="20" r="1.5" fill="#EF4444"/>
                                </svg>
                            </div>
                            <p class="text-base font-bold text-[#0F172A] dark:text-[#F8FAFC]">All Wrapped Up! No active deliverables</p>
                            <p class="text-xs text-[#64748B] dark:text-[#94A3B8] mt-0.5 max-w-sm mx-auto">All holiday deliverables are checked off your list. Relax by the fireside!</p>
                            @can('create-deliverable')
                            <div class="mt-3">
                                <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#10B981] hover:bg-[#059669] text-white shadow-xs transition-colors">
                                    View Projects & Tasks
                                </a>
                            </div>
                            @endcan
                        </div>
                    @endforelse
                </div>

                {{-- Overdue Deliverables Section --}}
                @if(count($groupedOverdue) > 0)
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest flex items-center gap-1.5">
                            Overdue Deliverables
                            <span class="bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 px-1.5 py-0.5 text-[9px] font-extrabold rounded-full">{{ count($groupedOverdue) }}</span>
                        </p>
                    </div>
                    @foreach($groupedOverdue as $group)
                        <x-dashboard-deliverable-card :group="$group" />
                    @endforeach
                </div>
                @endif

                {{-- Completed --}}
                @if($deliverables->where('status', 'Done')->count() > 0)
                <div>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3">Completed</p>
                    @php
                        $completedTasks = $deliverables->where('status', 'Done')->sortByDesc(function($t) {
                            return $t->created_at ? $t->created_at->timestamp : $t->id;
                        });
                        $groupedCompleted = [];
                        foreach($completedTasks as $t) {
                            $key = 'single_' . $t->id;
                            $groupedCompleted[$key] = [
                                'is_batch' => false,
                                'parent' => $t->parent,
                                'project' => $t->project,
                                'subtasks' => [$t]
                            ];
                        }
                    @endphp
                    @foreach($groupedCompleted as $group)
                    @php
                        $isBatch = $group['is_batch'];
                        $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
                        $project = $group['project'];
                        $subtasks = $group['subtasks'];
                        $targetCompletedId = $isBatch ? ($group['parent']->id ?? null) : ($subtasks[0]->id ?? null);

                        $completedType = null;
                        if (!$isBatch && !empty($subtasks[0])) {
                            $t = $subtasks[0];
                            if (!empty($t->post_type)) {
                                $completedType = $t->post_type . ($t->flow_type === 'direct_design' ? ' (Direct Design)' : '');
                            } elseif ($t->flow_type === 'direct_design') {
                                $completedType = 'Direct Design';
                            } elseif (!empty($t->task_type) && $t->task_type !== 'Deliverable') {
                                $completedType = $t->task_type;
                            }
                        } elseif ($isBatch && !empty($group['parent'])) {
                            $p = $group['parent'];
                            if (!empty($p->post_type)) {
                                $completedType = $p->post_type . ($p->flow_type === 'direct_design' ? ' (Direct Design)' : '');
                            } elseif ($p->flow_type === 'direct_design') {
                                $completedType = 'Direct Design';
                            }
                        }
                    @endphp
                    <a href="{{ route('projects.show', ['project' => $project->id, 'deliverable_id' => $targetCompletedId]) }}"
                       class="flex flex-col bg-white dark:bg-[#111827] rounded-xl px-5 py-4 border border-gray-200 dark:border-white/[0.08] card-shadow mb-2.5 transition-all hover:border-gray-300 dark:hover:border-white/20 group block">
                        <div class="flex items-start justify-between min-w-0">
                            <div class="flex items-start gap-4 min-w-0">
                                <svg width="12" height="12" fill="#10b981" viewBox="0 0 16 16" style="flex-shrink:0; margin-top:4px;">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                                </svg>
                                <div class="min-w-0">
                                    <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-200 line-through decoration-slate-400 dark:decoration-slate-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $mainTitle }}</h3>
                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[10px]">
                                        @if(!empty($project->brand->name))
                                            <span class="text-slate-500 dark:text-slate-400 font-medium">Brand: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->brand->name }}</span></span>
                                        @endif
                                        @if($project)
                                            <span class="text-gray-300 dark:text-slate-600">·</span>
                                            <span class="text-slate-500 dark:text-slate-400 font-medium">Project: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->name }}</span></span>
                                        @endif
                                        @if($completedType)
                                            <span class="text-gray-300 dark:text-slate-600">·</span>
                                            <span class="text-slate-500 dark:text-slate-400 font-medium">Type: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $completedType }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/60 flex-shrink-0 ml-4 shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                                Closed
                            </span>
                        </div>
                        @if($isBatch)
                            <div class="mt-2.5 ml-7 pl-3 border-l-2 border-gray-200 dark:border-slate-700 flex flex-col gap-1.5">
                                @foreach($subtasks as $t)
                                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 line-through decoration-slate-400 dark:decoration-slate-500">{{ $t->title }}</span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif

            </div>

            {{-- Sidebar --}}
            <div class="flex flex-col gap-6">

                {{-- Upcoming deadlines --}}
                <div class="deadlines-card bg-white dark:bg-[#111827] rounded-2xl p-6 border border-gray-200 dark:border-white/[0.08] card-shadow">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-[13px] font-extrabold text-slate-900 dark:text-white">Upcoming Deadlines</h3>
                        <svg class="w-4 h-4 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @php
                        $upcomingGroups = collect($groupedActive)->filter(function($g) { return !empty($g['deadline']); })->sortBy(function($g) { return $g['deadline']; })->take(5);
                    @endphp
                    @forelse($upcomingGroups as $group)
                    @php
                        $isBatch = $group['is_batch'];
                        $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
                        $project = $group['project'];
                        $deadline = $group['deadline'];
                        $targetUpcomingId = $isBatch ? ($group['parent']->id ?? null) : ($group['subtasks'][0]->id ?? null);
                    @endphp
                    <a href="{{ route('projects.show', ['project' => $project->id, 'deliverable_id' => $targetUpcomingId]) }}" class="flex flex-col gap-2 mb-4 last:mb-0 group block">
                        <div class="flex items-start gap-3">
                            @php $daysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($deadline)->startOfDay(), false); @endphp
                            <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-black border
                                {{ $daysLeft < 0 ? 'bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 border-red-300 dark:border-red-800/60' : ($daysLeft <= 2 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-amber-300 dark:border-amber-800/60' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-white/5') }}">
                                {{ \Carbon\Carbon::parse($deadline)->format('d') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $mainTitle }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($deadline)->format('M j') }}
                                    @if($daysLeft < 0)
                                        · <span class="text-red-600 dark:text-red-400 font-bold">{{ abs($daysLeft) }}d overdue</span>
                                    @elseif($daysLeft === 0)
                                        · <span class="text-amber-600 dark:text-amber-400 font-bold">today</span>
                                    @elseif($daysLeft <= 7)
                                        · <span class="text-slate-700 dark:text-slate-300 font-bold">{{ $daysLeft }}d left</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($isBatch)
                            <div class="ml-11 pl-2 border-l-2 border-gray-100 dark:border-slate-800 flex flex-col gap-1">
                                @foreach($group['subtasks'] as $t)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-semibold text-gray-500 dark:text-slate-400">{{ $t->title }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </a>
                    @empty
                    <div x-show="!$store.theme.halloween && !$store.theme.christmas">
                        <p class="text-[12px] text-gray-400 dark:text-slate-500 font-medium text-center py-4">No upcoming deadlines.</p>
                    </div>
                    <div x-show="$store.theme.halloween" x-cloak class="text-center py-6 relative overflow-hidden">
                        <div class="w-8 h-8 mx-auto mb-2 rounded-xl bg-orange-500/10 dark:bg-orange-500/10 flex items-center justify-center border border-orange-500/20">
                            <svg class="w-4 h-4 text-[#C2410C] dark:text-[#F97316]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/>
                            </svg>
                        </div>
                        <p class="text-[12px] font-semibold text-[#121118] dark:text-[#F4F0F8]">No upcoming deadlines</p>
                        <p class="text-[10px] text-[#524E59] dark:text-[#A198AC] mt-0.5">Your schedule is completely clear</p>
                    </div>
                    <div x-show="$store.theme.christmas" x-cloak class="text-center py-6 relative overflow-hidden">
                        <div class="w-8 h-8 mx-auto mb-2 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20">
                            <svg class="w-4 h-4 text-[#047857] dark:text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/>
                            </svg>
                        </div>
                        <p class="text-[12px] font-semibold text-[#0F172A] dark:text-[#F8FAFC]">No upcoming deadlines</p>
                        <p class="text-[10px] text-[#64748B] dark:text-[#94A3B8] mt-0.5">Your holiday schedule is clear</p>
                    </div>
                    @endforelse
                </div>

                {{-- Priority breakdown --}}
                @php
                    $highCount   = $deliverables->where('status','!=','Done')->where('priority','High Priority')->count();
                    $mediumCount = $deliverables->where('status','!=','Done')->where('priority','Medium')->count();
                    $lowCount    = $deliverables->where('status','!=','Done')->whereNotIn('priority',['High Priority','Medium'])->count();
                    $total       = $deliverables->where('status','!=','Done')->count();
                @endphp
                @if($total > 0)
                <div class="sidebar-card bg-white dark:bg-[#111827] rounded-2xl p-6 border border-gray-200 dark:border-white/[0.08] card-shadow">
                    <h3 class="text-[13px] font-extrabold text-slate-900 dark:text-white mb-5">Priority Breakdown</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <svg width="12" height="12" fill="#ef4444" viewBox="0 0 16 16" style="flex-shrink:0;">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                                </svg>
                                <span class="text-[12px] font-semibold text-slate-700 dark:text-slate-300">High Priority (Urgent)</span>
                            </div>
                            <span class="text-[12px] font-extrabold text-slate-900 dark:text-white">{{ $highCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <svg width="12" height="12" fill="#f59e0b" viewBox="0 0 16 16" style="flex-shrink:0;">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                                </svg>
                                <span class="text-[12px] font-semibold text-slate-700 dark:text-slate-300">Medium (Standard)</span>
                            </div>
                            <span class="text-[12px] font-extrabold text-slate-900 dark:text-white">{{ $mediumCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <svg width="12" height="12" fill="#64748b" viewBox="0 0 16 16" style="flex-shrink:0;">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                                </svg>
                                <span class="text-[12px] font-semibold text-slate-700 dark:text-slate-300">Low Priority (Normal)</span>
                            </div>
                            <span class="text-[12px] font-extrabold text-slate-900 dark:text-white">{{ $lowCount }}</span>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>
</x-layout>
