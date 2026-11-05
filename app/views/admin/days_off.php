<?php
// Dni wolne wybieramy dla konkretnego pracownika — jak w module dostępności.
$today = date('Y-m-d');
?>
<div class="admin-toolbar">
    <div>
        <h2>Dni wolne</h2>
        <p class="text-muted mb-0">Urlopy i jednorazowe dni wolne pracowników. Jeden wpis na dzień.</p>
    </div>
    <?php if ($selectedEmployee !== null): ?>
        <div class="admin-actions">
            <a class="btn btn-outline-light btn-sm"
               href="/?page=admin-availability&amp;employee_id=<?= (int) $selectedEmployeeId ?>">Dostępność tygodnia</a>
            <a class="btn btn-outline-light btn-sm"
               href="/?page=admin-employees">Pracownicy</a>
        </div>
    <?php endif; ?>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($employees === []): ?>
    <div class="admin-section">
        <p class="admin-empty mb-0">Nie ma jeszcze pracowników. Najpierw dodaj pracownika w module
            <a href="/?page=admin-employees">Pracownicy</a>.</p>
    </div>
<?php else: ?>
    <div class="admin-section">
        <h3>Wybierz pracownika</h3>
        <form method="get" action="/" class="admin-form">
            <input type="hidden" name="page" value="admin-days-off">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="days-off-employee">Pracownik</label>
                    <select class="form-select" id="days-off-employee" name="employee_id">
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>"
                                <?= $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $employee['first_name'] . ' ' . (string) $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-light mt-md-0 mt-2">Pokaż dni wolne</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selectedEmployee !== null): ?>
        <?php $selectedName = (string) $selectedEmployee['first_name'] . ' ' . (string) $selectedEmployee['last_name']; ?>

        <div class="admin-section">
            <h3>Dni wolne: <?= htmlspecialchars($selectedName, ENT_QUOTES, 'UTF-8') ?></h3>

            <?php if ($daysOff === []): ?>
                <p class="admin-empty mb-0">Brak zapisanych dni wolnych.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Data</th>
                                <th scope="col">Powód</th>
                                <th scope="col" class="text-end">Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daysOff as $dayOff): ?>
                                <?php
                                $dayOffDate = strtotime((string) $dayOff['date']);
                                $reason = trim((string) ($dayOff['reason'] ?? ''));
                                ?>
                                <tr>
                                    <td data-label="Data">
                                        <?= $dayOffDate !== false
                                            ? htmlspecialchars(date('d.m.Y', $dayOffDate), ENT_QUOTES, 'UTF-8')
                                            : '—' ?>
                                    </td>
                                    <td data-label="Powód">
                                        <?= $reason !== ''
                                            ? htmlspecialchars($reason, ENT_QUOTES, 'UTF-8')
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td data-label="Akcje">
                                        <form method="post" action="/?page=admin-days-off"
                                              class="admin-actions justify-content-end"
                                              data-confirm="Czy usunąć dzień wolny? Operacji nie można cofnąć.">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="employee_id" value="<?= (int) $selectedEmployeeId ?>">
                                            <input type="hidden" name="id" value="<?= (int) $dayOff['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-section">
            <h3>Dodaj dzień wolny</h3>

            <?php if (!$schemaReady): ?>
                <p class="admin-empty mb-0">Dodawanie dni wolnych jest niedostępne do czasu uruchomienia migracji 007.</p>
            <?php else: ?>
            <form method="post" action="/?page=admin-days-off" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="employee_id" value="<?= (int) $selectedEmployeeId ?>">

                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label" for="days-off-date">Data</label>
                        <input class="form-control" id="days-off-date" name="date" type="date"
                               min="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required
                               value="<?= htmlspecialchars($formDate, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label" for="days-off-reason">Powód (opcjonalnie)</label>
                        <input class="form-control" id="days-off-reason" name="reason" type="text" maxlength="150"
                               placeholder="np. urlop wypoczynkowy"
                               value="<?= htmlspecialchars($formReason, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Dodaj dzień wolny</button>
            </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
