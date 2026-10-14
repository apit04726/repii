<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>InstaGo — Instagram Reels, DP & Stories Downloader (Full HD)</title>
    <meta name="description" content="Download Instagram Reels, HD Profile Pictures (DP), Stories, and Photos online for free. Works seamlessly on iPhone, Android, Tablets, and PC. No watermark, original quality.">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="InstaGo — Best Instagram Reels, DP & Stories Downloader">
    <meta property="og:description" content="Fast, free, and watermark-free Instagram media downloader. Save HD Reels, Stories, Photos & DP to your gallery instantly.">
    <meta property="og:site_name" content="InstaGo">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
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
                <span>Fast & Free 1080p Instagram Downloader</span>
            </div>

            <h1 class="hero-title">
                Download Instagram <br>
                <span class="gradient-text">Reels, DP & Stories</span>
            </h1>

            <p class="hero-desc">
                Save HD Reels, Full Resolution Profile Pictures (DP), Stories, and Carousel Photos directly to your Gallery or PC.
            </p>

            <!-- Navigation Tabs -->
            <div class="tabs-container">
                <button type="button" class="tab-btn active" data-type="all">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                    <span>All-in-One</span>
                </button>
                <button type="button" class="tab-btn" data-type="reels">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    <span>Reels</span>
                </button>
                <button type="button" class="tab-btn" data-type="dp">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Profile DP</span>
                </button>
                <button type="button" class="tab-btn" data-type="stories">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span>Stories</span>
                </button>
                <button type="button" class="tab-btn" data-type="photos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span>Photos</span>
                </button>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form id="searchForm" autocomplete="off">
                    <div class="search-box-wrapper">
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

                        <div class="input-actions">
                            <button type="button" id="clearBtn" class="action-btn clear-btn" title="Clear input">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>

                            <button type="button" id="pasteBtn" class="action-btn" title="Paste from Clipboard">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                                <span>Paste</span>
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

            <!-- Error Alert -->
            <div id="errorAlert" class="alert-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span id="errorMessage">Something went wrong. Please check your URL.</span>
            </div>

            <!-- Loader Card -->
            <div id="loaderCard" class="loader-card">
                <div class="spinner"></div>
                <h4 class="loader-title">Processing Media</h4>
                <p id="loaderStatus" class="loader-subtitle">Connecting to Instagram servers...</p>
            </div>

            <!-- Dynamic Result Card -->
            <div id="resultCard" class="result-card">
                <div class="result-header">
                    <span id="resultTypeBadge" class="result-type-badge">REEL HD</span>
                    <div class="result-author">
                        <img id="resultAuthorAvatar" class="result-author-avatar" src="" alt="Avatar">
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

        <!-- Steps to Download Section -->
        <section class="section-steps">
            <div class="section-heading">
                <h2 class="section-title">How to Download from Instagram</h2>
                <p class="section-subtitle">Save any Reel, Profile DP, or Story in 3 easy steps</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-badge">1</div>
                    <h3>Copy Instagram Link</h3>
                    <p>Open Instagram on your phone or browser, navigate to the Reel, Story, or Profile, tap the Share icon and click <strong>"Copy Link"</strong>.</p>
                </div>

                <div class="step-card">
                    <div class="step-badge">2</div>
                    <h3>Paste in InstaGo</h3>
                    <p>Return to <strong>InstaGo</strong>, tap the <strong>"Paste"</strong> button in the search box, and click <strong>"Search"</strong>.</p>
                </div>

                <div class="step-card">
                    <div class="step-badge">3</div>
                    <h3>Download in Full HD</h3>
                    <p>Click <strong>"Download MP4"</strong> or <strong>"Download HD DP"</strong>. The file will be saved directly into your device's Gallery or Downloads folder.</p>
                </div>
            </div>
        </section>

        <!-- Features Showcase -->
        <section class="section-features">
            <div class="section-heading">
                <h2 class="section-title">Why Choose <span class="gradient-text">InstaGo</span>?</h2>
                <p class="section-subtitle">Engineered for speed, privacy, and maximum video quality</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <h3>Ultra Fast Downloads</h3>
                    <p>InstaGo fetches original media directly from high-speed Instagram CDN nodes without compression or delays.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <h3>Original 1080p Quality</h3>
                    <p>Get crystal-clear 1080p Full HD video streams and full-resolution profile images with zero watermark or logos.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3>100% Anonymous & Safe</h3>
                    <p>No login, no password, and no account registration required. Your searches and downloads remain completely private.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4v8l4-4z"/></svg>
                    </div>
                    <h3>All-in-One Downloader</h3>
                    <p>Reels, Profile DP, Active Stories, Carousels, and Photos all accessible from a single smart input field.</p>
                </div>
            </div>

            <!-- Device Compatibility -->
            <div class="devices-pill-list">
                <div class="device-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>iPhone & iPad (iOS Safari)</span>
                </div>
                <div class="device-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Android (Chrome & Samsung)</span>
                </div>
                <div class="device-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Windows PC & Mac</span>
                </div>
            </div>
        </section>

        <!-- Frequently Asked Questions -->
        <section class="section-faq">
            <div class="section-heading">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Got questions? We've got answers</p>
            </div>

            <div class="faq-list">
                <div class="faq-item open">
                    <button type="button" class="faq-question">
                        <span>Is InstaGo free to use?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Yes, InstaGo is 100% free with unlimited downloads. You do not need to purchase any subscription or install any third-party app.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>How can I download Instagram Reels on my iPhone?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Open Safari on your iPhone, paste the copied Reel URL into InstaGo, and click "Download MP4". Safari will prompt you with a download dialog, and the video will be saved directly into your Files or Photos app.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>Can I download full-size HD Profile Pictures (DP)?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Yes! Simply type the username (e.g. <code>@cristiano</code>) or paste the profile link into InstaGo. We fetch the uncompressed original HD profile picture for you to view or download.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>Do the downloaded videos contain any watermark?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>No, all videos and photos are downloaded in their raw, native quality without adding any logos, stamps, or watermarks.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>Where are downloaded videos saved on my phone?</span>
                        <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>On Android, files are saved in your device's <strong>Downloads</strong> folder or Gallery app. On iOS, they appear in the Safari Downloads list and can be saved to the Photos app with one tap.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-disclaimer">
                    <strong>Disclaimer:</strong> InstaGo is an independent tool and is not affiliated, endorsed, or associated with Instagram, Meta, or Facebook. We do not host any copyrighted videos or photos on our servers; all media files are served directly from official CDN servers.
                </div>
                <ul class="footer-links">
                    <li><a href="#searchForm">Reels Downloader</a></li>
                    <li><a href="#searchForm">DP Downloader</a></li>
                    <li><a href="#searchForm">Story Downloader</a></li>
                </ul>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <strong>InstaGo</strong>. All rights reserved. Fast & Simple Instagram Downloader.</p>
            </div>
        </div>
    </footer>

    <!-- App Script -->
    <script src="assets/js/app.js"></script>
</body>
</html>
