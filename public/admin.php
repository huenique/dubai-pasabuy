<?php

require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

/**
 * Create a dynamic table based.
 * @param \mysqli_result $columns
 * @param \mysqli_result $rows
 * @param string $columnWidth
 * @param string $title
 * @return void
 */
function display_table(
    mysqli_result $columns,
    mysqli_result $rows,
    string $columnWidth = "",
    string $title = ""
): void {
    // Sensitive information should not be shown (e.g. passwords)
    $exemptedCols = array("password", "cart");
    $tableColumns = "";
    $tableRows = "";
    $smallTitle = strtolower(substr($title, 0, -1));

    // Consider the entire table as a form. We want to somehow link the
    // checkboxes to the actions button next to the table header
    echo "<form method='post'>";

    // Create the table headers
    foreach ($columnNames = $columns->fetch_all(MYSQLI_ASSOC) as $column) {
        if (!in_array($columnField = $column["Field"], $exemptedCols)) {
            $columnField = ucwords(str_replace('_', ' ', $columnField));
            $tableColumns .= "<th  class='$columnWidth' scope='col'>{$columnField}</th>";
        }
    }

    $tableColumns .= <<<PARENT_CHECKBOX
    <th></th>
    <th>
        <div class="form-check">
            <input
                class="form-check-input $smallTitle"
                type="checkbox"
                value=""
                id="parent-checkbox-$smallTitle"
            />
            <label class="form-check-label" for="parent-checkbox-$smallTitle">
            </label>
        </div>
    </th>
    PARENT_CHECKBOX;

    // Create the table rows
    foreach ($rows->fetch_all(MYSQLI_ASSOC) as $row) {
        $tableRows .= "<tr class='align-text-top'>";
        foreach ($columnNames as $col) {
            if (!in_array($colField = $col["Field"], $exemptedCols)) {
                if (($smallTitle !== "orders") && ($colField !== "item")) {
                    $rowColVal = $row[$colField];
                    $tableRows .= "<td class='$columnWidth'>{$rowColVal}</td>";
                } else {
                    // Parse orders item column
                    // print_r($row[$colField]);

                    // $array_keys = array_keys(json_decode($row[$colField], true));
                    // foreach ($array_keys as $array_key) {
                    //     echo $array_key;
                    // }

                    $tableRows .= "<td class='$columnWidth'>";
                    $itemDisplay = "";
                    foreach (json_decode($row[$colField], true) as $items) {
                        foreach ($items as $itemName => $value) {
                            $itemDisplay .= "<p class='fst-italic fw-bolder text-decoration-underline'>$itemName</p>" . implode("<br>", $items[$itemName]) . "<br><br>";
                        }
                    }
                    $tableRows .= $itemDisplay;
                    $tableRows .= "</td>";
                }

            }
        }

        // Create the row options. Edit buttons and checkboxes included
        $checkboxName = $smallTitle . "Select[]";

        // Only allow row edits for the products table. Update to other tables
        // should only be performed either by the system or customer
        $editOption = ($smallTitle !== "product") ? ""
        : <<<EDIT_BTN
        <i
            data-feather="edit"
            class="mx-1 idiom-pointer"
            data-bs-toggle="modal"
            data-bs-target="#edit-modal"
            id="edit-trigger-{$row["id"]}-{$row["access"]}-{$row["name"]}-{$row["cost_aed"]}-{$row["cost_php"]}"
        />
        EDIT_BTN;

        $tableRows .= <<<ROW_OPTIONS
        <td class="$columnWidth">
            $editOption
            <th scope="col">
                <input
                    class="form-check-input $smallTitle"
                    type="checkbox"
                    name="$checkboxName"
                    value="{$row["id"]}"
                    id="{$row["id"]}"
                />
                <label for="{$row["id"]}"></label>
            </th>
        </td>
        ROW_OPTIONS;
        $tableRows .= "</tr>";
    }

    // Only enable row inserts for products table
    $insertAction = ($smallTitle !== "product") ? ""
    : <<<INSERT_ACTION
    <li>
        <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#insert-modal">
            <i class="feather-default" data-feather="plus-circle"></i> Insert new {$smallTitle}
        </button>
    </li>
    INSERT_ACTION;

    echo <<<TITLE_AND_ACTIONS
    <div class="d-flex mb-5">
        <h3 class="flex-grow-1" id='section-title'>$title</h3>
        <div class="dropdown">
            <button
                class="btn btn-secondary dropdown-toggle"
                type="button" id="dropdownMenuButton1"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li>
                    <button class="dropdown-item" type="submit" name="delete-selected">
                        <i class="feather-default" data-feather="trash"></i>Delete selected
                    </button>
                </li>
                $insertAction
            </ul>
        </div>
    </div>
    TITLE_AND_ACTIONS;

    echo "<div class='table-responsive-sm'>";
    echo "<table class='table align-middle'>";
    echo "<thead>$tableColumns</thead>";
    echo "<tbody>";
    echo $tableRows;
    echo "</tbody></table></div>";

    echo <<<SCRIPTS
    <script>
        let parentCheckbox$smallTitle = document.getElementById("parent-checkbox-$smallTitle");
        parentCheckbox$smallTitle.addEventListener("change", e => {
            document.querySelectorAll("tbody .$smallTitle").forEach(checkbox => {
                checkbox.checked = e.target.checked
            })
        });

        document.querySelectorAll("tbody .$smallTitle").forEach(checkbox => {
            checkbox.addEventListener("change", ()=> {
                let tbodyCheckbox = document.querySelectorAll("tbody .$smallTitle").length;
                let tbodyCheckedbox = document.querySelectorAll("tbody .$smallTitle:checked").length;

                if(tbodyCheckbox == tbodyCheckedbox){
                    parentCheckbox$smallTitle.indeterminate = false;
                    parentCheckbox$smallTitle.checked = true;
                }

                if (tbodyCheckbox > tbodyCheckedbox && tbodyCheckedbox>=1) {
                    parentCheckbox$smallTitle.indeterminate = true;
                }

                if(tbodyCheckedbox==0) {
                    parentCheckbox$smallTitle.indeterminate = false;
                    parentCheckbox$smallTitle.checked = false;
                }

            })
        })
    </script>
    SCRIPTS;

    // End of table/form
    echo "</form>";

    $columns->free_result();
    $rows->free_result();
};

/**
 * Display the orders table from the database.
 * NOTE: Call this on page load to prevent page loads.
 */
function display_orders(mysqli $conn)
{
    display_table(
        $conn->query("DESCRIBE orders"),
        $conn->query("SELECT * FROM orders"),
        "",
        "Orders"
    );
}

/**
 * Display the products table from the database.
 * NOTE: Call this on page load to prevent page loads.
 */
function display_products(mysqli $conn)
{
    display_table(
        $conn->query("DESCRIBE products"),
        $conn->query("SELECT * FROM products"),
        "",
        "Products"
    );
}

/**
 * Display the customers table from the database.
 * NOTE: Call this on page load to prevent page loads.
 */
function display_customers(mysqli $conn)
{
    display_table(
        $conn->query("DESCRIBE customers"),
        $conn->query("SELECT * FROM customers"),
        "",
        "Customers"
    );
}

session_start();

$path = preg_replace("~.*/~", "", $_SERVER['REQUEST_URI']);

// Set the session admin
// TODO: impl proper authentication
if (isset($_POST["adminUser"])) {
    $_SESSION["sessionAdmin"] = $_POST["adminUser"];
}

if (isset($_GET["signoutAdmin"])) {
    $_SESSION["sessionAdmin"] = null;
    header("Location: admin");
}

// Update products table row on admin request
if (isset($_POST["targetRowId"])) {

    $id = $_POST["targetRowId"];
    $name = $_POST["productName"];
    $costAed = $_POST["costAed"] ? $_POST["costAed"] : null;
    $costPhp = $_POST["costPhp"] ? $_POST["costPhp"] : null;
    $access = $_POST["productAccess"];

    $stmt = $conn->prepare("UPDATE products SET `name`=?,cost_aed=?,cost_php=?,access=? WHERE id=?");
    $stmt->bind_param("sddsi", $name, $costAed, $costPhp, $access, $id);
    $stmt->execute();
}

// Insert row into products table on admin request
if (isset($_POST["newRowId"])) {

    $name = $_POST["productName"];
    $costAed = $_POST["costAed"] ? $_POST["costAed"] : null;
    $costPhp = $_POST["costPhp"] ? $_POST["costPhp"] : null;
    $access = $_POST["productAccess"];

    $stmt = $conn->prepare("INSERT INTO products (`name`,cost_aed,cost_php,access) VALUES (?,?,?,?)");
    $stmt->bind_param("sdds", $name, $costAed, $costPhp, $access);
    $stmt->execute();
}

// Delete the selected rows from the db
if (isset($_POST["delete-selected"])) {
    if (isset($_POST["customerSelect"])) {
        foreach ($_POST["customerSelect"] as $id) {
            $conn->query("DELETE FROM customers WHERE id='$id'");
        }
    } elseif (isset($_POST["productSelect"])) {
        foreach ($_POST["productSelect"] as $id) {
            $conn->query("DELETE FROM products WHERE id='$id'");
        }
    } elseif (isset($_POST["orderSelect"])) {
        foreach ($_POST["orderSelect"] as $id) {
            $conn->query("DELETE FROM orders WHERE id='$id'");
        }
    }
}
?>
<title>Dashboard</title>
<style>
    .feather-default {
        width: 16px;
        height: 16px;
        margin-bottom: 2%;
        margin-right: 4%;
    }
    .brand-logo {
        max-width: 40%;
    }
</style>

<?php if ($path === "admin" && isset($_SESSION["sessionAdmin"])) {?>
<body data-new-gr-c-s-check-loaded="14.1016.0" data-gr-ext-installed="">

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 pe-3 ps-3 pb-3" href="admin">
        <img class="brand-logo" src="static/img/pasabuy_logo.png">
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">
    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <form method="get" id="sign-out">
                <input class="input-default" name="signoutAdmin">
                <a class="nav-link px-3" href="javascript:;" onclick="document.getElementById('sign-out').submit()">Sign out</a>
            </form>
        </div>
    </div>
</header>
<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item" onclick="showSummary()">
                        <a class="nav-link active" aria-current="page" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home" aria-hidden="true">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg> Dashboard </a>
                    </li>
                    <li class="nav-item" onclick="showOrders()">
                        <a class="nav-link" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file" aria-hidden="true">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                <polyline points="13 2 13 9 20 9"></polyline>
                            </svg> Orders </a>
                    </li>
                    <li class="nav-item" onclick="showProducts()">
                        <a class="nav-link" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart" aria-hidden="true">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg> Products </a>
                    </li>
                    <li class="nav-item" onclick="showCustomers()">
                        <a class="nav-link" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg> Customers </a>
                    </li>
                </ul>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 vh-100">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                    </div>
                </div>
            </div>
            <div class="section-default" id="summary-section">
                <h3>Welcome, <?php echo $_SESSION["sessionAdmin"]; ?>!</h3>
            </div>
            <div class="section-default" id="orders-section"> <?php display_orders($conn);?> </div>
            <div class="section-default" id="products-section"> <?php display_products($conn);?> </div>
            <div class="section-default" id="customers-section"> <?php display_customers($conn);?> </div>
        </main>
    </div>
    <!-- Modal for editing table rows -->
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="edit-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-modal">Update Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Update-products-table-row Form -->
                <form method="post">
                    <div class="modal-body">
                        <input class="input-default" id="modal-target-row" name="targetRowId" value="">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Name</span>
                            <input type="text" class="form-control" id="curr-product-name" name="productName" value="" />
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Cost AED</span>
                            <input type="text" class="form-control" id="curr-cost-aed" name="costAed" value="" />
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Cost PHP</span>
                            <input type="text" class="form-control" id="curr-cost-php" name="costPhp" value="" />
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Access</span>
                            <select class="form-select" name="productAccess">
                                <option id="current-access" selected></option>
                                <option id="alt-access" value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
                <form action="utils/s3Upload" method="post" enctype="multipart/form-data">
                    <div class="modal-body border-top">
                        <p class="">Upload product media:</p>
                        <p>Note: Only .jpg, .jpeg, .gif, .png formats allowed to a max size of 5 MB.</p>
                        <label for="file_name">Filename:</label>
                        <input type="file" name="anyfile" id="anyfile">
                        <input class="input-default" type="text" id="upload-product-id" name="productId" value="">
                        <input type="submit" name="submitProductImg" value="Upload">
                    </div>
                </form>
                <!-- /Update-products-table-row Form -->
            </div>
        </div>
    </div>
    <!-- /Modal for editing table rows -->
    <!-- Modal for inserting table rows -->
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="insert-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insert-modal">Insert Porduct</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Insert-products-table-row Form -->
                <form method="post">
                    <div class="modal-body">
                        <input class="input-default" id="modal-target-row" name="newRowId" value="">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Name</span>
                            <input type="text" class="form-control" name="productName" value="" />
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Cost AED</span>
                            <input type="text" class="form-control" name="costAed" value="" />
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Cost PHP</span>
                            <input type="text" class="form-control" name="costPhp" value="" />
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Access</span>
                            <select class="form-select" name="productAccess">
                                <option value="next" selected>next</option>
                                <option value="onhand">onhand</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
                <!-- /Insert-products-table-row Form -->
            </div>
        </div>
    </div>
    <!-- /Modal for inserting table rows -->
</div>

<!-- AuthN user --> <?php } else {?>
<div class="vh-100 d-flex justify-content-center align-items-center">
    <div class="card w-25">
        <div class="card-body">
            <h3 class="card-title">Welcome!</h3>
            <div class="mt-5">
                <form method="post">
                    <div class="mb-3">
                        <label for="admin-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="admin-username" name="adminUser">
                    </div>
                    <div class="mb-3">
                        <label for="admin-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="admin-password" name="adminPassw">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php }?>
<!-- /AuthN user -->

<script>
    feather.replace()
</script>
<script defer>
    // Helpers for page navigation.
    showSummary();

    function resetDisplay() {
        let sections = document.querySelectorAll(".section-default");
        sections.forEach(section => {
            section.style.display = "none";
        });

        // Uncheck all checkboxes
        document.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false);
    }

    function showOrders() {
        resetDisplay();
        document.getElementById("orders-section").style.display = "unset";
    }

    function showProducts() {
        resetDisplay();
        document.getElementById("products-section").style.display = "unset";
    }

    function showCustomers() {
        resetDisplay();
        document.getElementById("customers-section").style.display = "unset";
    }

    function showSummary() {
        resetDisplay();
        document.getElementById("summary-section").style.display = "unset";
    }

    document.getElementById("edit-modal").addEventListener("show.bs.modal", (e) => {
        const currNameInp = document.getElementById("curr-product-name");
        const currCostAedInp = document.getElementById("curr-cost-aed");
        const currCostPhpInp = document.getElementById("curr-cost-php");
        const currAccessInp = document.getElementById("current-access");
        const altAccessInp = document.getElementById("alt-access");

        // Extract row data from modal trigger
        const data = (e.relatedTarget.id).split("-");
        const currAccess = data[3];
        const rowId = data[2];
        const altAccess = (currAccess === "next") ? "onhand" : "next";

        // Inject modal values
        document.getElementById("modal-target-row").value = rowId;
        document.getElementById("upload-product-id").value = rowId;

        currAccessInp.value = currAccess;
        currAccessInp.innerText = currAccess;
        altAccessInp.value = altAccess;
        altAccessInp.innerText = altAccess;

        currNameInp.value = data[4];
        currCostAedInp.value = data[5];
        currCostPhpInp.value = data[6];
    });
</script>
