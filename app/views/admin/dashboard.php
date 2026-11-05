<?php
// Wspólna mapa statusów rezerwacji na klasy Bootstrapa (jak w reservations.php).
$statusClasses = [
    'oczekująca'   => 'text-bg-warning',
    'potwierdzona' => 'text-bg-info',
    'zrealizowana' => 'text-bg-success',
    'anulowana'    => 'text-bg-secondary',
];

$tiles = [
    ['label' => 'Klienci', 'key' => 'clients', 'page' => 'admin-users'],
    ['label' => 'Pracownicy', 'key' => 'employees', 'page' => 'admin-employees'],
    ['label' => 'Usługi', 'key' => 'services', 'page' => 'admin-services'],
    ['label' => 'Rezerwacje', 'key' => 'reservations_total', 'page' => 'admin-reservations'],
    ['label' => 'Na dziś', 'key' => 'reservations_today', 'page' => 'admin-reservations'],
];
?>

<div class="admin-toolbar">
    <div>
        <h2>Dashboard</h2>
        <p class="text-muted mb-0">Podsumowanie pracy warsztatu i szybki dostęp do modułów.</p>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-3">
    <?php foreach ($tiles as $tile): ?>
        <div class="col">
            <a class="card h-100 text-decoration-none"
               href="/?page=<?= htmlspecialchars($tile['page'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="card-body">
                    <p class="card-title text-muted mb-1"><?= htmlspecialchars($tile['label'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="display-5 mb-0"><?= (int) ($counters[$tile['key']] ?? 0) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-section">
    <h3>Najbliższe rezerwacje</h3>

    <?php if ($upcoming === []): ?>
        <p class="admin-empty mb-0">Brak nadchodzących rezerwacji.</p>
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
                    <?php foreach ($upcoming as $reservation): ?>
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

<?php
// Kolory słupków zgodne z badge'ami statusów rezerwacji.
$statusColors = [
    'oczekująca'   => 'var(--bs-warning)',
    'potwierdzona' => 'var(--bs-info)',
    'zrealizowana' => 'var(--bs-success)',
    'anulowana'    => 'var(--bs-secondary)',
];
$maxStatus = 0;
foreach ($byStatus as $statusRow) {
    $maxStatus = max($maxStatus, (int) $statusRow['total']);
}
?>
<div class="admin-section">
    <h3>Statystyki</h3>

    <div class="row g-3">
        <div class="col-xl-8">
            <h4 class="admin-stat-heading">Rezerwacje według statusu</h4>

            <div class="admin-chart">
                <?php foreach ($byStatus as $statusRow): ?>
                    <?php
                    $statusName = (string) $statusRow['status'];
                    $statusTotal = (int) $statusRow['total'];
                    $statusHeight = $maxStatus > 0 ? round($statusTotal / $maxStatus * 100, 1) : 0;
                    $statusColor = $statusColors[$statusName] ?? 'var(--bs-secondary)';
                    ?>
                    <div class="admin-chart-item">
                        <div class="admin-chart-track">
                            <div class="admin-chart-bar"
                                 style="height: <?= $statusHeight ?>%; background-color: <?= htmlspecialchars($statusColor, ENT_QUOTES, 'UTF-8') ?>">
                                <span class="admin-chart-value"><?= $statusTotal ?></span>
                            </div>
                        </div>
                        <span class="admin-chart-label"><?= htmlspecialchars($statusName, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-xl-4">
            <h4 class="admin-stat-heading">Przychód</h4>
            <div class="admin-stat-tile">
                <p class="admin-stat-label mb-1">Przychód zrealizowanych</p>
                <p class="admin-stat-value mb-0">
                    <?= htmlspecialchars(number_format((float) $revenue, 2, ',', ' ') . ' zł', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
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
                                <th scope="col" class="text-end">Rezerwacje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topServices as $serviceRow): ?>
                                <tr>
                                    <td data-label="Usługa"><?= htmlspecialchars((string) $serviceRow['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-label="Rezerwacje" class="text-end"><?= (int) $serviceRow['total'] ?></td>
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
                                <th scope="col" class="text-end">Rezerwacje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workload as $workloadRow): ?>
                                <tr>
                                    <td data-label="Pracownik"><?= htmlspecialchars((string) $workloadRow['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-label="Rezerwacje" class="text-end"><?= (int) $workloadRow['total'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
