<?php

// STARTS HERE
$sevDeskAuth=$_ENV[sevAuth];
$sevDeskContactsURLALL="https://my.sevdesk.de/api/v1/Contact?limit=100000"; // change on prouction also: (offset=)
$sevDeskContactsURL="https://my.sevdesk.de/api/v1/Contact";

$zohoAuth=""; // find a generate class 

$response =  new stdclass();

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

$counter=0; // Locally:  used to create a pause to concerve memory operation


    // $request = callURL($sevDeskContactsURLALL,$sevDeskAuth); // live (fetch contact once)
    $request = file_get_contents("sevDeskContact.json"); // locally

    if($request){

        $result = json_decode($request, true);
        $total_contacts= count($result["objects"]);

        // Local test only (remove when going live)
        $logfile = fopen("Report.csv", "a");
        
        $logReportHeader="S/N,Customer Number,Name,Total invoice,2020 Total,2021 Total,2020 Total,Last Invoice".PHP_EOL;

        fwrite($logfile, $logReportHeader);

        // Loop through each contacts

        foreach($result["objects"] as $contact){
            $counter++;
            // if($counter>20) {die("Saving Memory");}
            
            // get customer invoices
            $contactID=$contact["id"];
            $customerNumber=$contact["customerNumber"];
            $customerName=$contact["name2"]; // used for reporting

            $sevDeskInvoiceURL= "https://my.sevdesk.de/api/v1/Invoice?contact[id]=$contactID&contact[objectName]=Contact";


            // $invoiceRequest = callURL($sevDeskInvoiceURL,$sevDeskAuth) ;// live (fetch contact once)
            $invoiceRequest = file_get_contents("sevDeskInvoice.json"); // locally

            if($invoiceRequest){
                $invoice_result = json_decode($invoiceRequest, true);
                $total_invoice=0;

                // defaults
                $invoice_2020=0;
                $invoice_2021=0;
                $invoice_2022=0;

                $last_invoice_date="";
                
                // loop through the invoices 
                foreach($invoice_result["objects"] as $invoice){

                    $invoice_date=$invoice["invoiceDate"];
                    $date_explode=explode("-",$invoice_date);
                    $invoice_year=$date_explode[0];

                    $invoice_customer_id=$invoice["contact"]["id"]; // for local search

                    if( $invoice_customer_id === $contactID){
                        $total_invoice++;

                        // record last invoice date as first found date
                        if(!isset($last_invoice_date) || empty($last_invoice_date)){
                            $last_invoice_date=$invoice["invoiceDate"]; 
                        }

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

                }


                $logReportBody="$counter,$customerNumber,$customerName,$total_invoice,$invoice_2020,$invoice_2021,$invoice_2022,$last_invoice_date".PHP_EOL;

                fwrite($logfile, $logReportBody); // remove
                
                // end

                echo $report_mail_body="-$customerNumber($customerName) has $total_invoice invoice, in 2020 : EUR $invoice_2020 , in 2021 : EUR $invoice_2021 , in 2022 : EUR $invoice_2022 and Last invoice date: $last_invoice_date".PHP_EOL;
                
            }
            
            // Update Zoho fields
            
            // Start Here
            // Zoho Ends Here
            
        }
        fclose($logfile); // remove after
    }



?>