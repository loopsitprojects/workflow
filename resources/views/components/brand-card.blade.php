@props(['brand'])

<div class="bg-white dark:bg-[#111827] rounded-2xl card-shadow border border-gray-100 dark:border-white/[0.06] flex flex-col relative group transition-all hover:shadow-md hover:-translate-y-0.5 overflow-hidden">

    {{-- Accent bar --}}
    <div class="h-1 w-full" style="background: {{ ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6'][($brand->id % 4)] }}"></div>

    <div class="p-5 flex flex-col gap-4">

        {{-- Header: logo + name + menu --}}
        <div class="flex justify-between items-start gap-3">
            <a href="{{ route('brands.show', $brand) }}" class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-gray-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0 border border-gray-100 dark:border-slate-700">
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                         class="w-8 h-8 object-contain"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($brand->name) }}&background=E2E8F0&color=475569&bold=true';">
                </div>
                <div class="min-w-0">
                    <h3 class="text-[15px] font-extrabold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate leading-tight">
                        {{ $brand->name }}
                    </h3>
                    @if($brand->location)
                        <p class="text-[11px] text-gray-400 dark:text-slate-500 truncate mt-0.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            {{ $brand->location }}
                        </p>
                    @elseif($brand->description)
                        <p class="text-[11px] text-gray-400 dark:text-slate-500 truncate mt-0.5">{{ $brand->description }}</p>
                    @endif
                </div>
            </a>

            {{-- 3-dot menu --}}
            <div class="relative flex-shrink-0" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="text-gray-300 dark:text-slate-600 hover:text-gray-500 dark:hover:text-slate-400 focus:outline-none p-1 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-1 w-36 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-100 dark:border-slate-700 overflow-hidden z-10"
                     style="display: none;">
                    <button type="button"
                            @click="$dispatch('open-edit', { slug: '{{ $brand->slug }}' })"
                            class="block w-full text-left px-4 py-2.5 text-[11px] font-bold text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-blue-600 dark:hover:text-blue-400 uppercase tracking-wider transition-colors">
                        Edit Brand
                    </button>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-gray-50 dark:border-white/[0.06]"></div>

        {{-- Stats row --}}
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                <span class="text-[12px] font-bold text-gray-700 dark:text-slate-300">{{ $brand->projects_count ?? $brand->active_projects }}</span>
                <span class="text-[11px] text-gray-400 dark:text-slate-500">projects</span>
            </div>
            <div class="w-px h-3 bg-gray-100 dark:bg-slate-700"></div>
            <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[12px] font-bold text-gray-700 dark:text-slate-300">{{ $brand->members->count() }}</span>
                <span class="text-[11px] text-gray-400 dark:text-slate-500">members</span>
            </div>
            @if($brand->health_score)
                <div class="ml-auto">
                    @php
                        $healthColors = [
                            'Stable'    => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                            'At Risk'   => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                            'Critical'  => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
                        ];
                        $hc = $healthColors[$brand->health_score] ?? 'bg-gray-50 text-gray-500 dark:bg-slate-800 dark:text-slate-400';
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $hc }}">
                        {{ $brand->health_score }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Member avatars + View Brand --}}
        <div class="flex items-center justify-between">
            {{-- Real member avatars --}}
            <div class="flex -space-x-2">
                @foreach($brand->members->take(4) as $member)
                    <div class="w-7 h-7 rounded-full border-2 border-white dark:border-[#0F172A] overflow-hidden flex-shrink-0" title="{{ $member->name }} ({{ $member->role }})">
                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                    </div>
                @endforeach
                @if($brand->members->count() > 4)
                    <div class="w-7 h-7 rounded-full border-2 border-white dark:border-[#0F172A] bg-gray-100 dark:bg-slate-700 flex items-center justify-center text-[9px] font-black text-gray-500 dark:text-slate-400">
                        +{{ $brand->members->count() - 4 }}
                    </div>
                @endif
                @if($brand->members->isEmpty())
                    <span class="text-[11px] text-gray-300 dark:text-slate-600 italic">No members</span>
                @endif
            </div>

            <a href="{{ route('brands.show', $brand) }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1 hover:gap-2 transition-all">
                View Brand
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

    </div>
</div>
