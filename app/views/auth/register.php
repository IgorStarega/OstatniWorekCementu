<h2>Rejestracja</h2>

<?php if ($message !== ''): ?>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($verificationLink !== ''): ?>
    <p role="status">Tryb lokalny (brak wysyłki e-mail):
        <a href="<?= htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') ?>">link potwierdzający</a></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<form method="post" action="/?page=register" id="register-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <label for="first_name">Imię</label>
    <input id="first_name" name="first_name" type="text" minlength="2" maxlength="50" autocomplete="given-name" required
           value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>">

    <label for="last_name">Nazwisko</label>
    <input id="last_name" name="last_name" type="text" minlength="2" maxlength="50" autocomplete="family-name" required
           value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>">

    <label for="email">Adres e-mail</label>
    <input id="email" name="email" type="email" maxlength="100" autocomplete="email" required
           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">

    <label for="phone">Numer telefonu</label>
    <input id="phone" name="phone" type="tel" minlength="7" maxlength="20" autocomplete="tel" required
           placeholder="np. 500600700 lub +48 500 600 700"
           pattern="^\+?[0-9\s\-]{7,20}$"
           value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">

    <label for="password">Hasło (od 8 do 72 znaków)</label>
    <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>

    <label for="password_confirmation">Powtórz hasło</label>
    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="72"
           autocomplete="new-password" required>

    <button type="submit">Utwórz konto</button>
</form>

<p>Masz już konto? <a href="/?page=login">Zaloguj się</a></p>
