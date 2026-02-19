<div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
    <table class="w-full text-sm text-left">
        <thead class="bg-orange-700 text-white uppercase text-xs">
        <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Nama</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Outlet</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        @foreach($users as $key => $user)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                <td class="px-4 py-3 text-nowrap">{{ $user->name }}</td>
                <td class="px-4 py-3 text-nowrap">{{ $user->email }}</td>
                <td class="px-4 py-3">
                    <ul>
                        @foreach($user->getRoleNames() as $role)
                            <li>{{ $role }}</li>
                        @endforeach
                    </ul>
                </td>
                <td class="px-4 py-3">
                    <ul>
                        @foreach($user->outlets as $outlet)
                            <li>{{ $outlet->name }}</li>
                        @endforeach
                    </ul>
                </td>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            class="sr-only peer"
                            {{ $user->is_active ? 'checked' : '' }}
                            onchange="toggleOutletStatus(this, '{{ route('core.user.update', $user) }}')"
                        >
                        <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-2">
                        <button
                            type="button"
                            data-route="{{ route('core.user-role.update', $user) }}"
                            @click="$dispatch('open-edit', {
                                        user: @js($user),
                                        action: $el.dataset.route
                                    })"
                            class="px-3 py-2 bg-yellow-500 text-white rounded"
                        >
                            <i class="fa fa-pen"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<x-modal id="modal-form" title="Tambah User" size="md">
    <form method="POST" action="{{ route('core.user.store') }}">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama karyawan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('code') }}" required placeholder="Masukkan email karyawan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required placeholder="Passowrd" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Role
                    </label>
                    <div class="relative">
                        <select name="role" class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected((old('role') ?? '') === $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Outlet
                    </label>
                    <div class="relative">
                        <select
                            name="outlet[]"
                            multiple
                            class="select2 w-full"
                            data-placeholder="Pilih outlet"
                        >
                            @foreach($outlets as $outlet)
                                <option
                                    value="{{ $outlet->id }}"
                                    @selected(collect(old('outlet', []))->contains($outlet->id))
                                >
                                    {{ $outlet->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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

<div
    x-data="editUserModal()"
    x-show="open"
    @open-edit.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 flex items-center justify-center z-50"
>

    <div
        class="absolute inset-0 bg-black/80 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300">
            <h3 class="font-semibold text-lg">
                Edit User
            </h3>
            <button @click="open = false" class="text-gray-600 hover:text-gray-400 hover:cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="bg-white w-full max-w-md rounded-xl">

            <form :action="action" method="POST">
                @csrf
                <div class="p-5 text-gray-300">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                            <input type="text" x-model="form.name" name="name" required placeholder="Masukkan nama karyawan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                            <input type="email" x-model="form.email" name="email" required placeholder="Masukkan email karyawan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" placeholder="Password" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Role
                            </label>
                            <div class="relative">
                                <select name="role" x-model="form.role" class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected((old('role') ?? '') === $role->id)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Outlet
                            </label>
                            <div class="relative">
                                <select
                                    name="outlet_ids[]"
                                    x-model="form.outlet"
                                    multiple
                                    class="select2 w-full"
                                    data-placeholder="Pilih outlet"
                                >
                                    @foreach($outlets as $outlet)
                                        <option
                                            value="{{ $outlet->id }}"
                                            @selected(collect(old('outlet', []))->contains($outlet->id))
                                        >
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
        </div>
    </div>


</div>

@push('js')
    <script>
        function toggleOutletStatus(el, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_active: el.checked ? 1 : 0
                })
            })
                .then(res => {
                    if (!res.ok) {
                        throw res;
                    }
                    return res.json();
                })
                .then(res => {
                    // console.log(res.payload); // data utama
                })
                .catch(async err => {
                    el.checked = !el.checked; // rollback toggle

                    let message = 'Terjadi kesalahan';

                    if (err.json) {
                        const e = await err.json();
                        message = e.message ?? message;
                    }

                    alert(message);
                });
        }

        function editUserModal() {
            return {
                open: false,
                action: '',
                form: {
                    name: '',
                    email: '',
                    role: '',
                    outlet: '',
                },

                fill(payload) {
                    const user = payload.user

                    this.open = true
                    this.action = payload.action

                    this.form = {
                        name: user.name,
                        email: user.email,
                        role: user.role,
                        outlet: user.outlet,
                    }
                }
            }
        }
    </script>
@endpush
