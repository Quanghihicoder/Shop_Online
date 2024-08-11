var xhr = false;

if (window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
else if (window.ActiveXObject)
    xhr = new ActiveXObject("Microsoft.XMLHTTP");

const submitForm = () => {
    let inputEmail = document.getElementById("login-form-input-email").value
    let inputPass = document.getElementById("login-form-input-password").value

    let params = "email=" + inputEmail + "&pass=" + inputPass;

    xhr.open("POST", "./php/login.php", true);

    // this&that => this%26that
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            console.log(xhr.responseText.split("\n"));
 
        }
    };
    xhr.send(params);
}