<?php

    // Set up the Solr server URL
    $solr_server = 'http://solr:8983/solr/final_authors/';

    // Get the number of results and the search query from the form submission
    $numResults = $_POST['num-results'];
    $query = $_POST['query'];
    
    // Check if the search query is a phrase query (enclosed in quotation marks)
    if (substr($query, 0, 1) == '"' && substr($query, -1) == '"') {
        $phrase_query = true;
    } else {
        $phrase_query = false;
    }

    // Check if the boolean search option is selected
    if (isset($_POST['boolean-search'])) {
        // Execute the preprocess script with the boolean search option
        $processed_query = shell_exec('python3 ../scripts/preprocess.py -query -boolean '.$query);
    }
    else {
        // Execute the preprocess script with the normal search option
        $processed_query = shell_exec('python3 ../scripts/preprocess.py -query -normal '.$query);
    }

    // If the search query was a phrase query, enclose it in quotation marks again
    if($phrase_query == true){
        $processed_query = '"'.$processed_query.'"';
    }
    
    // Trim any leading or trailing white space from the processed query
    $processed_query = trim($processed_query);
    
    // Process the form input
    switch ($_POST['mode']) {
        case 'normal':
            // Set up the Solr API URL with the normal search options
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=false&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q='.$processed_query.'&useParams=';
            
            // Execute the search query and store the response
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            $response = json_decode($response,true);

            // Get the number of documents found in the search
            $numFound = $response['response']['numFound'];
            
            // Set up the HTML output for the search results
            $html = "<h1><u>Search results for: ".$query."</u></h1>\n";
            
            // Loop through the search results
            foreach ($response["response"]["docs"] as $doc) {
                // Get the ID of the current document
                $current_id = $doc["id"];

                // Get the score of the current document
                $score = $response["debug"]["explain"][$current_id];
                $score = substr($score, 0, 6);
                $score = trim($score);

                // Check if any of the highlighted fields for the current document are set
                // If so, store the first value in $highlighted
                if(isset($response["highlighting"][$current_id]["title"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["title"][0];
                }
                elseif (isset($response["highlighting"][$current_id]["booktitle"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["booktitle"][0];
                }
                elseif (isset($response["highlighting"][$current_id]["journal"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["journal"][0];
                }
                elseif (isset($response["highlighting"][$current_id]["author"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["author"][0];
                }

                 // Add the current document's information to the HTML output
                $html .= "<h3>";
                $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
                $html .= "<p>".$highlighted."</p>";
                $html .= "<hr>";
            }
            // Output the HTML
            echo $html;
            break;
            
        case 'author':
            $processed_query = str_replace(' ', '%20', $query);
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=true&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q=author:"'.$processed_query.'"&useParams=';
            
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            $response = json_decode($response,true);
            $numFound = $response['response']['numFound'];
            
            $html = "<h1><u>Search results for: ".$query."</u></h1>\n";
            
            foreach ($response["response"]["docs"] as $doc) {
                
                $current_id = $doc["id"];

                $score = $response["debug"]["explain"][$current_id];
                $score = substr($score, 0, 6);
                $score = trim($score);

                if(isset($response["highlighting"][$current_id]["title"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["title"][0];
                }
                elseif (isset($response["highlighting"][$current_id]["booktitle"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["booktitle"][0];
                }
                elseif (isset($response["highlighting"][$current_id]["journal"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["journal"][0];
                }
                elseif (isset($response["highlighting"][$current_id]["author"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["author"][0];
                }

                $html .= "<h3>";
                $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
                $html .= "<p>".$highlighted."</p>";
                $html .= "<hr>";
            }
            echo $html;
            break;
            
        case 'title':
            $processed_query = str_replace(' ', '%20', $query);
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=true&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q=title:'.$processed_query.'&useParams=';
            
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            $response = json_decode($response,true);
            $numFound = $response['response']['numFound'];
            
            $html = "<h1><u>Search results for: ".$query."</u></h1>\n";
            
            foreach ($response["response"]["docs"] as $doc) {
                
                $current_id = $doc["id"];

                $score = $response["debug"]["explain"][$current_id];
                $score = substr($score, 0, 6);
                $score = trim($score);

                // handle documents that the query matched in some other field than title
                if(isset($response["highlighting"][$current_id]["title"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["title"][0];
                    $html .= "<h3>";
                    $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
                    $html .= "<p>".$highlighted."</p>";
                    $html .= "<hr>";
                }
            }
            echo $html;
            break;
            
        case 'booktitle':
            $processed_query = str_replace(' ', '%20', $query);
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=true&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q=booktitle:'.$processed_query.'&useParams=';
            
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            $response = json_decode($response,true);
            $numFound = $response['response']['numFound'];
            
            $html = "<h1><u>Search results for: ".$query."</u></h1>\n";
            
            foreach ($response["response"]["docs"] as $doc) {
                
                $current_id = $doc["id"];

                $score = $response["debug"]["explain"][$current_id];
                $score = substr($score, 0, 6);
                $score = trim($score);

                // handle documents that the query matched in some other field than title
                if(isset($response["highlighting"][$current_id]["booktitle"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["booktitle"][0];
                    $html .= "<h3>";
                    $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
                    $html .= "<p>".$highlighted."</p>";
                    $html .= "<hr>";
                }
            }
            echo $html;
            break;
            
        case 'journal':
            $processed_query = str_replace(' ', '%20', $query);
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=true&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q=journal:'.$processed_query.'&useParams=';
            
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            $response = json_decode($response,true);
            $numFound = $response['response']['numFound'];
            
            $html = "<h1><u>Search results for: ".$query."</u></h1>\n";
            
            foreach ($response["response"]["docs"] as $doc) {
                
                $current_id = $doc["id"];

                $score = $response["debug"]["explain"][$current_id];
                $score = substr($score, 0, 6);
                $score = trim($score);

                // handle documents that the query matched in some other field than title
                if(isset($response["highlighting"][$current_id]["journal"][0])) {
                    $highlighted = $response["highlighting"][$current_id]["journal"][0];
                    $html .= "<h3>";
                    $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
                    $html .= "<p>".$highlighted."</p>";
                    $html .= "<hr>";
                }
            }
            echo $html;
            break;
            
        default:
            echo "If you see me, please pretend you don't.";
            break;
        
        // If no documents are found, display a message and image
        if ($numFound == 0){
            echo '<p>No documents were found<br>You did a hole in the water. See for yourself:</p><br>';
            echo '<img src="/media/images/hole-in-the-water.png">';    
        }
}
    
    