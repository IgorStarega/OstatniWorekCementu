<h2>Rejestracja</h2>

<?php if ($message !== ''): ?>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<form method="post" action="/?page=register" id="register-form">
    <label for="first_name">Imię</label>
    <input id="first_name" name="first_name" type="text" maxlength="50" autocomplete="given-name" required
           value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>">

    <label for="last_name">Nazwisko</label>
    <input id="last_name" name="last_name" type="text" maxlength="50" autocomplete="family-name" required
           value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>">

    <label for="email">Adres e-mail</label>
    <input id="email" name="email" type="email" maxlength="100" autocomplete="email" required
           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">

    <label for="phone">Numer telefonu</label>
    <input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" required
           value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">

    <label for="password">Hasło (minimum 8 znaków)</label>
    <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>

    <label for="password_confirmation">Powtórz hasło</label>
    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8"
           autocomplete="new-password" required>

    <button type="submit">Utwórz konto</button>
</form>

<p>Masz już konto? <a href="/?page=login">Zaloguj się</a></p>
