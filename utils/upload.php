<?php
require_once "paths.php";

$newFilepath;

if (isset($_POST["submitImage"])) {
    try {
        $newFilepath = upload_image($_FILES["imageFile"]["tmp_name"]);
        echo "alert('upload success')";
    } catch (Exception $e) {
        echo "alert($e)";
    }
}

function upload_image(string $filepath): string
{
    $allowedTypes = [
        "image/png" => "png",
        "image/jpeg" => "jpg",
    ];

    $fileSize = filesize($filepath);

    // We don't want to allow users to upload empty files
    if (!$fileSize) {
        throw new Exception("The file is empty.");
    }

    $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
    $filetype = finfo_file($fileinfo, $filepath);

    // 3 MB (1 byte * 1024 * 1024 * 3 (for 3 MB))
    if ($fileSize > 3145728) {
        throw new Exception("The file is too large");
    }

    if (!in_array($filetype, array_keys($allowedTypes))) {
        throw new Exception("File not allowed.");
    }

    $uploadDir = "uploads";
    $filename = basename($filepath);
    $extension = $allowedTypes[$filetype];
    $targetDirectory = join_paths(__DIR__, $uploadDir);
    $newFilepath = join_paths($targetDirectory, $filename . "." . $extension);

    if (!copy($filepath, $newFilepath)) {
        throw new Exception("Can't move file.");
    }

    unlink($filepath);
    return join_paths($uploadDir, basename($newFilepath));
}
