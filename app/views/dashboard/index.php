<h2>Panel</h2>
<p class="text-muted">
    Zalogowany pracownik:
    <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>
</p>

<section class="mb-4">
    <h3>Dziś</h3>
    <?php
    $rows = $todayRows;
    $emptyMessage = 'Brak wizyt na dziś';
    require BASE_PATH . '/app/views/dashboard/_table.php';
    ?>
</section>

<section class="mb-4">
    <h3>Przyszłe</h3>
    <?php
    $rows = $futureRows;
    $emptyMessage = 'Brak przyszłych wizyt';
    require BASE_PATH . '/app/views/dashboard/_table.php';
    ?>
</section>

<section>
    <h3>Historia</h3>
    <?php
    $rows = $historyRows;
    $emptyMessage = 'Brak historii';
    require BASE_PATH . '/app/views/dashboard/_table.php';
    ?>
</section>
