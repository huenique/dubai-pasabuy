<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

function display_items(mysqli $conn, string $table): void {
    $items = $conn->query("SELECT * FROM $table");
    foreach ($items->fetch_all(MYSQLI_ASSOC) as $row) {
        echo <<<ITEM
            <div class="card item-card">
                <img src="../pasabuy/static/item.png" class="card-img-top" alt="...">
                <div class="card-body">
                    <p class="card-text">{$row["name"]}</p>
                </div>
            </div>
        ITEM;
    }
    $items -> free_result();
};
?>
<title>Dubai Pasabuy</title>

<div class="container" >
    <h1>Pasabuy Today</h1>
    <div class="btn-group btn-group-lg" role="group" aria-label="menu">
        <button type="button" class="btn btn-outline-dark" onclick="displayCurrentItems()">on hand</button>
        <button type="button" class="btn btn-outline-dark" onclick="displayNextItems()">next batch</button>
    </div>
    <section id="current-items" class="hidden-section"><?php display_items($conn, "current_items") ?></section>
    <section id="next-items" class="hidden-section"><?php display_items($conn, "next_items") ?></section>
</div>

<script>
if (!document.getElementById("current-items").style.display) {
    displayCurrentItems();
}

function displayCurrentItems() {
    document.getElementById("next-items").style.display = "none";
    document.getElementById("current-items").style.display = "unset";
}

function displayNextItems() {
    document.getElementById("current-items").style.display = "none"
    document.getElementById("next-items").style.display = "unset";
}
</script>
