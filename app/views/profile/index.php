<h2>Mój profil</h2>

<?php if ($message !== ''): ?>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<form method="post" action="/?page=profile" id="profile-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <label for="first_name">Imię</label>
    <input id="first_name" name="first_name" type="text" minlength="2" maxlength="50" autocomplete="given-name" required
           value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>">

    <label for="last_name">Nazwisko</label>
    <input id="last_name" name="last_name" type="text" minlength="2" maxlength="50" autocomplete="family-name" required
           value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>">

    <label for="phone">Numer telefonu</label>
    <input id="phone" name="phone" type="tel" minlength="7" maxlength="20" autocomplete="tel" required
           placeholder="np. 500600700 lub +48 500 600 700"
           pattern="^\+?[0-9\s\-]{7,20}$"
           value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">

    <button type="submit">Zapisz zmiany</button>
</form>
