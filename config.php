<?php
/**
 * InstaGo - Configuration File
 * Instagram Reels, DP, Stories & Photos Downloader
 */

// Site Details
define('SITE_NAME', 'InstaGo');
define('SITE_TAGLINE', 'Fastest Instagram Reels, DP & Stories Downloader');
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Optional RapidAPI / Third-party API Key for ultra-high stability (Free tiers available on RapidAPI)
// Leave empty to use direct built-in scrapers
define('RAPIDAPI_KEY', '');
define('RAPIDAPI_HOST', 'instagram-downloader-download-instagram-videos-stories1.p.rapidapi.com');

// Optional Instagram Session Cookie (sessionid)
// If provided, allows fetching 100% of Reels, Stories, and HD DP without rate limits
define('INSTAGRAM_SESSION_ID', '22996724939%3AK5Zi6yeqjWfZ1R%3A19%3AAYnlWO1cKV9jLNltGiJinF4pGJRiZIbM2DmDtzd2iQ');
define('INSTAGRAM_CSRF_TOKEN', '4ukCpjDx9plIbLX5Kk5nDvRVIdy5bVqd');
define('INSTAGRAM_DS_USER_ID', '22996724939');

// Proxy configuration (Optional, useful if your hosting server IP gets restricted by Instagram)
define('USE_PROXY', false);
define('PROXY_URL', ''); // e.g. 'http://username:password@ip:port'

// Timeouts
define('CURL_TIMEOUT', 15);
