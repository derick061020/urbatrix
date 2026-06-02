{{-- Modal "Ver broker" · datos, contratos y unidades --}}
<dialog id="modal-broker-{{ $b->id }}" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[660px] max-w-[95vw]">
    <div class="bg-white rounded-2xl overflow-hidden flex flex-col max-h-[92vh]">

        {{-- Hero --}}
        <div class="px-6 pt-5 pb-0 border-b border-ink-100" style="border-top:3px solid #5c7c68">
            <div class="flex items-start gap-4">
                <div class="crm-avatar" style="background:{{ $bg }};width:48px;height:48px;font-size:16px">{{ $init }}</div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] uppercase tracking-[0.12em] text-ink-500 mb-1">Broker · #BR-{{ str_pad((string) $b->id, 4, '0', STR_PAD_LEFT) }}</div>
                    <div class="text-[17px] font-bold text-ink-950 leading-tight truncate">{{ $b->name }}</div>
                    <div class="text-[12px] text-ink-500 truncate">{{ $b->email }}{{ $b->phone ? ' · '.$b->phone : '' }}</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-[10px] uppercase tracking-wider text-ink-500">Comisión</div>
                    <div class="text-[30px] font-bold text-brand leading-none">{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}%</div>
                </div>
                <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1 shrink-0"><i class="pi pi-times text-[12px]"></i></button>
            </div>

            {{-- Tabs --}}
            <div class="flex items-center gap-1 mt-4">
                @foreach(['datos'=>'Datos & comisión','contratos'=>'Contratos','unidades'=>'Unidades'] as $key=>$label)
                    <button type="button" data-tab="{{ $key }}"
                        onclick="brkTab({{ $b->id }}, '{{ $key }}')"
                        class="px-3.5 py-2.5 text-[13px] font-semibold border-b-2 -mb-px transition-colors {{ $loop->first ? 'text-brand border-brand' : 'text-ink-500 border-transparent' }}">
                        {{ $label }}
                        @if($key==='contratos' && $b->brokerDocuments->count())
                            <span class="crm-pill bg-info-soft text-info ml-1">{{ $b->brokerDocuments->count() }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">

            {{-- ===== Panel: Datos & comisión ===== --}}
            <div data-panel="datos">
                <form method="POST" action="{{ route('admin.agents.update', $b->id) }}" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="text-[12px] font-semibold text-ink-700">Nombre completo</label>
                        <input type="text" name="name" value="{{ $b->name }}" required class="crm-input pl-3 mt-1">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[12px] font-semibold text-ink-700">Email</label>
                            <input type="email" name="email" value="{{ $b->email }}" required class="crm-input pl-3 mt-1">
                        </div>
                        <div>
                            <label class="text-[12px] font-semibold text-ink-700">Teléfono</label>
                            <input type="text" name="phone" value="{{ $b->phone }}" class="crm-input pl-3 mt-1">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 items-end">
                        <div>
                            <label class="text-[12px] font-semibold text-ink-700">Tasa de comisión (%)</label>
                            <input type="number" name="commission_rate" value="{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}" step="0.01" min="0" max="100" class="crm-input pl-3 mt-1">
                        </div>
                        <label class="flex items-center gap-2 text-[13px] text-ink-700 h-9 px-1">
                            <input type="checkbox" name="active" value="1" {{ ($b->verification_status ?? 'approved') === 'approved' ? 'checked' : '' }} class="w-4 h-4 accent-brand"> Broker activo
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>

            {{-- ===== Panel: Contratos ===== --}}
            <div data-panel="contratos" style="display:none">
                <div class="p-6 space-y-4">
                    {{-- Subir contrato --}}
                    <form method="POST" action="{{ route('admin.agents.documents.store', $b->id) }}" enctype="multipart/form-data"
                          class="border border-ink-100 rounded-xl p-4 bg-ink-50/40 space-y-3">
                        @csrf
                        <div class="text-[12px] font-semibold text-ink-700">Subir contrato para este broker</div>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="title" required placeholder="Título (ej. Contrato 2026)" class="crm-input pl-3">
                            <select name="category" class="crm-input pl-3">
                                <option value="Contrato">Contrato</option>
                                <option value="Anexo">Anexo</option>
                                <option value="Legal">Legal</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="file" name="file" required class="crm-input pl-3 flex-1 py-1.5">
                            <button type="submit" class="crm-btn crm-btn-primary shrink-0"><i class="pi pi-upload"></i> Subir</button>
                        </div>
                    </form>

                    {{-- Lista de contratos --}}
                    <div class="border border-ink-100 rounded-xl overflow-hidden divide-y divide-ink-100">
                        @forelse($b->brokerDocuments as $doc)
                            <div class="px-4 py-3 flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi {{ $doc->icon }}"></i></span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $doc->title }}</div>
                                    <div class="text-[11px] text-ink-400">{{ $doc->category }} · {{ $doc->file_size ?: $doc->format }} · {{ $doc->downloads }} descargas</div>
                                </div>
                                <button type="button" class="text-ink-500 hover:text-brand p-1" title="Ver"
                                    onclick="openBrokerDoc({{ \Illuminate\Support\Js::from(['title'=>$doc->title,'format'=>strtoupper($doc->format),'kind'=>$doc->previewKind(),'url'=>$doc->fileUrl(),'download'=>$doc->downloadUrl()]) }})"><i class="pi pi-eye"></i></button>
                                <a href="{{ $doc->downloadUrl() }}" target="_blank" class="text-ink-500 hover:text-brand p-1" title="Descargar"><i class="pi pi-download"></i></a>
                                <button type="button" class="text-ink-400 hover:text-err p-1" title="Eliminar"
                                    onclick="openBrokerDocDelete({{ \Illuminate\Support\Js::from(['url'=>route('admin.agents.documents.destroy', [$b->id, $doc->id]),'title'=>$doc->title]) }})"><i class="pi pi-trash"></i></button>
                            </div>
                        @empty
                            <div class="px-4 py-8 text-center text-[12px] text-ink-400">Este broker todavía no tiene contratos. Subí el primero arriba.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===== Panel: Unidades ===== --}}
            <div data-panel="unidades" style="display:none">
                <form method="POST" action="{{ route('admin.agents.units', $b->id) }}" class="p-6 space-y-3">
                    @csrf
                    <div class="text-[11px] text-ink-500">El broker solo verá expedientes de las unidades seleccionadas.</div>
                    <div class="space-y-2 max-h-[360px] overflow-y-auto">
                        @forelse($units as $u)
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-ink-50 cursor-pointer border border-ink-100">
                                <input type="checkbox" name="unit_ids[]" value="{{ $u->id }}"
                                       {{ in_array($u->id, $assignedIds) ? 'checked' : '' }}
                                       class="w-4 h-4 accent-brand">
                                <div class="flex-1">
                                    <div class="text-[13px] font-semibold text-ink-900">{{ $u->custom_id ?? $u->name }}</div>
                                    <div class="text-[11px] text-ink-500">{{ $u->name }} · {{ $u->status }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="text-[12px] text-ink-500 text-center py-6">No hay unidades disponibles.</div>
                        @endforelse
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Guardar asignación</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</dialog>
