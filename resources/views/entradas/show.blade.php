@extends('layouts.app')
@section('titulo', $entrada->titulo)
@section('contenido')
<section class="lectura">
    <article>
        <p class="aviso__categoria">{{ $entrada->categoria->nombre }}</p>
        <h2>{{ $entrada->titulo }}</h2>
        <p class="meta">Por {{ $entrada->autor->name }} · {{ $entrada->created_at->format('d/m/Y H:i') }}</p>
        @if ($entrada->imagen)
            <figure class="entrada-imagen">
                <img src="{{ $entrada->imagen_url }}" alt="Imagen de la entrada: {{ $entrada->titulo }}">
            </figure>
        @endif
        <div class="texto">{{ $entrada->contenido }}</div>
    </article>

    @can('update', $entrada)
        <div class="acciones">
            <a href="{{ route('entradas.edit', $entrada) }}" class="boton boton--secundario">Editar</a>
            <form method="post" action="{{ route('entradas.destroy', $entrada) }}" class="en-linea">
                @csrf
                @method('DELETE')
                <button type="submit" class="boton boton--peligro">Eliminar</button>
            </form>
        </div>
    @endcan

    <h3>Comentarios ({{ $entrada->comentarios->count() }})</h3>
    @forelse ($entrada->comentarios as $c)
        <div class="comentario">
            <strong>{{ $c->autor->name }}</strong>
            <span class="meta">· {{ $c->created_at->format('d/m/Y H:i') }}</span>
            <p class="texto">{{ $c->texto }}</p>
        </div>
    @empty
        <p class="vacio">Aún no hay comentarios.</p>
    @endforelse

    @auth
        <form method="post" action="{{ route('comentarios.store', $entrada) }}" class="formulario">
            @csrf
            <label for="texto">Tu comentario</label>
            <textarea id="texto" name="texto" maxlength="1000" required></textarea>
            <button type="submit" class="boton">Comentar</button>
        </form>
    @else
        <p><a href="{{ route('login') }}">Inicia sesión</a> para comentar.</p>
    @endauth
</section>
@endsection
