@component('mail::message')
# New Contact Form Submission

You have received a new message through the contact form.

## Contact Details

**Name:** {{ $name }}
**Email:** {{ $email }}
**Submitted:** {{ now()->format('F j, Y g:i A') }}

## Message

@component('mail::panel')
{{ $message }}
@endcomponent

## Action Required

Please respond to this inquiry at your earliest convenience by replying directly to this email (the reply-to is set to {{ $email }}).

@component('mail::button', ['url' => 'mailto:' . $email])
Reply to {{ $name }}
@endcomponent

Best regards,
Scandere AI Notification System

@component('mail::subcopy')
This is an automated notification from the Scandere AI Store contact form. Reply-to address is set to {{ $email }} for your convenience.
@endcomponent
@endcomponent
