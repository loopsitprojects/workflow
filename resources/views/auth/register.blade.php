<x-layout>
    <div class="flex flex-col sm:justify-center items-center pt-6 pb-20">
        <div>
            <a href="/">
                <img src="{{ asset('LoopsBlack.png') }}" alt="Loops" class="h-10 w-auto dark:hidden">
                <img src="{{ asset('LoopsWhite.png') }}" alt="Loops" class="h-10 w-auto hidden dark:block">
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-[#1f2937] shadow-md overflow-hidden sm:rounded-2xl border border-gray-200 dark:border-white/[0.06]">
            
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create your account</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">You've been invited to join as a <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $role }}</span>.</p>
            </div>

            <form method="POST" action="/register?signature={{ request()->query('signature') }}&expires={{ request()->query('expires') }}&role={{ urlencode($role) }}">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div class="mt-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                    <p class="mt-1 text-[11px] text-gray-500">Letters, numbers, underscores, and hyphens only.</p>
                    @error('username')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input id="password" type="password" name="password" required
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button type="submit"
                        class="w-full inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors dark:focus:ring-offset-[#1f2937]">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
