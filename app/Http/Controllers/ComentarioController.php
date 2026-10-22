<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Entrada;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request, Entrada $entrada)
    {
        $datos = $request->validate([
            'texto' => ['required', 'string', 'max:1000'],
        ]);

        $comentario = new Comentario($datos);
        $comentario->entrada_id = $entrada->id;
        $comentario->user_id = $request->user()->id;
        $comentario->save();

        return redirect()->route('entradas.show', $entrada)->with('status', 'Comentario publicado.');
    }
}
