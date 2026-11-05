<?php
// Kolory akcji spójne z resztą panelu (Bootstrap 5.3).
$actionClasses = [
    'create' => 'text-bg-success',
    'update' => 'text-bg-info',
    'delete' => 'text-bg-danger',
    'status' => 'text-bg-warning',
    'login'  => 'text-bg-secondary',
];
?>
<div class="admin-toolbar">
    <div>
        <h2>Log operacji</h2>
        <p class="text-muted mb-0">Ostatnie 100 udanych operacji wykonanych w panelu administratora.</p>
    </div>
    <a class="btn btn-outline-light btn-sm" href="/?page=admin-dashboard">Wróć do dashboardu</a>
</div>

<?php foreach ($errors ?? [] as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<div class="admin-section">
    <h3>Ostatnie operacje</h3>

    <?php if (empty($schemaReady)): ?>
        <p class="admin-empty mb-0">Log operacji jest niedostępny, dopóki tabela <code>admin_log</code> nie zostanie utworzona.</p>
    <?php elseif ($entries === []): ?>
        <p class="admin-empty mb-0">Brak zapisanych operacji.</p>
    <?php else: ?>
        <p class="text-muted">Wyświetlono <?= count($entries) ?> najnowszych wpisów.</p>
        <div class="admin-table-wrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Akcja</th>
                        <th scope="col">Encja</th>
                        <th scope="col">Szczegóły</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
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
                            <td data-label="Encja">
                                <?= htmlspecialchars((string) $entry['entity'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($entry['entity_id'] !== null): ?>
                                    <small class="text-muted">#<?= (int) $entry['entity_id'] ?></small>
                                <?php endif; ?>
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
