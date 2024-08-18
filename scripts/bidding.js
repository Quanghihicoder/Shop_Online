var xhr = false;

if (window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
else if (window.ActiveXObject)
    xhr = new ActiveXObject("Microsoft.XMLHTTP");

var selectedID = null

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
                    } else {
                        getDataList();
                    }
                } 
            }                        
        }
    };
    
    xhr.send(null);
}

const getUserInfo = () => {
    xhr.open("GET", "./php/customerBasicInfo.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            if (xhr.responseText.length > 0) {
                parser = new DOMParser();
                xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");

                if (xmlDoc.getElementsByTagName("info").length > 0) {
                    if (xmlDoc.getElementsByTagName("name").length > 0 && xmlDoc.getElementsByTagName("balance").length > 0) {
                        
                        let name = xmlDoc.getElementsByTagName("name")[0].childNodes[0].nodeValue;
                        let balance = xmlDoc.getElementsByTagName("balance")[0].childNodes[0].nodeValue;

                        document.getElementById("navigation-account-name").innerHTML = "Hi, " + name;
                        document.getElementById("navigation-account-balance").innerHTML = "Balance: $" + balance;
                    } 
                    else {
                        document.getElementById("navigation-account-name").innerHTML = "";
                        document.getElementById("navigation-account-balance").innerHTML = "";
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
            if (xhr.responseText == "") {
                document.getElementById("list-container").innerHTML = "<p>There are currently no auctions on the market!</p>";
            } else {
                document.getElementById("list-container").innerHTML = xhr.responseText;
            }
            getUserInfo();
        }
    };
    
    xhr.send(null);
}

const bidItem = (id) => {
    selectedID = id;
    document.getElementById("form-container").classList.remove("hidden")
    document.getElementById("success-message-container").classList.add("hidden")
    document.getElementById("overlay").classList.add("display");
}

const buyItem = (id) => {
    let params = "auctionid=" + id;
    
    xhr.open("POST", "./php/bidding.php", true);
    
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            parser = new DOMParser();
            xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");

            document.getElementById("form-container").classList.add("hidden")
            document.getElementById("success-message-container").classList.remove("hidden")
            document.getElementById("overlay").classList.add("display");

            if (xmlDoc.getElementsByTagName("errors").length > 0) {
                if (xmlDoc.getElementsByTagName("buy").length > 0) {        
                    document.getElementById("success-message").innerHTML = "";
                    document.getElementById("error-message").innerHTML = xmlDoc.getElementsByTagName("buy")[0].childNodes[0].nodeValue;
                } 
            } else {                  
                if (xmlDoc.getElementsByTagName("message").length > 0) {

                    console.log(xmlDoc.getElementsByTagName("message")[0].nodeValue)

                    document.getElementById("error-message").innerHTML= "";
                    document.getElementById("success-message").innerHTML = xmlDoc.getElementsByTagName("message")[0].childNodes[0].nodeValue
                }  
            }

            getDataList();
        }
    };
    xhr.send(params);
}

const closeForm = () => {
    selectedID = null
    document.getElementById("overlay").classList.remove("display");
}

const submitForm = (event) => {
    event.preventDefault();

    let inputAmountError = document.getElementById("bid-form-input-error")

    if (selectedID == null) {
        inputAmountError.innerHTML = "Invalid auction.";
    } else {
        let inputAmount = document.getElementById("bid-form-input").value
    
        let params = "auctionid=" + selectedID + "&amount=" + inputAmount;
    
        xhr.open("POST", "./php/bidding.php", true);
    
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    
        xhr.onreadystatechange = function() {
            if ((xhr.readyState == 4) && (xhr.status == 200)) {
                parser = new DOMParser();
                xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");
    
                if (xmlDoc.getElementsByTagName("errors").length > 0) {
                    if (xmlDoc.getElementsByTagName("bid").length > 0) {
                        inputAmountError.innerHTML = xmlDoc.getElementsByTagName("bid")[0].childNodes[0].nodeValue;
                    } 
                    else {
                        inputAmountError.innerHTML = "";
                    }
                } else {
                    inputAmountError.innerHTML = "";
                    
                    if (xmlDoc.getElementsByTagName("message").length > 0) {
                        document.getElementById("success-message").innerHTML = xmlDoc.getElementsByTagName("message")[0].childNodes[0].nodeValue
                        
                        document.getElementById("form-container").classList.add("hidden")
                        document.getElementById("success-message-container").classList.remove("hidden")
                    }  
                }

                getDataList();
            }
        };
        xhr.send(params);
    }
}

const interval = setInterval(() => {
    getDataList();
}, 5000);


