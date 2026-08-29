@extends('layouts.admin')

@section('title', 'CRUD Channel Pembayaran - Portal SPMB')
@section('page_title', 'Channel Pembayaran')

@section('content')
<div id="channels-settings-container" class="space-y-6">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-slate-900 sm:text-3xl sm:truncate dark:text-white">
                Kelola Channel Pembayaran
            </h2>
            <p class="text-xs text-brand-emerald font-semibold uppercase tracking-wider mt-1">
                Sekolah Anak Saleh • Metode Pembayaran Siswa
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            @if($activeTab === 'winpay')
                <form action="{{ route('admin.payment-channels.sync') }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm text-xs font-bold text-slate-700 dark:text-slate-250 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <i data-lucide="refresh-cw" class="w-4 h-4 mr-1.5 text-blue-500"></i> Sinkronkan Winpay
                    </button>
                </form>
            @endif
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-xs font-bold text-white bg-brand-emerald hover-emerald transition">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5 text-brand-yellow"></i> Tambah Channel Baru
            </button>
        </div>
    </div>

    <!-- dynamic tab gateway navigation -->
    @if($gateways->isEmpty())
        <div class="bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50 p-5 rounded-2xl text-xs font-bold flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400"></i>
            <span>Tidak ada Payment Gateway aktif. Silakan aktifkan minimal satu gateway di menu <strong>CRUD Gateway</strong> terlebih dahulu untuk mengelola channel pembayaran.</span>
        </div>
    @else
        <div class="flex flex-wrap gap-2 bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            @foreach($gateways as $gw)
                <a href="{{ route('admin.payment-channels.index', ['tab' => $gw->code]) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === $gw->code ? 'bg-brand-emerald text-white shadow' : 'text-slate-650 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    {{ $gw->name }}
                    <span class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ $activeTab === $gw->code ? 'bg-brand-yellow text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">
                        {{ $gw->payment_channels_count }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
        <!-- Type Tabs (Pills) -->
        <div class="flex flex-wrap gap-1 p-1 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200/60 dark:border-slate-800/80 w-full md:w-auto">
            <a href="{{ route('admin.payment-channels.index', array_merge(request()->query(), ['type' => ''])) }}" class="px-4 py-2 rounded-lg text-xs font-bold transition text-center flex-1 md:flex-initial {{ !request('type') ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm border border-slate-200/50 dark:border-slate-800' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                Semua Tipe
            </a>
            <a href="{{ route('admin.payment-channels.index', array_merge(request()->query(), ['type' => 'va'])) }}" class="px-4 py-2 rounded-lg text-xs font-bold transition text-center flex-1 md:flex-initial {{ request('type') === 'va' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm border border-slate-200/50 dark:border-slate-800' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                Virtual Account (VA)
            </a>
            <a href="{{ route('admin.payment-channels.index', array_merge(request()->query(), ['type' => 'qris'])) }}" class="px-4 py-2 rounded-lg text-xs font-bold transition text-center flex-1 md:flex-initial {{ request('type') === 'qris' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm border border-slate-200/50 dark:border-slate-800' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                QRIS
            </a>
            <a href="{{ route('admin.payment-channels.index', array_merge(request()->query(), ['type' => 'ewallet'])) }}" class="px-4 py-2 rounded-lg text-xs font-bold transition text-center flex-1 md:flex-initial {{ request('type') === 'ewallet' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm border border-slate-200/50 dark:border-slate-800' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                E-Wallet
            </a>
            <a href="{{ route('admin.payment-channels.index', array_merge(request()->query(), ['type' => 'retail'])) }}" class="px-4 py-2 rounded-lg text-xs font-bold transition text-center flex-1 md:flex-initial {{ request('type') === 'retail' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm border border-slate-200/50 dark:border-slate-800' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                Modern Retail
            </a>
        </div>

        <!-- Search & Status Filter Form -->
        <form method="GET" action="{{ route('admin.payment-channels.index') }}" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-center">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            
            <!-- Search -->
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode channel..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-36">
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Table List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center">
            <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">
                Daftar Channel Pembayaran ({{ $activeGateway->name ?? 'Tanpa Gateway' }})
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-950/20">
                        <th class="py-4 px-6">Nama Channel</th>
                        <th class="py-4 px-6">Kode Pembayaran</th>
                        <th class="py-4 px-6">Tipe Channel</th>
                        <th class="py-4 px-6 text-center">Status Aktif</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($channels as $channel)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/50 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-650 dark:text-slate-350 text-xs shadow-inner">
                                        @if(str_contains(strtoupper($channel->code), 'BCA'))
                                            BCA
                                        @elseif(str_contains(strtoupper($channel->code), 'BNI'))
                                            BNI
                                        @elseif(str_contains(strtoupper($channel->code), 'MANDIRI'))
                                            MDR
                                        @elseif(str_contains(strtoupper($channel->code), 'BRI'))
                                            BRI
                                        @elseif(str_contains(strtoupper($channel->code), 'QRIS'))
                                            QR
                                        @else
                                            {{ substr($channel->code, 0, 3) }}
                                        @endif
                                    </div>
                                    <div class="font-extrabold text-slate-800 dark:text-slate-200">{{ $channel->name }}</div>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-brand-emerald dark:text-emerald-450 font-bold">
                                {{ $channel->code }}
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $typeColors = [
                                        'va' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/40',
                                        'qris' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/40',
                                        'ewallet' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/40',
                                        'retail' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/40',
                                    ];
                                    $col = $typeColors[$channel->type] ?? 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800/40 dark:text-slate-400';
                                @endphp
                                <span class="px-2 py-0.5 border rounded text-[9px] font-extrabold uppercase tracking-wider {{ $col }}">
                                    {{ strtoupper($channel->type) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <form action="{{ route('admin.payment-channels.toggle', $channel->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $channel->is_active ? 'bg-brand-emerald' : 'bg-slate-200 dark:bg-slate-700' }}">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $channel->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex justify-end items-center gap-1.5">
                                    <button onclick="openEditModal({{ json_encode($channel) }})" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition">
                                        Edit
                                    </button>
                                    <button type="button" onclick="confirmDelete('{{ route('admin.payment-channels.destroy', $channel->id) }}', 'Apakah Anda yakin ingin menghapus channel {{ $channel->name }} ini?')" class="bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="credit-card" class="w-8 h-8 text-slate-300"></i>
                                    <p class="text-xs font-bold">Belum ada channel pembayaran terdaftar pada gateway ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div id="createModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/40 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden text-left transform scale-95 transition-all duration-150" id="createModalBody">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-sm flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4 text-brand-yellow"></i> Tambah Channel Pembayaran Baru
            </h3>
            <button onclick="closeCreateModal()" class="text-white hover:text-brand-yellow transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('admin.payment-channels.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            
            @if($activeGateway)
                <input type="hidden" name="payment_gateway_id" value="{{ $activeGateway->id }}">
            @else
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Gateway Pembayaran</label>
                    <select name="payment_gateway_id" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
                        @foreach($gateways as $gw)
                            <option value="{{ $gw->id }}">{{ $gw->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Channel</label>
                <input type="text" name="name" required placeholder="Contoh: Permata Virtual Account" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Channel</label>
                <input type="text" name="code" required placeholder="Contoh: PERMATA, QRIS, MANDIRI" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition font-mono uppercase">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe Channel</label>
                <select name="type" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
                    <option value="va">Virtual Account (VA)</option>
                    <option value="qris">QRIS</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="retail">Retail Outlet</option>
                </select>
            </div>

            <div class="flex items-center">
                <input id="create_is_active" name="is_active" type="checkbox" checked value="1" class="h-4 w-4 rounded border-slate-300 text-brand-emerald focus:ring-brand-emerald">
                <label for="create_is_active" class="ml-2 block text-xs font-bold text-slate-650 dark:text-slate-350">
                    Status Aktif (Langsung Aktifkan Channel)
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateModal()" class="border border-slate-250 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition">
                    Batal
                </button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition">
                    Simpan Channel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/40 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden text-left transform scale-95 transition-all duration-150" id="editModalBody">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-sm flex items-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4 text-brand-yellow"></i> Edit Channel Pembayaran
            </h3>
            <button onclick="closeEditModal()" class="text-white hover:text-brand-yellow transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            <input type="hidden" name="payment_gateway_id" id="edit_gateway_id">

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Channel</label>
                <input type="text" name="name" id="edit_name" required placeholder="Contoh: Permata Virtual Account" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Channel</label>
                <input type="text" name="code" id="edit_code" required placeholder="Contoh: PERMATA, QRIS" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition font-mono uppercase">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe Channel</label>
                <select name="type" id="edit_type" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
                    <option value="va">Virtual Account (VA)</option>
                    <option value="qris">QRIS</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="retail">Retail Outlet</option>
                </select>
            </div>

            <div class="flex items-center">
                <input id="edit_is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-emerald focus:ring-brand-emerald">
                <label for="edit_is_active" class="ml-2 block text-xs font-bold text-slate-650 dark:text-slate-350">
                    Status Aktif
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="border border-slate-250 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition">
                    Batal
                </button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Create Modal Handler
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        const body = document.getElementById('createModalBody');
        modal.classList.remove('hidden');
        setTimeout(() => {
            body.classList.remove('scale-95');
            body.classList.add('scale-100');
        }, 10);
    }
    
    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        const body = document.getElementById('createModalBody');
        body.classList.remove('scale-100');
        body.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    // Edit Modal Handler
    function openEditModal(channel) {
        document.getElementById('edit_gateway_id').value = channel.payment_gateway_id;
        document.getElementById('edit_name').value = channel.name;
        document.getElementById('edit_code').value = channel.code;
        document.getElementById('edit_type').value = channel.type;
        document.getElementById('edit_is_active').checked = !!channel.is_active;
        
        // Dynamic action URL
        const form = document.getElementById('editForm');
        form.action = `/admin/payment-channels/${channel.id}/update`;

        const modal = document.getElementById('editModal');
        const body = document.getElementById('editModalBody');
        modal.classList.remove('hidden');
        setTimeout(() => {
            body.classList.remove('scale-95');
            body.classList.add('scale-100');
        }, 10);
    }
    
    function closeEditModal() {
        const modal = document.getElementById('editModal');
        const body = document.getElementById('editModalBody');
        body.classList.remove('scale-100');
        body.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        const createModal = document.getElementById('createModal');
        const editModal = document.getElementById('editModal');
        if (e.target === createModal) {
            closeCreateModal();
        }
        if (e.target === editModal) {
            closeEditModal();
        }
    });
</script>
@endsection
