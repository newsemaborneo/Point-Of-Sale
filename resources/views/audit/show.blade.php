<x-layouts.app title="Detail Log Aktivitas">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Detail Log Aktivitas</h1>
                <p class="text-sm text-slate-500">Informasi lengkap mengenai aktivitas sistem.</p>
            </div>
            <a href="{{ route('audit.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kembali</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                <div>
                    <p class="font-medium text-slate-800">ID Log:</p>
                    <p>{{ $activityLog->id }}</p>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Waktu:</p>
                    <p>{{ $activityLog->created_at->format('d M Y H:i:s') }}</p>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Pengguna:</p>
                    <p>{{ $activityLog->user->name ?? 'Sistem' }} (ID: {{ $activityLog->user_id ?? '-' }})</p>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Modul:</p>
                    <p>{{ $activityLog->module }}</p>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Aksi:</p>
                    <p>{{ $activityLog->action }}</p>
                </div>
                <div>
                    <p class="font-medium text-slate-800">IP Address:</p>
                    <p>{{ $activityLog->ip_address ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="font-medium text-slate-800">Deskripsi:</p>
                    <p>{{ $activityLog->description }}</p>
                </div>
                @if ($activityLog->old_data)
                    <div class="md:col-span-2">
                        <p class="font-medium text-slate-800">Data Lama:</p>
                        <pre class="bg-slate-50 p-3 rounded-lg text-xs overflow-x-auto">{{ json_encode($activityLog->old_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
                @if ($activityLog->new_data)
                    <div class="md:col-span-2">
                        <p class="font-medium text-slate-800">Data Baru:</p>
                        <pre class="bg-slate-50 p-3 rounded-lg text-xs overflow-x-auto">{{ json_encode($activityLog->new_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>