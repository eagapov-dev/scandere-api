@component('mail::message')
# Verify Your Email Address

Hello {{ $user->first_name }},

Thank you for registering with Scandere AI! Please verify your email address to activate your account.

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

This verification link will expire in 24 hours.

If you did not create an account, no further action is required.

Thanks,<br>
The Scandere AI Team

@slot('subcopy')
If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:
[{{ $verificationUrl }}]({{ $verificationUrl }})
@endslot
@endcomponent
