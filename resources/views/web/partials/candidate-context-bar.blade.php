@php
    $userAllRegs = auth()->check() ? auth()->user()->registrations()->with(['unit', 'grade', 'classProgram'])->where('registration_status', '!=', 'draft')->orWhereHas('payments', function($q) { $q->where('payment_type', 'registration_fee')->where('status', 'success'); })->latest()->get() : collect();
    $otherRegs = $userAllRegs->where('id', '!=', $registration->id);
@endphp

@if($registration)
    <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 animate-fade-in">
        <!-- Current Active Candidate Info -->
        <div class="flex items-center gap-3 min-w-0">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-brand-emerald to-emerald-500 text-white font-black text-sm flex items-center justify-center shadow-md shadow-emerald-500/20 flex-shrink-0">
                {{ substr($registration->candidate_name ?? 'A', 0, 1) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sedang Mengelola:</span>
                    <span class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm truncate">{{ $registration->candidate_name }}</span>
                    <span class="text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">#{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <span class="font-semibold text-brand-emerald dark:text-emerald-400">{{ $registration->unit?->name }}</span>
                    <span>•</span>
                    <span>{{ $registration->grade?->name }} ({{ $registration->classProgram?->name ?? 'Reguler' }})</span>
                </div>
            </div>
        </div>

        <!-- Switch to other children (if multiple exist) -->
        @if($otherRegs->isNotEmpty())
            <div class="flex items-center gap-2 flex-wrap border-t md:border-t-0 pt-2.5 md:pt-0 border-slate-150 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Beralih Ananda:</span>
                <div class="flex items-center gap-1.5 flex-wrap">
                    @foreach($otherRegs as $other)
                        @php
                            $targetStageUrl = route('dashboard.detail', $other->id);
                            if (Route::is('dashboard.form')) {
                                $targetStageUrl = route('dashboard.form', $other->id);
                            } elseif (Route::is('dashboard.verification')) {
                                $targetStageUrl = route('dashboard.verification', $other->id);
                            } elseif (Route::is('dashboard.observation')) {
                                $targetStageUrl = route('dashboard.observation', $other->id);
                            } elseif (Route::is('dashboard.result') || Route::is('dashboard.payment')) {
                                $targetStageUrl = route('dashboard.result', $other->id);
                            }
                        @endphp
                        <a href="{{ $targetStageUrl }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-brand-emerald dark:bg-slate-800 dark:hover:bg-slate-750 dark:text-slate-200 text-xs font-bold transition border border-slate-200 dark:border-slate-700 shadow-sm"
                           title="Kelola data {{ $other->candidate_name }}">
                            <span>👦 {{ $other->candidate_name }}</span>
                            <span class="text-[9px] px-1 py-0.2 bg-white dark:bg-slate-900 rounded text-slate-500 font-extrabold">{{ $other->unit?->code }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
