@extends('layouts.app', [
    'activeModule' => 'core',
    'activeMenu' => 'user-role'
])
@section('title', 'User & Role')

@section('content')
    <div x-data="{ tab: 'general' }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">User & Role</h2>

            <div x-show="tab === 'general'">
                <div class="flex items-center space-x-3">
                    <button
                        @click="$dispatch('open-modal', 'modal-form')"
                        class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                        <i class="fa fa-plus"></i>
                        Tambah
                    </button>
                </div>
            </div>

            <div x-show="tab === 'role'">
                <div class="flex items-center space-x-3">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-role')"
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
                @click="tab = 'general'"
                :class="tab === 'general'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
                class="flex-1 text-center py-3 rounded-xl font-medium transition">
                User
            </button>

            <button
                @click="tab = 'role'"
                :class="tab === 'role'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
                class="flex-1 text-center py-3 rounded-xl font-medium transition">
                Role & Permission
            </button>
        </div>

        <!-- TAB CONTENT -->
        <div class="mt-4">
            <div x-show="tab === 'general'">
                @include('core::user_role.components.user')
            </div>
            <div x-show="tab === 'role'">
                @include('core::user_role.components.role')
            </div>
        </div>
    </div>
@endsection
