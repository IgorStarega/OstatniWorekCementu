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
