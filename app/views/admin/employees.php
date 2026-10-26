<div class="admin-toolbar">
    <div>
        <h2>Pracownicy</h2>
        <p class="text-muted mb-0">Konta pracowników, dane kontaktowe i przypisane usługi.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-light btn-sm" href="/?page=admin-employee-services">Przypisz usługi</a>
        <?php if ($editingId > 0): ?>
            <a class="btn btn-outline-light btn-sm" href="/?page=admin-employees">Anuluj edycję</a>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<div class="admin-section">
    <h3><?= $editingId > 0 ? 'Edytuj pracownika' : 'Dodaj pracownika' ?></h3>

    <form method="post" action="/?page=admin-employees" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editingId > 0 ? 'update' : 'create' ?>">
        <?php if ($editingId > 0): ?>
            <input type="hidden" name="id" value="<?= (int) $editingId ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label" for="employee-first-name">Imię</label>
                <input class="form-control" id="employee-first-name" name="first_name" type="text"
                       minlength="2" maxlength="50" autocomplete="given-name" required
                       value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="employee-last-name">Nazwisko</label>
                <input class="form-control" id="employee-last-name" name="last_name" type="text"
                       minlength="2" maxlength="50" autocomplete="family-name" required
                       value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="employee-email">E-mail</label>
                <input class="form-control" id="employee-email" name="email" type="email"
                       maxlength="100" autocomplete="email" required
                       value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="employee-phone">Telefon</label>
                <input class="form-control" id="employee-phone" name="phone" type="tel"
                       minlength="7" maxlength="20" autocomplete="tel" required
                       placeholder="np. 500600700"
                       pattern="^\+?[0-9\s\-]{7,20}$"
                       value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="employee-description">Opis (opcjonalnie)</label>
                <textarea class="form-control" id="employee-description" name="description"
                          rows="3"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <?php if ($editingId === 0): ?>
                <div class="col-md-6">
                    <label class="form-label" for="employee-password">Hasło startowe</label>
                    <input class="form-control" id="employee-password" name="password" type="password"
                           minlength="8" maxlength="72" autocomplete="new-password" required>
                    <small class="text-muted">Od 8 do 72 znaków. Pracownik może je później zmienić.</small>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary mt-3">
            <?= $editingId > 0 ? 'Zapisz zmiany' : 'Dodaj pracownika' ?>
        </button>
    </form>
</div>

<div class="admin-section">
    <h3>Lista pracowników</h3>

    <?php if ($employees === []): ?>
        <p class="admin-empty mb-0">Nie ma jeszcze żadnego pracownika. Dodaj pierwszego powyżej.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Pracownik</th>
                        <th scope="col">Kontakt</th>
                        <th scope="col">Opis</th>
                        <th scope="col">Usługi</th>
                        <th scope="col">Aktywny</th>
                        <th scope="col" class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <?php
                        $isEmployeeActive = (int) $employee['is_active'] === 1;
                        $employeeDescription = trim((string) ($employee['description'] ?? ''));
                        if (mb_strlen($employeeDescription) > 90) {
                            $employeeDescription = mb_substr($employeeDescription, 0, 90) . '…';
                        }
                        ?>
                        <tr>
                            <td data-label="Pracownik">
                                <strong><?= htmlspecialchars((string) $employee['first_name'] . ' ' . (string) $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td data-label="Kontakt">
                                <div><?= htmlspecialchars((string) $employee['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars((string) ($employee['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td data-label="Opis">
                                <?= $employeeDescription !== ''
                                    ? htmlspecialchars($employeeDescription, ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td data-label="Usługi"><span class="badge text-bg-secondary"><?= (int) $employee['services_count'] ?></span></td>
                            <td data-label="Aktywny">
                                <span class="badge-status <?= $isEmployeeActive ? 'is-active' : 'is-inactive' ?>">
                                    <?= $isEmployeeActive ? 'aktywny' : 'nieaktywny' ?>
                                </span>
                            </td>
                            <td data-label="Akcje">
                                <div class="admin-actions justify-content-end">
                                    <a class="btn btn-sm btn-outline-light"
                                       href="/?page=admin-employees&amp;edit=<?= (int) $employee['id'] ?>">Edytuj</a>
                                    <a class="btn btn-sm btn-outline-info"
                                       href="/?page=admin-employee-services&amp;employee_id=<?= (int) $employee['id'] ?>">Usługi</a>

                                    <form method="post" action="/?page=admin-employees">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int) $employee['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <?= $isEmployeeActive ? 'Dezaktywuj' : 'Aktywuj' ?>
                                        </button>
                                    </form>

                                    <form method="post" action="/?page=admin-employees"
                                          data-confirm="Czy na pewno usunąć pracownika <?= htmlspecialchars((string) $employee['first_name'] . ' ' . (string) $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>?">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $employee['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
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
