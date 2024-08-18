var xhr = false;

if (window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
else if (window.ActiveXObject)
    xhr = new ActiveXObject("Microsoft.XMLHTTP");

const action = (type) => {
    let params = "action=" + type;
    
    xhr.open("POST", "./php/maintenance.php", true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == 4) && (xhr.status == 200)) {
            if (type == "get_revenue") {
                document.getElementById("navigation-account-balance").innerHTML = "Admin balance: $" + xhr.responseText;
            } 
            else {
                if (xhr.responseText != "") {
                    document.getElementById("result-container").innerHTML = xhr.responseText;
                }

                action("get_revenue")
            }
        }
    };
    xhr.send(params);
}

