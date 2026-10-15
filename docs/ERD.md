# ERD — OstatniWorekCementu

Diagram bazy danych w składni [Mermaid](https://mermaid.js.org/syntax/entityRelationshipDiagram.html)
(GitHub renderuje go natywnie). Źródłem prawdy jest `database/database-bez-rekordow.sql` —
przy każdej zmianie schematu zaktualizuj ten plik i `database.sql`.

Starszy plik `docs/ERD.jpg` (7 tabel, wrzesień 2026) jest archiwalny i nie odpowiada
obecnemu schematowi (10 tabel).

```mermaid
erDiagram
    users {
        int id PK
        varchar first_name
        varchar last_name
        varchar email UK
        varchar password
        varchar phone
        enum role
        tinyint is_active
        int failed_attempts
        datetime locked_until
        datetime email_verified_at
        datetime created_at
    }

    employees {
        int id PK
        int user_id UK "FK -> users.id, CASCADE"
        text description
        tinyint is_active
    }

    employee_availability {
        int id PK
        int employee_id FK "FK -> employees.id, CASCADE"
        tinyint day_of_week
        time start_time
        time end_time
    }

    employee_services {
        int employee_id PK "FK -> employees.id, CASCADE"
        int service_id PK "FK -> services.id, CASCADE"
    }

    reservations {
        int id PK
        int user_id FK "FK -> users.id, RESTRICT"
        int employee_id FK "FK -> employees.id, RESTRICT"
        int service_id FK "FK -> services.id, RESTRICT"
        decimal final_price
        date reservation_date
        time start_time
        time end_time
        enum status
        text comment
        datetime created_at
    }

    services {
        int id PK
        int category_id FK "FK -> service_categories.id, CASCADE"
        varchar name
        text description
        int duration_minutes
        decimal price
        tinyint is_active
        datetime created_at
    }

    service_categories {
        int id PK
        varchar name
        text description
    }

    password_resets {
        int id PK
        varchar email "indeks, brak FK"
        char token_hash UK
        datetime expires_at
        datetime created_at
    }

    email_verifications {
        int id PK
        int user_id FK "FK -> users.id, CASCADE"
        char token_hash UK
        datetime expires_at
        datetime created_at
    }

    email_sends {
        int id PK
        varchar email "część indeksu email_purpose_created"
        varchar purpose "część indeksu email_purpose_created"
        datetime created_at "część indeksu email_purpose_created"
    }

    users ||--o| employees : "pracownik"
    users ||--o{ reservations : "rezerwuje"
    users ||--o{ email_verifications : "tokeny weryfikacji"
    employees ||--o{ employee_availability : "dostępność"
    employees ||--o{ employee_services : "obsługuje"
    employees ||--o{ reservations : "realizuje"
    services ||--o{ employee_services : "przypisanie"
    services ||--o{ reservations : "dotyczy"
    service_categories ||--o{ services : "kategoria"
```

## Klucze i ograniczenia spoza diagramu

* `employee_availability` — unikalny `uq_employee_day (employee_id, day_of_week)`.
* `reservations` — unikalny `uq_employee_slot (employee_id, reservation_date, start_time)`
  (blokuje podwójną rezerwację tego samego terminu).
* `password_resets` i `email_sends` nie mają kluczy obcych — powiązanie z kontem odbywa się
  po adresie e-mail (`password_resets.email`, `email_sends.email`).
* `email_sends` — indeks `email_purpose_created (email, purpose, created_at)`, po którym działają
  limity wysyłek (cooldown 60 s, maks. 3/godzinę) i retencja 24 h.
