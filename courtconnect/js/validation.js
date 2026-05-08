function validateEmail(id) {
    let email = id.value;
    let msg = document.getElementById("emailErr");
    let good = false;
    if (email.includes("@") && email.includes(".")) {
        msg.textContent = "";
        good = true;
    } else {
        msg.textContent = "Must use proper email.";
        good = false;
    }
    return good;
}

function validatePassword(id) {
    let pwd = id.value;
    let msg = document.getElementById("pwdErr");
    let good = false;

    if (pwd.trim() === "") {
        msg.textContent = "Password is required.";
        good = false;
    } else if (pwd.length < 6) {
        msg.textContent = "Password must be at least 6 characters.";
        good = false;
    } else if (!/[0-9]/.test(pwd)) {
        msg.textContent = "Password must contain a number.";
        good = false;
    } else if (!/[a-zA-Z]/.test(pwd)) {
        msg.textContent = "Password must contain a letter.";
        good = false;
    } else {
        msg.textContent = "";
        good = true;
    }

    return good;
}

function validate() {
    let email = document.getElementById("email");
    let pwd = document.getElementById("pwd");
    return validateEmail(email) && validatePassword(pwd);
}