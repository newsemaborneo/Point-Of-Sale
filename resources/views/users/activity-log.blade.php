<x-layouts.app title="Log Aktivitas">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Log Aktivitas {{ $user->name }}</h1>
                <p class="text-sm text-slate-500">Riwayat tindakan pengguna di sistem.</p>
            </div>
            <a href="{{ route('users.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kembali</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Tindakan</th>
                        <th class="px-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-4">{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                            <td class="px-4 py-4">{{ $log->action }}</td>
                            <td class="px-4 py-4">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada aktivitas untuk pengguna ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts.app>
