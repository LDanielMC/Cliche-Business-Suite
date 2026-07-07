@extends('layouts.app')

@section('title', 'Paquete de Aprobación')

@section('styles')
<style>
    .aprobacion-container { padding: 25px; max-width: 1100px; margin: 0 auto; }
    .page-header { margin-bottom: 20px; }
    .page-header h1 { color: #333; font-size: 26px; }
    .subtitle { color: #666; margin-top: 4px; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
    .upload-row { display: flex; gap: 12px; align-items: center; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px; }
    .fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; margin-top: 20px; }
    .foto-card { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
    .foto-card img { width: 100%; height: 130px; object-fit: cover; display: block; }
    .foto-card-footer { padding: 8px; display: flex; justify-content: space-between; align-items: center; }
    .badge { padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 500; text-transform: capitalize; }
    .badge-pendiente { background: #cce5ff; color: #004085; }
    .badge-conservada { background: #fff3cd; color: #856404; }
    .badge-aprobada { background: #d4edda; color: #155724; }
    .badge-descartada { background: #f8d7da; color: #721c24; }
    .empty-state { text-align: center; padding: 30px; color: #666; }
</style>
@endsection

@section('content')
<div class="aprobacion-container">
    <div class="page-header">
        <h1>Paquete de Aprobación — {{ $paquete->cliente->nombre_negocio }}</h1>
        <div class="subtitle">Periodo {{ ucfirst($paquete->mes_revision_legible) }} · Cuota: {{ $paquete->cantidad_requerida }} fotos · Fecha límite: {{ $paquete->fecha_limite->format('d/m/Y') }} · Estado: {{ str_replace('_', ' ', $paquete->estatus) }}</div>
    </div>

    <div class="card-box">
        <h3>Subir fotografías candidatas</h3>
        <form method="POST" action="{{ route('aprobaciones.fotos.store', $paquete) }}" enctype="multipart/form-data" style="margin-top:15px;">
            @csrf
            <div class="upload-row">
                <input type="file" name="fotos[]" multiple accept="image/*" required>
                <button type="submit" class="btn-primary">Subir</button>
            </div>
            @error('fotos')
                <p style="color:#dc3545; margin-top:8px;">{{ $message }}</p>
            @enderror
        </form>
    </div>

    <div class="card-box">
        <h3>Fotografías candidatas ({{ $paquete->fotos->count() }})</h3>
        @if($paquete->fotos->count() > 0)
            <div class="fotos-grid">
                @foreach($paquete->fotos as $foto)
                    <div class="foto-card">
                        <img src="{{ $foto->url }}" alt="Foto candidata">
                        <div class="foto-card-footer">
                            <span class="badge badge-{{ $foto->estatus }}">{{ $foto->estatus }}</span>
                            @if($foto->estatus !== 'aprobada')
                                <form action="{{ route('aprobaciones.fotos.destroy', [$paquete, $foto]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta fotografía?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Quitar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">Aún no se han subido fotografías candidatas.</div>
        @endif
    </div>
</div>
@endsection
