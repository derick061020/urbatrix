{{-- Un campo del formulario KYC. Parámetros: $name (name del input, con corchetes),
     $key (clave con puntos para old() y errores), $label, $type, $req, $opts, $val. --}}
@php
    $val  = old($key, $val ?? '');
    $id   = 'f_' . str_replace(['.', '[', ']'], '_', $key);
    $err  = $errors->has($key);
@endphp
<div class="cdd-fld {{ in_array($type, ['textarea', 'radio']) ? 'full' : '' }} {{ $err ? 'err' : '' }}">
    <label for="{{ $id }}">{{ __($label) }} @if($req)<span class="req">*</span>@endif</label>
    @if ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" class="cli-input pl-3" @if($req) required @endif>
            <option value="">{{ __('Elige una opción') }}</option>
            @foreach ($opts as $k => $l)<option value="{{ $k }}" @selected($val === $k)>{{ __($l) }}</option>@endforeach
        </select>
    @elseif ($type === 'radio')
        <div class="cdd-opts">
            @foreach ($opts as $k => $l)
                <label class="cdd-opt"><input type="radio" name="{{ $name }}" value="{{ $k }}" @checked($val === $k) @if($req) required @endif> <span>{{ __($l) }}</span></label>
            @endforeach
        </div>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="3" class="cli-input pl-3 py-2" @if($req) required @endif>{{ $val }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" value="{{ $val }}" class="cli-input pl-3"
               type="{{ $type === 'percent' ? 'number' : ($type === 'money' ? 'text' : $type) }}"
               @if($type === 'percent') min="0" max="100" step="0.01" inputmode="decimal" @endif
               @if($type === 'money') inputmode="decimal" @endif
               @if($req) required @endif>
    @endif
    @error($key)<div class="msg">{{ $message }}</div>@enderror
</div>
