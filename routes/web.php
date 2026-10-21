<?php

use Illuminate\Support\Facades\Route;

// XML Sitemap Route
Route::get('/sitemap.xml', function () {
    $baseUrl = url('/');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
    
    // Home / Portal root
    $xml .= '<url>';
    $xml .= '<loc>' . htmlspecialchars($baseUrl . '/') . '</loc>';
    $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
    $xml .= '<changefreq>daily</changefreq>';
    $xml .= '<priority>1.0</priority>';
    $xml .= '<xhtml:link rel="alternate" hreflang="bn" href="' . htmlspecialchars($baseUrl . '/?lang=bn') . '"/>';
    $xml .= '<xhtml:link rel="alternate" hreflang="en" href="' . htmlspecialchars($baseUrl . '/?lang=en') . '"/>';
    $xml .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($baseUrl . '/') . '"/>';
    $xml .= '</url>';

    // Login Portal
    $xml .= '<url>';
    $xml .= '<loc>' . htmlspecialchars($baseUrl . '/login') . '</loc>';
    $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
    $xml .= '<changefreq>weekly</changefreq>';
    $xml .= '<priority>0.9</priority>';
    $xml .= '<xhtml:link rel="alternate" hreflang="bn" href="' . htmlspecialchars($baseUrl . '/login?lang=bn') . '"/>';
    $xml .= '<xhtml:link rel="alternate" hreflang="en" href="' . htmlspecialchars($baseUrl . '/login?lang=en') . '"/>';
    $xml .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($baseUrl . '/login') . '"/>';
    $xml .= '</url>';

    // Password Reset
    $xml .= '<url>';
    $xml .= '<loc>' . htmlspecialchars($baseUrl . '/reset-password') . '</loc>';
    $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
    $xml .= '<changefreq>monthly</changefreq>';
    $xml .= '<priority>0.3</priority>';
    $xml .= '</url>';

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});

// Single Page Application Fallback
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
