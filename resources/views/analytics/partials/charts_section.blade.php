{{-- Analytic Diagrams, Graphs & Pie Charts Section --}}
<div class="flex flex-col gap-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                <span>Analytics Diagrams & Charts</span>
            </h3>
            <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-0.5">
                Visual status distribution, throughput velocity, deliverable formats, and brand workloads.
            </p>
        </div>
    </div>

    {{-- 4 Charts Grid (2x2 on desktop) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- 1. Status Pie / Donut Chart --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Work Status Distribution</h4>
                        <p class="text-[11px] text-gray-400 mt-0.5">Active and Completed deliverables</p>
                    </div>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300">
                        {{ $charts['status_pie']['total'] }} Total
                    </span>
                </div>

                {{-- Canvas Container --}}
                <div class="relative h-60 w-full flex items-center justify-center">
                    @if($charts['status_pie']['total'] > 0)
                        <canvas id="statusPieChart"></canvas>
                    @else
                        <div class="text-center py-12 text-gray-400 text-xs">
                            No deliverables found for this date range to chart.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Custom Legend Pills --}}
            <div class="grid grid-cols-2 gap-3 pt-4 mt-2 border-t border-gray-50 dark:border-slate-800/80 text-center">
                <div class="p-2.5 rounded-xl bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20">
                    <span class="block text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase">Active</span>
                    <span class="text-sm font-extrabold text-blue-600 dark:text-blue-400">{{ $charts['status_pie']['data'][0] ?? 0 }}</span>
                    <span class="text-[10px] text-gray-400 block">({{ $charts['status_pie']['percentages'][0] ?? 0 }}%)</span>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/20">
                    <span class="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Completed</span>
                    <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">{{ $charts['status_pie']['data'][1] ?? 0 }}</span>
                    <span class="text-[10px] text-gray-400 block">({{ $charts['status_pie']['percentages'][1] ?? 0 }}%)</span>
                </div>
            </div>
        </div>

        {{-- 2. Velocity Timeline Activity Graph --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Activity Timeline & Velocity</h4>
                        <p class="text-[11px] text-gray-400 mt-0.5">Deliverables Opened vs Closed over time</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-bold">
                        <span class="inline-flex items-center gap-1.5 text-blue-500">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span>Opened</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-500">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Closed</span>
                        </span>
                    </div>
                </div>

                {{-- Canvas Container --}}
                <div class="relative h-60 w-full">
                    <canvas id="timelineActivityChart"></canvas>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-50 dark:border-slate-800/80 text-xs text-gray-500 dark:text-slate-400">
                <span>Period Throughput:</span>
                <span class="font-bold text-gray-900 dark:text-white">
                    {{ array_sum($charts['timeline_graph']['closed']) }} closed of {{ array_sum($charts['timeline_graph']['opened']) }} opened
                </span>
            </div>
        </div>

        {{-- 3. Deliverable Formats & Categories (Pie Chart) --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Deliverable Formats & Types</h4>
                        <p class="text-[11px] text-gray-400 mt-0.5">Top creative deliverable categories produced</p>
                    </div>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-gray-100 dark:bg-slate-800 text-gray-500">
                        {{ count($charts['types_pie']['labels']) }} Types
                    </span>
                </div>

                <div class="relative h-60 w-full flex items-center justify-center">
                    @if(count($charts['types_pie']['labels']) > 0)
                        <canvas id="typesPieChart"></canvas>
                    @else
                        <div class="text-center py-12 text-gray-400 text-xs">
                            No deliverable type data found.
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2 pt-4 mt-2 border-t border-gray-50 dark:border-slate-800/80 justify-center">
                @foreach($charts['types_pie']['labels'] as $idx => $label)
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[10px] font-bold bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-800 text-gray-700 dark:text-slate-300">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $charts['types_pie']['colors'][$idx] ?? '#3b82f6' }}"></span>
                        <span>{{ $label }}:</span>
                        <b class="text-gray-900 dark:text-white font-mono">{{ $charts['types_pie']['data'][$idx] ?? 0 }}</b>
                    </span>
                @endforeach
            </div>
        </div>

        {{-- 4. Brand Workload & Completion (Horizontal Bar Chart) --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Brand Workload & Completion</h4>
                        <p class="text-[11px] text-gray-400 mt-0.5">Top client brands: Active work vs Completed</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-bold">
                        <span class="inline-flex items-center gap-1 text-blue-500">
                            <span class="w-2.5 h-2.5 rounded-sm bg-blue-500"></span>
                            <span>Active</span>
                        </span>
                        <span class="inline-flex items-center gap-1 text-emerald-500">
                            <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                            <span>Done</span>
                        </span>
                    </div>
                </div>

                <div class="relative h-60 w-full flex items-center justify-center">
                    @if(count($charts['brand_bar']['labels']) > 0)
                        <canvas id="brandBarChart"></canvas>
                    @else
                        <div class="text-center py-12 text-gray-400 text-xs">
                            No brand data to display for this filter.
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-50 dark:border-slate-800/80 text-xs text-gray-500 dark:text-slate-400">
                <span>Top active client brands</span>
                <span class="font-bold text-gray-900 dark:text-white">
                    {{ count($charts['brand_bar']['labels']) }} Brands Tracked
                </span>
            </div>
        </div>

    </div>
</div>

{{-- Chart.js Initialization Script --}}
<script>
(function() {
    function getChartTheme() {
        const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
        return {
            textColor: isDark ? '#94a3b8' : '#64748b',
            gridColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.04)',
            tooltipBg: isDark ? '#1e293b' : '#0f172a',
            tooltipText: '#ffffff',
        };
    }

    function initCharts() {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded yet, retrying...');
            setTimeout(initCharts, 100);
            return;
        }

        const theme = getChartTheme();

        // 1. Status Pie / Donut Chart
        const statusEl = document.getElementById('statusPieChart');
        if (statusEl) {
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($charts['status_pie']['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($charts['status_pie']['data']) !!},
                        backgroundColor: {!! json_encode($charts['status_pie']['colors']) !!},
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0F172A' : '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: theme.tooltipBg,
                            titleColor: theme.tooltipText,
                            bodyColor: theme.tooltipText,
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.parsed;
                                    const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ` ${context.label}: ${val} deliverables (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Timeline Activity Chart (Line / Area)
        const timelineEl = document.getElementById('timelineActivityChart');
        if (timelineEl) {
            new Chart(timelineEl, {
                type: 'line',
                data: {
                    labels: {!! json_encode($charts['timeline_graph']['labels']) !!},
                    datasets: [
                        {
                            label: 'Opened',
                            data: {!! json_encode($charts['timeline_graph']['opened']) !!},
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Completed',
                            data: {!! json_encode($charts['timeline_graph']['closed']) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: theme.tooltipBg,
                            titleColor: theme.tooltipText,
                            bodyColor: theme.tooltipText,
                            padding: 10,
                            cornerRadius: 10
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: theme.textColor, font: { size: 10, weight: 'bold' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: theme.gridColor },
                            ticks: {
                                color: theme.textColor,
                                font: { size: 10 },
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // 3. Types / Formats Pie Chart
        const typesEl = document.getElementById('typesPieChart');
        if (typesEl) {
            new Chart(typesEl, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($charts['types_pie']['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($charts['types_pie']['data']) !!},
                        backgroundColor: {!! json_encode($charts['types_pie']['colors']) !!},
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0F172A' : '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: theme.tooltipBg,
                            titleColor: theme.tooltipText,
                            bodyColor: theme.tooltipText,
                            padding: 10,
                            cornerRadius: 10
                        }
                    }
                }
            });
        }

        // 4. Brand Workload Bar Chart
        const brandEl = document.getElementById('brandBarChart');
        if (brandEl) {
            new Chart(brandEl, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($charts['brand_bar']['labels']) !!},
                    datasets: [
                        {
                            label: 'Active',
                            data: {!! json_encode($charts['brand_bar']['active']) !!},
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                            maxBarThickness: 24,
                        },
                        {
                            label: 'Completed/Closed',
                            data: {!! json_encode($charts['brand_bar']['completed']) !!},
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                            maxBarThickness: 24,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: theme.tooltipBg,
                            titleColor: theme.tooltipText,
                            bodyColor: theme.tooltipText,
                            padding: 10,
                            cornerRadius: 10
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: theme.gridColor },
                            ticks: { color: theme.textColor, font: { size: 10 }, precision: 0 }
                        },
                        y: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { color: theme.textColor, font: { size: 11, weight: '600' } }
                        }
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
})();
</script>
