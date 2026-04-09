<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen w-full flex items-center justify-center bg-gray-50 relative overflow-hidden" style="min-height: 100vh;">
            <!-- Fondo patrón hojas SVG -->
            <svg class="absolute inset-0 w-full h-full" style="z-index:0; min-height:100vh; min-width:100vw;" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <defs>
                    <pattern id="leafPattern" patternUnits="userSpaceOnUse" width="80" height="80">
                        <g>
                            <path d="M40 10 Q45 40 40 70 Q35 40 40 10 Z" fill="none" stroke="#14532d" stroke-width="2"/>
                            <ellipse cx="40" cy="40" rx="10" ry="25" fill="none" stroke="#14532d" stroke-width="1.5"/>
                        </g>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="#f8fafc"/>
                <rect width="100%" height="100%" fill="url(#leafPattern)" fill-opacity="0.13"/>
            </svg>

            <!-- Contenedor centrado vertical y horizontal -->
            <div class="relative z-10 flex flex-col items-center justify-center w-full" style="min-height: 100vh;">
                <div class="bg-white bg-opacity-95 shadow-lg rounded-lg px-8 py-6 mb-2 max-w-2xl w-full flex flex-col items-center">
                    <h1 style="font-size: 2.5rem; line-height: 1.2;" class="md:text-6xl font-extrabold text-gray-800 uppercase tracking-wide text-center">Sistema de Valoración de Daños Ambientales</h1>
                    <p class="text-base text-gray-600 mt-4 text-center">Plataforma de apoyo para la evaluación pericial de delitos medioambientales</p>
                </div>
                <!-- Formulario login -->
                <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-2xl overflow-hidden sm:rounded-lg border border-eco-200">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
