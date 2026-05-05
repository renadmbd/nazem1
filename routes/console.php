<?php

/*
|--------------------------------------------------------------------------
| ملف Commands الخاص بأوامر Artisan
|--------------------------------------------------------------------------
*/

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// أمر بسيط يعرض اقتباس تحفيزي عند تشغيل: php artisan inspire
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
