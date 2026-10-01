<h2>Logowanie</h2>

<?php if (!empty($message)): ?>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<form method="post" action="/?page=login">
    <label for="email">Adres e-mail</label>
    <input id="email" name="email" type="email" maxlength="100" autocomplete="email" required
           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">

    <label for="password">Hasło</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>

    <button type="submit">Zaloguj się</button>
</form>

<p>Nie masz konta? <a href="/?page=register">Zarejestruj się</a></p>
