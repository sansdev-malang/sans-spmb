<div class="px-4 py-2.5 border-b border-slate-100 font-bold text-slate-800 flex justify-between items-center bg-slate-50/50 rounded-t-2xl">
    <span>Notifikasi Masuk</span>
    @if($unreadCount > 0)
        <button hx-post="{{ route('admin.notifications.mark-all-read') }}" hx-target="#notifDropdown" hx-swap="innerHTML" class="text-[10px] text-brand-emerald hover:underline font-bold transition">Tandai Semua Dibaca</button>
    @endif
</div>
<div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
    @forelse($notifications as $n)
        @php
            $data = $n->data;
            $bgClass = $n->read_at ? 'bg-white' : 'bg-emerald-50/20';
        @endphp
        <a href="{{ route('admin.notifications.read-redirect', $n->id) }}" class="block px-4 py-3 hover:bg-slate-50 transition {{ $bgClass }}">
            <div class="font-bold text-slate-800 flex justify-between items-center gap-2">
                <span class="truncate max-w-[180px]">{{ $data['title'] ?? 'Notifikasi' }}</span>
                @if(!$n->read_at)
                    <span class="h-1.5 w-1.5 bg-brand-emerald rounded-full shrink-0"></span>
                @endif
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 line-clamp-2">{{ $data['message'] ?? '' }}</div>
            <div class="text-[9px] text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
        </a>
    @empty
        <div class="px-4 py-8 text-center text-slate-400">
            <i data-lucide="bell-off" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
            <p class="text-[10px] font-medium">Tidak ada notifikasi baru.</p>
        </div>
    @endforelse
</div>
<div class="px-4 py-2 border-t border-slate-100 text-center bg-slate-50/50 rounded-b-2xl">
    <a href="{{ route('admin.verification') }}" class="text-[10px] text-brand-emerald font-bold hover:underline">Lihat Semua Aktivitas</a>
</div>
