{{-- Deliverable Read-Only Details Modal --}}
<div x-show="detailModalOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="closeDeliverableModal()">

    {{-- Backdrop blur --}}
    <div x-show="detailModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeDeliverableModal()"
         class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm transition-opacity"></div>

    {{-- Modal Dialog Container --}}
    <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
        <div x-show="detailModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop
             class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#0F172A] text-left shadow-2xl transition-all w-full max-w-4xl border border-gray-100 dark:border-slate-800 flex flex-col max-h-[92vh]">

            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-start justify-between gap-4 bg-gray-50/50 dark:bg-slate-900/50 flex-shrink-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                        {{-- Deliverable ID --}}
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-extrabold bg-gray-200 dark:bg-slate-800 text-gray-700 dark:text-slate-300">
                            #<span x-text="deliverableDetail?.id || selectedDeliverableId || '—'"></span>
                        </span>

                        {{-- Subtask / Task Type --}}
                        <template x-if="deliverableDetail?.task_type">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20"
                                  x-text="deliverableDetail.task_type">
                            </span>
                        </template>

                        {{-- State Badge --}}
                        <template x-if="deliverableDetail?.state_label">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                  :class="{
                                      'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20': deliverableDetail.state_label === 'Closed',
                                      'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': deliverableDetail.state_label === 'Completed',
                                      'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20': deliverableDetail.state_label === 'Active'
                                  }"
                                  x-text="deliverableDetail.state_label">
                            </span>
                        </template>

                        {{-- Priority Badge --}}
                        <template x-if="deliverableDetail?.priority">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="{
                                      'bg-red-500/10 text-red-500 border border-red-500/20': deliverableDetail.priority === 'Urgent',
                                      'bg-amber-500/10 text-amber-500 border border-amber-500/20': deliverableDetail.priority === 'High',
                                      'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400': deliverableDetail.priority !== 'Urgent' && deliverableDetail.priority !== 'High'
                                  }"
                                  x-text="deliverableDetail.priority + ' Priority'">
                            </span>
                        </template>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight break-words"
                        x-text="deliverableDetail?.title || 'Loading deliverable details...'"></h2>

                    <p class="text-xs text-gray-500 dark:text-slate-400 font-medium mt-0.5 flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-gray-700 dark:text-slate-200" x-text="deliverableDetail?.brand?.name || 'Brand'"></span>
                        <span>•</span>
                        <span x-text="deliverableDetail?.project?.name || 'Project'"></span>
                        <template x-if="deliverableDetail?.project?.workflow_type">
                            <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-gray-100 dark:bg-slate-800 text-gray-500"
                                  x-text="deliverableDetail.project.workflow_type"></span>
                        </template>
                    </p>
                </div>

                {{-- Action Controls --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <template x-if="deliverableDetail?.id">
                        <a :href="'/analytics/deliverables/' + deliverableDetail.id"
                           target="_blank"
                           title="Open in standalone tab"
                           class="p-2 rounded-xl text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </template>

                    <button type="button"
                            @click="closeDeliverableModal()"
                            class="p-2 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body (Scrollable) --}}
            <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">

                {{-- Loading Skeleton --}}
                <div x-show="detailLoading" class="space-y-4 py-8">
                    <div class="animate-pulse flex space-x-4">
                        <div class="flex-1 space-y-4 py-1">
                            <div class="h-4 bg-gray-200 dark:bg-slate-800 rounded w-3/4"></div>
                            <div class="space-y-2">
                                <div class="h-4 bg-gray-200 dark:bg-slate-800 rounded"></div>
                                <div class="h-4 bg-gray-200 dark:bg-slate-800 rounded w-5/6"></div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                        <div class="h-24 bg-gray-100 dark:bg-slate-800/60 rounded-2xl animate-pulse"></div>
                        <div class="h-24 bg-gray-100 dark:bg-slate-800/60 rounded-2xl animate-pulse"></div>
                        <div class="h-24 bg-gray-100 dark:bg-slate-800/60 rounded-2xl animate-pulse"></div>
                    </div>
                </div>

                {{-- Error Message --}}
                <div x-show="detailError" class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400">
                    <p class="font-bold" x-text="detailError"></p>
                </div>

                {{-- Loaded Deliverable Content --}}
                <div x-show="!detailLoading && deliverableDetail" class="space-y-6">

                    {{-- Progress & Current Stage Ribbon --}}
                    <div class="bg-gray-50 dark:bg-slate-900/60 rounded-2xl p-4 border border-gray-100 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                                <span x-text="(deliverableDetail?.progress_percent || 0) + '%'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Current Workflow Stage</span>
                                <h4 class="text-sm font-extrabold text-gray-900 dark:text-white"
                                    x-text="deliverableDetail?.approval_stage || deliverableDetail?.status || 'In Progress'"></h4>
                            </div>
                        </div>

                        <div class="w-full sm:w-64">
                            <div class="flex justify-between text-[11px] font-bold mb-1">
                                <span class="text-gray-400">Milestone Progress</span>
                                <span class="text-blue-600 dark:text-blue-400" x-text="(deliverableDetail?.progress_percent || 0) + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-full rounded-full transition-all duration-500"
                                     :style="'width: ' + (deliverableDetail?.progress_percent || 0) + '%'"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 3 Key Information Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Card 1: Deadlines & Dates --}}
                        <div class="bg-white dark:bg-[#0F172A] p-4 rounded-2xl border border-gray-100 dark:border-slate-800 space-y-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Timeline & Dates</span>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Open Date:</span>
                                    <span class="font-bold text-gray-900 dark:text-white font-mono" x-text="deliverableDetail?.schedule?.open_date || '—'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Due Date:</span>
                                    <span class="font-bold font-mono"
                                          :class="deliverableDetail?.schedule?.is_overdue ? 'text-red-500 font-bold' : 'text-gray-900 dark:text-white'"
                                          x-text="deliverableDetail?.schedule?.due_date || 'None'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Closed Date:</span>
                                    <span class="font-bold font-mono"
                                          :class="deliverableDetail?.schedule?.closed_date ? 'text-emerald-500' : 'text-blue-500'"
                                          x-text="deliverableDetail?.schedule?.closed_date || 'Active / Open'"></span>
                                </div>
                                <template x-if="deliverableDetail?.schedule?.designer_deadline">
                                    <div class="flex justify-between pt-1 border-t border-gray-50 dark:border-slate-800">
                                        <span class="text-gray-500 dark:text-slate-400">Designer Due:</span>
                                        <span class="font-bold text-amber-500 font-mono" x-text="deliverableDetail.schedule.designer_deadline"></span>
                                    </div>
                                </template>
                                <template x-if="deliverableDetail?.schedule?.revision_deadline">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-slate-400">Revision Due:</span>
                                        <span class="font-bold text-red-500 font-mono" x-text="deliverableDetail.schedule.revision_deadline"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Card 2: Team & Assignments --}}
                        <div class="bg-white dark:bg-[#0F172A] p-4 rounded-2xl border border-gray-100 dark:border-slate-800 space-y-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Assigned Personnel</span>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Assignee:</span>
                                    <span class="font-bold text-gray-900 dark:text-white" x-text="deliverableDetail?.assignee_name || 'Unassigned'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Writer:</span>
                                    <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="deliverableDetail?.team?.writer?.name || 'None'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Designer:</span>
                                    <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="deliverableDetail?.team?.designer?.name || 'None'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Approver:</span>
                                    <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="deliverableDetail?.team?.approver?.name || 'None'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Quality & Assets Overview --}}
                        <div class="bg-white dark:bg-[#0F172A] p-4 rounded-2xl border border-gray-100 dark:border-slate-800 space-y-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Quality & Metrics</span>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Revision Cycles:</span>
                                    <span class="font-bold" :class="(deliverableDetail?.revisions?.count || 0) > 0 ? 'text-amber-500' : 'text-emerald-500'"
                                          x-text="(deliverableDetail?.revisions?.count || 0) + ' rounds'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Artwork Assets:</span>
                                    <span class="font-semibold text-gray-700 dark:text-slate-300"
                                          x-text="(deliverableDetail?.assets?.artwork_files?.length || 0) + (deliverableDetail?.assets?.artwork_urls?.length || 0) + ' items'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-slate-400">Reference Assets:</span>
                                    <span class="font-semibold text-gray-700 dark:text-slate-300"
                                          x-text="(deliverableDetail?.assets?.reference_files?.length || 0) + (deliverableDetail?.assets?.reference_urls?.length || 0) + ' items'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description & Brief (if present) --}}
                    <template x-if="deliverableDetail?.description">
                        <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Description / Brief</span>
                            <div class="text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-line" x-text="deliverableDetail.description"></div>
                        </div>
                    </template>

                    {{-- Creative Content (Concept, Caption, Post Copy) --}}
                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center gap-2">
                            <span>Creative Content & Copy</span>
                            <span class="flex-1 h-px bg-gray-100 dark:bg-slate-800"></span>
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Concept --}}
                            <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800 flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Concept</span>
                                    <template x-if="deliverableDetail?.content?.concept">
                                        <button type="button"
                                                @click="navigator.clipboard.writeText(deliverableDetail.content.concept)"
                                                class="text-[10px] font-bold text-blue-500 hover:underline">
                                            Copy
                                        </button>
                                    </template>
                                </div>
                                <div class="text-gray-800 dark:text-slate-200 leading-relaxed flex-1 whitespace-pre-line"
                                     x-text="deliverableDetail?.content?.concept || 'No concept provided.'"></div>
                            </div>

                            {{-- Caption --}}
                            <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800 flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Caption</span>
                                    <template x-if="deliverableDetail?.content?.caption">
                                        <button type="button"
                                                @click="navigator.clipboard.writeText(deliverableDetail.content.caption)"
                                                class="text-[10px] font-bold text-blue-500 hover:underline">
                                            Copy
                                        </button>
                                    </template>
                                </div>
                                <div class="text-gray-800 dark:text-slate-200 leading-relaxed flex-1 whitespace-pre-line"
                                     x-text="deliverableDetail?.content?.caption || 'No caption provided.'"></div>
                            </div>
                        </div>

                        {{-- Post Copy / Full Text --}}
                        <div class="bg-gray-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-gray-100 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Primary Post Copy</span>
                                <template x-if="deliverableDetail?.content?.post_copy">
                                    <button type="button"
                                            @click="navigator.clipboard.writeText(deliverableDetail.content.post_copy)"
                                            class="text-[10px] font-bold text-blue-500 hover:underline">
                                        Copy Post Copy
                                    </button>
                                </template>
                            </div>
                            <div class="text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-line font-mono text-[11px] bg-white dark:bg-[#0F172A] p-3 rounded-xl border border-gray-100 dark:border-slate-800"
                                 x-text="deliverableDetail?.content?.post_copy || 'No post copy recorded.'"></div>
                        </div>
                    </div>

                    {{-- Artwork & Deliverables Gallery --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center gap-2">
                            <span>Artwork & Final Deliverables</span>
                            <span class="flex-1 h-px bg-gray-100 dark:bg-slate-800"></span>
                        </h4>

                        <template x-if="(!deliverableDetail?.assets?.artwork_files?.length) && (!deliverableDetail?.assets?.artwork_urls?.length)">
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-900/40 text-center text-gray-400">
                                No artwork files uploaded yet.
                            </div>
                        </template>

                        {{-- Artwork Files & Media --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            <template x-for="(file, idx) in (deliverableDetail?.assets?.artwork_files || [])" :key="idx">
                                <div class="group relative rounded-xl overflow-hidden border border-gray-200 dark:border-slate-800 bg-gray-100 dark:bg-slate-900 aspect-video flex items-center justify-center">
                                    <template x-if="file.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)">
                                        <img :src="file" alt="Artwork" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="file.match(/\.(mp4|webm|mov)$/i)">
                                        <video :src="file" class="w-full h-full object-cover" preload="metadata"></video>
                                    </template>
                                    <template x-if="!file.match(/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|mov)$/i)">
                                        <div class="flex flex-col items-center justify-center p-2 text-center">
                                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span class="text-[10px] font-bold text-gray-500">Document</span>
                                        </div>
                                    </template>
                                    <a :href="file" target="_blank"
                                       class="absolute inset-0 bg-slate-900/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 text-white font-bold text-[11px]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>View</span>
                                    </a>
                                </div>
                            </template>
                        </div>

                        {{-- Artwork External Links --}}
                        <template x-if="deliverableDetail?.assets?.artwork_urls?.length">
                            <div class="space-y-1.5 pt-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">External Artwork Links:</span>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(url, idx) in deliverableDetail.assets.artwork_urls" :key="idx">
                                        <a :href="url" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            <span x-text="url.length > 40 ? url.substring(0, 40) + '...' : url"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Reference Files & Links --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center gap-2">
                            <span>Reference Materials & Brief Assets</span>
                            <span class="flex-1 h-px bg-gray-100 dark:bg-slate-800"></span>
                        </h4>

                        <template x-if="(!deliverableDetail?.assets?.reference_files?.length) && (!deliverableDetail?.assets?.reference_urls?.length)">
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-900/40 text-center text-gray-400">
                                No reference materials attached.
                            </div>
                        </template>

                        <div class="flex flex-wrap gap-2">
                            <template x-for="(file, idx) in (deliverableDetail?.assets?.reference_files || [])" :key="idx">
                                <a :href="file" target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-gray-300 dark:hover:border-slate-700 transition">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Download Reference File</span>
                                </a>
                            </template>

                            <template x-for="(url, idx) in (deliverableDetail?.assets?.reference_urls || [])" :key="idx">
                                <a :href="url" target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-gray-300 dark:hover:border-slate-700 text-blue-600 dark:text-blue-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span x-text="url"></span>
                                </a>
                            </template>
                        </div>
                    </div>

                    {{-- Revisions History Log (if any) --}}
                    <template x-if="deliverableDetail?.revisions?.history?.length">
                        <div class="space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                <span>Revisions History (<span x-text="deliverableDetail.revisions.history.length"></span>)</span>
                            </h4>
                            <div class="divide-y divide-gray-100 dark:divide-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden bg-white dark:bg-[#0F172A]">
                                <template x-for="(rev, idx) in deliverableDetail.revisions.history" :key="rev.id">
                                    <div class="p-3.5 space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="rev.user_name"></span>
                                            <span class="text-gray-400 font-mono" x-text="rev.created_at"></span>
                                        </div>
                                        <p class="text-gray-700 dark:text-slate-300 leading-relaxed" x-text="rev.instructions"></p>
                                        <template x-if="rev.image_url">
                                            <div class="pt-1">
                                                <a :href="rev.image_url" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-500 hover:underline">
                                                    <span>View Annotated Screenshot</span>
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Approval Audit Trail (if any) --}}
                    <template x-if="deliverableDetail?.approvals?.length">
                        <div class="space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                <span>Approval Audit Trail (<span x-text="deliverableDetail.approvals.length"></span>)</span>
                            </h4>
                            <div class="divide-y divide-gray-100 dark:divide-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden bg-white dark:bg-[#0F172A]">
                                <template x-for="appr in deliverableDetail.approvals" :key="appr.id">
                                    <div class="p-3.5 flex items-center justify-between gap-3 text-[11px]">
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="appr.stage"></span>
                                            <span class="text-gray-400"> approved by </span>
                                            <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="appr.user_name"></span>
                                            <template x-if="appr.user_role">
                                                <span class="text-[10px] text-gray-400" x-text="'(' + appr.user_role + ')'"></span>
                                            </template>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-gray-400 font-mono" x-text="appr.created_at"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Subtasks List (if carousel / parent) --}}
                    <template x-if="deliverableDetail?.subtasks?.length">
                        <div class="space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                <span>Child Subtasks / Slides (<span x-text="deliverableDetail.subtasks.length"></span>)</span>
                            </h4>
                            <div class="divide-y divide-gray-100 dark:divide-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden bg-white dark:bg-[#0F172A]">
                                <template x-for="sub in deliverableDetail.subtasks" :key="sub.id">
                                    <div class="p-3.5 flex items-center justify-between gap-3 text-xs">
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="sub.title"></span>
                                            <template x-if="sub.post_type">
                                                <span class="ml-1.5 text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-100 dark:bg-slate-800 text-gray-500"
                                                      x-text="sub.post_type"></span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
                                                  x-text="sub.approval_stage || sub.status"></span>
                                            <button type="button"
                                                    @click="viewDeliverableDetails(sub.id)"
                                                    class="text-[11px] font-bold text-blue-500 hover:underline">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-900/50 flex items-center justify-between flex-shrink-0">
                <span class="text-[11px] text-gray-400">
                    Read-only deliverable details report.
                </span>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="closeDeliverableModal()"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#0F172A] border border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
