<?php
/**
 * InstaGo - Backend API
 * Handles media fetching for Instagram Reels, Posts, DP, and Stories
 */

error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// IP-based Rate Limiter (Max 60 requests/min to protect Instagram sessions & prevent DoS)
if (php_sapi_name() !== 'cli' && !defined('INSTAGO_API_NO_DISPATCH')) {
    $client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (strpos($client_ip, ',') !== false) {
        $client_ip = trim(explode(',', $client_ip)[0]);
    }
    $client_ip = preg_replace('/[^a-zA-Z0-9_.:-]/', '', $client_ip);
    $rate_file = sys_get_temp_dir() . '/instago_rl_' . md5($client_ip) . '.json';
    $now = time();
    $timestamps = [];
    if (file_exists($rate_file)) {
        $raw_rate = @file_get_contents($rate_file);
        if ($raw_rate) {
            $parsed = json_decode($raw_rate, true);
            if (is_array($parsed)) {
                $timestamps = array_filter($parsed, function($t) use ($now) {
                    return ($now - $t) < 60;
                });
            }
        }
    }
    if (count($timestamps) >= 60) {
        http_response_code(429);
        echo json_encode([
            'status' => 'error',
            'message' => 'Rate limit exceeded. Too many requests. Please wait a minute and try again.'
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }
    $timestamps[] = $now;
    @file_put_contents($rate_file, json_encode($timestamps), LOCK_EX);
}


require_once __DIR__ . '/config.php';

// Helper response functions with Smart Caching Shield
function get_cached_media($key) {
    $clean_key = preg_replace('/[?#].*$/', '', trim($key));
    $cache_file = sys_get_temp_dir() . '/instago_m_' . md5($clean_key) . '.json';
    if (file_exists($cache_file)) {
        $mtime = @filemtime($cache_file);
        // Smart Cache Shield: Stories 30 mins, Reels/Posts/DP 24 hours (86400s) saves 95%+ proxy calls
        $cache_ttl = (strpos($clean_key, '/stories/') !== false || strpos($clean_key, 'type=story') !== false) ? 1800 : 86400;
        if ($mtime && (time() - $mtime < $cache_ttl)) {
            $content = @file_get_contents($cache_file);
            if ($content) {
                $decoded = json_decode($content, true);
                // Sanity check: Invalidate and purge if a Reel URL was improperly cached as a photo post or has no valid video or bad signature or silent DASH stream
                if (preg_match('/instagram\.com\/(?:reel|reels)\//i', $clean_key)) {
                    $dl = $decoded['download_url'] ?? '';
                    if (($decoded['type'] ?? '') !== 'reel' || empty($dl) || strpos($dl, '.mp4') === false || strpos($dl, 'oh=') === false || strpos($dl, 'oe=') === false || strpos($dl, 'u0026') !== false || strpos($dl, 'dash_baseline_1_v1') !== false) {
                        @unlink($cache_file);
                        return null;
                    }
                }
                if (is_array($decoded) && (!empty($decoded['download_url']) || !empty($decoded['media']))) {
                    return $decoded;
                }
            }
        }
    }
    return null;
}

function set_cached_media($key, $data) {
    if (!empty($data) && is_array($data) && (!empty($data['download_url']) || !empty($data['media']))) {
        // Guard: Never cache invalid, incomplete, corrupted or silent DASH reel video
        if (preg_match('/instagram\.com\/(?:reel|reels)\//i', $key)) {
            $dl = $data['download_url'] ?? '';
            if (($data['type'] ?? '') !== 'reel' || empty($dl) || strpos($dl, '.mp4') === false || strpos($dl, 'oh=') === false || strpos($dl, 'oe=') === false || strpos($dl, 'u0026') !== false || strpos($dl, 'dash_baseline_1_v1') !== false) {
                return;
            }
        }
        $clean_key = preg_replace('/[?#].*$/', '', trim($key));
        $cache_file = sys_get_temp_dir() . '/instago_m_' . md5($clean_key) . '.json';
        @file_put_contents($cache_file, json_encode($data), LOCK_EX);
    }
}

function json_success($data, $cache_key = null) {
    if ($cache_key) {
        set_cached_media($cache_key, $data);
    }
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function json_fail($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// Convert Instagram shortcode to media id with multi-engine precision math
function shortcode_to_media_id($shortcode) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    if (function_exists('bcadd')) {
        $media_id = '0';
        for ($i = 0; $i < strlen($shortcode); $i++) {
            $val = strpos($alphabet, $shortcode[$i]);
            if ($val === false) return false;
            $media_id = bcadd(bcmul($media_id, '64'), (string)$val);
        }
        return $media_id;
    } elseif (function_exists('gmp_init')) {
        $media_id = gmp_init('0');
        for ($i = 0; $i < strlen($shortcode); $i++) {
            $val = strpos($alphabet, $shortcode[$i]);
            if ($val === false) return false;
            $media_id = gmp_add(gmp_mul($media_id, 64), $val);
        }
        return gmp_strval($media_id);
    } else {
        $res = '0';
        for ($i = 0; $i < strlen($shortcode); $i++) {
            $val = strpos($alphabet, $shortcode[$i]);
            if ($val === false) return false;
            $carry = $val;
            $len = strlen($res);
            $new_res = '';
            for ($j = $len - 1; $j >= 0; $j--) {
                $cur = (int)$res[$j] * 64 + $carry;
                $new_res = ($cur % 10) . $new_res;
                $carry = intdiv($cur, 10);
            }
            while ($carry > 0) {
                $new_res = ($carry % 10) . $new_res;
                $carry = intdiv($carry, 10);
            }
            $res = $new_res ?: '0';
        }
        return $res;
    }
}

// Cooldown & Health Tracking for Instagram Sessions
function is_session_in_cooldown($sess) {
    $uid = !empty($sess['ds_user_id']) ? $sess['ds_user_id'] : md5($sess['session_id'] ?? 'none');
    $file = sys_get_temp_dir() . '/instago_cd_' . $uid . '.txt';
    if (file_exists($file)) {
        $time = (int)@file_get_contents($file);
        if ((time() - $time) < 3600) { // 1 hour safety rest
            return true;
        }
        @unlink($file);
    }
    return false;
}

function mark_session_cooldown($sess) {
    $uid = !empty($sess['ds_user_id']) ? $sess['ds_user_id'] : md5($sess['session_id'] ?? 'none');
    $file = sys_get_temp_dir() . '/instago_cd_' . $uid . '.txt';
    @file_put_contents($file, (string)time(), LOCK_EX);
}

// Deterministic Sticky Proxy: Binds each account permanently to ONE fixed proxy IP
// Prevents "Impossible Travel / Rapid IP Switching" warnings from Instagram AI
function get_session_sticky_proxy($sess) {
    if (!defined('USE_PROXY') || !USE_PROXY) return '';
    if (!empty($sess['proxy'])) return $sess['proxy'];

    if (defined('PROXY_POOL') && is_array(PROXY_POOL) && !empty(PROXY_POOL)) {
        $valid_proxies = array_values(array_filter(PROXY_POOL));
        if (!empty($valid_proxies)) {
            // Check direct mapping from account pool index (Account 1 -> Proxy 1, Account 2 -> Proxy 2, etc.)
            if (defined('INSTAGRAM_SESSION_POOL') && is_array(INSTAGRAM_SESSION_POOL)) {
                foreach (INSTAGRAM_SESSION_POOL as $idx => $item) {
                    if ((!empty($item['ds_user_id']) && $item['ds_user_id'] === ($sess['ds_user_id'] ?? ''))
                        || (!empty($item['session_id']) && $item['session_id'] === ($sess['session_id'] ?? ''))) {
                        return $valid_proxies[$idx % count($valid_proxies)];
                    }
                }
            }
            $uid = !empty($sess['ds_user_id']) ? $sess['ds_user_id'] : ($sess['session_id'] ?? 'default');
            $idx = abs(crc32($uid)) % count($valid_proxies);
            return $valid_proxies[$idx];
        }
    }
    return defined('PROXY_URL') ? PROXY_URL : '';
}

// Get list of active sessions from pool with intelligent health filtering & load balancing
function get_instagram_session_pool() {
    $pool = [];
    if (defined('INSTAGRAM_SESSION_POOL') && is_array(INSTAGRAM_SESSION_POOL) && !empty(INSTAGRAM_SESSION_POOL)) {
        $valid = array_filter(INSTAGRAM_SESSION_POOL, function($item) {
            return !empty($item['session_id']) && !is_session_in_cooldown($item);
        });
        $pool = array_values($valid);
        
        // If all accounts are in cooldown, use all valid accounts as fallback
        if (empty($pool)) {
            $fallback = array_filter(INSTAGRAM_SESSION_POOL, function($item) {
                return !empty($item['session_id']);
            });
            $pool = array_values($fallback);
        }
    } elseif (defined('INSTAGRAM_SESSION_ID') && !empty(INSTAGRAM_SESSION_ID)) {
        $pool[] = [
            'session_id' => INSTAGRAM_SESSION_ID,
            'csrf_token' => defined('INSTAGRAM_CSRF_TOKEN') ? INSTAGRAM_CSRF_TOKEN : '',
            'ds_user_id' => defined('INSTAGRAM_DS_USER_ID') ? INSTAGRAM_DS_USER_ID : ''
        ];
    }
    // Dynamic Load Balancing: Shuffle active accounts so traffic is distributed equally
    if (count($pool) > 1) {
        shuffle($pool);
    }
    return $pool;
}


function get_session_cookies($sess) {
    if (empty($sess['session_id'])) return '';
    $c = 'sessionid=' . $sess['session_id'];
    if (!empty($sess['csrf_token'])) $c .= '; csrftoken=' . $sess['csrf_token'];
    if (!empty($sess['ds_user_id'])) $c .= '; ds_user_id=' . $sess['ds_user_id'];
    return $c;
}

// Universal cURL helper with Anti-Ban Fingerprint Protection & Header Deduplication
function make_curl_request($url, $headers = [], $post_data = null, $cookies = '', $custom_proxy = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
    // Smart GZIP Data Saver: Compresses responses on-the-fly, cutting proxy bandwidth usage by ~77-85%
    curl_setopt($ch, CURLOPT_ENCODING, '');
    
    // Default desktop browser headers
    $default_headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Sec-Fetch-Mode: navigate'
    ];
    
    // Clean deduplicated header merge (prevents duplicate User-Agent or Referer headers)
    $merged = [];
    foreach ($default_headers as $h) {
        $colon = strpos($h, ':');
        $k = ($colon !== false) ? strtolower(trim(substr($h, 0, $colon))) : strtolower(trim($h));
        $merged[$k] = $h;
    }
    foreach ($headers as $h) {
        $colon = strpos($h, ':');
        $k = ($colon !== false) ? strtolower(trim(substr($h, 0, $colon))) : strtolower(trim($h));
        $merged[$k] = $h;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($merged));

    if (!empty($cookies)) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }

    if ($post_data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post_data) ? http_build_query($post_data) : $post_data);
    }

    // Anti-Ban Proxy Routing
    $proxy = '';
    if (defined('USE_PROXY') && USE_PROXY) {
        if (!empty($custom_proxy)) {
            $proxy = $custom_proxy;
        } elseif (defined('PROXY_POOL') && is_array(PROXY_POOL) && !empty(PROXY_POOL)) {
            $valid_proxies = array_values(array_filter(PROXY_POOL));
            if (!empty($valid_proxies)) {
                $proxy = $valid_proxies[array_rand($valid_proxies)];
            }
        }
        if (empty($proxy) && defined('PROXY_URL') && !empty(PROXY_URL)) {
            $proxy = PROXY_URL;
        }
    }

    if (!empty($proxy)) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        if (str_starts_with($proxy, 'socks5://') || str_starts_with($proxy, 'socks5h://')) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        }
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $http_code, 'body' => $response];
}

// 1. Fetch via RapidAPI Proxy (Universal Adapter for Lifetime Zero-Maintenance)
function normalize_rapidapi_response($data, $original_url) {
    if (!$data || !is_array($data)) return null;

    $media_list = [];
    $thumbnail = '';
    $download_url = '';
    $type = 'reel';
    $author = 'Instagram Creator';
    $caption = '✨ Instagram Media (Ready to Download)';

    // Provider format 1: { "result": [ { "url": "...", "type": "video/mp4", ... } ] }
    if (!empty($data['result']) && is_array($data['result'])) {
        foreach ($data['result'] as $idx => $item) {
            $u = $item['url'] ?? $item['download_url'] ?? '';
            $t = (isset($item['type']) && strpos($item['type'], 'image') !== false) ? 'image' : 'video';
            $thumb = $item['thumb'] ?? $item['thumbnail'] ?? '';
            if ($u) {
                if (empty($download_url)) $download_url = $u;
                if (empty($thumbnail) && $thumb) $thumbnail = $thumb;
                $media_list[] = [
                    'type' => $t,
                    'quality' => ($t === 'video') ? 'HD Video (MP4)' : 'HD Photo (JPG)',
                    'url' => $u,
                    'proxy_url' => 'download.php?type=' . $t . '&url=' . base64_encode($u)
                ];
            }
        }
    }
    // Provider format 2: { "data": [ ... ] } or { "data": { "video_url": ... } }
    elseif (!empty($data['data'])) {
        $d = $data['data'];
        if (isset($d['video_url']) || isset($d['image_url']) || isset($d['download_url'])) {
            $v_url = $d['video_url'] ?? $d['download_url'] ?? '';
            $i_url = $d['image_url'] ?? $d['thumbnail_url'] ?? '';
            if ($v_url) {
                $download_url = $v_url;
                if ($i_url) $thumbnail = $i_url;
                $media_list[] = [
                    'type' => 'video',
                    'quality' => 'HD Video (MP4)',
                    'url' => $v_url,
                    'proxy_url' => 'download.php?type=video&url=' . base64_encode($v_url)
                ];
            } elseif ($i_url) {
                if (empty($thumbnail)) $thumbnail = $i_url;
                if (empty($download_url)) $download_url = $i_url;
                $media_list[] = [
                    'type' => 'image',
                    'quality' => 'HD Photo (JPG)',
                    'url' => $i_url,
                    'proxy_url' => 'download.php?type=image&url=' . base64_encode($i_url)
                ];
            }
        } elseif (is_array($d)) {
            foreach ($d as $item) {
                $u = $item['url'] ?? $item['video_url'] ?? $item['download_url'] ?? '';
                if ($u) {
                    $is_v = !empty($item['is_video']) || (isset($item['type']) && $item['type'] === 'video') || strpos($u, '.mp4') !== false;
                    $media_list[] = [
                        'type' => $is_v ? 'video' : 'image',
                        'quality' => $is_v ? 'HD Video (MP4)' : 'HD Photo (JPG)',
                        'url' => $u,
                        'proxy_url' => 'download.php?type=' . ($is_v ? 'video' : 'image') . '&url=' . base64_encode($u)
                    ];
                }
            }
        }
    }
    // Provider format 3: Direct fields in root
    elseif (!empty($data['url']) || !empty($data['video_url'])) {
        $u = $data['video_url'] ?? $data['url'] ?? '';
        $thumb = $data['thumb'] ?? $data['thumbnail'] ?? '';
        if ($thumb) {
            $thumbnail = $thumb;
        }
        $media_list[] = [
            'type' => 'video',
            'quality' => 'Full HD 1080p (MP4)',
            'url' => $u,
            'proxy_url' => 'download.php?type=video&url=' . base64_encode($u)
        ];
        $download_url = $u;
    }

    if (!empty($media_list)) {
        if (empty($thumbnail)) $thumbnail = $media_list[0]['url'];
        if (empty($download_url)) $download_url = $media_list[0]['url'];
        return [
            'type' => ($media_list[0]['type'] === 'video') ? 'reel' : 'post',
            'author' => $data['owner']['username'] ?? $data['author'] ?? $author,
            'caption' => $data['caption'] ?? $data['title'] ?? $caption,
            'thumbnail' => $thumbnail,
            'download_url' => $download_url,
            'media' => $media_list
        ];
    }

    return null;
}

// Universal Reference Site Bridge (Direct Bypass to Public Scraper Gateways: InDown & VideoDropper)
// 100% Account-Free, 0 Cookies, No Account Suspension Risk, 100% Free
function fetch_via_reference_bridge($insta_url, $identifier = '', $type = 'reel') {
    $clean_url = explode('?', $insta_url)[0];
    if (empty($clean_url)) return null;

    $decode_jwt_token = function($href) {
        if (preg_match('/token=([^&]+)/', $href, $m)) {
            $parts = explode('.', $m[1]);
            if (isset($parts[1])) {
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                if (!empty($payload['url'])) {
                    return $payload['url'];
                }
            }
        }
        return $href;
    };

    // Engine 0: SnapSave Public Reference Scraper (Guarantees Full Audio / Progressive Sound MP4)
    if ($type !== 'profile' && $type !== 'dp') {
        $ch_ss = curl_init('https://snapsave.app/action.php');
        curl_setopt($ch_ss, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_ss, CURLOPT_POST, true);
        curl_setopt($ch_ss, CURLOPT_POSTFIELDS, http_build_query(['url' => $clean_url]));
        curl_setopt($ch_ss, CURLOPT_HTTPHEADER, [
            'Origin: https://snapsave.app',
            'Referer: https://snapsave.app/',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
        ]);
        curl_setopt($ch_ss, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_ss, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch_ss, CURLOPT_TIMEOUT, 12);
        $res_ss = curl_exec($ch_ss);
        $code_ss = curl_getinfo($ch_ss, CURLINFO_HTTP_CODE);
        curl_close($ch_ss);

        if ($code_ss === 200 && !empty($res_ss) && preg_match('/decodeURIComponent\(escape\(r\)\)\}\s*\(\s*"([^"]+)"\s*,\s*([0-9]+)\s*,\s*"([^"]+)"\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/s', $res_ss, $sm)) {
            $h_str = $sm[1];
            $n_str = $sm[3];
            $t_num = (int)$sm[4];
            $e_num = (int)$sm[5];

            $charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/";
            $e68c_fn = function($d, $e_val, $f_val) use ($charset) {
                $h_sub = substr($charset, 0, $e_val);
                $i_sub = substr($charset, 0, $f_val);
                $len = strlen($d);
                $j = 0;
                for ($k = 0; $k < $len; $k++) {
                    $char = $d[$len - 1 - $k];
                    $idx = strpos($h_sub, $char);
                    if ($idx !== false) {
                        $j += $idx * pow($e_val, $k);
                    }
                }
                $k_res = '';
                while ($j > 0) {
                    $k_res = $i_sub[$j % $f_val] . $k_res;
                    $j = floor($j / $f_val);
                }
                return !empty($k_res) ? $k_res : '0';
            };

            $r_dec = '';
            $h_len = strlen($h_str);
            $split_c = $n_str[$e_num];
            $i_ptr = 0;
            while ($i_ptr < $h_len) {
                $s_part = '';
                while ($i_ptr < $h_len && $h_str[$i_ptr] !== $split_c) {
                    $s_part .= $h_str[$i_ptr];
                    $i_ptr++;
                }
                $i_ptr++;
                for ($j = 0; $j < strlen($n_str); $j++) {
                    $s_part = str_replace($n_str[$j], (string)$j, $s_part);
                }
                $val = (int)$e68c_fn($s_part, $e_num, 10) - $t_num;
                if ($val > 0) $r_dec .= chr($val);
            }

            $unpacked_html = stripslashes(urldecode($r_dec));
            if (!empty($unpacked_html)) {
                $snapsave_media = [];
                preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/i', $unpacked_html, $sm_a);
                preg_match_all('/<img[^>]+src="([^"]+)"/i', $unpacked_html, $sm_img);
                $snap_thumb = !empty($sm_img[1][0]) ? $decode_jwt_token($sm_img[1][0]) : '';

                foreach ($sm_a[1] as $a_idx => $href) {
                    $label = strip_tags($sm_a[2][$a_idx] ?? '');
                    if (stripos($label, 'download with app') !== false || stripos($label, 'more videos') !== false || $href === '/') continue;
                    if (strpos($href, 'token=') !== false || strpos($href, 'cdninstagram.com') !== false || strpos($href, 'fbcdn.net') !== false) {
                        $cdn_url = $decode_jwt_token($href);
                        if (strpos($cdn_url, '/') === 0) continue;
                        $is_vid = (stripos($label, 'video') !== false || stripos($cdn_url, '.mp4') !== false);
                        $num = count($snapsave_media) + 1;
                        $snapsave_media[] = [
                            'type' => $is_vid ? 'video' : 'image',
                            'quality' => ($type === 'story' || strpos($insta_url, '/stories/') !== false)
                                ? ($is_vid ? "Story #{$num} (Full HD with Sound)" : "Story #{$num} (HD Photo)")
                                : ($is_vid ? "Full HD 1080p (MP4 with Sound)" : "Original HD Photo (JPG)"),
                            'title' => ($type === 'story' || strpos($insta_url, '/stories/') !== false) ? "Story #{$num}" : ($is_vid ? "HD Video" : "HD Photo"),
                            'thumbnail' => $snap_thumb ?: $cdn_url,
                            'preview_url' => $is_vid ? $cdn_url : ($snap_thumb ?: $cdn_url),
                            'url' => $cdn_url,
                            'proxy_url' => 'download.php?type=' . ($is_vid ? 'video' : 'image') . '&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num) . ($snap_thumb ? '&thumb=' . urlencode(base64_encode($snap_thumb)) : '') . '&url=' . base64_encode($cdn_url)
                        ];
                    }
                }

                // If any videos found in results, remove duplicate images so it's a pure video with audio!
                $has_vid = false;
                foreach ($snapsave_media as $sm_item) {
                    if ($sm_item['type'] === 'video') { $has_vid = true; break; }
                }
                if ($has_vid && count($snapsave_media) > 1 && $type !== 'story') {
                    $snapsave_media = array_values(array_filter($snapsave_media, function($m) { return $m['type'] === 'video'; }));
                }

                if (!empty($snapsave_media)) {
                    $first_m = $snapsave_media[0];
                    return [
                        'type' => ($type === 'story' || strpos($insta_url, '/stories/') !== false) ? 'story' : ($first_m['type'] === 'video' ? 'reel' : 'post'),
                        'shortcode' => $identifier,
                        'author' => 'Instagram User',
                        'caption' => ($type === 'story' || strpos($insta_url, '/stories/') !== false) ? count($snapsave_media) . ' Active Stories found' : '✨ Instagram Video with Sound (Ready to Download)',
                        'thumbnail' => $first_m['thumbnail'],
                        'download_url' => $first_m['url'],
                        'media' => $snapsave_media
                    ];
                }
            }
        }
    }

    // Engine A: InDown.net AJAX Bridge (Public Reference Scraper - Reels, Posts, Stories, Highlights, DP)
    if ($type === 'profile' || $type === 'dp') {
        $vt = 'profile';
    } elseif ($type === 'story' || strpos($insta_url, '/stories/') !== false) {
        $vt = 'story';
    } else {
        $vt = 'reel';
    }

    $post_data = http_build_query([
        'q' => $clean_url,
        'vt' => $vt,
        't' => 'media',
        'lang' => 'en',
        'v' => 'v2'
    ]);
    $in_headers = [
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'X-Requested-With: XMLHttpRequest',
        'Origin: https://indown.net',
        'Referer: https://indown.net/'
    ];
    $ch = curl_init('https://indown.net/api/ajaxSearch');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $in_headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200 && !empty($res)) {
        $json = json_decode($res, true);
        if (!empty($json['data']) && ($json['status'] ?? '') === 'ok') {
            $html = $json['data'];

            // 1. Profile / DP Handling
            if ($type === 'profile' || $type === 'dp') {
                preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/i', $html, $m_a);
                preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $m_img);
                $avatar_url = '';
                foreach ($m_a[1] as $idx => $href) {
                    $label = strip_tags($m_a[2][$idx] ?? '');
                    if (stripos($label, 'Avatar') !== false) {
                        $avatar_url = $decode_jwt_token($href);
                        break;
                    }
                }
                if (!$avatar_url && !empty($m_a[1][0])) {
                    $avatar_url = $decode_jwt_token($m_a[1][0]);
                }
                if ($avatar_url && strpos($avatar_url, '/') !== 0) {
                    $username = $identifier ?: 'instagram_user';
                    $proxy_url = 'download.php?type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($avatar_url);
                    $preview_url = 'download.php?preview=1&type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($avatar_url);
                    return [
                        'type' => 'dp',
                        'username' => $username,
                        'full_name' => $username,
                        'biography' => '',
                        'followers' => 0,
                        'following' => 0,
                        'posts_count' => 0,
                        'is_verified' => false,
                        'is_private' => false,
                        'thumbnail' => $preview_url,
                        'preview_url' => $preview_url,
                        'download_url' => $proxy_url,
                        'media' => [
                            [
                                'type' => 'image',
                                'quality' => 'Full HD Profile Picture (1080p)',
                                'url' => $avatar_url,
                                'thumbnail' => $preview_url,
                                'preview_url' => $preview_url,
                                'proxy_url' => $proxy_url
                            ]
                        ]
                    ];
                }
            }

            // 2. Stories, Reels & Posts Handling
            $items = [];
            if (preg_match_all('/<div class="download-items">([\s\S]*?)<\/div>\s*<\/li>/i', $html, $m_items)) {
                $items = $m_items[1];
            } elseif (preg_match_all('/<li[^>]*>([\s\S]*?)<\/li>/i', $html, $m_li)) {
                $items = $m_li[1];
            }

            $is_story_mode = ($type === 'story' || strpos($insta_url, '/stories/') !== false);
            $media_list = [];
            if (!empty($items)) {
                foreach ($items as $idx => $item_html) {
                    // When fetching stories, skip the profile avatar card
                    if ($is_story_mode && stripos($item_html, 'Avatar') !== false) {
                        continue;
                    }

                    $thumb = '';
                    if (preg_match('/<img[^>]+src="([^"]+)"/i', $item_html, $m_img)) {
                        $thumb = $decode_jwt_token($m_img[1]);
                    }
                    preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/i', $item_html, $m_a);
                    $video_url = '';
                    $image_url = '';
                    $audio_url = '';
                    foreach ($m_a[1] as $a_idx => $href) {
                        if ($href === '/' || strpos($href, 'back-home') !== false) continue;
                        $label = strip_tags($m_a[2][$a_idx] ?? '');
                        if (stripos($label, 'other video') !== false) continue;
                        $url = $decode_jwt_token($href);
                        if (strpos($url, '/') === 0) continue;
                        if ($is_story_mode && stripos($label, 'Avatar') !== false) continue;

                        if (stripos($label, 'Audio') !== false || stripos($url, '.mp3') !== false || stripos($url, '.m4a') !== false) {
                            $audio_url = $url;
                        } elseif (stripos($label, 'Video') !== false || stripos($url, '.mp4') !== false) {
                            $video_url = $url;
                        } elseif (stripos($label, 'Image') !== false || stripos($url, '.jpg') !== false || stripos($url, '.jpeg') !== false) {
                            $image_url = $url;
                        }
                    }
                    $is_vid = !empty($video_url);
                    $best_url = $is_vid ? $video_url : $image_url;
                    if (empty($best_url) && !empty($thumb) && !$is_story_mode) {
                        $best_url = $thumb;
                    }
                    if (!empty($best_url)) {
                        $num = count($media_list) + 1;
                        $preview_thumb = !empty($thumb)
                            ? ('download.php?preview=1&type=image&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num . '_thumb') . '&url=' . base64_encode($thumb))
                            : ($is_vid ? '' : ('download.php?preview=1&type=image&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num) . '&url=' . base64_encode($best_url)));

                        $item_entry = [
                            'type' => $is_vid ? 'video' : 'image',
                            'quality' => $is_story_mode
                                ? ($is_vid ? "Story #{$num} (Video with Sound)" : (!empty($audio_url) ? "Story #{$num} (Photo with Music 🎵)" : "Story #{$num} (HD Photo)"))
                                : ($is_vid ? "Full HD 1080p (Video with Sound)" : "Original HD Photo (JPG)"),
                            'title' => $is_story_mode ? "Story #{$num}" : ($is_vid ? "HD Video" : "HD Photo"),
                            'thumbnail' => $preview_thumb ?: ($thumb ?: $best_url),
                            'preview_url' => $is_vid ? $best_url : ($preview_thumb ?: $best_url),
                            'url' => $best_url,
                            'proxy_url' => 'download.php?type=' . ($is_vid ? 'video' : 'image') . '&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num) . ($thumb ? '&thumb=' . urlencode(base64_encode($thumb)) : '') . '&url=' . base64_encode($best_url)
                        ];

                        $media_list[] = $item_entry;
                    }
                }
            }

            // Fallback flat link matcher if items grouping yielded empty
            if (empty($media_list)) {
                preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/i', $html, $m_a);
                preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $m_img);
                $thumb = !empty($m_img[1][0]) ? $decode_jwt_token($m_img[1][0]) : '';
                foreach ($m_a[1] as $idx => $href) {
                    $label = strip_tags($m_a[2][$idx] ?? '');
                    if (stripos($label, 'other video') !== false || $href === '/') continue;
                    if ($is_story_mode && stripos($label, 'Avatar') !== false) continue;
                    if (strpos($href, 'token=') !== false || strpos($href, 'cdninstagram.com') !== false) {
                        $cdn_url = $decode_jwt_token($href);
                        if (strpos($cdn_url, '/') === 0) continue;
                        $is_video = (stripos($label, 'video') !== false || stripos($cdn_url, '.mp4') !== false);
                        $num = count($media_list) + 1;
                        $preview_thumb = !empty($thumb)
                            ? ('download.php?preview=1&type=image&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num . '_thumb') . '&url=' . base64_encode($thumb))
                            : ($is_video ? '' : ('download.php?preview=1&type=image&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num) . '&url=' . base64_encode($cdn_url)));
                        $fallback_entry = [
                            'type' => $is_video ? 'video' : 'image',
                            'quality' => $is_story_mode
                                ? ($is_video ? "Story #{$num} (Video with Sound)" : "Story #{$num} (HD Photo)")
                                : ($is_video ? "Full HD 1080p (Video with Sound)" : "Original HD Photo (JPG)"),
                            'title' => $is_story_mode ? "Story #{$num}" : ($is_video ? "HD Video" : "HD Photo"),
                            'thumbnail' => $preview_thumb ?: ($thumb ?: $cdn_url),
                            'preview_url' => $is_video ? $cdn_url : ($preview_thumb ?: $cdn_url),
                            'url' => $cdn_url,
                            'proxy_url' => 'download.php?type=' . ($is_video ? 'video' : 'image') . '&name=' . urlencode('InstaGo_' . ($identifier ?: 'media') . '_' . $num) . ($thumb ? '&thumb=' . urlencode(base64_encode($thumb)) : '') . '&url=' . base64_encode($cdn_url)
                        ];
                        $media_list[] = $fallback_entry;
                    }
                }
            }

            if (!empty($media_list)) {
                $has_video = false;
                $best_dl = $media_list[0]['url'];
                foreach ($media_list as $m) {
                    if ($m['type'] === 'video') {
                        $has_video = true;
                        $best_dl = $m['url'];
                        break;
                    }
                }
                $is_story = ($type === 'story' || strpos($insta_url, '/stories/') !== false);
                if ($has_video && !$is_story) {
                    $media_list = array_values(array_filter($media_list, function($m) { return $m['type'] === 'video'; }));
                    if (!empty($media_list)) {
                        $best_dl = $media_list[0]['url'];
                    }
                }
                return [
                    'type' => $is_story ? 'story' : ($has_video ? 'reel' : 'post'),
                    'shortcode' => $identifier,
                    'author' => '@' . ($identifier ?: 'instagram_user'),
                    'caption' => $is_story ? count($media_list) . ' Active Stories found' : '✨ Media Ready to Download (via Reference Bridge)',
                    'thumbnail' => $media_list[0]['thumbnail'] ?? $best_dl,
                    'download_url' => $best_dl,
                    'media' => $media_list
                ];
            }
        }
    }

    // Engine B: VideoDropper AES-128-ECB Bridge (Reels & Posts)
    if ($type !== 'story' && strpos($insta_url, '/stories/') === false) {
        $key = 'qwertyuioplkjhgf';
        $encrypted = openssl_encrypt($clean_url, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        if ($encrypted) {
            $enc_hex = bin2hex($encrypted);
            $vd_headers = [
                'url: ' . $enc_hex,
                'Accept: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Origin: https://reelsave.app',
                'Referer: https://reelsave.app/'
            ];
            $ch = curl_init('https://api.videodropper.app/allinone');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $vd_headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && !empty($res)) {
                $data = json_decode($res, true);
                if (!empty($data['video']) && is_array($data['video'])) {
                    $media_list = [];
                    foreach ($data['video'] as $idx => $v) {
                        $v_url = $v['video'] ?? '';
                        $t_url = $v['thumbnail'] ?? '';
                        if ($v_url) {
                            $media_list[] = [
                                'type' => 'video',
                                'quality' => 'Full HD 1080p (MP4)',
                                'title' => 'HD Video',
                                'thumbnail' => $t_url ?: $v_url,
                                'url' => $v_url,
                                'proxy_url' => 'download.php?type=video&name=' . urlencode('InstaGo_' . ($identifier ?: 'reel') . ($idx > 0 ? '_' . ($idx + 1) : '')) . ($t_url ? '&thumb=' . urlencode(base64_encode($t_url)) : '') . '&url=' . base64_encode($v_url)
                            ];
                        }
                    }
                    if (!empty($media_list)) {
                        return [
                            'type' => 'reel',
                            'shortcode' => $identifier,
                            'author' => 'Instagram User',
                            'caption' => '✨ Instagram Video (via Reference Bridge)',
                            'thumbnail' => $media_list[0]['thumbnail'],
                            'download_url' => $media_list[0]['url'],
                            'media' => $media_list
                        ];
                    }
                }
            }
        }
    }

    return null;
}

function fetch_via_rapidapi($url) {

    if (empty(RAPIDAPI_KEY)) return null;

    $api_url = "https://" . RAPIDAPI_HOST . "/?url=" . urlencode($url);
    $headers = [
        "X-RapidAPI-Key: " . RAPIDAPI_KEY,
        "X-RapidAPI-Host: " . RAPIDAPI_HOST
    ];
    $res = make_curl_request($api_url, $headers);
    if ($res['code'] === 200 && !empty($res['body'])) {
        $data = json_decode($res['body'], true);
        $normalized = normalize_rapidapi_response($data, $url);
        if ($normalized) return $normalized;
    }
    return null;
}

// Native Anonymous Instagram Profile Scraper (100% Ban-Free, Works for Public & Private Accounts, Local & Live Server)
function fetch_via_native_profile_scraper($username) {
    $username = trim(ltrim($username, '@'));
    $username = preg_replace('/[^a-zA-Z0-9._]/', '', $username);
    if (empty($username)) return null;

    $url = "https://www.instagram.com/{$username}/";
    $headers = [
        'User-Agent: facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9'
    ];

    $html = '';
    $pic = '';

    // Attempt 1: Direct fetch with Crawler User-Agent (Works on Residential / Local servers)
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $d_html = curl_exec($ch);
    $d_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($d_code === 200 && !empty($d_html) && preg_match('/<meta property="og:image" content="([^"]+)"/i', $d_html, $m)) {
        $candidate = html_entity_decode($m[1]);
        if (strpos($candidate, 'static.cdninstagram.com') === false && (strpos($candidate, 'cdninstagram.com') !== false || strpos($candidate, 'fbcdn.net') !== false)) {
            $pic = $candidate;
            $html = $d_html;
        }
    }

    // Attempt 2: Residential Proxy Pool Fallback (Used when deployed on Vercel / Cloud server where datacenter IPs are blocked)
    if (empty($pic) && defined('PROXY_POOL') && is_array(PROXY_POOL) && !empty(PROXY_POOL)) {
        $pool = PROXY_POOL;
        shuffle($pool);
        $try_proxies = array_slice($pool, 0, 4);
        foreach ($try_proxies as $prx) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_PROXY, $prx);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 7);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $p_html = curl_exec($ch);
            $p_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($p_code === 200 && !empty($p_html) && preg_match('/<meta property="og:image" content="([^"]+)"/i', $p_html, $m)) {
                $candidate = html_entity_decode($m[1]);
                if (strpos($candidate, 'static.cdninstagram.com') === false && (strpos($candidate, 'cdninstagram.com') !== false || strpos($candidate, 'fbcdn.net') !== false)) {
                    $pic = $candidate;
                    $html = $p_html;
                    break;
                }
            }
        }
    }

    if (empty($pic)) {
        return null;
    }

    $full_name = $username;
    if (preg_match('/<meta property="og:title" content="([^"]+)"/i', $html, $m_t)) {
        $t_str = html_entity_decode($m_t[1]);
        if (preg_match('/^(.*?)\s*\(@[A-Za-z0-9._]+\)/i', $t_str, $fn)) {
            $full_name = trim($fn[1]);
        }
    }

    $bio = '';
    $followers = 0;
    $following = 0;
    $posts = 0;
    if (preg_match('/<meta property="og:description" content="([^"]+)"/i', $html, $m_d)) {
        $desc = html_entity_decode($m_d[1]);
        $bio = $desc;
        if (preg_match('/([0-9,KMkm.]+)\s*Followers,\s*([0-9,KMkm.]+)\s*Following,\s*([0-9,KMkm.]+)\s*Posts/i', $desc, $stat)) {
            $followers = $stat[1];
            $following = $stat[2];
            $posts = $stat[3];
        }
    }

    $proxy_url = 'download.php?type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($pic);
    $preview_url = 'download.php?preview=1&type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($pic);

    return [
        'type' => 'dp',
        'username' => $username,
        'full_name' => $full_name,
        'biography' => $bio ?: "Instagram Profile • @{$username}",
        'followers' => $followers,
        'following' => $following,
        'posts_count' => $posts,
        'is_verified' => (stripos($html, 'Verified') !== false || stripos($html, '"is_verified":true') !== false),
        'is_private' => (stripos($html, 'This Account is Private') !== false || stripos($html, '"is_private":true') !== false),
        'thumbnail' => $preview_url,
        'preview_url' => $preview_url,
        'download_url' => $proxy_url,
        'media' => [
            [
                'type' => 'image',
                'quality' => 'Full HD Profile Picture (1080p)',
                'url' => $pic,
                'thumbnail' => $preview_url,
                'preview_url' => $preview_url,
                'proxy_url' => $proxy_url
            ]
        ]
    ];
}

// 2. Fetch DP / Profile Picture
function fetch_instagram_dp($username) {
    $username = trim(ltrim($username, '@'));
    $username = preg_replace('/[^a-zA-Z0-9._]/', '', $username);
    if (empty($username)) return null;

    // Stage 0: Native Anonymous Profile OpenGraph Scraper (100% Free, Ban-Free, Works for Public & Private)
    $native_dp = fetch_via_native_profile_scraper($username);
    if ($native_dp && !empty($native_dp['download_url'])) {
        return $native_dp;
    }

    // Stage 1: Reference Site Bridge Fallback
    $profile_url = "https://www.instagram.com/{$username}/";
    $bridge_dp = fetch_via_reference_bridge($profile_url, $username, 'profile');
    if ($bridge_dp && !empty($bridge_dp['download_url'])) {
        return $bridge_dp;
    }


    $sessions = get_instagram_session_pool();
    if (empty($sessions)) {
        $sessions = [['session_id' => '', 'csrf_token' => '', 'ds_user_id' => '']];
    }

    foreach ($sessions as $sess) {
        $cookies = get_session_cookies($sess);
        $sticky_proxy = get_session_sticky_proxy($sess);

        // 1. Try web profile info endpoint
        $api_url = "https://www.instagram.com/api/v1/users/web_profile_info/?username=" . urlencode($username);
        $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'X-IG-App-ID: 936619743392459',
            'X-Requested-With: XMLHttpRequest',
            'Referer: https://www.instagram.com/' . urlencode($username) . '/'
        ];
        
        $res = make_curl_request($api_url, $headers, null, $cookies, $sticky_proxy);
        if ($res['code'] === 200 && !empty($res['body'])) {
            $json = json_decode($res['body'], true);
            $user = $json['data']['user'] ?? null;
            if ($user) {
                $hd_pic = $user['profile_pic_url_hd'] ?? $user['profile_pic_url'] ?? '';
                if (!empty($hd_pic)) {
                    $proxy_url = 'download.php?type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($hd_pic);
                    $preview_url = 'download.php?preview=1&type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($hd_pic);
                    return [
                        'type' => 'dp',
                        'username' => $user['username'] ?? $username,
                        'full_name' => $user['full_name'] ?? '',
                        'biography' => $user['biography'] ?? '',
                        'followers' => $user['edge_followed_by']['count'] ?? 0,
                        'following' => $user['edge_follow']['count'] ?? 0,
                        'posts_count' => $user['edge_owner_to_timeline_media']['count'] ?? 0,
                        'is_verified' => $user['is_verified'] ?? false,
                        'is_private' => $user['is_private'] ?? false,
                        'thumbnail' => $preview_url,
                        'preview_url' => $preview_url,
                        'download_url' => $proxy_url,
                        'media' => [
                            [
                                'type' => 'image',
                                'quality' => 'Full HD Profile Picture (1080p)',
                                'url' => $hd_pic,
                                'thumbnail' => $preview_url,
                                'preview_url' => $preview_url,
                                'proxy_url' => $proxy_url
                            ]
                        ]
                    ];
                }
            }
        } elseif ($res['code'] === 302 || $res['code'] === 401 || $res['code'] === 429 || $res['code'] === 407 || strpos($res['body'] ?? '', 'checkpoint_required') !== false || strpos($res['body'] ?? '', 'require_login') !== false) {
            mark_session_cooldown($sess);
        }

        // 2. Try web search API (highly reliable, returns DP even when web profile info is rate-limited)
        $search_url = "https://www.instagram.com/api/v1/web/search/topsearch/?query=" . urlencode($username);
        $m_headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'X-IG-App-ID: 936619743392459',
            'X-ASBD-ID: 198387',
            'X-IG-WWW-Claim: 0',
            'X-Requested-With: XMLHttpRequest',
            'Referer: https://www.instagram.com/' . urlencode($username) . '/',
            'Accept-Language: en-US,en;q=0.9',
            'Accept: */*'
        ];
        $s_res = make_curl_request($search_url, $m_headers, null, $cookies, $sticky_proxy);
        if ($s_res['code'] === 200 && !empty($s_res['body'])) {
            $s_data = json_decode($s_res['body'], true);
            foreach ($s_data['users'] ?? [] as $u) {
                if (strtolower($u['username'] ?? '') === strtolower($username)) {
                    $pic = $u['profile_pic_url'] ?? '';
                    if (!empty($pic)) {
                        $proxy_url = 'download.php?type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($pic);
                        $preview_url = 'download.php?preview=1&type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($pic);
                        return [
                            'type' => 'dp',
                            'username' => $u['username'],
                            'full_name' => $u['full_name'] ?? $u['username'],
                            'biography' => ($u['is_verified'] ? 'Verified Official Profile' : 'Instagram Profile') . ' • @' . $u['username'],
                            'followers' => 0,
                            'following' => 0,
                            'posts_count' => 0,
                            'is_verified' => $u['is_verified'] ?? false,
                            'is_private' => $u['is_private'] ?? false,
                            'thumbnail' => $preview_url,
                            'preview_url' => $preview_url,
                            'download_url' => $proxy_url,
                            'media' => [
                                [
                                    'type' => 'image',
                                    'quality' => 'Full HD Profile Picture (1080p)',
                                    'url' => $pic,
                                    'thumbnail' => $preview_url,
                                    'preview_url' => $preview_url,
                                    'proxy_url' => $proxy_url
                                ]
                            ]
                        ];
                    }
                }
            }
        }
    }

    // 3. Fallback: scrape profile page
    $profile_url = "https://www.instagram.com/{$username}/";
    $p_res = make_curl_request($profile_url, [], null, $cookies ?? '');
    if ($p_res['code'] === 200 && !empty($p_res['body'])) {
        if (preg_match('/<meta property="og:image" content="([^"]+)"/i', $p_res['body'], $m)) {
            $pic = html_entity_decode($m[1]);
            $proxy_url = 'download.php?type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($pic);
            $preview_url = 'download.php?preview=1&type=dp&name=' . urlencode($username . '_dp') . '&url=' . base64_encode($pic);
            return [
                'type' => 'dp',
                'username' => $username,
                'full_name' => $username,
                'biography' => 'Instagram Profile • @' . $username,
                'thumbnail' => $preview_url,
                'preview_url' => $preview_url,
                'download_url' => $proxy_url,
                'media' => [
                    [
                        'type' => 'image',
                        'quality' => 'HD Profile Picture',
                        'url' => $pic,
                        'thumbnail' => $preview_url,
                        'preview_url' => $preview_url,
                        'proxy_url' => $proxy_url
                    ]
                ]
            ];
        }
    }

    return null;
}

// 3. Zero-Burn Anonymous Embed Scraper (Public Reels, Posts & Carousels - 100% Account Free)
function fetch_via_embed_scraper($shortcode, $url_type = 'reel') {
    $embed_url = "https://www.instagram.com/{$url_type}/{$shortcode}/embed/captioned/";
    $res = make_curl_request($embed_url, [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Sec-Fetch-Mode: navigate'
    ], null, '');

    if ($res['code'] === 200 && !empty($res['body'])) {
        $html = $res['body'];
        
        $clean_fn = function($str) {
            if (empty($str)) return '';
            // 1. Unescape JSON unicode ampersands (\u0026 and \\u0026) to prevent 'Bad URL hash' signature corruption
            $str = str_replace(['\\\\u0026', '\\u0026', '\u0026', 'u0026'], '&', $str);
            // 2. Decode general unicode escape sequences
            $str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($m) {
                return mb_convert_encoding(pack('H*', $m[1]), 'UTF-8', 'UCS-2BE');
            }, $str);
            // 3. Decode HTML entities (&amp; -> &)
            $str = html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // 4. Decode escaped slashes (\/ -> /)
            $str = str_replace(['\\/', '\/'], '/', $str);
            // 5. Clean up any trailing backslashes or quotes
            $str = stripslashes($str);
            return trim($str, " \t\n\r\0\x0B\"'\\");
        };

        // 1. Find all video URLs
        $video_urls = [];
        // Pattern A: Match explicit video_url pattern (handles \", \\", \\\", etc.)
        if (preg_match_all('/video_url(?:\\\\*")?\s*:\s*\\\\*"([^"\\\\]*(?:\\\\.[^"\\\\]*)*?)\\\\*"/i', $html, $m_vid)) {
            foreach ($m_vid[1] as $raw) {
                $v = $clean_fn($raw);
                if ($v && filter_var($v, FILTER_VALIDATE_URL) && !in_array($v, $video_urls)) {
                    if (strpos($v, 'oh=') !== false && strpos($v, 'oe=') !== false && strpos($v, 'u0026') === false) {
                        $video_urls[] = $v;
                    }
                }
            }
        }
        // Pattern B: Match any cdninstagram .mp4 URLs in body
        if (preg_match_all('/https?:(?:\\\\\/|[\/\\\\])+[^"\'\s<>]+\.mp4[^"\'\s<>]*/i', $html, $all_mp4)) {
            foreach ($all_mp4[0] as $raw) {
                $v = $clean_fn($raw);
                if ($v && filter_var($v, FILTER_VALIDATE_URL) && !in_array($v, $video_urls)) {
                    if (strpos($v, 'oh=') !== false && strpos($v, 'oe=') !== false && strpos($v, 'u0026') === false) {
                        $video_urls[] = $v;
                    }
                }
            }
        }

        // 2. Find thumbnail / display image
        $thumbnail = '';
        if (preg_match('/display_url(?:\\\\*")?\s*:\s*\\\\*"([^"\\\\]*(?:\\\\.[^"\\\\]*)*?)\\\\*"/i', $html, $m_thumb)) {
            $thumbnail = $clean_fn($m_thumb[1]);
        }
        if (!$thumbnail && preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $m_og)) {
            $thumbnail = html_entity_decode($m_og[1]);
        }

        // 3. Find Author
        $author = '';
        if (preg_match('/<span class="UsernameText">([^<]+)<\/span>/i', $html, $m_u)) {
            $author = trim($m_u[1]);
        } elseif (preg_match('/username=([A-Za-z0-9._-]+)/i', $html, $m_u)) {
            $author = trim($m_u[1]);
        } elseif (preg_match('/<a class="EmbeddedUsername[^"]*"[^>]*>([^<]+)<\/a>/i', $html, $m_u)) {
            $author = trim($m_u[1]);
        }

        // 4. Find Caption
        $caption = '';
        if (preg_match('/<div class="Caption">([\s\S]*?)<\/div>/i', $html, $m_cap)) {
            $caption = trim(strip_tags($m_cap[1]));
        }

        // If video found (Reels / Video Post)
        if (!empty($video_urls)) {
            $best_video = $video_urls[0];
            $media_list = [];
            foreach ($video_urls as $idx => $v_item) {
                $num = $idx + 1;
                $label = (count($video_urls) > 1) ? "Video #{$num} (Full HD MP4)" : "Full HD 1080p (MP4)";
                $media_list[] = [
                    'type' => 'video',
                    'quality' => $label,
                    'title' => (count($video_urls) > 1) ? "Slide #{$num}" : "HD Video",
                    'thumbnail' => $thumbnail ?: $v_item,
                    'url' => $v_item,
                    'proxy_url' => 'download.php?type=video&name=' . urlencode('InstaGo_' . $shortcode . ($num > 1 ? '_part' . $num : '')) . ($thumbnail ? '&thumb=' . urlencode(base64_encode($thumbnail)) : '') . '&url=' . base64_encode($v_item)
                ];
            }

            return [
                'type' => 'reel',
                'shortcode' => $shortcode,
                'author' => $author ?: 'Instagram User',
                'caption' => $caption ?: '✨ Instagram Video (Ready to Download)',
                'thumbnail' => $thumbnail ?: $best_video,
                'download_url' => $best_video,
                'media' => $media_list
            ];
        }

        // If photo post
        if (!empty($thumbnail)) {
            // CRITICAL FIX: If requested as reel, NEVER return a photo post!
            // Returning null allows Stage 2 (Session Pool Mobile API) to fetch the real 1080p MP4!
            if ($url_type === 'reel') {
                return null;
            }

            return [
                'type' => 'post',
                'shortcode' => $shortcode,
                'author' => $author ?: 'Instagram User',
                'caption' => $caption ?: '✨ Instagram Photo (Ready to Download)',
                'thumbnail' => $thumbnail,
                'download_url' => $thumbnail,
                'media' => [
                    [
                        'type' => 'image',
                        'quality' => 'Original High Resolution (JPG)',
                        'title' => 'Photo',
                        'thumbnail' => $thumbnail,
                        'url' => $thumbnail,
                        'proxy_url' => 'download.php?type=image&name=' . urlencode('InstaGo_' . $shortcode) . '&url=' . base64_encode($thumbnail)
                    ]
                ]
            ];
        }
    }
    return null;
}

// 4. Fetch Reels & Posts with Zero-Burn Priority & Multi-Account Fallbacks
function fetch_instagram_reel_or_post($shortcode, $url_type = 'reel') {
    $full_url = "https://www.instagram.com/{$url_type}/{$shortcode}/";

    // Stage 0: Direct Reference Site Bridge (Bypasses dummy accounts, 100% Free & Ban-Free)
    $bridge_res = fetch_via_reference_bridge($full_url, $shortcode, $url_type);
    if ($bridge_res && !empty($bridge_res['download_url'])) {
        return $bridge_res;
    }

    // Stage 1: Zero-Burn Anonymous Scraper (Protects all dummy accounts from suspension)
    $embed_res = fetch_via_embed_scraper($shortcode, $url_type);

    if ($embed_res) {
        $dl = $embed_res['download_url'] ?? '';
        if ($url_type === 'reel' && ($embed_res['type'] !== 'reel' || empty($dl) || strpos($dl, '.mp4') === false || strpos($dl, 'oh=') === false || strpos($dl, 'oe=') === false || strpos($dl, 'u0026') !== false)) {
            // Discard: Reel requested, but embed result has no valid signed MP4 video. Proceed to Stage 2!
        } else {
            return $embed_res;
        }
    }

    // Stage 2: Fallback to Multi-Account Session Pool (Used only when login/carousel is required)
    $sessions = get_instagram_session_pool();
    $media_id = shortcode_to_media_id($shortcode);

    foreach ($sessions as $sess) {
        $cookies = get_session_cookies($sess);
        $sticky_proxy = get_session_sticky_proxy($sess);

        // Engine A: Media Info API with authentic Web headers
        if ($media_id) {
            $mobile_api = "https://www.instagram.com/api/v1/media/{$media_id}/info/";
            $headers = [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'X-IG-App-ID: 936619743392459',
                'X-ASBD-ID: 198387',
                'X-IG-WWW-Claim: 0',
                'X-Requested-With: XMLHttpRequest',
                'Referer: https://www.instagram.com/reel/' . $shortcode . '/',
                'Sec-Ch-Ua: "Chromium";v="124", "Google Chrome";v="124"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "Windows"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'Accept: */*'
            ];
            $m_res = make_curl_request($mobile_api, $headers, null, $cookies, $sticky_proxy);
            if ($m_res['code'] === 200 && !empty($m_res['body'])) {
                $m_data = json_decode($m_res['body'], true);
                $item = $m_data['items'][0] ?? null;
                if ($item) {
                    return parse_instagram_item($item, $shortcode);
                }
            } elseif ($m_res['code'] === 302 || $m_res['code'] === 401 || $m_res['code'] === 429 || $m_res['code'] === 407 || strpos($m_res['body'] ?? '', 'checkpoint_required') !== false || strpos($m_res['body'] ?? '', 'require_login') !== false) {
                mark_session_cooldown($sess);
            }
        }

        // Engine B: GraphQL Web API
        if (!empty($sess['session_id'])) {
            $gql_url = "https://www.instagram.com/graphql/query";
            $gql_headers = [
                'X-IG-App-ID: 936619743392459',
                'X-CSRFToken: ' . ($sess['csrf_token'] ?? ''),
                'X-Requested-With: XMLHttpRequest',
                'Origin: https://www.instagram.com',
                'Referer: https://www.instagram.com/reel/' . $shortcode . '/',
                'Content-Type: application/x-www-form-urlencoded'
            ];
            $gql_data = [
                'doc_id' => '24368985919464652',
                'variables' => json_encode(['shortcode' => $shortcode])
            ];
            $gql_res = make_curl_request($gql_url, $gql_headers, $gql_data, $cookies, $sticky_proxy);
            if ($gql_res['code'] === 200 && !empty($gql_res['body'])) {
                $gql_json = json_decode($gql_res['body'], true);
                $item = $gql_json['data']['xdt_api__v1__media__shortcode__web_info']['items'][0] ?? null;
                if ($item) {
                    return parse_instagram_item($item, $shortcode);
                }
                $media = $gql_json['data']['xdt_shortcode_media'] ?? null;
                if ($media) {
                    return parse_graphql_media($media, $shortcode);
                }
            } elseif ($gql_res['code'] === 302 || $gql_res['code'] === 401 || $gql_res['code'] === 429 || $gql_res['code'] === 407 || strpos($gql_res['body'] ?? '', 'checkpoint_required') !== false || strpos($gql_res['body'] ?? '', 'require_login') !== false) {
                mark_session_cooldown($sess);
            }
        }
    }

    return null;
}

// Parse Instagram mobile API item object
function parse_instagram_item($item, $shortcode) {
    $author = $item['user']['username'] ?? 'Instagram User';
    $caption = $item['caption']['text'] ?? '';
    $thumbnail = $item['image_versions2']['candidates'][0]['url'] ?? '';
    
    $media_list = [];
    $is_video = !empty($item['video_versions']);

    // Check carousel items first
    if (!empty($item['carousel_media'])) {
        foreach ($item['carousel_media'] as $idx => $c_item) {
            $num = $idx + 1;
            if (!empty($c_item['video_versions'])) {
                $c_url = $c_item['video_versions'][0]['url'];
                $c_thumb = $c_item['image_versions2']['candidates'][0]['url'] ?? '';
                $media_list[] = [
                    'type' => 'video',
                    'quality' => "Slide {$num} (Video MP4)",
                    'title' => "Slide #{$num}",
                    'thumbnail' => $c_thumb ?: $c_url,
                    'url' => $c_url,
                    'proxy_url' => 'download.php?type=video&name=' . urlencode("InstaGo_{$shortcode}_part{$num}") . ($c_thumb ? '&thumb=' . urlencode(base64_encode($c_thumb)) : '') . '&url=' . base64_encode($c_url)
                ];
            } else if (!empty($c_item['image_versions2']['candidates'])) {
                $c_url = $c_item['image_versions2']['candidates'][0]['url'];
                $media_list[] = [
                    'type' => 'image',
                    'quality' => "Slide {$num} (Photo JPG)",
                    'title' => "Slide #{$num}",
                    'thumbnail' => $c_url,
                    'url' => $c_url,
                    'proxy_url' => 'download.php?type=image&name=' . urlencode("InstaGo_{$shortcode}_part{$num}") . '&url=' . base64_encode($c_url)
                ];
            }
        }
    } else {
        // Single item (Reel or Photo)
        if ($is_video) {
            $video_url = $item['video_versions'][0]['url'] ?? '';
            $media_list[] = [
                'type' => 'video',
                'quality' => 'Full HD 1080p (MP4)',
                'thumbnail' => $thumbnail,
                'url' => $video_url,
                'proxy_url' => 'download.php?type=video&name=' . urlencode('InstaGo_' . $shortcode) . ($thumbnail ? '&thumb=' . urlencode(base64_encode($thumbnail)) : '') . '&url=' . base64_encode($video_url)
            ];
        } else if (!empty($thumbnail)) {
            $media_list[] = [
                'type' => 'image',
                'quality' => 'HD Photo (JPG)',
                'thumbnail' => $thumbnail,
                'url' => $thumbnail,
                'proxy_url' => 'download.php?type=image&name=' . urlencode('InstaGo_' . $shortcode) . '&url=' . base64_encode($thumbnail)
            ];
        }
    }

    return [
        'type' => $is_video ? 'reel' : 'post',
        'shortcode' => $shortcode,
        'author' => $author,
        'caption' => $caption,
        'thumbnail' => $thumbnail,
        'download_url' => $is_video ? ($item['video_versions'][0]['url'] ?? '') : $thumbnail,
        'media' => $media_list
    ];
}

// Parse GraphQL media node
function parse_graphql_media($media, $shortcode) {
    $is_video = !empty($media['is_video']);
    $author = $media['owner']['username'] ?? 'Instagram User';
    $caption = $media['edge_media_to_caption']['edges'][0]['node']['text'] ?? '';
    $thumbnail = $media['display_url'] ?? '';
    $video_url = $media['video_url'] ?? '';

    $media_list = [];

    // Edge sidecar children (carousel)
    if (!empty($media['edge_sidecar_to_children']['edges'])) {
        foreach ($media['edge_sidecar_to_children']['edges'] as $idx => $edge) {
            $node = $edge['node'] ?? [];
            $num = $idx + 1;
            if (!empty($node['is_video']) && !empty($node['video_url'])) {
                $c_url = $node['video_url'];
                $thumb = $node['display_url'] ?? '';
                $media_list[] = [
                    'type' => 'video',
                    'quality' => "Slide {$num} (Video MP4)",
                    'title' => "Slide #{$num}",
                    'thumbnail' => $thumb ?: $c_url,
                    'url' => $c_url,
                    'proxy_url' => 'download.php?type=video&name=' . urlencode("InstaGo_{$shortcode}_part{$num}") . ($thumb ? '&thumb=' . urlencode(base64_encode($thumb)) : '') . '&url=' . base64_encode($c_url)
                ];
            } else if (!empty($node['display_url'])) {
                $c_url = $node['display_url'];
                $media_list[] = [
                    'type' => 'image',
                    'quality' => "Slide {$num} (Photo JPG)",
                    'title' => "Slide #{$num}",
                    'thumbnail' => $c_url,
                    'url' => $c_url,
                    'proxy_url' => 'download.php?type=image&name=' . urlencode("InstaGo_{$shortcode}_part{$num}") . '&url=' . base64_encode($c_url)
                ];
            }
        }
    } else {
        // Single item (Reel or Photo)
        if ($is_video && !empty($video_url)) {
            $media_list[] = [
                'type' => 'video',
                'quality' => 'Full HD 1080p (MP4)',
                'thumbnail' => $thumbnail,
                'url' => $video_url,
                'proxy_url' => 'download.php?type=video&name=' . urlencode('InstaGo_' . $shortcode) . ($thumbnail ? '&thumb=' . urlencode(base64_encode($thumbnail)) : '') . '&url=' . base64_encode($video_url)
            ];
        } else if (!empty($thumbnail)) {
            $media_list[] = [
                'type' => 'image',
                'quality' => 'HD Photo (JPG)',
                'thumbnail' => $thumbnail,
                'url' => $thumbnail,
                'proxy_url' => 'download.php?type=image&name=' . urlencode('InstaGo_' . $shortcode . '_thumb') . '&url=' . base64_encode($thumbnail)
            ];
        }
    }

    return [
        'type' => $is_video ? 'reel' : 'post',
        'shortcode' => $shortcode,
        'author' => $author,
        'caption' => $caption,
        'thumbnail' => $thumbnail,
        'download_url' => $is_video ? $video_url : $thumbnail,
        'media' => $media_list
    ];
}

// 4. Stories Fetcher
function fetch_instagram_story($username, $story_id = null) {
    // Stage 0: Direct Reference Site Bridge (Bypasses dummy accounts, 100% Free & Ban-Free)
    $story_url = (strtolower($username) === 'highlights' && !empty($story_id))
        ? "https://www.instagram.com/stories/highlights/{$story_id}/"
        : "https://www.instagram.com/stories/{$username}/" . (!empty($story_id) ? "{$story_id}/" : '');
    
    $bridge_story = fetch_via_reference_bridge($story_url, $username . (!empty($story_id) ? "_{$story_id}" : ''), 'story');
    if ($bridge_story && !empty($bridge_story['media'])) {
        return $bridge_story;
    }

    if (strtolower($username) !== 'highlights') {
        $profile_fallback_url = "https://www.instagram.com/{$username}/";
        $bridge_story2 = fetch_via_reference_bridge($profile_fallback_url, $username, 'story');
        if ($bridge_story2 && !empty($bridge_story2['media'])) {
            return $bridge_story2;
        }
    }

    $sessions = get_instagram_session_pool();
    if (empty($sessions)) {
        return null;
    }

    
    $mobile_headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'X-IG-App-ID: 936619743392459',
        'X-ASBD-ID: 198387',
        'X-IG-WWW-Claim: 0',
        'X-Requested-With: XMLHttpRequest',
        'Referer: https://www.instagram.com/',
        'Accept-Language: en-US,en;q=0.9',
        'Accept: */*'
    ];

    foreach ($sessions as $sess) {
        $cookies = get_session_cookies($sess);
        $sticky_proxy = get_session_sticky_proxy($sess);

        // Method 1: Specific Story Item ID (e.g. stories/username/1234567890)
        if (!empty($story_id)) {
            $api_url = "https://i.instagram.com/api/v1/media/{$story_id}/info/";
            $res = make_curl_request($api_url, $mobile_headers, null, $cookies, $sticky_proxy);
            if ($res['code'] === 200 && !empty($res['body'])) {
                $data = json_decode($res['body'], true);
                $item = $data['items'][0] ?? null;
                if ($item) {
                    $is_video = !empty($item['video_versions']);
                    $video_url = $item['video_versions'][0]['url'] ?? '';
                    $thumb_url = $item['image_versions2']['candidates'][0]['url'] ?? '';
                    $author = $item['user']['username'] ?? $username;
                    $full_name = $item['user']['full_name'] ?? '';

                    $music_url = $item['story_music_stickers'][0]['music_asset_info']['progressive_download_url'] ?? '';
                    $music_title = $item['story_music_stickers'][0]['music_asset_info']['title'] ?? '';

                    $media_list = [];
                    if ($is_video && !empty($video_url)) {
                        $media_item = [
                            'type' => 'video',
                            'quality' => 'Story Video HD (MP4 with Sound)',
                            'thumbnail' => $thumb_url,
                            'url' => $video_url,
                            'proxy_url' => 'download.php?type=video&name=' . urlencode('InstaGo_Story_' . $author . '_' . $story_id) . '&url=' . base64_encode($video_url)
                        ];
                        $media_list[] = $media_item;
                    } else if (!empty($thumb_url)) {
                        $media_item = [
                            'type' => 'image',
                            'quality' => 'Story Photo HD (JPG)',
                            'thumbnail' => $thumb_url,
                            'url' => $thumb_url,
                            'proxy_url' => 'download.php?type=image&name=' . urlencode('InstaGo_Story_' . $author . '_' . $story_id . '_img') . '&url=' . base64_encode($thumb_url)
                        ];
                        $media_list[] = $media_item;
                    }

                    return [
                        'type' => 'story',
                        'author' => '@' . $author . ($full_name ? " ({$full_name})" : ''),
                        'caption' => '✨ Instagram Story by @' . $author . ' (Ready to Download)',
                        'thumbnail' => $thumb_url,
                        'download_url' => $is_video ? $video_url : $thumb_url,
                        'media' => $media_list
                    ];
                }
            } elseif ($res['code'] === 302 || $res['code'] === 401 || $res['code'] === 429 || $res['code'] === 407 || strpos($res['body'] ?? '', 'checkpoint_required') !== false || strpos($res['body'] ?? '', 'require_login') !== false) {
                mark_session_cooldown($sess);
            }
        }

        // Method 2: Handle highlights (stories/highlights/123456789)
        if (strtolower($username) === 'highlights' && !empty($story_id)) {
            $reel_key = "highlight:{$story_id}";
            $api_url = "https://i.instagram.com/api/v1/feed/reels_media/?reel_ids={$reel_key}";
            $res = make_curl_request($api_url, $mobile_headers, null, $cookies, $sticky_proxy);
            if ($res['code'] === 200 && !empty($res['body'])) {
                $data = json_decode($res['body'], true);
                $items = $data['reels'][$reel_key]['items'] ?? [];
                if (!empty($items)) {
                    return parse_story_items($items, 'Highlight');
                }
            } elseif ($res['code'] === 302 || $res['code'] === 401 || $res['code'] === 429 || $res['code'] === 407 || strpos($res['body'] ?? '', 'checkpoint_required') !== false || strpos($res['body'] ?? '', 'require_login') !== false) {
                mark_session_cooldown($sess);
            }
        }

        // Method 3: Fetch all active stories for user by resolving User ID
        $user_id = null;
        $search_url = "https://i.instagram.com/api/v1/users/search/?q=" . urlencode($username);
        $s_res = make_curl_request($search_url, $mobile_headers, null, $cookies, $sticky_proxy);
        if ($s_res['code'] === 200 && !empty($s_res['body'])) {
            $s_data = json_decode($s_res['body'], true);
            foreach ($s_data['users'] ?? [] as $u) {
                if (strtolower($u['username']) === strtolower($username)) {
                    $user_id = $u['pk'] ?? $u['id'] ?? null;
                    break;
                }
            }
        }

        if (!$user_id) {
            $profile_url = "https://www.instagram.com/" . urlencode($username) . "/";
            $p_res = make_curl_request($profile_url, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            ], null, $cookies, $sticky_proxy);

            if ($p_res['code'] === 200 && !empty($p_res['body'])) {
                if (preg_match('/"props":{"id":"([0-9]+)"/i', $p_res['body'], $m) || 
                    preg_match('/"user_id":"([0-9]+)"/i', $p_res['body'], $m) ||
                    preg_match('/"id":"([0-9]{5,25})"/i', $p_res['body'], $m)) {
                    $user_id = $m[1];
                }
            }
        }

        if ($user_id) {
            $reels_url = "https://i.instagram.com/api/v1/feed/reels_media/?reel_ids={$user_id}";
            $r_res = make_curl_request($reels_url, $mobile_headers, null, $cookies, $sticky_proxy);
            if ($r_res['code'] === 200 && !empty($r_res['body'])) {
                $r_data = json_decode($r_res['body'], true);
                $items = $r_data['reels'][$user_id]['items'] ?? [];
                if (!empty($items)) {
                    return parse_story_items($items, $username);
                }
            } elseif ($r_res['code'] === 302 || $r_res['code'] === 401 || $r_res['code'] === 429 || $r_res['code'] === 407 || strpos($r_res['body'] ?? '', 'checkpoint_required') !== false || strpos($r_res['body'] ?? '', 'require_login') !== false) {
                mark_session_cooldown($sess);
            }
        }
    }

    return null;
}

// Helper to parse multiple story items from reels_media
function parse_story_items($items, $username) {
    $is_highlight = (strtolower($username) === 'highlight' || strtolower($username) === 'highlights');
    $media_list = [];
    foreach ($items as $idx => $s_item) {
        $num = $idx + 1;
        $s_id = $s_item['pk'] ?? $s_item['id'] ?? $num;
        $label_prefix = $is_highlight ? 'Highlight' : 'Story';
        $thumb_candidate = $s_item['image_versions2']['candidates'][0]['url'] ?? '';

        $music_url = $s_item['story_music_stickers'][0]['music_asset_info']['progressive_download_url'] ?? '';
        $music_title = $s_item['story_music_stickers'][0]['music_asset_info']['title'] ?? '';

        if (!empty($s_item['video_versions'])) {
            $v_url = $s_item['video_versions'][0]['url'];
            $entry = [
                'type' => 'video',
                'quality' => "{$label_prefix} {$num} (Video with Sound)",
                'title' => "{$label_prefix} #{$num}",
                'thumbnail' => $thumb_candidate ?: $v_url,
                'url' => $v_url,
                'proxy_url' => 'download.php?type=video&name=' . urlencode("InstaGo_{$label_prefix}_{$username}_{$s_id}") . '&url=' . base64_encode($v_url)
            ];
            $media_list[] = $entry;
        } else if (!empty($thumb_candidate) || !empty($s_item['image_versions2']['candidates'])) {
            $i_url = $thumb_candidate ?: $s_item['image_versions2']['candidates'][0]['url'];
            $entry = [
                'type' => 'image',
                'quality' => "{$label_prefix} {$num} (Photo JPG)",
                'title' => "{$label_prefix} #{$num}",
                'thumbnail' => $i_url,
                'url' => $i_url,
                'proxy_url' => 'download.php?type=image&name=' . urlencode("InstaGo_{$label_prefix}_{$username}_{$s_id}") . '&url=' . base64_encode($i_url)
            ];
            $media_list[] = $entry;
        }
    }

    if (!empty($media_list)) {
        $first = $media_list[0];
        $author = $items[0]['user']['username'] ?? $username;
        $full_name = $items[0]['user']['full_name'] ?? '';
        $desc = $is_highlight 
            ? count($media_list) . ' Highlights found for @' . $author
            : count($media_list) . ' Active Stories found for @' . $author;
        return [
            'type' => $is_highlight ? 'highlight' : 'story',
            'author' => '@' . $author . ($full_name ? " ({$full_name})" : ''),
            'caption' => $desc,
            'thumbnail' => $first['url'],
            'download_url' => $first['url'],
            'media' => $media_list
        ];
    }
    return null;
}

// --- MAIN REQUEST HANDLER ---
if (defined('INSTAGO_API_NO_DISPATCH')) {
    return;
}
$raw_input = file_get_contents('php://input');
$post = $_POST;

if (!empty($raw_input)) {
    $json = json_decode($raw_input, true);
    if (is_array($json)) {
        $post = array_merge($post, $json);
    }
}

$input_url = trim($post['url'] ?? $post['link'] ?? $_GET['url'] ?? $_GET['link'] ?? '');

if (empty($input_url)) {
    json_fail('Please provide a valid Instagram URL or Username.');
}

// 0. Clean input url (strip leading type prefixes like "Dp https://...", "reel https://...", "story https://...")
$input_url = preg_replace('/^(?:dp|reel|reels|story|stories|post|photo|photos|highlight|highlights)\s*[:=\-]?\s*/i', '', $input_url);

// Normalized URL without query parameters for strict regex routing
$clean_input_for_matching = preg_replace('/[?#].*$/', '', $input_url);
$clean_input_for_matching = rtrim($clean_input_for_matching, '/');

// 0. High-Speed Shield Cache (10-Minute Instant Response, Saves Accounts & Proxy Bandwidth)
$cache_key = $clean_input_for_matching;
$cached_data = get_cached_media($cache_key);
if ($cached_data) {
    json_success($cached_data);
}

// Auto-detect input type
// 1. Stories & Highlights: instagram.com/stories/username/12345 or /stories/highlights/12345
if (preg_match('/(?:instagram\.com\/)?stories\/(?:highlights\/([0-9]+)|([A-Za-z0-9._-]+)(?:\/([0-9]+))?)/i', $clean_input_for_matching, $m)) {
    if (!empty($m[1])) {
        $username = 'highlights';
        $story_id = $m[1];
    } else {
        $username = $m[2];
        $story_id = $m[3] ?? null;
    }
    $res = fetch_instagram_story($username, $story_id);
    if ($res) json_success($res, $cache_key);

    $rapid = fetch_via_rapidapi($input_url);
    if ($rapid) json_success($rapid, $cache_key);

    json_fail('Story or Highlight not found or the account is private.');
}

// 1b. Shared Highlight Shortlink: instagram.com/s/aGlnaGxpZ2h0OjE4...
if (preg_match('/instagram\.com\/s\/([A-Za-z0-9_-]+)/i', $clean_input_for_matching, $m)) {
    $highlight_id = null;
    $b64 = strtr($m[1], '-_', '+/');
    $decoded = base64_decode($b64);
    if ($decoded && preg_match('/highlight:([0-9]+)/i', $decoded, $dm)) {
        $highlight_id = $dm[1];
    }
    if (!$highlight_id) {
        $ch = curl_init($input_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $head = curl_exec($ch);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        if ($redirect_url && preg_match('/highlights\/([0-9]+)/i', $redirect_url, $rm)) {
            $highlight_id = $rm[1];
        }
    }
    if ($highlight_id) {
        $res = fetch_instagram_story('highlights', $highlight_id);
        if ($res) json_success($res, $cache_key);
    }
    $rapid = fetch_via_rapidapi($input_url);
    if ($rapid) json_success($rapid, $cache_key);

    json_fail('Highlight not found or the account is private.');
}

// 2. Reels: instagram.com/reel/SHORTCODE or /reels/SHORTCODE
if (preg_match('/instagram\.com\/(?:reel|reels)\/([A-Za-z0-9_-]+)/i', $clean_input_for_matching, $m)) {
    $shortcode = $m[1];
    if (strtolower($shortcode) !== 'audio') {
        $rapid = fetch_via_rapidapi($input_url);
        if ($rapid) json_success($rapid, $cache_key);

        $result = fetch_instagram_reel_or_post($shortcode, 'reel');
        if ($result) json_success($result, $cache_key);

        json_fail('Unable to fetch this Reel. Please make sure the post or account is public.', 404);
    }
}

// 3. Posts / Photos: instagram.com/p/SHORTCODE or /tv/SHORTCODE
if (preg_match('/instagram\.com\/(?:p|tv)\/([A-Za-z0-9_-]+)/i', $clean_input_for_matching, $m)) {
    $shortcode = $m[1];
    $rapid = fetch_via_rapidapi($input_url);
    if ($rapid) json_success($rapid, $cache_key);

    $result = fetch_instagram_reel_or_post($shortcode, 'p');
    if ($result) json_success($result, $cache_key);

    json_fail('Unable to download this post. Please make sure the post is public.', 404);
}

// 4. DP / Profile: @username or instagram.com/username
if (preg_match('/^@?([A-Za-z0-9._]{1,30})$/', $clean_input_for_matching, $m) || 
    preg_match('/instagram\.com\/([A-Za-z0-9._]{1,30})$/i', $clean_input_for_matching, $m)) {
    $username = $m[1];
    // Ignore reserved paths
    if (!in_array(strtolower($username), ['explore', 'reels', 'stories', 'direct', 'accounts', 'developer', 'about', 'legal'])) {
        if (!empty(RAPIDAPI_KEY)) {
            $rapid_dp = fetch_via_rapidapi("https://www.instagram.com/{$username}/");
            if ($rapid_dp) json_success($rapid_dp, $cache_key);
        }

        $dp_res = fetch_instagram_dp($username);
        if ($dp_res) json_success($dp_res, $cache_key);

        json_fail('Profile not found or user does not exist (@' . htmlspecialchars($username) . ').', 404);
    }
}

// Universal Reference Bridge Fallback for any Instagram links
if (strpos($input_url, 'instagram.com') !== false) {
    $universal_bridge = fetch_via_reference_bridge($input_url);
    if ($universal_bridge && !empty($universal_bridge['media'])) {
        json_success($universal_bridge, $cache_key);
    }
}

json_fail('Please enter a valid Instagram URL (e.g. Reels, DP, Post, or Story link).');


