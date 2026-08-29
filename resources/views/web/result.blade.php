@extends('layouts.portal')

@section('title', 'Hasil Seleksi & Administrasi Akhir - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8 space-y-6">
<style>
    /* Styling for dynamic rich text instructions from Quill */
    .instructions-body ul {
        list-style-type: disc !important;
        padding-left: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .instructions-body ol {
        list-style-type: decimal !important;
        padding-left: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .instructions-body li {
        margin-bottom: 0.625rem !important;
        line-height: 1.625 !important;
    }
</style>

    <!-- MAIN CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- CARD HEADER -->
        <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-lg flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-brand-yellow"></i>
                    Hasil Seleksi & Administrasi Akhir
                </h2>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <p class="text-xs text-brand-yellow/90 font-medium">
                        Pengumuman kelulusan resmi dan rincian pembiayaan pendidikan.
                    </p>
                </div>
            </div>
            @if($registration->registration_status === 'completed')
                <span class="bg-green-700 text-white font-bold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-green-500 shadow-sm flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-300 animate-ping"></span> Lunas & Resmi
                </span>
            @else
                <span class="bg-amber-600 text-white font-bold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-amber-455 shadow-sm">
                    Menunggu Pelunasan
                </span>
            @endif
        </div>

        <div class="p-8 space-y-8">
            
            <!-- ANNOUNCEMENT BANNER -->
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100/50 dark:from-emerald-950/10 dark:to-emerald-900/5 border border-emerald-200/60 dark:border-emerald-900/50 rounded-2xl p-6 flex flex-col sm:flex-row gap-5 items-center text-center sm:text-left">
                <div class="h-16 w-16 bg-brand-emerald text-white rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                    <i data-lucide="party-popper" class="w-8 h-8 text-brand-yellow"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-black text-slate-850 dark:text-white">Alhamdulillah, Dinyatakan LULUS & DITERIMA</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Selamat kepada ananda <strong class="text-slate-800 dark:text-slate-200">{{ $registration->candidate_name }}</strong> yang telah lolos seluruh tahapan observasi kesiapan belajar dan berkas pendaftaran.
                    </p>
                </div>
            </div>

            <!-- STUDENT PROFILE META -->
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-5 border border-slate-100 dark:border-slate-850 grid grid-cols-2 sm:grid-cols-6 gap-4 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">No. Registrasi</span>
                    <span class="font-extrabold text-brand-emerald dark:text-emerald-450 mt-1 block">SANS-{{ substr($registration->period->year ?? '2026', 0, 4) }}-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nama Calon Siswa</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->candidate_name }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tingkat / Unit</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->admission_level }} - {{ $registration->unit->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Program Kelas</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->classProgram->name ?? 'Reguler' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Layanan Tambahan</span>
                    <span class="font-extrabold text-brand-emerald dark:text-emerald-450 mt-1 block">
                        {{ $registration->extraServices->count() > 0 ? $registration->extraServices->pluck('name')->implode(', ') : 'Tidak Ada' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tahun Pelajaran</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->period->year ?? '2027-2028' }}</span>
                </div>
            </div>

            <!-- TUITION FEES COMPONENT BREAKDOWN -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-1.5">
                    <i data-lucide="receipt" class="w-4 h-4 text-brand-emerald"></i> Rincian Biaya Pendidikan Masuk Awal
                </h4>

                <div class="bg-white dark:bg-slate-900 border border-slate-150/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-inner">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-850">
                                @if($registration->registration_status !== 'completed')
                                    <th class="p-4 text-center w-12 select-none">Pilih</th>
                                @endif
                                <th class="p-4">Komponen Pembiayaan</th>
                                <th class="p-4 text-right">Nominal</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                            @if(isset($feeDetails['items']) && is_array($feeDetails['items']))
                                @php
                                    $successfulPayments = $registration->payments()
                                        ->where('status', 'success')
                                        ->where('payment_type', 'final_fee')
                                        ->get();
                                @endphp
                                @foreach($feeDetails['items'] as $item)
                                    @php
                                        $isPaid = isset($paidItemNames) && in_array($item['name'], $paidItemNames);
                                        
                                        // Find payment receipt for this specific item
                                        $itemPayment = null;
                                        if ($isPaid && isset($successfulPayments)) {
                                            foreach ($successfulPayments as $p) {
                                                if (isset($p->payment_info['selected_items']) && is_array($p->payment_info['selected_items'])) {
                                                    foreach ($p->payment_info['selected_items'] as $si) {
                                                        if ($si['name'] === $item['name']) {
                                                            $itemPayment = $p;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    <tr class="text-slate-650 dark:text-slate-350 {{ $isPaid ? 'bg-slate-50/40 dark:bg-slate-950/10' : '' }}">
                                        @if($registration->registration_status !== 'completed')
                                            <td class="p-4 text-center">
                                                @if($isPaid)
                                                    <span class="inline-flex items-center justify-center text-green-600 dark:text-green-400">
                                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                    </span>
                                                @else
                                                    <input type="checkbox" class="fee-checkbox rounded text-brand-emerald focus:ring-brand-emerald cursor-pointer" data-amount="{{ $item['amount'] }}" data-index="{{ $loop->index }}" data-gateways="{{ json_encode($item['gateways'] ?? ['winpay']) }}" checked>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="p-4 font-medium {{ $isPaid ? 'line-through text-slate-400' : '' }}">{{ $item['name'] }}</td>
                                        <td class="p-4 text-right font-bold {{ $isPaid ? 'text-slate-400' : 'text-slate-800 dark:text-slate-200' }}">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                        <td class="p-4 text-center">
                                            @if($isPaid || $registration->registration_status === 'completed')
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Lunas</span>
                                                    @if($itemPayment)
                                                        <a href="{{ route('dashboard.payment.receipt', $itemPayment->id) }}" class="download-link-animate inline-flex items-center gap-0.5 text-[9px] font-bold text-brand-emerald hover:underline" title="Unduh Kwitansi">
                                                            <i data-lucide="download" class="w-3 h-3 text-brand-emerald"></i> Unduh
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Tanggungan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr class="bg-slate-50/50 dark:bg-slate-950/30 text-xs font-black text-slate-800 dark:text-white uppercase border-t border-slate-150 dark:border-slate-800">
                                @if($registration->registration_status !== 'completed')
                                    <td></td>
                                @endif
                                <td class="p-4">Total Biaya</td>
                                <td class="p-4 text-right text-brand-emerald dark:text-emerald-400 text-sm font-extrabold" id="total-amount-display">Rp 0</td>
                                <td class="p-4 text-center">
                                    @if($registration->registration_status === 'completed')
                                        <span class="text-[10px] bg-green-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm">Lunas</span>
                                    @else
                                        <span class="text-[10px] bg-amber-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm" id="total-status-badge">Belum Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="gateway-conflict-warning" class="hidden text-[11px] text-red-800 dark:text-red-300 font-extrabold bg-red-50 dark:bg-red-955/20 border border-red-200/50 dark:border-red-900/50 rounded-xl p-4 mt-4 flex items-center gap-2.5 shadow-sm leading-relaxed">
                    <i data-lucide="alert-triangle" class="w-4.5 h-4.5 text-red-600 flex-shrink-0 animate-bounce"></i>
                    <span>Komponen biaya yang dipilih tidak dapat dibayar bersamaan karena menggunakan metode pembayaran berbeda (misal BNI Snap saja & Winpay saja). Silakan centang item satu per satu.</span>
                </div>
            </div>

            <!-- INSTRUCTIONS BOX -->
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 space-y-3.5 text-xs text-slate-600 dark:text-slate-400">
                <h5 class="font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5 uppercase tracking-wider text-[10px]">
                    <i data-lucide="info" class="w-4 h-4 text-brand-emerald"></i> Informasi Penting & Prosedur Daftar Ulang
                </h5>
                <div class="instructions-body text-slate-650 dark:text-slate-350">
                    @if($registration->registration_status !== 'completed')
                        {!! $registration->unit?->re_registration_instructions_unpaid 
                            ?: \App\Models\Setting::get('re_registration_instructions_unpaid', '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut ke Pembayaran Online</strong> di bawah untuk memilih metode transfer Virtual Account Bank (BNI) atau pemindaian kode QRIS secara instan.</li><li><strong>Daftar Ulang Resmi:</strong> Setelah seluruh komponen biaya di atas terkonfirmasi <strong>Lunas</strong> oleh sistem, calon siswa secara resmi terdaftar dan Anda dapat mencetak Surat Keterangan Penerimaan (SKP) langsung dari halaman ini.</li></ul>') !!}
                    @else
                        {!! $registration->unit?->re_registration_instructions_completed 
                            ?: \App\Models\Setting::get('re_registration_instructions_completed', '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>') !!}
                    @endif
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
                @if($registration->registration_status === 'completed')
                    <!-- Completed buttons -->
                    <a href="{{ route('dashboard.admission-letter.download', $registration->id) }}" class="download-link-animate w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-8 py-3.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="download" class="w-4.5 h-4.5"></i> Unduh Surat Kelulusan
                    </a>
                    @if(isset($successfulPayments) && $successfulPayments->count() > 1)
                        <button onclick="openReceiptsModal()" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 px-8 py-3.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-4.5 h-4.5 text-brand-emerald"></i> Unduh Kwitansi Pembayaran
                        </button>
                    @elseif(isset($successfulPayments) && $successfulPayments->count() === 1)
                        <a href="{{ route('dashboard.payment.receipt', $successfulPayments->first()->id) }}" class="download-link-animate w-full sm:w-auto border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 px-8 py-3.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-4.5 h-4.5 text-brand-emerald"></i> Unduh Kwitansi Pembayaran
                        </a>
                    @endif
                @else
                    <!-- Unpaid buttons -->
                    <a href="{{ route('dashboard.payment', $registration->id) }}" id="payment-btn" data-base-url="{{ route('dashboard.payment', $registration->id) }}" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-8 py-3.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="credit-card" class="w-4.5 h-4.5 text-brand-yellow animate-pulse"></i> Lanjut ke Pembayaran Online
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.fee-checkbox');
        const totalDisplay = document.getElementById('total-amount-display');
        const paymentBtn = document.getElementById('payment-btn');
        const totalBadge = document.getElementById('total-status-badge');

        if (checkboxes.length > 0 && totalDisplay && paymentBtn) {
            const baseUrl = paymentBtn.getAttribute('data-base-url');

            const calculateTotal = function() {
                let total = 0;
                const checkedIndices = [];
                let commonGateways = null;

                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        checkedIndices.push(cb.getAttribute('data-index'));
                        total += parseInt(cb.getAttribute('data-amount'), 10);

                        const itemGateways = JSON.parse(cb.getAttribute('data-gateways') || '[]');
                        if (commonGateways === null) {
                            commonGateways = [...itemGateways];
                        } else {
                            commonGateways = commonGateways.filter(gw => itemGateways.includes(gw));
                        }
                    }
                });

                // Update total display format Rupiah
                totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');

                // Update dynamic URL query param
                paymentBtn.href = baseUrl + '?items=' + checkedIndices.join(',');

                const warningBox = document.getElementById('gateway-conflict-warning');
                const hasConflict = checkedIndices.length > 0 && commonGateways && commonGateways.length === 0;

                if (hasConflict) {
                    if (warningBox) {
                        warningBox.classList.remove('hidden');
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                    paymentBtn.classList.add('opacity-50', 'pointer-events-none');
                    if (totalBadge) {
                        totalBadge.className = "text-[10px] bg-red-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm select-none animate-pulse";
                        totalBadge.textContent = "Konflik Metode";
                    }
                } else {
                    if (warningBox) {
                        warningBox.classList.add('hidden');
                    }

                    // Update state & badge
                    if (checkedIndices.length === 0) {
                        paymentBtn.classList.add('opacity-50', 'pointer-events-none');
                        if (totalBadge) {
                            totalBadge.className = "text-[10px] bg-slate-400 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm select-none";
                            totalBadge.textContent = "Kosong";
                        }
                    } else {
                        paymentBtn.classList.remove('opacity-50', 'pointer-events-none');
                        if (totalBadge) {
                            totalBadge.className = "text-[10px] bg-amber-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm select-none";
                            totalBadge.textContent = "Belum Lunas";
                        }
                    }
                }
            };

            // Bind change listener to each checkbox
            checkboxes.forEach(cb => {
                cb.addEventListener('change', calculateTotal);
            });

            // Initial calculation on render
            calculateTotal();
        }

        // Handle loading animations for download buttons (placed outside check block so it runs unconditionally)
        document.querySelectorAll('.download-link-animate').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const originalHref = this.getAttribute('href');
                const originalContent = this.innerHTML;
                
                // Generate a unique token
                const token = 'dt_' + Date.now();
                const downloadUrl = originalHref + (originalHref.includes('?') ? '&' : '?') + 'download_token=' + token;
                
                // Show spinner animation
                this.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="animate-spin h-3.5 w-3.5 text-brand-emerald" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mohon Tunggu...</span>';
                this.style.pointerEvents = 'none';
                
                // Start the download
                window.location.href = downloadUrl;
                
                // Poll for the cookie
                const cookieName = 'download_status_' + token;
                const checkInterval = setInterval(() => {
                    const cookies = document.cookie.split(';');
                    let cookieFound = false;
                    for (let i = 0; i < cookies.length; i++) {
                        const c = cookies[i].trim();
                        if (c.indexOf(cookieName + '=') === 0) {
                            cookieFound = true;
                            // Delete the cookie
                            document.cookie = cookieName + '=; Max-Age=-99999999; path=/;';
                            break;
                        }
                    }
                    
                    if (cookieFound) {
                        clearInterval(checkInterval);
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = '';
                    }
                }, 150);
                
                // Safety timeout fallback (15 seconds) in case of network/render errors
                setTimeout(() => {
                    clearInterval(checkInterval);
                    if (this.style.pointerEvents === 'none') {
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = '';
                    }
                }, 15000);
            });
        });
    });
</script>

<!-- Receipts Modal -->
@if(isset($successfulPayments) && $successfulPayments->count() > 1)
    <div id="receiptsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeReceiptsModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 dark:border-slate-800">
                <div class="bg-white dark:bg-slate-900 px-6 pt-6 pb-4 sm:p-8">
                    <h3 class="text-base font-extrabold text-slate-800 dark:text-white mb-4 flex items-center gap-2" id="modal-title">
                        <i data-lucide="file-text" class="w-5 h-5 text-brand-emerald"></i>
                        Pilih Kwitansi Pembayaran
                    </h3>
                    <div class="space-y-3">
                        @foreach($successfulPayments as $index => $p)
                            @php
                                $itemNames = collect($p->payment_info['selected_items'] ?? [])->pluck('name')->implode(', ');
                            @endphp
                            <div class="border border-slate-150 dark:border-slate-800 rounded-2xl p-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-950/20">
                                <div class="space-y-1">
                                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Kwitansi #{{ $index + 1 }}</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white block leading-tight">{{ $itemNames ?: 'Biaya Administrasi Akhir' }}</span>
                                    <span class="text-[10px] text-brand-emerald font-extrabold block">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('dashboard.payment.receipt', $p->id) }}" class="download-link-animate bg-white hover:bg-slate-50 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-[10px] font-bold shadow-sm transition flex items-center gap-1">
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-brand-emerald"></i> Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 sm:py-5 flex justify-end border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeReceiptsModal()" class="border border-slate-200 dark:border-slate-700 text-slate-650 dark:text-slate-400 px-5 py-2.5 rounded-xl text-xs font-bold transition hover:bg-slate-100 dark:hover:bg-slate-800">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function openReceiptsModal() {
            document.getElementById('receiptsModal').classList.remove('hidden');
        }
        function closeReceiptsModal() {
            document.getElementById('receiptsModal').classList.add('hidden');
        }
    </script>
@endif
@endsection
