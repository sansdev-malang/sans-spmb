@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 select-none">

        {{-- Info Counter --}}
        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium order-2 sm:order-1 text-center sm:text-left">
            Menampilkan
            <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->firstItem() }}</span>
            &ndash;
            <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->total() }}</span>
            data
        </div>

        {{-- Simple Pagination Navigation --}}
        <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center gap-3.5 order-1 sm:order-2">

            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-xs font-bold text-slate-400 dark:text-slate-600 bg-white/60 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-lg cursor-not-allowed select-none">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xs hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-brand-emerald dark:hover:text-emerald-400 hover:border-slate-300 dark:hover:border-slate-600 transition">
                    Sebelumnya
                </a>
            @endif

            {{-- Info Halaman --}}
            <span class="text-xs sm:text-xs text-slate-700 dark:text-slate-300 font-bold px-1 select-none">
                Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xs hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-brand-emerald dark:hover:text-emerald-400 hover:border-slate-300 dark:hover:border-slate-600 transition">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-xs font-bold text-slate-400 dark:text-slate-600 bg-white/60 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-lg cursor-not-allowed select-none">
                    Berikutnya
                </span>
            @endif

        </nav>
    </div>

@elseif ($paginator->total() > 0)
    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
        Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ $paginator->total() }}</span> data (semua ditampilkan)
    </div>
@endif
