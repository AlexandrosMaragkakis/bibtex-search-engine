<?php
require_once('header.php');
?>

<style>
form {
    margin: auto;
    text-align: center;
}

p {
    font-family: Arial, sans-serif;
    font-size: 35px;
    color: #333;
    text-align: center;
    margin-bottom: 5px;
    margin-top: 40px
}
</style>


<main>

    <form action="includes/search.inc.php" method="post">
        <p>Search for an author:</p>
        <input autofocus required type="text" name="searchbar"><input type="submit" name="query" class="gbutton"
            value="Go">
        <br>

        <label><input type="radio" name="mode" value="normal" style="height: 12px; width: 12px;" checked> Normal
            search</label>
        <label><input type="radio" name="mode" value="author" style="height: 12px; width: 12px;"> Search by
            author</label>
        <label><input type="radio" name="mode" value="title" style="height: 12px; width: 12px;"> Search by title</label>
        <label><input type="radio" name="mode" value="booktitle" style="height: 12px; width: 12px;"> Search by book
            title</label>
        <label><input type="radio" name="mode" value="journal" style="height: 12px; width: 12px;"> Search by
            journal</label>
        <br>
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