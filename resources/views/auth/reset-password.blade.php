<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Loops</title>
    <link rel="icon" type="image/png" href="{{ asset('desktop.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; transition: background-color 0.3s; }
        .bg-mesh {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(217,100%,92%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(202,100%,94%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(222,100%,92%,1) 0, transparent 50%);
        }
        .dark .bg-mesh {
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(217,100%,15%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(202,100%,10%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(222,100%,15%,1) 0, transparent 50%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(203, 213, 225, 0.8);
            transition: background 0.3s, border-color 0.3s;
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-mesh h-screen flex items-center justify-center p-6 overflow-hidden">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('LoopsBlack.png') }}" alt="Loops Logo" class="w-24 h-24 object-contain mb-3 dark:hidden" />
            <img src="{{ asset('LoopsWhite.png') }}" alt="Loops Logo" class="w-24 h-24 object-contain mb-3 hidden dark:block" />
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Create New Password</h1>
            <p class="text-gray-600 dark:text-slate-300 font-medium mt-2 text-center text-sm">Please set a new password for <span class="font-bold text-gray-900 dark:text-white">{{ $email }}</span></p>
        </div>

        <div class="glass-card rounded-2xl p-8 shadow-xl shadow-slate-200/60 dark:shadow-none">
            @if (session('success'))
                <div class="mb-4 text-xs font-bold text-green-600 dark:text-green-400 bg-green-500/10 border border-green-500/30 p-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- New Password -->
                <div x-data="{ show: false }">
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-2 ml-1">New Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required autofocus
                            class="w-full px-4 py-2.5 pr-12 bg-white dark:bg-slate-800/50 border border-gray-300 dark:border-slate-700 rounded-xl text-[13px] font-medium text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 transition-all outline-none placeholder:text-gray-400 dark:placeholder:text-slate-500"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 transition-colors"
                                aria-label="Toggle password visibility">
                            <template x-if="!show">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-xs font-bold text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div x-data="{ show: false }">
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-2 ml-1">Confirm New Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                            class="w-full px-4 py-2.5 pr-12 bg-white dark:bg-slate-800/50 border border-gray-300 dark:border-slate-700 rounded-xl text-[13px] font-medium text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 transition-all outline-none placeholder:text-gray-400 dark:placeholder:text-slate-500"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 transition-colors"
                                aria-label="Toggle password visibility">
                            <template x-if="!show">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-[#0055D4] text-white rounded-xl text-[13px] font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-[0.98]">
                    Reset Password
                </button>
            </form>
        </div>

        <p class="text-center mt-10 text-[11px] font-bold text-gray-500 dark:text-slate-500 uppercase tracking-widest">
            &copy; {{ date('Y') }} Loops Creative Tools
        </p>
    </div>
</body>
</html>
