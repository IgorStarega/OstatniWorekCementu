<?php
// Wspólna mapa statusów rezerwacji na klasy Bootstrapa (jak w reservations.php).
$statusClasses = [
    'oczekująca'   => 'text-bg-warning',
    'potwierdzona' => 'text-bg-info',
    'zrealizowana' => 'text-bg-success',
    'anulowana'    => 'text-bg-secondary',
];

$actionClasses = [
    'create' => 'text-bg-success',
    'update' => 'text-bg-info',
    'delete' => 'text-bg-danger',
    'status' => 'text-bg-warning',
    'login'  => 'text-bg-secondary',
];

$tiles = [
    ['label' => 'Klienci', 'key' => 'clients', 'page' => 'admin-users'],
    ['label' => 'Pracownicy', 'key' => 'employees', 'page' => 'admin-employees'],
    ['label' => 'Usługi', 'key' => 'services', 'page' => 'admin-services'],
    ['label' => 'Rezerwacje', 'key' => 'reservations_total', 'page' => 'admin-reservations'],
    ['label' => 'Oczekujące', 'key' => 'reservations_pending', 'page' => 'admin-reservations', 'filter' => ['status' => 'oczekująca']],
    ['label' => 'Na dziś', 'key' => 'reservations_today', 'page' => 'admin-reservations'],
];

$statusCounts = [];
$totalStatus = 0;
foreach ($byStatus as $statusRow) {
    $statusCounts[(string) $statusRow['status']] = (int) $statusRow['total'];
    $totalStatus += (int) $statusRow['total'];
}

$maxStatus = $statusCounts === [] ? 0 : max($statusCounts);
$totalServiceReservations = 0;
foreach ($topServices as $serviceRow) {
    $totalServiceReservations += (int) $serviceRow['total'];
}
$totalWorkloadReservations = 0;
foreach ($workload as $workloadRow) {
    $totalWorkloadReservations += (int) $workloadRow['total'];
}

$reservationRows = $upcoming !== [] ? $upcoming : $recentReservations;
$reservationHeading = $upcoming !== [] ? 'Najbliższe rezerwacje' : 'Ostatnie rezerwacje';
?>
<div class="admin-toolbar">
    <div>
        <h2>Dashboard</h2>
        <p class="text-muted mb-0">Podsumowanie pracy warsztatu, statystyki i szybki dostęp do modułów.</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-outline-light btn-sm" href="/?page=admin-reservations">Rezerwacje</a>
        <a class="btn btn-outline-light btn-sm" href="/?page=admin-reservations-export">Eksport CSV</a>
        <a class="btn btn-primary btn-sm" href="/?page=admin-services">Dodaj usługę</a>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-3">
    <?php foreach ($tiles as $tile): ?>
        <?php
        $tileQuery = array_merge(['page' => $tile['page']], $tile['filter'] ?? []);
        $tileHref = '/?' . http_build_query($tileQuery);
        ?>
        <div class="col">
            <a class="card h-100 text-decoration-none" href="<?= htmlspecialchars($tileHref, ENT_QUOTES, 'UTF-8') ?>">
                <div class="card-body">
                    <p class="card-title text-muted mb-1"><?= htmlspecialchars($tile['label'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="display-5 mb-0"><?= (int) ($counters[$tile['key']] ?? 0) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-section">
    <h3>Statystyki</h3>

    <div class="admin-stat-grid">
        <div class="admin-stat-tile">
            <p class="admin-stat-label mb-1">Przychód zrealizowanych</p>
            <p class="admin-stat-value mb-0">
                <?= htmlspecialchars(number_format((float) $revenue, 2, ',', ' ') . ' zł', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="admin-stat-hint mb-0">zrealizowanych: <?= (int) ($statusCounts['zrealizowana'] ?? 0) ?></p>
        </div>

        <div class="admin-stat-tile">
            <p class="admin-stat-label mb-1">Wartość w toku</p>
            <p class="admin-stat-value mb-0">
                <?= htmlspecialchars(number_format((float) $revenuePending, 2, ',', ' ') . ' zł', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="admin-stat-hint mb-0">oczekujące i potwierdzone</p>
        </div>

        <div class="admin-stat-tile">
            <p class="admin-stat-label mb-1">Średnia zrealizowana</p>
            <p class="admin-stat-value mb-0">
                <?= htmlspecialchars(number_format((float) $averageCompleted, 2, ',', ' ') . ' zł', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="admin-stat-hint mb-0">na jedną rezerwację</p>
        </div>

        <div class="admin-stat-tile">
            <p class="admin-stat-label mb-1">W tym miesiącu</p>
            <p class="admin-stat-value mb-0">
                <?= htmlspecialchars(number_format((float) $monthly['revenue'], 2, ',', ' ') . ' zł', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="admin-stat-hint mb-0"><?= (int) $monthly['reservations'] ?> rezerwacji utworzonych w tym miesiącu</p>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-8">
            <h4 class="admin-stat-heading">Rezerwacje według statusu (łącznie <?= $totalStatus ?>)</h4>

            <?php if ($totalStatus === 0): ?>
                <p class="admin-empty mb-0">Brak rezerwacji do wyświetlenia.</p>
            <?php else: ?>
                <div class="admin-chart">
                    <?php foreach ($byStatus as $statusRow): ?>
                        <?php
                        $statusName = (string) $statusRow['status'];
                        $statusTotal = (int) $statusRow['total'];
                        $statusHeight = $maxStatus > 0 ? round($statusTotal / $maxStatus * 100, 1) : 0;
                        $statusShare = $totalStatus > 0 ? round($statusTotal / $totalStatus * 100) : 0;
                        $statusColor = $statusColors[$statusName] ?? 'var(--bs-secondary)';
                        $statusHref = '/?' . http_build_query([
                            'page'   => 'admin-reservations',
                            'status' => $statusName,
                        ]);
                        ?>
                        <a class="admin-chart-item" href="<?= htmlspecialchars($statusHref, ENT_QUOTES, 'UTF-8') ?>"
                           title="Pokaż rezerwacje: <?= htmlspecialchars($statusName, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="admin-chart-track">
                                <div class="admin-chart-bar"
                                     style="height: <?= $statusHeight ?>%; background-color: <?= htmlspecialchars($statusColor, ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="admin-chart-value"><?= $statusTotal ?></span>
                                </div>
                            </div>
                            <span class="admin-chart-label"><?= htmlspecialchars($statusName, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="admin-chart-share"><?= $statusShare ?>%</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-xl-4">
            <h4 class="admin-stat-heading">Najbliższe dni wolne</h4>

            <?php if (!$daysOffReady): ?>
                <p class="admin-empty mb-0">Niedostępne do czasu uruchomienia migracji 007.</p>
            <?php elseif ($upcomingDaysOff === []): ?>
                <p class="admin-empty mb-0">Brak zaplanowanych dni wolnych.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Data</th>
                                <th scope="col">Pracownik</th>
                                <th scope="col">Powód</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingDaysOff as $dayOff): ?>
                                <?php
                                $dayOffDate = strtotime((string) $dayOff['date']);
                                $dayOffReason = trim((string) ($dayOff['reason'] ?? ''));
                                ?>
                                <tr>
                                    <td data-label="Data">
                                        <?= $dayOffDate !== false
                                            ? htmlspecialchars(date('d.m.Y', $dayOffDate), ENT_QUOTES, 'UTF-8')
                                            : '—' ?>
                                    </td>
                                    <td data-label="Pracownik"><?= htmlspecialchars((string) $dayOff['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-label="Powód">
                                        <?= $dayOffReason !== ''
                                            ? htmlspecialchars($dayOffReason, ENT_QUOTES, 'UTF-8')
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <h4 class="admin-stat-heading">Najczęściej rezerwowane usługi (TOP 5)</h4>

            <?php if ($topServices === []): ?>
                <p class="admin-empty mb-0">Brak danych o rezerwacjach usług.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Usługa</th>
                                <th scope="col" class="admin-progress-cell">Udział</th>
                                <th scope="col" class="text-end">Liczba</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topServices as $serviceRow): ?>
                                <?php
                                $serviceTotal = (int) $serviceRow['total'];
                                $serviceWidth = $totalServiceReservations > 0
                                    ? round($serviceTotal / $totalServiceReservations * 100, 1)
                                    : 0;
                                ?>
                                <tr>
                                    <td data-label="Usługa"><?= htmlspecialchars((string) $serviceRow['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-label="Udział" class="admin-progress-cell">
                                        <div class="admin-progress" role="img"
                                             aria-label="<?= $serviceTotal ?> rezerwacji">
                                            <div class="admin-progress-bar" style="width: <?= $serviceWidth ?>%"></div>
                                        </div>
                                    </td>
                                    <td data-label="Liczba" class="text-end"><?= $serviceTotal ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-6">
            <h4 class="admin-stat-heading">Obłożenie pracowników (TOP 5)</h4>

            <?php if ($workload === []): ?>
                <p class="admin-empty mb-0">Brak danych o pracownikach.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Pracownik</th>
                                <th scope="col" class="admin-progress-cell">Udział</th>
                                <th scope="col" class="text-end">Liczba</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workload as $workloadRow): ?>
                                <?php
                                $workloadTotal = (int) $workloadRow['total'];
                                $workloadWidth = $totalWorkloadReservations > 0
                                    ? round($workloadTotal / $totalWorkloadReservations * 100, 1)
                                    : 0;
                                ?>
                                <tr>
                                    <td data-label="Pracownik"><?= htmlspecialchars((string) $workloadRow['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-label="Udział" class="admin-progress-cell">
                                        <div class="admin-progress" role="img"
                                             aria-label="<?= $workloadTotal ?> rezerwacji">
                                            <div class="admin-progress-bar" style="width: <?= $workloadWidth ?>%"></div>
                                        </div>
                                    </td>
                                    <td data-label="Liczba" class="text-end"><?= $workloadTotal ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="admin-section">
    <h3><?= htmlspecialchars($reservationHeading, ENT_QUOTES, 'UTF-8') ?></h3>

    <?php if ($upcoming === [] && $recentReservations !== []): ?>
        <p class="text-muted">Brak nadchodzących rezerwacji — pokazujemy ostatnie zapisane terminy.</p>
    <?php endif; ?>

    <?php if ($reservationRows === []): ?>
        <p class="admin-empty mb-0">Brak rezerwacji do wyświetlenia.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Godzina</th>
                        <th scope="col">Klient</th>
                        <th scope="col">Usługa</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservationRows as $reservation): ?>
                        <?php
                        $status = (string) $reservation['status'];
                        $statusClass = $statusClasses[$status] ?? 'text-bg-secondary';
                        ?>
                        <tr>
                            <td data-label="Data"><?= htmlspecialchars(date('d.m.Y', strtotime((string) $reservation['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="Godzina"><?= htmlspecialchars(substr((string) $reservation['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="Klient"><?= htmlspecialchars((string) $reservation['client_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="Usługa"><?= htmlspecialchars((string) $reservation['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="Status">
                                <span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td data-label="Akcje">
                                <div class="admin-actions justify-content-end">
                                    <a class="btn btn-sm btn-outline-light"
                                       href="/?page=admin-reservations&amp;id=<?= (int) $reservation['id'] ?>">Szczegóły</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="admin-section">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="mb-0">Ostatnie operacje</h3>
        <a class="btn btn-outline-light btn-sm" href="/?page=admin-log">Pełny log</a>
    </div>

    <?php if (!$logReady): ?>
        <p class="admin-empty mt-3 mb-0">Log jest niedostępny do czasu uruchomienia migracji 006.</p>
    <?php elseif ($recentLog === []): ?>
        <p class="admin-empty mt-3 mb-0">Brak zapisanych operacji.</p>
    <?php else: ?>
        <div class="admin-table-wrap mt-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Akcja</th>
                        <th scope="col">Szczegóły</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLog as $entry): ?>
                        <?php
                        $action = (string) $entry['action'];
                        $actionClass = $actionClasses[$action] ?? 'text-bg-secondary';
                        $createdAt = strtotime((string) $entry['created_at']);
                        $details = trim((string) ($entry['details'] ?? ''));
                        ?>
                        <tr>
                            <td data-label="Data">
                                <?= $createdAt !== false
                                    ? htmlspecialchars(date('d.m.Y H:i', $createdAt), ENT_QUOTES, 'UTF-8')
                                    : '—' ?>
                            </td>
                            <td data-label="Autor">
                                <?= htmlspecialchars(
                                    $entry['user_name'] !== null ? (string) $entry['user_name'] : 'system',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td data-label="Akcja">
                                <span class="badge <?= htmlspecialchars($actionClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td data-label="Szczegóły">
                                <?= $details !== ''
                                    ? htmlspecialchars($details, ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
