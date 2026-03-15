<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $roles = AdminRole::withCount('utilisateurs')->get();
        $admins = Utilisateur::where('role', 'Admin')->get();

        return view('admin.roles.index', compact('roles', 'admins'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:admin_roles,name'],
            'description' => ['nullable', 'string'],
        ]);

        AdminRole::create($data);

        return back()->with('status', 'Rôle ajouté.');
    }

    public function toggle(AdminRole $role, Utilisateur $utilisateur)
    {
        $has = $utilisateur->hasAdminRole($role->name);

        if ($has) {
            $utilisateur->adminRoles()->detach($role);
        } else {
            $utilisateur->adminRoles()->attach($role);
        }

        return back()->with('status', 'Attribution de rôle mise à jour.');
    }
}
