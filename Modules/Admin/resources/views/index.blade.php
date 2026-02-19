@extends('layouts.dashboard')
@section('title', 'POS Campagna')

@section('content')
    <div class="max-w-5xl mt-7 mx-auto">
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-foreground mb-2">
                Selamat Datang di Campagna POS
            </h2>
            <p class="text-muted-foreground">
                Pilih modul untuk memulai
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('core') }}">
                <div class="group cursor-pointer transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-1 border border-gray-300 bg-card rounded-xl">
                    <div class="p-8 flex items-center gap-6">
                        <div class="h-16 w-16 rounded-xl bg-slate-600 flex items-center justify-center transition-transform group-hover:scale-110">
                            <i class="text-white fa-solid fa-cog"></i>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold text-card-foreground mb-1">
                                Core
                            </h3>
                            <p class="text-muted-foreground text-sm">
                                Pengaturan inti sistem
                            </p>
                        </div>
                    </div>
                </div>
            </a>
            <a href="{{ route('management') }}">
                <div class="group cursor-pointer transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-1 border border-gray-300 bg-card rounded-xl">
                    <div class="p-8 flex items-center gap-6">
                        <div class="h-16 w-16 rounded-xl bg-blue-600 flex items-center justify-center transition-transform group-hover:scale-110">
                            <i class="text-white fa-solid fa-chart-line"></i>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold text-card-foreground mb-1">
                                Manajemen
                            </h3>
                            <p class="text-muted-foreground text-sm">
                                Laporan & pengaturan
                            </p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>
@endsection
