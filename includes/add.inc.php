<?php

    switch ($_POST['submit']) {

        case 'Find':
            $name = $_POST['name'];

            // perform a search query for the input name
            $solr_server = 'http://solr:8983/solr/final_authors/';
            $solr_api_start = 'select?fl=id&indent=true&q.op=AND&q=author%3A%20';
            $solr_api_end = '&rows=10&useParams=$wh=json';
        
            $name = $_POST['name'];
            $name = explode(' ', $name);
            $name = implode('%20', $name);

            // Perform the search using cURL and the Solr API endpoint
            $search_output = shell_exec("curl -X POST ".$solr_server."'".$solr_api_start.$name.$solr_api_end."'");

            // Decode the response from the Solr server
            $search_output = json_decode($search_output);

            // Check if the search returned any documents with an ID
            if (isset($search_output->{'response'}->{'docs'}[0]->{'id'})) {

                // The search returned a document with a matching ID, so the author is already indexed
                // Get the ID of the document
                $id = $search_output->{'response'}->{'docs'}[0]->{'id'};

                // Set up variables for the Solr update API endpoint and delete command
                $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
                $delete_xml = '<delete><query>'.$id.'</query></delete>';

                // Execute the delete command using cURL
                $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");

                // Parse the response from the delete command to check if it was successful
                $xmlElement = new SimpleXMLElement($output);
                $status = (int)$xmlElement->lst[0]->int[0];

                // If the delete was not successful, redirect to the add-doc page with an error message
                if ($status != 0) {
                    header('Location: http://localhost:8088/add-doc.php?upload=false');

                }
            }

            // Execute a Python script to retrieve the BibTeX entry for the author
            $filename = shell_exec('python3 ../scripts/get-bibtex.py '.$_POST['name']);

            // Execute a Python script to preprocess the BibTeX entry and extract relevant information
            exec('python3 ../scripts/preprocess.py -bibtex '.$filename);

            // Open the result file produced by the preprocessing script
            $myfile = fopen("result.txt", "r") or die("Unable to open file!");

            // Read the contents of the result file
            $result = fread($myfile, filesize("result.txt")).rtrim(' ');

            // If the result is not "SUCCESS", redirect to the add-doc page with an error message
            if ($result != "SUCCESS") {
                header('Location: http://localhost:8088/add-doc.php?upload=false');
                exec('rm result.txt');
                exit(1);
            }

            // Execute a cURL command to send a JSON document to the Solr index for indexing
            $output = shell_exec("curl -X POST -H 'Content-Type: application/json' --data-binary @tmp.json http://solr:8983/solr/final_authors/update/json/docs?commit=true");
            
            // Decode the response from the Solr server
            $json = json_decode($output);

            // Get the status code from the response header
            $status = $json->responseHeader->status;

            // If the status code is 0, the indexing was successful and the user is redirected to the add-doc page with a success message
            if ($status == 0) {
                header('Location: http://localhost:8088/add-doc.php?upload=true');
            }

            // Clean up by deleting the temporary JSON file and the BibTeX file
            exec('rm tmp.json && rm '.$filename);

            break;

        case 'Upload':

            // Handle the file upload
            $target_dir = "";
            $filename = $target_dir . basename($_FILES["author-doc"]["name"]);
            move_uploaded_file($_FILES["author-doc"]["tmp_name"], $filename);

            // Get the file extension of the uploaded file
            $path_parts = pathinfo($_FILES["author-doc"]["name"]);
            $extension = $path_parts['extension'];

            // If the file is not a ZIP file, it is assumed to be a BibTeX file
            if ($extension != "zip") {
                // Execute a Python script to preprocess the BibTeX file and extract relevant information
                $output = shell_exec('python3 ../scripts/preprocess.py -bibtex '.$filename);

                // Open the result file produced by the preprocessing script
                $myfile = fopen("result.txt", "r") or die("Unable to open file!");

                // Read the contents of the result file
                $result = fread($myfile, filesize("result.txt")).rtrim(' ');

                // If the result is not "SUCCESS", redirect to the add-doc page with an error message
                if ($result != "SUCCESS") {
                    header('Location: http://localhost:8088/add-doc.php?upload=false');
                    exec('rm result.txt');
                    exit(1);
                }

                // Send the JSON document to the Solr index for indexing
                $output = shell_exec("curl -X POST -H 'Content-Type: application/json' --data-binary @tmp.json http://solr:8983/solr/final_authors/update/json/docs?commit=true");
                
                // Clean up by deleting the temporary JSON file and the BibTeX file
                exec('rm tmp.json && rm '.$filename);

                // Decode the response from the Solr server
                $json = json_decode($output);

                // Get the status code from the response header
                $status = $json->responseHeader->status;

                // If the status code is 0, the indexing was successful and the user is redirected to the add-doc page with a success message
                if ($status == 0) {
                    header('Location: http://localhost:8088/add-doc.php?upload=true');
                }
             } else {
		
                // Execute a Python script to process the collection of BibTeX files and extract relevant information
                exec('python3 ../scripts/mass-import.py '.$filename);

                // Open the result file produced by the mass-import script
                $myfile = fopen("result.txt", "r") or die("Unable to open file!");

                // Read the contents of the result file
                $result = fread($myfile, filesize("result.txt")).rtrim(' ');

                // If the result is "SUCCESS", redirect to the add-doc page with a success message
                if ($result == "SUCCESS") {
                    header('Location: http://localhost:8088/add-doc.php?upload=true');
                } else { // If the result is not "SUCCESS", redirect to the add-doc page with an error message
                    header('Location: http://localhost:8088/add-doc.php?upload=false');
                }
            }
            break;
        }
    // Clean up by deleting the result file and exit
    exec('rm result.txt');
exit();

