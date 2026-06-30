{{--
    Dropdown personalizado (reemplaza al <select> nativo) para un look pulcro.
    Comparte estilos/JS con el layout admin_crm (.crm-select).

    Vars:
      $name        string  — name del input oculto que viaja en el form
      $options     array   — asociativo [value => label]
      $selected    mixed   — valor seleccionado actual (string|int|null)
      $placeholder string  — opcional, texto cuando no hay valor (default '—')
      $required    bool    — opcional, marca el input (validación server-side)
--}}
@php
    $selected      = (string) ($selected ?? '');
    $placeholder   = $placeholder ?? '—';
    $required      = $required ?? false;
    $hasSelection  = $selected !== '' && array_key_exists($selected, $options);
    $selLabel      = $hasSelection ? $options[$selected] : $placeholder;
@endphp
<div class="crm-select mt-1" data-select data-placeholder="{{ $placeholder }}">
    <input type="hidden" name="{{ $name }}" value="{{ $selected }}" @if($required) required @endif>
    <button type="button" class="crm-select__btn" aria-haspopup="listbox" aria-expanded="false">
        <span class="crm-select__label {{ $hasSelection ? '' : 'is-placeholder' }}" data-select-label>{{ $selLabel }}</span>
        <i class="pi pi-angle-down crm-select__caret"></i>
    </button>
    <div class="crm-select__menu" role="listbox">
        @foreach($options as $val => $label)
            <button type="button" class="crm-select__opt {{ (string) $val === $selected ? 'is-active' : '' }}" role="option" data-value="{{ $val }}">
                <span>{{ $label }}</span>
                <i class="pi pi-check crm-select__check"></i>
            </button>
        @endforeach
    </div>
</div>
