<?php
    switch ($_POST['submit']) {
    
        // If the form was submitted with the "Delete" button
        case 'Delete':
            
            $selectedDocIds = $_POST['doc-id'];

            // Set up the Solr server URL and API endpoint for an update request
            $solr_server = 'http://solr:8983/solr/final_authors/';
            $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
            
        
            // Loop through the array of selected document IDs
            foreach ($selectedDocIds as $id) {
                // Construct the XML string for a delete request for the current ID
                $delete_xml = '<delete><query>'.$id.'</query></delete>';

                // Execute the delete request and get the response
                $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");

                // Parse the response as XML and get the status code
                $xmlElement = new SimpleXMLElement($output);
                $status = (int)$xmlElement->lst[0]->int[0];

                // If the delete was successful, redirect to the delete page with a success message
                if ($status == 0) {
                    header('Location: http://localhost:8088/delete-doc.php?delete=true');
                } else { // If the delete was not successful, redirect to the delete page with a failure message
                    header('Location: http://localhost:8088/delete-doc.php?delete=false');
                }
            }
            break;
            
        case 'Delete-all':
            $solr_server = 'http://solr:8983/solr/final_authors/';
            $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
            $delete_xml = '<delete><query>*:*</query></delete>';
            $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");
            header('Location: http://localhost:8088/delete-doc.php?deleteall=true');
            
            break;
        }

