<div class="admin-toolbar">
    <div>
        <h2>Kategorie</h2>
        <p class="text-muted mb-0">Grupuj usługi, aby klienci łatwiej znajdowali ofertę.</p>
    </div>
    <?php if ($editingId > 0): ?>
        <a class="btn btn-outline-light btn-sm" href="/?page=admin-categories">Anuluj edycję</a>
    <?php endif; ?>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<div class="admin-section">
    <h3><?= $editingId > 0 ? 'Edytuj kategorię' : 'Dodaj kategorię' ?></h3>

    <form method="post" action="/?page=admin-categories" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editingId > 0 ? 'update' : 'create' ?>">
        <?php if ($editingId > 0): ?>
            <input type="hidden" name="id" value="<?= (int) $editingId ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <label class="form-label" for="category-name">Nazwa</label>
                <input class="form-control" id="category-name" name="name" type="text"
                       minlength="2" maxlength="100" required
                       value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-7">
                <label class="form-label" for="category-description">Opis (opcjonalnie)</label>
                <textarea class="form-control" id="category-description" name="description"
                          rows="3"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">
            <?= $editingId > 0 ? 'Zapisz zmiany' : 'Dodaj kategorię' ?>
        </button>
    </form>
</div>

<div class="admin-section">
    <h3>Lista kategorii</h3>

    <?php if ($categories === []): ?>
        <p class="admin-empty mb-0">Nie ma jeszcze żadnej kategorii. Dodaj pierwszą powyżej.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Opis</th>
                        <th scope="col">Usługi</th>
                        <th scope="col" class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td data-label="Nazwa"><strong><?= htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td data-label="Opis">
                                <?= $category['description'] !== null && $category['description'] !== ''
                                    ? htmlspecialchars((string) $category['description'], ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td data-label="Usługi"><span class="badge text-bg-secondary"><?= (int) $category['services_count'] ?></span></td>
                            <td data-label="Akcje">
                                <div class="admin-actions justify-content-end">
                                    <a class="btn btn-sm btn-outline-light"
                                       href="/?page=admin-categories&amp;edit=<?= (int) $category['id'] ?>">Edytuj</a>

                                    <form method="post" action="/?page=admin-categories"
                                          data-confirm="Czy na pewno usunąć kategorię „<?= htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8') ?>”?">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
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
