@extends('layouts.app')
@section('titulo', 'Ingresar')
@section('errores_propios', '1')
@section('contenido')
<section class="acceso">
    <div class="tarjeta">
        <h2>Iniciar sesión</h2>
        <p class="tarjeta__intro">Ingresa con tu cuenta para publicar avisos y comentar.</p>
        @include('partials.errores')
        <form method="post" action="{{ route('login.entrar') }}" class="formulario">
            @csrf
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required autofocus autocomplete="email" value="{{ old('email') }}">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit" class="boton boton--ancho">Ingresar</button>
        </form>
    </div>
    <p class="acceso__pie">¿Aún no tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a></p>
</section>
@endsection
