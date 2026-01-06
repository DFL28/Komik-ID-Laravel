<?php

use Carbon\Carbon;

if (!function_exists('resolveMedia')) {
    function resolveMedia(?string $path, string $fallback = '/images/cover-placeholder.svg'): string
    {
        if (!$path) {
            return asset($fallback);
        }
        
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        
        return asset('storage/' . $path);
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime): string
    {
        if (!$datetime) {
            return 'Tidak diketahui';
        }
        
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        return $carbon->diffForHumans();
    }
}

if (!function_exists('truncateText')) {
    function truncateText(?string $text, int $length = 150): string
    {
        if (!$text || strlen($text) <= $length) {
            return $text ?? '';
        }
        
        return substr($text, 0, $length) . '...';
    }
}

if (!function_exists('formatChapterNumber')) {
    function formatChapterNumber($number): string
    {
        if (is_numeric($number) && floor($number) == $number) {
            return (string) intval($number);
        }
        return (string) $number;
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser()
    {
        return auth()->user();
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->is_admin;
    }
}
