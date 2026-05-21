{{--
    Plan-view hotspot marker — exact paths from Figma (193:12464 / 193:12465 / 193:12466).
    Required: $state ('default'|'hot'|'reserved'|'sold'|'2nd'), $side ('left'|'right'), $uid (unique id)
--}}
@php
    $palette = [
        'default'  => ['fill' => '#5c7c68', 'r' => '0.361', 'g' => '0.486', 'b' => '0.408'],
        'hot'      => ['fill' => '#f06a23', 'r' => '0.941', 'g' => '0.416', 'b' => '0.137'],
        'reserved' => ['fill' => '#cd9600', 'r' => '0.804', 'g' => '0.588', 'b' => '0.000'],
        'sold'     => ['fill' => '#a4a4a4', 'r' => '0.643', 'g' => '0.643', 'b' => '0.643'],
        '2nd'      => ['fill' => '#3b82f6', 'r' => '0.231', 'g' => '0.510', 'b' => '0.965'],
    ];
    $p = $palette[$state ?? 'default'] ?? $palette['default'];
    $fid = 'mkf_'.$uid;
    $gid = 'mkg_'.$uid;
    // Build the feColorMatrix RGB string once
    $shadowRgb = "0 0 0 0 {$p['r']} 0 0 0 0 {$p['g']} 0 0 0 0 {$p['b']}";

    // The two ribbon orientations exported from Figma (193:12464 = LEFT, 193:12466 = RIGHT)
    $pathLeft  = 'M49.2412 2.48242C69.1235 2.48242 85.2412 18.6002 85.2412 38.4824C85.2412 58.3647 69.1235 74.4824 49.2412 74.4824C29.359 74.4823 13.2412 58.3646 13.2412 38.4824C13.2412 33.5063 14.2507 28.7659 16.0762 24.4549C17.3694 21.4011 17.9368 18.0197 17.0456 14.8254L14.6387 6.19824C14.2396 4.76836 15.5374 3.44166 16.9756 3.80957L26.2675 6.18622C29.3329 6.97027 32.5481 6.42266 35.4711 5.21123C39.713 3.45318 44.3636 2.48245 49.2412 2.48242Z';
    $pathRight = 'M49.2412 2.48242C29.359 2.48242 13.2412 18.6002 13.2412 38.4824C13.2412 58.3647 29.359 74.4824 49.2412 74.4824C69.1234 74.4823 85.2412 58.3646 85.2412 38.4824C85.2412 33.5063 84.2317 28.7659 82.4062 24.4549C81.1131 21.4011 80.5457 18.0197 81.4369 14.8254L83.8438 6.19824C84.2428 4.76836 82.945 3.44166 81.5068 3.80957L72.2149 6.18622C69.1495 6.97027 65.9343 6.42266 63.0113 5.21123C58.7695 3.45318 54.1188 2.48245 49.2412 2.48242Z';
    $path = (($side ?? 'left') === 'right') ? $pathRight : $pathLeft;
@endphp
<svg viewBox="0 0 99 134" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" class="fg-plan-marker-svg">
  <g filter="url(#{{ $fid }})">
    <path d="{{ $path }}" fill="{{ $p['fill'] }}"/>
    <path d="{{ $path }}" fill="url(#{{ $gid }})" fill-opacity="0.16"/>
  </g>
  <circle cx="49.2414" cy="38.4826" r="31.8621" stroke="white" stroke-opacity="0.2" stroke-width="1.65517"/>
  <defs>
    <filter id="{{ $fid }}" x="-0.000168" y="-0.000337" width="98.4828" height="133.241" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
      <feFlood flood-opacity="0" result="bg"/>
      <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="ha1"/>
      <feOffset dy="1.65517"/>
      <feGaussianBlur stdDeviation="2.06897"/>
      <feColorMatrix type="matrix" values="{{ $shadowRgb }} 0 0 0 0.31 0"/>
      <feBlend mode="normal" in2="bg" result="e1"/>

      <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="ha2"/>
      <feOffset dy="7.44828"/>
      <feGaussianBlur stdDeviation="3.72414"/>
      <feColorMatrix type="matrix" values="{{ $shadowRgb }} 0 0 0 0.27 0"/>
      <feBlend mode="normal" in2="e1" result="e2"/>

      <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="ha3"/>
      <feOffset dy="16.5517"/>
      <feGaussianBlur stdDeviation="4.96552"/>
      <feColorMatrix type="matrix" values="{{ $shadowRgb }} 0 0 0 0.16 0"/>
      <feBlend mode="normal" in2="e2" result="e3"/>

      <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="ha4"/>
      <feOffset dy="29.7931"/>
      <feGaussianBlur stdDeviation="5.7931"/>
      <feColorMatrix type="matrix" values="{{ $shadowRgb }} 0 0 0 0.05 0"/>
      <feBlend mode="normal" in2="e3" result="e4"/>

      <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="ha5"/>
      <feOffset dy="45.5172"/>
      <feGaussianBlur stdDeviation="6.62069"/>
      <feColorMatrix type="matrix" values="{{ $shadowRgb }} 0 0 0 0.01 0"/>
      <feBlend mode="normal" in2="e4" result="e5"/>

      <feBlend mode="normal" in="SourceGraphic" in2="e5" result="shape"/>
    </filter>
    <linearGradient id="{{ $gid }}" x1="49.2412" y1="2.48242" x2="49.2412" y2="74.4824" gradientUnits="userSpaceOnUse">
      <stop stop-color="white"/>
      <stop offset="1" stop-color="white" stop-opacity="0"/>
    </linearGradient>
  </defs>
</svg>
