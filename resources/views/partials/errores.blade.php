@if ($errors->any())
    <div class="alerta alerta--error" role="alert">
        <ul>
            @foreach ($errors->all() as $mensaje)
                <li>{{ $mensaje }}</li>
            @endforeach
        </ul>
    </div>
@endif
