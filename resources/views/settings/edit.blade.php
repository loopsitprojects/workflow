<x-layout>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Account Settings</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Update your personal account details and password here.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white dark:bg-[#1f2937] rounded-2xl shadow-sm border border-gray-200 dark:border-white/[0.06] overflow-hidden">
            <form action="{{ route('settings.update') }}" method="POST" class="p-6 sm:p-8 space-y-6">
                @csrf
                
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your account's profile information and email address.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-white/[0.06] my-8">

                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Update Password</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ensure your account is using a long, random password to stay secure. Leave blank to keep your current password.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                        <input type="password" name="current_password" id="current_password"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                        @error('current_password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/[0.1] dark:bg-black/[0.2] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2.5 transition-colors">
                    </div>
                </div>

                <div class="pt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors dark:focus:ring-offset-[#1f2937]">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
