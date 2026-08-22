<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Auto-login: grab the first user and store in session.
     */
    public function login(Request $request): RedirectResponse
    {
        $user = User::first();

        if (!$user) {
            $user = User::create([
                'name' => 'Usuario Demo',
                'email' => 'demo@elcantarito.com',
                'password' => bcrypt('password'),
            ]);
        }

        $request->session()->put('auth_user_id', $user->id);
        $request->session()->save();

        return redirect()->route('home');
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_user_id');
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
