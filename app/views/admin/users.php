<?php
$roleBadgeClasses = [
    'klient'    => 'text-bg-secondary',
    'pracownik' => 'text-bg-info',
    'admin'     => 'text-bg-warning',
];

$actionParams = ['page' => 'admin-users'];
if ($roleFilter !== null) {
    $actionParams['role'] = $roleFilter;
}
if ($activeFilter !== null) {
    $actionParams['active'] = (string) $activeFilter;
}
$actionUrl = '/?' . http_build_query($actionParams);
?>
<div class="admin-toolbar">
    <div>
        <h2>Użytkownicy</h2>
        <p class="text-muted mb-0">Konta klientów, pracowników i administratorów.</p>
    </div>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<div class="admin-section">
    <h3>Filtry</h3>
    <form method="get" action="/" class="admin-form">
        <input type="hidden" name="page" value="admin-users">
        <div class="row align-items-end g-2">
            <div class="col-md-4">
                <label class="form-label" for="users-role">Rola</label>
                <select class="form-select" id="users-role" name="role">
                    <option value="">— wszyscy —</option>
                    <?php foreach (User::ROLES as $roleOption): ?>
                        <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $roleFilter === $roleOption ? 'selected' : '' ?>>
                            <?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="users-active">Aktywny</label>
                <select class="form-select" id="users-active" name="active">
                    <option value="">— wszyscy —</option>
                    <option value="1" <?= $activeFilter === 1 ? 'selected' : '' ?>>aktywni (1)</option>
                    <option value="0" <?= $activeFilter === 0 ? 'selected' : '' ?>>zablokowani (0)</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-outline-light">Filtruj</button>
                    <a class="btn btn-outline-light" href="/?page=admin-users">Wyczyść filtry</a>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="admin-section">
    <h3>Lista użytkowników</h3>

    <?php if ($users === []): ?>
        <p class="admin-empty mb-0">Brak użytkowników spełniających kryteria.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Użytkownik</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Rola</th>
                        <th scope="col">Aktywny</th>
                        <th scope="col">Utworzono</th>
                        <th scope="col" class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $userId = (int) $user['id'];
                        $isSelf = $userId === currentUserId();
                        $userRole = (string) $user['role'];
                        $roleBadgeClass = $roleBadgeClasses[$userRole] ?? 'text-bg-secondary';
                        $isActive = (int) $user['is_active'] === 1;
                        $createdTimestamp = strtotime((string) $user['created_at']);
                        ?>
                        <tr>
                            <td data-label="Użytkownik">
                                <strong><?= htmlspecialchars((string) $user['first_name'] . ' ' . (string) $user['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ($isSelf): ?>
                                    <small class="text-muted">(to Ty)</small>
                                <?php endif; ?>
                            </td>
                            <td data-label="E-mail"><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="Rola">
                                <span class="badge <?= htmlspecialchars($roleBadgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td data-label="Aktywny">
                                <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-danger' ?>">
                                    <?= $isActive ? 'tak' : 'nie' ?>
                                </span>
                            </td>
                            <td data-label="Utworzono">
                                <?= $createdTimestamp !== false
                                    ? htmlspecialchars(date('d.m.Y', $createdTimestamp), ENT_QUOTES, 'UTF-8')
                                    : '—' ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="admin-actions justify-content-end flex-wrap">
                                    <form method="post" action="<?= htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') ?>"
                                          class="d-flex align-items-center gap-2"
                                          <?= $isSelf ? 'title="Nie możesz zmienić własnego konta"' : '' ?>>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="role">
                                        <input type="hidden" name="id" value="<?= $userId ?>">

                                        <label class="visually-hidden" for="user-role-<?= $userId ?>">Rola użytkownika</label>
                                        <select class="form-select form-select-sm" id="user-role-<?= $userId ?>" name="role"
                                            <?= $isSelf ? 'disabled title="Nie możesz zmienić własnego konta"' : '' ?>>
                                            <?php foreach (User::ROLES as $roleOption): ?>
                                                <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= $userRole === $roleOption ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-light"
                                            <?= $isSelf ? 'disabled title="Nie możesz zmienić własnego konta"' : '' ?>>
                                            Zmień rolę
                                        </button>
                                    </form>

                                    <form method="post" action="<?= htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') ?>"
                                          <?= $isSelf ? 'title="Nie możesz zmienić własnego konta"' : '' ?>>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $userId ?>">
                                        <button type="submit"
                                                class="btn btn-sm <?= $isActive ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                            <?= $isSelf ? 'disabled title="Nie możesz zmienić własnego konta"' : '' ?>>
                                            <?= $isActive ? 'Zablokuj' : 'Odblokuj' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
