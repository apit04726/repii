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
define('INSTAGRAM_SESSION_ID', '22996724939%3AK5Zi6yeqjWfZ1R%3A19%3AAYnlWO1cKV9jLNltGiJinF4pGJRiZIbM2DmDtzd2iQ');
define('INSTAGRAM_CSRF_TOKEN', '4ukCpjDx9plIbLX5Kk5nDvRVIdy5bVqd');
define('INSTAGRAM_DS_USER_ID', '22996724939');

// Session Pool (indown.io multi-account rotation setup)
// Automatically fails over across active accounts
define('INSTAGRAM_SESSION_POOL', [
    [
        // Account 1: @vishwraj__122 (Active & Verified)
        'session_id' => '22996724939%3AK5Zi6yeqjWfZ1R%3A19%3AAYnlWO1cKV9jLNltGiJinF4pGJRiZIbM2DmDtzd2iQ',
        'csrf_token' => '4ukCpjDx9plIbLX5Kk5nDvRVIdy5bVqd',
        'ds_user_id' => '22996724939'
    ],
    [
        // Account 2: @rohansharma00785 (Active & Verified)
        'session_id' => '23981181226%3ASgLOC93SLtYPnY%3A6%3AAYm__bhQx3VOMRpS6fIh_hPIUTa01RwQ7AnQGQUCSA',
        'csrf_token' => 'XdOVXGN9vA3eHrUOwqxHlRTBHP5Ob0XI',
        'ds_user_id' => '23981181226'
    ],
    [
        // Account 3: @ronnyshahh (Active & Verified)
        'session_id' => '38251868688%3Azd1kdAqce9qcpj%3A9%3AAYlbVgJ2If3l-WBW6YeK4G0CF7ndfz5wh0b_oK0N6g',
        'csrf_token' => 'tRDIrHkgzfiZ1mc9Mr0ncJYEz7zLmPig',
        'ds_user_id' => '38251868688'
    ]
]);

// Proxy configuration (Optional, useful if your hosting server IP gets restricted by Instagram)
define('USE_PROXY', false);
define('PROXY_URL', ''); // e.g. 'http://username:password@ip:port'

// Timeouts
define('CURL_TIMEOUT', 15);
