@extends('layouts.admin_crm')
@section('title', 'Tareas — CRM Duna Makai')
@section('page_title', 'Tareas')
@section('page_breadcrumb', 'Equipo · Tareas del día')
@php $activeRoute = 'crm.tareas'; @endphp

@section('content')
@php
    $pendientes = ($items ?? collect())->filter(fn($t) => $t->status !== 'completada');
    $completadas = ($items ?? collect())->where('status', 'completada');
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $pendientes->count() }} tareas pendientes</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-nueva-tarea').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nueva tarea') }}</button>
        </div>
    </div>

    <div class="flex items-center gap-2">
        @foreach (['todos' => 'Todos','alta' => 'Alta prioridad','media' => 'Media','baja' => 'Baja'] as $slug => $label)
            <a href="?filtro={{ $slug }}" class="crm-tab {{ (($filtro ?? 'todos') === $slug) ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="crm-card overflow-hidden">
        <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
            <div class="text-[13px] font-bold text-ink-700">{{ __('Pendientes') }}</div>
            <span class="crm-pill bg-err-soft text-err">{{ $pendientes->count() }}</span>
        </div>
        <div class="divide-y divide-ink-100">
            @forelse($pendientes as $t)
                @php
                    $prioColor = ['alta' => 'err','media' => 'warn','baja' => 'ink-400'];
                    $c = $prioColor[$t->priority] ?? 'ink-400';
                @endphp
                <div class="px-5 py-3 flex items-center gap-3">
                    <form method="POST" action="{{ route('admin.crm.tareas.complete', $t->id) }}" class="m-0">@csrf
                        <button type="submit"><input type="checkbox" class="w-4 h-4 accent-brand" onclick="this.form.submit()"></button>
                    </form>
                    <span class="dot bg-{{ $c }}"></span>
                    <div class="flex-1">
                        <div class="text-[13px] font-medium text-ink-900">{{ $t->title }}</div>
                        <div class="text-[11px] text-ink-500 mt-0.5"><i class="pi pi-user text-[9px]"></i> {{ $t->responsible ?? 'Sin asignar' }}</div>
                    </div>
                    <span class="text-[11px] text-ink-500">{{ $t->due_label ?? optional($t->due_date)->format('d/m') }}</span>
                    <form method="POST" action="{{ route('admin.crm.tareas.delete', $t->id) }}" class="m-0">@csrf @method('DELETE')
                        <button type="submit" class="text-ink-400 hover:text-err"><i class="pi pi-trash text-[11px]"></i></button>
                    </form>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-[12px] text-ink-500">{{ __('No hay tareas pendientes.') }}</div>
            @endforelse
        </div>
    </div>

    @if($completadas->count())
    <div class="crm-card overflow-hidden">
        <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
            <div class="text-[13px] font-bold text-ink-700">{{ __('Completadas') }}</div>
            <span class="crm-pill bg-ink-200 text-ink-600">{{ $completadas->count() }}</span>
        </div>
        <div class="divide-y divide-ink-100">
            @foreach($completadas as $t)
                <div class="px-5 py-3 flex items-center gap-3">
                    <input type="checkbox" checked class="w-4 h-4 accent-brand">
                    <span class="dot bg-ink-300"></span>
                    <div class="flex-1">
                        <div class="text-[13px] font-medium text-ink-400 line-through">{{ $t->title }}</div>
                        <div class="text-[11px] text-ink-400 mt-0.5"><i class="pi pi-user text-[9px]"></i> {{ $t->responsible ?? 'Para mí' }}</div>
                    </div>
                    <span class="text-[11px] text-ink-400">{{ __('Completado') }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<dialog id="modal-nueva-tarea" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.tareas.store') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">@csrf
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900">{{ __('Nueva tarea') }}</div>
        <div class="p-6 space-y-3">
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Título') }}</label><input type="text" name="title" required class="crm-input pl-3 mt-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Responsable') }}</label><input type="text" name="responsible" required class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Área') }}</label><input type="text" name="area" class="crm-input pl-3 mt-1"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha límite') }}</label><input type="date" name="due_date" required value="{{ now()->toDateString() }}" class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Prioridad') }}</label><select name="priority" required class="crm-input pl-3 mt-1"><option value="alta">Alta</option><option value="media" selected>{{ __('Media') }}</option><option value="baja">{{ __('Baja') }}</option></select></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Expediente') }}</label>
                    <select name="reservation_id" class="crm-input pl-3 mt-1">
                        <option value="">{{ __('Sin expediente') }}</option>
                        @foreach(($reservations ?? collect()) as $r)
                            <option value="{{ $r->id }}">{{ $r->first_name }} {{ $r->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Proyecto') }}</label>
                    <select name="project_id" class="crm-input pl-3 mt-1">
                        <option value="">{{ __('Sin proyecto') }}</option>
                        @foreach(($projects ?? collect()) as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Notas') }}</label><textarea name="notes" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea></div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Crear tarea') }}</button>
        </div>
    </form>
</dialog>
@endsection
