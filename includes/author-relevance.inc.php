<?php
    
    $author1_id = $_POST['value1'];
    $flag = 'similar';
    if(isset($_POST['value2'])) {
        $author2_id = $_POST['value2'];
        $flag = 'comparison';
    }
    if($flag == 'comparison'){
        $result = shell_exec('python3 ../scripts/calculate_relevance.py '.$author1_id.' '.$author2_id);
        echo '<b>Results:'.$result.'</b>';
    }
    else{
        $solr_server = 'http://solr:8983/solr/final_authors/';
        $solr_api = 'select?indent=true&q.op=OR&q=*%3A*&rows=0&useParams=&wt=json';
        $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");

        $response = json_decode($response,true);
        $numFound = $response['response']['numFound'];
    }
    

    
?>