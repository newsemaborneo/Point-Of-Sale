<x-layouts.app title="Point of Sale (POS)">

    <div
        id="pos-root"
        x-data="pos()"
        x-init="startClock(); watchFullscreen();"
        :class="isFullscreen ? 'fixed inset-0 z-[100] bg-slate-100' : 'h-[calc(100vh-6rem)] -m-8'"
        class="flex flex-col overflow-hidden"
    >
        {{-- ============================================================ --}}
        {{-- TOP BAR: clock, store status, fullscreen toggle --}}
        {{-- ============================================================ --}}
        <div class="bg-slate-900 text-white flex items-center justify-between px-4 py-1.5 shrink-0 z-40">
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

            {{-- Center: live clock --}}
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-mono font-bold tracking-widest" x-text="currentTime"></span>
                <span class="text-xs text-slate-400" x-text="currentDate"></span>
            </div>

            {{-- Right: fullscreen toggle --}}
            <button @click="toggleFullscreen()"
                    class="flex items-center gap-1.5 text-xs font-semibold text-slate-300 hover:text-white
                           bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-lg transition-all">
                {{-- Enter fullscreen icon --}}
                <template x-if="!isFullscreen">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15
                                 M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                    </svg>
                </template>
                {{-- Exit fullscreen icon --}}
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
        </div>


        <div class="relative flex flex-1 flex-col md:flex-row overflow-hidden">


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


        <div class="flex-[2] md:flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="bg-white border-b border-slate-200/60 shadow-sm z-10">
                <div class="px-4 py-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery"
                               placeholder="Cari produk atau scan barcode..."
                               @keydown.f2.prevent="$el.focus()"
                               class="block w-full pl-11 pr-4 py-2.5 bg-slate-50 border-0 text-slate-900
                                      rounded-xl ring-1 ring-inset ring-slate-200 focus:ring-2
                                      focus:ring-inset focus:ring-indigo-600 text-sm transition-all
                                      placeholder:text-slate-400">
                    </div>
                </div>

                {{-- Category filter chips: tampil di semua ukuran layar --}}
                <div class="flex items-center gap-2 overflow-x-auto px-4 pb-3 custom-scrollbar">
                    <button @click="activeCategory = null"
                            :class="activeCategory === null
                                ? 'bg-indigo-600 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                        Semua
                    </button>
                    @foreach($categories as $cat)
                    <button @click="activeCategory = {{ $cat->id }}"
                            :class="activeCategory === {{ $cat->id }}
                                ? 'bg-indigo-600 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Products grid --}}
            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-slate-50/50">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="group relative bg-white border border-slate-200 rounded-2xl
                                    overflow-hidden cursor-pointer hover:border-indigo-400
                                    hover:shadow-xl hover:shadow-indigo-500/10 transition-all
                                    duration-300 transform hover:-translate-y-1 flex flex-col
                                    select-none"
                             :class="product.stock <= 0 ? 'opacity-60 cursor-not-allowed' : ''">
                            <div class="aspect-square bg-slate-100 overflow-hidden relative">
                                <img :src="product.photo && !product.photo.startsWith('📦')
                                            ? '/storage/' + product.photo
                                            : 'https://placehold.co/200x200/e2e8f0/94a3b8?text=Produk'"
                                     :alt="product.name"
                                     class="w-full h-full object-cover transition-transform
                                            duration-500 group-hover:scale-110"> >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60
                                            via-transparent to-transparent opacity-0
                                            group-hover:opacity-100 transition-opacity
                                            flex items-end justify-center pb-3">
                                    <span class="bg-white/90 text-indigo-700 text-xs font-bold
                                                 px-3 py-1.5 rounded-lg shadow">+ Tambah</span>
                                </div>
                                <template x-if="product.stock <= 0">
                                    <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                                        <span class="bg-rose-100 text-rose-600 text-xs font-bold
                                                     px-2 py-1 rounded-md">Habis</span>
                                    </div>
                                </template>
                            </div>
                            <div class="p-2.5 flex flex-col flex-1">
                                <h3 class="text-xs font-bold text-slate-800 line-clamp-2
                                           leading-tight flex-1" x-text="product.name"></h3>
                                <div class="mt-1.5">
                                    <p class="text-[10px] font-medium text-slate-400">
                                        Stok: <span x-text="product.stock"></span>
                                    </p>
                                    <p class="text-sm font-black text-indigo-600"
                                       x-text="formatRupiah(product.price)"></p>
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
        <div class="flex-[1] md:flex-none w-full md:w-96 lg:w-[400px] bg-white
                    border-t md:border-t-0 md:border-l border-slate-200/80
                    shadow-2xl flex flex-col z-20 min-h-0">

            {{-- Cart header --}}
            <div class="px-4 py-3 border-b border-slate-100 flex items-center
                        justify-between bg-slate-50/50 shrink-0">
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
            <div class="flex-1 overflow-y-auto p-3 custom-scrollbar min-h-0">
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
                    <div class="p-3 bg-white border border-slate-100 rounded-xl
                                shadow-sm mb-2 hover:border-indigo-200 transition-colors">
                        <div class="flex justify-between items-start gap-2">
                            <h4 class="text-xs font-bold text-slate-800 leading-tight
                                       line-clamp-1 flex-1" x-text="item.name"></h4>
                            <button @click="removeFromCart(index)"
                                    class="text-slate-300 hover:text-rose-500
                                           transition-colors shrink-0">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
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
                        <div class="flex items-center justify-between mt-2">
                            <div class="font-black text-indigo-600 text-sm"
                                 x-text="formatRupiah(item.price * item.quantity)"></div>
                            <div class="flex items-center bg-slate-100 rounded-lg p-0.5">
                                <button @click="updateQty(index, -1)"
                                        class="w-7 h-7 flex items-center justify-center
                                               text-slate-600 hover:bg-white hover:shadow-sm
                                               rounded-md transition-all font-bold text-base">−</button>
                                <span class="w-8 text-center text-xs font-black text-slate-800"
                                      x-text="item.quantity"></span>
                                <button @click="updateQty(index, 1)"
                                        class="w-7 h-7 flex items-center justify-center
                                               text-slate-600 hover:bg-white hover:shadow-sm
                                               rounded-md transition-all font-bold text-base">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Checkout section --}}
            <div class="bg-white border-t border-slate-200 p-4 space-y-3
                        shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.08)] shrink-0">
                <div class="space-y-2">
                    {{-- Subtotal --}}
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-bold text-slate-700" x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Diskon</span>
                        <span class="font-bold text-emerald-600"
                              x-text="'- ' + formatRupiah(totalDiscount)"></span>
                    </div>
                    <div x-show="appliedVoucher" class="flex justify-between text-sm text-emerald-600">
                        <span class="text-slate-500">Voucher (<span x-text="appliedVoucher.code"></span>)</span>
                        <span class="font-bold" x-text="'- ' + formatRupiah(voucherDiscountAmount)"></span>
                    </div>
                    <div class="flex justify-between items-center pt-2
                                border-t border-dashed border-slate-200">
                        <span class="text-slate-800 font-bold">Total</span>
                        <span class="font-black text-2xl text-indigo-600"
                              x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>

                {{-- Voucher Input --}}
                <div class="border-t border-slate-200 pt-3">
                    <label for="voucher-code" class="block text-xs font-semibold text-slate-600 mb-1">Kode Voucher</label>
                    <div class="flex gap-2">
                        <input type="text" id="voucher-code" x-model="voucherCodeInput"
                               placeholder="Masukkan kode voucher"
                               class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm
                                      focus:ring-indigo-500 focus:border-indigo-500">
                        <button @click="applyVoucher()" :disabled="!voucherCodeInput || isLoadingVoucher"
                                class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white
                                       hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed">
                            <span x-show="!isLoadingVoucher">Terapkan</span>
                            <span x-show="isLoadingVoucher">Memeriksa...</span>
                        </button>
                    </div>
                    <p x-show="voucherMessage"
                       :class="voucherMessageSuccess ? 'text-emerald-600' : 'text-rose-600'"
                       class="text-xs mt-1" x-text="voucherMessage"></p>
                    <button x-show="appliedVoucher" @click="removeVoucher()"
                            class="text-xs text-rose-500 hover:text-rose-700 mt-1">Hapus Voucher</button>
                </div>
                <button @click="showCheckoutModal = true" :disabled="cart.length === 0 || !storeOpen"
                        class="w-full relative group overflow-hidden rounded-xl bg-slate-900
                               px-4 py-3.5 font-bold text-white shadow-lg transition-all
                               hover:scale-[1.02] active:scale-95 disabled:opacity-40
                               disabled:cursor-not-allowed disabled:hover:scale-100">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-indigo-600
                                opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    <div class="relative flex items-center justify-center gap-2 text-sm">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
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
             class="fixed inset-0 z-[110] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
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
                                              rounded-xl border-2 p-3 transition-all"
                                       :class="checkoutForm.payment_method === method.value
                                           ? 'border-indigo-500 bg-indigo-50'
                                           : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" x-model="checkoutForm.payment_method"
                                           :value="method.value" class="sr-only">
                                    <span class="text-2xl mb-1" x-text="method.icon"></span>
                                    <span class="text-xs font-bold text-slate-700"
                                          x-text="method.label"></span>
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
                                   rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold
                                   text-white hover:bg-indigo-700 disabled:opacity-60
                                   transition-all shadow-lg shadow-indigo-500/20">
                        <svg x-show="!isLoading" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
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
                        <a :href="'/transactions/' + successData.sale_id + '/receipt'"
                           target="_blank"
                           class="flex-1 rounded-xl border-2 border-slate-200 bg-white
                                  px-4 py-3 text-sm font-bold text-slate-700
                                  hover:bg-slate-50 transition-colors text-center">
                            Cetak Struk
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end pos-root --}}

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pos', () => ({

            /* ── data ── */
            products:     @json($productsJson),
            storeHours:   @json($storeHours),   // { open: "08:00", close: "21:00", enabled: true }
            searchQuery:  '',
            activeCategory: null,
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
                    const matchSearch = !this.searchQuery ||
                        p.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                    const matchCat = this.activeCategory === null ||
                        p.category_id === this.activeCategory;
                    return matchSearch && matchCat;
                });
            },
            get subtotal() {
                return this.cart.reduce((t, i) => t + i.price * i.quantity, 0);
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

                /* F11 shortcut */
                document.addEventListener('keydown', e => {
                    if (e.key === 'F11') { e.preventDefault(); this.toggleFullscreen(); }
                    if (e.key === 'F2')  { document.querySelector('[x-model="searchQuery"]')?.focus(); }
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
                    if (existing.quantity < product.stock) existing.quantity++;
                } else {
                    this.cart.push({
                        product_id:  product.id,
                        name:        product.name,
                        price:       product.price,
                        category_id: product.category_id,
                        quantity:    1,
                    });
                }
                this.updatePaidAmount();
            },
            removeFromCart(index) {
                this.cart.splice(index, 1);
                this.updatePaidAmount();
            },
            updateQty(index, change) {
                const item   = this.cart[index];
                const newQty = item.quantity + change;
                if (newQty <= 0) { this.removeFromCart(index); }
                else             { item.quantity = newQty; }
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
                        /* reset cart and voucher */
                        this.cart     = [];
                        this.discount = 0; // This `discount` variable is not used anymore, `totalDiscount` is. Can be removed.
                        this.appliedVoucher = null;
                        this.voucherCodeInput = '';
                        this.voucherMessage = '';
                        this.voucherMessageSuccess = false;
                        this.checkoutForm.paid_amount = 0;
                        // Update product stocks in the frontend
                        // Assuming the backend response includes updated stock for sold items
                        // This part needs to be implemented in the backend `TransactionController@store`
                        // to return `items_sold` with product_id and quantity.
                        // For now, let's simulate a stock update if the backend doesn't provide it.
                        // The backend `TransactionController@store` already decrements stock,
                        // but the frontend `products` array needs to be updated.
                        // The current `TransactionController@store` doesn't return `items_sold`.
                        // Let's assume for now that the `products` array will be reloaded on page refresh
                        // or a more sophisticated stock update mechanism is in place.
                        // For a quick fix, we can iterate through the cart and decrement stock.
                        this.cart.forEach(soldItem => {
                            const productInList = this.products.find(p => p.id === soldItem.product_id);
                            if (productInList) {
                                productInList.stock -= soldItem.quantity;
                                if (productInList.stock < 0) productInList.stock = 0; // Prevent negative stock in UI
                            }
                        });

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
