@extends('layouts.app', [
    'activeModule' => 'core',
    'activeMenu' => 'business-profile'
])
@section('title', 'Profil Bisnis')

@section('content')
    <div class="px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">
            Profil Bisnis
        </h2>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200">
        <div class="p-6">
            <form action="{{ route('core.business-profile.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="flex items-stretch gap-6">
                    <!-- LEFT -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Usaha
                            </label>
                            <input type="text"
                                   name="name"
                                   placeholder="Masukkan nama usaha"
                                   value="{{ @$profile->name }}"
                                   class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Jenis Usaha
                            </label>
                            <select name="type"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                <option value="">Pilih jenis usaha</option>
                                @foreach(['Angkringan', 'Restoran', 'Hybrid'] as $type)
                                    <option value="{{ $type }}" {{ @$profile->type == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="w-64 flex">
                        <input
                            type="file"
                            name="logo"
                            class="dropify"
                            data-max-file-size="2M"
                            data-allowed-file-extensions="jpg jpeg png webp"
                            data-default-file="{{ @$profile->picture->url ?? '' }}"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Lengkap
                    </label>
                    <textarea name="address" rows="3" placeholder="Masukkan alamat lengkap" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">{{ @$profile->address }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            No. HP / WhatsApp
                        </label>
                        <input type="text" name="phone" value="{{ @$profile->phone_number }}" placeholder="081234567890" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input type="email" name="email" value="{{ @$profile->email }}" placeholder="email@example.com" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        NPWP <span class="text-gray-400">(Opsional)</span>
                    </label>
                    <input type="text" name="npwp" value="{{ @$profile->npwp }}" placeholder="12.345.678.9-012.000" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')

@endpush
