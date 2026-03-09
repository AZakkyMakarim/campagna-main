@if(session()->has('import_errors_messages'))
    <x-modal id="modal-import-errors" title="Hasil Import Data" size="2xl" icon="fa-exclamation-triangle">
        <div class="p-6">
            <!-- Summary Header -->
            <div
                class="mb-5 flex flex-col sm:flex-row sm:items-center gap-4 bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex-1 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 text-green-600">
                        <i class="fa fa-check text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Berhasil Diimport</p>
                        <p class="text-xl font-bold text-gray-800">{{ session('import_success_count', 0) }} Data</p>
                    </div>
                </div>

                <div class="hidden sm:block w-px h-12 bg-gray-300"></div>

                <div class="flex-1 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-red-100 text-red-600">
                        <i class="fa fa-times text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Gagal / Dilewati</p>
                        <p class="text-xl font-bold text-gray-800">
                            {{ session('import_errors_count', count(session('import_errors_messages'))) }} Data</p>
                    </div>
                </div>
            </div>

            <!-- Error List -->
            <div class="space-y-2">
                <h4 class="text-sm font-bold text-gray-700 mb-3 border-b border-gray-200 pb-2">Detail Error:</h4>
                <div class="bg-red-50/50 rounded-xl border border-red-100 p-2 overflow-y-auto" style="max-height: 45vh;">
                    <ul class="divide-y divide-red-100/60">
                        @foreach((array) session('import_errors_messages') as $index => $message)
                            <li class="py-2.5 px-3 flex gap-3 text-sm text-red-700">
                                <span class="font-mono text-xs text-red-400 mt-0.5 w-6 text-right">{{ $index + 1 }}.</span>
                                <span class="flex-1">{{ $message }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end px-5 py-4 bg-gray-50 rounded-b-xl border-t border-gray-200">
            <button type="button" @click="open = false"
                class="px-5 py-2.5 bg-gray-800 text-white rounded-lg font-medium hover:bg-gray-700 transition">
                Tutup Peringatan
            </button>
        </div>
    </x-modal>

    <!-- Auto-open trigger script -->
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-import-errors' }));
            }, 300);
        });
    </script>
@endif