<label for="titulo">Título</label>
<input type="text" id="titulo" name="titulo" maxlength="120" required value="{{ old('titulo', $entrada->titulo ?? '') }}">
<label for="categoria_id">Categoría</label>
<select name="categoria_id" id="categoria_id">
    @foreach ($categorias as $c)
        <option value="{{ $c->id }}" @selected((int) old('categoria_id', $entrada->categoria_id ?? 0) === (int) $c->id)>{{ $c->nombre }}</option>
    @endforeach
</select>
<label for="contenido">Contenido</label>
<textarea id="contenido" name="contenido" rows="8" maxlength="5000" required>{{ old('contenido', $entrada->contenido ?? '') }}</textarea>

<label for="imagen">Imagen (opcional)</label>
<input type="file" id="imagen" name="imagen" accept="image/png,image/jpeg,image/webp">
<p class="ayuda">Formatos JPG, PNG o WebP. Máximo 2 MB.</p>
@if (! empty($entrada->imagen))
    <div class="imagen-actual">
        <img src="{{ $entrada->imagen_url }}" alt="Imagen actual de la entrada">
        <label class="casilla"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la imagen actual</label>
    </div>
@endif
