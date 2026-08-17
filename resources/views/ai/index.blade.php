<x-layouts.app title="AI Intelligence" :hideHeader="true" :noScroll="true" :noPadding="true">
    <div class="animate-fade-in-down flex-1 flex flex-col min-h-0 w-full h-full">

        {{-- Main layout: sidebar + chat --}}
        <div class="flex flex-1 min-h-0 overflow-hidden">

            {{-- ── SIDEBAR: Conversation History (Desktop only) ── --}}
            <aside class="hidden lg:flex flex-col w-64 shrink-0 border-r border-slate-200 bg-slate-900 min-h-0">
                {{-- Sidebar header --}}
                <div class="px-4 pt-5 pb-3 border-b border-slate-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-7 w-7 rounded-lg bg-indigo-500 flex items-center justify-center">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.873l-1.16 3.2a.75.75 0 001.45.46l1.01-2.79m-1.3-1.87h.01m8.922-8.922A5.25 5.25 0 1017.7 17.7l-1.99 1.99a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 010-1.06l1.99-1.99A5.25 5.25 0 0017.7 6.95z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-white">AI LAKUPOS</span>
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
                            <form action="{{ route('ai.conversations.destroy', $conv->id) }}" method="POST" class="opacity-0 group-hover:opacity-100 shrink-0">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus percakapan ini?')" class="p-1 rounded hover:text-red-400">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </a>
                    @empty
                        <p class="px-4 py-3 text-xs text-slate-500">Belum ada riwayat percakapan.</p>
                    @endforelse
                </div>
            </aside>

            {{-- ── MAIN CHAT AREA ── --}}
            <div class="flex flex-col flex-1 min-h-0 min-w-0 bg-white">
                {{-- Chat Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-900 to-slate-800 px-4 sm:px-6 pt-10 sm:pt-4 pb-4 text-white">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-inner">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                            </div>
                            <span class="absolute -bottom-1 -right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-slate-900">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            </span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold">Ask AI Assistant</h2>
                            <p class="text-[11px] font-medium text-slate-300">
                                {{ isset($activeConversation) ? $activeConversation->title : 'Percakapan Baru' }}
                            </p>
                        </div>
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
                        <div class="relative flex items-center rounded-2xl border border-slate-300 bg-white p-1.5 transition-all focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-500/20">
                            <input id="chat-input" type="text" placeholder="Tanya AI tentang performa bisnis, atau bandingkan Juli vs Agustus..." class="w-full border-0 bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0" />
                            <button id="chat-send-btn" type="button" class="ml-2 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="h-5 w-5 translate-x-0.5 -translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                            </button>
                        </div>
                        <p class="mt-2 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            💡 Coba: "bandingkan penjualan Juli vs Agustus" · "stok kritis" · "jam sibuk hari ini"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        // ─── Markdown parser ─────────────────────────────────────────
        function parseMarkdown(text) {
            text = text.replace(/<!--CHART:.*?-->/gs, '');
            text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            text = text.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
            text = text.replace(/`(.+?)`/g, '<code class="bg-slate-100 text-indigo-600 px-1 rounded text-xs">$1</code>');
            text = text.replace(/^&gt; (.+)$/gm, '<div class="border-l-4 border-indigo-300 pl-3 text-slate-500 text-xs my-1">$1</div>');
            text = text.replace(/^- (.+)$/gm, '<div class="flex gap-2 my-0.5"><span class="text-indigo-400 mt-0.5">▪</span><span>$1</span></div>');
            text = text.replace(/^\d+\. (.+)$/gm, '<div class="flex gap-2 my-0.5"><span class="text-indigo-400 font-bold mt-0.5">•</span><span>$1</span></div>');
            text = text.replace(/\n/g, '<br>');
            return text;
        }

        // ─── Chart extraction & rendering ────────────────────────────
        function extractChart(text) {
            const match = text.match(/<!--CHART:(\{.*?\})-->/s);
            if (!match) return null;
            try { return JSON.parse(match[1]); } catch { return null; }
        }

        function renderChart(chartData) {
            const id = 'ai-chart-' + (++chartCounter);
            const isLine       = chartData.type === 'line';
            const isComparison = chartData.type === 'comparison';
            const isDanger     = chartData.color === 'danger';
            const isCurrency   = chartData.currency === true;

            const wrapper = document.createElement('div');
            wrapper.className = 'mt-3 p-4 bg-white border border-slate-200 rounded-xl shadow-sm';

            const titleEl = document.createElement('p');
            titleEl.className = 'text-xs font-bold text-slate-500 uppercase tracking-wider mb-3';
            titleEl.textContent = '📊 ' + (chartData.title || 'Grafik');
            wrapper.appendChild(titleEl);

            const canvasWrap = document.createElement('div');
            canvasWrap.style.cssText = 'position:relative;height:' + (isLine || isComparison ? '240' : '200') + 'px;width:100%';
            const canvas = document.createElement('canvas');
            canvas.id = id;
            canvasWrap.appendChild(canvas);
            wrapper.appendChild(canvasWrap);

            const primaryColor = isDanger ? '239,68,68' : '99,102,241';

            let datasets;
            if (isComparison) {
                datasets = [
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
            } else if (isLine) {
                datasets = [{
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
                        const { ctx: c, chartArea } = ctx.chart;
                        if (!chartArea) return `rgba(${primaryColor},0.1)`;
                        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, `rgba(${primaryColor},0.35)`);
                        g.addColorStop(1, `rgba(${primaryColor},0.02)`);
                        return g;
                    },
                }];
            } else {
                const palette = isDanger
                    ? chartData.labels.map(() => `rgba(${primaryColor},0.75)`)
                    : ['rgba(99,102,241,0.8)','rgba(139,92,246,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(59,130,246,0.8)'];
                datasets = [{
                    label: chartData.title,
                    data: chartData.data,
                    backgroundColor: palette.slice(0, chartData.data.length),
                    borderColor: palette.map(c => c.replace('0.8', '1').replace('0.75', '1')).slice(0, chartData.data.length),
                    borderWidth: 2,
                    borderRadius: 6,
                }];
            }

            setTimeout(() => {
                new Chart(canvas, {
                    type: (isLine) ? 'line' : 'bar',
                    data: { labels: chartData.labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: isComparison, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const val = ctx.parsed.y;
                                        const lbl = isComparison ? (ctx.dataset.label + ': ') : ' ';
                                        return isCurrency
                                            ? lbl + 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                                            : lbl + val + ' ' + (chartData.unit || '');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.04)' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: v => isCurrency ? 'Rp ' + new Intl.NumberFormat('id-ID').format(v) : v
                                }
                            },
                            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } }
                        }
                    }
                });
            }, 50);

            return wrapper;
        }

        // ─── Append message ──────────────────────────────────────────
        function appendMessage(role, text) {
            const outerDiv = document.createElement('div');
            outerDiv.className = 'flex w-full ' + (role === 'user' ? 'justify-end' : 'justify-start');

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
            bubble.innerHTML = parseMarkdown(text);
            contentWrapper.appendChild(bubble);

            if (role !== 'user') {
                const chartData = extractChart(text);
                if (chartData) contentWrapper.appendChild(renderChart(chartData));
            }

            outerDiv.innerHTML = iconHtml;
            outerDiv.appendChild(contentWrapper);
            inner.appendChild(outerDiv);
            scrollBottom();
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
