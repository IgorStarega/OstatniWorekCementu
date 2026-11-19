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

<section class="mb-4">
    <h3>Przyszłe</h3>

    <?php if ($future === []): ?>
        <p class="text-muted">
            Brak nadchodzących rezerwacji.
            <a href="/?page=offer">Przejdź do oferty</a>
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
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
                            <td><?= htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y', strtotime((string) $row['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(substr((string) $row['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= formatPrice($row['final_price']) ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-outline-light btn-sm" href="/?page=reservation&amp;id=<?= (int) $row['id'] ?>">Szczegóły</a>
                                <form method="post" action="/?page=reservation" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" style="min-height:44px">Anuluj</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section>
    <h3>Historia</h3>

    <?php if ($history === []): ?>
        <p class="text-muted">
            Brak historii.
            <a href="/?page=offer">Przejdź do oferty</a>
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
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
                            <td><?= htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y', strtotime((string) $row['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(substr((string) $row['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= formatPrice($row['final_price']) ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-outline-light btn-sm" href="/?page=reservation&amp;id=<?= (int) $row['id'] ?>">Szczegóły</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
