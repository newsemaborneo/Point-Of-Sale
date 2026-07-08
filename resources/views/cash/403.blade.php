<x-layouts.app title="Akses Ditolak">
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <h1 class="text-6xl font-bold text-red-600">403</h1>
            <p class="text-2xl font-semibold text-gray-800 mt-4">Akses Ditolak</p>
            <p class="text-gray-600 mt-2">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
            </p>
            <a href="{{ url()->previous() }}" class="mt-6 inline-block px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-semibold">
                Kembali ke Halaman Sebelumnya
            </a>
        </div>
    </div>
</x-layouts.app>