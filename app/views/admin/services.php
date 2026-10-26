<div class="admin-toolbar">
    <div>
        <h2>Usługi</h2>
        <p class="text-muted mb-0">Oferta warsztatu: ceny, czas trwania i widoczność dla klientów.</p>
    </div>
    <?php if ($editingId > 0): ?>
        <a class="btn btn-outline-light btn-sm" href="/?page=admin-services">Anuluj edycję</a>
    <?php endif; ?>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($categories === []): ?>
    <p role="alert">Najpierw dodaj kategorię usług, aby móc tworzyć usługi.
        <a href="/?page=admin-categories">Przejdź do kategorii</a>.</p>
<?php endif; ?>

<div class="admin-section">
    <h3><?= $editingId > 0 ? 'Edytuj usługę' : 'Dodaj usługę' ?></h3>

    <form method="post" action="/?page=admin-services" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editingId > 0 ? 'update' : 'create' ?>">
        <?php if ($editingId > 0): ?>
            <input type="hidden" name="id" value="<?= (int) $editingId ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <label class="form-label" for="service-name">Nazwa</label>
                <input class="form-control" id="service-name" name="name" type="text"
                       minlength="2" maxlength="150" required
                       value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="service-category">Kategoria</label>
                <select class="form-select" id="service-category" name="category_id" required>
                    <option value="">— wybierz —</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"
                            <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mt-1">
            <div class="col-12">
                <label class="form-label" for="service-description">Opis (opcjonalnie)</label>
                <textarea class="form-control" id="service-description" name="description"
                          rows="3"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <div class="row mt-1">
            <div class="col-sm-4">
                <label class="form-label" for="service-duration">Czas trwania (min)</label>
                <input class="form-control" id="service-duration" name="duration_minutes" type="number"
                       min="1" max="100000" step="1" required
                       value="<?= htmlspecialchars($durationMinutes, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="service-price">Cena (zł)</label>
                <input class="form-control" id="service-price" name="price" type="text"
                       inputmode="decimal" placeholder="0.00" required
                       value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-sm-4 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" id="service-active" name="is_active" type="checkbox" value="1"
                        <?= $isActive ? 'checked' : '' ?>>
                    <label class="form-check-label" for="service-active">Usługa aktywna</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">
            <?= $editingId > 0 ? 'Zapisz zmiany' : 'Dodaj usługę' ?>
        </button>
    </form>
</div>

<div class="admin-section">
    <h3>Lista usług</h3>

    <?php if ($services === []): ?>
        <p class="admin-empty mb-0">Nie ma jeszcze żadnej usługi. Dodaj pierwszą powyżej.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Kategoria</th>
                        <th scope="col">Cena</th>
                        <th scope="col">Czas</th>
                        <th scope="col">Aktywna</th>
                        <th scope="col" class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <?php $isServiceActive = (int) $service['is_active'] === 1; ?>
                        <tr>
                            <td data-label="Nazwa">
                                <strong><?= htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ((int) $service['reservations_count'] > 0): ?>
                                    <br><small class="text-muted">rezerwacje: <?= (int) $service['reservations_count'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Kategoria"><?= htmlspecialchars((string) $service['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="Cena"><?= htmlspecialchars(number_format((float) $service['price'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?> zł</td>
                            <td data-label="Czas"><?= (int) $service['duration_minutes'] ?> min</td>
                            <td data-label="Aktywna">
                                <span class="badge-status <?= $isServiceActive ? 'is-active' : 'is-inactive' ?>">
                                    <?= $isServiceActive ? 'aktywna' : 'nieaktywna' ?>
                                </span>
                            </td>
                            <td data-label="Akcje">
                                <div class="admin-actions justify-content-end">
                                    <a class="btn btn-sm btn-outline-light"
                                       href="/?page=admin-services&amp;edit=<?= (int) $service['id'] ?>">Edytuj</a>

                                    <form method="post" action="/?page=admin-services">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <?= $isServiceActive ? 'Dezaktywuj' : 'Aktywuj' ?>
                                        </button>
                                    </form>

                                    <form method="post" action="/?page=admin-services"
                                          data-confirm="Czy na pewno usunąć usługę „<?= htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8') ?>”?">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
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
