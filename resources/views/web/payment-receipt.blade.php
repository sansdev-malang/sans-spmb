<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran #{{ $payment->invoice_number }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            emerald: '#059669',
                            yellow: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
            .print-border {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen py-10 antialiased">

    <div class="max-w-xl mx-auto px-4">
        
        <!-- Action Buttons (No Print) -->
        <div class="no-print flex justify-between items-center mb-6">
            <a href="{{ route('dashboard.payment', $registration->id) }}" class="flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Pembayaran
            </a>
            <button onclick="window.print()" class="flex items-center gap-1.5 bg-brand-emerald hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Kwitansi
            </button>
        </div>

        <!-- Receipt Card Container -->
        <div class="bg-white print-border rounded-3xl shadow-lg border border-slate-150 overflow-hidden relative p-8 md:p-10 text-left">
            
            <!-- School Header Info -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-100">
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 leading-tight">
                        {{ \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh') }}
                    </h1>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                        Sistem Penerimaan Murid Baru (SPMB)
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> LUNAS
                    </span>
                </div>
            </div>

            <!-- Receipt Title -->
            <div class="py-6 text-center">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest">Bukti Pembayaran Resmi</h2>
                <p class="text-xs text-slate-500 font-bold mt-1">Nomor: {{ $payment->invoice_number }}</p>
            </div>

            <!-- Invoice Details Metadata -->
            <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-xs border-b border-dashed border-slate-200 pb-6 mb-6">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tanggal Transaksi</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $payment->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Metode Pembayaran</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $payment->payment_method }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nama Calon Siswa</span>
                    <span class="font-bold text-slate-850 mt-0.5 block">
                        {{ $registration->candidate_name ?? 'Calon Siswa' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Unit Pendidikan</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $registration->unit->name ?? '-' }}@if(!empty($registration->grade->name)) ({{ $registration->grade->name }})@endif
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tahun Ajaran & Gelombang</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $registration->period->year ?? '-' }} - {{ $registration->wave->name ?? '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Jalur & Program Kelas</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $registration->type->name ?? '-' }}@if(!empty($registration->classProgram->name)) ({{ $registration->classProgram->name }})@endif
                    </span>
                </div>
                @if($registration->extraServices->count() > 0)
                    <div class="col-span-2 border-t border-slate-100/80 pt-2.5 mt-0.5">
                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Layanan Tambahan</span>
                        <span class="font-extrabold text-brand-emerald mt-0.5 block">
                            {{ $registration->extraServices->pluck('name')->implode(', ') }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Pricing Details Table -->
            <div class="space-y-3 mb-8">
                <span class="text-[10px] text-slate-400 font-black uppercase tracking-wider block mb-2">Rincian Pembayaran</span>
                
                <!-- Base Amount -->
                @if($payment->payment_type === 'final_fee' && isset($payment->payment_info['selected_items']) && is_array($payment->payment_info['selected_items']))
                    @foreach($payment->payment_info['selected_items'] as $item)
                        <div class="flex justify-between items-center text-xs text-slate-600">
                            <span>{{ $item['name'] }}</span>
                            <span class="font-bold text-slate-800">
                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="flex justify-between items-center text-xs text-slate-600">
                        <span>
                            {{ $payment->payment_type === 'registration_fee' ? 'Biaya Pokok Formulir Pendaftaran' : 'Biaya Pokok Seleksi & Administrasi' }}
                        </span>
                        <span class="font-bold text-slate-800">
                            Rp {{ number_format($payment->base_amount, 0, ',', '.') }}
                        </span>
                    </div>
                @endif

                <!-- Admin Fee -->
                <div class="flex justify-between items-center text-xs text-slate-600">
                    <span>Biaya Administrasi Transaksi</span>
                    <span class="font-bold text-slate-800">
                        Rp {{ number_format($payment->admin_fee, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Total Row -->
                <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
                    <span class="text-xs font-black text-slate-900 uppercase">Total Terbayar</span>
                    <span class="text-sm font-black text-brand-emerald">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Receipt Bottom Note -->
            <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                <p class="text-[10px] text-slate-400 leading-normal font-semibold">
                    *Kwitansi ini diterbitkan secara sah dan otomatis oleh sistem SPMB Online Sekolah Anak Saleh. Tidak memerlukan tanda tangan basah. Harap simpan bukti pembayaran ini dengan baik.
                </p>
            </div>
            
        </div>
        
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
