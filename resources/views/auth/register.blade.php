<x-layout>
    <div class="min-h-[calc(100vh-3.5rem)] w-full flex flex-col sm:justify-center items-center pt-6 pb-20 bg-gray-50 dark:bg-[#0b1120]">
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

            <form method="POST" action="/invite/{{ $token }}">
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
                <div class="mt-4" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative mt-1" style="position: relative;">
                        <input id="password" :type="show ? 'text' : 'password'" name="password" required
                            class="block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 pr-10 transition-colors" style="padding-right: 40px;">
                        <button type="button" @click="show = !show"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; z-index: 10; display: flex; align-items: center;"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            aria-label="Toggle password visibility">
                            <template x-if="!show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mt-4" x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <div class="relative mt-1" style="position: relative;">
                        <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required
                            class="block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 pr-10 transition-colors" style="padding-right: 40px;">
                        <button type="button" @click="show = !show"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; z-index: 10; display: flex; align-items: center;"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            aria-label="Toggle password visibility">
                            <template x-if="!show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </template>
                        </button>
                    </div>
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
