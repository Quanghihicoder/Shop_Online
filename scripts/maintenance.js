var xhr = false;

if (window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
else if (window.ActiveXObject)
    xhr = new ActiveXObject("Microsoft.XMLHTTP");


const logOut = (event) => {
    event.preventDefault();

    xhr.open("GET", "./php/logout.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {   
            window.location.replace("./login.htm");
        }
    };
    
    xhr.send(null);
}

const action = (type) => {
    document.getElementById("result-container").innerHTML = ""

    let params = "action=" + type;

    xhr.open("POST", "./php/maintenance.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            if (xhr.responseText != "") {
                document.getElementById("result-container").innerHTML = xhr.responseText;
            }
        }
    };
    xhr.send(params);
}

