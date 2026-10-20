@extends('layouts.app')
@section('titulo', 'Inicio')
@section('contenido')
@php $activa = (int) request('categoria'); @endphp
<div class="portada">
    <section>
        <div class="encabezado-pagina">
            <h2>Avisos del semillero</h2>
            <p>Convocatorias, eventos y resultados publicados por los integrantes.</p>
        </div>

        <ul class="filtros">
            <li><a href="{{ route('entradas.index') }}" @class(['activo' => $activa === 0])>Todas</a></li>
            @foreach ($categorias as $c)
                <li><a href="{{ route('entradas.index', ['categoria' => $c->id]) }}" @class(['activo' => $activa === (int) $c->id])>{{ $c->nombre }}</a></li>
            @endforeach
        </ul>

        @forelse ($entradas as $en)
            <article @class(['aviso', 'aviso--con-imagen' => $en->imagen])>
                <div class="aviso__cuerpo">
                    <p class="aviso__categoria">{{ $en->categoria->nombre }}</p>
                    <h3 class="aviso__titulo"><a href="{{ route('entradas.show', $en) }}">{{ $en->titulo }}</a></h3>
                    <p class="aviso__resumen">{{ \Illuminate\Support\Str::limit($en->contenido, 170) }}</p>
                    <p class="meta">
                        Por {{ $en->autor->name }} · {{ $en->created_at->format('d/m/Y') }} ·
                        {{ $en->comentarios_count }} {{ $en->comentarios_count === 1 ? 'comentario' : 'comentarios' }}
                    </p>
                </div>
                @if ($en->imagen)
                    <a class="aviso__imagen" href="{{ route('entradas.show', $en) }}" tabindex="-1">
                        <img src="{{ $en->imagen_url }}" alt="" loading="lazy">
                    </a>
                @endif
            </article>
        @empty
            <p class="vacio">No hay avisos publicados en esta categoría.</p>
        @endforelse
    </section>

    <aside>
        <div class="panel">
            <h3>Publica un aviso</h3>
            @auth
                <p>Comparte una convocatoria, un evento o un resultado con el resto del semillero.</p>
                <div class="acciones"><a href="{{ route('entradas.create') }}" class="boton">Nueva entrada</a></div>
            @else
                <p>Solo los integrantes registrados pueden publicar y comentar. Cualquier persona puede leer los avisos.</p>
                <div class="acciones">
                    <a href="{{ route('login') }}" class="boton">Ingresar</a>
                    <a href="{{ route('registro') }}" class="boton boton--secundario">Registrarse</a>
                </div>
            @endauth
        </div>
    </aside>
</div>
@endsection
