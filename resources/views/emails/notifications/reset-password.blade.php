@component('mail::message')
# Reset Your Password

Hi {{ $user->name }},

We received a request to reset your password for your Scandere AI Store account. Click the button below to create a new password.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

## Important Information

@component('mail::panel')
**This password reset link will expire in {{ $expiryMinutes }} minutes.**

For security reasons, please reset your password as soon as possible.
@endcomponent

## Security Notice

- If you didn't request a password reset, please ignore this email
- Your password will remain unchanged if you don't click the reset link
- Never share your password reset link with anyone
- Scandere AI will never ask you for your password via email

## Having Trouble?

If the button above doesn't work, copy and paste the following URL into your browser:

{{ $resetUrl }}

If you continue to have issues or didn't request this password reset, please contact our support team at {{ config('services.admin.email') }}.

Best regards,
The Scandere AI Team

@component('mail::subcopy')
This is an automated security email. If you didn't request a password reset, you can safely ignore this message. Your account is secure.
@endcomponent
@endcomponent
