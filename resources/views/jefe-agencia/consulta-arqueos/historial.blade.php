
@extends('layouts.jefe-agencia')

@section('title', 'Historial de Arqueos')

@section('content')
<style>
.ha-heading {
    margin-bottom: 24px;
}

.ha-heading h2 {
    margin: 0 0 6px;
    color: #15395f;
    font-size: 27px;
}

.ha-heading p {
    color: #667c90;
    margin: 0;
}

.ha-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
    gap: 20px;
}

.ha-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 17px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(20, 56, 92, .04);
}

.ha-person {
    display: flex;
    gap: 14px;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e7eef4;
}

.ha-avatar {
    flex: 0 0 52px;
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: #e6f0fb;
    color: #164c96;
    font-size: 21px;
    font-weight: 800;
}

.ha-name {
    font-size: 16px;
    font-weight: 800;
    color: #15395f;
    line-height: 1.4;
}

.ha-label {
    font-size: 12px;
    color: #70849a;
    margin-top: 4px;
}

.ha-subtitle {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .04em;
    color: #52708b;
    text-transform: uppercase;
    padding: 16px 20px 7px;
}

.ha-entries {
    padding: 0 20px 10px;
}

.ha-entry {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #edf1f5;
}

.ha-entry:last-child {
    border-bottom: 0;
}

.ha-num {
    font-weight: 750;
    color: #183b60;
    font-size: 13px;
}

.ha-meta {
    font-size: 11px;
    color: #738599;
    margin-top: 5px;
}

.ha-state {
    font-size: 11px;
    font-weight: 800;
    background: #edf4fb;
    color: #1d5c99;
    border-radius: 20px;
    padding: 5px 8px;
    white-space: nowrap;
}

.ha-footer {
    padding: 16px 20px 20px;
}

.ha-btn {
    display: block;
    text-align: center;
    background: #164c96;
    color: #fff !important;
    text-decoration: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: 800;
}

.ha-btn:hover {
    background: #103a76;
}

.ha-empty {
    padding: 15px 0;
    color: #71869a;
    font-size: 13px;
}
</style>

<div class="ha-heading">
    <h2>Historial de Arqueos</h2>
    <p>
        Consulta por receptor. Cada ficha muestra sus últimos
        tres arqueos registrados en las agencias bajo su
        responsabilidad.
    </p>
</div>

<div class="ha-grid">
    @forelse($receptores as $receptor)
        @php
            $registros = $ultimos->get(
                $receptor->id,
                collect()
            );

            $nombre = $receptor->nombre_completo;
        @endphp

        <article class="ha-card">

            <div class="ha-person">
                <div class="ha-avatar">
                    {{ mb_strtoupper(mb_substr($nombre, 0, 1)) }}
                </div>

                <div>
                    <div class="ha-name">
                        {{ $nombre }}
                    </div>

                    <div class="ha-label">
                        Receptor ·
                        {{ $registros->count() }}
                        últimos arqueos
                    </div>
                </div>
            </div>

            <div class="ha-subtitle">
                Últimos 3 arqueos
            </div>

            <div class="ha-entries">

                @forelse($registros as $arqueo)

                    <div class="ha-entry">

                        <div>
                            <div class="ha-num">
                                <a
                                    href="{{ route(
                                        'jefeagencia.historial-arqueos.show',
                                        $arqueo->id
                                    ) }}"
                                    style="color:inherit;text-decoration:none"
                                >
                                    {{ $arqueo->numero_arqueo }}
                                </a>
                            </div>

                            <div class="ha-meta">
                                {{ $arqueo->fecha_operativa?->format('d/m/Y') }}

                                ·

                                {{ $arqueo->agencia?->nombre
                                    ?? $arqueo->agencia_nombre_historico }}

                                ·

                                {{ $arqueo->tipo }}
                            </div>

                            <div class="ha-meta">
                                Q {{ number_format(
                                    (float) $arqueo->total_arqueado,
                                    2
                                ) }}
                            </div>
                        </div>

                        <span class="ha-state">
                            {{ $arqueo->estado }}
                        </span>

                    </div>

                @empty

                    <div class="ha-empty">
                        Sin arqueos registrados
                    </div>

                @endforelse

            </div>

            <div class="ha-footer">
                <a
                    class="ha-btn"
                    href="{{ route(
                        'jefeagencia.historial-arqueos.receptor',
                        $receptor->id
                    ) }}"
                >
                    Ver arqueos completos
                </a>
            </div>

        </article>

    @empty

        <div
            style="
                background:#fff;
                border:1px solid #dce6ef;
                border-radius:15px;
                padding:30px;
                color:#60748a;
            "
        >
            No hay receptores asignados actualmente
            a las agencias autorizadas.
        </div>

    @endforelse
</div>

<div style="margin-top:24px">
    {{ $receptores->links() }}
</div>

@endsection
