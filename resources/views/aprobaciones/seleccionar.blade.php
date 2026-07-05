@extends('layouts.app')

@section('title', 'Seleccionar Fotografías')

@section('styles')
<style>
    .aprobacion-container { padding: 25px; max-width: 1100px; margin: 0 auto; }
    .page-header { margin-bottom: 20px; }
    .page-header h1 { color: #333; font-size: 26px; }
    .subtitle { color: #666; margin-top: 4px; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; }
    .instructions { background: #eef1fd; color: #4a4a8a; padding: 12px 16px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
    .counter { font-weight: 600; margin-bottom: 15px; }
    .counter.limite { color: #dc3545; }
    .fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
    .foto-card { border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
    .foto-card.decision-aprobar { border-color: #14b8a6; }
    .foto-card.decision-conservar { border-color: #ffc107; }
    .foto-card.decision-descartar { border-color: #dc3545; opacity: 0.6; }
    .foto-card img { width: 100%; height: 150px; object-fit: cover; display: block; }
    .decision-buttons { display: flex; gap: 4px; padding: 8px; }
    .decision-buttons button { flex: 1; padding: 6px 4px; font-size: 11px; border: 1px solid #e0e0e0; background: white; border-radius: 5px; cursor: pointer; }
    .decision-buttons button.active-aprobar { background: #14b8a6; color: white; border-color: #14b8a6; }
    .decision-buttons button.active-conservar { background: #ffc107; color: #333; border-color: #ffc107; }
    .decision-buttons button.active-descartar { background: #dc3545; color: white; border-color: #dc3545; }
    .decision-buttons button:disabled { opacity: 0.4; cursor: not-allowed; }
    .badge { padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 500; text-transform: capitalize; display: inline-block; margin-top: 8px; }
    .badge-aprobada { background: #d4edda; color: #155724; }
    .badge-descartada { background: #f8d7da; color: #721c24; }
    .badge-conservada { background: #fff3cd; color: #856404; }
    .btn-primary { background: #667eea; color: white; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; margin-top: 20px; }
    .btn-primary:disabled { background: #c4c4cc; cursor: not-allowed; }
    .empty-state { text-align: center; padding: 30px; color: #666; }
</style>
@endsection

@section('content')
<div class="aprobacion-container">
    <div class="page-header">
        <h1>Selecciona tus Fotografías</h1>
        <div class="subtitle">Periodo {{ ucfirst($paquete->mes_revision_legible) }} · Cuota: {{ $paquete->cantidad_requerida }} fotos · Fecha límite: {{ $paquete->fecha_limite->format('d/m/Y') }}</div>
    </div>

    <div class="card-box">
        @if($paquete->fotos->isEmpty())
            <div class="empty-state">Aún no hay fotografías candidatas cargadas para este periodo.</div>
        @elseif($paquete->estatus !== 'pendiente')
            <p style="margin-bottom:15px;">Este paquete ya fue procesado ({{ str_replace('_', ' ', $paquete->estatus) }}).</p>
            <div class="fotos-grid">
                @foreach($paquete->fotos as $foto)
                    <div class="foto-card">
                        <img src="{{ $foto->url }}" alt="Foto">
                        <div style="padding:8px;">
                            <span class="badge badge-{{ $foto->estatus }}">{{ $foto->estatus }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="instructions">
                Elige hasta <strong>{{ $paquete->cantidad_requerida }}</strong> fotografías para publicar este ciclo.
                Para el resto, decide si quieres <strong>conservarlas</strong> para el próximo ciclo o <strong>descartarlas</strong> definitivamente.
            </div>

            <form method="POST" action="{{ route('cliente.aprobaciones.confirmar', $paquete) }}"
                  x-data="{
                        cuota: {{ $paquete->cantidad_requerida }},
                        decisiones: {},
                        setDecision(id, valor) {
                            if (valor === 'aprobar' && this.countAprobar() >= this.cuota && this.decisiones[id] !== 'aprobar') return;
                            this.decisiones[id] = valor;
                        },
                        countAprobar() {
                            return Object.values(this.decisiones).filter(v => v === 'aprobar').length;
                        },
                        allDecided(total) {
                            return Object.keys(this.decisiones).length === total && this.countAprobar() > 0;
                        }
                  }"
                  @submit="if (!allDecided({{ $paquete->fotos->count() }})) { $event.preventDefault(); alert('Debes decidir qué hacer con cada fotografía y aprobar al menos una.'); }">
                @csrf

                <div class="counter" :class="{ 'limite': countAprobar() >= cuota }">
                    Aprobadas para este ciclo: <span x-text="countAprobar()"></span> / {{ $paquete->cantidad_requerida }}
                </div>

                <div class="fotos-grid">
                    @foreach($paquete->fotos as $foto)
                        <div class="foto-card"
                             :class="{ 'decision-aprobar': decisiones[{{ $foto->id }}] === 'aprobar', 'decision-conservar': decisiones[{{ $foto->id }}] === 'conservar', 'decision-descartar': decisiones[{{ $foto->id }}] === 'descartar' }">
                            <img src="{{ $foto->url }}" alt="Foto candidata">
                            <div class="decision-buttons">
                                <button type="button" :class="{ 'active-aprobar': decisiones[{{ $foto->id }}] === 'aprobar' }" :disabled="countAprobar() >= cuota && decisiones[{{ $foto->id }}] !== 'aprobar'" @click="setDecision({{ $foto->id }}, 'aprobar')">Aprobar</button>
                                <button type="button" :class="{ 'active-conservar': decisiones[{{ $foto->id }}] === 'conservar' }" @click="setDecision({{ $foto->id }}, 'conservar')">Conservar</button>
                                <button type="button" :class="{ 'active-descartar': decisiones[{{ $foto->id }}] === 'descartar' }" @click="setDecision({{ $foto->id }}, 'descartar')">Descartar</button>
                            </div>
                            <template x-if="decisiones[{{ $foto->id }}] === 'aprobar'">
                                <input type="hidden" name="aprobadas[]" value="{{ $foto->id }}">
                            </template>
                            <template x-if="decisiones[{{ $foto->id }}] === 'conservar'">
                                <input type="hidden" name="conservar[]" value="{{ $foto->id }}">
                            </template>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary" :disabled="!allDecided({{ $paquete->fotos->count() }})">Confirmar Selección</button>
            </form>
        @endif
    </div>
</div>
@endsection
