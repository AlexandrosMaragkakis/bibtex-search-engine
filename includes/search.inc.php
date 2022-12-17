<?php

    // preprocess query

    $solr_server = 'http://solr:8983/solr/final_authors/';
    $numResults = $_POST['num-results'];
    $query = $_POST['query'];
    
    if (substr($query, 0, 1) == '"' && substr($query, -1) == '"') {
        $phrase_query = true;
    } else {
        $phrase_query = false;
    }


    if (isset($_POST['boolean-search'])) {
        $processed_query = shell_exec('python3 ../scripts/preprocess.py -query -boolean '.$query);
    }
    else {
        $processed_query = shell_exec('python3 ../scripts/preprocess.py -query -normal '.$query);
    }

    if($phrase_query == true){
        $processed_query = '"'.$processed_query.'"';
    }
    
    $processed_query = trim($processed_query);
    
    // Process the form input
    switch ($_POST['mode']) {
        case 'normal':
            
            $solr_api = 'select?debug.explain.structured=false&debug=results&debugQuery=false&hl.fl=*&hl.fragsize=100';
            $solr_api .= '&hl.method=fastVector&hl.requireFieldMatch=false&hl.simple.post=%3C%2Fb%3E&hl.simple.pre=%3Cb%3E&hl.snippets=1';
            $solr_api .= '&hl.usePhraseHighLighter=true&hl=true&fl=author%2Cid&indent=true&q.op=OR&rows='.$numResults.'&q='.$processed_query.'&useParams=';
            
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
        
        if ($numFound == 0){
            echo '<p>No documents were found<br>You did a hole in the water. See for yourself:</p><br>';
            echo '<img src="/media/images/hole-in-the-water.png">';    
        }
}
    
    