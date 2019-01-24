# Manage Internal Newsletter Subscribers With Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mydnic/laravel-subscribers.svg)](https://packagist.org/packages/mydnic/laravel-subscribers)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://img.shields.io/travis/com/mydnic/laravel-subscribers.svg)](https://travis-ci.com/mydnic/laravel-subscribers)
[![Code Quality](https://img.shields.io/scrutinizer/g/mydnic/laravel-subscribers.svg)](https://scrutinizer-ci.com/g/mydnic/laravel-subscribers/)

## Usage

In your view, you simply need to add a form that you can customize the way you want

```blade
<form action="{{ route('subscribers.store') }}" method="post">
    @csrf
    <input type="email" name="email">
    <input type="submit" value="submit">
</form>
@if (session('subscribed'))
    <div class="alert alert-success">
        {{ session('subscribed') }}
    </div>
@endif
```
