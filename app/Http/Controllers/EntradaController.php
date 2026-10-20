<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Entrada;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EntradaController extends Controller
{
    private function reglas(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:120'],
            'contenido' => ['required', 'string', 'min:10', 'max:5000'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'imagen' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_imagen' => ['nullable', 'boolean'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'imagen.uploaded' => 'No se pudo subir la imagen. Verifica que pese menos de 2 MB.',
            'imagen.mimes' => 'La imagen debe ser un archivo JPG, PNG o WebP.',
            'imagen.max' => 'La imagen no puede superar los 2 MB.',
        ];
    }

    private function borrarImagen(Entrada $entrada): void
    {
        if ($entrada->imagen) {
            Storage::disk('public')->delete($entrada->imagen);
        }
    }

    public function index(Request $request)
    {
        $categoriaId = $request->integer('categoria') ?: null;

        $entradas = Entrada::with(['autor', 'categoria'])
            ->when($categoriaId, fn ($consulta) => $consulta->where('categoria_id', $categoriaId))
            ->withCount('comentarios')
            ->latest('id')
            ->get();

        return view('entradas.index', [
            'entradas' => $entradas,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function show(Entrada $entrada)
    {
        $entrada->load(['autor', 'categoria', 'comentarios.autor']);

        return view('entradas.show', ['entrada' => $entrada]);
    }

    public function create()
    {
        return view('entradas.create', ['categorias' => Categoria::orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        $entrada = new Entrada(Arr::except($datos, ['imagen', 'quitar_imagen']));
        $entrada->autor_id = $request->user()->id;

        if ($request->hasFile('imagen')) {
            $entrada->imagen = $request->file('imagen')->store('entradas', 'public');
        }

        $entrada->save();

        return redirect()->route('entradas.show', $entrada)->with('status', 'Entrada publicada.');
    }

    public function edit(Entrada $entrada)
    {
        Gate::authorize('update', $entrada);

        return view('entradas.edit', [
            'entrada' => $entrada,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Entrada $entrada)
    {
        Gate::authorize('update', $entrada);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        $entrada->fill(Arr::except($datos, ['imagen', 'quitar_imagen']));

        if ($request->hasFile('imagen')) {
            $this->borrarImagen($entrada);
            $entrada->imagen = $request->file('imagen')->store('entradas', 'public');
        } elseif ($request->boolean('quitar_imagen')) {
            $this->borrarImagen($entrada);
            $entrada->imagen = null;
        }

        $entrada->save();

        return redirect()->route('entradas.show', $entrada)->with('status', 'Entrada actualizada.');
    }

    public function destroy(Entrada $entrada)
    {
        Gate::authorize('delete', $entrada);

        $this->borrarImagen($entrada);
        $entrada->delete();

        return redirect()->route('entradas.index')->with('status', 'Entrada eliminada.');
    }
}
