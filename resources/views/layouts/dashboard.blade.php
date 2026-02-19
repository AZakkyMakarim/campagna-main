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


<div class="min-h-screen flex flex-col bg-background">

    {{-- Header --}}
    <header class="bg-card border-b border-gray-300 px-6 py-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-foreground font-serif">
                {{ @\Illuminate\Support\Facades\Auth::user()->businessProfile->name }}
            </h1>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-medium text-foreground">
                            {{ auth()->user()->name ?? 'Welcome!' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ parse_date_full(now()) }} : {{ parse_time_hm(now()) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <main class="flex-1 bg-gray-100 text-gray-900 overflow-y-auto">
        <div class="container-fluid mx-auto">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-300 py-3">
        <div class="text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} XylvaCode. All rights reserved.
        </div>
    </footer>

</div>


@vite('resources/js/app.js')
{{--<x-alert />--}}
@stack('js')

<script !src="">
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
