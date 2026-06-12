<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Loops</title>
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
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            transition: background 0.3s, border-color 0.3s;
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
    <script>
        // Synchronized with Layout.blade.php
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-mesh min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="w-full max-w-md">
    <div class="flex flex-col items-center mb-10">
        <img src="/LoopsWhite.png" alt="Loops White Logo" class="w-24 h-24 object-contain mb-3" />
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Welcome to Loops</h1>
        <p class="text-gray-500 dark:text-slate-400 font-medium mt-2">Sign in to manage your workspace</p>
    </div>
</div>

        <div class="glass-card rounded-2xl p-8 shadow-2xl shadow-gray-200/50 dark:shadow-none">
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 dark:text-slate-500 mb-2 ml-1">Email or Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700 rounded-xl text-[13px] font-medium dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder:text-gray-300 dark:placeholder:text-slate-600"
                        placeholder="admin@loops.com or admin">
                    @error('login')
                        <p class="mt-2 text-xs font-bold text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }">
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label class="text-[11px] font-semibold text-gray-400 dark:text-slate-500">Password</label>
                    </div>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                            class="w-full px-4 py-2.5 pr-12 bg-white dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700 rounded-xl text-[13px] font-medium dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder:text-gray-300 dark:placeholder:text-slate-600"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors"
                                aria-label="Toggle password visibility">
                            <template x-if="!show">
                                <svg class="w-4.5 h-4.5 w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <div class="flex items-center ml-1">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-blue-600 border-gray-200 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-800">
                    <label for="remember" class="ml-3 text-sm font-medium text-gray-500 dark:text-slate-400">Stay signed in</label>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-[#0055D4] text-white rounded-xl text-[13px] font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-[0.98]">
                    Sign In
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-gray-50 dark:border-slate-800 text-center">
                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">
                    Don't have an account? 
                    <a href="#" class="text-blue-500 font-bold hover:text-blue-700 transition-colors">Contact Admin</a>
                </p>
            </div>
        </div>

        <p class="text-center mt-10 text-[11px] font-bold text-gray-400 dark:text-slate-600 uppercase tracking-widest">
            &copy; {{ date('Y') }} Loops Creative Tools
        </p>
    </div>
</body>
</html>
