<?php
include 'db.php';

// Handle form submission to add movie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $release_date = $_POST['release_date'];
    $status = $_POST['status'];
    // Check if image URL is provided, else set to NULL
    $image = isset($_POST['image']) && !empty($_POST['image']) ? $_POST['image'] : NULL;

    // Prepare the insert query
    $stmt = $conn->prepare("INSERT INTO movies (title, genre, release_date, status, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $genre, $release_date, $status, $image);
    $stmt->execute();

    // Redirect to index page after success
    header("Location: index.php");
    exit;
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Add Movie</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="p-5">
    <h3>Add New Movie</h3>
    <form method="POST">
        <div class="form-group">
            <label>Movie Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Genre</label>
            <input type="text" name="genre" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Release Date</label>
            <input type="date" name="release_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="Published">Published</option>
                <option value="Draft">Draft</option>
            </select>
        </div>
        <div class="form-group">
            <label>Image URL</label>
            <input type="text" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Save Movie</button>
        <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</body>

</html>