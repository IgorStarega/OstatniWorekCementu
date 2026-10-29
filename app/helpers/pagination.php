<?php
// Wspólne dane i nawigacja paginacji dla list administracyjnych.

// Buduje dane paginacji. $params to istniejące parametry GET do zachowania.
function paginate(int $total, int $perPage, int $currentPage): array
{
    $perPage = max(1, $perPage);
    $pages = max(1, (int) ceil($total / $perPage));
    $currentPage = min(max(1, $currentPage), $pages);

    return [
        'page'    => $currentPage,
        'pages'   => $pages,
        'perPage' => $perPage,
        'offset'  => ($currentPage - 1) * $perPage,
        'total'   => $total,
        'hasPrev' => $currentPage > 1,
        'hasNext' => $currentPage < $pages,
    ];
}

// Renderuje nawigację stron, zachowując istniejące parametry zapytania.
// $params: tablica bez nazwy strony (np. bez 'p') — może być pusta.
function renderPagination(array $pagination, string $pageName, array $params = []): string
{
    $pages = max(1, (int) ($pagination['pages'] ?? 1));
    $current = min(max(1, (int) ($pagination['page'] ?? 1)), $pages);

    // Puste filtry nie zaśmiecają adresów, a stronę ustawiamy zawsze na końcu.
    $params = array_filter($params, static function ($value): bool {
        return $value !== null && $value !== '';
    });
    unset($params[$pageName]);

    $urlFor = static function (int $page) use ($pageName, $params): string {
        $query = $params;
        $query[$pageName] = $page;

        return '/?' . http_build_query($query);
    };

    $html = '<nav aria-label="Nawigacja stron"><ul class="pagination">';

    if ($current > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="'
            . htmlspecialchars($urlFor($current - 1), ENT_QUOTES, 'UTF-8')
            . '" rel="prev">« Poprzednia</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link" aria-disabled="true">« Poprzednia</span></li>';
    }

    // Maksymalnie siedem numerów stron; przy dłuższych listach skrót „…”.
    if ($pages <= 7) {
        $items = range(1, $pages);
    } elseif ($current <= 4) {
        $items = [1, 2, 3, 4, 5, '...', $pages];
    } elseif ($current >= $pages - 3) {
        $items = [1, '...', $pages - 4, $pages - 3, $pages - 2, $pages - 1, $pages];
    } else {
        $items = [1, '...', $current - 1, $current, $current + 1, '...', $pages];
    }

    foreach ($items as $item) {
        if ($item === '...') {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        } elseif ($item === $current) {
            $html .= '<li class="page-item active" aria-current="page"><span class="page-link">'
                . $current . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="'
                . htmlspecialchars($urlFor((int) $item), ENT_QUOTES, 'UTF-8')
                . '">' . (int) $item . '</a></li>';
        }
    }

    if ($current < $pages) {
        $html .= '<li class="page-item"><a class="page-link" href="'
            . htmlspecialchars($urlFor($current + 1), ENT_QUOTES, 'UTF-8')
            . '" rel="next">Następna »</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link" aria-disabled="true">Następna »</span></li>';
    }

    return $html . '</ul></nav>';
}
