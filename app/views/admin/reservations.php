<?php
// Wspólna mapa statusów rezerwacji na klasy Bootstrapa.
$statusClasses = [
    'oczekująca'   => 'text-bg-warning',
    'potwierdzona' => 'text-bg-info',
    'zrealizowana' => 'text-bg-success',
    'anulowana'    => 'text-bg-secondary',
];

// Filtry zachowujemy w linkach i formularzach, żeby nie zgubić kontekstu listy.
$filterParams = [];
foreach (['date_from', 'date_to', 'status', 'employee_id'] as $filterKey) {
    if (isset($filters[$filterKey]) && $filters[$filterKey] !== '') {
        $filterParams[$filterKey] = $filters[$filterKey];
    }
}
$filterQuery = http_build_query($filterParams);
$filterSuffix = $filterQuery === ''
    ? ''
    : '&amp;' . htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8');
?>

<?php if ($reservation === null): ?>
    <div class="admin-toolbar">
        <div>
            <h2>Rezerwacje</h2>
            <p class="text-muted mb-0">Terminy, statusy i obsługa zgłoszeń klientów.</p>
        </div>
    </div>

    <?php foreach ($errors as $error): ?>
        <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>

    <div class="admin-section">
        <h3>Filtry</h3>

        <form method="get" action="/" class="admin-form">
            <input type="hidden" name="page" value="admin-reservations">

            <div class="row">
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="filter-date-from">Data od</label>
                    <input class="form-control" id="filter-date-from" name="date_from" type="date"
                           value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="filter-date-to">Data do</label>
                    <input class="form-control" id="filter-date-to" name="date_to" type="date"
                           value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="filter-status">Status</label>
                    <select class="form-select" id="filter-status" name="status">
                        <option value="">— wszystkie —</option>
                        <?php foreach ($statuses as $statusOption): ?>
                            <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($filters['status'] ?? '') === $statusOption ? 'selected' : '' ?>>
                                <?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="filter-employee">Pracownik</label>
                    <select class="form-select" id="filter-employee" name="employee_id">
                        <option value="">— wszyscy —</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>"
                                <?= ($filters['employee_id'] ?? 0) === (int) $employee['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $employee['first_name'] . ' ' . (string) $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-actions mt-3">
                <button type="submit" class="btn btn-primary btn-sm">Filtruj</button>
                <a class="btn btn-outline-light btn-sm" href="/?page=admin-reservations">Wyczyść filtry</a>
            </div>
        </form>
    </div>

    <div class="admin-section">
        <h3>Lista rezerwacji</h3>

        <?php if ($reservations === []): ?>
            <p class="admin-empty mb-0">Brak rezerwacji spełniających kryteria.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Klient</th>
                            <th scope="col">Pracownik</th>
                            <th scope="col">Usługa</th>
                            <th scope="col">Data</th>
                            <th scope="col">Godzina</th>
                            <th scope="col">Cena</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $row): ?>
                            <?php
                            $rowStatus = (string) $row['status'];
                            $rowStatusClass = $statusClasses[$rowStatus] ?? 'text-bg-secondary';
                            ?>
                            <tr>
                                <td data-label="Klient">
                                    <strong><?= htmlspecialchars((string) $row['client_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars((string) $row['client_email'], ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td data-label="Pracownik"><?= htmlspecialchars((string) $row['employee_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Usługa"><?= htmlspecialchars((string) $row['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Data"><?= htmlspecialchars(date('d.m.Y', strtotime((string) $row['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Godzina"><?= htmlspecialchars(substr((string) $row['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Cena"><?= htmlspecialchars(number_format((float) $row['final_price'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?> zł</td>
                                <td data-label="Status">
                                    <span class="badge <?= htmlspecialchars($rowStatusClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($rowStatus, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td data-label="Akcje">
                                    <div class="admin-actions justify-content-end">
                                        <a class="btn btn-sm btn-outline-light"
                                           href="/?page=admin-reservations&amp;id=<?= (int) $row['id'] ?><?= $filterSuffix ?>">Szczegóły</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php
    $detailStatus = (string) $reservation['status'];
    $detailStatusClass = $statusClasses[$detailStatus] ?? 'text-bg-secondary';
    $comment = trim((string) ($reservation['comment'] ?? ''));
    ?>
    <div class="admin-toolbar">
        <div>
            <h2>Rezerwacja #<?= (int) $reservation['id'] ?></h2>
            <p class="text-muted mb-0">Szczegóły zgłoszenia klienta.</p>
        </div>
        <a class="btn btn-outline-light btn-sm"
           href="/?page=admin-reservations<?= $filterSuffix ?>">Wróć do listy</a>
    </div>

    <?php foreach ($errors as $error): ?>
        <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>

    <div class="admin-section">
        <h3>Dane rezerwacji</h3>

        <div class="row">
            <div class="col-md-6">
                <dl class="mb-0">
                    <dt>Klient</dt>
                    <dd><?= htmlspecialchars((string) $reservation['client_name'], ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt>E-mail</dt>
                    <dd><?= htmlspecialchars((string) $reservation['client_email'], ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt>Telefon</dt>
                    <dd><?= htmlspecialchars((string) ($reservation['client_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt>Pracownik</dt>
                    <dd><?= htmlspecialchars((string) $reservation['employee_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="mb-0">
                    <dt>Usługa</dt>
                    <dd><?= htmlspecialchars((string) $reservation['service_name'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= (int) $reservation['service_duration'] ?> min)</dd>

                    <dt>Termin</dt>
                    <dd>
                        <?= htmlspecialchars(date('d.m.Y', strtotime((string) $reservation['reservation_date'])), ENT_QUOTES, 'UTF-8') ?>,
                        <?= htmlspecialchars(substr((string) $reservation['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>–<?= htmlspecialchars(substr((string) $reservation['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                    </dd>

                    <dt>Cena</dt>
                    <dd><?= htmlspecialchars(number_format((float) $reservation['final_price'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?> zł</dd>

                    <dt>Status</dt>
                    <dd>
                        <span class="badge <?= htmlspecialchars($detailStatusClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($detailStatus, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        <dl class="mt-3 mb-0">
            <dt>Komentarz klienta</dt>
            <dd class="mb-0">
                <?php if ($comment === ''): ?>
                    <span class="text-muted">Brak komentarza.</span>
                <?php else: ?>
                    <?= nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')) ?>
                <?php endif; ?>
            </dd>
        </dl>
    </div>

    <div class="admin-section">
        <h3>Zmień status</h3>

        <form method="post" action="/?page=admin-reservations" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
            <input type="hidden" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="status_filter" value="<?= htmlspecialchars($filters['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="employee_id" value="<?= htmlspecialchars((string) ($filters['employee_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row align-items-end">
                <div class="col-sm-6 col-lg-4">
                    <label class="form-label" for="reservation-status">Nowy status</label>
                    <select class="form-select" id="reservation-status" name="status" required>
                        <?php foreach ($statuses as $statusOption): ?>
                            <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $detailStatus === $statusOption ? 'selected' : '' ?>>
                                <?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <button type="submit" class="btn btn-primary">Zmień status</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>
