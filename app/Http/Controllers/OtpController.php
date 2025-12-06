<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OtpController extends Controller
{
    public function showOtpForm(Request $request)
    {
        return view('auth.otp', ['email' => $request->email]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code != $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'OTP expired']);
        }

        // OTP correct → login user
        Auth::login($user);

        $userId = Auth::id();

        DB::table('logs')->insert([
            'user_id' => $userId,
            'time_in' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Clear OTP
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return redirect()->route('pos.index');
    }
}
