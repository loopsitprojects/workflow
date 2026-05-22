@php
    $initialUnreadCount = auth()->user() ? (auth()->user()->isAdmin()
        ? \Illuminate\Notifications\DatabaseNotification::whereNull('read_at')->count()
        : auth()->user()->unreadNotifications()->count()
    ) : 0;
@endphp

<nav x-data="{ unreadCount: {{ $initialUnreadCount }} }"
     @update-unread-count.window="unreadCount = $event.detail"
     class="sticky top-0 z-50 h-14 flex items-center justify-between px-6 border-b
            bg-white/80 dark:bg-[#111827]/90
            border-gray-200/60 dark:border-white/[0.06]
            backdrop-blur-xl transition-colors duration-300
            gap-4"
     style="box-shadow: 0 1px 0 rgba(0,0,0,0.04);">

    {{-- Left: Logo + Nav links --}}
    <div class="flex items-center gap-8">
        <a href="/" class="flex-shrink-0">
            <img :src="$store.theme.current === 'light' ? '{{ asset('LoopsBlack.png') }}' : '{{ asset('LoopsWhite.png') }}'"
                 alt="Loops"
                 class="h-7 w-auto">
        </a>

        <div class="hidden md:flex items-center gap-1 text-[13px] font-medium">
            <a href="/"
               class="px-3 py-1.5 rounded-lg transition-all duration-150
                      {{ request()->is('/') ? 'bg-gray-100 dark:bg-white/[0.08] text-gray-900 dark:text-white font-semibold' : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/[0.05]' }}">
                Dashboard
            </a>
            <a href="/brands"
               class="px-3 py-1.5 rounded-lg transition-all duration-150
                      {{ request()->is('brands*') ? 'bg-gray-100 dark:bg-white/[0.08] text-gray-900 dark:text-white font-semibold' : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/[0.05]' }}">
                Brands
            </a>
            @if(auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('admin.settings') }}"
                   class="px-3 py-1.5 rounded-lg transition-all duration-150
                          {{ request()->is('admin*') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold' : 'text-blue-500 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10' }}">
                    Admin
                </a>
            @endif
        </div>
    </div>

    {{-- Right: Actions + User --}}
    <div class="flex items-center gap-2">

        {{-- Theme toggle --}}
        <button @click="$store.theme.toggle()"
                aria-label="Toggle theme"
                class="p-2 rounded-lg text-gray-400 dark:text-slate-500
                       hover:text-gray-700 dark:hover:text-white
                       hover:bg-gray-100 dark:hover:bg-white/[0.06]
                       transition-all duration-150">
            <template x-if="$store.theme.current === 'light'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </template>
            <template x-if="$store.theme.current === 'dark'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </template>
        </button>

        {{-- Notifications --}}
        <button @click="$dispatch('open-notifications')"
                aria-label="Open notifications"
                class="relative p-2 rounded-lg text-gray-400 dark:text-slate-500
                       hover:text-gray-700 dark:hover:text-white
                       hover:bg-gray-100 dark:hover:bg-white/[0.06]
                       transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span x-cloak x-show="unreadCount > 0"
                  class="absolute top-1 right-1 min-w-[16px] h-4 px-1
                         bg-blue-500 text-white text-[9px] font-black
                         rounded-full flex items-center justify-center
                         border-2 border-white dark:border-[#111827]
                         pointer-events-none"
                  x-text="unreadCount > 9 ? '9+' : unreadCount">
            </span>
        </button>

        {{-- Divider --}}
        <div class="w-px h-5 bg-gray-200 dark:bg-white/[0.08] mx-1"></div>

        {{-- User --}}
        <div class="flex items-center gap-2.5 pl-1">
            <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-gray-100 dark:ring-white/[0.08] flex-shrink-0">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=3B82F6&color=fff&bold=true&size=64"
                     alt="{{ auth()->user()->name ?? '' }}"
                     class="w-full h-full object-cover">
            </div>
            <div class="hidden md:block">
                <p class="text-[13px] font-semibold text-gray-900 dark:text-white leading-none">
                    {{ auth()->user()->name ?? 'Guest' }}
                </p>
                <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wide mt-0.5">
                    {{ auth()->user()->role ?? '' }}
                </p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ml-1">
                @csrf
                <button type="submit"
                        title="Sign out"
                        class="p-1.5 rounded-lg text-gray-400 dark:text-slate-500
                               hover:text-gray-700 dark:hover:text-white
                               hover:bg-gray-100 dark:hover:bg-white/[0.06]
                               transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>

    </div>
</nav>
