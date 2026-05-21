@extends('layouts.client')
@section('title', 'Bienvenido — MAKAI')
@section('page_title', 'Bienvenido')
@section('page_breadcrumb', 'Aún sin propiedad')
@php $activeRoute = 'mi-propiedad'; @endphp

@section('content')
<div class="p-7">
    <div class="cli-card p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-ink-100 flex items-center justify-center text-ink-500 mx-auto">
            <i class="pi pi-home text-[26px]"></i>
        </div>
        <h2 class="font-display text-[20px] font-semibold text-ink-950 mt-5">Aún no tienes una propiedad asociada</h2>
        <p class="text-[13px] text-ink-500 mt-2 max-w-md mx-auto">Para acceder a tu expediente, contáctanos para procesar tu reserva. Nuestro equipo te asistirá en el proceso.</p>
        <a href="/" class="cli-btn cli-btn-primary mt-5 inline-flex">Ver listado de unidades</a>
    </div>
</div>
@endsection
