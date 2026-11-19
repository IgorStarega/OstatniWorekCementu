// Podstawowa walidacja formularza rejestracji po stronie przeglądarki.
const form = document.querySelector('#register-form');

if (form) {
    const password = document.querySelector('#password');
    const confirmation = document.querySelector('#password_confirmation');

    if (password && confirmation) {
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
}

// Terminy pobierane przez AJAX z ?page=api-slots. Siatka radio pozostaje
// w HTML jako fallback — bez JavaScriptu formularz działa dokładnie tak samo.
const slotGrid = document.querySelector('#slot-grid');

if (slotGrid) {
    const slotError = document.querySelector('#slot-error');
    const slotForm = slotGrid.closest('form');
    const slotSubmit = slotForm ? slotForm.querySelector('button[type="submit"]') : null;
    const dateInput = document.querySelector('#booking-date');

    function showSlotError(message) {
        if (!slotError) {
            return;
        }

        slotError.textContent = message;
        slotError.classList.remove('d-none');
    }

    function clearSlotError() {
        if (!slotError) {
            return;
        }

        slotError.textContent = '';
        slotError.classList.add('d-none');
    }

    function renderSlots(slots) {
        slotGrid.replaceChildren();

        slots.forEach(function (slot) {
            const label = document.createElement('label');
            label.className = 'slot-option';

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type = 'radio';
            input.name = 'start_time';
            input.value = slot;
            input.required = true;

            const text = document.createElement('span');
            text.className = 'slot-option-time';
            text.textContent = slot;

            label.appendChild(input);
            label.appendChild(text);
            slotGrid.appendChild(label);
        });

        if (slotSubmit) {
            slotSubmit.disabled = slots.length === 0;
        }

        if (slots.length === 0) {
            showSlotError('Brak wolnych terminów w tym dniu. Wybierz inną datę.');
        } else {
            clearSlotError();
        }
    }

    function loadSlots(date) {
        if (!date) {
            return;
        }

        const url = new URL(slotGrid.dataset.apiBase, window.location.origin);
        url.searchParams.set('employee_id', slotGrid.dataset.employeeId || '');
        url.searchParams.set('service_id', slotGrid.dataset.serviceId || '');
        url.searchParams.set('date', date);

        clearSlotError();

        fetch(url.toString(), { credentials: 'same-origin' })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { response: response, data: data };
                });
            })
            .then(function (result) {
                if (!result.response.ok || result.data.ok !== true) {
                    showSlotError(result.data.error || 'Nie udało się pobrać terminów.');
                    return;
                }

                renderSlots(Array.isArray(result.data.slots) ? result.data.slots : []);
            })
            .catch(function () {
                // Błąd sieci lub odpowiedź, która nie jest JSON-em.
                showSlotError('Nie udało się połączyć z serwerem. Spróbuj ponownie.');
            });
    }

    // Odświeżenie przy wejściu (stan mógł się zmienić od wyrenderowania)
    // oraz po zmianie daty, jeśli selektor daty jest na tej stronie.
    loadSlots(slotGrid.dataset.date || '');

    if (dateInput) {
        dateInput.addEventListener('change', function () {
            loadSlots(dateInput.value);
        });
    }
}

// Potwierdzenie akcji destrukcyjnych (np. usunięcia kategorii) bez inline'owego JS.
document.addEventListener('submit', function (event) {
    const target = event.target;

    if (!(target instanceof HTMLFormElement)) {
        return;
    }

    const message = target.getAttribute('data-confirm');

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
