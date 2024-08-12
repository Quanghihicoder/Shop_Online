var xhr = false;

if (window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
else if (window.ActiveXObject)
    xhr = new ActiveXObject("Microsoft.XMLHTTP");

const checkLoggedIn = () => {
    xhr.open("GET", "./php/checkLoggedIn.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            if (xhr.responseText.length > 0) {
                parser = new DOMParser();
                xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");
    
                if (xmlDoc.getElementsByTagName("loggedIn").length > 0) {
                    if (xmlDoc.getElementsByTagName("loggedIn")[0].childNodes[0].nodeValue == "true") {
                        window.location.replace("./listing.htm");
                    }
                } 
            }                        
        }
    };
    
    xhr.send(null);
}

const submitForm = (event) => {
    event.preventDefault();

    let inputFirstName = document.getElementById("register-form-input-first-name").value
    let inputLastName = document.getElementById("register-form-input-last-name").value
    let inputEmail = document.getElementById("register-form-input-email").value
    let inputPassword = document.getElementById("register-form-input-password").value
    let inputConfirmPassword = document.getElementById("register-form-input-confirm-password").value

    let inputFirstNameError = document.getElementById("register-form-input-first-name-error")
    let inputLastNameError = document.getElementById("register-form-input-last-name-error")
    let inputEmailError = document.getElementById("register-form-input-email-error")
    let inputPasswordError = document.getElementById("register-form-input-password-error")
    let inputConfirmPasswordError = document.getElementById("register-form-input-confirm-password-error")

    let params = "firstname=" + inputFirstName + "&lastname=" + inputLastName + "&email=" + inputEmail + "&password=" + inputPassword + "&confirmpassword=" + inputConfirmPassword;

    xhr.open("POST", "./php/register.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            parser = new DOMParser();
            xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");

            if (xmlDoc.getElementsByTagName("errors").length > 0) {
                if (xmlDoc.getElementsByTagName("firstname").length > 0) {
                    inputFirstNameError.innerHTML = xmlDoc.getElementsByTagName("firstname")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputFirstNameError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("lastname").length > 0) {
                    inputLastNameError.innerHTML = xmlDoc.getElementsByTagName("lastname")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputLastNameError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("email").length > 0) {
                    inputEmailError.innerHTML = xmlDoc.getElementsByTagName("email")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputEmailError.innerHTML = "";
                }
    
                if (xmlDoc.getElementsByTagName("password").length > 0) {
                    inputPasswordError.innerHTML = xmlDoc.getElementsByTagName("password")[0].childNodes[0].nodeValue;
                }
                else {
                    inputPasswordError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("confirmpassword").length > 0) {
                    inputConfirmPasswordError.innerHTML = xmlDoc.getElementsByTagName("confirmpassword")[0].childNodes[0].nodeValue;
                }
                else {
                    inputConfirmPasswordError.innerHTML = "";
                }
            } else {
                inputFirstNameError.innerHTML = "";
                inputLastNameError.innerHTML = "";
                inputEmailError.innerHTML = "";
                inputPasswordError.innerHTML = "";
                inputConfirmPasswordError.innerHTML = "";

                if (xmlDoc.getElementsByTagName("action").length > 0) {
                    if (xmlDoc.getElementsByTagName("action")[0].childNodes[0].nodeValue == "redirect") {
                        window.location.replace("./listing.htm");
                    }
                }  
            }
        }
    };
    xhr.send(params);
}