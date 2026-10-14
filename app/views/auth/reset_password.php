<h2>Ustaw nowe hasło</h2>

<?php if ($message !== ''): ?>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($reset !== null): ?>
    <form method="post" action="/?page=reset-password">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

        <label for="new_password">Nowe hasło (od 8 do 72 znaków)</label>
        <input id="new_password" name="new_password" type="password" minlength="8" maxlength="72"
               autocomplete="new-password" required>

        <label for="new_password_confirmation">Powtórz nowe hasło</label>
        <input id="new_password_confirmation" name="new_password_confirmation" type="password" minlength="8" maxlength="72"
               autocomplete="new-password" required>

        <button type="submit">Zapisz nowe hasło</button>
    </form>
<?php endif; ?>

<p><a href="/?page=forgot-password">Wygeneruj nowy link</a> · <a href="/?page=login">Wróć do logowania</a></p>
