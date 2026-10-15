<?php
// Helpery adresów URL. Linki absolutne budujemy wyłącznie z APP_URL (config app.url),
// a nie z nagłówka Host — dzięki temu nie da się zatruć linku w mailu.

// Czy bieżące żądanie przyszło po HTTPS? Za tunelem Cloudflare origin widzi zwykłe
// HTTP, więc protokół odczytujemy z nagłówków proxy. Ufamy im tylko dla żądań
// z pętli lokalnej, bo origin jest osiągalny wyłącznie przez tunel.
function requestIsHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if (!requestFromTrustedProxy()) {
        return false;
    }

    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    if (is_string($forwardedProto)) {
        $first = strtolower(trim(explode(',', $forwardedProto)[0]));
        if ($first === 'https') {
            return true;
        }
    }

    $cfVisitor = $_SERVER['HTTP_CF_VISITOR'] ?? '';
    if (is_string($cfVisitor)) {
        $visitor = json_decode($cfVisitor, true);
        if (is_array($visitor) && strtolower((string) ($visitor['scheme'] ?? '')) === 'https') {
            return true;
        }
    }

    return false;
}

// Żądanie pochodzi z zaufanego reverse proxy (adres pętli lokalnej).
function requestFromTrustedProxy(): bool
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!is_string($remote) || $remote === '') {
        return false;
    }

    return $remote === '127.0.0.1'
        || $remote === '::1'
        || strpos($remote, '127.') === 0;
}

// Pełny adres złożony z APP_URL i ścieżki aplikacji, np. absoluteUrl('/?page=login').
function absoluteUrl(string $path = '/'): string
{
    $base = rtrim((string) ($GLOBALS['config']['app']['url'] ?? ''), '/');

    if ($base === '') {
        return $path;
    }

    if ($path === '' || $path === '/') {
        return $base . '/';
    }

    return $base . '/' . ltrim($path, '/');
}
