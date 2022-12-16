<!DOCTYPE html>
<html>
<script>
// Reload page every time when accessed from history, f.e. the back button.
if (performance.navigation.type == 2) {
    location.reload(true);
}
</script>



<head>
    <title>ASTERAS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style1.css">
    <link rel="icon" href="/media/images/icons8-sun-star-16.png">
</head>
<header class="col-s-12 col-m-12 col-l-12">
    <a href="index.php" style="text-decoration: none;color: #000000;">
        <h1>ASTERAS<img src="/media/images/icons8-sun-star-48.png">&nbsp BibTeX Search Engine</h1>
    </a>
</header>
<nav class="header-nav">
    <ul class="list">
        <li><a id="search" class="col-s-12 col-m-2 col-l-4" title="Search" href="/index.php">Search</a></li>
        <li><a id="add" class="col-s-12 col-m-2 col-l-4" title="Add New Document" href="/add-doc.php">Add New
                Document</a></li>
        <li><a id="delete" class="col-s-12 col-m-2 col-l-4" title="Delete Document" href="/delete-doc.php">Delete
                Document</a></li>
    </ul>
</nav>

<body onunload="refreshPage()">