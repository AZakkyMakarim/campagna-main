<div class="overflow-hidden">
    <div
        x-data="rolePermissionManager({
            roles: @js($roles),
            permissions: @js($permissions)
        })"
        class="grid grid-cols-12 gap-6"
    >

        {{-- LEFT : ROLE LIST --}}
        <div class="col-span-3">
            <div class="bg-white rounded-xl border p-4 space-y-2">
                <h3 class="font-semibold mb-3">Daftar Role</h3>

                <template x-for="role in roles" :key="role.id">
                    <button
                        @click="selectRole(role)"
                        class="w-full text-left px-4 py-2 rounded-lg"
                        :class="activeRole?.id === role.id
                        ? 'bg-orange-600 text-white'
                        : 'hover:bg-gray-100'"
                        x-text="role.name"
                    ></button>
                </template>
            </div>
        </div>

        {{-- RIGHT : PERMISSION --}}
        <div class="col-span-9">
            <div class="bg-white rounded-xl border p-4">

                <template x-if="!activeRole">
                    <p class="text-gray-400">Pilih role terlebih dahulu</p>
                </template>

                <template x-if="activeRole">
                    <div>
                        <h3 class="font-semibold text-lg mb-1">
                            Permission:
                            <span class="text-orange-600" x-text="activeRole.name"></span>
                        </h3>

                        <p class="text-sm text-gray-500 mb-4" x-text="activeRole.description"></p>

                        <table class="w-full text-sm border">
                            <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Module</th>
                                <th class="p-2">Menu</th>
                            </tr>
                            </thead>

                            <tbody>
                            <template x-for="(items, module) in permissions" :key="module">
                                <tr class="border-t">
                                    {{-- MODULE --}}
                                    <td class="p-3 font-semibold align-top">
                                        <label class="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                @change="toggleModule(module)"
                                                :checked="isModuleChecked(module)"
                                            >
                                            <span x-text="module.toUpperCase()"></span>
                                        </label>
                                    </td>

                                    {{-- MENU --}}
                                    <td class="p-3">
                                        <div class="grid grid-cols-2 gap-2">
                                            <template x-for="perm in items" :key="perm.id">
                                                <label class="flex items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        :value="perm.name"
                                                        x-model="selectedPermissions"
                                                    >
                                                    <span x-text="perm.name.split('.')[1]"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>

                        <div class="mt-4 flex justify-end">
                            <button
                                @click="save()"
                                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500"
                            >
                                Simpan Permission
                            </button>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>
</div>

<x-modal id="modal-form-role" title="Tambah Role" size="md">
    <form method="POST" action="{{ route('core.role.store') }}">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Role</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama role" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" name="description" value="{{ old('code') }}" required placeholder="Masukkan deskripsi" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
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
                Simpan
            </button>
        </div>
    </form>
</x-modal>

@push('js')
    <script>
        function rolePermissionManager({ roles, permissions }) {
            return {
                roles,
                permissions,

                activeRole: null,
                selectedPermissions: [],

                selectRole(role) {
                    this.activeRole = role;

                    fetch("{{ route('core.role.get-role-permission', ':id') }}".replace(':id', this.activeRole.id))
                        .then(r => r.json())
                        .then(res => {
                            this.selectedPermissions = res.payload;
                        });
                },

                toggleModule(module) {
                    const perms = this.permissions[module].map(p => p.name);

                    if (this.isModuleChecked(module)) {
                        this.selectedPermissions =
                            this.selectedPermissions.filter(p => !perms.includes(p));
                    } else {
                        this.selectedPermissions = [
                            ...new Set([...this.selectedPermissions, ...perms])
                        ];
                    }
                },

                isModuleChecked(module) {
                    const perms = this.permissions[module].map(p => p.name);
                    return perms.every(p => this.selectedPermissions.includes(p));
                },

                save() {
                    fetch(
                        "{{ route('core.role.update-permission', ':id') }}".replace(':id', this.activeRole.id),
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                permissions: this.selectedPermissions
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.code === 200) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: res.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                })
                            } else {
                                throw res.message
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', err, 'error')
                        })
                        .finally(() => {
                            this.loading = false
                        })
                }
            }
        }
    </script>
@endpush
