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
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Optional RapidAPI / Third-party API Key for ultra-high stability (Free tiers available on RapidAPI)
// Leave empty to use direct built-in scrapers
define('RAPIDAPI_KEY', '');
define('RAPIDAPI_HOST', 'instagram-downloader-download-instagram-videos-stories1.p.rapidapi.com');

// Primary Instagram Session Cookie (sessionid)
define('INSTAGRAM_SESSION_ID', '38251868688%3AE9meNLI2lgLBU3%3A5%3AAYkYRmRjvsjSEZ11wvtpwHNo5DU65mU0Ns9ZJ2ny9w');
define('INSTAGRAM_CSRF_TOKEN', 'X5kJG1udcL5Z1qnr3VimY9cMG202eQBy');
define('INSTAGRAM_DS_USER_ID', '38251868688');

// Session Pool (indown.io multi-account rotation setup)
// Automatically fails over across active accounts
define('INSTAGRAM_SESSION_POOL', [
    [
        'session_id' => '38251868688%3AE9meNLI2lgLBU3%3A5%3AAYkYRmRjvsjSEZ11wvtpwHNo5DU65mU0Ns9ZJ2ny9w',
        'csrf_token' => 'X5kJG1udcL5Z1qnr3VimY9cMG202eQBy',
        'ds_user_id' => '38251868688'
    ],
    [
        'session_id' => '22996724939%3AK5Zi6yeqjWfZ1R%3A19%3AAYnlWO1cKV9jLNltGiJinF4pGJRiZIbM2DmDtzd2iQ',
        'csrf_token' => '4ukCpjDx9plIbLX5Kk5nDvRVIdy5bVqd',
        'ds_user_id' => '22996724939'
    ]
]);

// Proxy configuration (Optional, useful if your hosting server IP gets restricted by Instagram)
define('USE_PROXY', false);
define('PROXY_URL', ''); // e.g. 'http://username:password@ip:port'

// Timeouts
define('CURL_TIMEOUT', 15);
