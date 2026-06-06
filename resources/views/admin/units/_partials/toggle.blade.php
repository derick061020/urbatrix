{{--
    A CRM-styled labeled toggle (checkbox + hidden zero input).
    Vars: $name, $label, $checked (bool), $description (optional)
--}}
@php $tid = 'tg-'.$name.'-'.uniqid(); @endphp
<label for="{{ $tid }}" class="ub-toggle">
    <input type="hidden" name="{{ $name }}" value="0">
    <input id="{{ $tid }}" type="checkbox" name="{{ $name }}" value="1" {{ ($checked ?? false) ? 'checked' : '' }} class="ub-toggle__input sr-only">
    <span class="ub-toggle__track"><span class="ub-toggle__knob"></span></span>
    <span class="flex flex-col">
        <span class="text-[12px] font-semibold text-ink-700">{{ $label }}</span>
        @if(!empty($description))
            <span class="text-[11px] text-ink-500 mt-0.5">{{ $description }}</span>
        @endif
    </span>
</label>
