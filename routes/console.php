<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('telescope:prune')->everyThirtyMinutes();

// // تنفيذ النسخ الاحتياطي لقاعدة البيانات والملفات تلقائياً كل يوم
// Schedule::command('backup:run')->daily()->at('00:00');

// // تنظيف النسخ القديمة لضمان عدم امتلاء القرص الصلب
// Schedule::command('backup:clean')->daily()->at('01:00');


 Schedule::command('backup:run')->everyMinute();


Schedule::command('backup:clean')->everyMinute();