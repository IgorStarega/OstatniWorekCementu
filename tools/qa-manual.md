# QA — testy manualne

Uzupełnienie `tools/qa-smoke.sh` (automat). Rzeczy, których nie da się sprawdzić
curl em: ocena wizualna i użyteczność. Wymagania wstępne: `docker compose up -d`,
`APP_ENV=local`, pusty `SMTP_HOST`, dane testowe z `database/database.sql`
(hasło kont: `Haslo123!`).

Adres: <http://localhost:8082/>

## Widoki i formularze

- [ ] **Strona główna** — nagłówek, nawigacja i treść czytelne, brak rozjechanych elementów.
- [ ] **Rejestracja** — wszystkie pola z etykietami, kolejność logiczna, hasło ukryte.
- [ ] **Logowanie** — komunikat błędu widoczny i zrozumiały; link „Nie pamiętam hasła" działa.
- [ ] **Reset hasła** — w trybie `local` po wysłaniu formularza pojawia się link do resetu.
- [ ] **Weryfikacja e-mail** — po rejestracji w `local` link potwierdzający jest widoczny na ekranie.
- [ ] **Profil** — formularz danych i formularz zmiany hasła rozdzielone; po zapisie widać
      komunikat „Dane profilu zostały zapisane.".
- [ ] **Walidacja** — puste pola, za krótkie imię, błędny e-mail i telefon pokazują czytelne
      komunikaty przy polach/formularzu.
- [ ] **Błędy** — strony 403/404/500 wyglądają spójnie i nie zdradzają szczegółów technicznych.

## Responsywność

- [ ] **Telefon (≈375 px)** — brak poziomego przewijania, przyciski nie uciskają się,
      formularze i tabela rezerwacji czytelne.
- [ ] **Tablet (≈768 px)** i **desktop (≥1280 px)** — układ stabilny, nic się nie rozjeżdża.

## Spójność

- [ ] **Polskie znaki** — brak „krzaków" w nagłówkach, komunikatach i danych z bazy.
- [ ] **CSS** — strona ładuje `/css/style.css` (brak 404 w zakładce Network).
- [ ] **Sesja** — po wylogowaniu przycisk „Wstecz" nie pokazuje danych konta.
- [ ] **Komunikaty** — sukces jest informacją (`role="status"`), błąd alertem (`role="alert"`).
