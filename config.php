<?php
/**
 * InstaGo - Configuration File
 * Instagram Reels, DP, Stories & Photos Downloader
 */

// Direct access prevention
if (basename($_SERVER['PHP_SELF'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit('Access Denied');
}

// Site Details
define('SITE_NAME', 'InstaGo');
define('SITE_TAGLINE', 'Fastest Instagram Reels, DP & Stories Downloader');
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir === '/' || $scriptDir === '.' || str_starts_with($scriptDir, '/api')) { $scriptDir = ''; }
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$protocol = $is_https ? "https" : "http";
define('SITE_URL', rtrim($protocol . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $scriptDir, '/'));

// Optional RapidAPI / Third-party API Key for ultra-high stability (Free tiers available on RapidAPI)
// Leave empty to use direct built-in scrapers
define('RAPIDAPI_KEY', '');
define('RAPIDAPI_HOST', 'instagram-downloader-download-instagram-videos-stories1.p.rapidapi.com');

// Primary Instagram Session Cookie (sessionid)
// NOTE: NEVER use personal Instagram accounts! Instagram bans personal accounts used on servers.
define('INSTAGRAM_SESSION_ID', '22996724939%3AK5Zi6yeqjWfZ1R%3A19%3AAYnlWO1cKV9jLNltGiJinF4pGJRiZIbM2DmDtzd2iQ');
define('INSTAGRAM_CSRF_TOKEN', '4ukCpjDx9plIbLX5Kk5nDvRVIdy5bVqd');
define('INSTAGRAM_DS_USER_ID', '22996724939');

// Multi-Account Anti-Ban Session Pool (Load-Balanced)
// Add all your active accounts here. Traffic is automatically divided equally across all accounts.
define('INSTAGRAM_SESSION_POOL', [
    [
        // Account 1 (Active)
        'session_id' => '22996724939%3AK5Zi6yeqjWfZ1R%3A19%3AAYnlWO1cKV9jLNltGiJinF4pGJRiZIbM2DmDtzd2iQ',
        'csrf_token' => '4ukCpjDx9plIbLX5Kk5nDvRVIdy5bVqd',
        'ds_user_id' => '22996724939'
    ],
    [
        // Account 2 (Paste your reactivated account credentials here)
        'session_id' => '',
        'csrf_token' => '',
        'ds_user_id' => ''
    ],
    [
        // Account 3 (Paste your reactivated account credentials here)
        'session_id' => '',
        'csrf_token' => '',
        'ds_user_id' => ''
    ]
]);

// Anti-Ban Proxy Configuration (Guarantees 0% ban by routing server requests via residential IPs)
// Set to true once you add your proxy (e.g. Free 10 proxies from Webshare.io)
define('USE_PROXY', false);
define('PROXY_URL', ''); // e.g. 'http://username:password@p.webshare.io:80'

// Optional Proxy Pool for automatic multi-proxy rotation
define('PROXY_POOL', [
    // 'http://username:password@ip1:port',
    // 'http://username:password@ip2:port',
]);

// Timeouts
define('CURL_TIMEOUT', 15);
