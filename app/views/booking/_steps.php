<?php
// Wskaźnik kroków wspólny dla całego procesu rezerwacji.
$bookingSteps = [
    'service'  => 'Usługa',
    'employee' => 'Pracownik',
    'date'     => 'Data',
    'time'     => 'Godzina',
    'confirm'  => 'Potwierdzenie',
];
$bookingStepKeys = array_keys($bookingSteps);
$bookingStepIndex = array_search($currentStep ?? 'service', $bookingStepKeys, true);
$bookingStepIndex = $bookingStepIndex === false ? 0 : $bookingStepIndex;
?>
<ol class="booking-steps" aria-label="Kroki rezerwacji">
    <?php foreach ($bookingSteps as $stepKey => $stepLabel): ?>
        <?php
        $stepIndex = array_search($stepKey, $bookingStepKeys, true);
        $stepClass = $stepIndex < $bookingStepIndex ? 'is-done' : ($stepIndex === $bookingStepIndex ? 'is-active' : '');
        ?>
        <li class="booking-step <?= $stepClass ?>"<?= $stepIndex === $bookingStepIndex ? ' aria-current="step"' : '' ?>>
            <span class="booking-step-number"><?= (int) $stepIndex + 1 ?></span>
            <span class="booking-step-label"><?= htmlspecialchars($stepLabel, ENT_QUOTES, 'UTF-8') ?></span>
        </li>
    <?php endforeach; ?>
</ol>
