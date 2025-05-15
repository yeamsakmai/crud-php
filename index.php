<?php
include 'db.php';
include 'header.php';

// Delete movie if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prevent SQL injection by using prepared statements
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id); // "i" means the parameter is an integer
    $stmt->execute();
}

// Fetch movies
$result = $conn->query("SELECT * FROM movies");
$movies = $result->fetch_all(MYSQLI_ASSOC);
?>

<h3>Movie List</h3>
<a href="create.php" class="btn btn-success mb-3">+ Add New Movie</a>
<table class="table table-bordered">
    <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Genre</th>
            <th>Release Date</th>
            <th>Status</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($movies as $movie): ?>
            <tr>
                <td class="text-center"><?= $movie['id']; ?></td>
                <td><?= $movie['title']; ?></td>
                <td><?= $movie['genre']; ?></td>
                <td class="text-center"><?= $movie['release_date']; ?></td>
                <td class="text-center">
                    <?php if ($movie['status'] === 'Published'): ?>
                        <span class="badge badge-success">Published</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Draft</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <img src="<?= $movie['image']; ?>" class="movie-img" alt="Movie Image">
                </td>
                <td class="table-actions text-center">
                    <a href="edit.php?id=<?= $movie['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" data-id="<?= $movie['id']; ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this movie?
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="id" id="deleteMovieId">
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#deleteModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var movieId = button.data('id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#deleteMovieId').val(movieId);
        });
    });
</script>


<?php include 'footer.php'; ?>