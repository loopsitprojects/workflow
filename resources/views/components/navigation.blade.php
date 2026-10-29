@php
    $initialUnreadCount = auth()->user() ? (auth()->user()->isAdmin()
        ? \Illuminate\Notifications\DatabaseNotification::whereNull('read_at')->count()
        : auth()->user()->unreadNotifications()->count()
    ) : 0;
@endphp

<nav x-data="{ unreadCount: {{ $initialUnreadCount }} }"
     @update-unread-count.window="unreadCount = $event.detail"
     class="site-navbar sticky top-0 z-50 h-14 flex items-center justify-between px-6 border-b
            transition-colors duration-300
            gap-4 relative overflow-hidden"
     style="background: var(--bg-navbar, #0A2540); border-bottom: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 1px 4px rgba(0,0,0,0.12);">

    {{-- Halloween Panoramic Skyline & Ambient Horizon Glow --}}
    <div x-show="$store.theme.halloween" class="halloween-skyline absolute inset-0 pointer-events-none overflow-hidden z-0 transition-opacity duration-300" style="background: radial-gradient(circle at 50% 0%, rgba(249, 115, 22, 0.08) 0%, transparent 70%);" x-cloak>
        <svg class="absolute bottom-0 right-0 h-10 w-auto opacity-15 dark:opacity-25 pointer-events-none" viewBox="0 0 500 60" fill="none" preserveAspectRatio="none">
            <!-- Distant Haunted Spire & Bats -->
            <polygon points="120,60 135,15 150,60" fill="currentColor" class="text-orange-950 dark:text-orange-400"/>
            <polygon points="160,60 180,5 200,60" fill="currentColor" class="text-orange-950 dark:text-orange-400"/>
            <polygon points="210,60 220,25 230,60" fill="currentColor" class="text-orange-950 dark:text-orange-400"/>
            <path d="M 60 50 Q 80 35 100 50 Q 120 65 140 50" stroke="currentColor" stroke-width="1.5" class="text-orange-900/60 dark:text-orange-400/40" fill="none"/>
            <!-- Flying Bats in Navbar Horizon -->
            <path d="M 280 20 C 275 16 270 18 266 22 C 268 20 270 19 272 19 C 269 17 265 18 262 20 C 264 22 268 25 272 24 Z" fill="currentColor" class="text-orange-900 dark:text-orange-300"/>
            <path d="M 330 14 C 326 11 322 12 318 16 C 320 14 322 13 324 13 C 321 11 317 12 314 14 C 316 16 320 19 324 18 Z" fill="currentColor" class="text-orange-900 dark:text-orange-300"/>
        </svg>
    </div>

    {{-- Christmas Panoramic Skyline & Ambient Horizon Glow --}}
    <div x-show="$store.theme.christmas" class="christmas-skyline absolute inset-0 pointer-events-none overflow-hidden z-0 transition-opacity duration-300" style="background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.08) 0%, transparent 70%);" x-cloak>
        <svg class="absolute bottom-0 right-0 h-10 w-auto opacity-15 dark:opacity-25 pointer-events-none" viewBox="0 0 500 60" fill="none" preserveAspectRatio="none">
            <!-- Distant Snowy Alpine Ridge & Pine Silhouettes -->
            <polygon points="100,60 140,20 180,60" fill="currentColor" class="text-emerald-950 dark:text-emerald-400"/>
            <polygon points="170,60 210,12 250,60" fill="currentColor" class="text-emerald-950 dark:text-emerald-400"/>
            <polygon points="240,60 270,25 300,60" fill="currentColor" class="text-emerald-950 dark:text-emerald-400"/>
            <!-- Pine silhouettes -->
            <polygon points="40,60 48,35 56,60" fill="currentColor" class="text-emerald-900 dark:text-emerald-300"/>
            <polygon points="65,60 75,28 85,60" fill="currentColor" class="text-emerald-900 dark:text-emerald-300"/>
            <polygon points="320,60 328,32 336,60" fill="currentColor" class="text-emerald-900 dark:text-emerald-300"/>
            <polygon points="345,60 355,22 365,60" fill="currentColor" class="text-emerald-900 dark:text-emerald-300"/>
            <!-- Little North Star in nav -->
            <polygon points="410,18 412,23 417,25 412,27 410,32 408,27 403,25 408,23" fill="#FDE68A"/>
        </svg>
    </div>

    {{-- Left: Logo + Nav links --}}
    <div class="flex items-center gap-8 relative z-10">
        <a href="/" class="flex-shrink-0 flex items-center gap-2.5">
            <img src="{{ asset('LoopsWhite.png') }}"
                 alt="Loops"
                 class="h-7 w-auto">
            <template x-if="$store.theme.halloween">
                <span class="hidden sm:inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-black/40 dark:bg-black/60 text-[#F4F0F8] border border-white/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.2)] backdrop-blur-md select-none">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F97316] shadow-[0_0_8px_#F97316]"></span>
                    <span>🎃 SPOOKY</span>
                </span>
            </template>
            <template x-if="$store.theme.christmas">
                <span class="hidden sm:inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-black/40 dark:bg-black/60 text-[#F4F0F8] border border-white/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.2)] backdrop-blur-md select-none">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] shadow-[0_0_8px_#10B981]"></span>
                    <span>🎄 FESTIVE</span>
                </span>
            </template>
        </a>

        <div class="hidden md:flex items-center gap-1.5 text-[13px] font-medium">
            <a href="/"
               class="nav-link-item px-3 py-1.5 rounded-lg transition-all duration-150
                      {{ request()->is('/') ? 'nav-item-active bg-white/15 text-white font-semibold' : 'nav-item-inactive text-white/85 hover:text-white hover:bg-white/10' }}">
                Dashboard
            </a>
            <a href="/brands"
               class="nav-link-item px-3 py-1.5 rounded-lg transition-all duration-150
                      {{ request()->is('brands*') ? 'nav-item-active bg-white/15 text-white font-semibold' : 'nav-item-inactive text-white/85 hover:text-white hover:bg-white/10' }}">
                Brands
            </a>
            @if(\App\Services\FeatureManager::isAnalyticsEnabled())
                <a href="{{ route('analytics.index') }}"
                   class="nav-link-item px-3 py-1.5 rounded-lg transition-all duration-150
                          {{ request()->is('analytics*') ? 'nav-item-active bg-white/15 text-white font-semibold' : 'nav-item-inactive text-white/85 hover:text-white hover:bg-white/10' }}">
                    Analytics
                </a>
            @endif
            @if(auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('admin.settings') }}"
                   class="nav-link-item px-3 py-1.5 rounded-lg transition-all duration-150
                          {{ request()->is('admin*') ? 'nav-item-active bg-white/15 text-white font-semibold' : 'nav-item-inactive text-white/85 hover:text-white hover:bg-white/10' }}">
                    Admin
                </a>
            @endif
        </div>
    </div>

    {{-- Right: Actions + User --}}
    <div class="flex items-center gap-2 relative z-10">

        {{-- Theme toggle --}}
        <button @click="$store.theme.toggle()"
                :title="$store.theme.halloween 
                    ? ($store.theme.mode === 'dark' ? 'Switch to Halloween Light Mode' : 'Switch to Halloween Dark Mode')
                    : ($store.theme.christmas
                        ? ($store.theme.mode === 'dark' ? 'Switch to Christmas Light Mode' : 'Switch to Christmas Dark Mode')
                        : ($store.theme.mode === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode'))"
                aria-label="Toggle theme"
                class="p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10
                       transition-all duration-150 relative">
            <template x-if="$store.theme.mode === 'light'">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-white/80 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <template x-if="$store.theme.halloween">
                        <span class="text-xs select-none inline-block hover:scale-125 transition-transform" role="img" aria-label="Halloween Active" title="Halloween Theme Active">🎃</span>
                    </template>
                    <template x-if="$store.theme.christmas">
                        <span class="text-xs select-none inline-block hover:scale-125 transition-transform" role="img" aria-label="Christmas Active" title="Christmas Theme Active">🎄</span>
                    </template>
                </div>
            </template>
            <template x-if="$store.theme.mode === 'dark'">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-amber-300 hover:text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <template x-if="$store.theme.halloween">
                        <span class="text-xs select-none inline-block hover:scale-125 transition-transform" role="img" aria-label="Halloween Active" title="Halloween Theme Active">🎃</span>
                    </template>
                    <template x-if="$store.theme.christmas">
                        <span class="text-xs select-none inline-block hover:scale-125 transition-transform" role="img" aria-label="Christmas Active" title="Christmas Theme Active">🎄</span>
                    </template>
                </div>
            </template>
        </button>

        {{-- Notifications --}}
        <button @click="$dispatch('open-notifications')"
                aria-label="Open notifications"
                class="relative p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10
                       transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span x-cloak x-show="unreadCount > 0"
                  class="absolute top-1 right-1 min-w-[16px] h-4 px-1
                         bg-blue-500 text-white text-[9px] font-black
                         rounded-full flex items-center justify-center
                         border-2 border-[#0A2540]
                         pointer-events-none"
                  x-text="unreadCount > 9 ? '9+' : unreadCount">
            </span>
        </button>

        {{-- Divider --}}
        <div class="w-px h-5 bg-white/20 mx-1"></div>

        {{-- User --}}
        <div class="flex items-center gap-2.5 pl-1">
            <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-white/20 flex-shrink-0">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=3B82F6&color=fff&bold=true&size=64"
                     alt="{{ auth()->user()->name ?? '' }}"
                     class="w-full h-full object-cover">
            </div>
            <div class="hidden md:block">
                <p class="text-[13px] font-semibold text-white leading-none">
                    {{ auth()->user()->name ?? 'Guest' }}
                </p>
                <p class="text-[10px] text-slate-300 uppercase tracking-wide mt-0.5">
                    {{ auth()->user()->role ?? '' }}
                </p>
            </div>
            <a href="{{ route('settings.edit') }}"
               title="Account Settings"
               class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10
                      transition-all duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="ml-1">
                @csrf
                <button type="submit"
                        title="Sign out"
                        class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10
                               transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>

    </div>
</nav>
