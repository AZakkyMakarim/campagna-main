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
                    <i class="fa fa-file-import"></i>
                    Import
                </button>

            <div x-show="tab === 'raw'">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-raw')"
                        class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                        <i class="fa fa-plus"></i>
                        Tambah
                    </button>
                </div>

            <div x-show="tab === 'semi'">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-semi')"
                        class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                        <i class="fa fa-plus"></i>
                        Tambah
                    </button>
                </div>

            <div x-show="tab === 'finished'">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-finished')"
                        class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                        <i class="fa fa-plus"></i>
                        Tambah
                    </button>
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
        <div class="p-6">
            <div class="space-y-6">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Panduan Import:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>Gunakan format <b>.xlsx</b> atau <b>.csv</b></li>
                                <li>Pastikan kolom wajib terisi:
                                    <span class="font-medium bg-blue-100 px-1 rounded">Nama Bahan</span>,
                                    <span class="font-medium bg-blue-100 px-1 rounded">Tipe</span>,
                                    <span class="font-medium bg-blue-100 px-1 rounded">Satuan</span>
                                </li>
                                <li>Sistem akan mengupdate bahan jika nama sama.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Step 1: Download Template</label>
                    <a href="{{ route('management.ingredient.download-template') }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-orange-500 hover:text-orange-600 hover:bg-orange-50 transition cursor-pointer group">
                        <i class="fa fa-file-excel text-green-600 text-lg group-hover:scale-110 transition"></i>
                        <span class="font-medium">Download Format.xlsx</span>
                    </a>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Step 2: Upload File</label>
                    <input
                        type="file"
                        name="file"
                        required
                        accept=".csv, .xlsx, .xls"
                        class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2.5 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-orange-50 file:text-orange-700
                            hover:file:bg-orange-100
                            cursor-pointer border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
            <button
                type="button"
                @click="$dispatch('close-modal')"
                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition">
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2.5 bg-orange-600 text-white rounded-xl font-medium hover:bg-orange-700 shadow-lg shadow-orange-200 transition">
                <i class="fa fa-upload mr-2"></i>
                Mulai Import
            </button>
        </div>
    </form>
</x-modal>
