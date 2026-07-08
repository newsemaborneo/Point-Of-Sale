<x-layouts.app title="Log Aktivitas Sistem">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Log Aktivitas Sistem</h1>
        <p class="text-sm text-slate-500">Lihat semua aktivitas yang terjadi di sistem.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex justify-end mb-4">
                <form action="{{ route('audit.backup') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Backup Database</button>
                </form>
            </div>

            @if ($logs->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada log aktivitas yang tercatat.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Pengguna</th>
                                <th class="px-4 py-3">Modul</th>
                                <th class="px-4 py-3">Aksi</th>
                                <th class="px-4 py-3">Deskripsi</th>
                                <th class="px-4 py-3">IP</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="px-4 py-4">{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-4">{{ $log->user->name ?? 'Sistem' }}</td>
                                    <td class="px-4 py-4">{{ $log->module }}</td>
                                    <td class="px-4 py-4">{{ $log->action }}</td>
                                    <td class="px-4 py-4">{{ Str::limit($log->description, 50) }}</td>
                                    <td class="px-4 py-4">{{ $log->ip_address ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('audit.show', $log) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>