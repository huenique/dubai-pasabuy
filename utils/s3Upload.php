<?php
require "path.php";
require __DIR__ . "/../db/connection.php";
require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;

$bucket = "dubai-pasabuy";
$uploadPath = join_paths(dirname(__DIR__, 1), "uploads");
$maxsize = 10 * 1024 * 1024;

// Instantiate an Amazon S3 client.
$s3Client = new S3Client([
    "version" => getenv("S3_VERSION"),
    "region" => getenv("S3_REGION"),
    "credentials" => [
        "key" => getenv("AWS_KEY"),
        "secret" => getenv("AWS_SECRET"),
    ],
]);

if (isset($_POST["submitProductImg"])) {
    $productId = $_POST["productId"];

    if (!isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0) {
        die("Error: " . $_FILES["anyfile"]["error"]);
    }

    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $filename = $_FILES["anyfile"]["name"];
    $filetype = $_FILES["anyfile"]["type"];
    $filesize = $_FILES["anyfile"]["size"];

    $filePath = join_paths($uploadPath, $filename);
    $key = basename($filePath);

    // Validate file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if (!array_key_exists($ext, $allowed)) {
        die("Error: Please select a valid file format.");
    }

    if ($filesize > $maxsize) {
        die("Error: File size is larger than the allowed limit.");
    }

    // Validate type of the file
    if (!in_array($filetype, $allowed)) {
        die("Error: There was a problem uploading your file. Please try again.");
    }

    // Check whether file exists before uploading it
    if (file_exists($filePath)) {
        die($filename . " already exists at " . $filePath);
    }

    if (!move_uploaded_file($_FILES["anyfile"]["tmp_name"], $filePath)) {
        die("File was not uploaded.");
    }

    try {
        $result = $s3Client->putObject([
            "Bucket" => $bucket,
            "Key" => $key,
            "Body" => fopen($filePath, "r"),
            "ACL" => "public-read", // make file "public"
        ]);
        $imgPath = $result->get("ObjectURL");
        $conn->query("UPDATE products SET media='$imgPath' WHERE id='$productId'");

        // Remove uploaded path to keep the storage system clean.
        unlink($filePath);

        // Redirect to admin page once upload is complete.
        $parentUrl = str_replace("utils/s3Upload", "admin", $_SERVER["REQUEST_URI"]);
        header("Location: $parentUrl");
    } catch (Aws\S3\Exception\S3Exception$e) {
        echo "<strong>There was an error uploading the file.</strong>\n";
        echo $e->getMessage();
    }
}
