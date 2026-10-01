// Podstawowa walidacja formularza rejestracji po stronie przeglądarki.
const form = document.querySelector('#register-form');

if (form) {
    const password = document.querySelector('#password');
    const confirmation = document.querySelector('#password_confirmation');

    function checkPasswords() {
        if (password.value !== confirmation.value) {
            confirmation.setCustomValidity('Hasła muszą być takie same.');
        } else {
            confirmation.setCustomValidity('');
        }
    }

    password.addEventListener('input', checkPasswords);
    confirmation.addEventListener('input', checkPasswords);
    form.addEventListener('submit', checkPasswords);
}
