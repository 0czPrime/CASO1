<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function mostrarRegistro()
    {
        return view('auth.registro');
    }

    public function registrarUsuario(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
        ]);

        return redirect()->route('login')->with('status', 'Cuenta creada. Ya puedes iniciar sesión.');
    }

    public function mostrarLogin()
    {
        return view('auth.login');
    }

    public function iniciarSesion(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

if (! Auth::attempt($credenciales)) {
            return back()->withErrors(['email' => 'Correo o contraseña incorrectos.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('entradas.index');
    }
}
