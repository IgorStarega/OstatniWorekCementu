<h2>Panel pracownika</h2>
<p class="text-muted">
    Zalogowany pracownik:
    <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>
</p>

<section class="admin-section">
    <h3>Dziś — <?= htmlspecialchars(date('d.m.Y'), ENT_QUOTES, 'UTF-8') ?></h3>
    <?php
    $rows = $todayRows;
    $emptyMessage = 'Brak wizyt na dziś';
    require BASE_PATH . '/app/views/dashboard/_table.php';
    ?>
</section>

<section class="admin-section">
    <h3>Przyszłe wizyty</h3>
    <?php
    $rows = $futureRows;
    $emptyMessage = 'Brak zaplanowanych wizyt.';
    require BASE_PATH . '/app/views/dashboard/_table.php';
    ?>
</section>

<section class="admin-section">
    <h3>Historia</h3>
    <?php
    $rows = $historyRows;
    $emptyMessage = 'Brak zakończonych wizyt.';
    require BASE_PATH . '/app/views/dashboard/_table.php';
    ?>
</section>
