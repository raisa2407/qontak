<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class MekariAuthController extends Controller
{
    private $base_url = "https://service-chat.qontak.com/api/open";

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
        ]);

        $response = Http::post($this->base_url . '/v1/oauth/token', [
            'username' => $request->username,
            'password' => $request->password,
            'grant_type' => 'password',
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
        ]);

        if ($response->status() == 201) {
            $data = $response->json();
            
            Session::put('qontak_token', $data['access_token']);
            Session::put('qontak_refresh_token', $data['refresh_token']);
            Session::put('qontak_expires_at', now()->addSeconds($data['expires_in']));
            Session::put('qontak_client_id', $request->client_id);
            Session::put('qontak_client_secret', $request->client_secret);

            return redirect()->route('rooms.index')->with('success', 'Login successful!');
        }

        return back()->withErrors(['error' => 'Login failed. Please check your credentials.'])->withInput();
    }

    public function logout()
    {
        Session::forget('qontak_token');
        Session::forget('qontak_refresh_token');
        Session::forget('qontak_expires_at');
        Session::forget('qontak_client_id');
        Session::forget('qontak_client_secret');

        return redirect()->route('login')->with('success', 'Logged out successfully!');
    }

    public function refreshToken()
    {
        $refresh_token = Session::get('qontak_refresh_token');
        $client_id = Session::get('qontak_client_id');
        $client_secret = Session::get('qontak_client_secret');

        if (!$refresh_token || !$client_id || !$client_secret) {
            return redirect()->route('login');
        }

        $response = Http::post($this->base_url . '/v1/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ]);

        if ($response->status() == 201) {
            $data = $response->json();
            
            Session::put('qontak_token', $data['access_token']);
            Session::put('qontak_refresh_token', $data['refresh_token']);
            Session::put('qontak_expires_at', now()->addSeconds($data['expires_in']));

            return true;
        }

        return false;
    }
}