<x-layouts.app title="Notifikasi">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Notifikasi Anda</h1>
        <p class="text-sm text-slate-500">Lihat dan kelola semua notifikasi sistem.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex justify-end gap-4 mb-4">
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-300 transition-colors">Tandai Semua Dibaca</button>
                </form>
                <form action="{{ route('notifications.generate') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Generate Notifikasi Sistem</button>
                </form>
            </div>

            @if ($notifications->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada notifikasi baru.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($notifications as $notification)
                        <div class="flex items-start gap-4 p-4 rounded-xl border {{ $notification->is_read ? 'border-slate-200 bg-slate-50' : 'border-indigo-200 bg-indigo-50' }}">
                            <div class="flex-shrink-0">
                                @if (!$notification->is_read)
                                    <span class="block w-3 h-3 rounded-full bg-indigo-500 mt-1"></span>
                                @else
                                    <span class="block w-3 h-3 rounded-full bg-slate-300 mt-1"></span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900">{{ $notification->title }}</p>
                                <p class="text-sm text-slate-700">{{ $notification->message }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                @unless ($notification->is_read)
                                    <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-sm transition-colors">Tandai Dibaca</button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>