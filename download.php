<?php
/**
 * InstaGo - High-Speed Media Download & Preview Delivery
 * Direct fast CDN redirect & preview streaming for all devices (Android, iOS Safari, Windows, Mac)
 * Fixes Vercel 4.5MB serverless limit and permanently eliminates download.txt errors
 */

require_once __DIR__ . '/config.php';

ini_set('display_errors', 0);
error_reporting(0);

$raw_url = $_GET['url'] ?? '';
$type = $_GET['type'] ?? 'video';
$custom_name = $_GET['name'] ?? '';

if (empty($raw_url)) {
    http_response_code(400);
    die("Error: Missing media URL");
}

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

if (!$url || !preg_match('/^https?:\/\//i', $url)) {
    http_response_code(400);
    die("Error: Invalid media URL");
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

$filename = !empty($custom_name) 
    ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $custom_name) . '.' . $ext 
    : 'InstaGo_' . ucfirst($type) . '_' . date('Ymd_His') . '.' . $ext;

$is_preview = isset($_GET['preview']) || isset($_GET['view']);

// For previews (e.g. DP avatar or thumbnail inside page UI), stream image with CORS headers
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
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $img_data;
        exit;
    }
}

// For Full Downloads (MP4 videos and HD images):
// Direct HTTP 302 Redirect to high-speed Instagram CDN
// This eliminates Vercel's 4.5MB Lambda crash, fixes "download.txt" errors, and delivers 100% full download speed directly to the user's gallery!
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Location: " . $url, true, 302);
exit;
