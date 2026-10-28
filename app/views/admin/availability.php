<?php
// day_of_week: 1 = poniedziałek, 7 = niedziela (konwencja projektu).
$dayNames = [
    1 => 'Poniedziałek',
    2 => 'Wtorek',
    3 => 'Środa',
    4 => 'Czwartek',
    5 => 'Piątek',
    6 => 'Sobota',
    7 => 'Niedziela',
];
?>
<div class="admin-toolbar">
    <div>
        <h2>Dostępność</h2>
        <p class="text-muted mb-0">Godziny pracy pracownika w tygodniu. Dni numerowane od 1 (poniedziałek) do 7 (niedziela).</p>
    </div>
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
            <input type="hidden" name="page" value="admin-availability">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="availability-employee">Pracownik</label>
                    <select class="form-select" id="availability-employee" name="employee_id">
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>"
                                <?= $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $employee['first_name'] . ' ' . (string) $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-light mt-md-0 mt-2">Pokaż dostępność</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selectedEmployee !== null): ?>
        <div class="admin-section">
            <h3>
                Godziny pracy:
                <?= htmlspecialchars((string) $selectedEmployee['first_name'] . ' ' . (string) $selectedEmployee['last_name'], ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <div class="admin-table-wrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Dzień</th>
                            <th scope="col">Godziny</th>
                            <th scope="col" class="text-end">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dayNames as $dayOfWeek => $dayName): ?>
                            <?php $window = $availability[$dayOfWeek] ?? null; ?>
                            <tr>
                                <td data-label="Dzień">
                                    <strong><?= htmlspecialchars($dayName, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small class="text-muted">(<?= (int) $dayOfWeek ?>)</small>
                                </td>
                                <td data-label="Godziny">
                                    <?php if ($window !== null): ?>
                                        <?= htmlspecialchars(
                                            substr((string) $window['start_time'], 0, 5)
                                            . '–'
                                            . substr((string) $window['end_time'], 0, 5),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    <?php else: ?>
                                        wolne
                                    <?php endif; ?>
                                </td>
                                <td data-label="Akcje">
                                    <?php if ($window !== null): ?>
                                        <form method="post" action="/?page=admin-availability"
                                              class="admin-actions justify-content-end"
                                              data-confirm="Czy usunąć dostępność w dniu <?= htmlspecialchars($dayName, ENT_QUOTES, 'UTF-8') ?>?">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="employee_id" value="<?= (int) $selectedEmployeeId ?>">
                                            <input type="hidden" name="day_of_week" value="<?= (int) $dayOfWeek ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="/?page=admin-availability"
                                              class="admin-actions justify-content-end flex-wrap">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="upsert">
                                            <input type="hidden" name="employee_id" value="<?= (int) $selectedEmployeeId ?>">
                                            <input type="hidden" name="day_of_week" value="<?= (int) $dayOfWeek ?>">

                                            <label class="visually-hidden" for="availability-start-<?= (int) $dayOfWeek ?>">
                                                Godzina rozpoczęcia — <?= htmlspecialchars($dayName, ENT_QUOTES, 'UTF-8') ?>
                                            </label>
                                            <input class="form-control form-control-sm" type="time"
                                                   id="availability-start-<?= (int) $dayOfWeek ?>"
                                                   name="start_time"
                                                   value="<?= $formDay === $dayOfWeek ? htmlspecialchars($formStart, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                   required>

                                            <label class="visually-hidden" for="availability-end-<?= (int) $dayOfWeek ?>">
                                                Godzina zakończenia — <?= htmlspecialchars($dayName, ENT_QUOTES, 'UTF-8') ?>
                                            </label>
                                            <input class="form-control form-control-sm" type="time"
                                                   id="availability-end-<?= (int) $dayOfWeek ?>"
                                                   name="end_time"
                                                   value="<?= $formDay === $dayOfWeek ? htmlspecialchars($formEnd, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                   required>

                                            <button type="submit" class="btn btn-sm btn-primary">Dodaj</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
