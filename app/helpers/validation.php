<?php
// Wspólne pobieranie danych z formularzy i walidacja pól.
// Reguły trzymamy w jednym miejscu, żeby rejestracja, profil i reset hasła
// korzystały z dokładnie tych samych zasad.

// Bezpieczne pobranie tekstowego pola z POST — tablice i inne typy traktujemy jak brak wartości.
function postString(string $key): string
{
    return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
}

// Imię: wymagane, od 2 do 50 znaków. Zwraca komunikat błędu albo null.
function validationFirstName(string $firstName): ?string
{
    if ($firstName === '' || mb_strlen($firstName) < 2 || mb_strlen($firstName) > 50) {
        return 'Imię jest wymagane (od 2 do 50 znaków).';
    }

    return null;
}

// Nazwisko: wymagane, od 2 do 50 znaków. Zwraca komunikat błędu albo null.
function validationLastName(string $lastName): ?string
{
    if ($lastName === '' || mb_strlen($lastName) < 2 || mb_strlen($lastName) > 50) {
        return 'Nazwisko jest wymagane (od 2 do 50 znaków).';
    }

    return null;
}

// E-mail: poprawny format i maksymalnie 100 znaków. Zwraca komunikat błędu albo null.
function validationEmail(string $email, string $message = 'Podaj poprawny adres e-mail (maksymalnie 100 znaków).'): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        return $message;
    }

    return null;
}

// Telefon: od 7 do 20 znaków, dopuszczalne cyfry, spacje, myślniki i znak +.
function validationPhone(string $phone): ?string
{
    if ($phone === '' || !preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
        return 'Podaj poprawny numer telefonu (od 7 do 20 znaków, dopuszczalne cyfry, spacje, myślniki i znak +).';
    }

    return null;
}

// Hasło: od 8 do 72 znaków (72 to limit bcrypt). Zwraca komunikat błędu albo null.
function validationPassword(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Hasło musi mieć co najmniej 8 znaków.';
    }
    if (strlen($password) > 72) {
        return 'Hasło może mieć maksymalnie 72 znaki.';
    }

    return null;
}

// Nazwa kategorii usług: wymagana, od 2 do 100 znaków.
function validationCategoryName(string $name): ?string
{
    if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        return 'Nazwa kategorii jest wymagana (od 2 do 100 znaków).';
    }

    return null;
}

// Nazwa usługi: wymagana, od 2 do 150 znaków.
function validationServiceName(string $name): ?string
{
    if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 150) {
        return 'Nazwa usługi jest wymagana (od 2 do 150 znaków).';
    }

    return null;
}

// Czas trwania: liczba całkowita większa od zera (minuty).
function validationDurationMinutes(string $duration): ?string
{
    if (!preg_match('/^[0-9]{1,5}$/', $duration) || (int) $duration < 1) {
        return 'Czas trwania musi być liczbą całkowitą większą od 0 minut.';
    }

    return null;
}

// Cena: liczba nieujemna, maksymalnie dwa miejsca po przecinku/kropce.
function validationPrice(string $price): ?string
{
    if (!preg_match('/^[0-9]{1,8}([.,][0-9]{1,2})?$/', $price)) {
        return 'Cena musi być liczbą większą lub równą 0 (np. 0.00 albo 1200.50).';
    }

    return null;
}

// Ujednolica cenę do formatu bazy danych (kropka, zawsze dwa miejsca po przecinku).
function priceToDecimal(string $price): string
{
    return number_format((float) str_replace(',', '.', $price), 2, '.', '');
}
