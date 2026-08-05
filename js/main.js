
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const pwdRegex = new RegExp("^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{6,}$");

init()


/**
 * Shows alert for registration form
 * @param {string} text
 * @returns {void}
 */
function regAlert(text) {
    let divRegAlert = document.querySelector('#regAlert');
    if (divRegAlert != null) {
        document.querySelector('#regAlert #regAlertText').innerHTML = text;
        divRegAlert.classList.remove('d-none');
    }
}

/**
 * Checks if username exists in database through AJAX call
 * @param {string} username
 * @returns {string}
 */
function checkUsernameExist(username) {
    let result = "";
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            result = this.responseText;
        }
    };
    xhr.open("GET", "register.php?username=" + username, false);
    xhr.send();
    return result;
}

/**
 * Validates profile edit form before submitting
 * @param {Element} regForm
 * @returns {boolean}
 */
function validateRegForm(regForm) {
    //pasword match
    let pwd = regForm.querySelector('input[name="regPwd1"]');
    let pwd2 = regForm.querySelector('input[name="regPwd2"]');
    if (pwd.value == pwd2.value) {
        if (!pwdRegex.test(pwd.value)) {
            pwd.value = null;
            pwd2.value = null;
            regAlert("Heslo musí mít aspoň 6 znaku, aspoň jedno velké písmeno a aspoň jedno číslo");
            return false;
        }
    } else {
        pwd.value = null;
        pwd2.value = null;
        regAlert("Hesla musí být stejná");
        return false;
    }

    //username doesnt exist in db
    let username = regForm.querySelector('input[name="regUsername"]');
    let response = checkUsernameExist(username.value);
    if (response == 'failedRequest' || response == 'undefined') {
        regAlert("Nepodařilo se zkontrolovat username")
        return false;
    } else if (response.length > 0) {
        regAlert("Tento username je již používán")
        return false;
    }

    //birth date control (between 1900 and 2006 (15y + only))
    let bDate = new Date(regForm.querySelector('input[name="birthday"]').value);
    let date = new Date();
    date.setFullYear(date.getFullYear() - 15);
    if (bDate.getFullYear() < 1900 || bDate > date) {
        regAlert("Nesmyslný datum narození, vyberte opravdový datum (Starší než 15, mladší než 123 let)");
        return false;
    }

    return true;
}

/**
 * Validates profile edit form before submitting
 * @param {Element} form
 * @returns {boolean}
 */
function validateProfileForm(form) {
    let pwd1 = form.querySelector('input[name="pwd"]');
    let pwd2 = form.querySelector('input[name="pwd2"]');
    if (pwd1.value != "" || pwd2.value != "") {
        if (pwd1.value == pwd2.value) {
            if (!pwdRegex.test(pwd1.value)) {
                pwd1.value = null;
                pwd2.value = null;
                regAlert("Heslo musí mít aspoň 6 znaku, aspoň jedno velké písmeno a aspoň jedno číslo");
                return false;
            }
        } else {
            pwd1.value = null;
            pwd2.value = null;
            regAlert("Hesla musí být stejná, zopakujte heslo dvakrát");
            return false;
        }
    }

    let bDate = new Date(form.querySelector('input[name="birthday"]').value);
    let date = new Date();
    date.setFullYear(date.getFullYear() - 15);
    if (bDate.getFullYear() < 1900 || bDate > date) {
        regAlert("Nesmyslný datum narození, vyberte opravdový datum (Starší než 15, mladší než 123 let)");
        return false;
    }

    return true;
}

/**
 * Adds all needed event listeners for all pages
 * @returns {void}
 */
function addEventListeners() {
    //Search button focus on input click
    let searchInput = document.querySelector('input[name="search"]');
    searchInput.addEventListener('click', function () {
        focus(document.querySelector('#searchForm button'));
    })

    //Register form open/close
    if (document.querySelector('#btnRegister') != null) {
        document.querySelector('#btnRegister').addEventListener("click", function (event) {
            event.preventDefault();
            document.querySelector('#myModal').classList.remove('d-none')
        })
        document.querySelector('.modal-bg').addEventListener("click", function (event) {
            document.querySelector('#myModal').classList.add('d-none')
        })
        document.querySelector('.modal-bg #regClose').addEventListener("click", function (event) {
            document.querySelector('#myModal').classList.add('d-none')
        })
    }

    //Close button for alerts
    let divRegAlert = document.querySelector('#regAlert');
    if (divRegAlert != null) {
        divRegAlert.addEventListener("click", function (event) {
            event.stopImmediatePropagation()
        })
        document.querySelector('#regAlertX').addEventListener("click", function () {
            document.querySelector('#regAlert #regAlertText').innerHTML = "";
            divRegAlert.classList.add('d-none');
        })
    }
    let divAlert = document.querySelector('#divAlert');
    if (divAlert != null) {
        document.querySelector('#alertX').addEventListener("click", function () {
            divAlert.classList.add('d-none');
        })
    }

    //Register form validation
    const regForm = document.querySelector('#regForm');
    if (regForm != null) {
        regForm.addEventListener("click", function (event) {
            event.stopImmediatePropagation()
        })
        regForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (validateRegForm(regForm)) {
                regForm.submit();
            }
        })
    }

    //Profile update validation
    const profileForm = document.querySelector('#profileForm');
    if (profileForm != null) {
        profileForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (validateProfileForm(profileForm)) {
                profileForm.submit();
            }
        })
    }
}


/**
 * Initializes js code and functions
 * @returns {any}
 */
function init() {
    addEventListeners();
}