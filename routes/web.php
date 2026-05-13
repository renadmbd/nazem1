<?php
/*
|--------------------------------------------------------------------------
| ملف الروتس حق نظام NAZEM
|--------------------------------------------------------------------------
|
| هذا الملف مسؤول عن:
| - تعريف كل الروابط (URLs) اللي بالموقع.
| - يربط كل رابط بالصفحة أو الكنترولر المناسب.
| - فيه روتات الهوم، تسجيل الدخول، التسجيل، الداشبورد، الداتا، التنبيهات،
|   البروفايل، اختبار الإيميل، واستعادة كلمة المرور بالكود.
|
| باختصار: هذا هو "خريطة الطريق" للتطبيق كامل، أي صفحة أو أكشن جديد
| لازم نضيف له روت هنا.
|
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Public pages (Home)
|--------------------------------------------------------------------------
*/
// هنا الروتات المفتوحة للجميع بدون تسجيل دخول

// صفحة الهوم (Landing Page)
Route::get('/', function () {
    return view('home');
})->name('home');

// لو أحد دخل /home نحوله لنفس صفحة الهوم
Route::get('/home', function () {
    return redirect()->route('home');
});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
// كل شي له علاقة بتسجيل الدخول والتسجيل والخروج

// صفحة تسجيل الدخول (فقط للضيوف)
Route::get('/login', function () {
    return view('login');
})->name('login')->middleware('guest');

// استقبال بيانات تسجيل الدخول
Route::post('/login', function () {

    $data = request()->validate([
        'email'      => 'required|email',
        'password'   => 'required',
        'login_type' => 'required|in:user,admin',
    ]);

    if (!Auth::attempt([
        'email' => $data['email'],
        'password' => $data['password'],
    ])) {
        return back()->with('error', 'Invalid email or password.');
    }

    request()->session()->regenerate();

    $user = Auth::user();

    // إذا اختار Admin لكن حسابه مو Admin
    if ($data['login_type'] === 'admin' && $user->role !== 'admin') {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return back()->with('error', 'This account is not registered as admin.');
    }

    return redirect()->route('dashboard');
})->middleware('guest');

// تسجيل خروج
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');


// صفحة التسجيل (فقط للضيوف)
Route::get('/signup', function () {
    return view('signup');
})->name('signup')->middleware('guest');

// استقبال بيانات التسجيل
Route::post('/signup', function () {

    request()->validate([
        'name'     => 'required',
        'email'    => 'required|email|unique:users',
        'password' => 'required|confirmed|min:6',
    ]);

    User::create([
        'name'     => request('name'),
        'email'    => request('email'),
        'password' => Hash::make(request('password')),
        'role'     => 'user',
    ]);

    return redirect()->route('login')
        ->with('success', 'Account created successfully. Log in now.');
})->middleware('guest');


/*
|--------------------------------------------------------------------------
| Authenticated Pages
|--------------------------------------------------------------------------
*/
// كل الروتات اللي هنا لازم المستخدم يكون مسجل دخول

Route::middleware('auth')->group(function () {

    // لوحة التحكم الرئيسية
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // صفحة التنبيهات
    Route::get('/alerts', [AlertController::class, 'index'])
        ->name('alerts');

    // صفحة البروفايل
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});


/*
|--------------------------------------------------------------------------
| Admin Only Pages
|--------------------------------------------------------------------------
*/
// فقط الأدمن يقدر يدخل أو ينفذ هذي العمليات

Route::middleware(['auth', 'admin'])->group(function () {

    // صفحة الداتا + Preview
    Route::get('/data', [DataController::class, 'index'])
        ->name('data');

    // رفع ملف CSV → إضافة إلى جدول items
    Route::post('/data/import', [DataController::class, 'import'])
        ->name('data.import');

    // البيع السريع → تحديث الكمية + إضافة order
    Route::post('/data/quick-sell', [DataController::class, 'quickSell'])
        ->name('data.quickSell');

    // حذف كل البيانات من صفحة الداتا
    Route::post('/data/delete-all', [DataController::class, 'deleteAll'])
        ->name('data.deleteAll');
});


/*
|--------------------------------------------------------------------------
| Test Email
|--------------------------------------------------------------------------
*/
// اختبار إرسال الإيميل
Route::get('/test-email', function () {
    Mail::raw('Hello from NAZEM email test ✅', function ($message) {
        $message->to('renadmbd@gmail.com')
                ->subject('NAZEM Test Email');
    });

    return 'Test email sent (تم ارسال الايميل).';
});


/*
|--------------------------------------------------------------------------
| Forgot Password Routes
|--------------------------------------------------------------------------
*/
// Routes: Forgot Password + Verify Code (ضيوف فقط)

Route::middleware('guest')->group(function () {

    // صفحة إدخال الإيميل لنسيان كلمة المرور
    Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])
        ->name('password.request');

    // استقبال الإيميل وإرسال الكود
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->name('password.email');

    // صفحة إدخال الكود + الباسورد الجديد
    Route::get('/verify-code', [PasswordResetController::class, 'showVerifyForm'])
        ->name('password.verify.form');

    // استقبال الكود + الباسورد وتحديث كلمة المرور
    Route::post('/verify-code', [PasswordResetController::class, 'verifyAndReset'])
        ->name('password.verify');
});
