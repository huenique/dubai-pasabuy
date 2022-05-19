<?php

if (isset($_GET["logoutUser"])) {
    session_start();
    session_unset();
    session_destroy();
}

/** Navbar component */
function display_navbar() {
    echo <<<NAVBAR
    <style>
        .sneaky {
            display: none;
        }
        a {
            cursor: pointer;
        }
    </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
        <a class="navbar-brand" href="home.php">Dubai Pasabuy</a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarText"
                aria-controls="navbarText"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">My Cart</a>
                    </li>
                </ul>
                <span class="nav-item">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" id="profile-dropdown" data-bs-toggle="dropdown" aria-expanded="false">Profile</a>
                        <ul class="dropdown-menu" aria-labelledby="profile-dropdown">
                            <li><button class="dropdown-item" href="#">Settings</button></li>
                            <form method="get">
                                <input class="sneaky" name="logoutUser" value="yes"></input>
                                <li><button class="dropdown-item" type="submit" name="logoutUser">Logout</button></li>
                            </form>
                        </ul>
                    </div>
                </span>
            </div>
        </div>
    </nav>
    NAVBAR;
}