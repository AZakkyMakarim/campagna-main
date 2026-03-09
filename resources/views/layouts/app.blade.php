<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS')</title>
{{--    <link rel="icon" href="favicon.svg" type="image/svg+xml">--}}
{{--    <link rel="icon" href="favicon.ico" sizes="any">--}}
    @vite('resources/css/app.css')
    @stack('css')
</head>
<body class="bg-gray-50 text-gray-900">

<div x-data="{ activeModule: '{{ @$activeModule }}', activeMenu: '{{ @$activeMenu }}', activeSubmenu: '{{ @$activeSubmenu }}' }" class="min-h-screen flex flex-col bg-background">
    <div class="flex flex-1 overflow-hidden" x-data="{ sidebarOpen: true }">
        <aside
            x-show="sidebarOpen && activeModule"
            x-transition
            class="w-64 p-4 border-r border-gray-300 bg-white flex flex-col"
        >
            <div class="flex-1 overflow-y-auto">
                <template x-if="activeModule === 'core'">
                    <div class="flex flex-col space-y-2">
                        <h4 class="uppercase mb-3 h-8 flex items-center font-bold justify-center">Core</h4>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('core.business-profile') }}"
                               @click="activeMenu = 'business-profile'"
                               :class="activeMenu === 'business-profile' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-building"></i>
                                <span>Profil Bisnis</span>
                            </a>

                            <a href="{{ route('core.outlet') }}"
                               @click="activeMenu = 'outlet'"
                               :class="activeMenu === 'outlet' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-store"></i>
                                <span>Outlet</span>
                            </a>

                            <a href="{{ route('core.order-type') }}"
                               @click="activeMenu = 'order-type'"
                               :class="activeMenu === 'order-type' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-clipboard-list"></i>
                                <span>Jenis Order</span>
                            </a>

                            <a href="{{ route('core.user-role') }}"
                               @click="activeMenu = 'user-role'"
                               :class="activeMenu === 'user-role' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-users"></i>
                                <span>User & Role</span>
                            </a>

                            <a href="{{ route('core.payment-method') }}"
                               @click="activeMenu = 'payment-method'"
                               :class="activeMenu === 'payment-method' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-receipt"></i>
                                <span>Metode Pembayaran</span>
                            </a>

                            <a href="{{ route('core.tax-rule') }}"
                               @click="activeMenu = 'tax-rule'"
                               :class="activeMenu === 'tax-rule' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-percent"></i>
                                <span>Pajak</span>
                            </a>

                            <a href="{{ route('core.printer-struck') }}"
                               @click="activeMenu = 'printer-struck'"
                               :class="activeMenu === 'printer-struck' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-print"></i>
                                <span>Printer & Struk</span>
                            </a>

{{--                            <a href=""--}}
{{--                               @click="activeSubmenu = 'settings'"--}}
{{--                               :class="activeSubmenu === 'settings' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"--}}
{{--                               class="flex items-center gap-2 px-3 py-2 rounded-xl">--}}
{{--                                <i class="fa fa-gear"></i>--}}
{{--                                <span>Pengaturan Umum</span>--}}
{{--                            </a>--}}
                        </div>
                    </div>
                </template>

                <template x-if="activeModule === 'management'">
                    <div class="flex flex-col space-y-2">
                        <h4 class="uppercase mb-3 h-8 flex items-center font-bold justify-center">Manajemen</h4>

                        <div class="flex-1 overflow-y-auto py-4 text-sm">
                            <ul class="space-y-1">
                                <li>
                                    <a
                                        href="{{ route('management') }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-orange-500 hover:text-white"
                                        :class="{ 'bg-orange-500 text-white': activeMenu === 'dashboard' }"
                                        @click="activeMenu = 'dashboard'"
                                    >
                                        <i class="fa fa-home"></i>
                                        <span class="font-medium">Dashboard</span>
                                    </a>
                                </li>

                                <li x-data="{ open: activeMenu === 'ingredient-receipt' }">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors"
                                        :class="open ? 'text-orange-600' : 'text-gray-700'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <i class="fa fa-box-open"></i>
                                            <span class="font-medium">Bahan & Resep</span>
                                        </div>
                                        <i :class="open ? 'fa fa-chevron-down' : 'fa fa-chevron-right'"></i>
                                    </button>

                                    <ul x-show="open" x-transition class="mt-1 ml-4 pl-4 border-l border-gray-300 space-y-1">
                                        <li>
                                            <a
                                                href="{{ route('management.ingredient') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'ingredient' }"
                                                @click="activeSubmenu = 'ingredient'"
                                            >
                                                Bahan
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.recipe') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'receipt' }"
                                                @click="activeSubmenu = 'receipt'"
                                            >
                                                Resep
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li x-data="{ open: activeMenu === 'inventory' }">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors"
                                        :class="open ? 'text-orange-600' : 'text-gray-700'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <i class="fa fa-boxes-stacked"></i>
                                            <span class="font-medium">Inventory</span>
                                        </div>
                                        <i :class="open ? 'fa fa-chevron-down' : 'fa fa-chevron-right'"></i>
                                    </button>

                                    <ul x-show="open" x-transition class="mt-1 ml-4 pl-4 border-l border-gray-300 space-y-1">
                                        <li>
                                            <a
                                                href="{{ route('management.inventory.stock') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'stock' }"
                                                @click="activeSubmenu = 'stock'"
                                            >
                                                Stok
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li x-data="{ open: activeMenu === 'purchasing' }">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors"
                                        :class="open ? 'text-orange-600' : 'text-gray-700'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <i class="fa fa-truck-fast"></i>
                                            <span class="font-medium">Purchasing</span>
                                        </div>
                                        <i :class="open ? 'fa fa-chevron-down' : 'fa fa-chevron-right'"></i>
                                    </button>

                                    <ul x-show="open" x-transition class="mt-1 ml-4 pl-4 border-l border-gray-300 space-y-1">
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.unit-conversion') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'unit-conversion' }"
                                                @click="activeSubmenu = 'unit-conversion'"
                                            >
                                                Konversi
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.vendor') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'vendor' }"
                                                @click="activeSubmenu = 'vendor'"
                                            >
                                                Vendor
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.purchase') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'purchase' }"
                                                @click="activeSubmenu = 'purchase'"
                                            >
                                                Pembelian
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.production') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'production' }"
                                                @click="activeSubmenu = 'production'"
                                            >
                                                Produksi
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li x-data="{ open: activeMenu === 'menu' }">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors"
                                        :class="open ? 'text-orange-600' : 'text-gray-700'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <i class="fa fa-bowl-food"></i>
                                            <span class="font-medium">Menu & Paket</span>
                                        </div>
                                        <i :class="open ? 'fa fa-chevron-down' : 'fa fa-chevron-right'"></i>
                                    </button>

                                    <ul x-show="open" x-transition class="mt-1 ml-4 pl-4 border-l border-gray-300 space-y-1">
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.menu.single') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'single' }"
                                                @click="activeSubmenu = 'single'"
                                            >
                                                Menu
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.menu.bundle') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'bundle' }"
                                                @click="activeSubmenu = 'bundle'"
                                            >
                                                Paket
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li x-data="{ open: activeMenu === 'sales' }">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors"
                                        :class="open ? 'text-orange-600' : 'text-gray-700'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <i class="fa fa-layer-group"></i>
                                            <span class="font-medium">Analisa Kategori</span>
                                        </div>
                                        <i :class="open ? 'fa fa-chevron-down' : 'fa fa-chevron-right'"></i>
                                    </button>

                                    <ul x-show="open" x-transition class="mt-1 ml-4 pl-4 border-l border-gray-300 space-y-1">
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.sales.category_analysis.nota') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'category_analysis_nota' }"
                                                @click="activeSubmenu = 'category_analysis_nota'"
                                            >
                                                Nota
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.sales.category_analysis.menu') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'category_analysis_menu' }"
                                                @click="activeSubmenu = 'category_analysis_menu'"
                                            >
                                                Menu
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.sales.category_analysis.payment_method') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'category_analysis_payment_method' }"
                                                @click="activeSubmenu = 'category_analysis_payment_method'"
                                            >
                                                Metode Pembayaran
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('management.purchasing.sales.category_analysis.order') }}"
                                                class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-orange-500 hover:text-white"
                                                :class="{ 'bg-orange-500 text-white': activeSubmenu === 'category_analysis_order' }"
                                                @click="activeSubmenu = 'category_analysis_order'"
                                            >
                                                Order
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>

                <template x-if="activeModule === 'transaction'">
                    <div class="flex flex-col space-y-2">
                        <h4 class="uppercase mb-3 h-8 flex items-center font-bold justify-center">Kasir</h4>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('transaction') }}"
                               @click="activeMenu = 'dashboard'"
                               :class="activeMenu === 'dashboard' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-dashboard"></i>
                                <span>Dashboard</span>
                            </a>
                        </div>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('transaction.order') }}"
                               @click="activeMenu = 'order'"
                               :class="activeMenu === 'order' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-cart-shopping"></i>
                                <span>Order</span>
                            </a>
                        </div>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('transaction.list-order') }}"
                               @click="activeMenu = 'list-order'"
                               :class="activeMenu === 'list-order' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-list-1-2"></i>
                                <span>List Order</span>
                            </a>
                        </div>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('transaction.kitchen-display') }}"
                               @click="activeMenu = 'kitchen-display'"
                               :class="activeMenu === 'kitchen-display' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-kitchen-set"></i>
                                <span>Kitchen Display</span>
                            </a>
                        </div>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('transaction.reservation') }}"
                               @click="activeMenu = 'reservation'"
                               :class="activeMenu === 'reservation' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-calendar-plus"></i>
                                <span>Reservasi</span>
                            </a>
                        </div>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('transaction.shift') }}"
                               @click="activeMenu = 'shift'"
                               :class="activeMenu === 'shift' ? 'bg-orange-600 text-white font-medium' : 'hover:text-white hover:bg-orange-500'"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl">
                                <i class="fa fa-clock"></i>
                                <span>Shift</span>
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            @if(subdomain() == 'admin')
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <a
                        href="{{ route('admin') }}"
                        class="flex items-center justify-center gap-2 px-3 py-2
                   rounded-xl text-sm font-medium
                   bg-gray-100 text-gray-700
                   hover:bg-gray-900 hover:text-white transition"
                    >
                        <i class="fa fa-arrow-left"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            @endif
        </aside>

        <main
            class="flex-1 text-gray-900 overflow-y-auto transition-all"
            :class="sidebarOpen ? 'ml-0' : 'ml-0'"
        >
            <header class="h-16 px-6 shadow-md flex items-center bg-white">
                <div class="flex items-center justify-between w-full">
                    <!-- LEFT -->
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-lg hover:bg-gray-100 transition"
                            title="Toggle Menu"
                        >
                            <i class="fa fa-bars text-xl"></i>
                        </button>

                        <h1 class="text-2xl font-bold text-gray-900 font-serif leading-none">
                            {{ @Auth::user()->businessProfile->name ?? 'Business Profile' }}
                        </h1>

                        <!-- Divider -->
                        <span class="text-gray-400 text-xl leading-none">/</span>

                        <!-- Outlet Switcher -->
                        <form action="{{ route('admin.switch-outlet') }}" method="POST" class="flex items-center my-auto">
                            @csrf
                            <div class="relative flex">
                                <select
                                    name="outlet_id"
                                    onchange="this.form.submit()"
                                    class="select2"
                                >
                                    <option value="">Outlet</option>
                                    @foreach(\App\Models\Outlet::get() as $outlet)
                                        <option value="{{ $outlet->id }}" @selected(session('active_outlet_id') == $outlet->id)  >
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center gap-4">
                        <div class="text-right leading-tight">
                            <p class="text-sm font-bold text-foreground">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ parse_date_full(now()) }} • {{ parse_time_hm(now()) }}
                            </p>
                        </div>

                        <!-- LOGOUT BUTTON -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg
                   bg-red-500 text-white text-xs font-semibold
                   hover:bg-red-600 transition"
                                title="Logout"
                            >
                                <i class="fa fa-right-from-bracket"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="container-fluid m-5">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Footer --}}
    <footer class="border-t border-gray-300 py-3">
        <div class="text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} XylvaCode. All rights reserved.
        </div>
    </footer>
</div>


@vite('resources/js/app.js')
<x-alert />
<x-import-errors-modal />
@stack('js')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('menu', () => ({
            activeMenu: 'dashboard', // default active menu
            activeSubmenu: '', // default active submenu

            init() {
                // Initial checks to set submenu state based on URL or initial state
            }
        }));
    });
</script>
<script !src="">
    document.addEventListener('DOMContentLoaded', () => {
        const $dropify = $('.dropify')

        if ($dropify.length) {
            $dropify.each(function () {
                if (!$(this).data('dropify')) {
                    $(this).dropify()
                }
            })
        }

        if (!window.$ || !$.fn.select2) {
            console.error('Select2 NOT READY');
            return;
        }

        $('.select2').each(function () {
            $(this).select2({
                placeholder: $(this).data('placeholder') || 'Pilih data',
                closeOnSelect: false,
                width: '100%'
            });
        });

        $(document).on('select2:select', '.select2', function () {
            // hanya auto-close untuk single select
            if (!$(this).prop('multiple')) {
                $(this).select2('close');
            }
        });
    })

    function previewImage(url, title = '') {
        Swal.fire({
            title: title,
            imageUrl: url,
            imageAlt: title,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            background: '#111827',
            color: '#ffffff',
            customClass: {
                image: 'rounded-lg shadow-lg'
            }
        });
    }
</script>

</body>
</html>
