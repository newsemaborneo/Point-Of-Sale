<x-layouts.app title="Point of Sale (POS)">

    <div
        id="pos-root"
        x-data="pos()"
        x-init="startClock(); watchFullscreen();"
        :class="isFullscreen ? 'fixed inset-0 z-[100] bg-slate-50/90 backdrop-blur-sm' : 'h-[calc(100dvh-7.5rem)] lg:h-[calc(100dvh-6rem)] -mx-4 -mt-2 -mb-8 sm:-m-6 lg:-m-8'"
        class="flex flex-col overflow-hidden relative bg-slate-50/50"
    >
        {{-- Background Accents --}}
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        {{-- ============================================================ --}}
        {{-- TOP BAR: clock, store status, fullscreen toggle --}}
        {{-- ============================================================ --}}
        <div class="bg-slate-900/80 backdrop-blur-xl border-b border-white/10 text-white flex items-center justify-between px-5 py-2.5 shrink-0 z-40 shadow-lg">
            {{-- Left: store status badge --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full animate-pulse"
                          :class="storeOpen ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                    <span class="text-xs font-bold"
                          x-text="storeOpen ? 'TOKO BUKA' : 'TOKO TUTUP'"></span>
                </div>
                <span class="text-slate-600 text-xs">|</span>
                <span class="text-xs text-slate-400" x-text="storeHoursLabel"></span>
            </div>

            {{-- Center: live clock & Return --}}
            <div class="hidden md:flex items-center gap-4">
                <button @click="showReturnModal = true"
                        class="flex items-center gap-1.5 text-xs font-semibold text-rose-300 hover:text-white
                               bg-rose-500/20 hover:bg-rose-600 px-3 py-1.5 rounded-xl transition-all duration-300 shadow-inner">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    Retur Transaksi
                </button>
                <div class="flex items-center gap-2 border-l border-slate-700 pl-4">
                    <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-mono font-bold tracking-widest" x-text="currentTime"></span>
                    <span class="text-xs text-slate-400" x-text="currentDate"></span>
                </div>
            </div>

            {{-- Right: fullscreen toggle --}}
            <button @click="toggleFullscreen()"
                    class="hidden md:flex items-center gap-1.5 text-xs font-semibold text-slate-300 hover:text-white
                           bg-slate-800/50 hover:bg-slate-700 px-3 py-1.5 rounded-xl transition-all duration-300 shadow-inner">
                <template x-if="!isFullscreen">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15
                                 M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                    </svg>
                </template>
                <template x-if="isFullscreen">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25
                                 M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
                    </svg>
                </template>
                <span x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"></span>
                <span class="text-slate-500 text-[10px] ml-1">F11</span>
            </button>

            {{-- Mobile Tab Toggle --}}
            <div class="flex md:hidden items-center bg-slate-800/60 rounded-lg p-1 gap-1">
                <button @click="mobileTab = 'products'" 
                        :class="mobileTab === 'products' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white'"
                        class="px-3 py-1.5 rounded-md text-xs font-bold transition-all">Produk</button>
                <button @click="mobileTab = 'cart'" 
                        :class="mobileTab === 'cart' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white'"
                        class="px-3 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5">
                    Keranjang
                    <span x-show="cart.length > 0" x-text="cart.length" class="bg-rose-500 text-white px-1.5 py-0.5 rounded-full text-[9px] leading-none"></span>
                </button>
            </div>
        </div>


        <div class="relative flex flex-1 flex-col lg:flex-row overflow-hidden">


        <div x-show="showOpenRegisterModal" style="display:none"
             class="fixed inset-0 z-[110] flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342
                                         1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0
                                         0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75
                                         c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504
                                         1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21
                                         a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0
                                         01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15
                                         10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-white">Buka Sesi Kasir</h2>
                            <p class="text-indigo-200 text-xs mt-0.5">Masukkan saldo awal sebelum mulai bertransaksi</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Saldo Awal Kas</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-500 font-semibold text-sm">Rp</span>
                            </div>
                            <input type="number" x-model="openRegisterForm.opening_balance"
                                   placeholder="0" min="0"
                                   class="block w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200
                                          text-slate-900 rounded-xl focus:ring-2 focus:ring-indigo-500
                                          font-bold text-xl transition-all">
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Jumlah uang tunai yang ada di laci kasir saat ini.</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28
                                 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347
                                 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75
                                 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                 clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs text-amber-700 font-medium">
                            Saldo awal akan digunakan untuk menghitung selisih kas saat sesi ditutup.
                        </p>
                    </div>
                </div>
                <div class="px-6 pb-6 flex gap-3">
                    <button @click="openRegister" :disabled="isOpeningRegister"
                            class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl
                                   bg-indigo-600 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700
                                   disabled:opacity-60 transition-all shadow-lg shadow-indigo-500/30">
                        <span x-show="!isOpeningRegister">Buka Kasir &amp; Mulai Transaksi</span>
                        <span x-show="isOpeningRegister">Membuka...</span>
                    </button>
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex justify-center items-center rounded-xl bg-slate-100
                              px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-all">
                        Batal
                    </a>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- STATUS BAR KASIR (desktop) --}}
        {{-- FIX #1: parent sekarang `relative`, jadi badge ini menempel --}}
        {{-- dengan benar di pojok kanan atas area POS (bukan di root layout) --}}
        {{-- ============================================================ --}}
        @if($cashRegister)
        <div class="hidden md:block absolute top-2 right-0 z-30 m-4">
            <div class="flex items-center gap-2 bg-emerald-600 text-white rounded-full px-4 py-1.5
                        text-xs font-bold shadow-lg">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                Kasir Aktif &middot; Saldo Awal: Rp {{ number_format($cashRegister->opening_balance, 0, ',', '.') }}
            </div>
        </div>
        @endif


        <div class="flex-1 lg:flex-1 flex-col min-h-0 overflow-hidden" :class="mobileTab === 'products' ? 'flex' : 'hidden lg:flex'">
            <div class="bg-white/70 backdrop-blur-md border-b border-white/40 shadow-sm z-10 px-5 py-4">
                <div class="relative flex-1 mb-4">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery"
                           placeholder="Cari produk atau scan barcode..."
                           @keydown.f2.prevent="$el.focus()"
                           @keydown.enter.prevent="addScannedProduct()"
                           class="block w-full pl-12 pr-14 py-3.5 bg-white/80 border border-slate-200/60 text-slate-900
                                  rounded-2xl shadow-inner focus:bg-white focus:ring-2
                                  focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all
                                  placeholder:text-slate-400 font-medium">
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                        <button @click="openScannerModal()" type="button" title="Scan dengan Kamera" class="p-2 text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-colors shadow-sm focus:outline-none">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Category filter chips: tampil di semua ukuran layar --}}
                <div class="flex items-center gap-2.5 overflow-x-auto pb-1 custom-scrollbar">
                    <button @click="activeCategory = null"
                            :class="activeCategory === null
                                ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-500/30'
                                : 'bg-white/80 text-slate-600 hover:bg-white hover:shadow-sm border border-slate-200/60'"
                            class="shrink-0 text-xs font-bold px-4 py-2.5 rounded-xl transition-all duration-300">
                        Semua
                    </button>
                    @foreach($categories as $cat)
                    <button @click="activeCategory = {{ $cat->id }}"
                            :class="activeCategory === {{ $cat->id }}
                                ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-500/30'
                                : 'bg-white/80 text-slate-600 hover:bg-white hover:shadow-sm border border-slate-200/60'"
                            class="shrink-0 text-xs font-bold px-4 py-2.5 rounded-xl transition-all duration-300">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Products grid --}}
            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar relative z-0">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="group relative bg-white/70 backdrop-blur-sm border border-slate-200/50 rounded-3xl
                                    overflow-hidden cursor-pointer hover:border-indigo-400
                                    hover:shadow-2xl hover:shadow-indigo-500/20 transition-all
                                    duration-300 transform hover:-translate-y-1.5 flex flex-col
                                    select-none"
                             :class="product.stock <= 0 ? 'opacity-60 cursor-not-allowed' : ''">
                            <div class="aspect-[4/3] sm:aspect-square bg-slate-100/50 overflow-hidden relative">
                                <img :src="product.photo && !product.photo.startsWith('📦')
                                            ? '/storage/' + product.photo
                                            : 'https://placehold.co/200x200/e2e8f0/94a3b8?text=Produk'"
                                     :alt="product.name"
                                     class="w-full h-full object-cover transition-transform
                                            duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-indigo-900/60
                                            via-transparent to-transparent opacity-0
                                            group-hover:opacity-100 transition-opacity duration-300
                                            flex items-end justify-center pb-4">
                                    <span class="bg-white/95 text-indigo-700 text-xs font-black
                                                 px-4 py-2 rounded-xl shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">+ Tambah</span>
                                </div>
                                <template x-if="product.stock <= 0">
                                    <div class="absolute inset-0 bg-white/70 backdrop-blur-[2px] flex items-center justify-center">
                                        <span class="bg-rose-100 text-rose-600 text-xs font-bold
                                                     px-3 py-1.5 rounded-lg">Habis</span>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4 flex flex-col flex-1 bg-white/50">
                                <h3 class="text-xs font-bold text-slate-800 line-clamp-2
                                           leading-tight flex-1" x-text="product.name"></h3>
                                <div class="mt-2.5 flex items-end justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">
                                            Stok: <span x-text="product.stock" class="text-slate-600"></span>
                                        </p>
                                        <p class="text-sm font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600"
                                           x-text="formatRupiah(product.price)"></p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="filteredProducts.length === 0">
                        <div class="col-span-full flex flex-col items-center justify-center
                                    py-16 text-slate-400">
                            <svg class="h-14 w-14 mb-3 opacity-30" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25
                                         2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25
                                         c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125
                                         -1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504
                                         1.125 1.125 1.125z"/>
                            </svg>
                            <p class="font-medium text-sm">Tidak ada produk ditemukan.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="lg:flex-none w-full lg:w-[400px] bg-white/80 backdrop-blur-xl
                    border-t lg:border-t-0 lg:border-l border-white/50
                    shadow-[-10px_0_30px_-15px_rgba(0,0,0,0.1)] flex-col z-20 min-h-0"
             :class="mobileTab === 'cart' ? 'flex flex-1' : 'hidden lg:flex'">

            {{-- Cart header --}}
            <div class="px-5 py-4 border-b border-slate-200/50 flex items-center
                        justify-between bg-white/50 shrink-0">
                <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5
                                 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3
                                 2.1-4.684 2.924-7.138a60.114 60.114 0
                                 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75
                                 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0
                                 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    Keranjang
                    <span x-show="cart.length > 0" x-text="cart.length"
                          class="bg-indigo-600 text-white text-xs font-bold
                                 px-1.5 py-0.5 rounded-full"></span>
                </h2>
                <button @click="cart = []" x-show="cart.length > 0"
                        class="text-xs font-bold text-rose-500 hover:text-rose-600
                               bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-md
                               transition-colors">
                    Kosongkan
                </button>
            </div>

            {{-- Cart items --}}
            <div class="flex-1 lg:flex-1 overflow-y-auto p-3 custom-scrollbar min-h-0 lg:max-h-full">
                <template x-if="cart.length === 0">
                    <div class="flex flex-col items-center justify-center h-full
                                text-slate-400 opacity-60 py-12">
                        <svg class="h-14 w-14 mb-3" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383
                                     1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75
                                     -3h11.218c1.121-2.3 2.1-4.684
                                     2.924-7.138a60.114 60.114 0
                                     00-16.536-1.84M7.5 14.25L5.106
                                     5.272M6 20.25a.75.75 0 11-1.5
                                     0 .75.75 0 011.5 0zm12.75
                                     0a.75.75 0 11-1.5 0 .75.75
                                     0 011.5 0z"/>
                        </svg>
                        <p class="font-semibold text-sm">Keranjang kosong</p>
                        <p class="text-xs mt-1">Klik produk untuk menambahkan</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="p-4 bg-white/70 border border-slate-200/60 rounded-2xl
                                shadow-sm mb-3 hover:border-indigo-300 hover:shadow-md transition-all duration-300 group">
                        <div class="flex justify-between items-start gap-2">
                            <h4 class="text-sm font-bold text-slate-800 leading-tight
                                       line-clamp-2 flex-1" x-text="item.name"></h4>
                            <button @click="removeFromCart(index)"
                                    class="text-slate-300 hover:text-rose-500
                                           transition-colors shrink-0 opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0
                                         006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75
                                         0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0
                                         007.596 19h4.807a2.75 2.75 0
                                         002.742-2.53l.841-10.52.149.023a.75.75 0
                                         00.23-1.482A41.03 41.03 0 0014
                                         4.193V3.75A2.75 2.75 0 0011.25
                                         1h-2.5zM10 4c.84 0 1.673.025
                                         2.5.075V3.75c0-.69-.56-1.25-1.25
                                         -1.25h-2.5c-.69 0-1.25.56-1.25
                                         1.25v.325C8.327 4.025 9.16 4
                                         10 4zM8.58 7.72a.75.75 0
                                         00-1.5.06l.3 7.5a.75.75 0
                                         101.5-.06l-.3-7.5zm4.34.06a.75.75
                                         0 10-1.5-.06l-.3 7.5a.75.75 0
                                         101.5.06l.3-7.5z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <div class="font-black text-indigo-600 text-sm"
                                 x-text="formatRupiah(item.price * item.quantity)"></div>
                            <div class="flex items-center bg-slate-100/80 rounded-xl p-1 border border-slate-200/50">
                                <button @click="updateQty(index, -1)"
                                        class="w-8 h-8 flex items-center justify-center
                                               text-slate-600 hover:bg-white hover:shadow-sm
                                               rounded-lg transition-all font-bold text-lg">−</button>
                                <span class="w-10 text-center text-sm font-black text-slate-800"
                                      x-text="item.quantity"></span>
                                <button @click="updateQty(index, 1)"
                                        class="w-8 h-8 flex items-center justify-center
                                               text-slate-600 hover:bg-white hover:shadow-sm
                                               rounded-lg transition-all font-bold text-lg">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Checkout section --}}
            <div class="bg-white/90 backdrop-blur-lg border-t border-slate-200/60 p-5 space-y-4
                        shadow-[0_-20px_40px_-15px_rgba(0,0,0,0.1)] shrink-0 z-10">
                <div class="space-y-2.5">
                    {{-- Subtotal --}}
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-medium">Subtotal</span>
                        <span class="font-bold text-slate-700" x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-medium">Diskon</span>
                        <span class="font-bold text-emerald-600"
                              x-text="'- ' + formatRupiah(totalDiscount)"></span>
                    </div>
                    <div x-show="appliedVoucher" class="flex justify-between text-sm text-emerald-600">
                        <span class="text-slate-500 font-medium">Voucher (<span x-text="appliedVoucher.code"></span>)</span>
                        <span class="font-bold" x-text="'- ' + formatRupiah(voucherDiscountAmount)"></span>
                    </div>
                    <div class="flex justify-between items-center pt-3
                                border-t border-dashed border-slate-200">
                        <span class="text-slate-800 font-black text-lg">Total</span>
                        <span class="font-black text-3xl text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600"
                              x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>

                {{-- Voucher Input --}}
                <div class="border-t border-slate-200/60 pt-4">
                    <label for="voucher-code" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kode Voucher</label>
                    <div class="flex gap-2">
                        <input type="text" id="voucher-code" x-model="voucherCodeInput"
                               placeholder="Masukkan kode voucher"
                               class="flex-1 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                        <button @click="applyVoucher()" :disabled="!voucherCodeInput || isLoadingVoucher"
                                class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-bold text-white
                                       hover:bg-slate-700 transition-all shadow-md disabled:opacity-50 disabled:shadow-none">
                            <span x-show="!isLoadingVoucher">Terapkan</span>
                            <span x-show="isLoadingVoucher">Proses...</span>
                        </button>
                    </div>
                    <p x-show="voucherMessage"
                       :class="voucherMessageSuccess ? 'text-emerald-600' : 'text-rose-600'"
                       class="text-xs font-medium mt-1.5" x-text="voucherMessage"></p>
                    <button x-show="appliedVoucher" @click="removeVoucher()"
                            class="text-xs font-bold text-rose-500 hover:text-rose-600 mt-2 transition-colors">Hapus Voucher</button>
                </div>
                <button @click="showCheckoutModal = true" :disabled="cart.length === 0 || !storeOpen"
                        class="w-full relative group overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600
                               px-4 py-4 font-black text-white shadow-xl shadow-indigo-500/30 transition-all duration-300
                               hover:-translate-y-1 hover:shadow-indigo-500/40 active:translate-y-0 disabled:opacity-50
                               disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-indigo-500/30">
                    <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative flex items-center justify-center gap-2 text-base">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198
                                     1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75
                                     0 013 6h-.75m0 0v-.375c0-.621.504-1.125
                                     1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0
                                     .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0
                                     1.125.504 1.125 1.125v9.75c0 .621-.504
                                     1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75
                                     0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125
                                     1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75
                                     0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Proses Pembayaran</span>
                    </div>
                </button>
            </div>
        </div>
        <div x-show="!storeOpen && storeHours && storeHours.enabled"
             style="display:none"
             class="absolute inset-0 z-[90] flex items-center justify-center
                    bg-slate-900/80 backdrop-blur-sm">
            <div class="text-center px-8 py-10 rounded-3xl bg-white shadow-2xl max-w-sm mx-4">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center
                            rounded-full bg-rose-100">
                    <svg class="h-10 w-10 text-rose-600" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948
                                 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949
                                 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12
                                 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900">Toko Sedang Tutup</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Transaksi tidak dapat diproses di luar jam operasional.
                </p>
                <div class="mt-4 inline-flex items-center gap-2 bg-slate-100
                            rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="'Buka: ' + storeHours.open + ' – ' + storeHours.close"></span>
                </div>
                <p class="mt-3 text-xs text-slate-400" x-text="'Sekarang: ' + currentTime"></p>
            </div>
        </div>
        </div>{{-- end MAIN BODY --}}
        <div x-show="showCheckoutModal" style="display:none"
             class="fixed inset-0 z-[110] flex items-center justify-center px-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"
                 @click="showCheckoutModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-800">Konfirmasi Pembayaran</h3>
                    <button @click="showCheckoutModal = false"
                            class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94
                                     10l-3.72 3.72a.75.75 0 101.06 1.06L10
                                     11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06
                                     10l3.72-3.72a.75.75 0 00-1.06-1.06L10
                                     8.94 6.28 5.22z"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    {{-- Customer --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Pelanggan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <select x-model="checkoutForm.customer_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50
                                       px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pelanggan Umum / Tanpa Akun</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Metode pembayaran --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Metode Pembayaran
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="method in paymentMethods" :key="method.value">
                                <label class="relative flex flex-col items-center cursor-pointer
                                              rounded-2xl border-2 p-4 transition-all duration-300"
                                       :class="checkoutForm.payment_method === method.value
                                           ? 'border-indigo-500 bg-gradient-to-br from-indigo-50 to-blue-50 shadow-md shadow-indigo-500/10'
                                           : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                                    <input type="radio" x-model="checkoutForm.payment_method"
                                           :value="method.value" class="sr-only">
                                    <span class="text-3xl mb-2" x-text="method.icon"></span>
                                    <span class="text-sm font-bold text-slate-700"
                                          x-text="method.label"></span>
                                    <div x-show="checkoutForm.payment_method === method.value"
                                         class="absolute top-2 right-2 text-indigo-600">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Uang diterima (cash only) --}}
                    <div x-show="checkoutForm.payment_method === 'cash'" x-transition>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Uang Diterima
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center
                                        pointer-events-none">
                                <span class="text-slate-500 font-semibold">Rp</span>
                            </div>
                            <input type="number" x-model="checkoutForm.paid_amount"
                                   :min="grandTotal"
                                   class="block w-full pl-12 pr-4 py-3 bg-slate-50
                                          border border-slate-200 text-slate-900 rounded-xl
                                          focus:ring-2 focus:ring-indigo-600 font-bold text-xl">
                        </div>
                        <div class="flex gap-2 mt-2.5 flex-wrap">
                            <template x-for="amount in quickAmounts" :key="amount">
                                <button @click="checkoutForm.paid_amount = amount"
                                        class="text-xs font-bold px-3 py-1.5 bg-slate-100
                                               hover:bg-indigo-100 hover:text-indigo-700
                                               rounded-lg transition-colors"
                                        x-text="formatRupiah(amount)"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Ringkasan --}}
                    <div class="bg-slate-50 rounded-xl p-4 space-y-2 border border-slate-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Total Tagihan</span>
                            <span class="font-bold text-slate-800"
                                  x-text="formatRupiah(grandTotal)"></span>
                        </div>
                        <div x-show="checkoutForm.payment_method === 'cash'"
                             class="flex justify-between text-sm">
                            <span class="text-slate-500">Uang Diterima</span>
                            <span class="font-bold text-slate-800"
                                  x-text="formatRupiah(Number(checkoutForm.paid_amount)||0)"></span>
                        </div>
                        <div x-show="checkoutForm.payment_method === 'cash'"
                             class="flex justify-between pt-2 border-t border-slate-200">
                            <span class="font-semibold text-slate-700">Kembalian</span>
                            <span class="font-black text-xl"
                                  :class="(Number(checkoutForm.paid_amount)-grandTotal)>=0
                                      ? 'text-emerald-600' : 'text-rose-500'"
                                  x-text="formatRupiah(Math.max(0,Number(checkoutForm.paid_amount)-grandTotal))">
                            </span>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button @click="processPayment" :disabled="isLoading"
                            class="flex-1 inline-flex justify-center items-center gap-2
                                   rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3.5 text-base font-black
                                   text-white hover:from-indigo-700 hover:to-blue-700 disabled:opacity-60
                                   transition-all duration-300 shadow-xl shadow-indigo-500/30 transform hover:-translate-y-1">
                        <svg x-show="!isLoading" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143
                                 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75
                                 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0
                                 011.05-.143z" clip-rule="evenodd"/>
                        </svg>
                        <span x-show="!isLoading">Konfirmasi &amp; Bayar</span>
                        <span x-show="isLoading">Memproses...</span>
                    </button>
                    <button @click="showCheckoutModal = false"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-3
                                   text-sm font-semibold text-slate-600 hover:bg-slate-50
                                   transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <div x-show="showSuccessModal" style="display:none"
             class="fixed inset-0 z-[120] flex items-center justify-center px-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                {{-- Success header --}}
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 px-8 py-8 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center
                                rounded-full bg-white/20 shadow-inner">
                        <svg class="h-9 w-9 text-white" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75
                                 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75
                                 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0
                                 00-1.06-1.06l-3.892 3.893-1.48-1.481a.75.75 0
                                 10-1.06 1.06l2.01 2.012a.75.75 0 001.06 0l4.422-4.424z"
                                 clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-white">Pembayaran Berhasil!</h2>
                    <p class="text-emerald-100 text-sm mt-1" x-text="successData.invoice"></p>
                </div>

                {{-- Receipt summary --}}
                <div class="p-6 space-y-3">
                    <div class="bg-slate-50 rounded-2xl p-4 space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Total Belanja</span>
                            <span class="font-bold text-slate-800"
                                  x-text="formatRupiah(successData.grand_total)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Metode Bayar</span>
                            <span class="font-bold text-slate-800 capitalize"
                                  x-text="successData.payment_method"></span>
                        </div>
                        <template x-if="successData.payment_method === 'cash'">
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Uang Diterima</span>
                                    <span class="font-bold text-slate-800"
                                          x-text="formatRupiah(successData.paid_amount)"></span>
                                </div>
                                <div class="flex justify-between text-sm pt-2
                                            border-t border-slate-200 mt-2">
                                    <span class="font-semibold text-slate-700">Kembalian</span>
                                    <span class="font-black text-lg text-emerald-600"
                                          x-text="formatRupiah(successData.change)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button @click="showSuccessModal = false"
                                class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm
                                       font-bold text-white hover:bg-emerald-700
                                       transition-colors shadow-lg shadow-emerald-500/20">
                            Transaksi Baru
                        </button>
                        <button type="button"
                                @click="printReceipt(successData.sale_id)"
                                class="flex-1 rounded-xl border-2 border-slate-200 bg-white
                                       px-4 py-3 text-sm font-bold text-slate-700
                                       hover:bg-slate-50 transition-colors text-center">
                            Cetak Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Return Modal --}}
        <div x-show="showReturnModal" style="display:none"
             class="fixed inset-0 z-[120] flex items-center justify-center px-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReturnModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-rose-50/50">
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Retur Transaksi
                    </h3>
                    <button @click="showReturnModal = false"
                            class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                        </svg>
                    </button>
                </div>
                                <div class="p-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nomor Invoice
                    </label>
                    <input type="text" x-model="returnInvoiceNumber"
                           placeholder="Contoh: INV-2026..."
                           @keydown.enter="searchReturn"
                           class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-2 focus:ring-rose-500 font-bold uppercase transition-all mb-2">
                    
                    <template x-if="returnError">
                        <p class="text-xs text-rose-500 font-medium mb-3" x-text="returnError"></p>
                    </template>
                    
                    <div class="flex gap-2.5 mt-4">
                        <button @click="searchReturn" :disabled="isSearchingReturn || !returnInvoiceNumber"
                                class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700 disabled:opacity-60 transition-all shadow-md shadow-rose-500/20">
                            <span x-show="!isSearchingReturn">Cari & Proses</span>
                            <span x-show="isSearchingReturn">Mencari...</span>
                        </button>
                        <button @click="showReturnModal = false"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Camera Scanner Modal --}}
        <div x-show="showScannerModal" style="display:none"
             class="fixed inset-0 z-[130] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="relative w-full max-w-md flex flex-col bg-white rounded-3xl overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Scan Barcode via Kamera
                    </h3>
                    <div class="flex items-center gap-2">
                        {{-- Flash / Torch Button --}}
                        <button x-show="hasTorch" @click="toggleTorch()" type="button" 
                                :class="isTorchOn ? 'bg-amber-400 text-slate-900 shadow-md shadow-amber-400/40 ring-2 ring-amber-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all" title="Nyalakan / Matikan Lampu Flash">
                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 2L3 14h8l-1 8 11-13h-8l1-7z"/>
                            </svg>
                            <span x-text="isTorchOn ? 'Flash ON' : 'Flash OFF'"></span>
                        </button>
                        
                        <button @click="closeScannerModal()"
                                class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg p-1.5 transition-colors">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- High Performance Direct Video Scanner Feed --}}
                <div class="bg-black relative flex items-center justify-center min-h-[300px] max-h-[380px] overflow-hidden">
                    <video id="scanner-live-video" class="w-full h-full object-cover" playsinline autoplay muted></video>
                    
                    {{-- Laser Target Guide --}}
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="w-4/5 h-36 border-2 border-indigo-400/80 rounded-2xl relative shadow-[0_0_20px_rgba(99,102,241,0.4)]">
                            <div class="absolute inset-x-2 top-1/2 h-0.5 bg-rose-500 shadow-[0_0_12px_3px_rgba(244,63,94,0.9)] animate-pulse"></div>
                            <div class="absolute -top-1.5 -left-1.5 w-4 h-4 border-t-4 border-l-4 border-indigo-500 rounded-tl"></div>
                            <div class="absolute -top-1.5 -right-1.5 w-4 h-4 border-t-4 border-r-4 border-indigo-500 rounded-tr"></div>
                            <div class="absolute -bottom-1.5 -left-1.5 w-4 h-4 border-b-4 border-l-4 border-indigo-500 rounded-bl"></div>
                            <div class="absolute -bottom-1.5 -right-1.5 w-4 h-4 border-b-4 border-r-4 border-indigo-500 rounded-br"></div>
                        </div>
                    </div>
                </div>
                
                {{-- Helper & Fallback Manual Input --}}
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col gap-2.5">
                    <p class="text-xs text-slate-500 text-center font-medium">Posisikan barcode produk di dalam kotak bidik</p>
                    <div class="flex gap-2">
                        <input type="text" x-model="searchQuery" @keydown.enter.prevent="addScannedProduct(); closeScannerModal();" placeholder="Ketik manual barcode..." class="flex-1 px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                        <button @click="addScannedProduct(); closeScannerModal();" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition-colors">Cari</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js"></script>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pos', () => ({

            /* ── data ── */
            products:     @json($productsJson),
            storeHours:   @json($storeHours),   // { open: "08:00", close: "21:00", enabled: true }
            searchQuery:  '',
            activeCategory: null,
            mobileTab: 'products',
            barcodeBuffer: '',
            barcodeTimeout: null,
            cart:         [],
            availablePromotions: @json($activePromotions),
            availableVouchers:   @json($activeVouchers),
            voucherCodeInput: '',
            appliedVoucher: null,
            discount:     0,
            isFullscreen: false,
            currentTime:  '',
            currentDate:  '',
            storeOpen:    false,
            storeHoursLabel: '',
            showCheckoutModal:    false,
            showSuccessModal:     false,
            showReturnModal:      false,
            showScannerModal:     false,
            hasTorch:             false,
            isTorchOn:            false,
            cameraMediaStream:    null,
            barcodeAnimId:        null,
            zxingReaderInstance:  null,
            isScanningInProgress: false,
            returnInvoiceNumber:  '',
            returnError:          '',
            isSearchingReturn:    false,
            showOpenRegisterModal: {{ $cashRegister ? 'false' : 'true' }},
            isLoading:           false,
            isOpeningRegister:   false,
            isLoadingVoucher:    false,
            voucherMessage:      '',
            voucherMessageSuccess: false,
            openRegisterForm:  { opening_balance: 0 },
            successData: {
                invoice: '', sale_id: null, grand_total: 0,
                paid_amount: 0, change: 0, payment_method: 'cash',
            },
            checkoutForm: {
                customer_id:     '',
                payment_method:  'cash',
                paid_amount:     0,
            },
            paymentMethods: [
                { value: 'cash',     label: 'Tunai',    icon: '💵' },
                { value: 'transfer', label: 'Transfer', icon: '🏦' },
                { value: 'qris',     label: 'QRIS',     icon: '📱' },
            ],

            /* ── computed ── */
            get filteredProducts() {
                return this.products.filter(p => {
                    const q = this.searchQuery ? this.searchQuery.trim().toLowerCase() : '';
                    const matchSearch = !q ||
                        p.name.toLowerCase().includes(q) ||
                        (p.barcode && String(p.barcode).toLowerCase().includes(q)) ||
                        (p.sku && String(p.sku).toLowerCase().includes(q));
                    const matchCat = this.activeCategory === null ||
                        p.category_id === this.activeCategory;
                    return matchSearch && matchCat;
                });
            },
            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            openScannerModal() {
                this.showScannerModal = true;
                this.$nextTick(() => {
                    this.startHardwareAcceleratedScanner();
                });
            },
            
            async startHardwareAcceleratedScanner() {
                this.isScanningInProgress = true;
                const videoEl = document.getElementById('scanner-live-video');
                if (!videoEl) return;

                try {
                    // Open camera stream
                    this.cameraMediaStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 1280, min: 640 },
                            height: { ideal: 720, min: 480 }
                        },
                        audio: false
                    });
                    videoEl.srcObject = this.cameraMediaStream;
                    await videoEl.play();

                    // METHOD 1: Native Android Chromium BarcodeDetector (Hardware Accelerated, Ultra-Fast!)
                    if ('BarcodeDetector' in window) {
                        try {
                            const detector = new BarcodeDetector({
                                formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e', 'qr_code', 'itf', 'codabar']
                            });

                            const detectLoop = async () => {
                                if (!this.isScanningInProgress) return;
                                try {
                                    if (videoEl.readyState >= 2) {
                                        const detected = await detector.detect(videoEl);
                                        if (detected.length > 0 && detected[0].rawValue) {
                                            this.onCodeSuccessfullyScanned(detected[0].rawValue);
                                            return;
                                        }
                                    }
                                } catch (loopErr) {}
                                this.barcodeAnimId = requestAnimationFrame(detectLoop);
                            };
                            this.barcodeAnimId = requestAnimationFrame(detectLoop);
                            return;
                        } catch (nativeErr) {
                            console.warn('Native BarcodeDetector init failed, using ZXing fallback', nativeErr);
                        }
                    }

                    // METHOD 2: ZXing Browser MultiFormat Reader Fallback (iOS Safari / Other)
                    if (typeof ZXing !== 'undefined') {
                        const hints = new Map();
                        const formats = [
                            ZXing.BarcodeFormat.EAN_13,
                            ZXing.BarcodeFormat.EAN_8,
                            ZXing.BarcodeFormat.CODE_128,
                            ZXing.BarcodeFormat.CODE_39,
                            ZXing.BarcodeFormat.UPC_A,
                            ZXing.BarcodeFormat.UPC_E,
                            ZXing.BarcodeFormat.QR_CODE
                        ];
                        hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, formats);
                        hints.set(ZXing.DecodeHintType.TRY_HARDER, true);

                        this.zxingReaderInstance = new ZXing.BrowserMultiFormatReader(hints);
                        this.zxingReaderInstance.decodeFromVideoElement(videoEl, (result, err) => {
                            if (result && result.getText() && this.isScanningInProgress) {
                                this.onCodeSuccessfullyScanned(result.getText());
                            }
                        });
                    }
                } catch (cameraErr) {
                    console.error('Gagal mengakses kamera:', cameraErr);
                    alert('Gagal mengakses kamera: ' + (cameraErr?.message || 'Izin kamera ditolak.'));
                }
            },

            onCodeSuccessfullyScanned(code) {
                if (!code || !this.isScanningInProgress) return;
                this.isScanningInProgress = false;
                this.searchQuery = code.trim();
                this.addScannedProduct();
                this.closeScannerModal();
            },
            
            closeScannerModal() {
                this.isScanningInProgress = false;
                if (this.barcodeAnimId) {
                    cancelAnimationFrame(this.barcodeAnimId);
                    this.barcodeAnimId = null;
                }
                if (this.zxingReaderInstance) {
                    try { this.zxingReaderInstance.reset(); } catch(e) {}
                    this.zxingReaderInstance = null;
                }
                if (this.cameraMediaStream) {
                    this.cameraMediaStream.getTracks().forEach(track => track.stop());
                    this.cameraMediaStream = null;
                }
                const videoEl = document.getElementById('scanner-live-video');
                if (videoEl) {
                    videoEl.srcObject = null;
                }
                this.showScannerModal = false;
            },

            searchReturn() {
                if (!this.returnInvoiceNumber) return;
                this.isSearchingReturn = true;
                this.returnError = '';
                fetch(`/api/sales/by-invoice/${this.returnInvoiceNumber}`)
                    .then(r => r.json())
                    .then(data => {
                        this.isSearchingReturn = false;
                        if (data.success && data.sale) {
                            window.location.href = `/sales/${data.sale.id}/return/create`;
                        } else {
                            this.returnError = 'Invoice tidak ditemukan atau status salah.';
                        }
                    })
                    .catch(err => {
                        this.isSearchingReturn = false;
                        this.returnError = 'Terjadi kesalahan sistem.';
                    });
            },
            get promotionDiscountAmount() {
                let promoDisc = 0;
                // Apply product-specific percentage/nominal discounts from promotions
                this.cart.forEach(item => {
                    this.availablePromotions.forEach(promo => {
                        // Check if promotion applies to this product
                        const productInPromo = promo.products.some(p => p.id === item.product_id);
                        const appliesToAll = promo.products.length === 0;

                        if (promo.is_active && (productInPromo || appliesToAll)) {
                            if (promo.type === 'percent_discount') {
                                promoDisc += (item.price * item.quantity) * (promo.value / 100);
                            } else if (promo.type === 'nominal_discount') {
                                promoDisc += promo.value * item.quantity;
                            }
                            // For buy_x_get_y, bundling, happy_hour, more complex logic is needed.
                            // For now, we'll keep it simple.
                        }
                    });
                });
                return promoDisc;
            },
            get voucherDiscountAmount() {
                if (!this.appliedVoucher) return 0;

                let discount = 0;
                if (this.subtotal < this.appliedVoucher.min_purchase) {
                    return 0; // Voucher min purchase not met
                }

                if (this.appliedVoucher.type === 'percent') {
                    discount = this.subtotal * (this.appliedVoucher.value / 100);
                    if (this.appliedVoucher.max_discount && discount > this.appliedVoucher.max_discount) {
                        discount = this.appliedVoucher.max_discount;
                    }
                } else if (this.appliedVoucher.type === 'nominal') {
                    discount = this.appliedVoucher.value;
                }
                return discount;
            },
            get totalDiscount() {
                return this.promotionDiscountAmount + this.voucherDiscountAmount;
            },
            get grandTotal() { return this.subtotal - this.totalDiscount; },
            get quickAmounts() {
                const gt = this.grandTotal;
                const ceil = (v, n) => Math.ceil(v / n) * n;
                return [...new Set([
                    ceil(gt, 1000), ceil(gt, 5000),
                    ceil(gt, 10000), ceil(gt, 50000),
                    ceil(gt, 100000)
                ])].filter(v => v >= gt);
            },

            /* ── clock ── */
            startClock() {
                const tick = () => {
                    const now  = new Date();
                    const hh   = String(now.getHours()).padStart(2,'0');
                    const mm   = String(now.getMinutes()).padStart(2,'0');
                    const ss   = String(now.getSeconds()).padStart(2,'0');
                    this.currentTime = `${hh}:${mm}:${ss}`;
                    this.currentDate = now.toLocaleDateString('id-ID', {
                        weekday: 'short', day: '2-digit',
                        month:   'short', year: 'numeric'
                    });
                    this._checkStoreOpen(now);
                };
                tick();
                setInterval(tick, 1000);

                /* F11 & F2 shortcuts + Global Barcode Scanner */
                document.addEventListener('keydown', e => {
                    // Shortcuts
                    if (e.key === 'F11') { e.preventDefault(); this.toggleFullscreen(); }
                    if (e.key === 'F2')  { e.preventDefault(); document.querySelector('[x-model="searchQuery"]')?.focus(); }

                    // Jangan tangkap ketikan jika user sedang mengetik di input field (kecuali radio/checkbox)
                    const isInputFocus = (e.target.tagName === 'INPUT' && !['radio', 'checkbox'].includes(e.target.type)) || e.target.tagName === 'TEXTAREA';
                    
                    if (!isInputFocus) {
                        if (e.key.length === 1) { // Karakter biasa dari scanner
                            this.barcodeBuffer += e.key;
                            clearTimeout(this.barcodeTimeout);
                            // Scanner hardware mengetik sangat cepat (biasanya <30ms per karakter)
                            this.barcodeTimeout = setTimeout(() => {
                                this.barcodeBuffer = '';
                            }, 100); 
                        } else if (e.key === 'Enter' && this.barcodeBuffer.length >= 3) {
                            e.preventDefault();
                            this.handleGlobalScan(this.barcodeBuffer);
                            this.barcodeBuffer = '';
                        }
                    }
                });
            },

            /* ── store open/close check ── */
            _checkStoreOpen(now) {
                if (!this.storeHours || !this.storeHours.enabled) {
                    this.storeOpen = true;
                    this.storeHoursLabel = 'Jadwal tidak diaktifkan';
                    return;
                }
                const [oh, om] = this.storeHours.open.split(':').map(Number);
                const [ch, cm] = this.storeHours.close.split(':').map(Number);
                const cur  = now.getHours() * 60 + now.getMinutes();
                const open = oh * 60 + om;
                const cls  = ch * 60 + cm;
                /* handle overnight (close < open) */
                if (cls < open) {
                    this.storeOpen = cur >= open || cur < cls;
                } else {
                    this.storeOpen = cur >= open && cur < cls;
                }
                this.storeHoursLabel = `Jam Buka: ${this.storeHours.open} – ${this.storeHours.close}`;
            },

            /* ── fullscreen ── */
            toggleFullscreen() {
                const el = document.getElementById('pos-root');
                if (!document.fullscreenElement) {
                    el.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            },
            watchFullscreen() {
                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                    /* hide/show sidebar & header when in fullscreen */
                    const sidebar = document.querySelector('aside');
                    const header  = document.querySelector('header');
                    if (sidebar) sidebar.style.display = this.isFullscreen ? 'none' : '';
                    if (header)  header.style.display  = this.isFullscreen ? 'none' : '';
                });
            },

            /* ── cart ── */
            addToCart(product) {
                if (product.stock <= 0) return;
                const existing = this.cart.find(i => i.product_id === product.id);
                if (existing) {
                    if (product.stock > 0) {
                        existing.quantity++;
                        product.stock--;   // kurangi stok tampilan saat produk ditambahkan
                    }
                } else {
                    this.cart.push({
                        product_id:  product.id,
                        name:        product.name,
                        price:       product.price,
                        category_id: product.category_id,
                        quantity:    1,
                    });
                    product.stock--;       // kurangi stok tampilan saat produk pertama kali masuk keranjang
                }
                this.updatePaidAmount();
            },
            addScannedProduct() {
                if (!this.searchQuery || this.searchQuery.trim() === '') return;

                const query = this.searchQuery.trim().toLowerCase();
                const exactMatch = this.products.find(p =>
                    (p.barcode && String(p.barcode).trim().toLowerCase() === query) ||
                    (p.sku && String(p.sku).trim().toLowerCase() === query)
                );

                if (exactMatch) {
                    this.addToCart(exactMatch);
                    this.playScanBeep();
                    this.searchQuery = ''; // reset after scan
                } else if (this.filteredProducts.length === 1) {
                    this.addToCart(this.filteredProducts[0]);
                    this.playScanBeep();
                    this.searchQuery = ''; // reset after scan
                } else {
                    alert('Produk dengan barcode/kata kunci "' + this.searchQuery + '" tidak ditemukan.');
                }
            },
            handleGlobalScan(barcode) {
                if (!barcode) return;
                const query = String(barcode).trim().toLowerCase();
                const exactMatch = this.products.find(p => 
                    (p.barcode && String(p.barcode).trim().toLowerCase() === query) ||
                    (p.sku && String(p.sku).trim().toLowerCase() === query)
                );
                
                if (exactMatch) {
                    this.addToCart(exactMatch);
                    this.playScanBeep();
                } else {
                    alert('Scan Gagal: Produk dengan barcode "' + barcode + '" tidak ditemukan di database.');
                }
            },
            playScanBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, ctx.currentTime); // 800Hz beep
                    gain.gain.setValueAtTime(0.1, ctx.currentTime); // low volume
                    osc.start();
                    osc.stop(ctx.currentTime + 0.1); // 100ms beep
                } catch(e) {}
            },
            removeFromCart(index) {
                const item = this.cart[index];
                // kembalikan stok tampilan sesuai jumlah yang ada di keranjang
                const productInList = this.products.find(p => p.id === item.product_id);
                if (productInList) {
                    productInList.stock += item.quantity;
                }
                this.cart.splice(index, 1);
                this.updatePaidAmount();
            },
            updateQty(index, change) {
                const item    = this.cart[index];
                const newQty  = item.quantity + change;
                if (newQty <= 0) {
                    this.removeFromCart(index);
                } else {
                    const productInList = this.products.find(p => p.id === item.product_id);
                    if (change > 0) {
                        // tambah qty → kurangi stok tampilan
                        if (productInList && productInList.stock <= 0) return; // tidak bisa tambah kalau stok habis
                        if (productInList) productInList.stock--;
                    } else {
                        // kurangi qty → kembalikan stok tampilan
                        if (productInList) productInList.stock++;
                    }
                    item.quantity = newQty;
                }
                this.updatePaidAmount();
            },
            updatePaidAmount() {
                this.checkoutForm.paid_amount = this.grandTotal;
            },

            /* ── voucher ── */
            async applyVoucher() {
                if (!this.voucherCodeInput) return;
                this.isLoadingVoucher = true;
                this.voucherMessage = '';
                this.voucherMessageSuccess = false;
                this.appliedVoucher = null;

                try {
                    const res = await fetch('{{ route('vouchers.check') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ code: this.voucherCodeInput, subtotal: this.subtotal })
                    });
                    const data = await res.json();
                    this.voucherMessage = data.message;
                    this.voucherMessageSuccess = data.success;
                    if (data.success) {
                        this.appliedVoucher = data.voucher;
                    }
                } catch (e) {
                    this.voucherMessage = 'Terjadi kesalahan saat memeriksa voucher.';
                    this.voucherMessageSuccess = false;
                } finally {
                    this.isLoadingVoucher = false;
                    this.updatePaidAmount();
                }
            },
            removeVoucher() {
                this.appliedVoucher = null;
                this.voucherCodeInput = '';
                this.voucherMessage = '';
                this.voucherMessageSuccess = false;
                this.updatePaidAmount();
            },

            /* ── helpers ── */
            formatRupiah(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
                }).format(amount || 0);
            },
            printReceipt(saleId) {
                if (!saleId) return;

                const url = '/transactions/' + saleId + '/receipt';
                const printWindow = window.open(url, '_blank', 'width=420,height=760,noopener,noreferrer');

                if (!printWindow) {
                    window.location.href = url;
                    return;
                }

                printWindow.focus();
            },

            /* ── open register ── */
            async openRegister() {
                this.isOpeningRegister = true;
                try {
                    const res = await fetch('{{ route('cash.open') }}', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            opening_balance: this.openRegisterForm.opening_balance
                        })
                    });
                    if (res.ok || res.redirected) {
                        window.location.reload();
                    } else {
                        const d = await res.json().catch(() => ({}));
                        alert(d.message || 'Gagal membuka kasir.');
                    }
                } catch { alert('Terjadi kesalahan jaringan.'); }
                finally  { this.isOpeningRegister = false; }
            },

            /* ── process payment ── */
            async processPayment() {
                if (this.checkoutForm.payment_method === 'cash' &&
                    Number(this.checkoutForm.paid_amount) < this.grandTotal) {
                    alert('Uang diterima kurang dari total tagihan!');
                    return;
                }
                this.isLoading = true;
                try {
                    const res = await fetch('{{ route('transactions.store') }}', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            items:          this.cart,
                            customer_id:    this.checkoutForm.customer_id || null,
                            payment_method: this.checkoutForm.payment_method,
                            paid_amount:    this.checkoutForm.payment_method === 'cash'
                                                ? Number(this.checkoutForm.paid_amount) // Use actual paid amount for cash
                                                : this.grandTotal, // For other methods, assume grandTotal is paid
                            voucher_code:   this.appliedVoucher ? this.appliedVoucher.code : null, // Pass voucher code
                            subtotal:       this.subtotal,
                            discount_total: this.totalDiscount, // Use totalDiscount
                            grand_total:    this.grandTotal,
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showCheckoutModal = false;
                        /* populate success modal */
                        this.successData = {
                            invoice:        data.invoice_number || 'INV-XXXXX',
                            sale_id:        data.sale_id,
                            grand_total:    this.grandTotal,
                            paid_amount:    Number(this.checkoutForm.paid_amount),
                            change:         Math.max(0, Number(this.checkoutForm.paid_amount) - this.grandTotal),
                            payment_method: this.checkoutForm.payment_method,
                        };
                        this.showSuccessModal = true;
                        // Update product stocks in the frontend before clearing the cart
                        this.cart.forEach(soldItem => {
                            const productInList = this.products.find(p => p.id === soldItem.product_id);
                            if (productInList) {
                                productInList.stock -= soldItem.quantity;
                                if (productInList.stock < 0) productInList.stock = 0; // Prevent negative stock in UI
                            }
                        });

                        /* reset cart and voucher */
                        this.cart     = [];
                        this.discount = 0; 
                        this.appliedVoucher = null;
                        this.voucherCodeInput = '';
                        this.voucherMessage = '';
                        this.voucherMessageSuccess = false;
                        this.checkoutForm.paid_amount = 0;

                    } else {
                        alert('❌ ' + (data.message || 'Terjadi kesalahan.'));
                    }
                } catch (e) {
                    console.error("Error processing payment:", e);
                    alert('Terjadi kesalahan jaringan atau server.');
                }
                finally  { this.isLoading = false; }
            },
        }));
    });
    </script>
    @endpush
</x-layouts.app>
