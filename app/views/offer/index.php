<h2>Oferta usług</h2>
<p class="text-muted">Przeglądaj usługi budowlane albo zawęź listę do jednej kategorii.</p>

<?php if ($categoryList === []): ?>
    <p>Na razie nie ma żadnych aktywnych usług. Zapraszamy wkrótce.</p>
<?php else: ?>
    <nav aria-label="Filtr kategorii">
        <ul class="nav nav-pills gap-2 mb-4">
            <li class="nav-item">
                <a class="nav-link<?= $categoryId === null ? ' active' : '' ?>" href="/?page=offer">Wszystkie</a>
            </li>
            <?php foreach ($categoryList as $category): ?>
                <li class="nav-item">
                    <a class="nav-link<?= $categoryId === (int) $category['id'] ? ' active' : '' ?>"
                       href="/?page=offer&amp;category=<?= (int) $category['id'] ?>">
                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                        <span class="badge text-bg-secondary ms-1"><?= (int) $category['services_count'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="row g-3 mb-4">
        <?php foreach ($categoryList as $category): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100<?= $categoryId === (int) $category['id'] ? ' border-primary' : '' ?>">
                    <div class="card-body">
                        <h3 class="card-title h5">
                            <a class="link-underline-opacity-0"
                               href="/?page=offer&amp;category=<?= (int) $category['id'] ?>">
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h3>
                        <?php if ((string) $category['description'] !== ''): ?>
                            <p class="card-text mb-2">
                                <?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                        <span class="badge text-bg-secondary">
                            <?= (int) $category['services_count'] ?> <?= ((int) $category['services_count'] === 1 ? 'usługa' : 'usług') ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 class="h4 mb-3">
        <?= $categoryId === null ? 'Wszystkie usługi' : 'Usługi w wybranej kategorii' ?>
    </h3>

    <?php if ($serviceList === []): ?>
        <p>Ta kategoria nie ma obecnie żadnej aktywnej usługi.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($serviceList as $service): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <span class="badge text-bg-secondary align-self-start mb-2">
                                <?= htmlspecialchars($service['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <h4 class="card-title h5 mb-1">
                                <a href="/?page=service&amp;id=<?= (int) $service['id'] ?>">
                                    <?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h4>
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="card-text mb-1">
                                <small class="text-muted">
                                    Czas trwania: <?= formatDuration((int) $service['duration_minutes']) ?>
                                </small>
                            </p>
                            <p class="card-text fw-bold mb-3"><?= formatPrice($service['price']) ?></p>
                            <a class="btn btn-primary align-self-start"
                               href="/?page=service&amp;id=<?= (int) $service['id'] ?>">Szczegóły</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
