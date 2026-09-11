@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300 text-xs font-semibold leading-relaxed shadow-sm']) }}>
        <div class="h-5 w-5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center flex-shrink-0 mt-0.5 text-emerald-600 dark:text-emerald-400">
            <i data-lucide="check" class="w-3.5 h-3.5"></i>
        </div>
        <div class="flex-1">
            {{ $status }}
        </div>
    </div>
@endif
