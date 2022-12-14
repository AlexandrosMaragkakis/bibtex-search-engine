<?php
    
    switch($_POST['submit']) {
        
    case 'Delete':
        $solr_server = 'http://solr:8983/solr/new_authors/';
        $solr_api_start = 'select?fl=id&indent=true&q.op=AND&q=author%3A%20';
        $solr_api_end = '&rows=10&useParams=$wh=json';
        $name = $_POST['name'];
        $name = explode(' ', $name);
        $name = implode('%20', $name);
        $search_output = shell_exec("curl -X POST ".$solr_server."'".$solr_api_start.$name.$solr_api_end."'");
        
        $search_output = json_decode($search_output);

        // id found in response
        if(isset($search_output->{'response'}->{'docs'}[0]->{'id'})){
            
            $id = $search_output->{'response'}->{'docs'}[0]->{'id'};
        
            $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
            $delete_xml = '<delete><query>'.$id.'</query></delete>';
            
            $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");


            $xmlElement = new SimpleXMLElement($output);
            $status = (int)$xmlElement->lst[0]->int[0];
            if ($status == 0) {
                echo $_POST['name'].' was successfully deleted from the index.';
            }
        }
        // id not found
        else{
            echo $_POST['name'].' is not in the index.';
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