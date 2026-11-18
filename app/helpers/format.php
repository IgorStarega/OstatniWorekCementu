<?php
// Formatowanie wartości z bazy do widoków oferty (cena, czas trwania).
// Jedno miejsce, żeby lista i szczegóły usługi pokazywały je identycznie.

// Cena w złotych: 1200.00 -> "1 200,00 zł".
function formatPrice($price): string
{
    return number_format((float) $price, 2, ',', ' ') . ' zł';
}

// Czas trwania usługi: pełne godziny ("8 godz."), a przy niepełnych
// dopisujemy minuty ("1 godz. 30 min"); krótsze niż godzina — same minuty.
function formatDuration(int $minutes): string
{
    if ($minutes < 60) {
        return $minutes . ' min';
    }

    $hours = intdiv($minutes, 60);
    $rest = $minutes % 60;

    return $hours . ' godz.' . ($rest > 0 ? ' ' . $rest . ' min' : '');
}
