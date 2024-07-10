<?php
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null) {
        return Illuminate\Support\Arr::get($array, $key, $default);
    }
}
if (!function_exists('str_slug')) {
    function str_slug(string $title, string $separator = '-', string|null $language = 'en') {
        return Illuminate\Support\Str::slug($title, $separator, $language);
    }
}