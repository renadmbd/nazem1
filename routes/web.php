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
use App\Models\User;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Public pages (Home)
|--------------------------------------------------------------------------
*/
// هنا الروتات المفتوحة للجميع بدون تسجيل دخول

// صفحة الهوم ( Landing Page )
// أول صفحة يشوفها المستخدم قبل لا يسجل دخول
Route::get('/', function () {
    return view('home'); // يعرض فيو home.blade.php
})->name('home');

// لو أحد دخل /home نحوله لنفس صفحة الهوم
// بس عشان لو أحد تعود على /home ما تطلع له 404
Route::get('/home', function () {
    return redirect()->route('home'); // إعادة توجيه لـ route('home')
});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
// كل شي له علاقة بتسجيل الدخول والتسجيل والخروج

// صفحة تسجيل الدخول (فقط للضيوف)
// guest middleware يمنع المستخدم المسجل من الدخول لصفحة اللوق إن
Route::get('/login', function () {
    return view('login'); // يعرض فورم تسجيل الدخول
})->name('login')->middleware('guest');

// استقبال بيانات تسجيل الدخول
// هنا يتحقق من الإيميل والباسورد ويحاول يسوي تسجيل دخول
Route::post('/login', function () {

    // فاليديشن بسيط على الحقول
    $credentials = request()->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    // Auth::attempt يحاول يطابق الإيميل والباسورد مع users
    if (Auth::attempt($credentials)) {
        // regenerate عشان يحمي السيشن من هجمات سرقة السيشن
        request()->session()->regenerate();
        // لو نجحت يحوله للداشبورد
        return redirect()->route('dashboard');
    }

    // لو فشل يرجعه لنفس الصفحة مع رسالة خطأ
    return back()->with('error', 'Invalid email or password.');
});

// تسجيل خروج
// هذا الروت يستدعى لما يضغط المستخدم على زر "تسجيل خروج"
Route::post('/logout', function () {
    Auth::logout();                        // يفصل المستخدم
    request()->session()->invalidate();    // يلغي السيشن الحالية
    request()->session()->regenerateToken(); // ينشئ CSRF توكن جديد
    return redirect()->route('home');      // يرجعه للصفحة الرئيسية
})->middleware('auth')->name('logout');


// صفحة التسجيل (فقط للضيوف)
// تخلي المستخدم يسوي حساب جديد بالنظام
Route::get('/signup', function () {
    return view('signup'); // فورم إنشاء حساب
})->name('signup')->middleware('guest');

// استقبال بيانات التسجيل
// هنا ينشئ مستخدم جديد في جدول users
Route::post('/signup', function () {

    // يتحقق من الاسم والإيميل والباسورد
    request()->validate([
        'name'     => 'required',
        'email'    => 'required|email|unique:users', // يتأكد الإيميل مو مكرر
        'password' => 'required|confirmed|min:6',    // لازم يكون فيه حقل password_confirmation
    ]);

    // إنشاء المستخدم في قاعدة البيانات
    User::create([
        'name'     => request('name'),
        'email'    => request('email'),
        'password' => Hash::make(request('password')), // تشفير الباسورد
    ]);

    // بعد التسجيل يحوله لصفحة اللوق إن مع رسالة نجاح
    return redirect()->route('login')
        ->with('success', 'Account created successfully. Log in now.');
});


/*
|--------------------------------------------------------------------------
| Authenticated Pages (Dashboard / Data / Alerts / Profile)
|--------------------------------------------------------------------------
*/
// كل الروتات اللي تحت هذه لازم المستخدم يكون مسجل دخول عشان يدخلها

Route::middleware('auth')->group(function () {

    // لوحة التحكم الرئيسية
    // هذه الشاشة الرئيسية بعد تسجيل الدخول، فيها الكروت والملخصات
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // صفحة الداتا + Preview
    // تعرض جدول الأصناف (items) + معاينة البيانات
    Route::get('/data', [DataController::class, 'index'])
        ->name('data');

    // رفع ملف CSV → إضافة إلى جدول items
    // هنا نرفع ملف الإكسل/CSV وندخله بالداتابيس
    Route::post('/data/import', [DataController::class, 'import'])
        ->name('data.import');

    // البيع السريع → تحديث الكمية + إضافة order
    // هذا الروت حق خاصية Quick Sell في المشروع:
    // - يقلل الكمية من item
    // - ينشئ Order جديدة للتوثيق
    Route::post('/data/quick-sell', [DataController::class, 'quickSell'])
        ->name('data.quickSell');

    // صفحة التنبيهات
    // فيها تنبيهات الكميات المنخفضة + تواريخ الانتهاء (expiry) حسب منطق المشروع
    Route::get('/alerts', [AlertController::class, 'index'])
        ->name('alerts');

    // صفحة البروفايل (تستعرض بيانات المستخدم الحالي)
    // هنا ممكن نطورها لاحقاً لتعديل الاسم/الإيميل/الباسورد
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});


// حق تسجيل دخول الايميل بايميل حقيقي 
// هذا روت داخلي نستخدمه عشان نختبر إعدادات الإيميل من .env
// لما نزوره يرسل رسالة تجريبية للإيميل المحدد
Route::get('/test-email', function () {
    Mail::raw('Hello from NAZEM email test ✅', function ($message) {
        $message->to('mlm.projectteam@gmail.com')   // تقدرين تغيّرينه لأي إيميل تبغين تختبرينه
                ->subject('NAZEM Test Email');      // عنوان الإيميل
    });

    // يرجع نص بسيط في المتصفح يقول إن الإيميل انرسل
    return 'Test email sent (شيّكي بريدك الآن).';
});

// Routes: Forgot Password + Verify Code (ضيوف فقط)
// هذه المجموعة مسؤولة عن استعادة كلمة المرور عن طريق كود
Route::middleware('guest')->group(function () {

    // صفحة إدخال الإيميل لنسيان كلمة المرور
    // المستخدم يحط إيميله عشان يوصله الكود
    Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])
        ->name('password.request');

    // استقبال الإيميل وإرسال الكود
    // هنا الكنترولر:
    // - يتأكد الإيميل موجود
    // - ينشئ كود
    // - يرسله للمستخدم (بالإيميل حسب الإعدادات)
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->name('password.email');

    // صفحة إدخال الكود + الباسورد الجديد
    // فيها فورم فيه:
    // - الإيميل
    // - الكود
    // - كلمة المرور الجديدة + تأكيدها
    Route::get('/verify-code', [PasswordResetController::class, 'showVerifyForm'])
        ->name('password.verify.form');

    // استقبال الكود + الباسورد وتحديث كلمة المرور
    // الكنترولر:
    // - يتحقق من الكود
    // - لو صحيح يغير الباسورد للمستخدم
    // - بعدها يقدر يسجل دخول بالكلمة الجديدة
    Route::post('/verify-code', [PasswordResetController::class, 'verifyAndReset'])
        ->name('password.verify');
});
