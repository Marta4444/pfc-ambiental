<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Contar administradores actuales para implementar un límite de solo 5 Admins en total.
        $adminCount = User::where('role', 'admin')->count();
        $canCreateAdmin = $adminCount < 5;
        
        return view('auth.register', compact('canCreateAdmin', 'adminCount'));
    
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'agent_num' => ['required', 'string', 'max:50', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:user,admin',
        ], [
            'agent_num.required' => 'El número de agente es obligatorio.',
            'agent_num.unique' => 'Este número de agente ya está registrado.',
        ]);

        $role = $request->role ?? 'user';  //si por lo que fuera no tuviera rol, le asigna User por defecto

        //Validar que no haya ya 5 Admins en la BD cuando se intenta crear uno nuevo.
        if ($role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            
            if ($adminCount >= 5) {
                return back()
                    ->withErrors(['role' => 'No se pueden crear más administradores. Ya existen 5 administradores en el sistema. Solo se pueden registrar usuarios normales.'])
                    ->withInput();
            }
        }

        $user = User::create([
            'name' => $request->name,
            'agent_num' => $request->agent_num,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
