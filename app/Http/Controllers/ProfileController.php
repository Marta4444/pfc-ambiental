<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $adminCount = User::where('role', 'admin')->count();
        $canBecomeAdmin = $adminCount < 5 || $user->role === 'admin';
        
        return view('profile.edit', [
            'user' => $user,
            'adminCount' => $adminCount,
            'canBecomeAdmin' => $canBecomeAdmin,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        
        // VALIDACIÓN DE LÍMITE: Si el admin intenta cambiar su rol
        if ($user->role === 'admin' && isset($validated['role'])) {
            
            // Si intenta cambiar de admin a user
            if ($validated['role'] === 'user' && $user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                
                if ($adminCount <= 1) {
                    return Redirect::route('profile.edit')
                        ->withErrors(['role' => 'No puedes cambiar tu rol a usuario. Debe existir al menos un administrador en el sistema.'])
                        ->withInput();
                }
            }
            
            // Si intenta cambiar de user a admin (aunque no debería llegar aquí desde el perfil)
            if ($validated['role'] === 'admin' && $user->role !== 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                
                if ($adminCount >= 5) {
                    return Redirect::route('profile.edit')
                        ->withErrors(['role' => 'No se pueden crear más administradores. Ya existen 5 administradores en el sistema.'])
                        ->withInput();
                }
            }
        }    

        // Actualizar campos validados
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // VALIDACIÓN: Prevenir que el último admin se elimine
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            
            if ($adminCount <= 1) {
                return Redirect::route('profile.edit')
                    ->withErrors(['error' => 'No puedes eliminar tu cuenta. Debe haber al menos un administrador en el sistema.']);
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
