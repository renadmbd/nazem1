<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    // 1) عرض صفحة "نسيت كلمة المرور" (إدخال الإيميل)
    public function showRequestForm()
    {
        return view('auth.forgot');
    }

    // 2) استلام الإيميل، إنشاء كود، إرساله على Gmail
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', 'Email not found.');
        }

        // نولد كود من 6 أرقام
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // نحذف الأكواد القديمة لنفس اليوزر
        DB::table('password_otps')->where('user_id', $user->id)->delete();

        // نحفظ الكود في الجدول
        DB::table('password_otps')->insert([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // نرسل الإيميل
        Mail::to($user->email)->send(new ResetPasswordCode($code));

        // نخزن الإيميل في السيشن عشان نعبّيه تلقائي في صفحة التحقق
        return redirect()
            ->route('password.verify.form')
            ->with('email', $user->email);
    }

    // 3) عرض صفحة إدخال الكود + كلمة السر الجديدة
    public function showVerifyForm(Request $request)
    {
        // نقرأ الإيميل من السيشن لو موجود
        $email = session('email');

        return view('auth.verify-otp', compact('email'));
    }

    // 4) التحقق من الكود وتحديث كلمة المرور
    public function verifyAndReset(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'code'     => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', 'Invalid email or code.');
        }

        $row = DB::table('password_otps')
            ->where('user_id', $user->id)
            ->where('code', $request->code)
            ->first();

        if (! $row) {
            return back()->with('error', 'Invalid code.');
        }

        if (Carbon::parse($row->expires_at)->isPast()) {
            return back()->with('error', 'Code expired. Please request a new one.');
        }

        // نحدث الباسورد
        $user->password = Hash::make($request->password);
        $user->save();

        // نحذف الكود بعد الاستخدام
        DB::table('password_otps')->where('id', $row->id)->delete();

        return redirect()->route('login')
            ->with('success', 'Password updated. You can log in now.');
    }
}
