<?php
include 'db.php';
require 'addEpisodeHandler.php';
require 'episodeManager.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid movie ID");
}

// Fetch movie details
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
if (!$movie) {
    die("Movie not found");
}

// Initialize AddEpisodeHandler
$episodeHandler = new AddEpisodeHandler($conn);

// Handle movie update and episode updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $release_date = $_POST['release_date'];
    $status = $_POST['status'];
    $image = $_POST['image'];

    // Update movie
    $stmt = $conn->prepare("UPDATE movies SET title=?, genre=?, release_date=?, status=?, image=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $genre, $release_date, $status, $image, $id);
    $stmt->execute();

    // Handle episodes (update or insert)
    if (!empty($_POST['episode_titles'])) {
        $episode_ids = $_POST['episode_ids'] ?? [];
        $episode_titles = $_POST['episode_titles'];
        $episode_urls = $_POST['episode_urls'];

        for ($i = 0; $i < count($episode_titles); $i++) {
            $ep_id = !empty($episode_ids[$i]) ? (int)$episode_ids[$i] : 0;
            $ep_title = $conn->real_escape_string($episode_titles[$i]);
            $ep_url = $conn->real_escape_string($episode_urls[$i]);

            if ($ep_id > 0) {
                // Update existing episode
                $stmt = $conn->prepare("UPDATE episodes SET episode_title=?, video_url=? WHERE id=?");
                $stmt->bind_param("ssi", $ep_title, $ep_url, $ep_id);
                $stmt->execute();
            } elseif (!empty($ep_title) && !empty($ep_url)) {
                // Insert new episode using AddEpisodeHandler
                $episodeHandler->addEpisodes($id, [$ep_title], [$ep_url], [0]);
            }
        }
    }

    header("Location: index.php");
    exit;
}

// Handle single episode addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_episode'])) {
    $ep_title = $_POST['episode_title'];
    $ep_url = $_POST['episode_url'];
    if (!empty($ep_title) && !empty($ep_url)) {
        $episodeHandler->addEpisodes($id, [$ep_title], [$ep_url], [0]);
    } else {
        die("Episode title or URL cannot be empty");
    }
    header("Location: edit.php?id=$id");
    exit;
}

// Delete episode
if (isset($_GET['delete_episode_id'])) {
    $ep_id = (int)$_GET['delete_episode_id'];
    $stmt = $conn->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->bind_param("i", $ep_id);
    $stmt->execute();
    header("Location: edit.php?id=$id");
    exit;
}

// Fetch episodes
$stmt = $conn->prepare("SELECT * FROM episodes WHERE movie_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$episodeResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Movie</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .half {
            width: 48%;
            float: left;
            margin-right: 2%;
        }
    </style>
    <script>
        let editing = false;

        function addEpisodeForm() {
            if (editing) {
                alert("Please save the current episode before adding a new one.");
                return;
            }

            editing = true;

            const container = document.getElementById('episodes-container');
            const div = document.createElement('div');
            div.classList.add('form-group', 'episode-item');
            div.innerHTML = `
                <input type="hidden" name="episode_ids[]" value="">
                <input type="text" name="episode_titles[]" class="form-control mb-2" placeholder="Episode Title" required>
                <input type="text" name="episode_urls[]" class="form-control mb-2" placeholder="Video URL" required>
            `;
            container.appendChild(div);

            const btn = document.getElementById('add-episode-btn');
            btn.innerText = 'Save Episode';
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-success');
        }

        function saveEpisode() {
            if (editing) {
                // Submit the form to save the episode
                document.getElementById('movie-form').submit();
            }
            editing = false;
            const btn = document.getElementById('add-episode-btn');
            btn.innerText = '+ Add Episode';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
        }

        function handleEpisodeButton() {
            const btn = document.getElementById('add-episode-btn');
            if (btn.innerText === '+ Add Episode') {
                addEpisodeForm();
            } else {
                saveEpisode();
            }
        }
    </script>
</head>

<body class="p-4">
    <h2>Edit Movie</h2>
    <form id="movie-form" method="POST">
        <div class="row">
            <!-- Movie Info Column -->
            <div class="col-md-6">
                <h4>Movie Info</h4>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($movie['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Genre</label>
                    <input type="text" name="genre" class="form-control" value="<?= htmlspecialchars($movie['genre']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Release Date</label>
                    <input type="date" name="release_date" class="form-control" value="<?= $movie['release_date']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Published" <?= $movie['status'] === 'Published' ? 'selected' : ''; ?>>Published</option>
                        <option value="Draft" <?= $movie['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($movie['image']); ?>">
                </div>
                <?php if (!empty($movie['image'])): ?>
                    <div class="form-group">
                        <label>Preview:</label><br>
                        <img src="<?= htmlspecialchars($movie['image']); ?>" width="100">
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary mt-2">Update Movie</button>
                <a href="index.php" class="btn btn-secondary mt-2">Cancel</a>
            </div>

            <!-- Episodes Column -->
            <div class="col-md-6">
                <h4>Episodes</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Episode Title</th>
                            <th>Video URL</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($episode = $episodeResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($episode['episode_title']); ?></td>
                                <td><?= htmlspecialchars($episode['video_url']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $id; ?>&delete_episode_id=<?= $episode['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this episode?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </form>

    <!-- Separate form for adding a single episode -->
    <form method="POST">
        <input type="hidden" name="add_episode" value="1">
        <h4 class="mt-4">Add New Episode</h4>
        <div class="form-group">
            <label>Episode Title</label>
            <input type="text" name="episode_title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Video URL</label>
            <input type="text" name="episode_url" class="form-control" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success w-100">Add Episode</button>
        </div>
    </form>
</body>

</html>
