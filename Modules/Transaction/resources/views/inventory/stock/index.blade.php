@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'inventory',
    'activeSubmenu' => 'stock'
])
@section('title', 'Stok')

@section('content')
<div x-data="{ tab: 'stock-card' }"
     x-init="
         let navType = 'navigate';
         if (window.performance && window.performance.getEntriesByType) {
             const navEntries = window.performance.getEntriesByType('navigation');
             if (navEntries.length > 0) {
                 navType = navEntries[0].type;
             }
         }

         let isSamePage = false;
         try {
             if (document.referrer) {
                 isSamePage = (new URL(document.referrer)).pathname === window.location.pathname;
             }
         } catch(e) {}

         if (navType === 'reload' || navType === 'back_forward' || isSamePage) {
             tab = localStorage.getItem('activeStockTab') || 'stock-card';
         } else {
             tab = 'stock-card';
         }

         $watch('tab', value => localStorage.setItem('activeStockTab', value));
     ">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Stok</h2>
    </div>

    <div class="flex shadow-lg border border-gray-200 bg-white rounded-xl p-2">
        <button
            @click="tab = 'stock-card'"
            :class="tab === 'stock-card'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
            class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
            Kartu Stok
        </button>

        <button
            @click="tab = 'recap'"
            :class="tab === 'recap'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
            class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
            Rekap Stok
        </button>
    </div>

    <div class="mt-4">
        <div x-show="tab === 'stock-card'">
            @include('transaction::inventory.stock.components.stock_card')
        </div>
        <div x-show="tab === 'recap'">
            @include('transaction::inventory.stock.components.recap')
        </div>
    </div>

</div>
@endsection
