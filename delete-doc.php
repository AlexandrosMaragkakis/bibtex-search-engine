<?php
require_once('header.php');
?>

<?php
    if (isset($_GET['delete'])) {
    	if ($_GET['delete'] == "false") {
            echo "<script>alert('Something went wrong.');</script>";
        } else {
            echo "<script>alert('Author was successfully deleted.');</script>";
        }
    } elseif (isset($_GET['deleteall'])) {
        echo "<script>alert('All documents were deleted.');</script>";
    }
?>
<main>
    <br><br>
    <?php
        $solr_server = 'http://solr:8983/solr/final_authors/';
        $solr_api = 'select?indent=true&q.op=OR&q=*%3A*&rows=0&useParams=&wt=json';
        $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");

        $response = json_decode($response, true);
        $numFound = $response['response']['numFound'];

        echo '<p>&nbsp&nbsp&nbspNumber of documents in the index: '.$numFound.'</p>';
    ?>
    <br>
    <hr>
    <?php
    
    // Set the URL of the Solr server and the API endpoint to use.
    $solr_server = 'http://solr:8983/solr/final_authors/';
    $solr_api = 'select?fl=author%2Cid&indent=true&q.op=OR&q=*%3A*&rows=1000&useParams=';

    // Make a POST request to the Solr server and retrieve the response.
    $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");

    // Decode the response and generate the HTML for the form.
    $response = json_decode($response, true);
    $html = '<form id="form1" action="includes/delete.inc.php" method="post">';
    $html .= "<table>\n";
    $html .= "<tr><th>Author</th><th>Select</th></tr>\n";
    
    foreach ($response["response"]["docs"] as $doc) {
        $html .= "<tr>";
        // Add a checkbox to each row.
        $html .= "<td>" . $doc["author"][0] . "</td>";
        $html .= "<td><input type='checkbox' name='doc-id[]' value='" . $doc["id"] . "'</td>";
        $html .= "</tr>\n";
    }
    $html .= "</table>\n";
    $html .= '<input type="submit" id="submit1" name="submit" class="gbutton" value="Delete" disabled>';
    $html .= "</form";
    echo $html;

    ?>

    <hr>
    <hr>
    <form id="form2" action="includes/delete.inc.php" method="post">
        &nbsp&nbsp&nbspDelete <b>all</b> index entries<br><br>
        &nbsp&nbsp&nbsp<input type="submit" name="submit" class="gbutton" value="Delete-all">
    </form>

</main>
</body>

</html>
<script>
// Class active so that the current page goes green.
document.querySelector("#delete").outerHTML =
    '<a id="delete" class="col-s-12 col-m-3 col-l-3 active" title="Delete Document" href="/delete-doc.php">Delete Document</a>';
</script>

<script>
// Get the form and the submit button.
const form = document.getElementById('form1');
const submitButton = document.getElementById('submit1');

// Add a "click" event listener to each checkbox.
const checkboxes = form.querySelectorAll('input[type=checkbox]');
// Define the checkbox variable outside the for loop.
let checkbox;
for (checkbox of checkboxes) {
    // Use the checkbox variable inside the for loop.
    checkbox.addEventListener('click', () => {
        // Check if any of the checkboxes are checked.
        let checked = false;
        for (const cb of checkboxes) {
            if (cb.checked) {
                checked = true;
                break;
            }
        }

        // Log the current state of the checkboxes and the submit button.
        console.log('Checked:', checked);
        console.log('Submit button enabled:', !submitButton.disabled);

        // Enable the submit button if at least one checkbox is checked.
        // Otherwise, disable it.
        submitButton.disabled = !checked;
    });
}
</script>

