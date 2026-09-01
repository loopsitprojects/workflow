<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Loops</title>
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
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Forgot Password</h1>
            <p class="text-gray-600 dark:text-slate-300 font-medium mt-2 text-center text-sm">Enter your account email to receive a 6-digit verification code</p>
        </div>

        <div class="glass-card rounded-2xl p-8 shadow-xl shadow-slate-200/60 dark:shadow-none">
            @if (session('status'))
                <div class="mb-4 text-xs font-bold text-green-600 dark:text-green-400 bg-green-500/10 border border-green-500/30 p-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-2 ml-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-800/50 border border-gray-300 dark:border-slate-700 rounded-xl text-[13px] font-medium text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 transition-all outline-none placeholder:text-gray-400 dark:placeholder:text-slate-500"
                        placeholder="yourname@domain.com">
                    @error('email')
                        <p class="mt-2 text-xs font-bold text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-[#0055D4] text-white rounded-xl text-[13px] font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-[0.98]">
                    Send Verification Code
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        &larr; Back to Sign In
                    </a>
                </div>
            </form>
        </div>

        <p class="text-center mt-10 text-[11px] font-bold text-gray-500 dark:text-slate-500 uppercase tracking-widest">
            &copy; {{ date('Y') }} Loops Creative Tools
        </p>
    </div>
</body>
</html>
