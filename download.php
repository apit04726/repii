<?php
/**
 * InstaGo - Secure Media Download Proxy
 * Forces file download on all devices (iOS Safari, Android Chrome, Windows, Mac)
 * Bypasses Instagram CDN hotlink & CORS restrictions
 */

require_once __DIR__ . '/config.php';

// Disable error display for clean stream output
ini_set('display_errors', 0);
error_reporting(0);

// Set execution time for larger video downloads
set_time_limit(300);

$raw_url = $_GET['url'] ?? '';
$type = $_GET['type'] ?? 'video';
$custom_name = $_GET['name'] ?? '';

if (empty($raw_url)) {
    http_response_code(400);
    die("Error: Missing media URL");
}

// Decode URL if base64 encoded
if (base64_encode(base64_decode($raw_url, true)) === $raw_url) {
    $url = base64_decode($raw_url);
} else {
    $url = filter_var($raw_url, FILTER_VALIDATE_URL) ? $raw_url : '';
}

if (!$url || !preg_match('/^https?:\/\//i', $url)) {
    http_response_code(400);
    die("Error: Invalid media URL");
}

// Determine file extension and MIME type
$ext = ($type === 'audio') ? 'mp3' : (($type === 'image' || $type === 'dp') ? 'jpg' : 'mp4');
$mime = ($type === 'audio') ? 'audio/mpeg' : (($type === 'image' || $type === 'dp') ? 'image/jpeg' : 'video/mp4');

$filename = !empty($custom_name) 
    ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $custom_name) . '.' . $ext 
    : 'InstaGo_' . ucfirst($type) . '_' . date('Ymd_His') . '.' . $ext;

// Open cURL to fetch from Instagram CDN
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_REFERER, 'https://www.instagram.com/');

// Headers callback to forward content length
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header_line) use (&$content_length) {
    if (stripos($header_line, 'content-length:') === 0) {
        $parts = explode(':', $header_line, 2);
        if (isset($parts[1])) {
            $content_length = trim($parts[1]);
        }
    }
    return strlen($header_line);
});

// Set download headers
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Stream directly to client
curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code >= 400) {
    // If CDN failed directly, redirect user to the source URL as last resort
    header_remove('Content-Disposition');
    header("Location: " . $url);
    exit;
}
exit;
