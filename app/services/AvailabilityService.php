<?php
// Wyliczanie wolnych terminów (slotów) pracownika dla jednej usługi
// w wybranym dniu. Serce procesu rezerwacji: kontroler zapyta o sloty,
// a klient wybierze jeden z nich.
class AvailabilityService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Sloty startu co 15 minut, mieszczące się w oknie pracy pracownika
    // i niekolidujące z jego rezerwacjami. Rezerwacje, dzień wolny i okno
    // pobieramy RAZ na dzień, a nie osobno dla każdego kandydata —
    // przy 32 slotach i 5 rezerwacjach robi to różnicę w liczbie zapytań.
    public function slotsFor(int $employeeId, int $serviceId, string $date): array
    {
        // Format daty sprawdzamy "w obie strony": createFromFormat przyjmie
        // np. '2026-02-31', ale round-trip już nie, więc podwójna data odpada.
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            return [];
        }

        // Wczoraj i starsze nie interesują klienta. Dzisiejszy dzień
        // przechodzi dalej — sloty z minęłą godziną odfiltrujemy niżej.
        if ($date < date('Y-m-d')) {
            return [];
        }

        // 1 = poniedziałek ... 7 = niedziela (konwencja employee_availability).
        $dayOfWeek = (int) date('N', strtotime($date));

        $statement = $this->pdo->prepare(
            'SELECT start_time, end_time FROM employee_availability
             WHERE employee_id = :employee_id AND day_of_week = :day_of_week
             LIMIT 1'
        );
        $statement->execute([
            'employee_id' => $employeeId,
            'day_of_week' => $dayOfWeek,
        ]);
        $window = $statement->fetch();

        // Brak okna = pracownik w ten dzień tygodnia nie pracuje.
        if ($window === false) {
            return [];
        }

        // Urlop/dzień wolny blokuje cały dzień, niezależnie od okna.
        if ((new EmployeeDayOff($this->pdo))->isDayOff($employeeId, $date)) {
            return [];
        }

        $statement = $this->pdo->prepare(
            'SELECT duration_minutes, is_active FROM services WHERE id = :id'
        );
        $statement->execute(['id' => $serviceId]);
        $service = $statement->fetch();

        // Nieaktywna usługa nie podlega rezerwacji, więc nie ma slotów.
        if ($service === false || (int) $service['is_active'] !== 1) {
            return [];
        }

        // Wszystkie rezerwacje pracownika z tego dnia jednym zapytaniem;
        // anulowane nie blokują terminu.
        $statement = $this->pdo->prepare(
            "SELECT start_time, end_time FROM reservations
             WHERE employee_id = :employee_id
               AND reservation_date = :date
               AND status != 'anulowana'"
        );
        $statement->execute([
            'employee_id' => $employeeId,
            'date'        => $date,
        ]);

        // Porównujemy minuty od północy (int), nie stringi — porównywanie
        // "9:00" z "09:00" to gwarantowany, trudny do zauważenia błąd.
        $busy = [];
        foreach ($statement->fetchAll() as $reservation) {
            $busy[] = [
                self::timeToMinutes($reservation['start_time']),
                self::timeToMinutes($reservation['end_time']),
            ];
        }

        $duration = (int) $service['duration_minutes'];
        $windowStart = self::timeToMinutes($window['start_time']);
        $windowEnd = self::timeToMinutes($window['end_time']);

        $isToday = $date === date('Y-m-d');
        $nowMinutes = (int) date('H') * 60 + (int) date('i');

        $slots = [];
        for ($start = $windowStart; $start + $duration <= $windowEnd; $start += 15) {
            $end = $start + $duration;

            // Dokładnie ta sama reguła nakładania co Reservation::hasConflict:
            // kandydat koliduje, gdy zaczyna się przed końcem rezerwacji
            // i kończy po jej początku.
            $conflicts = false;
            foreach ($busy as [$busyStart, $busyEnd]) {
                if ($start < $busyEnd && $end > $busyStart) {
                    $conflicts = true;
                    break;
                }
            }
            if ($conflicts) {
                continue;
            }

            // Dla dzisiejszego dnia nie proponujemy godziny, która już trwa.
            if ($isToday && $start <= $nowMinutes) {
                continue;
            }

            $slots[] = sprintf('%02d:%02d', intdiv($start, 60), $start % 60);
        }

        return $slots;
    }

    // 'HH:MM[:SS]' -> minuty od północy.
    private static function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + (int) ($parts[1] ?? 0);
    }
}
