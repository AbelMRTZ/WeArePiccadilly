<?php
/*
 * Router for PHP built-in server: adds byte-range support so HTML5
 * video (<video> / <source>) works correctly during local development.
 * Usage: php -S localhost:8000 -t /path/to/dir router.php
 */

$uri      = $_SERVER['REQUEST_URI'];
$filePath = __DIR__ . urldecode(parse_url($uri, PHP_URL_PATH));

// PHP files and directories: let the server handle them normally
if (!is_file($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
    return false;
}

$mimes = [
    'mp4'   => 'video/mp4',
    'webm'  => 'video/webm',
    'mov'   => 'video/quicktime',
    'ogg'   => 'video/ogg',
    'mp3'   => 'audio/mpeg',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'png'   => 'image/png',
    'gif'   => 'image/gif',
    'svg'   => 'image/svg+xml',
    'ico'   => 'image/x-icon',
    'css'   => 'text/css; charset=utf-8',
    'js'    => 'application/javascript; charset=utf-8',
    'json'  => 'application/json',
    'html'  => 'text/html; charset=utf-8',
    'htm'   => 'text/html; charset=utf-8',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'eot'   => 'application/vnd.ms-fontobject',
];

$ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mime = $mimes[$ext] ?? 'application/octet-stream';
$size = filesize($filePath);

header("Content-Type: $mime");
header("Accept-Ranges: bytes");

if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m);
    $start  = (int) $m[1];
    $end    = (isset($m[2]) && $m[2] !== '') ? (int) $m[2] : $size - 1;
    $length = $end - $start + 1;

    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
    header("Content-Length: $length");

    $fp = fopen($filePath, 'rb');
    fseek($fp, $start);
    $remaining = $length;
    while ($remaining > 0 && !feof($fp)) {
        $chunk = min(65536, $remaining);
        echo fread($fp, $chunk);
        $remaining -= $chunk;
        flush();
    }
    fclose($fp);
} else {
    header("Content-Length: $size");
    readfile($filePath);
}
exit;
