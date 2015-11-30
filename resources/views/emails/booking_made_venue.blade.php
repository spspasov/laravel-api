Dear {{ $venue->account->name }},

A booking was made for {{ $booking->date }}.

Please review and either accept or reject it by clicking here:

{{ url() }}/api/auth/login/{{ $token }}