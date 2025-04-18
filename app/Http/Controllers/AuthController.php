<?php

namespace App\Http\Controllers;

use App\Services\BanqueCentraleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    protected $banqueCentraleService;

    public function __construct(BanqueCentraleService $banqueCentraleService)
    {
        $this->banqueCentraleService = $banqueCentraleService;
    }

    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);


        $response = $this->banqueCentraleService->signin(
            $request->username,
            $request->password
        );

        if ($response) {
            session(['banque_centrale_token' => $response['token']]);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'message' => 'Identifiants invalides.',
        ]);
    }

    public function logout(Request $request)
    {
        $this->banqueCentraleService->logout();
        $request->session()->forget('banque_centrale_token');
        return redirect('/login');
    }

    public function showResetPassword()
    {
        return Inertia::render('Auth/ResetPassword');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if ($this->banqueCentraleService->resetPassword(
            $request->current_password,
            $request->new_password
        )) {
            return redirect('/login')->with('success', 'Mot de passe modifié avec succès.');
        }

        return back()->withErrors([
            'message' => 'Erreur lors de la modification du mot de passe.',
        ]);
    }

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        if ($this->banqueCentraleService->forgotPassword($request->email)) {
            return back()->with('success', 'Un email de réinitialisation a été envoyé.');
        }

        return back()->withErrors([
            'message' => 'Erreur lors de l\'envoi de l\'email de réinitialisation.',
        ]);
    }
} 