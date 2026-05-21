@extends('layouts.admin_crm')
@section('title', 'Nueva unidad — CRM Duna Makai')
@section('page_title', 'Nueva unidad')
@section('page_breadcrumb', 'Proyectos · Unidades · Crear')
@php $activeRoute = 'units'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if($errors->any())
        <div class="px-4 py-3 rounded-lg bg-err-soft border border-err/30 text-err text-[12px] space-y-0.5">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    {{-- Header --}}
    <div class="crm-card p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <a href="{{ route('admin.units') }}" class="w-10 h-10 rounded-full border border-ink-200 flex items-center justify-center text-ink-600 hover:bg-ink-50 shrink-0"><i class="pi pi-arrow-left text-[12px]"></i></a>
        <div class="flex-1 min-w-0">
            <div class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">Nueva unidad</div>
            <div class="text-[20px] font-bold text-ink-950 leading-tight">Crear una unidad</div>
            <div class="text-[12px] text-ink-500 mt-0.5">Completá los datos básicos. Las imágenes se pueden subir tras crearla.</div>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" form="unit-create-form" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Crear unidad</button>
        </div>
    </div>

    {{-- Form --}}
    <form id="unit-create-form" method="POST" action="{{ route('admin.units.store') }}" class="space-y-4">
        @csrf
        @include('admin.units._partials.form_fields', ['unit' => null, 'agents' => $agents ?? collect()])
    </form>

    {{-- Info banner about images --}}
    <div class="crm-card p-4 flex items-start gap-3 bg-info-soft/40 border-info/20">
        <i class="pi pi-info-circle text-info mt-1"></i>
        <div class="text-[12px] text-ink-700">
            Las imágenes y el historial estarán disponibles después de crear la unidad. Vas a ser redirigido a la página de edición tras guardarla.
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-2">
        <a href="{{ route('admin.units') }}" class="crm-btn crm-btn-ghost">Cancelar</a>
        <button type="submit" form="unit-create-form" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Crear unidad</button>
    </div>
</div>
@endsection
