<?php
// Wspólny wskaźnik kroków procesu rezerwacji.
$bookingSteps = [
    'service'  => 'Usługa',
    'employee' => 'Pracownik',
    'date'     => 'Data',
    'time'     => 'Godzina',
    'confirm'  => 'Potwierdzenie',
];
$bookingStepKeys = array_keys($bookingSteps);
$bookingCurrentIndex = array_search($currentStep ?? 'service', $bookingStepKeys, true);
$bookingCurrentIndex = $bookingCurrentIndex === false ? 0 : $bookingCurrentIndex;
?>
<ol class="booking-steps list-unstyled d-flex flex-wrap gap-2 mb-4">
    <?php foreach ($bookingSteps as $stepKey => $stepLabel): ?>
        <?php
        $stepIndex = array_search($stepKey, $bookingStepKeys, true);
        $stepNumber = (int) $stepIndex + 1;
        ?>
        <li class="booking-step">
            <?php if ($stepIndex < $bookingCurrentIndex): ?>
                <a class="link-light" href="/?page=booking&amp;step=<?= htmlspecialchars($stepKey, ENT_QUOTES, 'UTF-8') ?>">
                    <?= $stepNumber ?>. <?= htmlspecialchars($stepLabel, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php elseif ($stepIndex === $bookingCurrentIndex): ?>
                <span class="is-active fw-bold" aria-current="step">
                    <?= $stepNumber ?>. <?= htmlspecialchars($stepLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php else: ?>
                <span class="text-muted">
                    <?= $stepNumber ?>. <?= htmlspecialchars($stepLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
