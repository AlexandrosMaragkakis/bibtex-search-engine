<?php
    
    switch($_POST['submit']) {
        
    case 'Delete':
        
        $selectedDocIds = $_POST['doc-id'];
        $solr_server = 'http://solr:8983/solr/new_authors/';
        $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
        
    
        // Loop through the array of selected values.
        foreach ($selectedDocIds as $id) {
            $delete_xml = '<delete><query>'.$id.'</query></delete>';
            $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");
            $xmlElement = new SimpleXMLElement($output);
            $status = (int)$xmlElement->lst[0]->int[0];
            if ($status == 0) {
                echo 'Deletion successful.';
            }
        }
        break;
        
    case 'Delete-all':
        $solr_server = 'http://solr:8983/solr/new_authors/';
        $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
        $delete_xml = '<delete><query>*:*</query></delete>';
        $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");
        echo "The core is now empty.";
        
        break;
    }
    
?>