<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Primary SEO Meta Tags -->
    <title>One Raymond TailorPro | Tailor & Apparel Management ERP Software</title>
    <meta name="title" content="One Raymond TailorPro | Tailor & Apparel Management ERP Software">
    <meta name="description" content="One Raymond TailorPro is an advanced tailor and apparel order management ERP software featuring military airman uniform tracking, custom officer measurements, staff piece-rate payroll, customer history, and delivery invoicing.">
    <meta name="keywords" content="tailor management software, tailoring ERP, apparel order tracking, military uniform tailoring, airman clothing records, tailor measurements app, tailor payroll sheet, One Raymond Tailors">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="One Raymond">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Internationalization & Hreflang -->
    <link rel="alternate" hreflang="bn" href="{{ url()->current() }}?lang=bn">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="One Raymond TailorPro">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="One Raymond TailorPro | Tailor & Apparel Management ERP Software">
    <meta property="og:description" content="Complete Tailor Management & Order Tracking ERP. Specialize in military uniform records, officer custom measurements, worker payroll sheets, and thermal delivery invoices.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="One Raymond TailorPro Logo">
    <meta property="og:locale" content="bn_BD">
    <meta property="og:locale:alternate" content="en_US">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="One Raymond TailorPro | Tailor & Apparel Management ERP Software">
    <meta name="twitter:description" content="Complete Tailor Management & Order Tracking ERP. Specialize in military uniform records, officer custom measurements, worker payroll sheets, and thermal delivery invoices.">
    <meta name="twitter:image" content="{{ asset('logo.png') }}">

    <!-- Mobile PWA & App Capabilities -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="One Raymond">
    <meta name="application-name" content="One Raymond">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="msapplication-TileColor" content="#0f172a">

    <!-- Apple Touch Icons for iOS Home Screen -->
    <link rel="apple-touch-icon" href="/pwa-icon-192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/pwa-icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/pwa-icon-192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/pwa-icon-192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/pwa-icon-512.png">

    <!-- Fonts Preconnect for Core Web Vitals -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Schema.org JSON-LD Structured Data (SoftwareApplication, Organization, WebSite) -->
    @php
    $schemaJson = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'SoftwareApplication',
          '@id' => url('/') . '#software',
          'name' => 'One Raymond TailorPro',
          'applicationCategory' => 'BusinessApplication',
          'operatingSystem' => 'Web, Android, iOS, Windows, macOS',
          'description' => 'Enterprise tailor and apparel management ERP software designed for military airman uniform records, custom officer tailoring measurements, staff piece-rate monthly payroll, customer history, and delivery invoicing.',
          'url' => url('/'),
          'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'BDT',
            'availability' => 'https://schema.org/InStock',
          ],
          'author' => [
            '@id' => url('/') . '#organization',
          ],
          'featureList' => [
            'Military Airman Uniform Catalog & Slip Management',
            'Officer Custom Shirt & Pant Measurement System',
            'Staff Monthly Piece-Rate Work Sheets & Financial Balance Ledger',
            'Thermal Slip Printing & PDF Invoice Generation',
            'Customer Historical Measurement Lookup',
            'Offline PWA & Mobile Ergonomic Drawer Navigation',
          ],
        ],
        [
          '@type' => 'Organization',
          '@id' => url('/') . '#organization',
          'name' => 'One Raymond Tailors',
          'url' => url('/'),
          'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('logo.png'),
            'width' => 512,
            'height' => 512,
          ],
          'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+8801819349885',
            'contactType' => 'customer service',
            'availableLanguage' => ['bn', 'en'],
          ],
        ],
        [
          '@type' => 'WebSite',
          '@id' => url('/') . '#website',
          'url' => url('/'),
          'name' => 'One Raymond TailorPro',
          'description' => 'Tailor & Apparel Management ERP Software',
          'publisher' => [
            '@id' => url('/') . '#organization',
          ],
          'inLanguage' => ['bn', 'en'],
        ],
      ],
    ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-sans antialiased selection:bg-primary/20">
    <div id="app">
        <!-- Semantic Crawlable HTML Shell (Ensures bots see rich content before JS mounts) -->
        <div style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #0f172a; color: #f8fafc; font-family: 'Poppins', sans-serif; padding: 24px; text-align: center;">
            <header style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #2563eb, #1d4ed8); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/></svg>
                </div>
                <h1 style="font-size: 24px; font-weight: 700; letter-spacing: -0.5px; margin: 0;">One Raymond Tailor<span style="color: #3b82f6;">Pro</span></h1>
            </header>
            
            <main style="max-width: 600px; margin: 0 auto;">
                <p style="font-size: 14px; color: #94a3b8; line-height: 1.6; margin-bottom: 24px;">
                    Professional Tailoring & Apparel ERP Software. Specialized in Military Airman Uniforms, Officer Tailoring Measurements, Staff Piece-Rate Payroll, and Thermal Delivery Invoices.
                </p>
                <div style="width: 38px; height: 38px; border: 3px solid rgba(59, 130, 246, 0.2); border-top-color: #3b82f6; border-radius: 50%; animation: tp-spin 0.8s linear infinite; margin: 0 auto 16px auto;"></div>
                <p style="font-size: 12px; color: #64748b; font-weight: 500;">Loading application...</p>
            </main>

            <noscript>
                <div style="margin-top: 20px; padding: 16px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; max-width: 500px; font-size: 13px;">
                    JavaScript is required to access the interactive dashboard features of One Raymond TailorPro. Please enable JavaScript in your browser to proceed.
                </div>
            </noscript>

            <style>
                @keyframes tp-spin { to { transform: rotate(360deg); } }
            </style>
        </div>
    </div>
</body>
</html>
