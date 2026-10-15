<h2>Potwierdzenie adresu e-mail</h2>

<?php if ($message !== ''): ?>
    <p role="<?= $status === 'error' ? 'alert' : 'status' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($status === 'success'): ?>
    <p><a href="/?page=login">Przejdź do logowania</a></p>
<?php else: ?>
    <p>Nie dostałeś wiadomości? Podaj adres e-mail, a wyślemy link ponownie
       (kolejny link najwcześniej po minucie, maksymalnie 3 na godzinę).</p>

    <form method="post" action="/?page=verify-email">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <label for="email">Adres e-mail</label>
        <input id="email" name="email" type="email" maxlength="100" autocomplete="email" required
               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">

        <button type="submit">Wyślij link ponownie</button>
    </form>

    <p><a href="/?page=login">Wróć do logowania</a></p>
<?php endif; ?>
