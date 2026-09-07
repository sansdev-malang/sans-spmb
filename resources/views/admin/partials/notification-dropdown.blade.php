<div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 font-bold text-slate-800 dark:text-white flex justify-between items-center bg-slate-50/50 dark:bg-slate-850/50 rounded-t-2xl">
    <div class="flex items-center gap-2">
        <i data-lucide="bell" class="w-4 h-4 text-brand-emerald"></i>
        <span class="text-xs">Notifikasi Masuk</span>
    </div>
    @if($unreadCount > 0)
        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-emerald/10 text-brand-emerald dark:bg-emerald-950/60 dark:text-emerald-400">
            {{ $unreadCount }} Baru
        </span>
    @endif
</div>
<div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
    @forelse($notifications as $n)
        @php
            $data = $n->data;
            $bgClass = $n->read_at ? 'bg-white dark:bg-slate-900' : 'bg-emerald-50/30 dark:bg-emerald-950/20';
            $redirectUrl = auth()->user()->isAdmin() 
                ? route('admin.notifications.read-redirect', $n->id) 
                : route('dashboard.notifications.read-redirect', $n->id);
            $isRevision = ($data['type'] ?? '') === 'warning' || str_contains(strtolower($data['title'] ?? ''), 'perbaikan') || str_contains(strtolower($data['title'] ?? ''), 'revisi');
            $isNew = str_contains(strtolower($data['title'] ?? ''), 'baru');
        @endphp
        <a href="{{ $redirectUrl }}" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition {{ $bgClass }}">
            <div class="font-bold text-slate-800 dark:text-slate-100 flex justify-between items-center gap-2">
                <div class="flex items-center gap-1.5 min-w-0">
                    @if($isRevision)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shrink-0">
                            Revisi
                        </span>
                    @elseif($isNew)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shrink-0">
                            Baru
                        </span>
                    @endif
                    <span class="truncate text-xs">{{ $data['title'] ?? 'Notifikasi' }}</span>
                </div>
                @if(!$n->read_at)
                    <span class="h-2 w-2 {{ $isRevision ? 'bg-amber-500' : 'bg-brand-emerald' }} rounded-full shrink-0 shadow-xs"></span>
                @endif
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">{{ $data['message'] ?? '' }}</div>
            <div class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 font-medium">{{ $n->created_at->diffForHumans() }}</div>
        </a>
    @empty
        <div class="px-4 py-8 text-center text-slate-400 dark:text-slate-500">
            <i data-lucide="bell-off" class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2"></i>
            <p class="text-[10px] font-medium">Tidak ada notifikasi baru.</p>
        </div>
    @endforelse
</div>
@if($unreadCount > 0)
    <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800 text-center bg-slate-50/50 dark:bg-slate-850/50 rounded-b-2xl">
        <button onclick="markAllNotificationsAsRead(event)" class="text-xs text-brand-emerald dark:text-emerald-400 hover:underline font-bold transition inline-flex items-center justify-center gap-1.5 cursor-pointer w-full py-0.5">
            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
            <span>Tandai Semua Dibaca</span>
        </button>
    </div>
@endif
