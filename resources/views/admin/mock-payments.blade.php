@extends('layouts.admin')

@section('title', 'Laporan Pembayaran - Admin Panel')
@section('page_title', 'Laporan Pembayaran')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Laporan Transaksi Pembayaran</h1>
            <p class="text-xs text-slate-500 mt-1">Laporan real-time transaksi pembayaran pendaftaran calon siswa terintegrasi Winpay SNAP API.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                📥 Ekspor CSV
            </button>
        </div>
    </div>

    <!-- Payments List Table -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                        <th class="py-4 px-6">No. Invoice</th>
                        <th class="py-4 px-6">Calon Siswa</th>
                        <th class="py-4 px-6">Nama Biaya</th>
                        <th class="py-4 px-6">Biaya Tambahan</th>
                        <th class="py-4 px-6">Metode</th>
                        <th class="py-4 px-6">Nominal</th>
                        <th class="py-4 px-6">Referensi Winpay</th>
                        <th class="py-4 px-6">Waktu Transaksi</th>
                        <th class="py-4 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 font-mono text-xs font-bold text-slate-700">
                                {{ $pay->invoice_number }}
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800">
                                {{ $pay->registration->candidate_name ?? 'Draft / Belum isi biodata' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                Biaya Pendaftaran
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                @php
                                    $fee = \App\Models\SpmbFee::where('name', 'like', '%' . ($pay->registration->admission_level ?? 'TK A') . '%')->first()
                                        ?? \App\Models\SpmbFee::where('is_active', true)->first()
                                        ?? (object)['name' => 'Pendaftaran TK A'];
                                @endphp
                                {{ $fee->name }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-bold">
                                {{ $pay->payment_method }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-slate-400">
                                {{ $pay->reference_id ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs">
                                {{ $pay->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($pay->status === 'success') bg-green-50 text-green-700 border border-green-200
                                    @elseif($pay->status === 'pending') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-red-50 text-red-700 border border-red-200 @endif">
                                    {{ $pay->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 px-6 text-center text-slate-400">
                                Belum ada riwayat transaksi pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($payments->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
