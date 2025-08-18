const loginForm = document.getElementById('login-form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const emailError = document.getElementById('email-error');
const passwordError = document.getElementById('password-error');
const authMessage = document.getElementById('auth-message');

function validateForm() {
    let valid = true;
    emailError.textContent = '';
    passwordError.textContent = '';

    if (!emailInput.value.trim()) {
        emailError.textContent = 'Veuillez saisir votre email.';
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
        emailError.textContent = 'Adresse email invalide.';
        valid = false;
    }

    if (!passwordInput.value.trim()) {
        passwordError.textContent = 'Veuillez saisir votre mot de passe.';
        valid = false;
    }

    return valid;
}

loginForm.addEventListener('submit', e => {
    e.preventDefault();
    if (!validateForm()) return;

    const formData = new FormData(loginForm);

    fetch(`${BASE_URL}/login`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        authMessage.style.display = 'block';
        authMessage.textContent = data.message || 'Réponse invalide';
        authMessage.className = 'auth-message ' + (data.status === 'success' ? 'success' : 'error');

        if (data.status === 'success' && data.redirect) {
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        }
    })
    .catch(() => {
        authMessage.style.display = 'block';
        authMessage.textContent = 'Erreur de connexion au serveur.';
        authMessage.className = 'auth-message error';
    });
});
