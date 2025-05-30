<?php
session_start();

// Directory to store uploaded files
define('UPLOAD_DIR', __DIR__ . '/uploads/');
// Directory to store generated download links
define('LINKS_FILE', __DIR__ . '/links.json');

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);

// Ensure links file exists
if (!file_exists(LINKS_FILE)) file_put_contents(LINKS_FILE, json_encode([]));

// Load links from file
function loadLinks() {
    return json_decode(file_get_contents(LINKS_FILE), true);
}

// Save links to file
function saveLinks($links) {
    file_put_contents(LINKS_FILE, json_encode($links));
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $filePath = UPLOAD_DIR . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        echo "File uploaded successfully: $fileName<br>";
        echo "Generate a permanent link below.<br>";
    } else {
        echo "Failed to upload file.<br>";
    }
}

// Generate permanent link
if (isset($_POST['generate_link']) && !empty($_POST['file_name'])) {
    $fileName = basename($_POST['file_name']);
    $filePath = UPLOAD_DIR . $fileName;

    if (file_exists($filePath)) {
        $links = loadLinks();

        // Check if a link already exists for this file
        $token = array_search($filePath, $links);
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            $links[$token] = $filePath;
            saveLinks($links);
        }

        $link = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?download=$token";
        echo "Permanent download link: <a href=\"$link\">$link</a><br>";
    } else {
        echo "File does not exist.<br>";
    }
}

// Handle file download
if (isset($_GET['download'])) {
    $token = basename($_GET['download']);
    $links = loadLinks();

    if (isset($links[$token]) && file_exists($links[$token])) {
        $filePath = $links[$token];
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    } else {
        echo "Invalid or expired link.<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Hosting</title>
</head>
<body>
    <h1>File Hosting with Permanent Links</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="file">Upload File:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Upload</button>
    </form>

    <form action="" method="post">
        <label for="file_name">Generate Link for:</label>
        <input type="text" name="file_name" id="file_name" placeholder="Uploaded file name" required>
        <button type="submit" name="generate_link">Generate Permanent Link</button>
    </form>
</body>
</html>