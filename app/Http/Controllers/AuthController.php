<?php
namespace App\Http\Controllers;

use App\Models\OtpCode;
use Illuminate\Support\Carbon;
use App\Helpers\SmsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput();
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgotPassword');
    }

     public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
        ]);

        $verification_code = rand(100000, 999999);

        OtpCode::create([
            'phone' => $request->phone,
            'code' => $verification_code,
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

         // Send SMS
       $this->sendSmsVerification($request->phone, $verification_code);

       // Redirect to verification page
        session(['phone' => $request->phone]);
        return redirect()->route('verify-otp-password');
    }

    public function resendOtp($phone)
    {
        // Optional: invalidate previous OTPs
        OtpCode::where('phone', $phone)->update(['is_used' => 1]);

        // Generate new OTP
        $otpCode = rand(100000, 999999);

        // Save OTP
        OtpCode::create([
            'phone' => $phone,
            'code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $this->sendSmsVerification($phone, $otpCode);

       // Redirect to verification page
        session(['phone' => $phone]);
        return redirect()->route('verify-otp-password');
    }

        protected function sendSmsVerification($phone, $code)
    {
        $message = 'OTP Code is ' . $code;
        SmsHelper::send($phone, $message);
    }

        public function showVerificationForm(Request $request)
    {
        // Get phone number from session
        $phone = session('phone');

        // If phone not in session, redirect or show error
        if (!$phone) {
            return redirect()->route('forgotPassword')->with('error', 'Session expired. Please try again.');
        }

        // Pass to view
        return view('auth.verify-otp-password', compact('phone'));

    }

    public function verifyPasswordCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required',
        ]);

        $otp = OtpCode::where('phone', $request->phone)
                  ->where('code', $request->code)
                  ->where('expires_at', '>', Carbon::now())
                  ->where('is_used', 0)
                  ->latest()
                  ->first();            

        if (!$otp) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

         $otp->update(['is_used' => 1]);

        session(['verified_phone' => $request->phone]);

        return redirect()->route('new-password-form')->with('success', 'Phone verified successfully!');
    }

    public function showNewPasswordForm()
    {
        $phone = session('verified_phone');

        if (!$phone) {
            return redirect()->route('forgot-password')->with('error', 'Session expired.');
        }

        return view('auth.reset-password', compact('phone'));
    }

    public function storeNewPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'User not found.']);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        session()->forget('verified_phone');

        return redirect()->route('login')->with('success', 'Password reset successfully. You can now login.');
    }


}
