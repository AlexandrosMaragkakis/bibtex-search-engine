<?php
    
    $author1_id = $_POST['value1'];
    $flag = 'similar';
    $numresults = $_POST['value3'];
    
    if($_POST['value2'] != '') {
        $author2_id = $_POST['value2'];
        $flag = 'comparison';
    }
    if($flag == 'comparison'){
        $result = shell_exec('python3 ../scripts/calculate_relevance.py '.$author1_id.' '.$author2_id);
        echo '<b>Results:'.$result.'</b>';
    }
    else{
        $solr_server = 'http://solr:8983/solr/final_authors/';
        $solr_api = 'select?debugQuery=true&indent=true&q.op=OR&q=%7B!mlt%20qf%3Dbooktitle%2Ctitle%2Cjournal%7D'.$author1_id.'&rows='.$numresults.'&useParams';
        $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
        
        $response = json_decode($response,true);
        $html = ''; //"<u>Authors that are similar to ".$author1_id."</u>\n";
            
        foreach ($response["response"]["docs"] as $doc) {
                
            $current_id = $doc["id"];

            $score = $response["debug"]["explain"][$current_id];
            $score = substr($score, 0, 6);
            $score = trim($score);

            $html .= "<h3>";
            $html .= "<u>".$doc["author"][0]."</u>"." [".$score."]</h3>";
            
            $html .= "<hr><br>";
        }
        echo $html;
    }
    

    
?>