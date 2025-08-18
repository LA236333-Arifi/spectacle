document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("register-form");
  var messageBox = document.getElementById("auth-message");
  var submitBtn = form.querySelector(".btn-primary");

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    clearErrors();
    message("");
    disableSubmit(true);

    var nom = form.nom_utilisateur.value.trim();
    var prenom = form.prenom_utilisateur.value.trim();
    var email = form.mail_utilisateur.value.trim();
    var emailConfirm = form.mail_utilisateur_confirm.value.trim();
    var password = form.mdp_utilisateur.value;
    var passwordConfirm = form.mdp_utilisateur_confirm.value;
    var role = form.role_utilisateur.value;

    var firstErrorId = null;
    var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var passwordRe = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

    if (nom.length < 2) firstErrorId = err("nom", "nom-error", "Le nom est requis (min. 2 caractères).", firstErrorId);
    if (prenom.length < 2) firstErrorId = err("prenom", "prenom-error", "Le prénom est requis (min. 2 caractères).", firstErrorId);
    if (!emailRe.test(email)) firstErrorId = err("email", "email-error", "Email invalide.", firstErrorId);
    if (email !== emailConfirm) firstErrorId = err("email-confirm", "email-confirm-error", "Les emails ne correspondent pas.", firstErrorId);
    if (!passwordRe.test(password)) firstErrorId = err("password", "password-error", "Le mot de passe doit contenir au moins 1 majuscule, 1 minuscule, 1 chiffre et 8 caractères.", firstErrorId);
    if (password !== passwordConfirm) firstErrorId = err("password-confirm", "password-confirm-error", "Les mots de passe ne correspondent pas.", firstErrorId);
    if (role === "0") firstErrorId = err("role", "role-error", "Veuillez sélectionner un rôle.", firstErrorId);

    if (firstErrorId) {
      document.getElementById(firstErrorId).focus();
      disableSubmit(false);
      return;
    }

    fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(function (res) {
        if (!res.ok) throw new Error("http_" + res.status);
        return res.json();
      })
      .then(function (result) {
        if (result.status === "success") {
          message(result.message, "success");
          setTimeout(function () {
            window.location.href = result.redirect;
          }, 1200);
        } else {
          message(result.message || "L'inscription a échoué. Veuillez réessayer plus tard.", "error");
        }
      })
      .catch(function () {
        message("Erreur réseau. Veuillez réessayer.", "error");
      })
      .finally(function () {
        disableSubmit(false);
      });
  });

  function err(inputId, errorId, msg, firstId) {
    var input = document.getElementById(inputId);
    var span = document.getElementById(errorId);
    if (span) span.textContent = msg;
    if (input) input.classList.add("is-invalid");
    return firstId || inputId;
  }

  function clearErrors() {
    var spans = document.querySelectorAll(".error-message");
    for (var i = 0; i < spans.length; i++) spans[i].textContent = "";
    var invalids = document.querySelectorAll(".is-invalid");
    for (var j = 0; j < invalids.length; j++) invalids[j].classList.remove("is-invalid");
  }

  function message(text, type) {
    if (!messageBox) return;
    messageBox.textContent = text || "";
    messageBox.style.color = type === "success" ? "green" : (type === "error" ? "red" : "");
  }

  function disableSubmit(state) {
    if (!submitBtn) return;
    submitBtn.disabled = state;
    submitBtn.textContent = state ? "Envoi..." : "Demander l'accès";
  }
});
