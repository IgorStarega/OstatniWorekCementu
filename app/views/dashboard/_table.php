<?php
$statusClasses = [
    'oczekująca'   => 'text-bg-warning',
    'potwierdzona' => 'text-bg-info',
    'zrealizowana' => 'text-bg-success',
    'anulowana'    => 'text-bg-secondary',
];
?>
<?php if ($rows === []): ?>
    <p class="text-muted mb-0"><?= htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') ?></p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th scope="col">Godzina</th>
                    <th scope="col">Klient</th>
                    <th scope="col">Usługa</th>
                    <th scope="col">Czas trwania</th>
                    <th scope="col">Cena</th>
                    <th scope="col">Status</th>
                    <th scope="col">Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $badgeClass = $statusClasses[$row['status']] ?? 'text-bg-secondary'; ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars(date('d.m.Y', strtotime((string) $row['reservation_date'])), ENT_QUOTES, 'UTF-8') ?>,
                            <?= htmlspecialchars(substr((string) $row['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>–<?= htmlspecialchars(substr((string) $row['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['client_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars((string) ($row['client_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= formatDuration((int) $row['duration_minutes']) ?></td>
                        <td><?= formatPrice($row['final_price']) ?></td>
                        <td>
                            <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'oczekująca'): ?>
                                <form method="post" action="/?page=dashboard" class="d-flex flex-wrap gap-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" name="status" value="potwierdzona" class="btn btn-outline-success btn-sm" style="min-height:44px">Potwierdź</button>
                                    <button type="submit" name="status" value="anulowana" class="btn btn-outline-danger btn-sm" style="min-height:44px">Anuluj</button>
                                </form>
                            <?php elseif ($row['status'] === 'potwierdzona'): ?>
                                <form method="post" action="/?page=dashboard">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" name="status" value="zrealizowana" class="btn btn-outline-light btn-sm" style="min-height:44px">Zrealizuj</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
