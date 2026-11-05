# QA — testy manualne

Uzupełnienie `tools/qa-smoke.sh` (automat). Rzeczy, których nie da się sprawdzić
curl em: ocena wizualna i użyteczność. Wymagania wstępne: `docker compose up -d`,
`APP_ENV=local` (linki potwierdzenia i resetu są widoczne na ekranie), dane testowe z `database/database.sql`
(hasło kont: `Haslo123!`).

Adres: <http://localhost:8082/>

## Widoki i formularze

- [ ] **Strona główna** — nagłówek, nawigacja i treść czytelne, brak rozjechanych elementów.
- [ ] **Rejestracja** — wszystkie pola z etykietami, kolejność logiczna, hasło ukryte.
- [ ] **Logowanie** — komunikat błędu widoczny i zrozumiały; link „Nie pamiętam hasła" działa.
- [ ] **Reset hasła** — po wysłaniu formularza pojawia się link do resetu.
- [ ] **Weryfikacja e-mail** — po rejestracji link potwierdzający jest widoczny na ekranie.
- [ ] **Profil** — formularz danych i formularz zmiany hasła rozdzielone; po zapisie widać
      komunikat „Dane profilu zostały zapisane.".
- [ ] **Walidacja** — puste pola, za krótkie imię, błędny e-mail i telefon pokazują czytelne
      komunikaty przy polach/formularzu.
- [ ] **Błędy** — strony 403/404/500 wyglądają spójnie i nie zdradzają szczegółów technicznych.

## Panel administratora

- [ ] **Dostęp** — administrator widzi link „Panel administratora”, klient i gość nie.
- [ ] **Kategorie** — dodanie, edycja i usunięcie kategorii; usunięcie kategorii z usługami
      pokazuje komunikat z liczbą, a formularz po błędzie zachowuje wpisane wartości.
- [ ] **Usługi** — cena `0.00` przechodzi, cena ujemna i tekst są odrzucane, czas `0` odrzucony;
      usługa nieaktywna ma wyraźną plakietkę, a usługa z rezerwacjami nie daje się usunąć.
- [ ] **Pracownicy** — nowy pracownik powstaje razem z kontem, duplikat e-maila jest odrzucany,
      dezaktywacja blokuje logowanie, usunięcie pracownika z rezerwacjami pokazuje komunikat.
- [ ] **Przypisania usług** — zaznaczenie kilku usług zapisuje je bez duplikatów, odznaczenie
      wszystkich czyści przypisania, a podmienione ID usługi jest odrzucane.
- [ ] **Wygląd** — industrialny ciemny motyw (grafit + żółty akcent) jest spójny na wszystkich
      stronach, a panel działa na 375 px.

## Panel administratora — moduły podstawowe

- [ ] **Dashboard** — kafelki pokazują aktualne liczby, sekcja statystyk ma wykres statusów,
      przychód, TOP 5 usług i obłożenie pracowników; kliknięcie słupka filtruje rezerwacje.
- [ ] **Użytkownicy** — lista, wyszukiwanie i filtry działają; zmiana roli oraz blokowanie
      i odblokowanie konta pokazują komunikat, własnego konta nie da się zablokować.
- [ ] **Rezerwacje** — lista i filtry (data, status, pracownik, szukanie) działają, szczegóły
      pokazują dane klienta, zmianę statusu i historię zmian.

## Panel administratora — moduły nowe

- [ ] **Dostępność** — ustaw pon–pt 8:00–16:00 dla Jana Kowalskiego,
      sprawdź w bazie 5 wierszy w `employee_availability`,
      spróbuj ustawić odwrócone godziny (16:00–8:00) → komunikat walidacji.
- [ ] **Dni wolne** — dodaj 24.12.2026 pracownikowi, spróbuj dodać drugi raz
      ten sam dzień → komunikat o duplikacie, nie błąd SQL.
- [ ] **Log operacji** — po zmianie statusu rezerwacji sprawdź, czy wpis
      pojawił się na liście (autor, akcja, data).
- [ ] **Eksport CSV** — pobierz plik, otwórz w Excelu/LibreOffice:
      polskie znaki poprawne, separator `;`, brak `Ã³`.
- [ ] **Paginacja** — jeśli lista ma >20 pozycji, przejdź na stronę 2,
      sprawdź czy filtry się zachowały.

## Responsywność

- [ ] **Telefon (≈375 px)** — brak poziomego przewijania, przyciski nie uciskają się,
      formularze i tabela rezerwacji czytelne.
- [ ] **Tablet (≈768 px)** i **desktop (≥1280 px)** — układ stabilny, nic się nie rozjeżdża.

## Spójność

- [ ] **Polskie znaki** — brak „krzaków" w nagłówkach, komunikatach i danych z bazy.
- [ ] **CSS** — strona ładuje `/css/style.css` (brak 404 w zakładce Network).
- [ ] **Sesja** — po wylogowaniu przycisk „Wstecz" nie pokazuje danych konta.
- [ ] **Komunikaty** — sukces jest informacją (`role="status"`), błąd alertem (`role="alert"`).
