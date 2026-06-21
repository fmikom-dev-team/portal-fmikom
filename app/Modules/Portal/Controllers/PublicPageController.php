<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalAcademicCalendar;
use App\Models\Portal\PortalEvent;
use App\Models\Portal\PortalPage;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function show($slug)
    {
        $page = PortalPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $academicCalendars = [];
        $events = [];

        if ($slug === 'kalender-akademik') {
            $academicCalendars = PortalAcademicCalendar::orderBy('start_date', 'asc')->get();
        } elseif ($slug === 'agenda-event') {
            $events = PortalEvent::where('status', 'published')->orderBy('start_time', 'asc')->get();
        }

        return Inertia::render('Public/Page', [
            'page' => $page,
            'academicCalendars' => $academicCalendars,
            'events' => $events,
        ]);
    }

    public function sitemapXml()
    {
        $baseUrl = url('/');
        $urls = [];

        // 1. Static URLs
        $urls[] = ['loc' => $baseUrl, 'lastmod' => now()->toDateString(), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => $baseUrl . '/berita', 'lastmod' => now()->toDateString(), 'changefreq' => 'daily', 'priority' => '0.8'];
        $urls[] = ['loc' => $baseUrl . '/dokumen', 'lastmod' => now()->toDateString(), 'changefreq' => 'weekly', 'priority' => '0.7'];
        $urls[] = ['loc' => $baseUrl . '/privacy-policy', 'lastmod' => now()->toDateString(), 'changefreq' => 'monthly', 'priority' => '0.5'];
        $urls[] = ['loc' => $baseUrl . '/terms-of-service', 'lastmod' => now()->toDateString(), 'changefreq' => 'monthly', 'priority' => '0.5'];
        $urls[] = ['loc' => $baseUrl . '/cookie-policy', 'lastmod' => now()->toDateString(), 'changefreq' => 'monthly', 'priority' => '0.5'];

        // 2. Dynamic Pages (PortalPage)
        $pages = PortalPage::where('is_published', true)->get();
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $baseUrl . '/halaman/' . $page->slug,
                'lastmod' => ($page->updated_at ?? now())->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 3. Dynamic Posts (PortalPost)
        $posts = \App\Models\Portal\PortalPost::where('status', 'published')->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $baseUrl . '/berita/' . $post->slug,
                'lastmod' => ($post->published_at ?? $post->updated_at ?? now())->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function sitemapHtml()
    {
        $categories = PortalPage::where('is_published', true)
            ->select('id', 'title', 'slug', 'category')
            ->get()
            ->groupBy('category');

        $latestPosts = \App\Models\Portal\PortalPost::where('status', 'published')
            ->select('id', 'title', 'slug', 'created_at')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Public/Sitemap', [
            'categories' => $categories,
            'latestPosts' => $latestPosts,
        ]);
    }
}
