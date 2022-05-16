<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

session_start();

// map to allow different html page interactions to call php functions.
if (array_key_exists("itemQuantity", $_POST)) {
    set_item_quantity();
}

// slider: quantity 
function set_item_quantity() {
    echo "<script>alert('amount updated!')</script>";
}

// checkout -> record to database
function checkout() {}

// remove -> delete from db
function remove_from_cart() {}
?>

<div class="container">
    <h1>My Cart</h1>
    <div class="d-flex justify-content-center">
        <div class="card item-card me-5">
            <img src="static/item.png" class="card-img-top" alt="...">
        </div>
        <div class="d-flex flex-column">
            <p>Item description</p>
            <span id="value">
                0
            </span>
            <!-- save amount to user's cart table with corresponding item ID -->
            <!-- if the item ID already has an existing amount, replace it -->
            <form action="#" method="post">
                <input id="input-value" style="display: none" value="0" name="itemQuantity">
                <button type="submit" class="btn btn-primary w-25">
                    <i data-feather="check"></i>
                </button>
            </form>

            <div class="btn-container">
                <button class="btn decrease">decrease</button>
                <button class="btn reset">reset</button>
                <button class="btn increase">increase</button>
            </div>
        </div>

    </div>
</div>

<script>
//set initial count
let count = 0;

//select value and buttons
const value = document.querySelector("#value");
const btn = document.querySelectorAll(".btn");

btn.forEach(function (btn) {
    btn.addEventListener("click", function (e) {
        const styles = e.currentTarget.classList;

        if (styles.contains("decrease")) {
            if (count > 0) {
                count--;
            }
        } else if (styles.contains("increase")) {
            count++;
        } else {
            count = 0;
        }
        if (count > 0) {
            value.style.color = "green";
        }
        // if (count < 0) {
        //     value.style.color = "red";
        // }
        if (count === 0) {
            value.style.color = "#222";
        }
        value.textContent = count;
        document.getElementById("input-value").value = count;
  });
});
</script>
<script>
    feather.replace()
</script>
