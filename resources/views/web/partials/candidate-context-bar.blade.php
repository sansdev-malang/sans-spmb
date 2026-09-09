@if($registration)
    <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 animate-fade-in">
        <!-- Current Active Candidate Info -->
        <div class="flex items-center gap-3 min-w-0">
            <div class="h-10 w-10 rounded-2xl bg-emerald-600 dark:bg-emerald-500 text-white font-black text-sm flex items-center justify-center shadow-md shadow-emerald-600/20 flex-shrink-0">
                {{ strtoupper(substr(trim($registration->candidate_name ?? 'A'), 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sedang Mengelola:</span>
                    <span class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm truncate">{{ $registration->candidate_name }}</span>
                    <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center gap-1">
                        <i data-lucide="tag" class="w-3 h-3 text-emerald-600 dark:text-emerald-400"></i> {{ $registration->id_label }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <span class="font-semibold text-brand-emerald dark:text-emerald-400">{{ $registration->unit?->name }}</span>
                    <span>•</span>
                    <span>{{ $registration->grade?->name }} ({{ $registration->classProgram?->name ?? 'Reguler' }})</span>
                </div>
            </div>
        </div>
    </div>
@endif
