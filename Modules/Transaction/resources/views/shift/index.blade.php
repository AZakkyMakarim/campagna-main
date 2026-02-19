@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'shift',
    'activeSubmenu' => 'shift',
])
@section('title', 'Shift')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Shift</h2>
        @if(!$cashierShift)
            <div class="flex items-center gap-4 bg-orange-50 border border-orange-200 rounded-lg px-5 py-3 bg-white">
                <div class="text-sm text-orange-700">
                    Shift belum dibuka
                </div>

                <a href="{{ route('transaction.shift.open') }}"
                    class="flex items-center gap-2 px-5 py-2
                        bg-orange-600 text-white font-semibold
                        rounded-lg shadow
                        hover:bg-orange-500
                        transition"
                >
                    <i class="fa fa-door-open"></i>
                    Buka Shift
                </a>
            </div>
        @else
            <div class="flex items-center gap-4 bg-orange-50 border border-orange-200 rounded-lg px-5 py-3 bg-white">
                <div class="text-sm text-orange-700">
                    Shift dibuka
                </div>

                <button
                    @click="$dispatch('open-modal', 'modal-form-closing')"
                    type="button"
                    class="flex items-center gap-2 px-5 py-2
                       bg-orange-600 text-white font-semibold
                       rounded-lg shadow
                       hover:bg-orange-500
                       transition"
                >
                    <i class="fa fa-door-closed"></i>
                    Tutup Shift
                </button>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div class="rounded-lg p-4 border border-gray-200 shadow shadow-md bg-white">
                <div class="space-y-1">
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <i class="fa fa-user"></i> Kasir
                </span>
                    <span class="block text-sm font-bold text-gray-600 leading-relaxed">{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                </div>
            </div>

            <div class="rounded-lg p-4 border border-gray-200 shadow shadow-md bg-white">
                <div class="space-y-1">
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <i class="fa fa-building"></i> Outlet
                </span>
                    <span class="block text-sm font-bold text-gray-600 leading-relaxed">{{ $outlet->name }}</span>
                </div>
            </div>

            <div class="rounded-lg p-4 border border-gray-200 shadow shadow-md bg-white">
                <div class="space-y-1">
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <i class="fa fa-credit-card"></i> Cash Awal
                </span>
                    <span class="block text-sm font-bold text-gray-600 leading-relaxed">{{ rp_format($outlet->initial_cash) }}</span>
                </div>
            </div>

            <div class="rounded-lg p-4 border border-gray-200 shadow shadow-md bg-white">
                <div class="space-y-1">
                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <i class="fa fa-wallet"></i> Petty Cash
                </span>
                    <span class="block text-sm font-bold text-orange-600 leading-relaxed">{{ rp_format($outlet->petty_cash) }}</span>
                </div>
            </div>
        </div>

        @if($cashierShift)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-lg p-4 border border-gray-200 shadow shadow-md bg-white">
                    <div class="space-y-3">
                        <span class="block font-semibold text-xl">
                            <i class="fa fa-credit-card text-orange-600"></i> Cash Fisik
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                                <span class="block font-semibold text-xl"><i class="fa fa-dollar-sign text-orange-600"></i></span>
                                <span class="block font-bold text-xl">{{ rp_format($cashierShift->opening_cash) }}</span>
                                <span class="block font-light text-xs">Modal Awal</span>
                            </div>
                            <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                                <span class="block font-semibold text-xl"><i class="fa fa-receipt text-orange-600"></i></span>
                                <span class="block font-bold text-xl">{{ rp_format(0) }}</span>
                                <span class="block font-light text-xs">Penjualan Cash</span>
                            </div>
                            <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                                <span class="block font-semibold text-xl"><i class="fa fa-piggy-bank text-orange-600"></i></span>
                                <span class="block font-bold text-xl">{{ rp_format(0) }}</span>
                                <span class="block font-light text-xs">Expected Cash</span>
                            </div>
                        </div>

                        <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                <span class="flex items-center gap-1">
                                    <i class="fa fa-receipt"></i>
                                    Total Transaksi
                                </span>

                                <span>{{ number_format(0, 0, ',', '.') }} Transaksi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg p-4 border border-gray-200 shadow shadow-md bg-white">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="block font-semibold text-xl">
                                <i class="fa fa-wallet text-orange-600"></i> Petty Cash
                            </span>

                            <button
                                @click="$dispatch('open-modal', 'modal-form-petty')"
                                type="button"
                                class="flex items-center gap-2 px-5 py-2 text-xs
                                       bg-orange-600 text-white font-semibold
                                       rounded-lg shadow
                                       hover:bg-orange-500
                                       transition"
                                >
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                                <span class="block font-semibold text-xl"><i class="fa fa-wallet text-orange-600"></i></span>
                                <span class="block font-bold text-xl">{{ rp_format($cashierShift->opening_petty_cash) }}</span>
                                <span class="block font-light text-xs">Dana Awal</span>
                            </div>
                            <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                                <span class="block font-semibold text-xl"><i class="fa fa-shopping-bag text-orange-600"></i></span>
                                <span class="block font-bold text-xl">{{ rp_format($cashMovement->sum('amount')) }}</span>
                                <span class="block font-light text-xs">Terpakai</span>
                            </div>
                            <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                                <span class="block font-semibold text-xl"><i class="fa fa-wallet text-orange-600"></i></span>
                                <span class="block font-bold text-xl">{{ rp_format($cashierShift->expected_petty_cash) }}</span>
                                <span class="block font-light text-xs">Sisa</span>
                            </div>
                        </div>

                        <div class="rounded-lg p-4 border border-gray-200 bg-gray-200 text-center">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                <span class="flex items-center gap-1">
                                    <i class="fa fa-receipt"></i>
                                    Total Transaksi
                                </span>

                                <span>{{ number_format($cashMovement->count(), 0, ',', '.') }} Transaksi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="relative max-h-[600px] overflow-y-auto rounded-lg shadow-lg border border-gray-200 bg-white">
            <span class="block font-semibold text-xl p-4"><i class="fa fa-clock-rotate-left text-orange-600"></i> Riwayat Shift Hari Ini</span>
            <table class="w-full text-sm text-left">
                <thead class="bg-orange-700 text-white uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Kasir</th>
                    <th class="px-4 py-3">Waktu Mulai</th>
                    <th class="px-4 py-3">Waktu Selesai</th>
                    <th class="px-4 py-3">Modal Awal</th>
                    <th class="px-4 py-3">Cash Sales</th>
                    <th class="px-4 py-3">Cash Fisik</th>
                    <th class="px-4 py-3">Selisih</th>
                    <th class="px-4 py-3">Setoran HO</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @if($histories->count() > 0)
                    @foreach($histories as $history)
                        <tr>
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $history->user->name }}</td>
                            <td class="px-4 py-3">{{ parse_date_time($history->opened_at) }}</td>
                            <td class="px-4 py-3">{{ parse_date_time($history->closed_at) }}</td>
                            <td class="px-4 py-3">{{ rp_format($history->opening_cash) }}</td>
                            <td class="px-4 py-3">{{ rp_format($history->actual_cash) }}</td>
                            <td class="px-4 py-3">{{ rp_format($history->expected_cash) }}</td>
                            <td class="px-4 py-3">{{ rp_format($history->cash_difference) }}</td>
                            <td class="px-4 py-3">{{ rp_format(@$history->cashMovements->where('category', 'HO_DEPOSIT')->first()->amount) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100%" class="py-16">
                            <div class="flex flex-col items-center justify-center text-center text-gray-500 gap-3">

                                <!-- ICON -->
                                <div class="w-14 h-14 flex items-center justify-center
                            rounded-full bg-orange-100 text-orange-500">
                                    <i class="fa fa-clock-rotate-left text-2xl"></i>
                                </div>

                                <!-- TITLE -->
                                <h3 class="text-sm font-semibold text-gray-600">
                                    Riwayat Shift Hari Ini
                                </h3>

                                <!-- DESCRIPTION -->
                                <p class="text-xs text-gray-400 max-w-xs">
                                    Belum ada aktivitas shift yang tercatat hari ini.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    @if($cashierShift)
        <x-modal id="modal-form-petty" title="Tambah Pengeluaran Petty Cash" subTitle="Catat pembelian darurat menggunakan petty cash" icon="fa-plus" size="md">
            <form method="POST" action="{{ route('transaction.shift.petty-cash.out') }}">
                @csrf
                <div class="p-5 space-y-5">
                    <div class="p-4 bg-orange-100 rounded-lg border border-orange-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Sisa Petty Cash</span>
                            <span class="text-xl font-bold text-orange-500">{{ rp_format($cashierShift->expected_petty_cash) }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi Pembelian</label>
                        <input type="text" name="description" required placeholder="Contoh: Beli gas LPG, Beli plastik kemasan"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah (Rp)</label>
                        <input type="number" step="1" min="0" name="amount" required placeholder="Masukkan jumlah pengeluaran"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-5 py-4 border-t">
                    <button type="button"
                            @click="$dispatch('close-modal')"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-orange-100 hover:text-orange-500">
                        Batal
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500">
                        Simpan
                    </button>
                </div>
            </form>
        </x-modal>
        <x-modal id="modal-form-closing" title="Closing Shift Kasir" subTitle="Lengkapi data closing shift" icon="fa-plus" size="md">
            <form method="POST" action="{{ route('transaction.shift.close', $cashierShift ?? 0) }}">
                @csrf
                <div
                    x-data="cashClosing({
                                expectedCash: {{ $cashierShift->expected_cash }},
                                nextModal: {{ $outlet->initial_cash }}
                            })"
                    class="p-5 space-y-5"
                >
                    <div class="p-4 bg-orange-100 rounded-lg border border-orange-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Expected Cash</span>
                            <span class="text-xl font-bold text-orange-500">{{ rp_format($cashierShift->expected_cash) }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Cash Fisik di Laci (Rp)</label>
                        <input type="number" step="1" min="0" name="amount" required placeholder="Masukkan cash fisik"
                               x-model.number="cashFisik"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div class="px-4 py-2 rounded-lg border"
                         :class="selisihClass">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-600">
                                Selisih
                            </span>
                            <span class="text-md font-bold"
                                  x-text="formatRupiah(selisih)">
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Modal Shift Berikutnya (Rp)</label>
                        <input type="number" step="1" min="0" name="amount" required readonly
                               value="{{ $outlet->initial_cash }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div class="px-4 py-2 rounded-lg border border-green-200 bg-green-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-600">
                                Setoran ke HO
                            </span>
                            <span class="text-md font-bold text-green-600"
                                  x-text="formatRupiah(setoranHO)">
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Catatan Closing (optional)</label>
                        <textarea name="description" id="" cols="30" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="Catatan jika ada selisih atau hal khusus..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-5 py-4">
                    <button type="button"
                            @click="$dispatch('close-modal')"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-orange-100 hover:text-orange-500">
                        Batal
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500">
                        Simpan
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
@endsection


@push('js')
    <script>
        function cashClosing({ expectedCash, nextModal }) {
            return {
                expectedCash: Number(expectedCash),
                nextModal: Number(nextModal),
                cashFisik: 0,

                // 🔥 SELISIH vs SISTEM
                get selisih() {
                    return this.cashFisik - this.expectedCash;
                },

                // 🔥 SETORAN HO = CASH FISIK - MODAL SHIFT BERIKUTNYA
                get setoranHO() {
                    const val = this.cashFisik - this.nextModal;
                    return val > 0 ? val : 0;
                },

                // WARNA SELISIH
                get selisihClass() {
                    if (this.selisih > 0) {
                        return 'text-green-600';
                    }
                    if (this.selisih < 0) {
                        return 'text-red-600';
                    }
                    return 'text-gray-600';
                },

                formatRupiah(val) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(val || 0);
                }
            }
        }
    </script>
@endpush
