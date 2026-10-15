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

// Żądanie pochodzi z zaufanego reverse proxy.
//
// Kto wpisuje TRUSTED_PROXY_IPS: administrator wdrażający aplikację za tunelem
// (np. cloudflared działający jako kontener) — podaje adres kontenera tunelu,
// ewentualnie CIDR. Jest to bezpieczne TYLKO dlatego, że origin jest nieosiągalny
// z zewnątrz (porty zbindowane na pętli lokalnej); gdyby ktoś mógł połączyć się
// bezpośrednio, mógłby podrobić X-Forwarded-Proto. Dlatego domyślna wartość jest
// pusta i nie rozszerzamy zaufania na całą prywatną pulę Dockera.
function requestFromTrustedProxy(): bool
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!is_string($remote) || $remote === '') {
        return false;
    }

    // Pętla lokalna jest zawsze zaufana.
    if ($remote === '127.0.0.1' || $remote === '::1' || strpos($remote, '127.') === 0) {
        return true;
    }

    // Adresy skonfigurowane ręcznie (TRUSTED_PROXY_IPS -> app.trusted_proxies).
    $trusted = $GLOBALS['config']['app']['trusted_proxies'] ?? [];
    if (!is_array($trusted)) {
        return false;
    }

    foreach ($trusted as $entry) {
        if (is_string($entry) && ipInCidr($remote, $entry)) {
            return true;
        }
    }

    return false;
}

// Dopasowanie adresu IPv4 do pojedynczego IP albo zapisu CIDR (np. 172.18.0.0/16).
// IPv6 obsługujemy wyłącznie jako ::1 w pętli lokalnej — nie udajemy pełnej obsługi IPv6.
function ipInCidr(string $ip, string $cidr): bool
{
    if (strpos($cidr, '/') === false) {
        return $ip === $cidr;
    }

    [$subnet, $maskBits] = explode('/', $cidr, 2);
    if (!ctype_digit($maskBits)) {
        return false;
    }

    $maskBits = (int) $maskBits;
    if ($maskBits > 32) {
        return false;
    }

    $ipLong = ip2long($ip);
    $subnetLong = ip2long($subnet);
    if ($ipLong === false || $subnetLong === false) {
        return false;
    }

    $mask = $maskBits === 0 ? 0 : (-1 << (32 - $maskBits));

    return ($ipLong & $mask) === ($subnetLong & $mask);
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
