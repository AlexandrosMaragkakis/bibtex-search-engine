<?php
    
    switch($_POST['submit']) {
        
    case 'Find':
        $name = $_POST['name'];

        // perform a search query for the input name
        $solr_server = 'http://solr:8983/solr/final_authors/';
        $solr_api_start = 'select?fl=id&indent=true&q.op=AND&q=author%3A%20';
        $solr_api_end = '&rows=10&useParams=$wh=json';
        $name = $_POST['name'];
        $name = explode(' ', $name);
        $name = implode('%20', $name);
        $search_output = shell_exec("curl -X POST ".$solr_server."'".$solr_api_start.$name.$solr_api_end."'");
        
        $search_output = json_decode($search_output);
        

        
        
        if(isset($search_output->{'response'}->{'docs'}[0]->{'id'})){
           
            // id found, author is already indexed, and we update the document by deleting and re-indexing
            $id = $search_output->{'response'}->{'docs'}[0]->{'id'};
            $solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary ';
            $delete_xml = '<delete><query>'.$id.'</query></delete>';
            
            $output = shell_exec("curl -X POST ".$solr_server.$solr_api."'".$delete_xml."'");


            $xmlElement = new SimpleXMLElement($output);
            $status = (int)$xmlElement->lst[0]->int[0];
            
            if ($status != 0) {
                header('Location: http://localhost:8088/add-doc.php?upload=false');
    
            }  
        }
        
        $filename = shell_exec('python3 ../scripts/get-bibtex.py '.$_POST['name']);
        $output = shell_exec('python3 ../scripts/preprocess.py -bibtex '.$filename);
        $output = trim($output);
        
        if ($output == "Failure") {
            header('Location: http://localhost:8088/add-doc.php?upload=false');
            exit();
        }


        $output = shell_exec("curl -X POST -H 'Content-Type: application/json' --data-binary @tmp.json http://solr:8983/solr/final_authors/update/json/docs?commit=true");
        $json = json_decode($output);
        $status = $json->responseHeader->status;
        if ($status == 0) {
            header('Location: http://localhost:8088/add-doc.php?upload=true');
        }  
        
        exec('rm tmp.json && rm '.$filename);
        break;
        
    case 'Upload':
        
        // handle upload
        $target_dir = "";
        $filename = $target_dir . basename($_FILES["author-doc"]["name"]);
        move_uploaded_file($_FILES["author-doc"]["tmp_name"], $filename);

        // get file extention
        $path_parts = pathinfo($_FILES["author-doc"]["name"]);
        $extension = $path_parts['extension'];

        // bibtex file case
        if($extension != "zip"){
            $output = shell_exec('python3 ../scripts/preprocess.py -bibtex '.$filename);
            $myfile = fopen("result.txt", "r") or die("Unable to open file!");
            $result = fread($myfile,filesize("result.txt")).rtrim(' ');
            if($result != "SUCCESS"){
                header('Location: http://localhost:8088/add-doc.php?upload=false');
                exec('rm result.txt');
                exit(1);
            }
            $output = shell_exec("curl -X POST -H 'Content-Type: application/json' --data-binary @tmp.json http://solr:8983/solr/final_authors/update/json/docs?commit=true");
            exec('rm tmp.json && rm '.$filename);
            $json = json_decode($output);
            $status = $json->responseHeader->status;
            if ($status == 0) {
                header('Location: http://localhost:8088/add-doc.php?upload=true');
            }  
            
        }
        // zip file case
        else{
            exec('python3 ../scripts/mass-import.py '.$filename);
            $myfile = fopen("result.txt", "r") or die("Unable to open file!");
            $result = fread($myfile,filesize("result.txt")).rtrim(' ');
            if($result == "SUCCESS"){
                header('Location: http://localhost:8088/add-doc.php?upload=true');
            }
            else{
                header('Location: http://localhost:8088/add-doc.php?upload=false');
            }
        }
        break;
    }
    exec('rm result.txt');
    exit();