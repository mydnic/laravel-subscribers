@php
    $isFullDocument = str_starts_with(ltrim($contentHtml), '<!DOCTYPE') || str_starts_with(ltrim($contentHtml), '<html');
@endphp

@if($isFullDocument)
    {!! $contentHtml !!}
@else
    <x-mail::layout>
        <x-slot:header>
            <x-mail::header :url="config('app.url')">
                {{ config('app.name') }}
            </x-mail::header>
        </x-slot:header>

        {!! $contentHtml !!}

        <x-slot:footer>
            <x-mail::footer>
                @isset($subscriber)
                    You are receiving this email because you subscribed.<br>
                    <a href="{{ $subscriber->getUnsubscribeUrl() }}">Unsubscribe</a>
                @endisset
            </x-mail::footer>
        </x-slot:footer>
    </x-mail::layout>
@endif
