@php
    $palette = [
        'pending'     => ['bg' => '#fef6e6', 'tx' => '#9b6f1d', 'br' => '#e9d3a4', 'lb' => 'Pendiente'],
        'pendiente'   => ['bg' => '#fef6e6', 'tx' => '#9b6f1d', 'br' => '#e9d3a4', 'lb' => 'Pendiente'],
        'generated'   => ['bg' => '#eaf3fb', 'tx' => '#2c6aa0', 'br' => '#bcd6ee', 'lb' => 'Generado'],
        'cargado'     => ['bg' => '#eaf3fb', 'tx' => '#2c6aa0', 'br' => '#bcd6ee', 'lb' => 'Cargado'],
        'signed'      => ['bg' => '#f1ecf8', 'tx' => '#5a3d99', 'br' => '#d6c8ec', 'lb' => 'Firmado'],
        'firmado'     => ['bg' => '#f1ecf8', 'tx' => '#5a3d99', 'br' => '#d6c8ec', 'lb' => 'Firmado'],
        'approved'    => ['bg' => '#eaf6ec', 'tx' => '#3d8048', 'br' => '#bedcc4', 'lb' => 'Aprobado'],
        'aprobada'    => ['bg' => '#eaf6ec', 'tx' => '#3d8048', 'br' => '#bedcc4', 'lb' => 'Aprobada'],
        'validado'    => ['bg' => '#eaf6ec', 'tx' => '#3d8048', 'br' => '#bedcc4', 'lb' => 'Validado'],
        'completada'  => ['bg' => '#eaf6ec', 'tx' => '#3d8048', 'br' => '#bedcc4', 'lb' => 'Completada'],
        'resuelta'    => ['bg' => '#eaf6ec', 'tx' => '#3d8048', 'br' => '#bedcc4', 'lb' => 'Resuelta'],
        'activa'      => ['bg' => '#eaf6ec', 'tx' => '#3d8048', 'br' => '#bedcc4', 'lb' => 'Activa'],
        'rejected'    => ['bg' => '#fbeaea', 'tx' => '#a83838', 'br' => '#eebcbc', 'lb' => 'Rechazado'],
        'rechazada'   => ['bg' => '#fbeaea', 'tx' => '#a83838', 'br' => '#eebcbc', 'lb' => 'Rechazada'],
        'rechazado'   => ['bg' => '#fbeaea', 'tx' => '#a83838', 'br' => '#eebcbc', 'lb' => 'Rechazado'],
        'vencido'     => ['bg' => '#fbeaea', 'tx' => '#a83838', 'br' => '#eebcbc', 'lb' => 'Vencido'],
        'vencida'     => ['bg' => '#fbeaea', 'tx' => '#a83838', 'br' => '#eebcbc', 'lb' => 'Vencida'],
        'alta'        => ['bg' => '#fbeaea', 'tx' => '#a83838', 'br' => '#eebcbc', 'lb' => 'Alta'],
        'media'       => ['bg' => '#fef6e6', 'tx' => '#9b6f1d', 'br' => '#e9d3a4', 'lb' => 'Media'],
        'baja'        => ['bg' => '#e8f4ef', 'tx' => '#3d8068', 'br' => '#b9dccd', 'lb' => 'Baja'],
        'en_proceso'  => ['bg' => '#eaf3fb', 'tx' => '#2c6aa0', 'br' => '#bcd6ee', 'lb' => 'En proceso'],
        'en_firma'    => ['bg' => '#fef6e6', 'tx' => '#9b6f1d', 'br' => '#e9d3a4', 'lb' => 'En firma'],
        'programada'  => ['bg' => '#eaf3fb', 'tx' => '#2c6aa0', 'br' => '#bcd6ee', 'lb' => 'Programada'],
        'en_atencion' => ['bg' => '#fef6e6', 'tx' => '#9b6f1d', 'br' => '#e9d3a4', 'lb' => 'En atención'],
        'en_tramite'  => ['bg' => '#f1ecf8', 'tx' => '#5a3d99', 'br' => '#d6c8ec', 'lb' => 'En trámite'],
    ];
    $key = strtolower((string)($s ?? 'pending'));
    $c = $palette[$key] ?? $palette['pending'];
    $label = $label ?? $c['lb'];
@endphp
<span class="inline-flex items-center" style="background:{{ $c['bg'] }};color:{{ $c['tx'] }};border:1px solid {{ $c['br'] }};border-radius:4px;padding:2px 8px;font-size:10px;font-weight:600;letter-spacing:0.3px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;">{{ $label }}</span>
