document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("change-password-form");
  var messageBox = document.getElementById("auth-message");
  var submitBtn = document.getElementById("submit-btn");

  // Récupération du token dans l'URL
  var urlParams = new URLSearchParams(window.location.search);
  var token = urlParams.get("token");

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    clearErrors();
    message("");
    disableSubmit(true);

    var newPassword = document.getElementById("new_password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    var firstErrorId = null;
    var passwordRe = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

    if (!passwordRe.test(newPassword)) {
      firstErrorId = err("new_password", "new-password-error", "Le mot de passe doit contenir au moins 8 caractères, avec 1 majuscule, 1 minuscule et 1 chiffre.", firstErrorId);
    }
    if (newPassword !== confirmPassword) {
      firstErrorId = err("confirm_password", "confirm-password-error", "Les mots de passe ne correspondent pas.", firstErrorId);
    }
    if (!token) {
      message("Lien invalide ou expiré. Veuillez recommencer.", "error");
      disableSubmit(false);
      return;
    }

    if (firstErrorId) {
      document.getElementById(firstErrorId).focus();
      disableSubmit(false);
      return;
    }

    // Préparation des données
    var formData = new FormData(form);
    formData.append("token", token);

    fetch(BASE_URL + "/password/change", {
      method: "POST",
      body: formData,
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(function (res) {
        if (!res.ok) throw new Error("HTTP_" + res.status);
        return res.json();
      })
      .then(function (result) {
        if (result.status === "success") {
          message(result.message, "success");
          setTimeout(function () {
            window.location.href = result.redirect || BASE_URL + "/login";
          }, 1500);
        } else {
          message(result.message || "La mise à jour a échoué.", "error");
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
    submitBtn.textContent = state ? "Envoi..." : "Changer le mot de passe";
  }
});
