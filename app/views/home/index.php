<?php
$role = currentUserRole();

$heroButtons = [];
if ($role === null) {
    $heroButtons[] = ['href' => '/?page=offer', 'label' => 'Zobacz ofertę', 'class' => 'btn-primary'];
    $heroButtons[] = ['href' => '/?page=login', 'label' => 'Zaloguj się', 'class' => 'btn-outline-light'];
} elseif ($role === 'klient') {
    $heroButtons[] = ['href' => '/?page=offer', 'label' => 'Zobacz ofertę', 'class' => 'btn-primary'];
    $heroButtons[] = ['href' => '/?page=booking', 'label' => 'Zarezerwuj termin', 'class' => 'btn-outline-light'];
} else {
    $heroButtons[] = ['href' => '/?page=offer', 'label' => 'Zobacz ofertę', 'class' => 'btn-primary'];
}
?>
<section class="mb-5">
    <h1 class="display-5 mb-3"><?= htmlspecialchars($appName ?? 'Ostatni Worek Cementu', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead text-muted mb-4">
        Usługi budowlane od sprawdzonych fachowców — wybierz usługę i zarezerwuj dogodny termin online.
    </p>
    <div class="booking-actions">
        <?php foreach ($heroButtons as $button): ?>
            <a class="btn <?= htmlspecialchars($button['class'], ENT_QUOTES, 'UTF-8') ?>"
               href="<?= htmlspecialchars($button['href'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($button['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($serviceList === []): ?>
    <div class="empty-state">
        <h2 class="h4">Oferta jest chwilowo niedostępna</h2>
        <p class="mb-0">Nie mamy teraz aktywnych usług. Zapraszamy wkrótce — nasza oferta wróci, gdy tylko
            fachowcy potwierdzą dostępność.</p>
    </div>
<?php else: ?>
    <section class="mb-5">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Usługi</p>
                        <p class="display-6 mb-0"><?= count($serviceList) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Kategorie</p>
                        <p class="display-6 mb-0"><?= count($categoryList) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Pracownicy</p>
                        <p class="display-6 mb-0"><?= (int) $employeeCount ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="h3 mb-3">Kategorie usług</h2>
        <div class="row g-3">
            <?php foreach ($categoryList as $category): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
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
                                <?= (int) $category['services_count'] ?>
                                <?= ((int) $category['services_count'] === 1 ? 'usługa' : 'usług') ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="h3 mb-3">Polecane usługi</h2>
        <div class="row g-3">
            <?php foreach ($featuredServices as $service): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <span class="badge text-bg-secondary align-self-start mb-2">
                                <?= htmlspecialchars($service['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <h3 class="card-title h5 mb-1">
                                <?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
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
    </section>

    <section class="text-center">
        <h2 class="h4">Gotowy do działania?</h2>
        <p class="text-muted">Zobacz pełną ofertę i wybierz usługę dla siebie.</p>
        <a class="btn btn-primary" href="/?page=offer">Przejdź do oferty</a>
    </section>
<?php endif; ?>
