@extends('layouts.main_user')

@section('title', 'Dashboard - Portal Cliente')

@php
    $activeRoute = 'dashboard';
@endphp

@section('content')
<div class="w-full bg-surface-50 px-2 py-4 sm:p-10 overflow-auto flex-1">
    <div>
        <header class="mb-4">
            <h1 class="text-3xl font-semibold text-surface-700">Portal Cliente</h1>
            <p class="text-surface-700 text-base">Bienvenido a tu portal personal</p>
        </header>

        <!-- Test Sidebar Collapse -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-surface-700 mb-4">Test de Sidebar Colapsable</h2>
            <p class="text-surface-600 mb-4">
                Haz clic en la X (o menú hamburguesa cuando está colapsado) en la esquina superior derecha del sidebar para probar la funcionalidad de colapsar.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded p-4">
                <p class="text-blue-800">
                    <strong>Funcionalidades implementadas:</strong>
                </p>
                <ul class="list-disc list-inside text-blue-700 mt-2">
                    <li>Sidebar se colapsa a 60px de ancho</li>
                    <li>Texto de navegación se oculta suavemente</li>
                    <li>Logo se reduce de tamaño</li>
                    <li>Botón CTA se oculta</li>
                    <li>Secciones de navegación se ocultan</li>
                    <li>Iconos se centran</li>
                    <li>Icono X cambia a menú hamburguesa</li>
                    <li>Transiciones suaves de 300ms</li>
                </ul>
            </div>
        </div>

        <!-- Welcome Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900">Progreso</h3>
                </div>
                <p class="text-gray-600">Revisa el estado de tu compra</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900">Documentos</h3>
                </div>
                <p class="text-gray-600">Gestiona tu documentación</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900">Pagos</h3>
                </div>
                <p class="text-gray-600">Consulta tus pagos</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900">Mensajes</h3>
                </div>
                <p class="text-gray-600">Comunicación directa</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Actividad Reciente</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <p class="ml-3 text-gray-600">Bienvenido a tu portal cliente</p>
                        <span class="ml-auto text-sm text-gray-500">Ahora</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <p class="ml-3 text-gray-600">Sidebar colapsable implementado</p>
                        <span class="ml-auto text-sm text-gray-500">Reciente</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
