<x-mail::message>
# {{ $title }}

{{ $content }}

@if($link !== null)
<x-mail::button :url="$link">
Kunjungi
</x-mail::button>
@endif

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
