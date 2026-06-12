<x-layout title="Subtask Types">
    <style>
        .settings-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .type-card {
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-primary);
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .type-workflow {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 4px 10px;
            border-radius: 8px;
        }
        .workflow-retainer { background: #eff6ff; color: #1e40af; }
        .workflow-campaign { background: #fff7ed; color: #9a3412; }
        
        .delete-btn {
            color: #94a3b8;
            transition: color 0.2s;
            cursor: pointer;
            padding: 4px;
        }
        .delete-btn:hover {
            color: #ef4444;
        }
    </style>

    <div class="settings-container">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-1">Subtask Types</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium tracking-tight">Configure available deliverable types for different workflows.</p>
            </div>
            <a href="{{ route('admin.settings') }}" class="px-5 py-2.5 bg-gray-100 dark:bg-slate-800 rounded-xl text-xs font-bold text-gray-600 dark:text-slate-400 hover:bg-gray-200 transition-all uppercase tracking-wider">Back to Settings</a>
        </div>

        <!-- Add New Type -->
        <div class="bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-slate-800 rounded-xl p-6 mb-8 card-shadow relative overflow-hidden">
            <!-- Decorative gradient orb -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center gap-4 mb-6 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight">Create Subtask Type</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5">Add a new deliverable category</p>
                </div>
            </div>
            
            <form action="{{ route('subtask-types.store') }}" method="POST" class="flex flex-wrap items-end gap-5 relative z-10">
                @csrf
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-[11px] font-semibold text-slate-400 mb-2 ml-1">Type Name</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <input type="text" name="name" placeholder="e.g. TikTok Reel, Newspaper Ad..." required class="w-full bg-white dark:bg-[#1E293B]/50 border border-gray-100 dark:border-slate-800 rounded-lg pl-10 pr-4 py-2 text-[13px] font-medium text-gray-900 dark:text-white placeholder-slate-400 outline-none focus:bg-white dark:focus:bg-[#1E293B] focus:border-blue-500 dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all">
                    </div>
                </div>
                <div class="w-[240px]">
                    <label class="block text-[11px] font-semibold text-slate-400 mb-2 ml-1">Workflow Category</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <select name="workflow_type" required class="w-full bg-white dark:bg-[#1E293B]/50 border border-gray-100 dark:border-slate-800 rounded-lg pl-10 pr-9 py-2 text-[13px] font-medium text-gray-900 dark:text-white outline-none focus:bg-white dark:focus:bg-[#1E293B] focus:border-blue-500 dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all appearance-none cursor-pointer">
                            <option value="retainer">Retainer Workflow</option>
                            <option value="campaign">Campaign / Pitch</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-[#0055D4] hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-[12px] font-bold transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] flex items-center justify-center gap-2">
                        Add Type
                    </button>
                </div>
            </form>
        </div>

        <!-- List Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Retainer Types -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-2 h-6 bg-blue-500 rounded-full shadow-lg shadow-blue-500/20"></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Retainer Types</h2>
                </div>
                <div class="space-y-3">
                    @foreach($types->where('workflow_type', 'retainer') as $type)
                        <div class="group flex items-center justify-between bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-slate-800/60 rounded-[18px] p-4 hover:border-blue-500/30 dark:hover:border-blue-500/30 hover:shadow-md hover:shadow-blue-500/5 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-[12px] bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 dark:text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <span class="font-extrabold text-[13px] text-gray-900 dark:text-white">{{ $type->name }}</span>
                            </div>
                            <form action="{{ route('subtask-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete this type?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2.5 text-gray-300 dark:text-slate-600 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 active:scale-95" title="Delete Type">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Campaign Types -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-2 h-6 bg-orange-500 rounded-full shadow-lg shadow-orange-500/20"></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Campaign / Pitch Types</h2>
                </div>
                <div class="space-y-3">
                    @foreach($types->where('workflow_type', 'campaign') as $type)
                        <div class="group flex items-center justify-between bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-slate-800/60 rounded-[18px] p-4 hover:border-orange-500/30 dark:hover:border-orange-500/30 hover:shadow-md hover:shadow-orange-500/5 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-[12px] bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-500 dark:text-orange-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </div>
                                <span class="font-extrabold text-[13px] text-gray-900 dark:text-white">{{ $type->name }}</span>
                            </div>
                            <form action="{{ route('subtask-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete this type?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2.5 text-gray-300 dark:text-slate-600 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 active:scale-95" title="Delete Type">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layout>
