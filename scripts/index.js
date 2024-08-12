var xhr = false;

if (window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
else if (window.ActiveXObject)
    xhr = new ActiveXObject("Microsoft.XMLHTTP");

const checkLoggedIn = () => {
    let navBar = document.getElementById("navigation-bar-container")
    let navAccount = document.getElementById("navigation-account-container")
    let logoutScript = document.getElementById("logout-script")

    xhr.open("GET", "./php/checkLoggedIn.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            if (xhr.responseText.length > 0) {
                parser = new DOMParser();
                xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");
    
                if (xmlDoc.getElementsByTagName("loggedIn").length > 0) {
                    if (xmlDoc.getElementsByTagName("loggedIn")[0].childNodes[0].nodeValue == "false") {
                        navBar.remove()
                        navAccount.remove()
                        logoutScript.remove()
                    }
                } 
            }                        
        }
    };
    
    xhr.send(null);
}

const logOut = (event) => {
    event.preventDefault();
    
    let navBar = document.getElementById("navigation-bar-container")
    let navAccount = document.getElementById("navigation-account-container")

    xhr.open("GET", "./php/logout.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {   
            navBar.remove()
            navAccount.remove()
            logoutScript.remove()
        }
    };
    
    xhr.send(null);
}