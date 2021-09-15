<?php

// STARTS HERE
$sevDeskAuth=$_ENV[sevAuth];
$sevDeskContactsURLALL="https://my.sevdesk.de/api/v1/Contact?limit=1"; // change on prouction also: (offset=)
$sevDeskContactsURL="https://my.sevdesk.de/api/v1/Contact";

$zohoAuth=""; // find a generate class 

$response = new stdclass();

// Function to call urls


$result = array();
function callURL($url,$auth){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // remove when going live
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
      $ch, CURLOPT_HTTPHEADER, [
        'Authorization: '.$auth]
    );
    
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    return curl_exec($ch);

    curl_close($ch);
}


    if($request = callURL($sevDeskContactsURLALL,$sevDeskAuth)){

        $result = json_decode($request, true);
        $total_contacts= count($result["objects"]);

        // Loop through each contacts

        foreach($result["objects"] as $contact){
            
            // get customer invoices
            $contactID=$contact["id"];
            $customerNumber=$contact["customerNumber"];
            $customerName=$contact["name2"]; // used for reporting

            $sevDeskInvoiceURL= "https://my.sevdesk.de/api/v1/Invoice?contact[id]=$contactID&contact[objectName]=Contact";

            if($invoiceRequest = callURL($sevDeskInvoiceURL,$sevDeskAuth)){

                $invoice_result = json_decode($invoiceRequest, true);
                $total_invoice= count($invoice_result["objects"]);

                // defaults
                $invoice_2020=0;
                $invoice_2021=0;
                $invoice_2022=0;

                if(isset($invoice_result["objects"][0]["invoiceDate"])){
                    $last_invoice_date=$invoice_result["objects"][0]["invoiceDate"]; // get the first invoice on the list
                }
                else{
                    $last_invoice_date="-";
                }
                
                // loop through the invoices 
                foreach($invoice_result["objects"] as $invoice){

                    $invoice_date=$invoice["invoiceDate"];
                    $date_explode=explode("-",$invoice_date);
                    $invoice_year=$date_explode[0];
                    
                    if($invoice_year==="2020"){
                        $invoice_2020 += $invoice["sumNet"];
                    }
                    else if($invoice_year==="2021"){
                        $invoice_2021 += $invoice["sumNet"];
                    }
                    else if($invoice_year==="2022"){
                        $invoice_2022 += $invoice["sumNet"];
                    }

                }

                echo $report_mail_body="$customerName has $total_invoice invoice, in 2020 : EUR $invoice_2020 , in 2021 : EUR $invoice_2021 , in 2022 : EUR $invoice_2022 and Last invoice date: $last_invoice_date".PHP_EOL;
            
            }

            // Update Zoho fields

            // Start Here
                class UpsertRecords
                {
                    public function execute(){
                        $curl_pointer = curl_init();
                        
                        $curl_options = array();
                        $url = "https://www.zohoapis.com/crm/v2/Leads/upsert";
                        
                        $curl_options[CURLOPT_URL] =$url;
                        $curl_options[CURLOPT_RETURNTRANSFER] = true;
                        $curl_options[CURLOPT_HEADER] = 1;
                        $curl_options[CURLOPT_CUSTOMREQUEST] = "POST";
                        $requestBody = array();
                        $recordArray = array();
                        $recordObject = array();
                        $recordObject["Company"]="FieldAPIValue";
                        $recordObject["Last_Name"]="347706107420006";
                        $recordObject["First_Name"]="347706107420006";
                        $recordObject["State"]="FieldAPIValue";

                        
                        
                        $recordArray[] = $recordObject;
                        $requestBody["data"] =$recordArray;
                        $curl_options[CURLOPT_POSTFIELDS]= json_encode($requestBody);
                        $headersArray = array();
                        
                        $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " . "$zohoAuth";
                        
                        $curl_options[CURLOPT_HTTPHEADER]=$headersArray;
                        
                        curl_setopt_array($curl_pointer, $curl_options);
                        
                        $result = curl_exec($curl_pointer);
                        $responseInfo = curl_getinfo($curl_pointer);
                        curl_close($curl_pointer);
                        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
                        if(strpos($headers," 100 Continue")!==false){
                            list( $headers, $content) = explode( "\r\n\r\n", $content , 2);
                        }
                        $headerArray = (explode("\r\n", $headers, 50));
                        $headerMap = array();
                        foreach ($headerArray as $key) {
                            if (strpos($key, ":") != false) {
                                $firstHalf = substr($key, 0, strpos($key, ":"));
                                $secondHalf = substr($key, strpos($key, ":") + 1);
                                $headerMap[$firstHalf] = trim($secondHalf);
                            }
                        }
                        $jsonResponse = json_decode($content, true);
                        if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
                            list ($headers, $content) = explode("\r\n\r\n", $content, 2);
                            $jsonResponse = json_decode($content, true);
                        }
                        var_dump($headerMap);
                        var_dump($jsonResponse);
                        var_dump($responseInfo['http_code']);
                        
                    }
                    
                }
                (new UpsertRecords())->execute();
            // Zoho Ends Here

        }
    }



?>