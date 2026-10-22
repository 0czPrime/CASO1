@extends('layouts.app')
@section('titulo', 'Nueva entrada')
@section('contenido')
<section class="lectura">
    <div class="tarjeta">
        <h2>Nueva entrada</h2>
        <form method="post" action="{{ route('entradas.store') }}" class="formulario" enctype="multipart/form-data">
            @csrf
            @include('entradas._formulario')
            <button type="submit" class="boton boton--ancho">Publicar</button>
        </form>
    </div>
</section>
@endsection
