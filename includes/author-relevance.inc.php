<?php
    
    // Get the ID of the first author
    $author1_id = $_POST['value1'];

    // Set the default flag to "similar"
    $flag = 'similar';

    // Get the number of results to display
    $numresults = $_POST['value3'];
    
    // If the ID of the second author is provided, set the flag to "comparison"
    if ($_POST['value2'] != '') {
        $author2_id = $_POST['value2'];
        $flag = 'comparison';
    }

    // If the flag is "comparison", execute a Python script to calculate the relevance of the two authors
    if ($flag == 'comparison') {
        $result = shell_exec('python3 ../scripts/calculate_relevance.py '.$author1_id.' '.$author2_id);
        echo '<b>Results:'.$result.'</b>';
    } else {
        // Set up the Solr server URL and API endpoint for a more-like-this (MLT) query
        $solr_server = 'http://solr:8983/solr/final_authors/';
        $solr_api = 'select?debugQuery=true&indent=true&q.op=OR&q=%7B!mlt%20qf%3Dbooktitle%2Ctitle%2Cjournal%7D'.$author1_id.'&rows='.$numresults.'&useParams';
        
        // Execute the Solr query and get the response
        $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
        
        // Decode the response from the Solr server
        $response = json_decode($response, true);

        // Initialize an empty HTML string to store the results
        $html = '';
            
        // Iterate over the documents in the response
        foreach ($response["response"]["docs"] as $doc) {
            // Get the ID of the current document
            $current_id = $doc["id"];

            // Extract the relevance score from the debug information in the response
            $score = $response["debug"]["explain"][$current_id];

            // Trim the score to six decimal places and remove any leading or trailing white space
            $score = substr($score, 0, 6);
            $score = trim($score);

            // Append the author name and relevance score to the HTML string
            $html .= "<h3>";
            $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
            
            // Add a horizontal rule and line break to the HTML string
            $html .= "<hr><br>";
        }

        // Output the HTML
        echo $html;
    }

    
