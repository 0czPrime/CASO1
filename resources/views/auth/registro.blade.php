@extends('layouts.app')
@section('titulo', 'Registro')
@section('errores_propios', '1')
@section('contenido')
<section class="acceso">
    <div class="tarjeta">
        <h2>Crear cuenta</h2>
        <p class="tarjeta__intro">Regístrate para publicar avisos del semillero y participar en los comentarios.</p>
        @include('partials.errores')
        <form method="post" action="{{ route('registro.guardar') }}" class="formulario">
            @csrf
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" maxlength="50" required autofocus autocomplete="name" value="{{ old('name') }}">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required autocomplete="email" value="{{ old('email') }}">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password">
            <p class="ayuda">Mínimo 8 caracteres.</p>
            <button type="submit" class="boton boton--ancho">Crear cuenta</button>
        </form>
    </div>
    <p class="acceso__pie">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</section>
@endsection
