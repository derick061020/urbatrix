<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KYC — Reserva {{ $reservation->reservation_code ?? '' }} · MAKAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/primeicons/primeicons.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans:    ['Inter', 'system-ui', 'sans-serif'],
              display: ['"Inter Tight"', 'Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
              brand: { DEFAULT:'#5c7c68', dark:'#4a6354', soft:'#5c7c6833', tint:'#eef2ef' },
              ink: { 950:'#171717', 900:'#222530', 700:'#2b303b', 600:'#5c5c5c', 500:'#717784', 400:'#a3a3a3', 300:'#cacfd8', 200:'#ebebeb', 100:'#f2f5f8', 50:'#f8f8f8' },
              err: { DEFAULT:'#fb3748', soft:'#ffebec' },
              ok:  { DEFAULT:'#1fc16b', soft:'#e3f7ec' },
              warn:{ DEFAULT:'#fa7319', soft:'#fff3eb' },
            },
          }
        }
      }
    </script>
    <style>
      html, body { font-family: 'Inter', system-ui, sans-serif; background:#fff; }

      .auth-input {
        width:100%; height:40px; padding:0 14px;
        border:1px solid #ebebeb; border-radius:10px;
        background:#fff; color:#171717; font-size:14px;
        transition: border-color .15s, box-shadow .15s;
      }
      .auth-input:focus { outline:none; border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }
      .auth-input::placeholder { color:#a3a3a3; }
      .auth-input[readonly] { background:#f8f8f8; color:#5c5c5c; }
      .auth-input.is-invalid { border-color:#fb3748 !important; box-shadow:0 0 0 3px rgba(251,55,72,.14) !important; }
      .auth-select { appearance:none; padding-right:36px; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a3a3a3' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat:no-repeat; background-position: right 12px center; }

      .auth-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        height:40px; padding:0 16px; border-radius:10px;
        font-weight:500; font-size:14px; line-height:1; cursor:pointer;
        transition: background-color .15s, border-color .15s, color .15s, transform .12s;
      }
      .auth-btn:active { transform: translateY(1px); }
      .auth-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; box-shadow: 0 1px 2px 0 rgba(10,13,20,.06); }
      .auth-btn-primary:hover { background:#4a6354; border-color:#4a6354; }
      .auth-btn-primary:disabled { background:#a3a3a3; border-color:#a3a3a3; cursor:not-allowed; }
      .auth-btn-ghost { background:#fff; color:#171717; border:1px solid #ebebeb; }
      .auth-btn-ghost:hover { background:#f8f8f8; }
      .auth-link { color:#171717; font-weight:500; font-size:14px; border-bottom:1px solid #171717; padding-bottom:1px; }
      .auth-link:hover { color:#5c7c68; border-color:#5c7c68; }

      .field-label { display:block; font-size:13px; font-weight:500; color:#171717; margin-bottom:6px; }
      .field-required { color:#fb3748; }

      /* Step containers + transitions */
      .reg-step { display:none; opacity:0; transform: translateY(8px); transition: opacity .25s ease, transform .25s ease; }
      .reg-step.active { display:block; opacity:1; transform: translateY(0); }

      /* Step indicator */
      .step-pill { display:inline-flex; align-items:center; gap:8px; font-size:14px; color:#a3a3a3; font-weight:500; white-space:nowrap; }
      .step-pill .num {
        width:22px; height:22px; border-radius:999px;
        display:inline-flex; align-items:center; justify-content:center;
        background:#fff; border:1px solid #ebebeb;
        font-size:11px; font-weight:600; color:#a3a3a3;
        transition: background-color .2s, color .2s, border-color .2s;
      }
      .step-pill.active       { color:#171717; }
      .step-pill.active .num  { background:#222530; color:#fff; border-color:#222530; }
      .step-pill.done         { color:#171717; }
      .step-pill.done .num    { background:#1fc16b; color:#fff; border-color:#1fc16b; }

      /* Decorative dot grid in upper band */
      .bg-pattern {
        background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px);
        background-size: 24px 24px;
      }

      /* Payment plan card */
      .pay-card {
          border:1px solid #ebebeb; border-radius:14px; padding:18px;
          background:#fff; cursor:pointer;
          transition: border-color .15s, background-color .15s;
      }
      .pay-card:hover { background:#f8f8f8; }
      .pay-card.selected { border-color:#5c7c68; background:#fff; box-shadow:0 0 0 1px #5c7c68; }
      .pay-card.is-invalid { border-color:#fb3748; background:#fff7f7; box-shadow:0 0 0 1px #fb3748; }

      /* Drop zone */
      .file-drop {
          border:2px dashed #ebebeb; border-radius:14px;
          padding: 22px 16px; text-align:center;
          cursor:pointer; transition: border-color .15s, background-color .15s;
      }
      .file-drop:hover { border-color:#5c7c68; background:#fafafa; }
      .file-drop.is-invalid { border-color:#fb3748; background:#fff7f7; box-shadow:0 0 0 3px rgba(251,55,72,.10); }

      /* Spinner used by success modal */
      .check-circle {
          width:64px; height:64px; border-radius:999px;
          background:#5c7c68; color:#fff;
          display:flex; align-items:center; justify-content:center;
          margin: 0 auto 18px;
      }

      /* Responsive */
      @media (max-width: 640px) {
          #step-indicator { display:none !important; }
          .reg-step h1 { font-size: 20px !important; }
          .form-section-title { font-size:13px !important; }
      }
    </style>
</head>
<body>

<div class="min-h-screen flex flex-col">

    {{-- ============= HEADER ============= --}}
    <header class="flex items-center justify-between px-7 lg:px-11 py-6 border-b border-ink-100 bg-white">
        <a href="/" class="flex items-center gap-3 select-none">
            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 shadow-sm" style="background:#5c7c68">
                <span class="block w-6 h-6"><img src="{{ asset('images/brand/makai-logo-mark.svg') }}" alt="" class="block w-full h-full"></span>
            </span>
            <span class="flex flex-col leading-none">
                <span class="font-display text-[14px] font-bold text-ink-950 tracking-tight">MAKAI</span>
                <span class="text-[9px] font-semibold text-ink-500 tracking-[0.18em] uppercase mt-1">Duna Development</span>
            </span>
        </a>

        {{-- Step indicator --}}
        <div id="step-indicator" class="hidden lg:flex items-center gap-5">
            @php
                $steps = [
                    ['Datos personales', 1],
                    ['Dirección',        2],
                    ['Profesión',        3],
                    ['Forma de pago',    4],
                ];
            @endphp
            @foreach($steps as $s)
                <div class="step-pill" data-step="{{ $s[1] }}">
                    <span class="num">{{ $s[1] }}</span>
                    <span>{{ $s[0] }}</span>
                </div>
                @if(!$loop->last)
                    <i class="pi pi-angle-right text-ink-300 text-[12px]"></i>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right hidden md:block">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">Reserva</div>
                <div class="text-[11px] font-bold text-ink-950">{{ $reservation->reservation_code ?? '—' }}</div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">Expira en</div>
                <div id="countdown" class="font-display text-[14px] font-bold text-warn">--:--</div>
            </div>
            <a href="/" class="auth-btn auth-btn-ghost w-10 px-0" title="Cerrar"><i class="pi pi-times text-[12px]"></i></a>
        </div>
    </header>

    {{-- "Volver" button on steps 2-4 --}}
    <button type="button" onclick="prevStep()" id="back-btn" class="hidden absolute top-[100px] left-7 lg:left-11 z-20 auth-btn auth-btn-ghost"><i class="pi pi-angle-left text-[12px]"></i> Volver</button>

    {{-- ============= BODY ============= --}}
    <main class="flex-1 flex items-start justify-center px-5 py-8 relative">
        <div class="absolute inset-x-0 top-0 h-[300px] bg-pattern opacity-50 pointer-events-none" aria-hidden="true"></div>

        <div class="w-full max-w-[640px] relative">

            {{-- ====== STEP 1 — Datos personales (KYC) ====== --}}
            <div class="reg-step active" data-step="1">
                <div class="text-center mb-7">
                    <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                        <i class="pi pi-id-card text-ink-600 text-[26px]"></i>
                    </div>
                    <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Datos personales</h1>
                    <p class="text-[14px] text-ink-500 mt-2">Completa los datos del adquiriente principal</p>
                </div>
                <div class="h-px bg-ink-200/70 mb-6"></div>

                <div class="form-section-title text-[11px] uppercase tracking-wider font-semibold text-ink-500 mb-4">Datos generales del adquiriente</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="field-label">Agregar titular secundario <span class="field-required">*</span></label>
                        <select id="addCoBuyer" class="auth-input auth-select" onchange="toggleCoBuyersPanel(this.value)">
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Nombre <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" value="{{ $reservation->first_name ?? '' }}" readonly>
                    </div>
                    <div>
                        <label class="field-label">Apellido <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" value="{{ $reservation->last_name ?? '' }}" readonly>
                    </div>
                    <div>
                        <label class="field-label">E-mail <span class="field-required">*</span></label>
                        <input type="email" class="auth-input" value="{{ $reservation->email ?? '' }}" readonly>
                    </div>
                    <div>
                        <label class="field-label">Teléfono <span class="field-required">*</span></label>
                        <input type="tel" class="auth-input" value="{{ $reservation->phone ?? '' }}" readonly>
                    </div>

                    <div>
                        <label class="field-label">Tipo de identificación <span class="field-required">*</span></label>
                        <select id="idType" class="auth-input auth-select">
                            <option value="">Seleccionar…</option>
                            <option>Cédula</option><option>Pasaporte</option><option>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Número de documento <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="document_number">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="field-label">Foto del documento de identidad<span class="field-required">*</span></label>

                        @if(! empty($existingKycDoc))
                            @php $status = $existingKycDoc['status'] ?? 'pending'; @endphp
                            {{-- Already uploaded during registration — show summary card with option to replace --}}
                            <div id="existing-doc-card" class="flex items-center gap-3 p-4 rounded-xl border border-ink-200 bg-ink-50/50">
                                <div class="w-11 h-11 rounded-lg bg-white border border-ink-200 flex items-center justify-center text-ok shrink-0">
                                    <i class="pi pi-check-circle text-[18px]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-ink-950">Documento ya cargado</div>
                                    <div class="text-[11px] text-ink-500 truncate">{{ $existingKycDoc['name'] }}</div>
                                    @if($status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded-full bg-ok-soft text-ok-dark text-[10px] font-semibold uppercase tracking-wider"><i class="pi pi-check text-[8px]"></i> Aprobado</span>
                                    @elseif($status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded-full bg-err-soft text-err text-[10px] font-semibold uppercase tracking-wider"><i class="pi pi-times text-[8px]"></i> Rechazado · vuelve a subir</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded-full bg-warn-soft text-warn text-[10px] font-semibold uppercase tracking-wider"><i class="pi pi-clock text-[8px]"></i> En verificación</span>
                                    @endif
                                </div>
                                <a href="{{ $existingKycDoc['url'] }}" target="_blank" class="auth-btn auth-btn-ghost text-[11px] py-1 px-3" title="Ver documento"><i class="pi pi-eye text-[11px]"></i> Ver</a>
                                <button type="button" class="auth-btn auth-btn-ghost text-[11px] py-1 px-3" onclick="document.getElementById('existing-doc-card').classList.add('hidden'); document.getElementById('replace-doc-card').classList.remove('hidden'); document.getElementById('idDocument').required = true;">Reemplazar</button>
                            </div>
                            {{-- Hidden replacement uploader, shown when user clicks "Reemplazar" --}}
                            <div id="replace-doc-card" class="hidden mt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] text-ink-500">Subir un nuevo archivo</span>
                                    <button type="button" class="text-[11px] text-brand font-semibold hover:underline"
                                            onclick="document.getElementById('replace-doc-card').classList.add('hidden'); document.getElementById('existing-doc-card').classList.remove('hidden'); document.getElementById('idDocument').value = ''; document.getElementById('idDocument').required = false; document.getElementById('fileName').textContent = ''; document.getElementById('fileSize').textContent = '0.00 / 4 MB';">Cancelar</button>
                                </div>
                                <div class="file-drop" onclick="document.getElementById('idDocument').click()">
                                    <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                                    <div class="text-[13px] font-semibold text-ink-700 mt-2">Arrastra aquí o haz clic para seleccionar</div>
                                    <div class="text-[11px] text-ink-500 mt-1">PDF, JPG o PNG · máx. 4 MB · <span id="fileSize">0.00 / 4 MB</span></div>
                                    <button type="button" class="auth-btn auth-btn-ghost text-[11px] py-1 px-3 mt-3" onclick="event.stopPropagation(); document.getElementById('idDocument').click()">Buscar archivo</button>
                                    <input type="file" id="idDocument" name="id_document" class="hidden" accept="image/*,.pdf" onchange="updateFileSize(this)">
                                    <div id="fileName" class="text-[11px] text-brand font-semibold mt-2"></div>
                                </div>
                            </div>
                            <input type="hidden" name="kyc_document_reused" value="1">
                        @else
                            {{-- No existing doc — show upload zone --}}
                            <div class="text-ink-400 text-[11px] font-normal mb-2" id="fileSize">0.00 / 4 MB</div>
                            <div class="file-drop" onclick="document.getElementById('idDocument').click()">
                                <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                                <div class="text-[13px] font-semibold text-ink-700 mt-2">Arrastra aquí o haz clic para seleccionar</div>
                                <div class="text-[11px] text-ink-500 mt-1">Pasaporte / Cédula / Licencia · PDF, JPG o PNG · máx. 4 MB</div>
                                <button type="button" class="auth-btn auth-btn-ghost text-[11px] py-1 px-3 mt-3" onclick="event.stopPropagation(); document.getElementById('idDocument').click()">Buscar archivo</button>
                                <input type="file" id="idDocument" name="id_document" required class="hidden" accept="image/*,.pdf" onchange="updateFileSize(this)">
                                <div id="fileName" class="text-[11px] text-brand font-semibold mt-2"></div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="field-label">Fecha de expedición <span class="field-required">*</span></label>
                        <input type="date" class="auth-input" data-name="expedition_date">
                    </div>
                    <div>
                        <label class="field-label">Lugar de expedición <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="expedition_place">
                    </div>
                    <div>
                        <label class="field-label">Fecha de nacimiento <span class="field-required">*</span></label>
                        <input type="date" class="auth-input" data-name="birth_date" id="birth_date" onchange="calculateAge()">
                    </div>
                    <div>
                        <label class="field-label">Edad <span class="field-required">*</span></label>
                        <input type="number" class="auth-input" data-name="age" id="age" readonly>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Nacionalidad <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="nationality">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="field-label">Estado civil <span class="field-required">*</span></label>
                        <select id="maritalStatus" class="auth-input auth-select" onchange="toggleSpouseFields()">
                            <option value="">Seleccionar…</option>
                            <option>Soltero/a</option>
                            <option>Casado/a</option>
                            <option>Divorciado/a</option>
                            <option>Viudo/a</option>
                        </select>
                    </div>

                    <div id="spouseFields" class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4" style="display:none;">
                        <div class="sm:col-span-2">
                            <label class="field-label">Nombre completo del cónyuge <span class="field-required">*</span></label>
                            <input type="text" id="spouseName" class="auth-input">
                        </div>
                        <div>
                            <label class="field-label">Nacionalidad (Cónyuge) <span class="field-required">*</span></label>
                            <input type="text" id="spouseNationality" class="auth-input">
                        </div>
                        <div>
                            <label class="field-label">Cédula / Pasaporte (Cónyuge) <span class="field-required">*</span></label>
                            <input type="text" id="spouseDocument" class="auth-input">
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Género <span class="field-required">*</span></label>
                        <select class="auth-input auth-select">
                            <option value="">Seleccionar…</option>
                            <option>Masculino</option><option>Femenino</option><option>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Lugar de nacimiento <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" placeholder="Ciudad / País">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">País de residencia <span class="field-required">*</span></label>
                        <input type="text" class="auth-input">
                    </div>
                </div>

                {{-- ============ TITULARES SECUNDARIOS ============ --}}
                <div id="coBuyersPanel" class="mt-7" style="display:none;">
                    <div class="h-px bg-ink-200/70 mb-5"></div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="form-section-title text-[11px] uppercase tracking-wider font-semibold text-ink-500">Titulares adicionales</div>
                            <p class="text-[12px] text-ink-500 mt-1">Agregá los datos de cada copropietario. Todos serán incluidos en los contratos.</p>
                        </div>
                        <button type="button" id="addCoBuyerBtn" onclick="addCoBuyerRow()" class="auth-btn auth-btn-ghost text-[12px] py-2 px-3">
                            <i class="pi pi-plus text-[11px]"></i> Agregar otro
                        </button>
                    </div>
                    <div id="coBuyersList" class="space-y-4"></div>
                </div>

                <template id="coBuyerTpl">
                    <div class="co-buyer-row rounded-2xl border border-ink-200 bg-ink-50/40 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-[13px] font-bold text-ink-900">Titular <span data-co-index>#2</span></div>
                            <button type="button" onclick="removeCoBuyerRow(this)" class="text-err text-[11px] font-semibold inline-flex items-center gap-1 hover:underline" title="Quitar">
                                <i class="pi pi-trash text-[11px]"></i> Quitar
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Nombre <span class="field-required">*</span></label>
                                <input type="text" data-co-field="first_name" class="auth-input">
                            </div>
                            <div>
                                <label class="field-label">Apellido <span class="field-required">*</span></label>
                                <input type="text" data-co-field="last_name" class="auth-input">
                            </div>
                            <div>
                                <label class="field-label">E-mail <span class="field-required">*</span></label>
                                <input type="email" data-co-field="email" class="auth-input">
                            </div>
                            <div>
                                <label class="field-label">Teléfono <span class="field-required">*</span></label>
                                <input type="tel" data-co-field="phone" class="auth-input">
                            </div>
                            <div>
                                <label class="field-label">Tipo de documento <span class="field-required">*</span></label>
                                <select data-co-field="id_type" class="auth-input auth-select">
                                    <option value="">Seleccionar…</option>
                                    <option>Cédula</option><option>Pasaporte</option><option>Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Número de documento <span class="field-required">*</span></label>
                                <input type="text" data-co-field="document_number" class="auth-input">
                            </div>
                            <div>
                                <label class="field-label">Fecha de nacimiento <span class="field-required">*</span></label>
                                <input type="date" data-co-field="birth_date" class="auth-input">
                            </div>
                            <div>
                                <label class="field-label">Nacionalidad <span class="field-required">*</span></label>
                                <input type="text" data-co-field="nationality" class="auth-input">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="field-label">Relación con el titular principal <span class="field-required">*</span></label>
                                <select data-co-field="relationship" class="auth-input auth-select">
                                    <option value="">Seleccionar…</option>
                                    <option>Cónyuge</option>
                                    <option>Hijo/a</option>
                                    <option>Padre / Madre</option>
                                    <option>Hermano/a</option>
                                    <option>Socio comercial</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="field-label">Porcentaje de copropiedad (%)</label>
                                <input type="number" min="1" max="99" step="1" data-co-field="ownership_pct" class="auth-input" placeholder="Ej. 50">
                            </div>
                        </div>
                    </div>
                </template>

                <div class="mt-8">
                    <button type="button" onclick="goToStep(2)" class="auth-btn auth-btn-primary w-full">Continuar</button>
                </div>
            </div>

            {{-- ====== STEP 2 — Dirección ====== --}}
            <div class="reg-step" data-step="2">
                <div class="text-center mb-7">
                    <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                        <i class="pi pi-map-marker text-ink-600 text-[26px]"></i>
                    </div>
                    <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Dirección de residencia</h1>
                    <p class="text-[14px] text-ink-500 mt-2">Dirección donde recibes correspondencia y documentos legales</p>
                </div>
                <div class="h-px bg-ink-200/70 mb-6"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="field-label">Dirección (Calle / número) <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="address">
                    </div>
                    <div>
                        <label class="field-label">Provincia / Estado <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="province">
                    </div>
                    <div>
                        <label class="field-label">Barrio / Sector <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="neighborhood">
                    </div>
                    <div>
                        <label class="field-label">Ciudad <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="city">
                    </div>
                    <div>
                        <label class="field-label">País <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="country_code">
                    </div>
                    <div>
                        <label class="field-label">Nombre del edificio / Torre <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="building_name">
                    </div>
                    <div>
                        <label class="field-label">Nro. Apartamento / Residencia <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="apartment_number">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Código postal <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="postal_code">
                    </div>
                </div>

                <div class="mt-8">
                    <button type="button" onclick="goToStep(3)" class="auth-btn auth-btn-primary w-full">Continuar</button>
                </div>
            </div>

            {{-- ====== STEP 3 — Profesión & Unidad ====== --}}
            <div class="reg-step" data-step="3">
                <div class="text-center mb-7">
                    <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                        <i class="pi pi-briefcase text-ink-600 text-[26px]"></i>
                    </div>
                    <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Profesión y unidad</h1>
                    <p class="text-[14px] text-ink-500 mt-2">Información laboral y datos de la unidad reservada</p>
                </div>
                <div class="h-px bg-ink-200/70 mb-6"></div>

                <div class="form-section-title text-[11px] uppercase tracking-wider font-semibold text-ink-500 mb-4">Datos profesionales</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Profesión <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="profession">
                    </div>
                    <div>
                        <label class="field-label">Ocupación <span class="field-required">*</span></label>
                        <input type="text" class="auth-input" data-name="occupation">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">¿Depende económicamente de alguien? <span class="field-required">*</span></label>
                        <select id="economicDependent" class="auth-input auth-select">
                            <option value="">Seleccionar…</option><option>No</option><option>Sí</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-title text-[11px] uppercase tracking-wider font-semibold text-ink-500 mt-7 mb-4">Unidad reservada</div>

                <div class="rounded-2xl border border-ink-200 p-5 bg-ink-50/40 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Unidad</label>
                        <input type="text" class="auth-input" value="{{ $reservation->unit_name ?? '' }}" readonly>
                    </div>
                    <div>
                        <label class="field-label">Nivel</label>
                        <input type="text" class="auth-input" value="{{ $unit->floor ?? 'N/A' }}" readonly>
                    </div>
                    <div>
                        <label class="field-label">Precio de lista</label>
                        <input type="text" class="auth-input" value="{{ $reservation->formatted_price ?? '' }}" readonly>
                    </div>
                    <div>
                        <label class="field-label">Precio final</label>
                        <input type="text" class="auth-input" value="{{ $reservation->formatted_price ?? '' }}" readonly>
                    </div>
                </div>

                <label class="flex items-start gap-2 cursor-pointer mt-5">
                    <input type="checkbox" id="terms-checkbox" checked class="w-4 h-4 rounded accent-brand mt-0.5">
                    <span class="text-[13px] text-ink-600">
                        Acepto los <a href="#" class="text-ink-950 hover:text-brand underline">Términos</a> y la
                        <a href="#" class="text-ink-950 hover:text-brand underline">Política de Privacidad</a>.
                    </span>
                </label>

                <div class="mt-8">
                    <button type="button" onclick="goToStep(4)" class="auth-btn auth-btn-primary w-full">Continuar</button>
                </div>
            </div>

            {{-- ====== STEP 4 — Forma de pago ====== --}}
            <div class="reg-step" data-step="4">
                <div class="text-center mb-7">
                    <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                        <i class="pi pi-credit-card text-ink-600 text-[26px]"></i>
                    </div>
                    <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Forma de pago</h1>
                    <p class="text-[14px] text-ink-500 mt-2">Selecciona el plan de pagos que mejor se adapte</p>
                </div>
                <div class="h-px bg-ink-200/70 mb-6"></div>

                <div id="step4-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                <div class="space-y-3">
                    {{-- Option A --}}
                    <label class="pay-card flex items-start gap-4" data-pay="A">
                        <input type="checkbox" name="payment_a" value="A" class="w-5 h-5 mt-1 accent-brand" onchange="onPaySelect(this)">
                        <div class="flex-1">
                            <div class="text-[14px] font-bold text-ink-950">Plan A — 30/40/30</div>
                            <div class="text-[12px] text-ink-500 mt-1">30% al firmar · 40% durante construcción · 30% a la entrega</div>
                        </div>
                    </label>

                    {{-- Option B --}}
                    <label class="pay-card flex items-start gap-4" data-pay="B">
                        <input type="checkbox" name="payment_b" value="B" class="w-5 h-5 mt-1 accent-brand" onchange="onPaySelect(this)">
                        <div class="flex-1">
                            <div class="text-[14px] font-bold text-ink-950">Plan B — 40/30/30</div>
                            <div class="text-[12px] text-ink-500 mt-1">40% al firmar · 30% durante construcción · 30% a la entrega</div>
                        </div>
                    </label>

                    {{-- Option C --}}
                    <label class="pay-card flex items-start gap-4" data-pay="C">
                        <input type="checkbox" name="payment_c" value="C" class="w-5 h-5 mt-1 accent-brand" onchange="onPaySelect(this)">
                        <div class="flex-1">
                            <div class="text-[14px] font-bold text-ink-950">Plan C — 50/20/30</div>
                            <div class="text-[12px] text-ink-500 mt-1">50% al firmar · 20% durante construcción · 30% a la entrega</div>
                        </div>
                    </label>

                    {{-- Personalizado --}}
                    <label class="pay-card flex items-start gap-4" data-pay="CUSTOM">
                        <input type="checkbox" name="payment_custom" value="PERSONALIZADO" id="payment_custom_checkbox" class="w-5 h-5 mt-1 accent-brand" onchange="onPaySelect(this); toggleCustomPayment();">
                        <div class="flex-1">
                            <div class="text-[14px] font-bold text-ink-950">Plan personalizado</div>
                            <div class="text-[12px] text-ink-500 mt-1">Configura los porcentajes que mejor se ajusten</div>

                            <div id="custom_payment_options" class="grid grid-cols-3 gap-3 mt-4" style="display:none;">
                                <div>
                                    <label class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Inicial</label>
                                    <div class="relative mt-1">
                                        <input type="text" name="custom_payment_1" placeholder="0" class="auth-input pr-7" inputmode="numeric">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 text-[12px]">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Construcción</label>
                                    <div class="relative mt-1">
                                        <input type="text" name="custom_payment_2" placeholder="0" class="auth-input pr-7" inputmode="numeric">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 text-[12px]">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Entrega</label>
                                    <div class="relative mt-1">
                                        <input type="text" name="custom_payment_3" placeholder="0" class="auth-input pr-7" inputmode="numeric">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 text-[12px]">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="mt-8">
                    <button type="button" onclick="submitForm()" id="submit-btn" class="auth-btn auth-btn-primary w-full">Enviar formulario</button>
                </div>
            </div>

            {{-- Success state (hidden) --}}
            <div id="success-state" class="hidden text-center py-16">
                <div class="check-circle"><i class="pi pi-check text-[28px] font-bold"></i></div>
                <h2 class="font-display text-[26px] font-medium text-ink-950">Formulario enviado</h2>
                <p class="text-[14px] text-ink-500 mt-2">Tu reserva ha sido procesada exitosamente.</p>
                <p class="text-[12px] text-ink-400 mt-1">Te redirigiremos a la página principal…</p>
            </div>

        </div>
    </main>

    {{-- ============= FOOTER ============= --}}
    <footer class="flex items-center justify-between px-7 lg:px-11 py-5 text-[12px] text-ink-500 border-t border-ink-100 bg-white">
        <span>© 2026 MAKAI RESIDENCES</span>
        <button class="flex items-center gap-1.5 hover:text-ink-700">
            <i class="pi pi-globe text-[12px]"></i><span>ESP</span><i class="pi pi-angle-down text-[10px]"></i>
        </button>
    </footer>
</div>

<script>
(function () {
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    let currentStep = 1;

    const isVisible = (el) => !!(el && el.getClientRects().length);
    const getStep = (step) => document.querySelector(`.reg-step[data-step="${step}"]`);

    const fieldIsRequired = (el) => {
        const holder = el.closest('div');
        return !!holder?.querySelector('.field-required');
    };

    const markInvalid = (el) => {
        el.classList.add('is-invalid');
        el.setAttribute('aria-invalid', 'true');
        return el;
    };

    const clearInvalid = (el) => {
        el.classList.remove('is-invalid');
        el.removeAttribute('aria-invalid');
    };

    const clearStepInvalid = (stepEl) => {
        stepEl?.querySelectorAll('.is-invalid').forEach(clearInvalid);
    };

    const scrollToFirstInvalid = (stepEl) => {
        const firstInvalid = stepEl?.querySelector('.is-invalid');
        if (!firstInvalid) return;
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (typeof firstInvalid.focus === 'function' && !firstInvalid.classList.contains('file-drop')) {
            firstInvalid.focus({ preventScroll: true });
        }
    };

    const validateStep = (stepNumber) => {
        const stepEl = getStep(stepNumber);
        if (!stepEl) return true;

        clearStepInvalid(stepEl);
        const invalidFields = [];

        stepEl.querySelectorAll('.auth-input').forEach((el) => {
            if (!isVisible(el) || el.disabled || el.readOnly || !fieldIsRequired(el)) return;
            if (!String(el.value || '').trim()) invalidFields.push(markInvalid(el));
        });

        stepEl.querySelectorAll('input[type="file"][required]').forEach((el) => {
            const drop = el.closest('.file-drop');
            if (!drop || !isVisible(drop)) return;
            if (!el.files || !el.files.length) invalidFields.push(markInvalid(drop));
        });

        if (invalidFields.length) {
            scrollToFirstInvalid(stepEl);
            return false;
        }

        return true;
    };

    /* ---------- Step navigation ---------- */
    window.goToStep = (n) => {
        if (n > currentStep && !validateStep(currentStep)) return;

        currentStep = n;
        document.querySelectorAll('.reg-step').forEach(el => el.classList.toggle('active', +el.dataset.step === n));
        document.querySelectorAll('#step-indicator .step-pill').forEach(p => {
            const s = +p.dataset.step;
            p.classList.toggle('active', s === n);
            p.classList.toggle('done',   s <  n);
            const num = p.querySelector('.num');
            if (s < n) num.innerHTML = '<i class="pi pi-check text-[10px]"></i>';
            else num.textContent = s;
        });
        document.getElementById('back-btn').classList.toggle('hidden', n === 1);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    window.prevStep = () => { if (currentStep > 1) goToStep(currentStep - 1); };

    document.addEventListener('input', (event) => {
        if (event.target.matches('.auth-input')) clearInvalid(event.target);
    });
    document.addEventListener('change', (event) => {
        if (event.target.matches('.auth-input')) clearInvalid(event.target);
        if (event.target.matches('input[type="file"]')) {
            const drop = event.target.closest('.file-drop');
            if (drop) clearInvalid(drop);
        }
    });

    /* ---------- File upload feedback ---------- */
    window.updateFileSize = (input) => {
        const size = document.getElementById('fileSize');
        const name = document.getElementById('fileName');
        if (input.files && input.files[0]) {
            const mb = (input.files[0].size / (1024 * 1024)).toFixed(2);
            size.textContent = mb + ' / 4 MB';
            size.style.color = mb > 4 ? '#fb3748' : '#a3a3a3';
            name.textContent = '✓ ' + input.files[0].name;
        } else {
            size.textContent = '0.00 / 4 MB';
            name.textContent = '';
        }
    };

    /* ---------- Calculate age from birth date ---------- */
    window.calculateAge = () => {
        const birthDateInput = document.getElementById('birth_date');
        const ageInput = document.getElementById('age');
        if (!birthDateInput.value) {
            ageInput.value = '';
            return;
        }
        const birthDate = new Date(birthDateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        ageInput.value = age >= 0 ? age : '';
    };

    /* ---------- Co-buyers (titulares secundarios) ---------- */
    window.toggleCoBuyersPanel = (val) => {
        const panel = document.getElementById('coBuyersPanel');
        const list  = document.getElementById('coBuyersList');
        if (val === 'si') {
            panel.style.display = 'block';
            if (list.children.length === 0) addCoBuyerRow();
        } else {
            panel.style.display = 'none';
            list.innerHTML = '';
        }
    };
    window.addCoBuyerRow = () => {
        const tpl = document.getElementById('coBuyerTpl');
        const list = document.getElementById('coBuyersList');
        const clone = tpl.content.firstElementChild.cloneNode(true);
        const idxEl = clone.querySelector('[data-co-index]');
        if (idxEl) idxEl.textContent = '#' + (list.children.length + 2);
        list.appendChild(clone);
        // Limit to 5 co-buyers
        if (list.children.length >= 5) {
            document.getElementById('addCoBuyerBtn').setAttribute('disabled', 'true');
            document.getElementById('addCoBuyerBtn').style.opacity = '.5';
        }
    };
    window.removeCoBuyerRow = (btn) => {
        const row = btn.closest('.co-buyer-row');
        const list = document.getElementById('coBuyersList');
        row.remove();
        // Re-number
        Array.from(list.querySelectorAll('[data-co-index]')).forEach((el, i) => el.textContent = '#' + (i + 2));
        document.getElementById('addCoBuyerBtn').removeAttribute('disabled');
        document.getElementById('addCoBuyerBtn').style.opacity = '';
        if (list.children.length === 0) {
            document.getElementById('addCoBuyer').value = 'no';
            document.getElementById('coBuyersPanel').style.display = 'none';
        }
    };
    window.collectCoBuyers = () => {
        const rows = document.querySelectorAll('#coBuyersList .co-buyer-row');
        const out = [];
        rows.forEach(row => {
            const obj = {};
            row.querySelectorAll('[data-co-field]').forEach(el => {
                obj[el.dataset.coField] = (el.value || '').trim();
            });
            // Only push if at least name+doc filled
            if (obj.first_name && obj.document_number) out.push(obj);
        });
        return out;
    };

    /* ---------- Spouse fields toggle ---------- */
    window.toggleSpouseFields = () => {
        const m = document.getElementById('maritalStatus').value;
        const sf = document.getElementById('spouseFields');
        if (m === 'Casado/a' || m === 'Divorciado/a' || m === 'Viudo/a') {
            sf.style.display = 'grid';
        } else {
            sf.style.display = 'none';
            document.getElementById('spouseName').value = '';
            document.getElementById('spouseNationality').value = '';
            document.getElementById('spouseDocument').value = '';
        }
    };

    /* ---------- Payment plan selection (single-select) ---------- */
    window.onPaySelect = (cb) => {
        if (cb.checked) {
            document.querySelectorAll('.pay-card input[type=checkbox]').forEach(other => {
                if (other !== cb) { other.checked = false; }
            });
        }
        document.querySelectorAll('.pay-card').forEach(card => {
            const i = card.querySelector('input[type=checkbox]');
            card.classList.remove('is-invalid');
            card.classList.toggle('selected', i.checked);
        });
    };

    window.toggleCustomPayment = () => {
        const cb = document.getElementById('payment_custom_checkbox');
        document.getElementById('custom_payment_options').style.display = cb.checked ? 'grid' : 'none';
    };

    /* ---------- Submit ---------- */
    window.submitForm = async () => {
        const submitBtn = document.getElementById('submit-btn');
        const errBox = document.getElementById('step4-error');
        if (!validateStep(currentStep)) return;

        // Get selected payment method
        let paymentMethod = '';
        if (document.querySelector('input[name="payment_a"]:checked')) paymentMethod = 'A';
        else if (document.querySelector('input[name="payment_b"]:checked')) paymentMethod = 'B';
        else if (document.querySelector('input[name="payment_c"]:checked')) paymentMethod = 'C';
        else if (document.querySelector('input[name="payment_custom"]:checked')) paymentMethod = 'PERSONALIZADO';

        if (!paymentMethod) {
            errBox.textContent = 'Selecciona un plan de pago para continuar.';
            errBox.classList.remove('hidden');
            document.querySelectorAll('.pay-card').forEach(card => card.classList.add('is-invalid'));
            return;
        }

        if (paymentMethod === 'PERSONALIZADO') {
            const customFields = [
                document.querySelector('input[name=custom_payment_1]'),
                document.querySelector('input[name=custom_payment_2]'),
                document.querySelector('input[name=custom_payment_3]'),
            ];
            const missingCustomFields = customFields.filter(field => field && !String(field.value || '').trim());
            if (missingCustomFields.length) {
                missingCustomFields.forEach(markInvalid);
                errBox.textContent = 'Completa los porcentajes del plan personalizado para continuar.';
                errBox.classList.remove('hidden');
                scrollToFirstInvalid(getStep(4));
                return;
            }
        }
        errBox.classList.add('hidden');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="pi pi-spin pi-spinner"></i> Enviando…';

        const fd = new FormData();
        fd.append('reservation_code', '{{ $reservation->reservation_code ?? "" }}');
        fd.append('payment_method', paymentMethod);
        fd.append('terms_accepted', document.getElementById('terms-checkbox')?.checked ? '1' : '0');
        fd.append('id_type', document.getElementById('idType')?.value || '');
        fd.append('marital_status', document.getElementById('maritalStatus')?.value || '');
        fd.append('economic_dependent', document.getElementById('economicDependent')?.value || '');
        fd.append('spouse_name', document.getElementById('spouseName')?.value || '');
        fd.append('spouse_nationality', document.getElementById('spouseNationality')?.value || '');
        fd.append('spouse_document', document.getElementById('spouseDocument')?.value || '');

        // Inputs with data-name attribute (semantic field names)
        document.querySelectorAll('[data-name]').forEach(el => fd.append(el.dataset.name, el.value));

        // Titulares secundarios (co-buyers)
        const coBuyers = (typeof collectCoBuyers === 'function') ? collectCoBuyers() : [];
        fd.append('co_buyers', JSON.stringify(coBuyers));

        // Custom payment percentages
        if (paymentMethod === 'PERSONALIZADO') {
            const c1 = document.querySelector('input[name=custom_payment_1]');
            const c2 = document.querySelector('input[name=custom_payment_2]');
            const c3 = document.querySelector('input[name=custom_payment_3]');
            if (c1?.value) fd.append('custom_payment_1', c1.value);
            if (c2?.value) fd.append('custom_payment_2', c2.value);
            if (c3?.value) fd.append('custom_payment_3', c3.value);
        }

        // ID document upload
        const idFile = document.getElementById('idDocument');
        if (idFile && idFile.files[0]) fd.append('id_document', idFile.files[0]);

        try {
            const res = await fetch('/reservations/update', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && (data.success || data.ok)) {
                showSuccess();
            } else {
                errBox.textContent = data.message || 'Error al enviar el formulario.';
                errBox.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Enviar formulario';
            }
        } catch (err) {
            errBox.textContent = 'Error de red. Por favor intenta de nuevo.';
            errBox.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enviar formulario';
        }
    };

    function showSuccess() {
        document.querySelectorAll('.reg-step').forEach(el => el.classList.remove('active'));
        document.getElementById('step-indicator')?.classList.add('hidden');
        document.getElementById('back-btn')?.classList.add('hidden');
        document.getElementById('success-state').classList.remove('hidden');
        setTimeout(() => { window.location.href = '/'; }, 3500);
    }

    /* ---------- Countdown timer ---------- */
    const expiresAt = new Date('{{ $reservation->expires_at ?? now()->addMinutes(10) }}');
    const countdownEl = document.getElementById('countdown');
    function tick() {
        const diff = expiresAt - new Date();
        if (diff <= 0) {
            countdownEl.textContent = 'EXPIRADO';
            countdownEl.className = 'font-display text-[14px] font-bold text-err';
            return;
        }
        const mm = Math.floor(diff / 60000);
        const ss = Math.floor((diff % 60000) / 1000);
        countdownEl.textContent = mm + ':' + String(ss).padStart(2,'0');
        if (mm < 5) countdownEl.className = 'font-display text-[14px] font-bold text-err';
    }
    tick(); setInterval(tick, 1000);
})();
</script>

</body>
</html>
