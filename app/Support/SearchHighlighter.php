<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class SearchHighlighter
{
    /**
     * Sorot kata kunci pada teks (aman dari XSS: teks di-escape dulu).
     */
    public static function highlight(?string $text, string $query): HtmlString
    {
        $escaped = e((string) $text);

        $words = array_unique(array_filter(
            preg_split('/\s+/', trim($query)) ?: [],
            fn (string $word): bool => mb_strlen($word) >= 2,
        ));

        foreach ($words as $word) {
            $escaped = preg_replace(
                '/'.preg_quote(e($word), '/').'/iu',
                '<mark class="rounded bg-yellow-100 px-0.5">$0</mark>',
                $escaped,
            );
        }

        return new HtmlString($escaped);
    }
}
