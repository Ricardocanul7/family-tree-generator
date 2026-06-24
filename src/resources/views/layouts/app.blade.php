<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Árbol Familiar')</title>
    @vite(['resources/css/app.css', 'resources/css/family-tree.css'])
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('family-tree.index') }}" class="flex items-center space-x-2 text-xl font-bold text-gray-800">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('Family Tree') }}
                </a>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('family-tree.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('View Tree') }}</a>
                    <a href="/admin" class="text-gray-600 hover:text-gray-900">{{ __('Admin') }}</a>

                    <div class="relative group">
                        <button class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors text-sm font-medium">
                            {{ strtoupper(App::getLocale()) }}
                        </button>
                        <div class="absolute right-0 mt-1 w-28 bg-white rounded-lg shadow-lg border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-20">
                            @foreach (['en' => 'English', 'es' => 'Español', 'pl' => 'Polski'] as $code => $name)
                                <a href="{{ route('language.switch', $code) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 first:rounded-t-lg last:rounded-b-lg {{ App::getLocale() === $code ? 'font-bold bg-gray-50' : '' }}">
                                    {{ $name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" title="{{ __('Toggle theme') }}">
                        <svg class="w-5 h-5 moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg class="w-5 h-5 sun-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/svg2pdf.js@2.2.4/dist/svg2pdf.umd.min.js"></script>
    @vite(['resources/js/app.js', 'resources/js/family-tree.js'])
    @stack('scripts')
</body>
</html>
