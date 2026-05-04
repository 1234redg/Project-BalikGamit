var recaptchaWidgetId;

function renderRecaptcha() {
    recaptchaWidgetId = grecaptcha.render('recaptcha-container', {
        sitekey: '6LeEo9gsAAAAAFu3tFTqvmL37-MSV3ormKSXQjdu', 
        size: 'normal',
        callback: onRecaptchaSuccess
    });
}

function handleSubmit() {
    // Select all potential fields safely using Optional Chaining
    var identifier = document.getElementById('identifier')?.value.trim();
    var pass = document.getElementById('pass')?.value.trim();
    var fname = document.getElementById('fname')?.value.trim();
    var lname = document.getElementById('lname')?.value.trim();
    var email = document.getElementById('email')?.value.trim();

    var isValid = true;

    // Check Login fields ONLY if they exist
    if (document.getElementById('identifier') && !identifier) isValid = false;
    if (document.getElementById('pass') && !pass) isValid = false;
    
    // Check Signup fields ONLY if they exist
    if (document.getElementById('fname') && !fname) isValid = false;
    if (document.getElementById('lname') && !lname) isValid = false;
    if (document.getElementById('email') && !email) isValid = false;

    if (!isValid) {
        document.getElementById('loginForm').reportValidity();
        return;
    }

    var container = document.getElementById('recaptcha-container');
    if (container) {
        container.style.display = 'block';
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    var btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.style.cursor  = 'not-allowed';
    }
}

function onRecaptchaSuccess(token) {
    var btn = document.getElementById('submitBtn');
    if (btn) {
        btn.style.opacity = '';
        btn.style.cursor  = '';
        btn.disabled      = false;
        btn.classList.add('success');
    }
    setTimeout(function () {
        if (btn) {
            btn.classList.remove('success');
            btn.classList.add('loading');
            btn.disabled = true;
        }
        document.getElementById('loginForm').submit();
    }, 400);
}