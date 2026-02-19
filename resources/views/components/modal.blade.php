@props([
    'id' => 'modal',
    'idModalTitle' => 'modal-title',
    'idSubModalTitle' => 'sub-modal-title',
    'icon' => '',
    'title' => 'Modal Title',
    'subTitle' => '',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ];
@endphp

<div
    x-data="{
        open: false,
        payload: null
    }"
    x-show="open"
    x-on:open-modal.window="
        (
            typeof $event.detail === 'string'
            && $event.detail === '{{ $id }}'
        )
        ? (payload = null, open = true)
        : (
            typeof $event.detail === 'object'
            && $event.detail.id === '{{ $id }}'
        )
            ? (payload = $event.detail.payload ?? null, open = true)
            : null
    "
    x-on:close-modal.window="open = false"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="display: none;"
>

    <!-- Backdrop -->
    <div
        class="absolute inset-0 bg-black/80 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <!-- Modal Box -->
    <div class="relative w-full {{ $sizes[$size] }} bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- Header -->
        <div class="flex items-start justify-between px-5 py-4
            bg-gradient-to-r from-orange-100 via-orange-100 to-white
            border-b border-gray-200 rounded-t-xl">
            <div class="flex items-center gap-3">
                <!-- ICON -->
                @if($icon)
                    <div class="w-9 h-9 flex items-center justify-center rounded-md bg-orange-500 text-white">
                        <i class="fa {{ $icon }} text-sm"></i>
                    </div>
                @endif

                <!-- TITLE + SUBTITLE -->
                <div class="flex flex-col justify-center">
                    <h3 class="font-semibold text-lg leading-tight" id="{{ $idModalTitle }}">
                        {{ $title }}
                    </h3>

                    @if($idSubModalTitle || !empty($subTitle))
                        <span class="font-light text-sm text-gray-600" id="{{ $idSubModalTitle }}">{{ $subTitle }}</span>
                    @endif
                </div>
            </div>
            <button @click="open = false" class="text-gray-600 hover:text-gray-400 hover:cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        {{ $slot }}
    </div>
</div>
