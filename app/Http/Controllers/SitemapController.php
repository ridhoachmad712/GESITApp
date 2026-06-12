<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Sitemap XML: beranda, arsip, semua kategori, dan seluruh
     * dokumen public+published — agar terindeks mesin pencari.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap-xml', 3600, function (): string {
            $urls = [
                ['loc' => route('home'), 'priority' => '1.0'],
                ['loc' => route('arsip.index'), 'priority' => '0.8'],
            ];

            foreach (Category::orderBy('id')->get() as $category) {
                $urls[] = [
                    'loc' => route('arsip.show', $category),
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'priority' => '0.7',
                ];
            }

            foreach (Document::published()->public()->orderBy('id')->get() as $document) {
                $urls[] = [
                    'loc' => route('documents.show', $document),
                    'lastmod' => $document->updated_at?->toAtomString(),
                    'priority' => '0.6',
                ];
            }

            $items = '';

            foreach ($urls as $url) {
                $items .= '<url><loc>'.e($url['loc']).'</loc>'
                    .(isset($url['lastmod']) ? '<lastmod>'.$url['lastmod'].'</lastmod>' : '')
                    .'<priority>'.$url['priority'].'</priority></url>';
            }

            return '<?xml version="1.0" encoding="UTF-8"?>'
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                .$items
                .'</urlset>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
