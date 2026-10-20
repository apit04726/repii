<?php
/**
 * InstaGo - Smart Media Gateway & 5-Second Auto Downloader
 * 100% Fully Responsive (Mobile, Tablet, Desktop)
 * Multi-Language Supported (English, ગુજરાતી, हिन्दी)
 * Solves Vercel 4.5MB Serverless Lambda Limit & Eliminates download.txt errors
 */

require_once __DIR__ . '/config.php';

ini_set('display_errors', 0);
error_reporting(0);

$raw_url = $_GET['url'] ?? '';
$type = $_GET['type'] ?? 'video';
$custom_name = $_GET['name'] ?? '';
$raw_thumb = $_GET['thumb'] ?? '';
$initial_lang = $_GET['lang'] ?? 'gu';

function decode_media_url($raw) {
    if (empty($raw)) return '';
    
    // Direct URL check
    if (filter_var($raw, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $raw)) {
        return $raw;
    }
    
    // Robust base64 handling: replace spaces back to + and handle URL-safe characters
    $normalized = str_replace([' ', '-'], ['+', '+'], $raw);
    $normalized = str_replace('_', '/', $normalized);
    $pad = strlen($normalized) % 4;
    if ($pad > 0) {
        $normalized .= str_repeat('=', 4 - $pad);
    }
    
    $decoded = @base64_decode($normalized, true);
    if ($decoded && preg_match('/^https?:\/\//i', $decoded)) {
        return $decoded;
    }
    
    // Handle double urlencoding
    $urldecoded = urldecode($raw);
    if ($urldecoded !== $raw) {
        return decode_media_url($urldecoded);
    }
    
    return '';
}

$url = decode_media_url($raw_url);
if (!empty($url)) {
    $url = str_replace(['u0026', '&amp;', '\\u0026'], '&', $url);
}
$thumb_url = !empty($raw_thumb) ? decode_media_url($raw_thumb) : '';

if (!$url || !preg_match('/^https?:\/\//i', $url)) {
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="gu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <title>Link Expired - InstaGo</title>
        <!-- Favicon & Touch Icons -->
        <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg?v=2">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png?v=2">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png?v=2">
        <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png?v=2">
        <link rel="shortcut icon" href="favicon.ico?v=2">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { background: #080c14; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
            .err-card { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; padding: 36px 28px; text-align: center; max-width: 440px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.5); backdrop-filter: blur(16px); }
            .err-icon { width: 64px; height: 64px; background: rgba(239, 68, 68, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #ef4444; }
            h2 { font-size: 22px; margin-bottom: 12px; font-weight: 700; }
            p { color: #94a3b8; font-size: 14px; line-height: 1.6; margin-bottom: 24px; }
            .btn-home { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #f09433, #dc2743); color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 12px; font-weight: 600; font-size: 15px; transition: 0.2s; }
            .btn-home:hover { transform: translateY(-2px); opacity: 0.95; }
        </style>
    </head>
    <body>
        <div class="err-card">
            <div class="err-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h2>લિંક મળી શકી નથી / Link Expired</h2>
            <p>મીડિયા લિંક ખોટી છે અથવા ઈન્સ્ટાગ્રામ સર્વર તરફથી એક્સપાયર થઈ ગઈ છે. કૃપા કરીને મુખ્ય સાઇટ પરથી નવી લિંક મેળવો.</p>
            <a href="index.php" class="btn-home">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                મુખ્ય પેજ પર પાછા જાઓ
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Strict SSRF Protection: Validate Host against authorized Instagram / Meta CDNs
$parsed_host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
$allowed_domains = [
    'cdninstagram.com',
    'fbcdn.net',
    'instagram.com',
    'facebook.com',
    'akamaihd.net',
    'threads.net',
    'unsplash.com'
];

$is_allowed = false;
foreach ($allowed_domains as $d) {
    if ($parsed_host === $d || str_ends_with($parsed_host, '.' . $d)) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    http_response_code(403);
    die("Error: Unauthorized media host");
}

$ext = ($type === 'audio') ? 'mp3' : (($type === 'image' || $type === 'dp') ? 'jpg' : 'mp4');
$mime = ($type === 'audio') ? 'audio/mpeg' : (($type === 'image' || $type === 'dp') ? 'image/jpeg' : 'video/mp4');

$clean_name = !empty($custom_name) 
    ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $custom_name) 
    : 'InstaGo_' . ucfirst($type) . '_' . date('Ymd_His');
$filename = $clean_name . '.' . $ext;

// If thumbnail is empty and it's an image or DP, media URL itself is the thumbnail
if (empty($thumb_url) && ($type === 'image' || $type === 'dp')) {
    $thumb_url = $url;
}

// 1. Preview mode (used for in-page DP avatar and thumbnail streaming with CORS)
$is_preview = isset($_GET['preview']) || isset($_GET['view']);
if ($is_preview) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.instagram.com/');
    $img_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && !empty($img_data)) {
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline');
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $img_data;
        exit;
    }
}

// 2. Direct Download Execution:
// Stream file with Content-Disposition: attachment so that mobile & desktop devices save the file directly
// to the Downloads/Gallery folder with 100% full audio/voice/music, instead of opening a muted browser player!
if (isset($_GET['direct']) && $_GET['direct'] == '1') {
    $is_vercel = !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL']);
    
    // Always stream directly with attachment headers so file saves to device gallery/downloads with full audio/song!
    $should_stream = true;
    if ($is_vercel && $type === 'video') {
        $ch_h = curl_init($url);
        curl_setopt($ch_h, CURLOPT_NOBODY, true);
        curl_setopt($ch_h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_h, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_h, CURLOPT_TIMEOUT, 3);
        curl_exec($ch_h);
        $cl = curl_getinfo($ch_h, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch_h);
        if ($cl > 4700000) {
            $should_stream = false;
        }
    }

    if ($should_stream) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1048576);
        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    header('Location: ' . $url);
    exit;
}


// 3. Render 5-Second Countdown Gateway Page (Smart, Professional, 100% Responsive for All Devices)
$direct_params = $_GET;
$direct_params['direct'] = '1';
$direct_download_url = 'download.php?' . http_build_query($direct_params);

// Multi-Language Support (EN, GU, HI)
$initial_lang = strtolower($_GET['lang'] ?? 'gu');
if (!in_array($initial_lang, ['en', 'gu', 'hi'])) {
    $initial_lang = 'gu';
}

$php_i18n = [
    'gu' => [
        'code' => 'GU',
        'cdn_badge' => 'અલ્ટ્રા-ફાસ્ટ CDN',
        'title' => 'ડાઉનલોડ તૈયાર થઈ રહ્યું છે',
        'subtitle' => 'તમારી ફાઇલ ૫ સેકન્ડમાં ઓટોમેટિક ડાઉનલોડ થશે',
        'sec_label' => 'સેકન્ડ',
        'spec_1080p' => '1080p અલ્ટ્રા HD',
        'spec_original' => 'ઓરિજિનલ HD',
        'spec_cdn' => 'ડાયરેક્ટ મેટા CDN',
        'spec_watermark' => 'વોટરમાર્ક વગર',
        'btn_download' => 'તરત ડાઉનલોડ કરો (Download Now)',
        'fallback_prefix' => 'જો ડાઉનલોડ ઓટોમેટિક શરૂ ન થાય, તો ',
        'fallback_link' => 'અહીં ક્લિક કરો',
        'return_btn' => 'મુખ્ય સાઇટ પર પાછા જાઓ',
        'trust1_title' => 'સુપરફાસ્ટ સ્પીડ',
        'trust1_desc' => 'અનલિમિટેડ બેન્ડવિડ્થ ડાયરેક્ટ સર્વર',
        'trust2_title' => '૧૦૦% સુરક્ષિત',
        'trust2_desc' => 'ડાયરેક્ટ SSL એન્ક્રિપ્ટેડ લિંક',
        'trust3_title' => 'ઓલ ડિવાઇસ સપોર્ટ',
        'trust3_desc' => 'Android, iPhone, Mac, Windows',
        'footer' => '© ' . date('Y') . ' InstaGo • હાઇ-સ્પીડ ઇન્સ્ટાગ્રામ મીડિયા ડાઉનલોડર • All Rights Reserved.',
        'status_init' => 'ઈન્સ્ટાગ્રામ CDN સાથે સુરક્ષિત કનેક્શન બની રહ્યું છે...'
    ],
    'en' => [
        'code' => 'EN',
        'cdn_badge' => 'Ultra-Fast CDN',
        'title' => 'Your Download is Ready',
        'subtitle' => 'Your file will automatically download in 5 seconds',
        'sec_label' => 'Seconds',
        'spec_1080p' => '1080p Ultra HD',
        'spec_original' => 'Original HD',
        'spec_cdn' => 'Direct Meta CDN',
        'spec_watermark' => 'Zero Watermark',
        'btn_download' => 'Download Now (Instant)',
        'fallback_prefix' => 'If download does not start automatically, ',
        'fallback_link' => 'click here',
        'return_btn' => 'Back to Main Site',
        'trust1_title' => 'Superfast Speed',
        'trust1_desc' => 'Unlimited Bandwidth Direct Server',
        'trust2_title' => '100% Safe & Secure',
        'trust2_desc' => 'Direct SSL Encrypted Link',
        'trust3_title' => 'All Device Support',
        'trust3_desc' => 'Android, iPhone, Mac, Windows',
        'footer' => '© ' . date('Y') . ' InstaGo • High-Speed Instagram Media Downloader • All Rights Reserved.',
        'status_init' => 'Connecting to secure Instagram CDN...'
    ],
    'hi' => [
        'code' => 'HI',
        'cdn_badge' => 'अल्ट्रा-फास्ट CDN',
        'title' => 'डाउनलोड तैयार हो रहा है',
        'subtitle' => 'आपकी फ़ाइल 5 सेकंड में स्वचालित रूप से डाउनलोड होगी',
        'sec_label' => 'सेकंड',
        'spec_1080p' => '1080p अल्ट्रा HD',
        'spec_original' => 'ओरिजिनल HD',
        'spec_cdn' => 'डायरेक्ट मेटा CDN',
        'spec_watermark' => 'वॉटरमार्क के बिना',
        'btn_download' => 'तुरंत डाउनलोड करें (Download Now)',
        'fallback_prefix' => 'यदि डाउनलोड अपने आप शुरू न हो, तो ',
        'fallback_link' => 'यहाँ क्लिक करें',
        'return_btn' => 'मुख्य साइट पर वापस जाएं',
        'trust1_title' => 'सुपरफ़ास्ट स्पीड',
        'trust1_desc' => 'असीमित बैंडविड्थ डायरेक्ट सर्वर',
        'trust2_title' => '100% सुरक्षित',
        'trust2_desc' => 'डायरेक्ट SSL एन्क्रिप्टेड लिंक',
        'trust3_title' => 'सभी डिवाइस सपोर्ट',
        'trust3_desc' => 'Android, iPhone, Mac, Windows',
        'footer' => '© ' . date('Y') . ' InstaGo • हाई-स्पीड इंस्टाग्राम मीडिया डाउनलोडर • सर्वाधिकार सुरक्षित।',
        'status_init' => 'इंस्टाग्राम CDN से सुरक्षित कनेक्शन बन रहा है...'
    ]
];
$L = $php_i18n[$initial_lang];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($initial_lang) ?>" class="dark-mode notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#080c14">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="google" content="notranslate">
    <title><?= htmlspecialchars($L['title']) ?> - InstaGo</title>
    
    <!-- Favicon & Touch Icons (Matching index.php) -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png?v=2">
    <link rel="shortcut icon" href="favicon.ico?v=2">

    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-base: #070a11;
            --card-bg: rgba(15, 23, 42, 0.72);
            --card-border: rgba(255, 255, 255, 0.08);
            --card-border-glow: rgba(225, 48, 108, 0.25);
            --ig-grad: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            --ig-cyan: #06b6d4;
            --ig-emerald: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            min-height: 100%;
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Ambient Animated Neon Background Orbs */
        .ambient-glow {
            position: fixed;
            pointer-events: none;
            border-radius: 50%;
            z-index: 0;
            filter: blur(100px);
            opacity: 0.45;
            transition: opacity 0.5s ease;
        }
        .orb-1 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, #e1306c 0%, rgba(225, 48, 108, 0) 70%);
            top: -120px;
            left: -100px;
        }
        .orb-2 {
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, #833ab4 0%, rgba(131, 58, 180, 0) 70%);
            bottom: -140px;
            right: -100px;
        }
        .orb-3 {
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, #f09433 0%, rgba(240, 148, 51, 0) 70%);
            top: 40%;
            right: 15%;
            opacity: 0.25;
        }

        /* Top Brand Navigation Header */
        .gateway-header {
            position: relative;
            z-index: 100;
            width: 100%;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 900px;
            margin: 0 auto 16px auto;
        }
        .brand-box {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: inherit;
        }
        .brand-logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--ig-grad);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(225, 48, 108, 0.4);
        }
        .brand-logo-icon svg {
            width: 20px;
            height: 20px;
            fill: #ffffff;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 40%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-right-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Language Switcher Dropdown (Matching index.php) */
        .lang-dropdown-wrapper {
            position: relative;
        }
        .lang-current-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .lang-current-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
        }
        .globe-icon {
            width: 14px;
            height: 14px;
        }
        .chevron-icon {
            width: 12px;
            height: 12px;
            transition: transform 0.25s ease;
        }
        .lang-dropdown-wrapper.open .chevron-icon {
            transform: rotate(180deg);
        }
        .lang-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 150px;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.7);
            padding: 6px;
            display: none;
            flex-direction: column;
            gap: 4px;
            backdrop-filter: blur(16px);
            z-index: 1000;
        }
        .lang-dropdown-wrapper.open .lang-menu {
            display: flex;
            animation: dropFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes dropFade {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .lang-option-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 8px 12px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: var(--text-main);
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .lang-option-btn:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        .lang-option-btn.active {
            background: rgba(225, 48, 108, 0.18);
            color: #f43f5e;
        }
        .lang-badge {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-dim);
        }
        .lang-option-btn.active .lang-badge {
            background: rgba(225, 48, 108, 0.25);
            color: #f43f5e;
        }

        .cdn-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: pulse-ring 1.8s infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.8; }
        }

        /* Main Gateway Card Layout */
        .gateway-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 640px;
            margin: 16px auto 30px auto;
            padding: 0 16px;
        }
        .gateway-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 36px 30px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .gateway-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--ig-grad);
        }

        /* Gateway Header Typography */
        .gateway-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff 30%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gateway-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        /* Circular Countdown Progress Ring */
        .timer-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .timer-svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        .timer-circle-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.08);
            stroke-width: 8;
        }
        .timer-circle-progress {
            fill: none;
            stroke: url(#timerGradient);
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 364;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear;
        }
        .timer-number-box {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .timer-number {
            font-size: 46px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            background: var(--ig-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .timer-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            margin-top: 4px;
        }

        /* Linear Smooth Progress Bar */
        .progress-bar-track {
            width: 100%;
            max-width: 380px;
            height: 6px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 99px;
            margin: 0 auto 16px auto;
            overflow: hidden;
            position: relative;
        }
        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: var(--ig-grad);
            border-radius: 99px;
            transition: width 1s linear;
            box-shadow: 0 0 12px rgba(225, 48, 108, 0.6);
        }

        /* Live Status Dynamic Ticker */
        .status-ticker {
            font-size: 13.5px;
            font-weight: 600;
            color: #38bdf8;
            min-height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 26px;
            transition: all 0.3s ease;
        }
        .status-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Media Card Preview Section */
        .media-preview-card {
            background: rgba(10, 15, 28, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-align: left;
            margin-bottom: 26px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .media-preview-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }
        .media-thumb-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            border-radius: 14px;
            overflow: hidden;
            background: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .media-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .media-thumb-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ec4899;
        }
        .media-type-pill {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .media-info {
            flex: 1;
            min-width: 0;
        }
        .media-filename {
            font-size: 14.5px;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 6px;
        }
        .media-specs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .spec-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
        }
        .spec-chip.highlight {
            background: rgba(225, 48, 108, 0.1);
            border-color: rgba(225, 48, 108, 0.25);
            color: #f43f5e;
        }

        /* Action Buttons Area */
        .action-area {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .btn-download-now {
            width: 100%;
            min-height: 56px;
            background: var(--ig-grad);
            border: none;
            border-radius: 16px;
            color: #ffffff;
            font-family: inherit;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 10px 28px rgba(225, 48, 108, 0.35);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .btn-download-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 36px rgba(225, 48, 108, 0.5);
            filter: brightness(1.05);
        }
        .btn-download-now:active {
            transform: translateY(0);
        }
        .btn-download-now.btn-completed {
            background: linear-gradient(135deg, #059669, #10b981);
            box-shadow: 0 10px 28px rgba(16, 185, 129, 0.4);
        }

        .fallback-text {
            font-size: 13px;
            color: var(--text-dim);
            line-height: 1.5;
        }
        .fallback-link {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px dashed rgba(56, 189, 248, 0.5);
            transition: color 0.2s ease;
        }
        .fallback-link:hover {
            color: #7dd3fc;
            border-bottom-style: solid;
        }

        .return-link-box {
            margin-top: 10px;
        }
        .btn-return {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.2s ease;
        }
        .btn-return:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        /* Features Trust Strip */
        .trust-strip {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto 20px auto;
            padding: 0 16px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .trust-item {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 14px 12px;
            text-align: center;
            backdrop-filter: blur(12px);
        }
        .trust-icon {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .trust-title {
            font-size: 12px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 2px;
        }
        .trust-desc {
            font-size: 10.5px;
            color: var(--text-dim);
        }

        /* Footer */
        .gateway-footer {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 18px 16px;
            font-size: 12px;
            color: var(--text-dim);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
        }

        /* Responsive Media Queries (Mobile & Tablet) */
        @media (max-width: 1024px) {
            .gateway-header {
                padding: 16px 20px;
                margin-bottom: 26px;
            }
            .gateway-wrapper {
                margin: 22px auto 32px auto;
                padding: 0 18px;
            }
        }

        @media (max-width: 768px) {
            .gateway-header {
                padding: 14px 18px;
                margin-bottom: 28px;
            }
            .gateway-wrapper {
                margin: 24px auto 30px auto;
                padding: 0 16px;
            }
        }

        @media (max-width: 640px) {
            .gateway-header {
                padding: 14px 16px;
                margin-bottom: 28px;
            }
            .gateway-wrapper {
                margin: 22px auto 28px auto;
                padding: 0 14px;
            }
            .brand-title {
                font-size: 18px;
            }
            .cdn-badge {
                padding: 4px 10px;
                font-size: 11px;
            }
            .gateway-card {
                padding: 26px 18px;
                border-radius: 22px;
            }
            .gateway-title {
                font-size: 21px;
            }
            .gateway-subtitle {
                font-size: 13px;
                margin-bottom: 20px;
            }
            .timer-container {
                width: 120px;
                height: 120px;
                margin-bottom: 16px;
            }
            .timer-number {
                font-size: 38px;
            }
            .media-preview-card {
                padding: 12px;
                gap: 12px;
                margin-bottom: 20px;
            }
            .media-thumb-wrapper {
                width: 66px;
                height: 66px;
                border-radius: 10px;
            }
            .media-filename {
                font-size: 13px;
            }
            .spec-chip {
                font-size: 10px;
                padding: 2px 6px;
            }
            .btn-download-now {
                min-height: 52px;
                font-size: 15px;
            }
            .trust-strip {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .trust-item {
                padding: 10px;
                display: flex;
                align-items: center;
                gap: 10px;
                text-align: left;
            }
            .trust-icon {
                font-size: 20px;
                margin-bottom: 0;
            }
        }

        @media (max-width: 420px) {
            .gateway-header {
                padding: 12px 14px;
                margin-bottom: 26px;
            }
            .gateway-wrapper {
                margin: 18px auto 24px auto;
                padding: 0 12px;
            }
        }

        /* Safe area support for iOS notches */
        @supports (padding: env(safe-area-inset-top)) {
            .gateway-header {
                padding-top: max(18px, env(safe-area-inset-top));
            }
            .gateway-footer {
                padding-bottom: max(18px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Neon Glows -->
    <div class="ambient-glow orb-1"></div>
    <div class="ambient-glow orb-2"></div>
    <div class="ambient-glow orb-3"></div>

    <!-- Header -->
    <header class="gateway-header">
        <a href="index.php?reset=1" class="brand-box">
            <div class="brand-logo-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </div>
            <span class="brand-title">InstaGo</span>
        </a>

        <div class="header-right-actions">
            <!-- Header Language Switcher Dropdown (Matching index.php) -->
            <div class="lang-dropdown-wrapper" id="headerLangDropdown">
                <button type="button" class="lang-current-btn notranslate" id="headerLangToggle" aria-expanded="false" aria-label="Select Language" translate="no">
                    <svg class="globe-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span class="current-lang-code notranslate" id="currentLangLabel" translate="no"><?= htmlspecialchars($L['code']) ?></span>
                    <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="lang-menu notranslate" id="headerLangMenu" translate="no">
                    <button type="button" class="lang-option-btn <?= $initial_lang === 'en' ? 'active' : '' ?>" data-lang="en">
                        <span class="lang-name">English</span>
                        <span class="lang-badge">EN</span>
                    </button>
                    <button type="button" class="lang-option-btn <?= $initial_lang === 'gu' ? 'active' : '' ?>" data-lang="gu">
                        <span class="lang-name">ગુજરાતી</span>
                        <span class="lang-badge">GU</span>
                    </button>
                    <button type="button" class="lang-option-btn <?= $initial_lang === 'hi' ? 'active' : '' ?>" data-lang="hi">
                        <span class="lang-name">हिन्दी</span>
                        <span class="lang-badge">HI</span>
                    </button>
                </div>
            </div>

            <div class="cdn-badge">
                <span class="pulse-dot"></span>
                <span id="cdnBadgeText"><?= htmlspecialchars($L['cdn_badge']) ?></span>
            </div>
        </div>
    </header>

    <!-- Main Gateway Box -->
    <main class="gateway-wrapper">
        <div class="gateway-card">
            
            <h1 id="pageTitle" class="gateway-title"><?= htmlspecialchars($L['title']) ?></h1>
            <p id="pageSubtitle" class="gateway-subtitle"><?= htmlspecialchars($L['subtitle']) ?></p>

            <!-- Circular SVG Countdown Ring -->
            <div class="timer-container">
                <svg class="timer-svg" viewBox="0 0 140 140">
                    <defs>
                        <linearGradient id="timerGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#f09433"/>
                            <stop offset="50%" stop-color="#dc2743"/>
                            <stop offset="100%" stop-color="#bc1888"/>
                        </linearGradient>
                    </defs>
                    <circle class="timer-circle-bg" cx="70" cy="70" r="58"></circle>
                    <circle id="timerRing" class="timer-circle-progress" cx="70" cy="70" r="58"></circle>
                </svg>
                <div class="timer-number-box">
                    <span id="timerNumber" class="timer-number notranslate" translate="no">5</span>
                    <span id="timerLabel" class="timer-label"><?= htmlspecialchars($L['sec_label']) ?></span>
                </div>
            </div>

            <!-- Smooth Linear Progress Bar -->
            <div class="progress-bar-track">
                <div id="progressBarFill" class="progress-bar-fill"></div>
            </div>

            <!-- Dynamic Live Status -->
            <div id="statusTicker" class="status-ticker">
                <span class="status-icon">⚡</span>
                <span id="statusText"><?= htmlspecialchars($L['status_init']) ?></span>
            </div>

            <!-- Media Preview Card -->
            <div class="media-preview-card">
                <div class="media-thumb-wrapper">
                    <?php if (!empty($thumb_url)): ?>
                        <img src="<?= htmlspecialchars($thumb_url) ?>" 
                             alt="Media Preview" 
                             class="media-thumb-img" 
                             referrerpolicy="no-referrer"
                             onerror="this.onerror=null; this.src='download.php?preview=1&url=<?= urlencode($raw_thumb ?: $raw_url) ?>';">
                    <?php else: ?>
                        <div class="media-thumb-placeholder">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        </div>
                    <?php endif; ?>
                    <span class="media-type-pill notranslate" translate="no"><?= htmlspecialchars(strtoupper($type)) ?></span>
                </div>
                <div class="media-info">
                    <div class="media-filename notranslate" translate="no" title="<?= htmlspecialchars($filename) ?>"><?= htmlspecialchars($filename) ?></div>
                    <div class="media-specs">
                        <span class="spec-chip highlight">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span id="specResText"><?= ($type === 'video') ? htmlspecialchars($L['spec_1080p']) : htmlspecialchars($L['spec_original']) ?></span>
                        </span>
                        <span class="spec-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            <span id="specCdnText"><?= htmlspecialchars($L['spec_cdn']) ?></span>
                        </span>
                        <span class="spec-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span id="specWatermarkText"><?= htmlspecialchars($L['spec_watermark']) ?></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Area -->
            <div class="action-area">
                <button type="button" id="downloadBtn" class="btn-download-now">
                    <svg id="btnIcon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span id="btnText"><?= htmlspecialchars($L['btn_download']) ?></span>
                </button>

                <p class="fallback-text">
                    <span id="fallbackPrefix"><?= htmlspecialchars($L['fallback_prefix']) ?></span>
                    <a id="manualTriggerLink" href="<?= htmlspecialchars($direct_download_url) ?>" class="fallback-link"><?= htmlspecialchars($L['fallback_link']) ?></a>
                </p>

                <div class="return-link-box">
                    <a href="index.php?reset=1" class="btn-return">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        <span id="returnBtnText"><?= htmlspecialchars($L['return_btn']) ?></span>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- Trust Badges Strip -->
    <section class="trust-strip">
        <div class="trust-item">
            <div class="trust-icon">⚡</div>
            <div id="trust1Title" class="trust-title"><?= htmlspecialchars($L['trust1_title']) ?></div>
            <div id="trust1Desc" class="trust-desc"><?= htmlspecialchars($L['trust1_desc']) ?></div>
        </div>
        <div class="trust-item">
            <div class="trust-icon">🛡️</div>
            <div id="trust2Title" class="trust-title"><?= htmlspecialchars($L['trust2_title']) ?></div>
            <div id="trust2Desc" class="trust-desc"><?= htmlspecialchars($L['trust2_desc']) ?></div>
        </div>
        <div class="trust-item">
            <div class="trust-icon">📱</div>
            <div id="trust3Title" class="trust-title"><?= htmlspecialchars($L['trust3_title']) ?></div>
            <div id="trust3Desc" class="trust-desc"><?= htmlspecialchars($L['trust3_desc']) ?></div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footerText" class="gateway-footer">
        <?= htmlspecialchars($L['footer']) ?>
    </footer>

    <!-- Smart Auto-Trigger & Multi-Language Translation Script -->
    <script>
        (function() {
            // Multi-Language Translation Dictionary (EN, GU, HI)
            const i18n = {
                gu: {
                    code: 'GU',
                    cdnBadge: 'અલ્ટ્રા-ફાસ્ટ CDN',
                    title: 'ડાઉનલોડ તૈયાર થઈ રહ્યું છે',
                    subtitle: 'તમારી ફાઇલ ૫ સેકન્ડમાં ઓટોમેટિક ડાઉનલોડ થશે',
                    secLabel: 'સેકન્ડ',
                    spec1080p: '1080p અલ્ટ્રા HD',
                    specOriginal: 'ઓરિજિનલ HD',
                    specCdn: 'ડાયરેક્ટ મેટા CDN',
                    specWatermark: 'વોટરમાર્ક વગર',
                    btnDownload: 'તરત ડાઉનલોડ કરો (Download Now)',
                    btnDone: 'ડાઉનલોડ શરૂ થઈ ગયું! (Saved)',
                    fallbackPrefix: 'જો ડાઉનલોડ ઓટોમેટિક શરૂ ન થાય, તો ',
                    fallbackLink: 'અહીં ક્લિક કરો',
                    returnBtn: 'મુખ્ય સાઇટ પર પાછા જાઓ',
                    trust1Title: 'સુપરફાસ્ટ સ્પીડ',
                    trust1Desc: 'અનલિમિટેડ બેન્ડવિડ્થ ડાયરેક્ટ સર્વર',
                    trust2Title: '૧૦૦% સુરક્ષિત',
                    trust2Desc: 'ડાયરેક્ટ SSL એન્ક્રિપ્ટેડ લિંક',
                    trust3Title: 'ઓલ ડિવાઇસ સપોર્ટ',
                    trust3Desc: 'Android, iPhone, Mac, Windows',
                    footer: '© ' + new Date().getFullYear() + ' InstaGo • હાઇ-સ્પીડ ઇન્સ્ટાગ્રામ મીડિયા ડાઉનલોડર • All Rights Reserved.',
                    statusSteps: {
                        5: { text: "ઈન્સ્ટાગ્રામ CDN સાથે સુરક્ષિત કનેક્શન બની રહ્યું છે...", icon: "⚡" },
                        4: { text: "ઓરિજિનલ 1080p સુપર HD સ્ટ્રીમ વેરિફાઇ થઈ રહી છે...", icon: "🎬" },
                        3: { text: "હાઇ-સ્પીડ ડાયરેક્ટ ડાઉનલોડ લિંક તૈયાર થઈ રહી છે...", icon: "🚀" },
                        2: { text: "સિક્યોર ડાઉનલોડ પેકેજ તૈયાર થઈ રહ્યું છે...", icon: "🔒" },
                        1: { text: "ફાઇલ તૈયાર છે! ડાઉનલોડ શરૂ થઈ રહ્યું છે...", icon: "✨" },
                        0: { text: "ડાઉનલોડ શરૂ થઈ ગયું છે! ગેલેરી અથવા ડાઉનલોડ્સ ચેક કરો. 🎉", icon: "🚀" }
                    }
                },
                en: {
                    code: 'EN',
                    cdnBadge: 'Ultra-Fast CDN',
                    title: 'Your Download is Ready',
                    subtitle: 'Your file will automatically download in 5 seconds',
                    secLabel: 'Seconds',
                    spec1080p: '1080p Ultra HD',
                    specOriginal: 'Original HD',
                    specCdn: 'Direct Meta CDN',
                    specWatermark: 'Zero Watermark',
                    btnDownload: 'Download Now (Instant)',
                    btnDone: 'Download Started! (Saved)',
                    fallbackPrefix: 'If download does not start automatically, ',
                    fallbackLink: 'click here',
                    returnBtn: 'Back to Main Site',
                    trust1Title: 'Superfast Speed',
                    trust1Desc: 'Unlimited Bandwidth Direct Server',
                    trust2Title: '100% Safe & Secure',
                    trust2Desc: 'Direct SSL Encrypted Link',
                    trust3Title: 'All Device Support',
                    trust3Desc: 'Android, iPhone, Mac, Windows',
                    footer: '© ' + new Date().getFullYear() + ' InstaGo • High-Speed Instagram Media Downloader • All Rights Reserved.',
                    statusSteps: {
                        5: { text: "Connecting to secure Instagram CDN...", icon: "⚡" },
                        4: { text: "Verifying original 1080p Ultra HD stream...", icon: "🎬" },
                        3: { text: "Preparing high-speed direct download link...", icon: "🚀" },
                        2: { text: "Generating secure download token...", icon: "🔒" },
                        1: { text: "File is ready! Launching download...", icon: "✨" },
                        0: { text: "Download started! Check your downloads or gallery. 🎉", icon: "🚀" }
                    }
                },
                hi: {
                    code: 'HI',
                    cdnBadge: 'अल्ट्रा-फास्ट CDN',
                    title: 'डाउनलोड तैयार हो रहा है',
                    subtitle: 'आपकी फ़ाइल 5 सेकंड में स्वचालित रूप से डाउनलोड होगी',
                    secLabel: 'सेकंड',
                    spec1080p: '1080p अल्ट्रा HD',
                    specOriginal: 'ओरिजिनल HD',
                    specCdn: 'डायरेक्ट मेटा CDN',
                    specWatermark: 'वॉटरमार्क के बिना',
                    btnDownload: 'तुरंत डाउनलोड करें (Download Now)',
                    btnDone: 'डाउनलोड शुरू हो गया! (Saved)',
                    fallbackPrefix: 'यदि डाउनलोड अपने आप शुरू न हो, तो ',
                    fallbackLink: 'यहाँ क्लिक करें',
                    returnBtn: 'मुख्य साइट पर वापस जाएं',
                    trust1Title: 'सुपरफ़ास्ट स्पीड',
                    trust1Desc: 'असीमित बैंडविड्थ डायरेक्ट सर्वर',
                    trust2Title: '100% सुरक्षित',
                    trust2Desc: 'डायरेक्ट SSL एन्क्रिप्टेड लिंक',
                    trust3Title: 'सभी डिवाइस सपोर्ट',
                    trust3Desc: 'Android, iPhone, Mac, Windows',
                    footer: '© ' + new Date().getFullYear() + ' InstaGo • हाई-स्पीड इंस्टाग्राम मीडिया डाउनलोडर • सर्वाधिकार सुरक्षित।',
                    statusSteps: {
                        5: { text: "इंस्टाग्राम CDN से सुरक्षित कनेक्शन बन रहा है...", icon: "⚡" },
                        4: { text: "ओरिजिनल 1080p सुपर HD स्ट्रीम जांची जा रही है...", icon: "🎬" },
                        3: { text: "हाई-स्पीड डायरेक्ट डाउनलोड लिंक तैयार हो रहा है...", icon: "🚀" },
                        2: { text: "सुरक्षित डाउनलोड टोकन बनाया जा रहा है...", icon: "🔒" },
                        1: { text: "फ़ाइल तैयार है! डाउनलोड शुरू हो रहा है...", icon: "✨" },
                        0: { text: "डाउनलोड शुरू हो गया है! अपनी गैलरी या डाउनलोड्स देखें। 🎉", icon: "🚀" }
                    }
                }
            };

            // Detect current language from URL, localStorage, or server initial
            const urlParams = new URLSearchParams(window.location.search);
            let currentLang = urlParams.get('lang') || localStorage.getItem('instago_app_lang') || <?= json_encode($initial_lang) ?>;
            if (!i18n[currentLang]) currentLang = 'gu';

            const headerLangDropdown = document.getElementById('headerLangDropdown');
            const headerLangToggle = document.getElementById('headerLangToggle');
            const currentLangLabel = document.getElementById('currentLangLabel');
            const langOptionBtns = document.querySelectorAll('.lang-option-btn');

            // Apply Language to all elements dynamically
            function applyLanguage(lang) {
                if (!i18n[lang]) return;
                currentLang = lang;
                const t = i18n[lang];

                try {
                    localStorage.setItem('instago_app_lang', lang);
                    localStorage.setItem('instago_guide_lang', lang);
                } catch(e) {}

                document.documentElement.lang = lang;
                if (currentLangLabel) currentLangLabel.textContent = t.code;

                langOptionBtns.forEach(btn => {
                    if (btn.getAttribute('data-lang') === lang) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

                // Update text elements
                const el = (id) => document.getElementById(id);
                if (el('cdnBadgeText')) el('cdnBadgeText').textContent = t.cdnBadge;
                if (el('pageTitle')) el('pageTitle').textContent = t.title;
                if (el('pageSubtitle')) el('pageSubtitle').textContent = t.subtitle;
                if (el('timerLabel') && !downloadTriggered) el('timerLabel').textContent = t.secLabel;
                if (el('specResText')) el('specResText').textContent = (mediaType === 'video' ? t.spec1080p : t.specOriginal);
                if (el('specCdnText')) el('specCdnText').textContent = t.specCdn;
                if (el('specWatermarkText')) el('specWatermarkText').textContent = t.specWatermark;
                if (el('btnText')) el('btnText').textContent = downloadTriggered ? t.btnDone : t.btnDownload;
                if (el('fallbackPrefix')) el('fallbackPrefix').textContent = t.fallbackPrefix;
                if (el('manualTriggerLink')) el('manualTriggerLink').textContent = t.fallbackLink;
                if (el('returnBtnText')) el('returnBtnText').textContent = t.returnBtn;
                if (el('trust1Title')) el('trust1Title').textContent = t.trust1Title;
                if (el('trust1Desc')) el('trust1Desc').textContent = t.trust1Desc;
                if (el('trust2Title')) el('trust2Title').textContent = t.trust2Title;
                if (el('trust2Desc')) el('trust2Desc').textContent = t.trust2Desc;
                if (el('trust3Title')) el('trust3Title').textContent = t.trust3Title;
                if (el('trust3Desc')) el('trust3Desc').textContent = t.trust3Desc;
                if (el('footerText')) el('footerText').textContent = t.footer;

                // Update status step
                updateStatusStep(lastSec);
            }

            // Language Dropdown Toggle Handlers
            if (headerLangToggle && headerLangDropdown) {
                headerLangToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = headerLangDropdown.classList.contains('open');
                    headerLangDropdown.classList.toggle('open');
                    headerLangToggle.setAttribute('aria-expanded', !isOpen);
                });

                document.addEventListener('click', (e) => {
                    if (!headerLangDropdown.contains(e.target)) {
                        headerLangDropdown.classList.remove('open');
                        headerLangToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            langOptionBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const chosen = btn.getAttribute('data-lang');
                    applyLanguage(chosen);
                    if (headerLangDropdown) {
                        headerLangDropdown.classList.remove('open');
                        if (headerLangToggle) headerLangToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            });

            // Media & Download Parameters
            const mediaType = <?= json_encode($type) ?>;
            const directUrl = <?= json_encode($direct_download_url) ?>;
            const fileName = <?= json_encode($filename) ?>;
            const rawMediaUrl = <?= json_encode($url) ?>;

            // DOM Elements
            const timerRing = document.getElementById('timerRing');
            const timerNumber = document.getElementById('timerNumber');
            const timerLabel = document.getElementById('timerLabel');
            const progressBarFill = document.getElementById('progressBarFill');
            const statusTicker = document.getElementById('statusTicker');
            const statusText = document.getElementById('statusText');
            const downloadBtn = document.getElementById('downloadBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');
            const manualTriggerLink = document.getElementById('manualTriggerLink');

            // Circumference of r=58 circle is ~364.42px
            const circumference = 2 * Math.PI * 58;
            timerRing.style.strokeDasharray = `${circumference} ${circumference}`;
            timerRing.style.strokeDashoffset = '0';
            progressBarFill.style.width = '0%';

            // Countdown Setup (Strictly 5 -> 4 -> 3 -> 2 -> 1 -> Done)
            const durationMs = 5000;
            let downloadTriggered = false;
            let lastSec = 5;
            const startTime = performance.now();

            function updateStatusStep(sec) {
                const t = i18n[currentLang] || i18n['gu'];
                if (t && t.statusSteps && t.statusSteps[sec]) {
                    statusText.textContent = t.statusSteps[sec].text;
                    const iconSpan = statusTicker.querySelector('.status-icon');
                    if (iconSpan) iconSpan.textContent = t.statusSteps[sec].icon;
                }
            }

            function updateSecondDisplay(sec) {
                timerNumber.textContent = sec;
                timerNumber.style.transform = 'scale(1.15)';
                setTimeout(() => { timerNumber.style.transform = 'scale(1)'; }, 140);
                updateStatusStep(sec);
            }

            // High-Speed Direct Downloader with Single Guaranteed Execution (No 3rd Tab)
            let downloadFired = false;
            let preloadedBlobUrl = null;
            let preloadPromise = null;

            // Start preloading the video/image in the background immediately as Tab 2 loads
            if (rawMediaUrl) {
                preloadPromise = fetch(rawMediaUrl, { mode: 'cors' })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.blob();
                    })
                    .then(blob => {
                        preloadedBlobUrl = URL.createObjectURL(blob);
                        return preloadedBlobUrl;
                    })
                    .catch(err => {
                        console.warn('Preload via blob failed:', err);
                        return null;
                    });
            }

            function triggerBlobDownload(blobUrl) {
                try {
                    const dlLink = document.createElement('a');
                    dlLink.href = blobUrl;
                    dlLink.setAttribute('download', fileName);
                    dlLink.style.display = 'none';
                    document.body.appendChild(dlLink);
                    dlLink.click();
                    setTimeout(() => { dlLink.remove(); }, 3000);
                    return true;
                } catch(e) {
                    return false;
                }
            }

            async function executeFileDownload() {
                if (downloadFired) return;
                downloadFired = true;

                // 1. If already preloaded and ready, trigger instant blob download (0 third tabs)
                if (preloadedBlobUrl) {
                    if (triggerBlobDownload(preloadedBlobUrl)) return;
                }

                // 2. If preload is still in progress, wait up to 3.5s for it to finish
                if (preloadPromise) {
                    try {
                        const blobUrl = await Promise.race([
                            preloadPromise,
                            new Promise((_, reject) => setTimeout(() => reject('timeout'), 3500))
                        ]);
                        if (blobUrl && triggerBlobDownload(blobUrl)) return;
                    } catch(e) {
                        // Preload timed out or failed
                    }
                }

                // 3. Fallback: navigate in the SAME tab so it NEVER opens a 3rd tab
                window.location.href = directUrl;
            }

            function triggerAutoDownload() {
                if (downloadTriggered) return;
                downloadTriggered = true;

                const t = i18n[currentLang] || i18n['gu'];

                // 1. Update UI to Completed State
                timerNumber.innerHTML = `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
                timerLabel.textContent = "Done";
                timerLabel.style.color = "#10b981";
                statusText.textContent = t.statusSteps[0].text;
                statusTicker.style.color = "#34d399";
                btnText.textContent = t.btnDone;
                downloadBtn.classList.add('btn-completed');
                btnIcon.innerHTML = `<polyline points="20 6 9 17 4 12"></polyline>`;

                timerRing.style.strokeDashoffset = circumference;
                progressBarFill.style.width = '100%';

                // 2. Trigger browser download exactly once
                executeFileDownload();

                // 3. Smart 9-Second Auto-Return to Homepage + Searchbar Blank Logic (Same Tab)
                try {
                    sessionStorage.setItem('instago_reset_search', '1');
                    localStorage.setItem('instago_reset_search_ts', Date.now().toString());
                } catch(e) {}

                let redirectSec = 9;
                const returnBtnText = document.getElementById('returnBtnText');
                const updateRedirectMsg = (sec) => {
                    const redirectMessages = {
                        gu: `ડાઉનલોડ શરૂ થઈ ગયું! ${sec} સેકન્ડમાં હોમપેજ પર પાછા જઈ રહ્યા છીએ... 🎉`,
                        en: `Download started! Returning to homepage in ${sec}s... 🎉`,
                        hi: `डाउनलोड शुरू हो गया! ${sec} सेकंड में होमपेज पर जा रहे हैं... 🎉`
                    };
                    const msg = redirectMessages[currentLang] || redirectMessages['en'];
                    if (statusText) statusText.textContent = msg;
                    if (returnBtnText) {
                        const baseText = (t && t.returnBtn) ? t.returnBtn : 'Back to Main Site';
                        returnBtnText.textContent = `${baseText} (${sec}s)`;
                    }
                };

                updateRedirectMsg(redirectSec);

                const redirectInterval = setInterval(() => {
                    redirectSec--;
                    if (redirectSec > 0) {
                        updateRedirectMsg(redirectSec);
                    } else {
                        clearInterval(redirectInterval);
                        returnToSameHomepage();
                    }
                }, 1000);
            }

            function returnToSameHomepage(e) {
                if (e && typeof e.preventDefault === 'function') e.preventDefault();
                try {
                    sessionStorage.setItem('instago_reset_search', '1');
                    localStorage.setItem('instago_reset_search_ts', Date.now().toString());
                } catch(err) {}

                if (window.opener && !window.opener.closed) {
                    try {
                        window.opener.location.href = 'index.php?reset=1';
                        window.opener.focus();
                        window.close();
                        return;
                    } catch(err) {
                        try {
                            window.opener.focus();
                            window.close();
                            return;
                        } catch(err2) {}
                    }
                }

                window.close();
                setTimeout(() => {
                    window.location.href = 'index.php?reset=1';
                }, 200);
            }

            // High Precision 60fps Animation Loop
            function tick(now) {
                if (downloadTriggered) return;

                const elapsed = Math.max(0, now - startTime);
                const progressRatio = Math.min(1, elapsed / durationMs);

                // Silky smooth continuous visual bar & ring
                timerRing.style.strokeDashoffset = circumference * progressRatio;
                progressBarFill.style.width = (progressRatio * 100) + '%';

                // Strictly monotonically decreasing seconds: 5 -> 4 -> 3 -> 2 -> 1 -> 0
                const remainingSec = Math.max(0, Math.ceil((durationMs - elapsed) / 1000));

                if (remainingSec !== lastSec) {
                    lastSec = remainingSec;
                    updateSecondDisplay(lastSec);
                }

                if (elapsed >= durationMs || remainingSec <= 0) {
                    triggerAutoDownload();
                } else {
                    requestAnimationFrame(tick);
                }
            }

            // Sync initial language if different from server
            if (currentLang !== <?= json_encode($initial_lang) ?>) {
                applyLanguage(currentLang);
            }

            // Start countdown
            updateSecondDisplay(5);
            requestAnimationFrame(tick);

            // Manual Download Button Handlers
            downloadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                downloadFired = false;
                executeFileDownload();
            });

            manualTriggerLink.addEventListener('click', function(e) {
                e.preventDefault();
                downloadFired = false;
                executeFileDownload();
            });

            const returnBtn = document.querySelector('.btn-return');
            if (returnBtn) {
                returnBtn.addEventListener('click', returnToSameHomepage);
            }

            const brandBox = document.querySelector('.brand-box');
            if (brandBox) {
                brandBox.addEventListener('click', returnToSameHomepage);
            }

        })();
    </script>
</body>
</html>
