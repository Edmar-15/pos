<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showRegister() {
        return view('auth.register');
    }

    public function showLogin() {
        return view('auth.login');
    }

    public function register(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create($validated);

        Auth::login($user);

        return redirect()->route('pos.index');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'credentials' => 'Sorry, incorrect credentials'
            ]);
        }

        // If password correct → normal login
        if (Auth::attempt($validated)) {
            $request->session()->regenerate();

            $userId = Auth::id();

            DB::table('logs')->insert([
                'user_id' => $userId,
                'time_in' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('pos.index');
        }

        // Password incorrect → Send OTP
        $otp = rand(100000, 999999);

        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Email OTP
        Mail::raw("Your OTP Code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Login OTP Code');
        });

        return redirect()->route('otp.verify.form', ['email' => $user->email]);
    }

    public function logout(Request $request) {
        $userId = Auth::id();

        $log = DB::table('logs')
            ->where('user_id', $userId)
            ->whereNull('time_out')
            ->orderBy('id', 'desc')
            ->first();

        if ($log) {
            DB::table('logs')
                ->where('id', $log->id)
                ->update([
                    'time_out' => now(),
                    'updated_at' => now(),
                ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('show.login');
    }
    
}
