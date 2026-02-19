@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'ingredient-receipt',
    'activeSubmenu' => 'ingredient'
])
@section('title', 'Bahan & Resep')

@section('content')
    <div x-data="{ tab: 'raw' }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Bahan</h2>

            <div class="flex items-center gap-2">
                <button
                    @click="$dispatch('open-modal', 'modal-import-ingredient')"
                    class="bg-green-600 text-white px-4 py-2 rounded-xl shadow hover:bg-green-500 transition flex items-center gap-2 hover:cursor-pointer">
                    <i class="fa fa-file-csv"></i>
                    Import
                </button>

                <div x-show="tab === 'raw'">
                    <div class="flex items-center space-x-3">
                        <button
                            @click="$dispatch('open-modal', 'modal-form-raw')"
                            class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                            <i class="fa fa-plus"></i>
                            Tambah
                        </button>
                    </div>
                </div>

                <div x-show="tab === 'semi'">
                    <div class="flex items-center space-x-3">
                        <button
                            @click="$dispatch('open-modal', 'modal-form-semi')"
                            class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                            <i class="fa fa-plus"></i>
                            Tambah
                        </button>
                    </div>
                </div>

                <div x-show="tab === 'finished'">
                    <div class="flex items-center space-x-3">
                        <button
                            @click="$dispatch('open-modal', 'modal-form-finished')"
                            class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                            <i class="fa fa-plus"></i>
                            Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB HEADER -->
        <div class="flex shadow-lg border border-gray-200 bg-white rounded-xl p-2">
            <button
                @click="tab = 'raw'"
                :class="tab === 'raw'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
                class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
                Bahan Baku
            </button>

            <button
                @click="tab = 'semi'"
                :class="tab === 'semi'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
                class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
                Bahan ½ Jadi
            </button>

            <button
                @click="tab = 'finished'"
                :class="tab === 'finished'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
                class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
                Bahan Jadi
            </button>
        </div>

        <!-- TAB CONTENT -->
        <div class="mt-4">
            <div x-show="tab === 'raw'">
                @include('management::ingredient.components.raw')
            </div>
            <div x-show="tab === 'semi'">
                @include('management::ingredient.components.semi')
            </div>
            <div x-show="tab === 'finished'">
                @include('management::ingredient.components.finished')
            </div>
        </div>
    </div>
@endsection

<x-modal id="modal-import-ingredient" title="Import Bahan" size="md">
    <form method="POST" action="{{ route('management.ingredient.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="p-5">
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 text-blue-700 rounded-lg text-sm">
                    <p class="font-bold mb-1">Panduan Import:</p>
                    <ul class="list-disc list-inside">
                        <li>Format file: CSV, Excel (.xlsx, .xls)</li>
                        <li>Kolom wajib: <b>Nama Bahan, Tipe, Satuan</b></li>
                        <li>Kolom opsional: <b>Stok Minimum, Stok, Harga Beli</b></li>
                        <li>Tipe: <i>raw, semi, finished</i></li>
                    </ul>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih File</label>
                    <input type="file" name="file" required accept=".csv, .xlsx, .xls" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 px-5 py-4">
            <button
                type="button"
                @click="$dispatch('close-modal')"
                class="px-4 py-2 rounded-lg border border-gray-300 hover:cursor-pointer hover:bg-orange-100 hover:text-orange-400">
                Batal
            </button>

            <button
                type="submit"
                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                Import
            </button>
        </div>
    </form>
</x-modal>
