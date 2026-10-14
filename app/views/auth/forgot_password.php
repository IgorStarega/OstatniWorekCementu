<h2>Odzyskiwanie hasła</h2>

<?php if ($message !== ''): ?>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($resetLink !== ''): ?>
    <p role="status">Tryb lokalny (brak wysyłki e-mail): <a href="<?= htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') ?>">link do resetu hasła</a></p>
<?php endif; ?>

<form method="post" action="/?page=forgot-password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <label for="email">Adres e-mail</label>
    <input id="email" name="email" type="email" maxlength="100" autocomplete="email" required
           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">

    <button type="submit">Wyślij link resetujący</button>
</form>

<p>Pamiętasz hasło? <a href="/?page=login">Zaloguj się</a></p>
