<!-- This is an HTML5 document, it doesn't contain any Hack code. -->
<!-- I am talking to you, GitHub's language detection algorithm. -->
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
    <script src="https://code.jquery.com/jquery-3.6.2.min.js"></script>
</head>
<header class="col-s-12 col-m-12 col-l-12">
    <a href="index.php" style="text-decoration: none;color: #000000;">
        <h1>ASTERAS<img src="/media/images/icons8-sun-star-48.png">&nbsp BibTeX Search Engine</h1>
        <h4>~Automated Search for TExt and ReseArcherS~</h3>
    </a>
</header>
<nav class="header-nav">
    <ul class="list">
        <li><a id="search" class="col-s-12 col-m-3 col-l-3" title="Search" href="/index.php">Search</a></li>
        <li><a id="relevance" class="col-s-12 col-m-3 col-l-3" title="Author Relevance Tool"
                href="/author-relevance-tool.php">Author Relevance Tool
            </a></li>
        <li><a id="add" class="col-s-12 col-m-3 col-l-3" title="Add New Document" href="/add-doc.php">Add New
                Document</a></li>
        <li><a id="delete" class="col-s-12 col-m-3 col-l-3" title="Delete Document" href="/delete-doc.php">Delete
                Document</a></li>

    </ul>
</nav>

<body onunload="refreshPage()">