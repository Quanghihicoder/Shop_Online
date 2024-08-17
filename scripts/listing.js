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
                    } else {
                        getUserInfo();
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

                        if (name.length > 6) {
                            name = name.slice(0, 6) + "...";
                        }

                        if (balance.length > 6) {
                            balance = "..." + balance.slice(-6) ;
                        }

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

const submitForm = (event) => {
    event.preventDefault();

    let inputItemName = document.getElementById("listing-form-input-itemname").value
    let inputCategory = document.getElementById("listing-form-input-category").value
    let inputDescription = document.getElementById("listing-form-input-description").value
    let inputStartPrice = document.getElementById("listing-form-input-start-price").value
    let inputReservePrice = document.getElementById("listing-form-input-reserve-price").value
    let inputBuyItNowPrice = document.getElementById("listing-form-input-buy-it-now-price").value
    let inputDurationDay = document.getElementById("listing-form-input-duration-day").value
    let inputDurationHour = document.getElementById("listing-form-input-duration-hour").value
    let inputDurationMin = document.getElementById("listing-form-input-duration-min").value

    let inputItemNameError = document.getElementById("listing-form-input-itemname-error")
    let inputCategoryError = document.getElementById("listing-form-input-category-error")
    let inputDescriptionError = document.getElementById("listing-form-input-description-error")
    let inputStartPriceError = document.getElementById("listing-form-input-start-price-error")
    let inputReservePriceError = document.getElementById("listing-form-input-reserve-price-error")
    let inputBuyItNowPriceError = document.getElementById("listing-form-input-buy-it-now-price-error")
    let inputDurationError = document.getElementById("listing-form-input-duration-error")

    let formSuccessMessage= document.getElementById("listing-form-success-message")

    let params = "itemname=" + inputItemName 
        + "&category=" + inputCategory 
        + "&desc=" + inputDescription 
        + "&startprice=" + inputStartPrice 
        + "&reserveprice=" + inputReservePrice 
        + "&buyitnowprice=" + inputBuyItNowPrice
        + "&durationday=" + inputDurationDay
        + "&durationhour=" + inputDurationHour
        + "&durationmin=" + inputDurationMin;

    xhr.open("POST", "./php/listing.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            parser = new DOMParser();
            xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");

            if (xmlDoc.getElementsByTagName("errors").length > 0) {
                if (xmlDoc.getElementsByTagName("itemname").length > 0) {
                    inputItemNameError.innerHTML = xmlDoc.getElementsByTagName("itemname")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputItemNameError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("category").length > 0) {
                    inputCategoryError.innerHTML = xmlDoc.getElementsByTagName("category")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputCategoryError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("desc").length > 0) {
                    inputDescriptionError.innerHTML = xmlDoc.getElementsByTagName("desc")[0].childNodes[0].nodeValue;
                } 
                else {
                    inputDescriptionError.innerHTML = "";
                }
    
                if (xmlDoc.getElementsByTagName("startprice").length > 0) {
                    inputStartPriceError.innerHTML = xmlDoc.getElementsByTagName("startprice")[0].childNodes[0].nodeValue;
                }
                else {
                    inputStartPriceError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("reserveprice").length > 0) {
                    inputReservePriceError.innerHTML = xmlDoc.getElementsByTagName("reserveprice")[0].childNodes[0].nodeValue;
                }
                else {
                    inputReservePriceError.innerHTML = "";
                }

                if (xmlDoc.getElementsByTagName("buyitnowprice").length > 0) {
                    inputBuyItNowPriceError.innerHTML = xmlDoc.getElementsByTagName("buyitnowprice")[0].childNodes[0].nodeValue;
                }
                else {
                    inputBuyItNowPriceError.innerHTML = "";
                }
                
                if (xmlDoc.getElementsByTagName("duration").length > 0) {
                    inputDurationError.innerHTML = xmlDoc.getElementsByTagName("duration")[0].childNodes[0].nodeValue;
                }
                else {
                    inputDurationError.innerHTML = "";
                }
            } else {               
                inputItemNameError.innerHTML = "";
                inputCategoryError.innerHTML = "";
                inputDescriptionError.innerHTML = "";
                inputStartPriceError.innerHTML = "";
                inputReservePriceError.innerHTML = "";
                inputBuyItNowPriceError.innerHTML = "";
                inputDurationError.innerHTML = "";

                if (xmlDoc.getElementsByTagName("message").length > 0) {
                    document.getElementById("listing-form").reset();
                    formSuccessMessage.innerHTML = xmlDoc.getElementsByTagName("message")[0].childNodes[0].nodeValue
                }  
            }
        }
    };
    xhr.send(params);
}

// const interval = setInterval(function() {
//     getUserInfo();
// }, 5000);