<?php
require_once('header.php');
?>
<?php
    if (isset($_GET['upload'])) {
        if ($_GET['upload'] == "false") {
            echo "<script>alert('Something went wrong.');</script>";
       } else {
            echo "<script>alert('The author was indexed successfully.');</script>";
       }
    }
?>
<main>
    <br><br>
    <hr>
    <div style="width: 40%; margin: auto;">
        <form action="includes/add.inc.php" method="post">
            Enter the <b>full</b> name of the author: <br>
            <input autofocus required type="text" name="name">
            <input type="submit" name="submit" class="gbutton" value="Find">
        </form>
        <hr>
        <form action="includes/add.inc.php" method="post" enctype="multipart/form-data">
            <p><u>Upload file bibtex file <b>(.bib)</b> or zipped bibtex files <b>(.zip)</b>:</u><br>
                bibtex files (zipped or not) should have the author's lastname as name<br>
                For example: minsky.bib</p>
            <input type="file" id="uploaded-file" name="author-doc" style="height: 44px;" accept=".bib, .zip" required>
            <input type="submit" name="submit" class="gbutton" value="Upload">
        </form>
    </div>
    <hr>
</main>
</body>

</html>
<script>
// Class active to see what page you're on
document.querySelector("#add").outerHTML =
    '<a id="add" class="col-s-12 col-m-3 col-l-3 active" title="Add New Document" href="/add-doc.php">Add New Document</a>';
</script>

