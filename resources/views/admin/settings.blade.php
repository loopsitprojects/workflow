<x-layout title="Admin Settings">
    <div class="flex flex-col gap-10">
        <!-- Header Section -->
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-1">Admin Settings</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 font-medium tracking-tight">Configure system settings and manage administrative deliverables.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- User Management Card -->
            <a href="{{ route('users.index') }}" class="group bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border border-gray-100 dark:border-slate-800 hover:border-blue-200 dark:hover:border-blue-500/50 transition-all hover:translate-y-[-4px]">
                <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">User Management</h3>
                <p class="text-sm text-gray-400 dark:text-slate-500 font-medium leading-relaxed mb-6">Add, edit, or remove team members and assign system roles.</p>
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 text-[10px] font-bold uppercase tracking-widest">
                    Manage Users
                    <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </div>
            </a>

            <!-- Subtask Type Management Card -->
            <a href="{{ route('subtask-types.index') }}" class="group bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border border-gray-100 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-500/50 transition-all hover:translate-y-[-4px]">
                <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-500 transition-colors">
                    <svg class="w-7 h-7 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Subtask Types</h3>
                <p class="text-sm text-gray-400 dark:text-slate-500 font-medium leading-relaxed mb-6">Define custom categories for deliverables like Carousel, Reels, or KV.</p>
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-[10px] font-bold uppercase tracking-widest">
                    Manage Types
                    <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </div>
            </a>

        </div>
    </div>
</x-layout>
