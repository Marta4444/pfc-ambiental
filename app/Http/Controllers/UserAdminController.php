<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserAdminController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtro por rol
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtro por estado activo
        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        // Búsqueda por nombre o email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('agent_num', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleActive(User $user)
    {
        // No permitir desactivar al último admin activo
        if ($user->role === 'admin' && $user->active) {
            $activeAdminCount = User::where('role', 'admin')->where('active', true)->count();
            
            if ($activeAdminCount <= 1) {
                return redirect()->back()->with('error', 'No se puede desactivar. Debe haber al menos un administrador activo en el sistema.');
            }
        }

        $user->update(['active' => !$user->active]);

        $status = $user->active ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "Usuario {$user->name} {$status} correctamente.");
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'agent_num' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'agent_num' => $validated['agent_num'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'active' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', "Usuario {$user->name} creado correctamente.");
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'agent_num' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,user'],
        ]);

        // No permitir cambiar el rol del último admin activo
        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $activeAdminCount = User::where('role', 'admin')->where('active', true)->count();
            
            if ($activeAdminCount <= 1) {
                return redirect()->back()->with('error', 'No se puede cambiar el rol. Debe haber al menos un administrador en el sistema.');
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'agent_num' => $validated['agent_num'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', "Usuario {$user->name} actualizado correctamente.");
    }
}
