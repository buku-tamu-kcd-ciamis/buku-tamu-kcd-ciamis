@php
    $seoSiteName = config('seo.site_name', 'Buku Tamu KCD Ciamis');
    $seoTitle = trim($__env->yieldContent('seo_title', $__env->yieldContent('title', config('seo.default_title', $seoSiteName))));
    $seoDescription = trim($__env->yieldContent('seo_description', config('seo.default_description', 'Aplikasi Buku Tamu KCD Ciamis.')));
    $seoKeywordsRaw = trim($__env->yieldContent('seo_keywords', implode(', ', config('seo.default_keywords', []))));
    $seoKeywords = collect(explode(',', $seoKeywordsRaw))
        ->map(fn (string $item): string => trim($item))
        ->filter(fn (string $item): bool => $item !== '')
        ->implode(', ');
    $seoCanonical = $__env->yieldContent('seo_canonical', url()->current());
    $seoRobots = trim($__env->yieldContent('seo_robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
    $seoImagePath = trim($__env->yieldContent('seo_image', config('seo.default_image', '/img/logo-cadisdik.png')));
    $seoImageUrl = str_starts_with($seoImagePath, 'http') ? $seoImagePath : asset(ltrim($seoImagePath, '/'));
    $seoImageAlt = trim($__env->yieldContent('seo_image_alt', config('seo.default_image_alt', $seoSiteName)));
    $seoImageWidth = (int) $__env->yieldContent('seo_image_width', (string) config('seo.default_image_width', 1200));
    $seoImageHeight = (int) $__env->yieldContent('seo_image_height', (string) config('seo.default_image_height', 630));
    $seoImageType = 'image/png';

    if (str_ends_with(strtolower($seoImageUrl), '.jpg') || str_ends_with(strtolower($seoImageUrl), '.jpeg')) {
        $seoImageType = 'image/jpeg';
    } elseif (str_ends_with(strtolower($seoImageUrl), '.webp')) {
        $seoImageType = 'image/webp';
    }
@endphp

<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoCanonical }}">
<link rel="image_src" href="{{ $seoImageUrl }}">

<meta property="og:locale" content="id_ID">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $seoSiteName }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoImageUrl }}">
<meta property="og:image:secure_url" content="{{ $seoImageUrl }}">
<meta property="og:image:type" content="{{ $seoImageType }}">
<meta property="og:image:width" content="{{ $seoImageWidth }}">
<meta property="og:image:height" content="{{ $seoImageHeight }}">
<meta property="og:image:alt" content="{{ $seoImageAlt }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="{{ config('seo.twitter_site', '@etamukcd') }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImageUrl }}">
<meta name="twitter:image:alt" content="{{ $seoImageAlt }}">
<meta name="twitter:url" content="{{ $seoCanonical }}">

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Organization",
    "name": "{{ $seoSiteName }}",
    "url": "{{ url('/') }}",
    "logo": "{{ $seoImageUrl }}",
    "sameAs": [
        "{{ url('/') }}"
    ]
}
</script>

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ $seoSiteName }}",
    "url": "{{ url('/') }}",
    "inLanguage": "id-ID",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url('/') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
