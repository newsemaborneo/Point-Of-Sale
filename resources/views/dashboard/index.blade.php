<x-layouts.app title="Dashboard">
    <div class="space-y-6 sm:space-y-8 animate-fade-in-down">
        {{-- Stat Cards --}}
        <section class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Revenue Card --}}
            <div class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_8px_30px_rgb(99,102,241,0.1)] hover:-translate-y-1 group">
                <div class="absolute -right-6 -top-6 rounded-full bg-indigo-50 p-8 transition-transform group-hover:scale-125">
                    <svg class="h-10 w-10 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="relative z-10">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-indigo-500">Pendapatan Hari Ini</h2>
                    <p class="mt-4 text-3xl sm:text-4xl font-black bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent tracking-tight break-words">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-3 flex items-center text-sm font-medium text-slate-500">
                        <span class="mr-2 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                        </span>
                        Dari penjualan hari ini
                    </p>
                </div>
            </div>

            {{-- Transactions Card --}}
            <div class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_8px_30px_rgb(16,185,129,0.1)] hover:-translate-y-1 group">
                <div class="absolute -right-6 -top-6 rounded-full bg-emerald-50 p-8 transition-transform group-hover:scale-125">
                    <svg class="h-10 w-10 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                </div>
                <div class="relative z-10">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-emerald-500">Transaksi Hari Ini</h2>
                    <p class="mt-4 text-3xl sm:text-4xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent tracking-tight">{{ $totalTransactions ?? 0 }}</p>
                    <p class="mt-3 text-sm font-medium text-slate-500">Total transaksi yang berhasil diselesaikan.</p>
                </div>
            </div>

            {{-- Low Stock Card --}}
            <div class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_8px_30px_rgb(244,63,94,0.1)] hover:-translate-y-1 group sm:col-span-2 lg:col-span-1">
                <div class="absolute -right-6 -top-6 rounded-full bg-rose-50 p-8 transition-transform group-hover:scale-125">
                    <svg class="h-10 w-10 text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="relative z-10">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-rose-500">Stok Menipis</h2>
                    <div class="mt-4 flex items-baseline gap-2">
                        <p class="text-3xl sm:text-4xl font-black bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent tracking-tight">{{ isset($lowStockProducts) ? $lowStockProducts->count() : 0 }}</p>
                        <span class="text-lg font-bold text-slate-500">Produk</span>
                    </div>
                    <p class="mt-3 text-sm font-medium text-slate-500">Butuh restock (di bawah batas minimum).</p>
                </div>
            </div>
        </section>

        {{-- Chart + Sidebar: kedua kolom dibuat flex agar tingginya saling menyesuaikan --}}
        <section class="grid items-stretch gap-6 sm:gap-8 xl:grid-cols-[2fr_1.2fr]">
            {{-- Sales Chart --}}
            <div class="flex flex-col rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6 sm:mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Ringkasan Penjualan</h2>
                        <p class="text-sm font-medium text-slate-500 mt-1">Grafik penjualan 7 hari terakhir.</p>
                    </div>
                </div>
                <div class="relative min-h-[280px] flex-1">
                    @php
                        $hasChartData = collect($salesChartDatasets ?? [])
                            ->sum(fn ($ds) => array_sum($ds['data'] ?? [])) > 0;
                    @endphp
                    @if($hasChartData)
                        <canvas id="salesChart" class="absolute inset-0 h-full w-full"></canvas>
                    @else
                        <div class="flex h-full min-h-[280px] items-center justify-center rounded-2xl border border-dashed border-slate-300">
                            <p class="font-medium text-slate-500 text-center px-4">Belum ada data penjualan dalam 7 hari terakhir.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-6 sm:gap-8">
                {{-- Best Sellers --}}
                <div class="flex flex-1 min-h-[320px] flex-col rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-slate-800">Produk Terlaris</h2>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase text-emerald-700">Top 5</span>
                    </div>
                    <div class="flex-1 space-y-4 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-transparent hover:scrollbar-thumb-slate-400">
                        @forelse ($bestSellers ?? [] as $index => $item)
                            <div class="group flex items-center justify-between rounded-2xl border border-slate-100 p-4 transition-all hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-600' : ($index === 1 ? 'bg-slate-200 text-slate-600' : ($index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-slate-100 text-slate-500')) }}">
                                        #{{ $index + 1 }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate">{{ $item->product->name ?? 'Produk tidak diketahui' }}</div>
                                        <div class="text-xs font-medium text-slate-500 truncate">{{ $item->product->category->name ?? 'Kategori' }}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 pl-3">
                                    <div class="font-bold text-indigo-600">{{ $item->total_qty }}</div>
                                    <div class="text-xs font-medium text-slate-500">Terjual</div>
                                </div>
                            </div>
                        @empty
                            <div class="flex h-full items-center justify-center rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                                <p class="font-medium text-slate-500">Belum ada data penjualan hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">Laporan Cepat</h2>
                    <a href="{{ route('reports.index') }}" class="group flex items-center justify-between rounded-2xl bg-indigo-50 p-4 transition-colors hover:bg-indigo-100">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="rounded-xl bg-white p-2 text-indigo-600 shadow-sm shrink-0">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-800 truncate">Laporan Penjualan</h3>
                                <p class="text-xs font-medium text-slate-500">Unduh PDF/Excel</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-indigo-400 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
            </div>
        </section>

        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
            <section class="mt-8 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-500">AI Center</p>
                        <h2 class="mt-2 text-2xl font-black bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">{{ $aiCenterTitle }}</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Period: 30 hari</span>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ auth()->user()->hasRole('admin') ? 'All branches' : (auth()->user()->branch->name ?? 'Cabang') }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-3">
                    <div class="rounded-3xl border border-indigo-100/50 bg-gradient-to-br from-indigo-50/80 via-white/80 to-white/80 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(99,102,241,0.05)]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-xl bg-indigo-100 p-2 text-indigo-600">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l7.5-7.5 4.5 4.5 7.5-7.5v12.75H3.75z" /></svg>
                            </span>
                            <span class="rounded-full bg-indigo-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">AI Analysis</span>
                        </div>
                        <p id="ai-count-analysis" class="mt-6 text-3xl font-black text-slate-800"><span class="animate-pulse">...</span></p>
                        <p class="mt-2 text-sm font-medium text-slate-600">Insight utama yang membutuhkan perhatian</p>
                    </div>

                    <div class="rounded-3xl border border-amber-100/50 bg-gradient-to-br from-amber-50/80 via-white/80 to-white/80 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(245,158,11,0.05)]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-xl bg-amber-100 p-2 text-amber-600">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                            </span>
                            <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">Risk</span>
                        </div>
                        <p class="mt-6 text-3xl font-black text-slate-800">
                            <span id="ai-count-risk" class="animate-pulse">...</span>
                        </p>
                        <p class="mt-2 text-sm font-medium text-slate-600">Area dengan potensi risk tinggi</p>
                    </div>

                    <div class="rounded-3xl border border-emerald-100/50 bg-gradient-to-br from-emerald-50/80 via-white/80 to-white/80 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(16,185,129,0.05)]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-xl bg-emerald-100 p-2 text-emerald-600">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <span class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">Opportunity</span>
                        </div>
                        <p class="mt-6 text-3xl font-black text-slate-800">
                            <span id="ai-count-opportunity" class="animate-pulse">...</span>
                        </p>
                        <p class="mt-2 text-sm font-medium text-slate-600">Peluang pertumbuhan yang layak ditindaklanjuti</p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <div class="rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <div class="mb-6 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">AI Analysis</p>
                                <h3 class="mt-2 text-xl font-bold text-slate-800">Insight utama</h3>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600">Live</span>
                        </div>

                        <div id="ai-insights-container" class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                            <!-- Loading Skeletons -->
                            <div class="animate-pulse space-y-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="h-4 bg-slate-200 rounded w-2/3"></div>
                                    <div class="h-3 bg-slate-200 rounded w-full mt-3"></div>
                                    <div class="h-3 bg-slate-200 rounded w-5/6 mt-2"></div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                                    <div class="h-3 bg-slate-200 rounded w-full mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/60 bg-white/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <div class="mb-6 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">AI Recommendation</p>
                                <h3 class="mt-2 text-xl font-bold text-slate-800">Rekomendasi prioritas</h3>
                            </div>
                            <span id="ai-recs-badge" class="rounded-full bg-indigo-100 px-2.5 py-1 text-[10px] font-bold uppercase text-indigo-700">Loading...</span>
                        </div>

                        <div id="ai-recommendations-container" class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                            <!-- Loading Skeletons -->
                            <div class="animate-pulse space-y-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="h-4 bg-slate-200 rounded w-2/3"></div>
                                    <div class="h-3 bg-slate-200 rounded w-full mt-3"></div>
                                    <div class="h-3 bg-slate-200 rounded w-5/6 mt-2"></div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                                    <div class="h-3 bg-slate-200 rounded w-full mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <h3 class="text-lg font-bold text-slate-700">Fitur AI analysis belum tersedia untuk role ini</h3>
                <p class="mt-2 text-sm text-slate-500">Panel AI Intelligence hanya tampil untuk akun dengan akses admin atau supervisor.</p>
            </section>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('salesChart');
            if (!canvas) return;

            const salesCtx = canvas.getContext('2d');

            // Data per cabang dikirim dari controller: [{ label, data: [...], color }, ...]
            const branchDatasetsRaw = @json($salesChartDatasets ?? []);
            const isMultiBranch = branchDatasetsRaw.length > 1;

            const chartDatasets = branchDatasetsRaw.map(function (branch) {
                let backgroundColor = 'transparent';

                // Hanya pakai area gradient kalau cuma 1 cabang (biar tidak menumpuk saat multi-line)
                if (!isMultiBranch) {
                    const gradient = salesCtx.createLinearGradient(0, 0, 0, 300);
                    const rgb = hexToRgb(branch.color);
                    gradient.addColorStop(0, `rgba(${rgb}, 0.35)`);
                    gradient.addColorStop(1, `rgba(${rgb}, 0)`);
                    backgroundColor = gradient;
                }

                return {
                    label: branch.label,
                    data: branch.data,
                    borderColor: branch.color,
                    backgroundColor: backgroundColor,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: branch.color,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: !isMultiBranch,
                    tension: 0.4,
                };
            });

            function hexToRgb(hex) {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result
                    ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}`
                    : '99, 102, 241';
            }

            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json($salesChartLabels ?? []),
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: isMultiBranch,
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { weight: '500', size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 12,
                            titleFont: { weight: 'bold' },
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                },
                                font: { weight: '500' }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: '500' } }
                        }
                    }
                }
            });
        });

        // Load AI Insights & Recommendations Asynchronously (via AJAX)
        (function() {
            const insightsContainer = document.getElementById('ai-insights-container');
            const recsContainer = document.getElementById('ai-recommendations-container');
            
            if (!insightsContainer || !recsContainer) return;

            fetch("{{ route('ai.dashboard-data') }}")
                .then(response => response.json())
                .then(data => {
                    const insights = data.insights || [];
                    const recs = data.recommendations || [];

                    // Update Counts
                    document.getElementById('ai-count-analysis').textContent = insights.length;
                    
                    const risks = insights.filter(item => ['critical', 'warning'].includes(item.severity)).length;
                    document.getElementById('ai-count-risk').textContent = risks;

                    const opps = insights.filter(item => item.severity === 'positive').length;
                    document.getElementById('ai-count-opportunity').textContent = opps;

                    // Update Recommendation Items Badge Count
                    const recsBadge = document.getElementById('ai-recs-badge');
                    if (recsBadge) {
                        recsBadge.textContent = `${recs.length} items`;
                    }

                    // Render Insights
                    if (insights.length === 0) {
                        insightsContainer.innerHTML = '<div class="py-8 text-center text-slate-500 font-medium">Belum ada insight tersedia.</div>';
                    } else {
                        insightsContainer.innerHTML = insights.map(item => {
                            let badgeClass = 'bg-emerald-100 text-emerald-700';
                            if (item.severity === 'critical') badgeClass = 'bg-rose-100 text-rose-700';
                            else if (item.severity === 'warning') badgeClass = 'bg-amber-100 text-amber-700';

                            return `
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="text-base font-bold text-slate-800">${item.title}</h4>
                                            <p class="mt-2 text-sm text-slate-600">${item.summary}</p>
                                        </div>
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase ${badgeClass}">
                                            ${item.badge}
                                        </span>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between text-xs font-medium text-slate-500">
                                        <span>${item.meta}</span>
                                        <span class="font-bold text-slate-700">${item.trend}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }

                    // Render Recommendations
                    if (recs.length === 0) {
                        recsContainer.innerHTML = '<div class="py-8 text-center text-slate-500 font-medium">Belum ada rekomendasi tersedia.</div>';
                    } else {
                        recsContainer.innerHTML = recs.map(item => {
                            let badgeClass = 'bg-emerald-100 text-emerald-700';
                            if (item.priority === 'High') badgeClass = 'bg-rose-100 text-rose-700';
                            else if (item.priority === 'Medium') badgeClass = 'bg-amber-100 text-amber-700';

                            return `
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="text-base font-bold text-slate-800">${item.title}</h4>
                                            <p class="mt-2 text-sm text-slate-600">${item.summary}</p>
                                        </div>
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase ${badgeClass}">
                                            ${item.priority}
                                        </span>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between gap-3 text-xs font-medium text-slate-500">
                                        <span>Confidence: ${item.confidence}%</span>
                                        <span class="font-bold text-emerald-700">${item.impact}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                })
                .catch(err => {
                    console.error(err);
                    insightsContainer.innerHTML = '<div class="py-8 text-center text-rose-500 font-medium">Gagal memuat insight.</div>';
                    recsContainer.innerHTML = '<div class="py-8 text-center text-rose-500 font-medium">Gagal memuat rekomendasi.</div>';
                });
        })();
    </script>
    @endpush
</x-layouts.app>
