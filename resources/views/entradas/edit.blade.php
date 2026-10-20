@extends('layouts.app')
@section('titulo', 'Editar entrada')
@section('contenido')
<section class="lectura">
    <div class="tarjeta">
        <h2>Editar entrada</h2>
        <form method="post" action="{{ route('entradas.update', $entrada) }}" class="formulario" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('entradas._formulario')
            <button type="submit" class="boton boton--ancho">Guardar cambios</button>
        </form>
    </div>
</section>
@endsection
