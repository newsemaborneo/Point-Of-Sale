<!-- <x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500">Ringkasan aktivitas toko Anda.</p>

        {{-- Bagian ini akan berisi ringkasan angka-angka utama --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Total Pendapatan Hari Ini</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Total Transaksi Hari Ini</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Status Toko</p>
                <p class="text-2xl font-bold mt-1 {{ $storeIsOpen ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $storeIsOpen ? 'Buka' : 'Tutup' }}
                </p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Cabang Aktif</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $userBranchName ?? 'Semua Cabang' }}</p>
            </div>
        </div>

        {{-- Grafik Penjualan --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Grafik Penjualan 7 Hari Terakhir</h2>
            <div class="relative h-96">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Produk Terlaris --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Produk Terlaris</h2>
            <ul class="divide-y divide-slate-200">
                @forelse($bestSellers as $item)
                    <li class="flex items-center justify-between py-3">
                        <span class="text-slate-700">{{ $item->product->name ?? 'Produk Tidak Dikenal' }}</span>
                        <span class="font-medium text-slate-900">{{ $item->total_qty }} unit</span>
                    </li>
                @empty
                    <li class="py-3 text-slate-500">Tidak ada data produk terlaris.</li>
                @endforelse
            </ul>
        </div>

        {{-- Produk Stok Menipis --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-rose-600 mb-4">Produk Stok Menipis</h2>
            @if($lowStockProducts->isEmpty())
                <p class="text-slate-500">Tidak ada produk dengan stok menipis saat ini.</p>
            @else
                <ul class="divide-y divide-slate-200">
                    @foreach($lowStockProducts as $product)
                        <li class="flex items-center justify-between py-3">
                            <span class="text-slate-700">{{ $product->name }}</span>
                            <span class="font-medium text-rose-600">Sisa: {{ $product->totalStock() }} (Min: {{ $product->min_stock }})</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const salesChartData = @json($salesChart);

            const dates = salesChartData.map(item => {
                const date = new Date(item.date);
                // Format tanggal menjadi "DD Mon" (misal: "04 Jul")
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            });
            const totals = salesChartData.map(item => item.total);

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Total Penjualan (Rp)',
                        data: totals,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.3,
                        fill: true // Mengisi area di bawah garis
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Penjualan'
                            },
                            ticks: {
                                callback: function(value, index, ticks) {
                                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal'
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-layouts.app> -->