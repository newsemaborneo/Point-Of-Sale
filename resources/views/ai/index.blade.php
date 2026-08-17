<x-layouts.app title="AI Intelligence" :hideHeader="true" :noScroll="true" :noPadding="true">
    <div class="animate-fade-in-down flex-1 flex flex-col min-h-0 w-full h-full">

        {{-- Main layout: sidebar + chat --}}
        <div class="flex flex-1 min-h-0 overflow-hidden relative">

            {{-- ── SIDEBAR: Conversation History (Desktop only) ── --}}
            <aside id="chat-sidebar" class="hidden lg:flex flex-col w-64 shrink-0 border-r border-slate-700 bg-slate-900 min-h-0 absolute lg:relative z-40 h-full lg:h-auto transition-all">
                {{-- Sidebar header --}}
                <div class="px-4 pt-5 pb-3 border-b border-slate-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-7 rounded-lg bg-indigo-500 flex items-center justify-center">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.873l-1.16 3.2a.75.75 0 001.45.46l1.01-2.79m-1.3-1.87h.01m8.922-8.922A5.25 5.25 0 1017.7 17.7l-1.99 1.99a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 010-1.06l1.99-1.99A5.25 5.25 0 0017.7 6.95z"/></svg>
                            </div>
                            <span class="text-sm font-bold text-white">AI LAKUPOS</span>
                        </div>
                        <button id="sidebar-close-btn" class="lg:hidden p-1 text-slate-400 hover:text-white rounded-md focus:outline-none transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form action="{{ route('ai.conversations.new') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-sm font-semibold text-white transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Percakapan Baru
                        </button>
                    </form>
                </div>

                {{-- Conversation list --}}
                <div class="flex-1 min-h-0 overflow-y-auto py-2 custom-scrollbar">
                    @forelse($conversations as $conv)
                        <a href="{{ route('ai.index', ['conversation' => $conv->id]) }}"
                           class="group flex items-center justify-between gap-2 px-3 py-2 mx-2 rounded-lg text-sm transition-colors
                               {{ isset($activeConversation) && $activeConversation?->id === $conv->id
                                   ? 'bg-indigo-600 text-white'
                                   : 'text-slate-300 hover:bg-slate-700' }}">
                            <span class="truncate">{{ $conv->title }}</span>
                            <form id="delete-conv-form-{{ $conv->id }}" action="{{ route('ai.conversations.destroy', $conv->id) }}" method="POST" class="opacity-0 group-hover:opacity-100 shrink-0">
                                @csrf @method('DELETE')
                                <button type="button" onclick="openDeleteModal(event, 'delete-conv-form-{{ $conv->id }}')" class="p-1 rounded hover:text-red-400 focus:outline-none">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </a>
                    @empty
                        <p class="px-4 py-3 text-xs text-slate-500">Belum ada riwayat percakapan.</p>
                    @endforelse
                </div>
            </aside>

            {{-- ── Mobile Overlay ── --}}
            <div id="sidebar-overlay" class="hidden absolute inset-0 bg-slate-900/50 z-30 lg:hidden transition-opacity"></div>

            {{-- ── MAIN CHAT AREA ── --}}
            <div class="flex flex-col flex-1 min-h-0 min-w-0 bg-white">
                {{-- Chat Header --}}
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-4 py-3 sm:px-6 sm:py-4 text-white shrink-0">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none transition-colors" title="Kembali ke Dashboard">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </a>
                        <div class="relative hidden sm:block">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-inner">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                            </div>
                            <span class="absolute -bottom-1 -right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-slate-900">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            </span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold">AI LAKUPOS</h2>
                            <p class="text-[11px] sm:text-xs font-medium text-indigo-200">
                                {{ isset($activeConversation) && $activeConversation ? $activeConversation->title : 'Asisten Cerdas' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <button id="sidebar-toggle-btn" class="lg:hidden p-2 -mr-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none transition-colors" title="Menu Riwayat">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                    </div>
                </div>

                {{-- AI Alerts Banner --}}
                <div id="ai-alerts-area" class="hidden"></div>

                {{-- Chat Messages Area --}}
                <div id="chat-messages-container" class="flex-1 min-h-0 overflow-y-auto bg-slate-50/50 p-4 lg:p-6 custom-scrollbar">
                    <div id="chat-messages-inner" class="mx-auto max-w-3xl w-full space-y-5">
                        @foreach ($chatMessages as $message)
                            <div class="flex w-full {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                @if($message['role'] !== 'user')
                                    <div class="mr-3 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.873l-1.16 3.2a.75.75 0 001.45.46l1.01-2.79m-1.3-1.87h.01m8.922-8.922A5.25 5.25 0 1017.7 17.7l-1.99 1.99a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 010-1.06l1.99-1.99A5.25 5.25 0 0017.7 6.95z"/></svg>
                                    </div>
                                @endif
                                <div class="max-w-[80%] {{ $message['role'] === 'user' ? 'w-fit' : 'w-full' }}">
                                    <div class="rounded-2xl px-5 py-3.5 text-sm leading-relaxed shadow-sm
                                        {{ $message['role'] === 'user'
                                            ? 'bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-tr-sm'
                                            : 'bg-white text-slate-700 border border-slate-200 rounded-tl-sm' }}">
                                        {!! \App\Helpers\MarkdownHelper::parse($message['text']) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Chat Input --}}
                <div class="border-t border-slate-200 bg-white p-3 pb-8 sm:pb-3 lg:p-4">
                    <div class="mx-auto max-w-3xl w-full">
                        {{-- Autocomplete wrapper (position relative so dropdown can anchor) --}}
                        <div class="relative">
                            {{-- Autocomplete Dropdown (appears ABOVE input when typing) --}}
                            <div id="autocomplete-dropdown"
                                 class="absolute bottom-full mb-2 left-0 right-0 z-50
                                        hidden bg-white border border-slate-200 rounded-2xl
                                        shadow-2xl shadow-slate-200/80 overflow-hidden">
                                <div class="px-3 pt-2.5 pb-1 border-b border-slate-100">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">💡 Saran Pertanyaan</p>
                                </div>
                                <ul id="autocomplete-list" class="py-1 max-h-64 overflow-y-auto"></ul>
                            </div>

                            <div class="relative flex items-center rounded-2xl border border-slate-300 bg-white p-1.5 transition-all focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-500/20">
                                <input id="chat-input" type="text"
                                       placeholder="Ketik pertanyaan... (contoh: stok, omset, laba)"
                                       autocomplete="off"
                                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0" />
                                <button id="chat-send-btn" type="button" class="ml-2 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-5 w-5 translate-x-0.5 -translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Delete Conversation Modal ── --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xs mx-4 overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1.5">Hapus Percakapan?</h3>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">Apakah Anda yakin ingin menghapus riwayat percakapan ini? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex gap-2.5 justify-center">
                    <button type="button" onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 focus:ring-2 focus:ring-slate-300 transition-all w-1/2">Batal</button>
                    <button type="button" id="confirm-delete-btn" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-all shadow-md shadow-red-200 w-1/2">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        .markdown-body p { margin-bottom: 0.5rem; }
        .markdown-body p:last-child { margin-bottom: 0; }
        .markdown-body ul { list-style-type: disc; margin-left: 1.2rem; margin-bottom: 0.5rem; }
        .markdown-body ol { list-style-type: decimal; margin-left: 1.2rem; margin-bottom: 0.5rem; }
        .markdown-body li { margin-bottom: 0.2rem; }
        .markdown-body strong { font-weight: 600; color: inherit; }
        .markdown-body table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; font-size: 0.85rem; }
        .markdown-body th, .markdown-body td { border: 1px solid #e2e8f0; padding: 0.4rem 0.6rem; text-align: left; }
        .markdown-body th { background-color: #f8fafc; font-weight: 600; }
        .markdown-body h1, .markdown-body h2, .markdown-body h3 { font-weight: 700; margin-top: 1rem; margin-bottom: 0.5rem; }
        .markdown-body h3 { font-size: 1.05rem; }
        .msg-appear { animation: msg-fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(10px); }
        @keyframes msg-fade-in { to { opacity: 1; transform: translateY(0); } }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const input     = document.getElementById('chat-input');
        const sendBtn   = document.getElementById('chat-send-btn');
        const container = document.getElementById('chat-messages-container');
        const inner     = document.getElementById('chat-messages-inner');
        if (!input || !sendBtn || !container || !inner) return;

        let chartCounter   = 0;
        let conversationId = {{ isset($activeConversation) && $activeConversation ? $activeConversation->id : 'null' }};

        // ─── Scroll ─────────────────────────────────────────────────
        function scrollBottom() { container.scrollTop = container.scrollHeight; }
        scrollBottom();

        // ─── Delete Modal Logic ──────────────────────────────────────
        let formToSubmitId = null;
        const deleteModal = document.getElementById('delete-modal');
        const deleteModalContent = deleteModal ? deleteModal.querySelector('.bg-white') : null;

        window.openDeleteModal = function(event, formId) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            formToSubmitId = formId;
            if (deleteModal) {
                deleteModal.classList.remove('hidden');
                // trigger reflow
                void deleteModal.offsetWidth;
                deleteModal.classList.remove('opacity-0');
                if (deleteModalContent) {
                    deleteModalContent.classList.remove('scale-95');
                    deleteModalContent.classList.add('scale-100');
                }
            }
        };

        window.closeDeleteModal = function() {
            if (deleteModal) {
                deleteModal.classList.add('opacity-0');
                if (deleteModalContent) {
                    deleteModalContent.classList.remove('scale-100');
                    deleteModalContent.classList.add('scale-95');
                }
                setTimeout(() => {
                    deleteModal.classList.add('hidden');
                    formToSubmitId = null;
                }, 300);
            }
        };

        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (formToSubmitId) {
                    document.getElementById(formToSubmitId).submit();
                }
            });
        }

        // ─── Sidebar Toggle ──────────────────────────────────────────
        const sidebarBtn   = document.getElementById('sidebar-toggle-btn');
        const sidebarClose = document.getElementById('sidebar-close-btn');
        const sidebarEl    = document.getElementById('chat-sidebar');
        const overlayEl    = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            if (window.innerWidth >= 1024) { // lg
                sidebarEl.classList.toggle('lg:flex');
                sidebarEl.classList.toggle('lg:hidden');
            } else { // mobile
                sidebarEl.classList.toggle('hidden');
                sidebarEl.classList.toggle('flex');
                if (overlayEl) overlayEl.classList.toggle('hidden');
            }
        }

        if (sidebarBtn && sidebarEl) {
            sidebarBtn.addEventListener('click', toggleSidebar);
        }
        if (sidebarClose && sidebarEl) {
            sidebarClose.addEventListener('click', toggleSidebar);
        }
        if (overlayEl) {
            overlayEl.addEventListener('click', toggleSidebar);
        }

        // ─── Markdown parser (Smooth & Rapi) ──────────────────────────
        // marked.js di-load di head atau sebelum body tertutup
        function parseMarkdown(text) {
            text = text.replace(/<!--CHART:.*?-->/gs, '');
            if (typeof marked !== 'undefined') {
                return '<div class="markdown-body text-sm">' + marked.parse(text) + '</div>';
            }
            return text; // Fallback
        }

        // ─── Chart extraction & rendering ────────────────────────────
        function extractChart(text) {
            const match = text.match(/<!--CHART:(\{.*?\})-->/s);
            if (!match) return null;
            try { return JSON.parse(match[1]); } catch { return null; }
        }

        // ─── Action Cards parsing & rendering ─────────────────────────
        function extractActions(text) {
            const matches = [...text.matchAll(/\[ACTION:(.*?):(.*?):(.*?):(.*?)\]/g)];
            return matches.map(m => ({
                action: m[1],
                type: m[2],
                param: m[3],
                label: m[4]
            }));
        }

        function renderActionCards(actions) {
            const container = document.createElement('div');
            container.className = 'mt-3 flex flex-wrap gap-2';
            
            actions.forEach(act => {
                const btn = document.createElement('button');
                btn.className = 'inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3.5 py-2.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors shadow-sm';
                
                let icon = '⚡';
                if (act.action === 'po') icon = '📦';
                if (act.action === 'close_register') icon = '🏪';
                if (act.action === 'discount') icon = '🏷️';
                
                btn.innerHTML = `${icon} ${act.label}`;
                
                btn.addEventListener('click', () => {
                    if (act.action === 'po') {
                        const parts = act.param.split('=');
                        const prodId = parts[0] === 'product_id' ? parts[1] : '';
                        window.location.href = `/purchases/create?product_id=${prodId}`;
                    } else if (act.action === 'close_register') {
                        window.location.href = `/cash/shift`;
                    } else if (act.action === 'discount') {
                        window.location.href = `/promotions`;
                    }
                });
                
                container.appendChild(btn);
            });
            return container;
        }

        function renderChart(chartData) {
            const id = 'ai-chart-' + (++chartCounter);
            const isLine       = chartData.type === 'line';
            const isComparison = chartData.type === 'comparison';
            const isDanger     = chartData.color === 'danger';
            const isCurrency   = chartData.currency === true;

            const wrapper = document.createElement('div');
            wrapper.className = 'mt-3 p-4 bg-white border border-slate-200 rounded-xl shadow-sm';

            // Title and Toggle Buttons Header
            const headerEl = document.createElement('div');
            headerEl.className = 'flex items-center justify-between border-b border-slate-100 pb-2 mb-3';

            const titleEl = document.createElement('p');
            titleEl.className = 'text-xs font-bold text-slate-500 uppercase tracking-wider';
            titleEl.textContent = '📊 ' + (chartData.title || 'Grafik');
            headerEl.appendChild(titleEl);

            // Controls for switching chart type
            if (!isComparison) {
                const controls = document.createElement('div');
                controls.className = 'flex gap-1.5';
                
                ['bar', 'line', 'pie'].forEach(type => {
                    const btn = document.createElement('button');
                    btn.className = 'px-2 py-1 text-[10px] font-semibold rounded-md border transition-all ' +
                        ((isLine && type === 'line') || (!isLine && type === 'bar')
                            ? 'bg-indigo-50 border-indigo-200 text-indigo-700'
                            : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50');
                    btn.textContent = type.toUpperCase();
                    btn.addEventListener('click', () => {
                        // Switch active class
                        controls.querySelectorAll('button').forEach(b => {
                            b.className = 'px-2 py-1 text-[10px] font-semibold rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-all';
                        });
                        btn.className = 'px-2 py-1 text-[10px] font-semibold rounded-md border border-indigo-200 bg-indigo-50 text-indigo-700 transition-all';
                        rebuildChart(type);
                    });
                    controls.appendChild(btn);
                });
                headerEl.appendChild(controls);
            }
            wrapper.appendChild(headerEl);

            const canvasWrap = document.createElement('div');
            canvasWrap.style.cssText = 'position:relative;height:' + (isLine || isComparison ? '240' : '200') + 'px;width:100%';
            const canvas = document.createElement('canvas');
            canvas.id = id;
            canvasWrap.appendChild(canvas);
            wrapper.appendChild(canvasWrap);

            let chartInstance = null;
            const primaryColor = isDanger ? '239,68,68' : '99,102,241';

            function getDatasetsForType(type) {
                if (isComparison) {
                    return [
                        {
                            label: chartData.nameA,
                            data: chartData.dataA,
                            backgroundColor: 'rgba(99,102,241,0.7)',
                            borderColor: 'rgba(99,102,241,1)',
                            borderWidth: 2,
                            borderRadius: 4,
                        },
                        {
                            label: chartData.nameB,
                            data: chartData.dataB,
                            backgroundColor: 'rgba(16,185,129,0.7)',
                            borderColor: 'rgba(16,185,129,1)',
                            borderWidth: 2,
                            borderRadius: 4,
                        }
                    ];
                }

                if (type === 'line') {
                    return [{
                        label: chartData.title,
                        data: chartData.data,
                        borderColor: `rgba(${primaryColor},1)`,
                        borderWidth: 2.5,
                        pointBackgroundColor: `rgba(${primaryColor},1)`,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                        backgroundColor: (ctx) => {
                            const chart = ctx.chart;
                            const { ctx: c, chartArea } = chart;
                            if (!chartArea) return `rgba(${primaryColor},0.1)`;
                            const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            g.addColorStop(0, `rgba(${primaryColor},0.35)`);
                            g.addColorStop(1, `rgba(${primaryColor},0.02)`);
                            return g;
                        },
                    }];
                }

                // Bar or Pie
                const isPie = type === 'pie';
                const palette = isDanger
                    ? chartData.labels.map(() => `rgba(${primaryColor},0.75)`)
                    : isPie 
                        ? ['rgba(99,102,241,0.7)', 'rgba(139,92,246,0.7)', 'rgba(16,185,129,0.7)', 'rgba(245,158,11,0.7)', 'rgba(59,130,246,0.7)']
                        : chartData.labels.map(() => 'rgba(99,102,241,0.8)');

                return [{
                    label: chartData.title,
                    data: chartData.data,
                    backgroundColor: palette.slice(0, chartData.data.length),
                    borderColor: palette.map(c => c.replace('0.8', '1').replace('0.75', '1').replace('0.7', '1')).slice(0, chartData.data.length),
                    borderWidth: isPie ? 1 : 2,
                    borderRadius: isPie ? 0 : 6,
                }];
            }

            function rebuildChart(type) {
                if (chartInstance) {
                    chartInstance.destroy();
                }

                const options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: isComparison || type === 'pie', position: 'top', labels: { boxWidth: 12, font: { size: 10 } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.parsed.y ?? ctx.parsed;
                                    const lbl = isComparison ? (ctx.dataset.label + ': ') : ' ';
                                    return isCurrency
                                        ? lbl + 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                                        : lbl + val + ' ' + (chartData.unit || '');
                                }
                            }
                        }
                    },
                    scales: type === 'pie' ? {} : {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { size: 9 },
                                callback: v => isCurrency ? 'Rp ' + new Intl.NumberFormat('id-ID').format(v) : v
                            }
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0 } }
                    }
                };

                chartInstance = new Chart(canvas, {
                    type: type,
                    data: { labels: chartData.labels, datasets: getDatasetsForType(type) },
                    options: options
                });
            }

            setTimeout(() => {
                rebuildChart(isLine ? 'line' : 'bar');
            }, 50);

            return wrapper;
        }

        // ─── Append message ──────────────────────────────────────────
        function appendMessage(role, text) {
            const outerDiv = document.createElement('div');
            // Tambahkan msg-appear untuk animasi smooth (fade-in-up)
            outerDiv.className = 'msg-appear flex w-full ' + (role === 'user' ? 'justify-end' : 'justify-start');

            const iconHtml = role !== 'user' ? `
                <div class="mr-3 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.873l-1.16 3.2a.75.75 0 001.45.46l1.01-2.79m-1.3-1.87h.01m8.922-8.922A5.25 5.25 0 1017.7 17.7l-1.99 1.99a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 010-1.06l1.99-1.99A5.25 5.25 0 0017.7 6.95z"/></svg>
                </div>` : '';

            const bubbleClass = role === 'user'
                ? 'bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-tr-sm'
                : 'bg-white text-slate-700 border border-slate-200 rounded-tl-sm';

            const contentWrapper = document.createElement('div');
            contentWrapper.className = 'max-w-[80%] ' + (role === 'user' ? 'w-fit' : 'w-full');

            const bubble = document.createElement('div');
            bubble.className = `rounded-2xl px-5 py-3.5 text-sm leading-relaxed shadow-sm ${bubbleClass}`;
            contentWrapper.appendChild(bubble);

            outerDiv.innerHTML = iconHtml;
            outerDiv.appendChild(contentWrapper);
            inner.appendChild(outerDiv);
            scrollBottom();

            const chartData = role !== 'user' ? extractChart(text) : null;
            const actions   = role !== 'user' ? extractActions(text) : [];

            if (role === 'assistant') {
                // Typewriter/streaming typing animation for assistant replies
                let idx = 0;
                // Strip chart meta comments & ACTION tags so it doesn't print raw text during typing
                const cleanText = text.replace(/<!--CHART:.*?-->/gs, '').replace(/\[ACTION:.*?\]/gs, '');
                
                // Typing speed: faster for longer texts so user doesn't wait indefinitely
                const speed = cleanText.length > 500 ? 5 : (cleanText.length > 200 ? 10 : 15);
                
                function typeNext() {
                    if (idx < cleanText.length) {
                        idx += 2;
                        bubble.innerHTML = parseMarkdown(cleanText.substring(0, idx));
                        scrollBottom();
                        setTimeout(typeNext, speed);
                    } else {
                        bubble.innerHTML = parseMarkdown(cleanText);
                        if (actions.length > 0) {
                            contentWrapper.appendChild(renderActionCards(actions));
                        }
                        if (chartData) {
                            contentWrapper.appendChild(renderChart(chartData));
                        }
                        scrollBottom();
                    }
                }
                typeNext();
            } else {
                bubble.innerHTML = parseMarkdown(text.replace(/\[ACTION:.*?\]/gs, ''));
                if (actions.length > 0) {
                    contentWrapper.appendChild(renderActionCards(actions));
                }
                if (chartData) {
                    contentWrapper.appendChild(renderChart(chartData));
                }
                scrollBottom();
            }
        }

        // ─── Send message ────────────────────────────────────────────
        function sendMessage() {
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            appendMessage('user', text);

            // Typing indicator
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typing-indicator';
            typingDiv.className = 'flex w-full justify-start items-center';
            typingDiv.innerHTML = `
                <div class="mr-3 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.873l-1.16 3.2a.75.75 0 001.45.46l1.01-2.79m-1.3-1.87h.01m8.922-8.922A5.25 5.25 0 1017.7 17.7l-1.99 1.99a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 010-1.06l1.99-1.99A5.25 5.25 0 0017.7 6.95z"/></svg>
                </div>
                <div class="max-w-[80%] rounded-2xl px-5 py-3.5 text-sm bg-white border border-slate-200 rounded-tl-sm flex items-center gap-1 text-slate-400">
                    <span class="animate-bounce" style="animation-delay:.1s">•</span>
                    <span class="animate-bounce" style="animation-delay:.2s">•</span>
                    <span class="animate-bounce" style="animation-delay:.3s">•</span>
                </div>`;
            inner.appendChild(typingDiv);
            scrollBottom();

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch("{{ route('ai.chat') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ message: text, conversation_id: conversationId })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('typing-indicator')?.remove();
                if (data.conversation_id && !conversationId) {
                    conversationId = data.conversation_id;
                    // Update URL without reload
                    history.replaceState(null, '', '?conversation=' + conversationId);
                }
                appendMessage('assistant', data.text || 'Maaf, terjadi kesalahan.');
            })
            .catch(() => {
                document.getElementById('typing-indicator')?.remove();
                appendMessage('assistant', 'Maaf, terjadi gangguan jaringan. Silakan coba kembali.');
            });
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });

        // ─── Typeahead / Autocomplete ─────────────────────────────────
        const dropdown     = document.getElementById('autocomplete-dropdown');
        const listEl       = document.getElementById('autocomplete-list');
        let activeIndex    = -1;
        let mouseInList    = false;

        const suggestions = [
            @foreach($contextSuggestions as $sug)
                { icon: '{{ $sug['icon'] }}', label: '{{ $sug['label'] }}', category: '{{ $sug['category'] }}', priority: true },
            @endforeach
            // Penjualan
            { icon: '📈', label: 'Berapa omset hari ini?',                                         category: 'Penjualan' },
            { icon: '🏆', label: 'Produk apa yang paling laris bulan ini?',                        category: 'Penjualan' },
            { icon: '📅', label: 'Bandingkan penjualan bulan ini dengan bulan lalu',               category: 'Penjualan' },
            { icon: '⏰', label: 'Jam berapa yang paling sibuk hari ini?',                          category: 'Penjualan' },
            { icon: '👥', label: 'Siapa pelanggan yang paling banyak berbelanja?',                 category: 'Penjualan' },
            { icon: '📊', label: 'Berapa total transaksi bulan ini?',                              category: 'Penjualan' },
            { icon: '🔄', label: 'Berapa rata-rata nilai transaksi bulan ini?',                    category: 'Penjualan' },
            // Stok
            { icon: '⚠️', label: 'Produk mana yang stoknya kritis atau hampir habis?',            category: 'Stok' },
            { icon: '📦', label: 'Produk apa yang tidak laku 30 hari terakhir (dead stock)?',      category: 'Stok' },
            { icon: '🏭', label: 'Berapa total produk aktif saat ini?',                            category: 'Stok' },
            { icon: '🔍', label: 'Berapa stok produk tertentu saat ini?',                          category: 'Stok' },
            // Keuangan
            { icon: '💰', label: 'Berapa laba kotor bulan ini?',                                   category: 'Keuangan' },
            { icon: '📉', label: 'Berapa total HPP (Harga Pokok Penjualan) bulan ini?',            category: 'Keuangan' },
            { icon: '🏦', label: 'Berapa total utang toko ke supplier?',                           category: 'Keuangan' },
            { icon: '💳', label: 'Metode pembayaran apa yang paling sering digunakan?',            category: 'Keuangan' },
            { icon: '🧾', label: 'Berapa margin keuntungan bulan ini?',                            category: 'Keuangan' },
            { icon: '🏪', label: 'Apakah kasir sudah buka atau tutup shift hari ini?',             category: 'Keuangan' },
            // Prediksi
            { icon: '🔮', label: 'Prediksi omset bulan depan berdasarkan tren 3 bulan terakhir',  category: 'Prediksi' },
            { icon: '📉', label: 'Produk mana yang stoknya diprediksi akan habis paling cepat?',  category: 'Prediksi' },
            { icon: '🛍️', label: 'Berikan rekomendasi bundling produk yang sering dibeli bersama', category: 'Prediksi' },
            { icon: '📆', label: 'Apa tren penjualan dalam 3 bulan terakhir?',                    category: 'Prediksi' },
            // Lainnya (Cabang, Promo, Pembelian, Panduan)
            { icon: '🏢', label: 'Bagaimana performa penjualan per cabang hari ini?',             category: 'Lainnya' },
            { icon: '🎫', label: 'Berapa banyak promo atau voucher yang aktif?',                  category: 'Lainnya' },
            { icon: '🚚', label: 'Tampilkan 5 transaksi pembelian (PO) terbaru ke supplier',      category: 'Lainnya' },
            { icon: '🖨️', label: 'Bagaimana cara setup printer kasir thermal?',                   category: 'Panduan' },
            { icon: '↩️', label: 'Apa kebijakan retur barang dari pelanggan?',                    category: 'Panduan' },
        ];

        const categoryColors = {
            'Penjualan': 'text-indigo-600 bg-indigo-50',
            'Stok':      'text-amber-600 bg-amber-50',
            'Keuangan':  'text-emerald-600 bg-emerald-50',
            'Prediksi':  'text-purple-600 bg-purple-50',
            'Lainnya':   'text-slate-600 bg-slate-100',
            'Panduan':   'text-cyan-600 bg-cyan-50',
        };

        function highlightMatch(text, query) {
            if (!query) return text;
            const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return text.replace(new RegExp('(' + escaped + ')', 'gi'), '<mark class="bg-indigo-100 text-indigo-800 rounded px-0.5">$1</mark>');
        }

        function showDropdown(filtered) {
            listEl.innerHTML = '';
            activeIndex = -1;
            if (!filtered.length) { dropdown.classList.add('hidden'); return; }

            filtered.forEach((item, idx) => {
                const li = document.createElement('li');
                const catClass = categoryColors[item.category] || 'text-slate-600 bg-slate-50';
                li.dataset.index = idx;
                li.className = 'flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-slate-50 transition-colors group';
                li.innerHTML = `
                    <span class="text-base flex-shrink-0">${item.icon}</span>
                    <span class="flex-1 text-sm text-slate-700 group-hover:text-slate-900">${highlightMatch(item.label, input.value.trim())}</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${catClass} flex-shrink-0">${item.category}</span>`;
                li.addEventListener('mouseenter', () => { setActive(idx); });
                li.addEventListener('mouseleave', () => { /* keep active */ });
                li.addEventListener('mousedown', (e) => {
                    e.preventDefault(); // prevent blur from firing before click
                    input.value = item.label;
                    closeDropdown();
                    sendMessage();
                });
                listEl.appendChild(li);
            });
            dropdown.classList.remove('hidden');
        }

        function closeDropdown() {
            dropdown.classList.add('hidden');
            activeIndex = -1;
        }

        function setActive(idx) {
            const items = listEl.querySelectorAll('li');
            items.forEach(li => li.classList.remove('bg-indigo-50'));
            activeIndex = idx;
            if (idx >= 0 && idx < items.length) {
                items[idx].classList.add('bg-indigo-50');
                items[idx].scrollIntoView({ block: 'nearest' });
            }
        }

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            if (!q) { closeDropdown(); return; }
            const filtered = suggestions.filter(s =>
                s.label.toLowerCase().includes(q) ||
                s.category.toLowerCase().includes(q)
            ).slice(0, 8);
            showDropdown(filtered);
        });

        input.addEventListener('keydown', e => {
            const items = listEl.querySelectorAll('li');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Escape') {
                closeDropdown();
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    input.value = suggestions.filter(s => {
                        const q = input.value.trim().toLowerCase();
                        return s.label.toLowerCase().includes(q) || s.category.toLowerCase().includes(q);
                    })[activeIndex]?.label || input.value;
                    closeDropdown();
                }
                sendMessage();
            }
        });

        input.addEventListener('blur', () => {
            // delay so mousedown on item fires first
            setTimeout(closeDropdown, 150);
        });
        // ─────────────────────────────────────────────────────────────

        // ─── Proactive AI Alerts ─────────────────────────────────────
        function loadAlerts() {
            fetch("{{ route('ai.alerts') }}", { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    const area = document.getElementById('ai-alerts-area');
                    if (!data.alerts?.length) { area.classList.add('hidden'); return; }
                    area.classList.remove('hidden');
                    area.innerHTML = data.alerts.map(a => {
                        const colors = {
                            warning: 'bg-amber-50 border-amber-200 text-amber-800',
                            danger:  'bg-red-50 border-red-200 text-red-800',
                            info:    'bg-blue-50 border-blue-200 text-blue-800',
                        };
                        return `<div class="flex items-start gap-3 px-5 py-3 border-b ${colors[a.type] || colors.info}">
                            <span class="text-lg">${a.icon}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold">${a.title}</p>
                                <p class="text-xs opacity-80 truncate">${a.message}</p>
                            </div>
                        </div>`;
                    }).join('');
                })
                .catch(() => {});
        }

        loadAlerts();
        setInterval(loadAlerts, 300000); // refresh every 5 minutes
    });
    </script>
    @endpush
</x-layouts.app>
