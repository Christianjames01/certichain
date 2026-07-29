(function () {
    "use strict";

    /* ---------- Show / hide password ---------- */
    document.querySelectorAll(".toggle-visibility").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var targetId = btn.getAttribute("data-target");
            var input = document.getElementById(targetId);
            if (!input) return;
            var icon = btn.querySelector("i");
            var isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            if (icon) {
                icon.classList.toggle("ti-eye", !isHidden);
                icon.classList.toggle("ti-eye-off", isHidden);
            }
            btn.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
        });
    });

    /* ---------- Button ripple ---------- */
    document.querySelectorAll(".btn-block").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            var rect = btn.getBoundingClientRect();
            var ripple = document.createElement("span");
            var size = Math.max(rect.width, rect.height);
            ripple.className = "btn-ripple";
            ripple.style.width = ripple.style.height = size + "px";
            ripple.style.left = (e.clientX - rect.left - size / 2) + "px";
            ripple.style.top = (e.clientY - rect.top - size / 2) + "px";
            btn.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });
    });

    /* ---------- Email format hint (register page) ---------- */
    var emailField = document.getElementById("email");
    var emailHint = document.getElementById("email-hint");
    if (emailField && emailHint) {
        emailField.addEventListener("input", function () {
            if (emailField.value === "") {
                emailHint.textContent = "";
                emailHint.className = "field-hint";
                return;
            }
            var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value);
            emailHint.textContent = valid ? "Looks good." : "Enter a valid email address.";
            emailHint.className = "field-hint " + (valid ? "ok" : "bad");
        });
    }

    /* ---------- Password strength meter (register page) ---------- */
    var passwordField = document.getElementById("password");
    var strengthMeter = document.getElementById("strength-meter");
    var strengthLabel = document.getElementById("strength-label");

    function scorePassword(value) {
        var score = 0;
        if (value.length >= 8) score++;
        if (value.length >= 12) score++;
        if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;
        return Math.min(score, 4);
    }

    if (passwordField && strengthMeter && strengthLabel) {
        passwordField.addEventListener("input", function () {
            var value = passwordField.value;
            strengthMeter.classList.remove("strength-weak", "strength-fair", "strength-good", "strength-strong");

            if (value === "") {
                strengthLabel.textContent = "Password strength";
                return;
            }

            var score = scorePassword(value);
            var labels = ["Too short", "Weak", "Fair", "Good", "Strong"];
            var classes = ["", "strength-weak", "strength-fair", "strength-good", "strength-strong"];

            strengthLabel.textContent = labels[score];
            if (classes[score]) strengthMeter.classList.add(classes[score]);
        });
    }

    /* ---------- Confirm password match hint (register page) ---------- */
    var confirmField = document.getElementById("confirm_password");
    var confirmHint = document.getElementById("confirm-hint");

    function checkConfirmMatch() {
        if (!passwordField || !confirmField || !confirmHint) return;
        if (confirmField.value === "") {
            confirmHint.textContent = "";
            confirmHint.className = "field-hint";
            return;
        }
        var match = confirmField.value === passwordField.value;
        confirmHint.textContent = match ? "Passwords match." : "Passwords do not match.";
        confirmHint.className = "field-hint " + (match ? "ok" : "bad");
    }

    if (confirmField) {
        confirmField.addEventListener("input", checkConfirmMatch);
        if (passwordField) passwordField.addEventListener("input", checkConfirmMatch);
    }
})();