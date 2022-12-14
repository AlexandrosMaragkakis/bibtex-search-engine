<?php
require_once('header.php');
?>

<main>
    <br><br>
    <?php 
    
        $solr_server = 'http://solr:8983/solr/new_authors/';
        $solr_api = 'select?indent=true&q.op=OR&q=*%3A*&rows=0&useParams=&wt=json';
        $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");

        $response = json_decode($response,true);
        $numFound = $response['response']['numFound'];
        
        //$numFound = $response['response']['numFound'];
        echo '<p>&nbsp&nbsp&nbspNumber of documents in the index: '.$numFound.'</p>';
    ?>
    <br>
    <hr>
    <form action="includes/delete.inc.php" method="post">
        &nbsp&nbspEnter the <b>full</b> name of the author you wish to delete: <br> <br>
        <input autofocus required type="text" name="name">
        <input type="submit" name="submit" class="gbutton" value="Delete">
    </form>
    <hr>
    <form action="includes/delete.inc.php" method="post">
        &nbsp&nbsp&nbspDelete <b>all</b> index entries, the boss went crazy<br><br>
        &nbsp&nbsp&nbsp<input type="submit" name="submit" class="gbutton" value="Delete-all">
    </form>

</main>




<script>
// class active so that the current page goes green
document.querySelector("#delete").outerHTML =
    '<a id="delete" class="col-s-12 col-m-4 col-l-4 active" title="Delete Document" href="/delete-doc.php">Delete Document</a>';
</script>