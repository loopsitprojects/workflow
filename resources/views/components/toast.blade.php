<div x-data="{ 
    toasts: [],
    addToast(message, type = 'success') {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => this.removeToast(id), 5000);
    },
    removeToast(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
}" 
@toast.window="addToast($event.detail.message, $event.detail.type)"
class="fixed bottom-8 right-8 z-[200] flex flex-col gap-3 w-80 pointer-events-none">
    
    <!-- Initial Flash Messages -->
    @if(session('success'))
        <div x-init="addToast('{{ session('success') }}', 'success')"></div>
    @endif
    @if(session('error'))
        <div x-init="addToast('{{ session('error') }}', 'error')"></div>
    @endif
    @if(session('info'))
        <div x-init="addToast('{{ session('info') }}', 'info')"></div>
    @endif

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-4 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="pointer-events-auto bg-white dark:bg-[#0F172A] rounded-2xl shadow-[0_15px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_15px_50px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-slate-800 p-4 flex items-center gap-4 group transition-colors duration-300">
            
            <div :class="{
                'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400': toast.type === 'success',
                'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400': toast.type === 'error',
                'bg-gray-50 text-gray-600 dark:bg-slate-800 dark:text-slate-400': toast.type === 'info'
            }" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                <template x-if="toast.type === 'success'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
            </div>

            <div class="flex-1 pr-4">
                <h4 class="text-[13px] font-black text-gray-900 dark:text-white leading-tight" x-text="toast.type === 'success' ? 'Success' : 'Alert'"></h4>
                <p class="text-[12px] text-gray-500 dark:text-slate-400 font-medium mt-0.5" x-text="toast.message"></p>
            </div>

            <button @click="removeToast(toast.id)" class="text-gray-300 hover:text-gray-900 dark:text-slate-600 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
