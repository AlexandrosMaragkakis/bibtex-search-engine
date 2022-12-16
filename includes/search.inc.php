<?php

    // preprocess query

    $solr_server = 'http://solr:8983/solr/final_authors/';
    $numResults = $_POST['num-results'];
    $query = $_POST['query'];
    
   
    
    // Process the form input
    switch ($_POST['mode']) {
        case 'normal':
            //
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=false&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q='.$query.'&useParams=';
    
            // case "Alexandros kaloxylos" gives illegal space character
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            
            $response = json_decode($response,true);
            
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
        case 'author':
            // Perform a search by author using the search query
            break;
        case 'title':
            // Perform a search by title using the search query
            break;
        case 'booktitle':
            // Perform a search by book title using the search query
            break;
        case 'journal':
            // Perform a search by journal using the search query
            break;
        default:
            // Invalid search mode, handle the error
            break;
}
    
    