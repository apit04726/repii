<?php
require_once __DIR__ . '/config.php';

// Security & AdSense Compliance Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

$msg_sent = false;
$msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $sender_email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '');
    $sender_subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $sender_category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'General Support');
    $sender_message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

    if (empty($sender_name) || empty($sender_email) || empty($sender_message)) {
        $msg_error = 'Please fill out all required fields with valid details.';
    } else {
        $msg_sent = true;
    }
}
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
    <title>Contact Us &amp; DMCA Copyright Notice - <?= htmlspecialchars(SITE_NAME) ?></title>
    <meta name="description" content="Get in touch with <?= htmlspecialchars(SITE_NAME) ?> support team or file a DMCA copyright takedown inquiry. 24/7 dedicated assistance.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars(SITE_URL . '/contact.php') ?>">
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
                            <circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
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
                <a href="./" id="contactBreadHome">Home</a>
                <span>/</span>
                <span id="contactBreadCurrent">Contact &amp; DMCA</span>
            </div>

            <div class="legal-header">
                <div class="legal-badge">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span id="contactBadgeText">Support &amp; Copyright Desk</span>
                </div>
                <h1 class="legal-title" id="contactTitleText">Contact Us &amp; DMCA Takedown Notice</h1>
                <p class="legal-subtitle" id="contactSubtitleText">We are here to assist with technical feedback, partner inquiries, and DMCA copyright compliance.</p>
            </div>

            <div class="contact-layout-grid">
                <!-- Left Column: Official Contact Channels & DMCA Policy -->
                <div class="contact-channels-wrap">
                    <div class="channel-card-item">
                        <div class="channel-card-head">
                            <span class="channel-card-title">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span id="ch1TitleText">Technical Support &amp; Feedback</span>
                            </span>
                            <span class="channel-badge" id="ch1BadgeText">24/7 Desk</span>
                        </div>
                        <p id="ch1DescText" style="color:var(--text-secondary);font-size:0.92rem;margin-bottom:6px;">For issues with video downloading, browser compatibility, or suggestions:</p>
                        <a href="mailto:support@instago.app" class="channel-card-email">support@instago.app</a>
                    </div>

                    <div class="channel-card-item">
                        <div class="channel-card-head">
                            <span class="channel-card-title">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span id="ch2TitleText">DMCA &amp; Copyright Compliance</span>
                            </span>
                            <span class="channel-badge" id="ch2BadgeText" style="background:rgba(221,42,123,0.12);color:var(--accent-pink);border-color:rgba(221,42,123,0.3);">Safe Harbor</span>
                        </div>
                        <p id="ch2DescText" style="color:var(--text-secondary);font-size:0.92rem;margin-bottom:6px;">For verified copyright holders, intellectual property, and link removal inquiries:</p>
                        <a href="mailto:dmca@instago.app" class="channel-card-email">dmca@instago.app</a>
                    </div>

                    <div class="channel-card-item">
                        <div class="channel-card-head">
                            <span class="channel-card-title">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                <span id="ch3TitleText">Business &amp; Partnerships</span>
                            </span>
                            <span class="channel-badge" id="ch3BadgeText" style="background:rgba(56,189,248,0.12);color:var(--accent-blue);border-color:rgba(56,189,248,0.3);">Enterprise</span>
                        </div>
                        <p id="ch3DescText" style="color:var(--text-secondary);font-size:0.92rem;margin-bottom:6px;">For press, advertising inquiries, and technical infrastructure collaborations:</p>
                        <a href="mailto:partner@instago.app" class="channel-card-email">partner@instago.app</a>
                    </div>

                    <div class="legal-box-highlight" style="margin:0;">
                        <p id="dmcaReqText" style="font-size:0.9rem;line-height:1.6;">
                            <strong>DMCA Notice Requirements (17 U.S.C. § 512):</strong> When submitting a takedown notice, please provide: (1) Description of copyrighted work, (2) The exact Instagram URL to block, (3) Proof of copyright ownership, and (4) Your full contact credentials.
                        </p>
                    </div>
                </div>

                <!-- Right Column: Interactive Contact Form -->
                <div class="contact-form-card">
                    <h2 class="contact-form-title" id="contactFormTitleText">Send Us a Direct Message</h2>
                    <p class="contact-form-desc" id="contactFormDescText">Fill out the form below and our dedicated support team will reply within 24 to 48 business hours.</p>

                    <?php if ($msg_sent): ?>
                        <div class="legal-box-highlight success" style="margin-bottom:20px;">
                            <p id="contactSuccessMsg"><strong>Thank you, <?= htmlspecialchars($sender_name) ?>!</strong> Your inquiry has been received. A support representative will review your message promptly.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($msg_error)): ?>
                        <div class="legal-box-highlight warning" style="margin-bottom:20px;">
                            <p><strong>Attention:</strong> <?= htmlspecialchars($msg_error) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" id="contactForm">
                        <div class="contact-form-row">
                            <div class="contact-input-group">
                                <label class="contact-label" for="contactName" id="labelNameText">Your Name *</label>
                                <input type="text" id="contactName" name="name" required placeholder="John Doe" class="contact-input" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="contact-input-group">
                                <label class="contact-label" for="contactEmail" id="labelEmailText">Email Address *</label>
                                <input type="email" id="contactEmail" name="email" required placeholder="john@example.com" class="contact-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="contact-input-group">
                            <label class="contact-label" for="contactCategory" id="labelCategoryText">Inquiry Type</label>
                            <select id="contactCategory" name="category" class="contact-select">
                                <option value="General Support" id="optGeneralText">General Support &amp; Technical Feedback</option>
                                <option value="DMCA Copyright" id="optDmcaText">DMCA Copyright Takedown Notice</option>
                                <option value="Bug Report" id="optBugText">Bug Report / Video Fetching Issue</option>
                                <option value="Partnership" id="optPartnerText">Business Partnership &amp; Press</option>
                            </select>
                        </div>

                        <div class="contact-input-group">
                            <label class="contact-label" for="contactSubject" id="labelSubjectText">Subject</label>
                            <input type="text" id="contactSubject" name="subject" placeholder="Brief summary of your inquiry" class="contact-input" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                        </div>

                        <div class="contact-input-group">
                            <label class="contact-label" for="contactMessage" id="labelMessageText">Message *</label>
                            <textarea id="contactMessage" name="message" rows="5" required placeholder="Provide as much detail as possible to help us assist you..." class="contact-textarea"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn-back-home" style="border:none;cursor:pointer;width:100%;justify-content:center;">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            <span id="btnSendMsgText">Send Message</span>
                        </button>
                    </form>

                    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                        <span id="contactTrustLine" style="color:var(--text-muted);font-size:0.84rem;">🔒 We protect your data under our <a href="privacy.php" style="color:var(--accent-blue);">Privacy Policy</a></span>
                        <span id="contactRespTime" style="color:var(--text-muted);font-size:0.84rem;">Response: &le; 24h</span>
                    </div>
                </div>
            </div>

            <div class="legal-action-bar">
                <a href="./" class="btn-back-home">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span id="contactBtnHomeText">Back to Downloader</span>
                </a>
                <span id="contactFooterTermsLine" style="color:var(--text-muted);font-size:0.88rem;">Read our <a href="terms.php" style="color:var(--accent-blue);">Terms of Service</a> &amp; <a href="privacy.php" style="color:var(--accent-blue);">Privacy Policy</a></span>
            </div>
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
