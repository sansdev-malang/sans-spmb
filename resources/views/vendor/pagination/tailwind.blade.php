@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 select-none">

        {{-- Info Counter --}}
        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium order-2 sm:order-1">
            Menampilkan
            <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->firstItem() }}</span>
            &ndash;
            <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->total() }}</span>
            data
        </div>

        {{-- Floating Pill Nav --}}
        <nav class="inline-flex items-stretch order-1 sm:order-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden divide-x divide-slate-200 dark:divide-slate-700" aria-label="Navigasi Halaman">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-400 dark:text-slate-600 cursor-not-allowed whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-brand-emerald dark:hover:text-emerald-400 transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Page Numbers (Desktop) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden sm:inline-flex items-center justify-center w-10 py-2 text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="hidden sm:inline-flex items-center justify-center w-10 py-2 text-xs font-bold bg-brand-emerald text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="hidden sm:inline-flex items-center justify-center w-10 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-brand-emerald dark:hover:text-emerald-400 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Current Page Badge (Mobile only) --}}
            <span class="sm:hidden inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-brand-emerald whitespace-nowrap">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-brand-emerald dark:hover:text-emerald-400 transition-colors whitespace-nowrap">
                    <span class="hidden sm:inline">Selanjutnya</span>
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-400 dark:text-slate-600 cursor-not-allowed whitespace-nowrap">
                    <span class="hidden sm:inline">Selanjutnya</span>
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif

        </nav>
    </div>

@elseif ($paginator->total() > 0)
    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
        Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->total() }}</span> data (semua ditampilkan)
    </div>
@endif
