<x-mail::message>
# رمز التفعيل لحسابك

مرحباً!

لإكمال عملية التسجيل في نظام الشكاوى، يرجى إدخال رمز التفعيل لمرة واحدة (OTP) التالي:

@component('mail::panel')
<h1 style="text-align: center; color: #10B981; font-size: 32px; font-weight: bold;">{{ $otp }}</h1>
@endcomponent

**ملاحظة هامة:** هذا الرمز صالح لمدة 5 دقائق فقط، يرجى استخدامه فوراً.

<br>
شكراً لك،<br>
{{ config('app.name') }}
</x-mail::message>