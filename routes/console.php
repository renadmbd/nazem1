<?php

/*
|--------------------------------------------------------------------------
| ملف Commands الخاص بأوامر Artisan
|--------------------------------------------------------------------------
|
| هذا الملف نستخدمه لتعريف أوامر مخصصة في Laravel.
| الفكرة ببساطة إنك تقدر تضيف أوامر جديدة وتشغلها من التيرمنال
| بالأمر: php artisan <اسم-الأمر>
|
| المثال الموجود هنا هو أمر جاهز من لارافيل يعطيك اقتباس تحفيزي.
| تقدر تقلّبه أو تضيف أوامر جديدة حسب احتياج مشروع NAZEM.
|
*/

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// أمر بسيط يعرض اقتباس تحفيزي عند تشغيل: php artisan inspire
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
