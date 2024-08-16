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
                    if (xmlDoc.getElementsByTagName("loggedIn")[0].childNodes[0].nodeValue == "false") {
                        window.location.replace("./login.htm");
                    }
                } 
            }                        
        }
    };
    
    xhr.send(null);
}

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


const getDataList = () => {
    xhr.open("GET", "./php/bidding.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {   
            document.getElementById("list-container").innerHTML = xhr.responseText;
        }
    };
    
    xhr.send(null);
}

const bidItem = (id) => {
    console.log(id)
    document.getElementById("overlay").classList.add("display");
}

const buyItem = (id) => {
    console.log(id)
    document.getElementById("overlay").classList.add("display");
}

const closeForm = () => {
    document.getElementById("overlay").classList.remove("display");
}

const submitForm = (event) => {

}

// const interval = setInterval(function() {
//     getDataList();
// }, 5000);


