<?php
$statusClasses = [
    'oczekująca'   => 'text-bg-warning',
    'potwierdzona' => 'text-bg-info',
    'zrealizowana' => 'text-bg-success',
    'anulowana'    => 'text-bg-secondary',
];
?>
<h2>Moje rezerwacje</h2>
<p class="text-muted">Nadchodzące wizyty i historia Twoich rezerwacji.</p>

<?php if ($future === [] && $history === []): ?>
    <div class="empty-state">
        <p class="mb-2">Nie masz jeszcze żadnych rezerwacji.</p>
        <a class="btn btn-primary" href="/?page=offer">Przejdź do oferty</a>
    </div>
<?php else: ?>
    <section class="mb-4">
        <h3 class="h4 mb-3">Nadchodzące</h3>

        <?php if ($future === []): ?>
            <p class="text-muted">Brak nadchodzących rezerwacji.</p>
        <?php else: ?>
            <div class="table-wrap admin-table-wrap">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Usługa</th>
                            <th scope="col">Pracownik</th>
                            <th scope="col">Data</th>
                            <th scope="col">Godzina</th>
                            <th scope="col">Cena</th>
                            <th scope="col">Status</th>
                            <th scope="col">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($future as $row): ?>
                            <?php $badgeClass = $statusClasses[$row['status']] ?? 'text-bg-secondary'; ?>
                            <tr>
                                <td data-label="Usługa"><?= htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Pracownik"><?= htmlspecialchars($row['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Data"><?= htmlspecialchars(date('d.m.Y', strtotime((string) $row['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Godzina"><?= htmlspecialchars(substr((string) $row['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Cena"><?= formatPrice($row['final_price']) ?></td>
                                <td data-label="Status">
                                    <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td data-label="Akcje">
                                    <a class="btn btn-outline-light btn-sm"
                                       href="/?page=reservation&amp;id=<?= (int) $row['id'] ?>">Szczegóły</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h3 class="h4 mb-3">Historia</h3>

        <?php if ($history === []): ?>
            <p class="text-muted">Historia jest jeszcze pusta.</p>
        <?php else: ?>
            <div class="table-wrap admin-table-wrap">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Usługa</th>
                            <th scope="col">Pracownik</th>
                            <th scope="col">Data</th>
                            <th scope="col">Godzina</th>
                            <th scope="col">Cena</th>
                            <th scope="col">Status</th>
                            <th scope="col">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                            <?php $badgeClass = $statusClasses[$row['status']] ?? 'text-bg-secondary'; ?>
                            <tr>
                                <td data-label="Usługa"><?= htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Pracownik"><?= htmlspecialchars($row['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Data"><?= htmlspecialchars(date('d.m.Y', strtotime((string) $row['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Godzina"><?= htmlspecialchars(substr((string) $row['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Cena"><?= formatPrice($row['final_price']) ?></td>
                                <td data-label="Status">
                                    <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td data-label="Akcje">
                                    <a class="btn btn-outline-light btn-sm"
                                       href="/?page=reservation&amp;id=<?= (int) $row['id'] ?>">Szczegóły</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
