<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'none'; style-src 'self'">
    <title>@yield('titulo') - Blog del Semillero</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<header class="cabecera">
    <div class="contenedor cabecera__fila">
        <a class="marca" href="{{ route('entradas.index') }}">
            <span class="marca__nombre">Blog del Semillero</span>
            <span class="marca__lema">Avisos y comunicados</span>
        </a>
        <nav class="menu">
            <a href="{{ route('entradas.index') }}">Inicio</a>
            @auth
                <a href="{{ route('entradas.create') }}">Nueva entrada</a>
                <span class="menu__usuario">{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}" class="en-linea">
                    @csrf
                    <button type="submit" class="boton-enlace">Salir</button>
                </form>
            @else
                <a href="{{ route('login') }}">Ingresar</a>
                <a href="{{ route('registro') }}" class="boton">Registrarse</a>
            @endauth
        </nav>
    </div>
</header>

<main class="contenedor">
    @if (session('status'))
        <div class="alerta" role="status">{{ session('status') }}</div>
    @endif
    @hasSection('errores_propios')
    @else
        @include('partials.errores')
    @endif
    @yield('contenido')
</main>

<footer class="pie">
    <div class="contenedor">Blog del Semillero · Proyecto de práctica, Tecnología en Análisis y Desarrollo de Software (ADSO) · SENA</div>
</footer>
</body>
</html>
