(function () {
    const form = document.getElementById('forgot-form');
    if (!form) return;

    const email = document.getElementById('email');
    const emailConfirm = document.getElementById('emailConfirm');
    const submitBtn = document.getElementById('forgot-submit');

    const emailErr = document.getElementById('email-error');
    const emailConfirmErr = document.getElementById('emailConfirm-error');

    const isValidEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

    function setState(input, ok, msgEl, msg) {
        input.classList.remove('input-valid', 'input-invalid');
        if (ok) {
            input.classList.add('input-valid');
            msgEl.textContent = '';
        } else {
            input.classList.add('input-invalid');
            msgEl.textContent = msg || '';
        }
    }

    function validate() {
        const v1 = email.value.trim();
        const v2 = emailConfirm.value.trim();

        let valid = true;

        if (!v1) {
            valid = false;
            setState(email, false, emailErr, 'Veuillez saisir votre email.');
        } else if (!isValidEmail(v1)) {
            valid = false;
            setState(email, false, emailErr, 'Format d’email invalide.');
        } else {
            setState(email, true, emailErr, '');
        }

        if (!v2) {
            valid = false;
            setState(emailConfirm, false, emailConfirmErr, 'Veuillez confirmer votre email.');
        } else if (!isValidEmail(v2)) {
            valid = false;
            setState(emailConfirm, false, emailConfirmErr, 'Format d’email invalide.');
        } else if (v1 !== v2) {
            valid = false;
            setState(emailConfirm, false, emailConfirmErr, 'Les emails ne correspondent pas.');
        } else {
            setState(emailConfirm, true, emailConfirmErr, '');
        }

        submitBtn.disabled = !valid;
        return valid;
    }

    email.addEventListener('input', validate);
    emailConfirm.addEventListener('input', validate);

    form.addEventListener('submit', (e) => {
        if (!validate()) e.preventDefault();
    });

    // init
    validate();
})();
