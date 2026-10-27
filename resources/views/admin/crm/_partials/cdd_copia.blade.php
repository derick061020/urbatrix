{{--
  Copias archivadas del contrato firmado o de la escritura ($key en kyc_docs).
  Sin copia todavía, un formulario para subirla: reenvía la misma acción con
  su fecha, que el controlador vuelve a guardar sin cambiarla.
--}}
@php $copias = (array) (($cdd->kyc_docs ?? [])[$key] ?? []); @endphp
@foreach($copias as $i => $p)
    <a href="{{ route('admin.crm.cdd.doc', [$reservation->id, $key, $i]) }}" class="crm-btn crm-btn-ghost !py-0.5 !px-2 !text-[11px]"><i class="pi pi-file-pdf"></i> {{ __('Copia :n', ['n' => $i + 1]) }}</a>
@endforeach
@if(! $copias)
    <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2 m-0">@csrf
        <input type="hidden" name="action" value="{{ $action }}"><input type="hidden" name="{{ $date[0] }}" value="{{ $date[1]->toDateString() }}">
        <input type="file" name="{{ $action === 'spa' ? 'spa_file' : 'deed_file' }}" required accept=".pdf,.jpg,.jpeg,.png" class="text-[11px] max-w-[180px]">
        <button class="crm-btn crm-btn-ghost !py-0.5 !px-2 !text-[11px]">{{ __('Subir copia') }}</button>
    </form>
@endif
