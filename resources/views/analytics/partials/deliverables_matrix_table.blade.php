<div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Deliverables List</h3>
            <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-0.5">
                Showing {{ $deliverables->count() }} deliverables in the active date period.
            </p>
        </div>

        {{-- Quick Search Input --}}
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="searchQuery" placeholder="Search title, brand, assignee..."
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <div class="overflow-x-auto no-scrollbar">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                    <th class="py-3 px-3">Deliverable</th>
                    <th class="py-3 px-3">Brand & Project</th>
                    <th class="py-3 px-3 text-center">State</th>
                    <th class="py-3 px-3">Open Date</th>
                    <th class="py-3 px-3">Due Date</th>
                    <th class="py-3 px-3">Closed Date</th>
                    <th class="py-3 px-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-800/60 text-xs">
                @forelse($deliverables as $d)
                    @php
                        $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
                        $isCompleted = ($d->status === 'Done' || $isClosed);
                        $stateLabel = $isCompleted ? 'Completed' : 'Active';

                        $openDate = $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('M d, Y') : '—';
                        $dueDate = $d->deadline ? \Carbon\Carbon::parse($d->deadline)->format('M d, Y') : null;
                        $isOverdue = !$isCompleted && $d->deadline && \Carbon\Carbon::parse($d->deadline)->isPast();

                        // Closed date
                        $closedDate = null;
                        if ($isCompleted || $isClosed) {
                            $closingAppr = $d->relationLoaded('approvalsHistory')
                                ? $d->approvalsHistory->first(fn($a) => in_array(strtolower((string)$a->stage), ['closed', 'close', 'approved', 'final approval', 'scheduled']))
                                : null;
                            $closedAt = $closingAppr?->created_at ?? $d->updated_at;
                            $closedDate = $closedAt ? \Carbon\Carbon::parse($closedAt)->format('M d, Y') : 'Done';
                        }
                    @endphp
                    <tr x-show="!searchQuery || '{{ strtolower(addslashes($d->title . ' ' . ($d->project?->brand?->name ?? '') . ' ' . ($d->project?->name ?? '') . ' ' . ($d->assignee_name ?? '') . ' ' . $stateLabel)) }}'.includes(searchQuery.toLowerCase())"
                        class="hover:bg-gray-50/60 dark:hover:bg-slate-900/40 transition">
                        <td class="py-3.5 px-3 font-semibold text-gray-900 dark:text-white">
                            <button type="button"
                                    @click="viewDeliverableDetails({{ $d->id }})"
                                    class="text-left hover:text-blue-500 dark:hover:text-blue-400 transition line-clamp-1 cursor-pointer font-bold">
                                {{ $d->title }}
                            </button>
                            <span class="text-[10px] text-gray-400 font-normal">#{{ $d->id }} • {{ $d->subtask_type ?? $d->task_type ?? 'Deliverable' }}</span>
                        </td>
                        <td class="py-3.5 px-3">
                            <p class="font-bold text-gray-800 dark:text-slate-200">{{ $d->project?->brand?->name ?? 'N/A' }}</p>
                            <span class="text-[10px] text-gray-400 truncate max-w-[130px] block">{{ $d->project?->name ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($isCompleted || $isClosed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    Completed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                    Active
                                </span>
                            @endif
                        </td>
                        {{-- Open Date --}}
                        <td class="py-3.5 px-3 font-mono text-[11px] text-gray-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $openDate }}
                        </td>
                        {{-- Due Date --}}
                        <td class="py-3.5 px-3 font-mono text-[11px] whitespace-nowrap">
                            @if($dueDate)
                                <span class="{{ $isOverdue ? 'text-red-500 font-bold' : 'text-gray-700 dark:text-slate-300' }}">
                                    {{ $dueDate }}
                                </span>
                                @if($isOverdue)
                                    <span class="block text-[9px] font-extrabold text-red-500 uppercase tracking-tighter">Overdue</span>
                                @endif
                            @else
                                <span class="text-gray-400">None</span>
                            @endif
                        </td>
                        {{-- Closed Date --}}
                        <td class="py-3.5 px-3 font-mono text-[11px] whitespace-nowrap">
                            @if($closedDate)
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $closedDate }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <button type="button"
                                    @click="viewDeliverableDetails({{ $d->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition cursor-pointer">
                                <span>View</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">No deliverables found for this date range and filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
