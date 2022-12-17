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
    
?>