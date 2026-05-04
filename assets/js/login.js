var recaptchaWidgetId;

/**
 * Called by reCAPTCHA API once it finishes loading (via onload=renderRecaptcha).
 * Renders a VISIBLE checkbox widget (size: 'normal') inside #recaptcha-container.
 * The container starts hidden in CSS and is only revealed after field validation.
 */
function renderRecaptcha() {
    recaptchaWidgetId = grecaptcha.render('recaptcha-container', {
        sitekey: '6LeEo9gsAAAAAFu3tFTqvmL37-MSV3ormKSXQjdu', // ← your site key
        size: 'normal',          // CHANGED from 'invisible' — shows the checkbox
        callback: onRecaptchaSuccess
    });
}

/**
 * Fires when the user clicks the Login button.
 * 1. Validates fields.
 * 2. Reveals the reCAPTCHA checkbox widget for the user to interact with.
 * We do NOT call grecaptcha.execute() — with size:'normal' the user clicks
 * the checkbox themselves and the callback fires automatically.
 */
function handleSubmit() {
    var identifier = document.getElementById('identifier').value.trim();
    var pass       = document.getElementById('pass').value.trim();

    if (!identifier || !pass) {
        document.getElementById('loginForm').reportValidity();
        return;
    }

    // Reveal the reCAPTCHA widget
    var container = document.getElementById('recaptcha-container');
    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Disable the Login button so they can't click it again while solving captcha
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.style.opacity = '0.6';
    btn.style.cursor  = 'not-allowed';
}

/**
 * Called automatically by reCAPTCHA once the user checks the box and verification passes.
 * The token is already injected into g-recaptcha-response by the reCAPTCHA library.
 * We briefly show the success state on the button, then submit.
 */
function onRecaptchaSuccess(token) {
    var btn = document.getElementById('submitBtn');

    // Reset any inline overrides from handleSubmit, then apply success class
    btn.style.opacity = '';
    btn.style.cursor  = '';
    btn.disabled      = false;
    btn.classList.add('success');

    // After the checkmark animation plays (~400 ms), switch to spinner + submit
    setTimeout(function () {
        btn.classList.remove('success');
        btn.classList.add('loading');
        btn.disabled = true;
        document.getElementById('loginForm').submit();
    }, 400);
}