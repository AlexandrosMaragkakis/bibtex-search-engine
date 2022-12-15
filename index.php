<?php
require_once('header.php');
?>


<main>

    <form action="includes/search.inc.php" method="post">
        Search for an author: <br>
        <input autofocus required type="text" name="searchbar">
        <input type="submit" name="query" class="gbutton" value="Go">
    </form>
</main>






<footer class="col-s-12 col-m-12 col-l-12">
    <p class="fonts">Contact us: <b>dit19120@uop.gr</b>/<b>dit19157@uop.gr</b></p>
</footer>
</body>

</html>
<script>
// class active so that the current page goes green
document.querySelector("#search").outerHTML =
    '<a id="index" class="col-s-12 col-m-4 col-l-4 active" title="Search" href="/index.php">Search</a>';
</script>