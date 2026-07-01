<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Relatório') - InfoVISA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-white text-gray-900">
    <div class="max-w-[1200px] mx-auto p-6 print:p-0">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
