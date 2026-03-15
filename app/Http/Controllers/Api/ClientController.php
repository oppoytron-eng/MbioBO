<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function profil(Request $request)
    {
        return response()->json($request->user());
    }

    public function modifierProfil(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'telephone' => ['sometimes', 'string', 'max:20', 'unique:users,telephone,' . $request->user()->id],
            'photo_url' => ['sometimes', 'url'],
        ]);

        $request->user()->update($data);

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $request->user()->fresh(),
        ]);
    }
}
