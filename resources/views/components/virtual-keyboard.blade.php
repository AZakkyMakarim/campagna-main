<div
    x-show="keyboardOpen"
    class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50
       bg-white/95 backdrop-blur
       rounded-xl border shadow-2xl
       w-[900px] max-w-[95vw]"
>

    <!-- HEADER -->
    <div class="flex items-center justify-between px-4 py-2 border-b">
                <span class="text-sm font-semibold text-gray-600">
                    Virtual Keyboard
                </span>

        <button
            @click="keyboardOpen = false"
            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-200 transition"
        >
            <i class="fa fa-times text-gray-600"></i>
        </button>
    </div>

    <!-- BODY -->
    <div class="p-4 space-y-3 text-center">

        <!-- ROW 1 -->
        <div class="grid grid-cols-10 gap-2">
            <template x-for="key in ['1','2','3','4','5','6','7','8','9','0']">
                <button
                    @click="pressKey(key)"
                    class="key-btn w-full"
                    x-text="key"
                ></button>
            </template>
        </div>

        <!-- ROW 2 -->
        <div class="grid grid-cols-10 gap-2">
            <template x-for="key in ['Q','W','E','R','T','Y','U','I','O','P']">
                <button
                    @click="pressKey(key)"
                    class="key-btn w-full"
                    x-text="key"
                ></button>
            </template>
        </div>

        <!-- ROW 3 -->
        <div class="grid grid-cols-11 gap-2 justify-center">
            <div></div>

            <template x-for="key in ['A','S','D','F','G','H','J','K','L']">
                <button
                    @click="pressKey(key)"
                    class="key-btn w-full"
                    x-text="key"
                ></button>
            </template>

            <div></div>
        </div>

        <!-- ROW 4 -->
        <div class="grid grid-cols-11 gap-2 justify-center">
            <div></div>
            <div></div>

            <template x-for="key in ['Z','X','C','V','B','N','M']">
                <button
                    @click="pressKey(key)"
                    class="key-btn w-full"
                    x-text="key"
                ></button>
            </template>

            <div></div>
            <div></div>
        </div>

        <!-- ROW 5 -->
        <div class="grid grid-cols-10 gap-2">
            <button @click="clearSearch()" class="key-btn col-span-2">CLR</button>
            <button @click="pressKey(' ')" class="key-btn col-span-6">Space</button>
            <button @click="backspace()" class="key-btn col-span-2">⌫</button>
        </div>

    </div>
</div>

@push('css')
    <style>
        .key-btn {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 18px;
            font-weight: 600;
            background: white;
        }
        .key-btn:hover {
            background: #fff7ed;
        }
    </style>
@endpush
