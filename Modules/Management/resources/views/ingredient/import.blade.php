@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'ingredient-receipt',
    'activeSubmenu' => 'ingredient'
])
@section('title', 'Bahan & Resep')

@section('content')
    <div x-data="{ tab: 'raw' }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Import</h2>
        </div>

        <form action="{{ route('') }}" method="post">
            <input type="file" name="file" id="">
            <button type="submit">Submit</button>
        </form>
    </div>
@endsection
