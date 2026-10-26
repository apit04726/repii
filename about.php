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
                var theme = localStorage.getItem('instago_theme_user_choice') || 'dark';
                document.documentElement.setAttribute('data-theme', theme);
            } catch(e) {}
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a0e17" id="metaThemeColor">
    <title>About Us &amp; Engineering Mission - <?= htmlspecialchars(SITE_NAME) ?></title>
    <meta name="description" content="Learn about <?= htmlspecialchars(SITE_NAME) ?>, our high-speed media processing architecture, privacy philosophy, and mission to deliver watermark-free Instagram downloads.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/about.php') ?>">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body>
    <div class="bg-glow-top"></div>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <nav class="nav-wrap">
                <a href="./" class="brand" aria-label="<?= htmlspecialchars(SITE_NAME) ?> Home">
                    <div class="brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </div>
                    <span><?= htmlspecialchars(SITE_NAME) ?></span>
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
                        <svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="7" r="4"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
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
        <div class="legal-page-wrap">
            <div class="legal-breadcrumbs">
                <a href="./" id="aboutBreadHome">Home</a>
                <span>/</span>
                <span id="aboutBreadCurrent">About Us</span>
            </div>

            <div class="legal-header">
                <div class="legal-badge">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span id="aboutBadgeText">Mission &amp; Engineering</span>
                </div>
                <h1 class="legal-title" id="aboutTitleText">About <?= htmlspecialchars(SITE_NAME) ?></h1>
                <p class="legal-subtitle" id="aboutSubtitleText">Building the world's cleanest, fastest, and most privacy-focused Instagram media downloader.</p>
            </div>

            <article class="legal-card" id="aboutArticleCard">
                <div id="aboutArticleBody">
                    <h2>Our Mission</h2>
                    <p><strong><?= htmlspecialchars(SITE_NAME) ?></strong> was founded with a straightforward goal: to provide an intuitive, high-speed, and secure web platform that empowers creators, educators, and social media enthusiasts to save publicly accessible Instagram media without intrusive advertisements, confusing redirects, or privacy compromises.</p>

                    <div class="legal-box-highlight success">
                        <p><strong>Our Philosophy:</strong> Clean design, zero login hurdles, zero watermarks, and maximum native video fidelity (1080p Full HD MP4).</p>
                    </div>

                    <h2>What Makes <?= htmlspecialchars(SITE_NAME) ?> Unique?</h2>
                    <div class="legal-contact-grid">
                        <div class="legal-contact-card">
                            <h4>🎬 1080p Pure Fidelity</h4>
                            <p>We never compress, resize, or alter original Instagram video bitrate or audio fidelity. Videos are delivered exactly as uploaded by original creators.</p>
                        </div>
                        <div class="legal-contact-card">
                            <h4>🔒 Privacy by Design</h4>
                            <p>No account required, no passwords stored, and zero tracking cookies. Your searches are processed entirely on-the-fly and never recorded.</p>
                        </div>
                        <div class="legal-contact-card">
                            <h4>⚡ High-Speed CDN Proxy</h4>
                            <p>Our distributed chunked proxy streams files with native download headers, preventing download timeouts and ensuring maximum download speed.</p>
                        </div>
                        <div class="legal-contact-card">
                            <h4>📱 All Devices Supported</h4>
                            <p>Engineered with a native app-like experience across iOS Safari (iPhone &amp; iPad), Android Chrome, Windows PC, Mac, and Linux.</p>
                        </div>
                    </div>

                    <h2>Fair &amp; Ethical Service</h2>
                    <p>We believe in supporting content creators and respecting intellectual property. <?= htmlspecialchars(SITE_NAME) ?> operates strictly within fair-use boundaries by encouraging users to credit original creators when using media for educational or commentary purposes, and we offer responsive DMCA copyright removal channels for copyright holders.</p>

                    <h2>Technical Reliability &amp; Uptime</h2>
                    <p>We actively maintain multi-node failover parsing pipelines to guarantee 99.9% uptime, even when third-party social protocols update their delivery endpoints.</p>
                </div>

                <div class="legal-action-bar">
                    <a href="./" class="btn-back-home">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span id="aboutBtnHomeText">Start Downloading Media</span>
                    </a>
                    <span id="aboutAssistText" style="color:var(--text-muted);font-size:0.88rem;">Need assistance? <a href="contact.php" style="color:var(--accent-blue);">Contact Support</a></span>
                </div>
            </article>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand & Disclaimer Column -->
                <div class="footer-brand-col">
                    <a href="./" class="footer-brand" aria-label="<?= htmlspecialchars(SITE_NAME) ?> Home">
                        <div class="brand-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </div>
                        <span class="footer-brand-name"><?= htmlspecialchars(SITE_NAME) ?></span>
                    </a>

                    <p class="footer-tagline" id="footerTagline">
                        The fastest, 100% anonymous &amp; watermark-free Instagram media downloader. Save HD Reels, Stories, DP, and Photos in 1080p.
                    </p>

                    <div class="footer-disclaimer" id="footerDisclaimer">
                        <strong>Disclaimer:</strong> <?= htmlspecialchars(SITE_NAME) ?> is an independent tool and is not affiliated, endorsed, or associated with Instagram, Meta, or Facebook. We do not host any copyrighted videos or photos on our servers; all media files are served directly from official CDN servers.
                    </div>

                    <div class="footer-trust-chips">
                        <span class="trust-chip"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="trustChip1">100% Free Forever</span></span>
                        <span class="trust-chip"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="trustChip2">No Login Required</span></span>
                        <span class="trust-chip"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span id="trustChip3">Zero Watermark</span></span>
                    </div>
                </div>

                <!-- Navigation Columns -->
                <div class="footer-nav-columns">
                    <div class="footer-nav-col">
                        <h4 class="footer-col-title" id="footColTools">Instagram Tools</h4>
                        <ul class="footer-links">
                            <li><a href="./#searchForm" class="foot-tool-link" data-type="reels" id="footLinkReels">Reels Downloader</a></li>
                            <li><a href="./#searchForm" class="foot-tool-link" data-type="dp" id="footLinkDP">DP Downloader</a></li>
                            <li><a href="./#searchForm" class="foot-tool-link" data-type="stories" id="footLinkStories">Story Downloader</a></li>
                            <li><a href="./#searchForm" class="foot-tool-link" data-type="highlights" id="footLinkHighlights">Highlights Downloader</a></li>
                            <li><a href="./#searchForm" class="foot-tool-link" data-type="photos" id="footLinkPhotos">Photo Downloader</a></li>
                        </ul>
                    </div>

                    <div class="footer-nav-col">
                        <h4 class="footer-col-title" id="footColGuides">Device Guides</h4>
                        <ul class="footer-links">
                            <li><a href="./#deviceGuide" id="footDevIos">iPhone &amp; iPad</a></li>
                            <li><a href="./#deviceGuide" id="footDevAndroid">Android</a></li>
                            <li><a href="./#deviceGuide" id="footDevPC">Windows PC</a></li>
                            <li><a href="./#deviceGuide" id="footDevMac">Mac OS</a></li>
                        </ul>
                    </div>

                    <div class="footer-nav-col">
                        <h4 class="footer-col-title" id="footColLegal">Legal &amp; Policy</h4>
                        <ul class="footer-links">
                            <li><a href="privacy.php" id="footLegPrivacy">Privacy Policy</a></li>
                            <li><a href="terms.php" id="footLegTerms">Terms of Service</a></li>
                            <li><a href="about.php" id="footLegAbout">About Us</a></li>
                            <li><a href="contact.php" id="footLegContact">Contact &amp; DMCA</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Social Share Bar -->
            <div class="footer-share-bar">
                <div class="share-bar-label">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    <span id="shareBarLabel">Share <?= htmlspecialchars(SITE_NAME) ?> with Friends:</span>
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
                <p id="footerCopyright">&copy; <?php echo date('Y'); ?> <strong><?= htmlspecialchars(SITE_NAME) ?></strong>. All rights reserved. Fast &amp; Simple Instagram Downloader.</p>
            </div>
        </div>
    </footer>

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
        <button type="button" class="dock-btn" data-type="highlights" aria-label="Highlights">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polygon points="12 8 13.5 11 17 11.5 14.5 14 15 17.5 12 16 9 17.5 9.5 14 7 11.5 10.5 11 12 8"/></svg>
            <span id="dockLabelHighlights">Highlights</span>
        </button>
        <button type="button" class="dock-btn" data-type="photos" aria-label="Photos">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span id="dockLabelPhotos">Photos</span>
        </button>
    </nav>

    <!-- App Script -->
    <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/assets/js/app.js') ?>"></script>
</body>
</html>
