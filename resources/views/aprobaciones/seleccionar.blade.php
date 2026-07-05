@extends('layouts.app')

@section('title', 'Seleccionar Fotografías')

@section('styles')
<style>
    .aprobacion-container { padding: 25px; max-width: 1100px; margin: 0 auto; }
    .page-header { margin-bottom: 20px; }
    .page-header h1 { color: #333; font-size: 26px; }
    .subtitle { color: #666; margin-top: 4px; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; }
    .counter { font-weight: 600; margin-bottom: 15px; }
    .counter.limite { color: #dc3545; }
    .fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .foto-card { border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; cursor: pointer; position: relative; }
    .foto-card.selected { border-color: #667eea; }
    .foto-card img { width: 100%; height: 130px; object-fit: cover; display: block; }
    .foto-card .check-badge { position: absolute; top: 6px; right: 6px; background: #667eea; color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; }
    .badge { padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 500; text-transform: capitalize; display: inline-block; margin-top: 8px; }
    .badge-aprobada { background: #d4edda; color: #155724; }
    .badge-descartada { background: #f8d7da; color: #721c24; }
    .btn-primary { background: #667eea; color: white; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; margin-top: 20px; }
    .btn-primary:disabled { background: #c4c4cc; cursor: not-allowed; }
    .empty-state { text-align: center; padding: 30px; color: #666; }
</style>
@endsection

@section('content')
<div class="aprobacion-container">
    <div class="page-header">
        <h1>Selecciona tus Fotografías</h1>
        <div class="subtitle">Periodo {{ $paquete->mes }}/{{ $paquete->anio }} · Cuota: {{ $paquete->cantidad_requerida }} fotos · Fecha límite: {{ $paquete->fecha_limite->format('d/m/Y') }}</div>
    </div>

    <div class="card-box">
        @if($paquete->fotos->isEmpty())
            <div class="empty-state">Aún no hay fotografías candidatas cargadas para este periodo.</div>
        @elseif($paquete->estado !== 'pendiente')
            <p style="margin-bottom:15px;">Este paquete ya fue procesado ({{ str_replace('_', ' ', $paquete->estado) }}).</p>
            <div class="fotos-grid">
                @foreach($paquete->fotos as $foto)
                    <div class="foto-card">
                        <img src="{{ $foto->url }}" alt="Foto">
                        <div style="padding:8px;">
                            <span class="badge badge-{{ $foto->estado }}">{{ $foto->estado }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <form method="POST" action="{{ route('cliente.aprobaciones.confirmar', $paquete) }}"
                  x-data="{ seleccionadas: [], cuota: {{ $paquete->cantidad_requerida }},
                            toggle(id) { if (this.seleccionadas.includes(id)) { this.seleccionadas = this.seleccionadas.filter(x => x !== id); } else if (this.seleccionadas.length < this.cuota) { this.seleccionadas.push(id); } } }"
                  @submit="if (seleccionadas.length === 0) { $event.preventDefault(); alert('Selecciona al menos una fotografía.'); }">
                @csrf

                <div class="counter" :class="{ 'limite': seleccionadas.length >= cuota }">
                    Seleccionadas: <span x-text="seleccionadas.length"></span> / {{ $paquete->cantidad_requerida }}
                </div>

                <div class="fotos-grid">
                    @foreach($paquete->fotos as $foto)
                        <div class="foto-card" :class="{ selected: seleccionadas.includes({{ $foto->id }}) }" @click="toggle({{ $foto->id }})">
                            <img src="{{ $foto->url }}" alt="Foto candidata">
                            <template x-if="seleccionadas.includes({{ $foto->id }})">
                                <div class="check-badge">✓</div>
                            </template>
                            <input type="checkbox" name="fotos_ids[]" value="{{ $foto->id }}" style="display:none" :checked="seleccionadas.includes({{ $foto->id }})">
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary" :disabled="seleccionadas.length === 0">Confirmar Selección</button>
            </form>
        @endif
    </div>
</div>
@endsection
