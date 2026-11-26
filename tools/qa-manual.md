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

## Rezerwacje i panele

- [ ] **Ścieżka klienta** — oferta → usługa → pracownik → data → godzina → potwierdzenie
      → zapis; po zapisie widać komunikat z numerem i listę „Moje rezerwacje".
- [ ] **Bramka kroków** — ręczne `?page=booking&step=confirm` bez wcześniejszych wyborów
      przekierowuje na brakujący krok i nie tworzy rezerwacji.
- [ ] **Moje rezerwacje** — sekcje nadchodzące/historia, badge statusów, szczegóły
      z historią zmian i przyciskiem anulowania tylko dla przyszłej wizyty.
- [ ] **Anulowanie** — przyszła wizyta anulowana przez POST; przeszła lub już anulowana
      jest odrzucana komunikatem serwera, a nie tylko ukryciem przycisku.
- [ ] **Panel pracownika** — sekcje dziś/przyszłe/historia, potwierdzenie, realizacja
      i anulowanie oczekującej wizyty.
- [ ] **Cudze ID** — `?page=reservation&id=<cudze>` daje 404; POST zmiany statusu
      cudzej wizyty daje 403 i nie zmienia bazy.
- [ ] **Telefon 375 px** — booking, „Moje rezerwacje" i panel pracownika bez poziomego
      przewijania; przyciski mają min. 44 px, empty states są czytelne.

## Responsywność

- [ ] **Telefon (≈375 px)** — brak poziomego przewijania, przyciski nie uciskają się,
      formularze i tabela rezerwacji czytelne.
- [ ] **Tablet (≈768 px)** i **desktop (≥1280 px)** — układ stabilny, nic się nie rozjeżdża.

## Spójność

- [ ] **Polskie znaki** — brak „krzaków" w nagłówkach, komunikatach i danych z bazy.
- [ ] **CSS** — strona ładuje `/css/style.css` (brak 404 w zakładce Network).
- [ ] **Sesja** — po wylogowaniu przycisk „Wstecz" nie pokazuje danych konta.
- [ ] **Komunikaty** — sukces jest informacją (`role="status"`), błąd alertem (`role="alert"`).

---

# Checklista przed prezentacją (etap 5)

Wykonuj po kolei na czystej bazie (`docker compose up -d`, świeży import).
Każdy punkt ma jawny oczekiwany wynik; jeśli wynik się różni — nie pokazuj tego
na prezentacji, tylko zgłoś i popraw.

## A. Ścieżka klienta — `anna.kowalska@example.com` / `Haslo123!`

- [ ] **1. Strona główna** — widać usługi z bazy, kategorie, liczniki (usługi,
      kategorie, pracownicy); nigdzie nie ma tekstu „w kolejnym etapie”.
- [ ] **2. Oferta i filtr kategorii** — `/?page=offer` pokazuje listę; klik kategorii
      zawęża usługi, „Wszystkie” wraca do pełnej listy; pusta kategoria ma komunikat.
- [ ] **3. Karta usługi** — `/?page=service&id=1` ma aktywny przycisk „Zarezerwuj”;
      klik od razu przechodzi do wyboru pracownika z ustawioną usługą.
- [ ] **4. Kalendarz** — krok daty ma dni z liczbą slotów; klik dnia z liczbą > 0
      pokazuje godziny; dzień pełny/urlop jest nieaktywny.
- [ ] **5. Potwierdzenie** — po zapisie w bazie `end_time = start_time + duration_minutes`,
      a `final_price` = cena usługi z chwili rezerwacji.
- [ ] **6. Moje rezerwacje → anulowanie** — status zmienia się na `anulowana`,
      wiersz zostaje w bazie (brak `DELETE`), komunikat jest czytelny.
- [ ] **7. Przełożenie** — nowa data i godzina zapisane; historia statusów
      wcześniejszych zmian nie znika, pojawia się wpis o zmianie terminu.
- [ ] **8. Cudze ID** — `?page=reservation&id=<id Tomasza>` i
      `?page=reschedule&id=<id Tomasza>` dają **404**, nie 403 i nie dane obcego.

## B. Pracownik — `jan.kowalski@example.com` / `Haslo123!`

- [ ] **9. „Dziś”** — panel pokazuje co najmniej jedną wizytę na dzisiejszą datę
      (id z seeda, np. rezerwacja #6).
- [ ] **10. Zmiana statusu własnej wizyty** — oczekująca → potwierdzona,
      potwierdzona → zrealizowana; komunikat sukcesu, badge się zmienia.
- [ ] **11. Cudza wizyta** — podmiana `id` w POST na wizytę innego pracownika
      daje **403** i nie zmienia statusu w bazie.

## C. Administrator — `admin@ostatniworekcementu.pl` / `Haslo123!`

- [ ] **12. Dashboard** — liczby kafelków są zgodne z `SELECT COUNT(*)` z bazy
      (klienci, pracownicy, usługi, rezerwacje, na dziś).
- [ ] **13. Rezerwacje** — filtry data/status/pracownik działają razem,
      paginacja zachowuje filtry, CSV pobiera się z polskimi znakami i `;`.
- [ ] **14. Usługi** — dezaktywacja usługi powoduje zniknięcie jej z oferty
      i ze strony głównej po odświeżeniu; aktywacja przywraca.
- [ ] **15. Dostępność + przerwy** — dodanie przerwy u Adama (np. 12:00–12:30)
      usuwa nachodzące godziny w kroku „godzina”; usunięcie przerwy je przywraca.
- [ ] **16. Log administratora** — po operacjach z punktów 12–15 są wpisy
      z autorem, akcją i datą; brak wpisów nie wywala strony.

## D. Responsywność — F12 → tryb urządzenia 375 px

- [ ] **17. Wszystkie widoki** — oferta, cały booking (5 kroków), panel klienta,
      panel pracownika (7 kolumn), `admin-reservations` (8 kolumn), kalendarz.
- [ ] **18. Przewijanie** — body nie ma poziomego scrolla; tabele przewijają się
      **w sobie** (`.table-wrap`), kalendarz zostaje siatką 7 kolumn.
- [ ] **19. Dotyk i formularze** — przyciski i sloty mają min. 44 px, pola mają
      100% szerokości, menu zwija się do przycisku hamburgera i nie wystaje.

## E. Odporność

- [ ] **20. SQL Injection** — wpisz `' OR 1=1 --` w e-mail logowania, pole komentarza
      i filtr admina; brak błędu SQL, brak logowania bez hasła.
- [ ] **21. XSS** — wpisz `<script>alert(1)</script>` w komentarz rezerwacji
      i nazwę kategorii; tekst jest widoczny dosłownie, skrypt się nie wykonuje.
- [ ] **22. CSRF** — `curl -X POST http://localhost:8082/?page=logout` bez tokenu
      daje **403**; to samo dla profilu i anulowania rezerwacji.
- [ ] **23. Bez JavaScriptu** — wyłącz JS w DevTools i przejdź rezerwację:
      siatka radio w kroku godziny działa (fallback), zapis się udaje.

## Znane ograniczenia (nie zgłaszaj jako błąd)

- Brak wysyłki e-maili — linki weryfikacji i resetu są pokazywane na ekranie
  (Cloudflare Tunnel nie przepuszcza SMTP, port 25 zablokowany).
- Przerwy w grafiku ma w danych demo tylko Adam — usługa 480 min w oknie 480 min
  nie ma miejsca na przerwę (świadome ograniczenie seeda).
- Kalendarz miesiąca wykonuje ok. 5 zapytań na dzień (≈150 na render) — opisany
  w README kompromis; przy tej skali nie wpływa na odczucie użytkownika.
- Po zmianie roli przez admina użytkownik musi się wylogować i zalogować,
  żeby nowa rola trafiła do sesji (sesja trzyma `user_id` i `role`).
- Testy 500 (błędny `DB_PASS`) wymagają restartu kontenera — poza automatem.
