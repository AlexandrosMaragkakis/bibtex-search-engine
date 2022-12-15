<?php

    // preprocess query

    $solr_server = 'http://solr:8983/solr/final_authors/';
    $numResults = $_POST['num-results'];
    $query = $_POST['query'];
    
    //$boolean_search = $_POST['boolean-search'];
    
    // Process the form input
    switch ($_POST['mode']) {
        case 'normal':
            //radio%20topolog&useParams=
            $solr_api = 'select?fl=author';
            $solr_api .= '&hl=true&hl.highlightMultiTerm=true&hl.requireFieldMatch=false&hl.usePhraseHighLighter=true';
            $solr_api .= '&hl.method=unified&hl.fragsize=20&hl.snippets=5';
            $solr_api .= '&hl.tag.pre=<b>&hl.tag.post=</b>&rows='.$numResults.'&indent=true&q.op=OR&q=';
            $solr_api .= $query;
            $solr_api .= '&rows='.$numResults.'&useParams=&wt=json';
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");


            $myfile = fopen("result.txt", "w") or die("Unable to open file!");
            fwrite($myfile, "curl -X POST "."'".$solr_server.$solr_api."'");
            



            $response = json_decode($response,true);
            print_r($response);
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
    
    