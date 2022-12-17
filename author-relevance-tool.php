<?php
require_once('header.php');
?>
<script>
function disableOption() {
    // Get the selected value from the first dropdown menu
    var selectedValue = document.getElementById("author1").value;
    // Get the options from the second dropdown menu
    var options = document.getElementById("author2").options;
    // Loop through the options
    for (var i = 0; i < options.length; i++) {
        // If the option's value is equal to the selected value from the first dropdown menu, disable the option
        if (options[i].value == selectedValue) {
            options[i].disabled = true;
        }
        // Otherwise, enable the option
        else {
            options[i].disabled = false;
        }
    }
}

function checkForm() {
    // Get the value of the dropdown menus
    var value1 = document.getElementById("author1").value;

    // If either value is empty, prevent the form submission and show an error message
    if (value1 == "") {
        alert("Please select a valid option");
        return false;
    }
    // Otherwise, calculate the relevance and update the webpage with the result
    calculateRelevance();
    // Prevent the form submission
    return false;
}

// This function is triggered when the user clicks the submit button
function calculateRelevance() {
    // Get the selected values from the form
    $("#result").html('Calculating...');
    var author1 = $("#author1").val();
    var author2 = $("#author2").val();
    var rows = $("#rows").val();
    console.log("rows:", rows);
    // Send an HTTP request to the server to calculate the relevance
    $.ajax({
        method: "POST",
        url: "includes/author-relevance.inc.php",
        data: {
            value1: author1,
            value2: author2,
            value3: rows
        },
        success: function(result) {
            // Update the webpage with the result of the calculation
            $("#result").html(result);
        }
    });
}
</script>

<style>
.form-container {
    display: flex;
    justify-content: center;
}

select {
    font-size: 18px;
}

h3 {
    display: block;
}
</style>

<br>
<h3 class="form-container">How to use the Author Relevance Tool?</h3>
<p class="form-container">Select an author from the&nbsp<b>first</b>&nbspmenu and press "Go", to retrieve authors that
    are
    similar to him/her.</p>
<p class="form-container">You also can select authors in&nbsp<b>both</b>&nbspmenus, if you wish to see how similar
    these two authors
    are.</p><br>

<div class="form-container">
    <form>
        <?php
            $solr_server = 'http://solr:8983/solr/final_authors/';
            $solr_api = 'select?fl=author%2Cid&indent=true&q.op=OR&q=*%3A*&rows=1000&sort=field(author)%20asc&useParams=';
            $response = shell_exec("curl -X POST "."'".$solr_server.$solr_api."'");
            $response = json_decode($response, true);
            
            $html = '<select onchange="disableOption()" required id="author1">';
            $html .= '<option value="">--</option>';
            // iterate over the data and create an option element for each item
            foreach ($response["response"]["docs"] as $author) {
                $html .= '<option value="' . $author['id'] . '">' . $author['author'][0] . '</option>';
            }
            $html .= '</select><br><br>';

            $html .= '<select id="author2">';
            $html .= '<option value="">--</option>';
            // iterate over the data and create an option element for each item
            foreach ($response["response"]["docs"] as $author) {
                $html .= '<option size="50" value="' . $author['id'] . '">' . $author['author'][0] . '</option>';
            }
            $html .= '</select>&nbsp&nbsp&nbsp';

            $html .= '<label>Number of Results:<input id="rows" type="number" class="results-input" name="rows" min="1" max="100"
            value="10"></label>';
            $html .= '<input style="width: 250px;" class="gbutton" type="button" value="Go" onclick="checkForm()">';
            echo $html;
        ?>
    </form>
</div>
<div class="form-container" id="result"></div>
</body>

</html>
<script>
// class active so that the current page goes green
document.querySelector("#relevance").outerHTML =
    '<a id="relevance" class="col-s-12 col-m-3 col-l-3 active" title="Author Relevance Tool" href="/author-relevance-tool.php">Author Relevance Tool';
</script>