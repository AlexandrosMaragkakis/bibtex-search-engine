<?php
require_once('header.php');
?>

<style>
.search-container {
    display: flex;
    align-items: center;
}


form {
    margin: auto;
    text-align: center;
}

.results-input {
    width: 60px;
    margin-left: 10px;
}

.boolean-input {
    width: 20px;
    height: 20px;
    margin-left: 10px;
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
    <div class="search-container">
        <form action="includes/search.inc.php" method="post">
            <p>Search ASTERAS:</p>
            <label>Number of Results:<input type="number" class="results-input" name="num-results" min="1" max="100"
                    value="10"></label>
            <label>Boolean search: <input type="checkbox" name="boolean-search" class="boolean-input" value="True"
                    unchecked></label>
            <input autofocus required type="text" size="40" style="height: 32px;" name="query">&nbsp&nbsp<input
                type="submit" name="submit" class="gbutton" value="Go">
            <br>
            <label><input type="radio" name="mode" value="normal" style="height: 12px; width: 12px;" checked> Just
                search!</label>
            <label><input type="radio" name="mode" value="author" style="height: 12px; width: 12px;"> Search by
                author</label>
            <label><input type="radio" name="mode" value="title" style="height: 12px; width: 12px;"> Search by
                title</label>
            <label><input type="radio" name="mode" value="booktitle" style="height: 12px; width: 12px;"> Search by book
                title</label>
            <label><input type="radio" name="mode" value="journal" style="height: 12px; width: 12px;"> Search by
                journal</label>

            <br>
        </form>
    </div>
    <hr>



</main>

</body>




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