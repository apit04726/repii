<?php
require_once __DIR__ . '/config.php';

// Security & AdSense Compliance Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <script>
        (function() {
            try {
                localStorage.removeItem('instago_theme');
                localStorage.removeItem('instago_theme_v2');
                var theme = localStorage.getItem('instago_theme_user_choice') || 'dark';
                document.documentElement.setAttribute('data-theme', theme);
            } catch(e) {}
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a0e17" id="metaThemeColor">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="InstaGo">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Instagram Reels Video Download (1080p Full HD) - InstaGo Reels Downloader</title>
    <meta name="description" content="InstaGo is the ultimate free Instagram Reels Downloader. Download Instagram Reels video in 1080p Full HD MP4 without watermark fast and easily. No login needed, 100% anonymous.">
    <meta name="keywords" content="instagram reels download, reels downloader, download instagram reels, instagram reels video download, instagram reel saver, save reels without watermark, free instagram reels download, instagram video download 1080p, instagram dp download, instagram story download">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/') ?>">
    
    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png?v=2">
    <link rel="shortcut icon" href="favicon.ico?v=2">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars(SITE_URL . '/') ?>">
    <meta property="og:title" content="Instagram Reels Video Download - InstaGo Reels Downloader">
    <meta property="og:description" content="Download Instagram Reels, HD Profile Pictures (DP), Stories, and Photos online for free in 1080p Full HD without watermark. Works on iPhone, Android, and PC.">
    <meta property="og:site_name" content="InstaGo">
    <meta property="og:image" content="<?= htmlspecialchars(SITE_URL . '/assets/img/og-banner.png') ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars(SITE_URL . '/assets/img/og-banner.png') ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="InstaGo - Instagram Reels & Video Downloader in 1080p Full HD">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Instagram Reels Video Download - InstaGo Reels Downloader">
    <meta name="twitter:description" content="Download Instagram Reels in 1080p Full HD without watermark fast, free, and anonymously.">
    <meta name="twitter:image" content="<?= htmlspecialchars(SITE_URL . '/assets/img/og-banner.png') ?>">
    <meta name="twitter:image:alt" content="InstaGo - Instagram Reels & Video Downloader in 1080p Full HD">

    <!-- Structured Data (Schema.org JSON-LD for Google Rich Snippets) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "InstaGo Reels Downloader",
      "image": "<?= htmlspecialchars(SITE_URL . '/assets/img/og-banner.png') ?>",
      "url": "<?= htmlspecialchars(SITE_URL . '/') ?>",
      "applicationCategory": "MultimediaApplication",
      "operatingSystem": "All (iOS, Android, Windows, macOS, Linux)",
      "description": "Free online Instagram Reels Downloader to save Instagram Reels videos, Stories, DP, and Photos in 1080p Full HD without watermark.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "bestRating": "5",
        "worstRating": "1",
        "ratingCount": "19420"
      }
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "HowTo",
      "name": "How to Download Instagram Reels Videos",
      "description": "Step by step guide to save Instagram Reels video to your phone gallery or PC in 1080p Full HD.",
      "step": [
        {
          "@type": "HowToStep",
          "name": "Copy & Paste Instagram Reel Link",
          "text": "Open Instagram, copy the Reel link and paste it into InstaGo search box.",
          "image": "<?= htmlspecialchars(SITE_URL . '/assets/img/guide-step-1.png') ?>"
        },
        {
          "@type": "HowToStep",
          "name": "Auto-Fetch & Preview",
          "text": "InstaGo auto-detects the video and displays an instant high-resolution preview.",
          "image": "<?= htmlspecialchars(SITE_URL . '/assets/img/guide-step-2.png') ?>"
        },
        {
          "@type": "HowToStep",
          "name": "Download in 1080p HD",
          "text": "Click 'Download MP4' to save the original high-definition video directly to your gallery without a watermark.",
          "image": "<?= htmlspecialchars(SITE_URL . '/assets/img/guide-step-3.png') ?>"
        }
      ]
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Is InstaGo free to use?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes, InstaGo is 100% free with unlimited downloads. You do not need to purchase any subscription or install third-party apps."
          }
        },
        {
          "@type": "Question",
          "name": "How can I download Instagram Reels on my iPhone?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Open Safari on your iPhone, paste the copied Reel URL into InstaGo, and click 'Download MP4'. Safari will prompt you with a download dialog, and the video will be saved directly into your Files or Photos app."
          }
        },
        {
          "@type": "Question",
          "name": "Do downloaded Instagram Reels contain watermarks?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "No, all videos and photos are downloaded in their raw native quality from official CDN nodes without adding any watermark, stamp, or logo."
          }
        },
        {
          "@type": "Question",
          "name": "Is it legal to download Instagram Reels videos?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Downloading Instagram Reels for personal offline viewing or status is legal. For commercial or public use, you should obtain permission from the original copyright owner."
          }
        }
      ]
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet (Auto Cache Busting) -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body>
    <div class="bg-glow-top"></div>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <nav class="nav-wrap">
                <a href="./" class="brand">
                    <div class="brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </div>
                    <span>InstaGo</span>
                </a>

                <div class="nav-actions">
                    <div class="status-badge-chip">
                        <span class="status-pulse-dot"></span>
                        <span id="statusBadgeText">Meta CDN Active</span>
                    </div>

                    <!-- Header Language Switcher Dropdown -->
                    <div class="lang-dropdown-wrapper" id="headerLangDropdown">
                        <button type="button" class="lang-current-btn" id="headerLangToggle" aria-expanded="false" aria-label="Select Language">
                            <svg class="globe-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                            <span class="current-lang-code" id="currentLangLabel">EN</span>
                            <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="lang-menu" id="headerLangMenu">
                            <button type="button" class="lang-option-btn active" data-lang="en">
                                <span class="lang-name">English</span>
                                <span class="lang-badge">EN</span>
                            </button>
                            <button type="button" class="lang-option-btn" data-lang="gu">
                                <span class="lang-name">ગુજરાતી</span>
                                <span class="lang-badge">GU</span>
                            </button>
                            <button type="button" class="lang-option-btn" data-lang="hi">
                                <span class="lang-name">हिन्दी</span>
                                <span class="lang-badge">HI</span>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle Dark/Light Mode">
                        <!-- Sun Icon (for dark mode) -->
                        <svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                        <!-- Moon Icon (for light mode) -->
                        <svg id="themeIconMoon" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="badge-pill">
                <span class="badge-dot"></span>
                <span id="heroBadgeText">Fast & Free 1080p Instagram Downloader</span>
            </div>

            <h1 class="hero-title" id="heroTitle">
                Download Instagram <br>
                <span class="gradient-text">Reels, DP & Stories</span>
            </h1>

            <p class="hero-desc" id="heroDesc">
                Save HD Reels, Full Resolution Profile Pictures (DP), Stories, and Carousel Photos directly to your Gallery or PC.
            </p>

            <!-- Main Application Console Card -->
            <div class="app-console-card">
                <!-- Navigation Tabs -->
                <div class="tabs-container">
                    <button type="button" class="tab-btn active" data-type="all">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                        <span id="tabLabelAll">All-in-One</span>
                    </button>
                    <button type="button" class="tab-btn" data-type="reels">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                        <span id="tabLabelReels">Reels</span>
                    </button>
                    <button type="button" class="tab-btn" data-type="dp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span id="tabLabelDP">Profile DP</span>
                    </button>
                    <button type="button" class="tab-btn" data-type="stories">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <span id="tabLabelStories">Stories</span>
                    </button>
                    <button type="button" class="tab-btn" data-type="photos">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <span id="tabLabelPhotos">Photos</span>
                    </button>
                </div>

                <!-- Search Form -->
                <div class="search-card">
                    <form id="searchForm" autocomplete="off">
                        <div class="search-box-wrapper">
                            <div class="search-input-area">
                                <div class="search-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                    </svg>
                                </div>

                                <input type="text" 
                                       id="urlInput" 
                                       class="search-input" 
                                       placeholder="Paste Instagram Reel, DP, Story, or Post URL..." 
                                       required>

                                <button type="button" id="clearBtn" class="action-btn clear-btn" title="Clear input">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>

                            <div class="input-actions">
                                <button type="button" id="pasteBtn" class="action-btn paste-btn" title="Paste from Clipboard">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                                    <span id="pasteBtnText">Paste</span>
                                </button>

                                <button type="submit" id="submitBtn" class="search-submit-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    <span id="submitBtnText">Search</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Auto-detection indicator -->
                    <div id="detectionIndicator" class="detection-indicator">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span id="detectedType">Instagram Reel Detected</span>
                    </div>
                </div>

                <!-- Micro Perks Highlights -->
                <div class="app-micro-perks">
                    <span class="perk-chip"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="perk1">1080p Ultra HD</span></span>
                    <span class="perk-chip"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="perk2">Zero Watermark</span></span>
                    <span class="perk-chip"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="perk3">100% Anonymous</span></span>
                    <span class="perk-chip"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="perk4">Unlimited Free</span></span>
                </div>
            </div>

            <!-- Error Alert -->
            <div id="errorAlert" class="alert-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span id="errorMessage">Something went wrong. Please check your URL.</span>
            </div>

            <!-- Loader Card -->
            <div id="loaderCard" class="loader-card">
                <div class="spinner"></div>
                <h4 class="loader-title" id="loaderTitle">Processing Media</h4>
                <p id="loaderStatus" class="loader-subtitle">Connecting to Instagram servers...</p>
            </div>

            <!-- Dynamic Result Card -->
            <div id="resultCard" class="result-card">
                <div class="result-header">
                    <span id="resultTypeBadge" class="result-type-badge">REEL HD</span>
                    <div class="result-author">
                        <img id="resultAuthorAvatar" class="result-author-avatar" src="" alt="Avatar" referrerpolicy="no-referrer">
                        <span id="resultAuthorName">@instagram</span>
                    </div>
                </div>

                <div class="result-body">
                    <div id="mediaPreviewContainer" class="media-preview-container">
                        <!-- Dynamic <video> or <img> rendered via JS -->
                    </div>

                    <div class="result-details">
                        <p id="resultCaption" class="result-caption">Instagram Media Caption</p>
                        
                        <div id="downloadOptions" class="download-options">
                            <!-- Download Action buttons rendered via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Top AdSense Banner Ad Slot -->
        <div class="ad-slot-wrapper ad-slot-top" aria-label="Advertisement">
            <span class="ad-badge-label">ADVERTISEMENT</span>
            <div class="ad-placeholder-box">
                <!-- Replace ca-pub-XXXXXXXXXXXXXXXX and data-ad-slot with your real Google AdSense details -->
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
                     data-ad-slot="1234567890"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <div class="ad-fallback-chip">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Google AdSense Slot — Top Responsive Banner (ca-pub-XXXXXXXXXXXXXXXX)</span>
                </div>
            </div>
        </div>

        <!-- Interactive Smart How-to-Download Guide Section -->
        <section class="section-steps" id="guideSection">
            <div class="section-heading">
                <div class="guide-badge-pill">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                    <span>Smart Guide</span>
                </div>
                <h2 class="section-title" id="guideHeading">How to Download from <span class="gradient-text">Instagram</span></h2>
                <p class="section-subtitle" id="guideSubheading">Save any Reel, Profile DP, Story or Photo in 3 easy steps</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-card-top">
                        <span class="step-num">01</span>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        </div>
                    </div>
                    <div class="step-img-frame" data-step-img="assets/img/guide-step-1.png" data-step-title="Step 1: Paste Instagram Link in InstaGo">
                        <div class="mockup-bar">
                            <div class="mock-dots"><span class="mock-dot d-red"></span><span class="mock-dot d-yellow"></span><span class="mock-dot d-green"></span></div>
                            <span class="mockup-url">instago.app/reels</span>
                            <span class="mockup-zoom" title="Click to zoom"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></span>
                        </div>
                        <div class="step-img-wrap">
                            <img src="assets/img/guide-step-1.png" alt="Step 1: Copy Instagram Reel Link and Paste in InstaGo" class="step-guide-img" loading="lazy" decoding="async">
                        </div>
                    </div>
                    <h3 id="stepTitle1">Copy & Paste Link</h3>
                    <p id="stepDesc1">Copy any Instagram Reel link and tap <strong>"Paste"</strong> in InstaGo. Our smart engine auto-detects the link immediately.</p>
                </div>

                <div class="step-card">
                    <div class="step-card-top">
                        <span class="step-num">02</span>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                        </div>
                    </div>
                    <div class="step-img-frame" data-step-img="assets/img/guide-step-2.png" data-step-title="Step 2: Instant HD Preview & Download Options">
                        <div class="mockup-bar">
                            <div class="mock-dots"><span class="mock-dot d-red"></span><span class="mock-dot d-yellow"></span><span class="mock-dot d-green"></span></div>
                            <span class="mockup-url">1080p Stream Ready</span>
                            <span class="mockup-zoom" title="Click to zoom"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></span>
                        </div>
                        <div class="step-img-wrap">
                            <img src="assets/img/guide-step-2.png" alt="Step 2: Auto-Fetch Media Details and Preview" class="step-guide-img" loading="lazy" decoding="async">
                        </div>
                    </div>
                    <h3 id="stepTitle2">Auto-Fetch & Preview</h3>
                    <p id="stepDesc2">InstaGo instantly connects to Instagram CDN to generate high-resolution video preview with complete creator details.</p>
                </div>

                <div class="step-card">
                    <div class="step-card-top">
                        <span class="step-num">03</span>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </div>
                    </div>
                    <div class="step-img-frame step-frame-dark" data-step-img="assets/img/guide-step-3.png" data-step-title="Step 3: Save in 1080p Full HD">
                        <div class="mockup-bar">
                            <div class="mock-dots"><span class="mock-dot d-red"></span><span class="mock-dot d-yellow"></span><span class="mock-dot d-green"></span></div>
                            <span class="mockup-url">1080p Video Player</span>
                            <span class="mockup-zoom" title="Click to zoom"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></span>
                        </div>
                        <div class="step-img-wrap">
                            <img src="assets/img/guide-step-3.png" alt="Step 3: Download and Watch Watermark-Free Video" class="step-guide-img" loading="lazy" decoding="async">
                        </div>
                    </div>
                    <h3 id="stepTitle3">Save in 1080p Full HD</h3>
                    <p id="stepDesc3">Tap <strong>"Download Video (MP4 HD)"</strong> to save the crystal-clear original video without any watermark directly to your device.</p>
                </div>
            </div>

            <!-- Smart Quick Content Guides -->
            <div class="guide-quick-tips">
                <div class="tip-card">
                    <span class="tip-icon">🎬</span>
                    <div class="tip-content">
                        <strong id="tipTitleReels">Reels & Videos</strong>
                        <span id="tipDescReels">Works for public Reels, audio tracks, and video clips in 1080p original clarity.</span>
                    </div>
                </div>
                <div class="tip-card">
                    <span class="tip-icon">👤</span>
                    <div class="tip-content">
                        <strong id="tipTitleDP">Full HD Profile DP</strong>
                        <span id="tipDescDP">Simply enter any username (e.g. @cristiano) to view & zoom original full-size avatar.</span>
                    </div>
                </div>
                <div class="tip-card">
                    <span class="tip-icon">⏱️</span>
                    <div class="tip-content">
                        <strong id="tipTitleStories">Stories & Highlights</strong>
                        <span id="tipDescStories">Paste profile link or story URL to download active 24h stories and highlight reels.</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ways to Download Instagram Reels Video by Device (Matching indown.io) -->
        <section class="section-device-guide" id="deviceGuide">
            <div class="section-heading">
                <h2 class="section-title" id="waysHeading">Ways to Download <span class="gradient-text">Instagram Reels Video</span></h2>
                <p class="section-subtitle" id="waysSubheading">Step-by-step instructions tailored for your smartphone or desktop</p>
                
                <!-- OS Selector Tabs -->
                <div class="device-os-tabs" role="tablist">
                    <button type="button" class="os-tab-btn active" data-os="ios">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.85c.66-.82 1.11-1.96.99-3.1-.97.04-2.14.65-2.83 1.46-.61.7-.1.14 1.83 1.05 2.99 1.08-.03 2.13-.65 2.79-1.35z"/></svg>
                        <span id="tabIos">iPhone & iPad (iOS)</span>
                    </button>
                    <button type="button" class="os-tab-btn" data-os="android">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.4116 13.8533 8.082 12 8.082s-3.5902.3296-5.1368.8677L4.8409 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396"/></svg>
                        <span id="tabAndroid">Android</span>
                    </button>
                    <button type="button" class="os-tab-btn" data-os="pc">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        <span id="tabPC">Windows PC & Mac</span>
                    </button>
                </div>
            </div>

            <!-- Device Panels -->
            <div class="device-panel active" id="panel-ios">
                <div class="device-guide-card">
                    <div class="device-badge">iOS Safari Flow</div>
                    <h3 id="iosGuideTitle">How to Download Instagram Reels on iPhone (iOS)</h3>
                    <ol class="device-step-list">
                        <li id="iosStep1">Open the <strong>Instagram app</strong> on your iPhone or iPad and navigate to your desired Reel video.</li>
                        <li id="iosStep2">Tap the paper airplane <strong>Share icon</strong> at the bottom right, then tap <strong>"Copy Link"</strong>.</li>
                        <li id="iosStep3">Open <strong>Safari</strong> browser, visit <strong>InstaGo</strong>, and tap the pink <strong>"Paste"</strong> button.</li>
                        <li id="iosStep4">Click <strong>"Download MP4"</strong>. Safari will display a native download prompt; click <strong>"Download"</strong>.</li>
                        <li id="iosStep5">Tap the blue Safari download circle, open the video, and tap <strong>Share &gt; Save Video</strong> to move it into your <strong>Photos App</strong>.</li>
                    </ol>
                </div>
            </div>

            <div class="device-panel" id="panel-android">
                <div class="device-guide-card">
                    <div class="device-badge">Android Chrome Flow</div>
                    <h3 id="androidGuideTitle">How to Download Instagram Reels on Android</h3>
                    <ol class="device-step-list">
                        <li id="androidStep1">Launch Instagram on your Android device (Samsung, OnePlus, Xiaomi, Pixel, etc.).</li>
                        <li id="androidStep2">Go to the Reel video, tap the <strong>3 dots or Share icon</strong>, and choose <strong>"Copy link"</strong>.</li>
                        <li id="androidStep3">Open Google Chrome, navigate to <strong>InstaGo</strong>, and tap <strong>"Paste"</strong>.</li>
                        <li id="androidStep4">Tap <strong>"Download MP4 (1080p HD)"</strong>. Chrome will automatically download the file into your <strong>Downloads</strong> folder.</li>
                        <li id="androidStep5">The video immediately appears in your <strong>Google Photos</strong> or phone's <strong>Gallery</strong> app.</li>
                    </ol>
                </div>
            </div>

            <div class="device-panel" id="panel-pc">
                <div class="device-guide-card">
                    <div class="device-badge">Desktop PC & Mac Flow</div>
                    <h3 id="pcGuideTitle">How to Download Instagram Reels on PC & Mac</h3>
                    <ol class="device-step-list">
                        <li id="pcStep1">Open <strong>instagram.com</strong> in your Chrome, Edge, Safari, or Firefox browser on your desktop or laptop.</li>
                        <li id="pcStep2">Click on the Reel you want to download and copy the URL from the browser address bar or click the three dots icon to copy link.</li>
                        <li id="pcStep3">Paste the link into the search box on <strong>InstaGo</strong> and click <strong>Search</strong>.</li>
                        <li id="pcStep4">Click <strong>"Download MP4"</strong> to save the high-speed uncompressed video straight to your PC's <strong>Downloads</strong> directory.</li>
                    </ol>
                </div>
            </div>
        </section>

        <!-- Mid AdSense In-Content Banner Ad Slot -->
        <div class="ad-slot-wrapper ad-slot-mid" aria-label="Advertisement">
            <span class="ad-badge-label">ADVERTISEMENT</span>
            <div class="ad-placeholder-box">
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
                     data-ad-slot="2345678901"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <div class="ad-fallback-chip">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Google AdSense Slot — In-Content Responsive Banner (ca-pub-XXXXXXXXXXXXXXXX)</span>
                </div>
            </div>
        </div>

        <!-- In-Depth Real SEO Content Article (Matching indown.io/reels) -->
        <section class="section-seo-article">
            <div class="article-grid">
                <div class="article-card">
                    <div class="article-icon">✨</div>
                    <h2 class="article-title" id="art1Title">Download Instagram Reels Video without Watermark</h2>
                    <p class="article-text" id="art1P1">
                        InstaGo Reels Downloader is engineered to deliver <strong>100% original, watermark-free video streams</strong>. Unlike screen-recorders or basic tools that compress videos or stamp third-party branding over your favourite clips, InstaGo connects directly to high-speed Instagram CDN nodes to fetch the raw, native MP4 video file.
                    </p>
                    <p class="article-text" id="art1P2">
                        Whether you are an aspiring content creator archiving your portfolio, a video editor gathering inspiration, or simply saving funny Reels to share with family on WhatsApp, InstaGo ensures your video preserves its original 1080p Full HD resolution, rich dynamic colors, and pristine 320kbps stereo sound.
                    </p>
                </div>

                <div class="article-card">
                    <div class="article-icon">💡</div>
                    <h2 class="article-title" id="art2Title">What is Instagram Reels & Why Save Them?</h2>
                    <p class="article-text" id="art2P1">
                        Instagram Reels is Meta's dynamic short-form video platform featuring 15 to 90-second creative multi-clip videos with trending audio tracks, AR augmented-reality filters, speed adjustments, and rich visual effects. Millions of viral tutorials, comedy skits, fitness routines, and travel guides are published daily.
                    </p>
                    <p class="article-text" id="art2P2">
                        However, Instagram does not provide an uncompressed native offline download option for public Reels. Using InstaGo, you can easily save unlimited public Reels directly into your phone's camera roll or desktop hard drive without requiring any Instagram login, password, or third-party app installations.
                    </p>
                </div>
            </div>
        </section>

        <!-- Features Showcase (6 Rich Cards) -->
        <section class="section-features">
            <div class="section-heading">
                <h2 class="section-title" id="featHeading">Why Choose <span class="gradient-text">InstaGo</span>?</h2>
                <p class="section-subtitle" id="featSubheading">Engineered for speed, privacy, and maximum video quality</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <h3 id="feat1Title">Ultra Fast CDN Fetching</h3>
                    <p id="feat1Desc">InstaGo fetches original media directly from high-speed Instagram CDN nodes without compression or delays.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <h3 id="feat2Title">Original 1080p Quality</h3>
                    <p id="feat2Desc">Get crystal-clear 1080p Full HD video streams and full-resolution profile images with zero watermark or logos.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3 id="feat3Title">100% Anonymous & Safe</h3>
                    <p id="feat3Desc">No login, no password, and no account registration required. Your searches and downloads remain completely private.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4v8l4-4z"/></svg>
                    </div>
                    <h3 id="feat4Title">All-in-One Downloader</h3>
                    <p id="feat4Desc">Reels, Profile DP, Active Stories, Carousels, and Photos all accessible from a single smart input field.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                    <h3 id="feat5Title">Zero Watermarks or Stamps</h3>
                    <p id="feat5Desc">Save completely clean videos without any creator watermark, TikTok stamps, or site logos added to the frame.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                    </div>
                    <h3 id="feat6Title">Unlimited Free Access</h3>
                    <p id="feat6Desc">Enjoy 24/7 unlimited downloads forever without daily download caps, subscriptions, or hidden paywalls.</p>
                </div>
            </div>

            <!-- Device Compatibility -->
            <div class="devices-pill-list">
                <div class="device-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span id="devChip1">iPhone & iPad (iOS Safari)</span>
                </div>
                <div class="device-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span id="devChip2">Android (Chrome & Samsung)</span>
                </div>
                <div class="device-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span id="devChip3">Windows PC & Mac</span>
                </div>
            </div>
        </section>

        <!-- Frequently Asked Questions (8 Comprehensive Questions like indown.io) -->
        <section class="section-faq">
            <div class="section-heading">
                <h2 class="section-title" id="faqHeading">Frequently Asked <span class="gradient-text">Questions</span></h2>
                <p class="section-subtitle" id="faqSubheading">Got questions? We've got answers</p>
            </div>

            <div class="faq-list">
                <div class="faq-item open">
                    <button type="button" class="faq-question">
                        <span id="faqQ1">Is InstaGo free to use?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA1">Yes, InstaGo is 100% free with unlimited downloads. You do not need to purchase any subscription or install any third-party app.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ2">How can I download Instagram Reels on my iPhone?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA2">Open Safari on your iPhone, paste the copied Reel URL into InstaGo, and click "Download MP4". Safari will prompt you with a download dialog, and the video will be saved directly into your Files or Photos app.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ3">Can I download full-size HD Profile Pictures (DP)?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA3">Yes! Simply type the username (e.g. <code>@cristiano</code>) or paste the profile link into InstaGo. We fetch the uncompressed original HD profile picture for you to view or download.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ4">Do the downloaded videos contain any watermark?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA4">No, all videos and photos are downloaded in their raw, native quality without adding any logos, stamps, or watermarks.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ5">Where are downloaded videos saved on my phone?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA5">On Android, files are saved in your device's <strong>Downloads</strong> folder or Gallery app. On iOS, they appear in the Safari Downloads list and can be saved to the Photos app with one tap.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ6">How many Reels videos can I download per day?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA6">There is absolutely no limit! You can download as many Instagram Reels videos, Stories, and photos as you want. InstaGo is 100% free and accessible 24x7.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ7">Can we download Reels video in original Full HD 1080p?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA7">Yes, InstaGo always serves the highest resolution stream available from Instagram's CDN nodes, which is typically 1080p Full HD MP4 with crystal-clear audio.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span id="faqQ8">Is it legal to download Instagram Reels video?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p id="faqA8">Downloading Instagram Reels for personal offline viewing, educational purposes, or status updates is permissible. If you intend to use content commercially, you must seek permission from the original copyright holder.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bottom AdSense Banner Ad Slot -->
        <div class="ad-slot-wrapper ad-slot-bottom" aria-label="Advertisement">
            <span class="ad-badge-label">ADVERTISEMENT</span>
            <div class="ad-placeholder-box">
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
                     data-ad-slot="3456789012"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <div class="ad-fallback-chip">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Google AdSense Slot — Bottom Responsive Banner (ca-pub-XXXXXXXXXXXXXXXX)</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand & Disclaimer Column -->
                <div class="footer-brand-col">
                    <a href="./" class="footer-brand" aria-label="InstaGo Home">
                        <div class="brand-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </div>
                        <span class="footer-brand-name">InstaGo</span>
                    </a>

                    <p class="footer-tagline">
                        The fastest, 100% anonymous &amp; watermark-free Instagram media downloader. Save HD Reels, Stories, DP, and Photos in 1080p.
                    </p>

                    <div class="footer-disclaimer" id="footerDisclaimer">
                        <strong>Disclaimer:</strong> InstaGo is an independent tool and is not affiliated, endorsed, or associated with Instagram, Meta, or Facebook. We do not host any copyrighted videos or photos on our servers; all media files are served directly from official CDN servers.
                    </div>

                    <div class="footer-trust-chips">
                        <span class="trust-chip"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> 100% Free Forever</span>
                        <span class="trust-chip"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> No Login Required</span>
                        <span class="trust-chip"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Zero Watermark</span>
                    </div>
                </div>

                <!-- Navigation Columns -->
                <div class="footer-nav-columns">
                    <div class="footer-nav-col">
                        <h4 class="footer-col-title">Instagram Tools</h4>
                        <ul class="footer-links">
                            <li><a href="#searchForm" id="footLinkReels">Reels Downloader</a></li>
                            <li><a href="#searchForm" id="footLinkDP">DP Downloader</a></li>
                            <li><a href="#searchForm" id="footLinkStories">Story Downloader</a></li>
                            <li><a href="#searchForm" id="footLinkPhotos">Photo Downloader</a></li>
                        </ul>
                    </div>

                    <div class="footer-nav-col">
                        <h4 class="footer-col-title">Device Guides</h4>
                        <ul class="footer-links">
                            <li><a href="#deviceGuide">iPhone &amp; iPad</a></li>
                            <li><a href="#deviceGuide">Android</a></li>
                            <li><a href="#deviceGuide">Windows PC</a></li>
                            <li><a href="#deviceGuide">Mac OS</a></li>
                        </ul>
                    </div>

                    <div class="footer-nav-col">
                        <h4 class="footer-col-title">Legal &amp; Policy</h4>
                        <ul class="footer-links">
                            <li><a href="javascript:void(0)" class="legal-open-btn" data-policy="privacy">Privacy Policy</a></li>
                            <li><a href="javascript:void(0)" class="legal-open-btn" data-policy="terms">Terms of Service</a></li>
                            <li><a href="javascript:void(0)" class="legal-open-btn" data-policy="about">About Us</a></li>
                            <li><a href="javascript:void(0)" class="legal-open-btn" data-policy="contact">Contact &amp; DMCA</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Social Share Bar -->
            <div class="footer-share-bar">
                <div class="share-bar-label">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    <span>Share InstaGo with Friends:</span>
                </div>
                <div class="share-chips">
                    <a href="https://api.whatsapp.com/send?text=Download%20Instagram%20Reels%20in%20Full%20HD%20free%20at%20<?= urlencode(SITE_URL) ?>" target="_blank" rel="noopener" class="share-btn whatsapp" title="Share on WhatsApp" aria-label="Share on WhatsApp">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.34C9.36 7.34 9.09 7.4 8.87 7.65C8.65 7.89 8.02 8.49 8.02 9.71C8.02 10.93 8.91 12.11 9.03 12.28C9.16 12.45 10.74 14.9 13.18 15.95C15.21 16.83 15.62 16.65 16.07 16.61C16.51 16.57 17.5 16.02 17.7 15.44C17.91 14.87 17.91 14.38 17.85 14.28C17.78 14.17 17.61 14.11 17.35 13.98C17.09 13.85 15.82 13.23 15.58 13.14C15.35 13.06 15.18 13.01 15.01 13.27C14.84 13.53 14.36 14.11 14.21 14.28C14.07 14.45 13.92 14.47 13.66 14.34C13.4 14.21 12.56 13.94 11.57 13.05C10.79 12.36 10.27 11.5 10.14 11.28C10.01 11.06 10.12 10.94 10.25 10.81C10.37 10.7 10.51 10.51 10.65 10.36C10.78 10.2 10.83 10.09 10.92 9.92C11 9.75 10.96 9.61 10.9 9.48C10.83 9.35 10.31 8.08 10.09 7.56C9.88 7.05 9.66 7.12 9.53 7.34Z"/></svg>
                    </a>
                    <a href="https://t.me/share/url?url=<?= urlencode(SITE_URL) ?>&text=Download%20Instagram%20Reels%20in%20Full%20HD" target="_blank" rel="noopener" class="share-btn telegram" title="Share on Telegram" aria-label="Share on Telegram">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL) ?>&text=Download%20Instagram%20Reels%20in%201080p%20HD" target="_blank" rel="noopener" class="share-btn twitter" title="Share on X (Twitter)" aria-label="Share on X (Twitter)">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL) ?>" target="_blank" rel="noopener" class="share-btn facebook" title="Share on Facebook" aria-label="Share on Facebook">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="footer-bottom">
                <p id="footerCopyright">&copy; <?php echo date('Y'); ?> <strong>InstaGo</strong>. All rights reserved. Fast &amp; Simple Instagram Downloader.</p>
            </div>
        </div>
    </footer>

    <!-- Comprehensive AdSense-Compliant Legal Policy Modal Dialog -->
    <div id="legalModal" class="legal-modal-backdrop" aria-hidden="true" role="dialog">
        <div class="legal-modal-box">
            <div class="legal-modal-header">
                <div class="modal-tab-pills">
                    <button type="button" class="modal-tab-btn active" data-tab="privacy">Privacy Policy</button>
                    <button type="button" class="modal-tab-btn" data-tab="terms">Terms of Service</button>
                    <button type="button" class="modal-tab-btn" data-tab="about">About Us</button>
                    <button type="button" class="modal-tab-btn" data-tab="contact">Contact Us</button>
                </div>
                <button type="button" class="modal-close-btn" id="legalModalClose" aria-label="Close Modal">&times;</button>
            </div>
            <div class="legal-modal-body">
                <!-- Privacy Policy Content -->
                <div class="modal-tab-pane active" id="pane-privacy">
                    <h3>Privacy Policy &amp; Cookie Disclosure</h3>
                    <p class="modal-date">Last Updated: <?php echo date('F Y'); ?></p>
                    <p>At InstaGo (accessible from <?= htmlspecialchars(SITE_URL) ?>), one of our main priorities is the privacy of our visitors. This Privacy Policy document outlines the types of information collected and how we use it in strict compliance with Google AdSense Policies, CCPA, and GDPR.</p>
                    <h4>1. Zero Log &amp; No Account Policy</h4>
                    <p>InstaGo does not require you to register, log in, or provide any personal credentials (such as Instagram username, password, or cookies). We do not record search queries or build personal profiles of our users.</p>
                    <h4>2. Google DoubleClick DART Cookies &amp; AdSense</h4>
                    <p>Google is one of our third-party vendors on our site. It uses cookies, known as DART cookies, to serve ads to our site visitors based upon their visit to this website and other sites on the internet. You may choose to opt out of the use of DART cookies by visiting the Google Ad and Content Network Privacy Policy at <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">policies.google.com/technologies/ads</a>.</p>
                    <h4>3. CDN &amp; Server Logs</h4>
                    <p>Like standard web hosts, our web servers record standard HTTP request logs (IP address, browser type, timestamp, referring URL). This data is used solely for system administration, fraud prevention, and performance optimization.</p>
                </div>

                <!-- Terms of Service Content -->
                <div class="modal-tab-pane" id="pane-terms">
                    <h3>Terms of Service &amp; Fair Use</h3>
                    <p class="modal-date">Last Updated: <?php echo date('F Y'); ?></p>
                    <p>By accessing or using InstaGo, you agree to be bound by these Terms of Service. If you disagree with any part of these terms, please do not use our service.</p>
                    <h4>1. Educational &amp; Personal Use</h4>
                    <p>InstaGo is provided strictly as an educational utility to assist users in making personal backup copies of public Instagram content they have legitimate rights to access under fair use doctrines.</p>
                    <h4>2. Intellectual Property Rights</h4>
                    <p>InstaGo does not own, host, or store any video, audio, or image files on its servers. All media files are retrieved on-the-fly and streamed directly from Instagram's official CDN. All copyrights, trademarks, and intellectual property remain the sole property of their respective creators and Meta Platforms Inc.</p>
                    <h4>3. User Responsibility &amp; Commercial Prohibition</h4>
                    <p>Users are strictly prohibited from utilizing InstaGo to pirate copyrighted work, distribute uncredited intellectual property, or use downloaded materials for unauthorized commercial purposes.</p>
                </div>

                <!-- About Us Content -->
                <div class="modal-tab-pane" id="pane-about">
                    <h3>About InstaGo</h3>
                    <p>InstaGo was founded with a clear mission: to provide the fastest, cleanest, and most reliable Instagram media downloader without intrusive popups, forced software downloads, or privacy compromises.</p>
                    <h4>Why Millions Choose InstaGo:</h4>
                    <ul>
                        <li><strong>Pure 1080p Fidelity:</strong> We never downscale or watermark original Instagram Reels or Photos.</li>
                        <li><strong>Safe &amp; Anonymous:</strong> Zero trackers, zero login walls, and zero stored personal data.</li>
                        <li><strong>Lightning Fast CDN:</strong> Distributed nodes for instantaneous stream fetching.</li>
                        <li><strong>Every Device Supported:</strong> Fully responsive on iOS Safari, Android, tablets, and desktop computers.</li>
                    </ul>
                </div>

                <!-- Contact Us Content -->
                <div class="modal-tab-pane" id="pane-contact">
                    <h3>Contact &amp; DMCA Inquiry</h3>
                    <p>We value user feedback, technical inquiries, and DMCA safe harbor notifications. If you have questions, feedback, or DMCA inquiries, please reach out to our dedicated support team:</p>
                    <div class="contact-card-box">
                        <p><strong>Official Inquiries &amp; Support:</strong> support@instago.app</p>
                        <p><strong>DMCA &amp; Copyright Compliance:</strong> dmca@instago.app</p>
                        <p><strong>Response Time:</strong> Typically within 24 to 48 business hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Zoom Modal for Real Step Screenshots -->
    <div id="guideLightbox" class="guide-lightbox-backdrop" aria-hidden="true" role="dialog">
        <div class="guide-lightbox-box">
            <div class="lightbox-header">
                <span id="lightboxTitle">Step Screenshot</span>
                <button type="button" class="lightbox-close-btn" id="lightboxCloseBtn" aria-label="Close Lightbox">&times;</button>
            </div>
            <div class="lightbox-body">
                <img id="lightboxImg" src="" alt="Enlarged Step Screenshot">
            </div>
        </div>
    </div>

    <!-- Mobile App-Style Bottom Navigation Dock (Mobile & Tablet) -->
    <nav class="mobile-app-dock" id="mobileAppDock" aria-label="Mobile App Navigation">
        <button type="button" class="dock-btn active" data-type="all" aria-label="All Media">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            <span id="dockLabelAll">All</span>
        </button>
        <button type="button" class="dock-btn" data-type="reels" aria-label="Reels">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            <span id="dockLabelReels">Reels</span>
        </button>
        <button type="button" class="dock-btn" data-type="dp" aria-label="Profile DP">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span id="dockLabelDP">DP</span>
        </button>
        <button type="button" class="dock-btn" data-type="stories" aria-label="Stories">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <span id="dockLabelStories">Stories</span>
        </button>
        <button type="button" class="dock-btn" data-type="photos" aria-label="Photos">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span id="dockLabelPhotos">Photos</span>
        </button>
    </nav>

    <!-- App Script (Auto Cache Busting) -->
    <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/assets/js/app.js') ?>"></script>
</body>
</html>
