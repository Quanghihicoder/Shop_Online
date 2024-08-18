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
                        window.location.replace("./bidding.htm");
                    }
                } 
            }                        
        }
    };
    
    xhr.send(null);
}


const submitForm = (event) => {
    event.preventDefault();

    let inputEmail = document.getElementById("login-form-input-email").value
    let inputPass = document.getElementById("login-form-input-password").value

    let inputEmailError = document.getElementById("login-form-input-email-error")
    let inputPassError = document.getElementById("login-form-input-password-error")


    let params = "email=" + inputEmail + "&pass=" + inputPass;

    xhr.open("POST", "./php/login.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            parser = new DOMParser();
            xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");

            if (xmlDoc.getElementsByTagName("errors").length > 0) {
                if (xmlDoc.getElementsByTagName("email").length > 0) {
                    inputEmailError.innerHTML = xmlDoc.getElementsByTagName("email")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputEmailError.innerHTML = "";
                }
    
                if (xmlDoc.getElementsByTagName("pass").length > 0) {
                    inputPassError.innerHTML = xmlDoc.getElementsByTagName("pass")[0].childNodes[0].nodeValue;
                }
                else {
                    inputPassError.innerHTML = "";
                }
            } else {

                inputEmailError.innerHTML = "";
                inputPassError.innerHTML = "";

                if (xmlDoc.getElementsByTagName("action").length > 0) {
                    if (xmlDoc.getElementsByTagName("action")[0].childNodes[0].nodeValue == "redirect") {
                        window.location.replace("./bidding.htm");
                    }
                }  
            }
        }
    };
    xhr.send(params);
}