{{--
    A CRM-styled labeled toggle (checkbox + hidden zero input).
    Vars: $name, $label, $checked (bool), $description (optional)
--}}
@php $tid = 'tg-'.$name.'-'.uniqid(); @endphp
<label for="{{ $tid }}" class="flex items-start gap-3 cursor-pointer select-none">
    <input type="hidden" name="{{ $name }}" value="0">
    <input id="{{ $tid }}" type="checkbox" name="{{ $name }}" value="1" {{ ($checked ?? false) ? 'checked' : '' }} class="peer sr-only">
    <span class="relative w-9 h-5 rounded-full bg-ink-200 peer-checked:bg-ok transition-colors shrink-0 mt-0.5">
        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-transform peer-checked:translate-x-4 shadow-sm"></span>
    </span>
    <span class="flex flex-col">
        <span class="text-[12px] font-semibold text-ink-700">{{ $label }}</span>
        @if(!empty($description))
            <span class="text-[11px] text-ink-500 mt-0.5">{{ $description }}</span>
        @endif
    </span>
</label>
