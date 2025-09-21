<x-mail::message>
# Verify Email

Hello {{ $user->name }},<br>

You are receiving this email to verify OTP.

The OTP is: {{ $otp->otp }} and it will expire in 60 minutes.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
