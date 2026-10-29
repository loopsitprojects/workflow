<x-layout :title="($deliverableData['title'] ?? 'Deliverable Details') . ' — Analytics Report'">
    <div class="flex flex-col gap-6 max-w-5xl mx-auto pb-16">

        {{-- Top Breadcrumb & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('analytics.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Analytics Dashboard</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-extrabold bg-gray-200 dark:bg-slate-800 text-gray-700 dark:text-slate-300">
                        #{{ $deliverableData['id'] }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                        {{ $deliverableData['task_type'] }}
                    </span>
                    @if($deliverableData['state_label'] === 'Closed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                            Closed
                        </span>
                    @elseif($deliverableData['state_label'] === 'Completed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Completed
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                            Active
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $deliverableData['priority'] === 'Urgent' ? 'bg-red-500/10 text-red-500' : ($deliverableData['priority'] === 'High' ? 'bg-amber-500/10 text-amber-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                        {{ $deliverableData['priority'] }} Priority
                    </span>
                </div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight mt-1.5">
                    {{ $deliverableData['title'] }}
                </h1>
                <p class="text-xs text-gray-500 dark:text-slate-400 font-medium mt-1">
                    <span class="font-bold text-gray-700 dark:text-slate-200">{{ $deliverableData['brand']['name'] ?? 'N/A' }}</span>
                    <span>•</span>
                    <span>{{ $deliverableData['project']['name'] ?? 'N/A' }}</span>
                    <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-gray-100 dark:bg-slate-800 text-gray-500 ml-1">
                        {{ $deliverableData['project']['workflow_type'] ?? 'campaign' }}
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 card-shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        {{-- Progress Ribbon --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border border-gray-100 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-base">
                    {{ $deliverableData['progress_percent'] }}%
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Current Workflow Stage</span>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                        {{ $deliverableData['approval_stage'] ?? $deliverableData['status'] }}
                    </h3>
                </div>
            </div>

            <div class="w-full sm:w-72">
                <div class="flex justify-between text-xs font-bold mb-1.5">
                    <span class="text-gray-400">Completion Milestone</span>
                    <span class="text-blue-600 dark:text-blue-400">{{ $deliverableData['progress_percent'] }}%</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full" style="width: {{ $deliverableData['progress_percent'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- 3 Key Information Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Dates --}}
            <div class="bg-white dark:bg-[#0F172A] p-5 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-3">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Timeline & Dates</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Open Date:</span>
                        <span class="font-bold text-gray-900 dark:text-white font-mono">{{ $deliverableData['schedule']['open_date'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Due Date:</span>
                        <span class="font-bold font-mono {{ !empty($deliverableData['schedule']['is_overdue']) ? 'text-red-500 font-bold' : 'text-gray-900 dark:text-white' }}">
                            {{ $deliverableData['schedule']['due_date'] ?? 'None' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Closed Date:</span>
                        <span class="font-bold font-mono {{ !empty($deliverableData['schedule']['closed_date']) ? 'text-emerald-500' : 'text-blue-500' }}">
                            {{ $deliverableData['schedule']['closed_date'] ?? 'Still Open (Active)' }}
                        </span>
                    </div>
                    @if($deliverableData['schedule']['designer_deadline'])
                        <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-slate-800">
                            <span class="text-gray-500">Designer Due:</span>
                            <span class="font-bold text-amber-500 font-mono">{{ $deliverableData['schedule']['designer_deadline'] }}</span>
                        </div>
                    @endif
                    @if($deliverableData['schedule']['revision_deadline'])
                        <div class="flex justify-between">
                            <span class="text-gray-500">Revision Due:</span>
                            <span class="font-bold text-red-500 font-mono">{{ $deliverableData['schedule']['revision_deadline'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Personnel --}}
            <div class="bg-white dark:bg-[#0F172A] p-5 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-3">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Assigned Team</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Assignee:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $deliverableData['assignee_name'] ?? 'Unassigned' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Writer:</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $deliverableData['team']['writer']['name'] ?? 'None' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Designer:</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $deliverableData['team']['designer']['name'] ?? 'None' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Approver:</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $deliverableData['team']['approver']['name'] ?? 'None' }}</span>
                    </div>
                </div>
            </div>

            {{-- Quality & Metrics --}}
            <div class="bg-white dark:bg-[#0F172A] p-5 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-3">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Quality & Assets</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Revision Cycles:</span>
                        <span class="font-bold {{ $deliverableData['revisions']['count'] > 0 ? 'text-amber-500' : 'text-emerald-500' }}">
                            {{ $deliverableData['revisions']['count'] }} rounds
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Artwork Assets:</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300">
                            {{ count($deliverableData['assets']['artwork_files']) + count($deliverableData['assets']['artwork_urls']) }} items
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description (if any) --}}
        @if(!empty($deliverableData['description']))
            <div class="bg-white dark:bg-[#0F172A] p-5 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Description / Brief</span>
                <div class="text-xs text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                    {{ $deliverableData['description'] }}
                </div>
            </div>
        @endif

        {{-- Creative Content (Concept, Caption, Post Copy) --}}
        <div class="bg-white dark:bg-[#0F172A] p-6 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-4">
            <h3 class="text-sm font-black text-gray-900 dark:text-white">Creative Content & Copy</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                {{-- Concept --}}
                <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">Concept</span>
                    <div class="text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $deliverableData['content']['concept'] ?? 'No concept recorded.' }}
                    </div>
                </div>

                {{-- Caption --}}
                <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">Caption</span>
                    <div class="text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $deliverableData['content']['caption'] ?? 'No caption recorded.' }}
                    </div>
                </div>
            </div>

            {{-- Post Copy --}}
            <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">Primary Post Copy</span>
                <div class="text-xs font-mono text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-line bg-white dark:bg-[#0F172A] p-4 rounded-xl border border-gray-100 dark:border-slate-800">
                    {{ $deliverableData['content']['post_copy'] ?? 'No post copy recorded.' }}
                </div>
            </div>
        </div>

        {{-- Artwork & Deliverables --}}
        <div class="bg-white dark:bg-[#0F172A] p-6 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-4">
            <h3 class="text-sm font-black text-gray-900 dark:text-white">Artwork & Final Deliverables</h3>

            @if(empty($deliverableData['assets']['artwork_files']) && empty($deliverableData['assets']['artwork_urls']))
                <p class="text-xs text-gray-400 py-3">No final artwork uploaded yet.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($deliverableData['assets']['artwork_files'] as $file)
                        <div class="group relative rounded-xl overflow-hidden border border-gray-200 dark:border-slate-800 bg-gray-100 dark:bg-slate-900 aspect-video flex items-center justify-center">
                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $file))
                                <img src="{{ $file }}" alt="Artwork" class="w-full h-full object-cover">
                            @elseif(preg_match('/\.(mp4|webm|mov)$/i', $file))
                                <video src="{{ $file }}" class="w-full h-full object-cover" preload="metadata"></video>
                            @else
                                <div class="p-2 text-center text-[10px] font-bold text-gray-500">Document Asset</div>
                            @endif
                            <a href="{{ $file }}" target="_blank"
                               class="absolute inset-0 bg-slate-900/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 text-white font-bold text-xs">
                                <span>View Asset</span>
                            </a>
                        </div>
                    @endforeach
                </div>

                @if(!empty($deliverableData['assets']['artwork_urls']))
                    <div class="space-y-2 pt-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">External Artwork Links:</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($deliverableData['assets']['artwork_urls'] as $url)
                                <a href="{{ $url }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                    <span>{{ Str::limit($url, 45) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Reference Materials --}}
        @if(!empty($deliverableData['assets']['reference_files']) || !empty($deliverableData['assets']['reference_urls']))
            <div class="bg-white dark:bg-[#0F172A] p-6 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-3">
                <h3 class="text-sm font-black text-gray-900 dark:text-white">Reference Materials</h3>
                <div class="flex flex-wrap gap-2 text-xs">
                    @foreach($deliverableData['assets']['reference_files'] as $ref)
                        <a href="{{ $ref }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 font-semibold">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Reference File</span>
                        </a>
                    @endforeach
                    @foreach($deliverableData['assets']['reference_urls'] as $url)
                        <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-blue-600 dark:text-blue-400 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>{{ $url }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Revision History (if any) --}}
        @if(!empty($deliverableData['revisions']['history']))
            <div class="bg-white dark:bg-[#0F172A] p-6 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-3">
                <h3 class="text-sm font-black text-gray-900 dark:text-white">
                    Revision History ({{ count($deliverableData['revisions']['history']) }})
                </h3>
                <div class="divide-y divide-gray-100 dark:divide-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden">
                    @foreach($deliverableData['revisions']['history'] as $rev)
                        <div class="p-4 space-y-1 text-xs">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $rev['user_name'] }}</span>
                                <span class="text-gray-400 font-mono">{{ $rev['created_at'] }}</span>
                            </div>
                            <p class="text-gray-700 dark:text-slate-300 leading-relaxed">{{ $rev['instructions'] }}</p>
                            @if(!empty($rev['image_url']))
                                <div class="pt-1">
                                    <a href="{{ $rev['image_url'] }}" target="_blank" class="text-blue-500 font-bold hover:underline">View Markup Screenshot</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Approval Audit Trail (if any) --}}
        @if(!empty($deliverableData['approvals']))
            <div class="bg-white dark:bg-[#0F172A] p-6 rounded-2xl border border-gray-100 dark:border-slate-800 card-shadow space-y-3">
                <h3 class="text-sm font-black text-gray-900 dark:text-white">
                    Approval Audit Trail ({{ count($deliverableData['approvals']) }})
                </h3>
                <div class="divide-y divide-gray-100 dark:divide-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden text-xs">
                    @foreach($deliverableData['approvals'] as $appr)
                        <div class="p-3.5 flex items-center justify-between gap-3 text-[11px]">
                            <div>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $appr['stage'] }}</span>
                                <span class="text-gray-400"> approved by </span>
                                <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $appr['user_name'] }}</span>
                                @if(!empty($appr['user_role']))
                                    <span class="text-[10px] text-gray-400">({{ $appr['user_role'] }})</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 font-mono">{{ $appr['created_at'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-layout>
