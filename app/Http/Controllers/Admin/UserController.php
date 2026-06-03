<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'plan' => 'required|string|in:free,pro,business',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'plan' => $validated['plan'],
        ]);

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'nullable|string|exists:roles,name',
            'plan' => 'required|string|in:free,pro,business'
        ]);

        if ($user->hasRole('super-admin') && auth()->id() !== $user->id) {
            return redirect()->back()->with('error', 'Cannot modify another super-admin.');
        }

        // Update plan
        $user->update([
            'plan' => $validated['plan']
        ]);

        // Update role
        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        } else {
            // Prevent removing own super-admin role
            if ($user->id === auth()->id() && $user->hasRole('super-admin')) {
                return redirect()->back()->with('error', 'You cannot remove your own super-admin role.');
            }
            $user->syncRoles([]);
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Cannot delete a super-admin.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Cannot delete yourself.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
