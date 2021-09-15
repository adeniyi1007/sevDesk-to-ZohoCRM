// const fetch = require("node-fetch");

// Fetch all sevDesk contacts from Zoho CRM where parameter of Address du sevDesk is selected
// search customer in sevDesk using customerNumber or Email (if present)
// https://www.zohoapis.com/crm/v2/Contacts/search?scope=ZohoCRM.modules.contacts.ALL&criteria=((Kundennummern:starts_with:1))



// STARTS HERE
const sevDeskAuth="cc0712e041ec8a14c83e99d37af3b38f";
const sevDeskContactsURL="https://my.sevdesk.de/api/v1/Contact";

function httpGet(url,authKey){
    
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", url, false );
    xmlHttp.setRequestHeader("Authorization",authKey);
    xmlHttp.setRequestHeader("Access-Control-Allow-Origin","*");
    xmlHttp.send(null);
    return xmlHttp.responseText;
}

// fetch(sevDeskContactsURL, {
//             mode: 'no-cors',
//             method: "get",
//             headers: {
//                  "Authorization": sevDeskAuth
//             }
//             // body: JSON.stringify(ob)
//  })

// Step 1 : Fetch all contacts from sevDesk and Loop
// var all_sev_contacts = httpGet(sevDeskContactsURL,sevDeskAuth);

// console.log(all_sev_contacts);

fetch(sevDeskContactsURL, {
  method: "GET",
  headers: {"Authorization":sevDeskAuth}
})
.then(json => console.log(json)) 
.catch(err => console.log(err));


// sevdesk authorization header: cc0712e041ec8a14c83e99d37af3b38f

// Use customer contact id from sevDesk to retrieve all invoices of the customer
// url: https://my.sevdesk.de/api/v1/Invoice?contact[id]=13142956&contact[objectName]=Contact

// loop through all invoices

//store 2020 invoice

// store 2021 invoices

// store 2022 invoices

// send an update query to zoho CRM 

// repeat step for all customer.



// queries keep
// https://my.sevdesk.de/api/v1/Contact?depth=1&customerNumber=13397

// 2020 timestamp: 1577836851