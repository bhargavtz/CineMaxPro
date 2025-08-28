<?php
// Include header and functions (assuming they exist and are needed)
// require_once 'includes/header.php';
// require_once 'includes/functions.php';

// Database configuration - assuming config.php exists and has these variables
// If config.php does not exist or is not configured, this part will need adjustment.
require_once 'config.php';

// --- Database Connection and Table Alteration ---
// Attempt to connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

    // Check if movies table exists and alter it if necessary
    $check_table_sql = "SHOW TABLES LIKE 'movies'";
    $table_exists = $conn->query($check_table_sql)->num_rows > 0;

    if ($table_exists) {
        // Check if columns exist before altering to avoid errors
        $check_columns_sql = "
            SELECT COUNT(*) AS num
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME = 'movies'
            AND COLUMN_NAME IN ('poster_path', 'language', 'cast', 'trailer_link', 'age_rating');
        ";
        $columns_result = $conn->query($check_columns_sql);
        $columns_data = $columns_result->fetch_assoc();

        if ($columns_data['num'] < 5) { // If any of the required columns are missing
            $alter_table_sql = "
                ALTER TABLE movies
                ADD COLUMN poster_path VARCHAR(255) NULL,
                ADD COLUMN language VARCHAR(50) NULL,
                ADD COLUMN cast TEXT NULL,
                ADD COLUMN trailer_link VARCHAR(255) NULL,
                ADD COLUMN age_rating VARCHAR(10) NULL;
            ";
            if (!$conn->query($alter_table_sql)) {
                echo "Error altering table: " . $conn->error . "<br>";
            } else {
                echo "Movies table altered successfully.<br>";
            }
        } else {
            echo "Movies table already has the required columns.<br>";
        }
    } else {
        echo "Movies table does not exist. Please create it first.<br>";
        // Optionally, you could create the table here if it doesn't exist.
        // For now, we assume it exists based on cinema_schema.sql.
    }

    // --- CRUD Operations ---

    // Initialize variables
    $movies = [];
    $message = '';
    $movie_to_edit = null;

    // Handle form submission for Create and Update
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Handle poster upload
        $target_dir = "uploads/posters/";
        $uploadOk = 1;
        $poster_filename = "";

        if (isset($_FILES["poster_path"]) && $_FILES["poster_path"]["error"] == UPLOAD_ERR_OK) {
            // Ensure the upload directory exists
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $original_filename = basename($_FILES["poster_path"]["name"]);
            $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $poster_filename = uniqid() . '.' . $imageFileType; // Unique filename
            $target_file = $target_dir . $poster_filename;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["poster_path"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                $message = "File is not an image.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                $message .= " Your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["poster_path"]["tmp_name"], $target_file)) {
                    $message = "The file ". htmlspecialchars( $poster_filename ). " has been uploaded.";
                } else {
                    $message = "Sorry, there was an error uploading your file.";
                    $uploadOk = 0; // Ensure upload failed
                }
            }
        } elseif (isset($_POST['existing_poster_path']) && !empty($_POST['existing_poster_path'])) {
            // If no new file was uploaded, keep the existing poster path
            $poster_filename = $_POST['existing_poster_path'];
            $uploadOk = 1; // Assume existing path is valid
        } else {
            // No file uploaded and no existing path provided
            $message = "Poster is required.";
            $uploadOk = 0;
        }


        // Process form data if upload was successful or if it's an update without new upload
        if ($uploadOk) {
            $title = $conn->real_escape_string($_POST['title']);
            $description = $conn->real_escape_string($_POST['description']);
            $genre = $conn->real_escape_string($_POST['genre']);
            $language = $conn->real_escape_string($_POST['language']);
            $cast = $conn->real_escape_string($_POST['cast']);
            $trailer_link = $conn->real_escape_string($_POST['trailer_link']);
            $age_rating = $conn->real_escape_string($_POST['age_rating']);

            if (isset($_POST['movie_id']) && !empty($_POST['movie_id'])) {
                // Update existing movie
                $movie_id = intval($_POST['movie_id']);
                $sql = "UPDATE movies SET
                            title = '$title',
                            genre = '$genre',
                            description = '$description',
                            language = '$language',
                            cast = '$cast',
                            trailer_link = '$trailer_link',
                            age_rating = '$age_rating'";
                if (!empty($poster_filename)) {
                    $sql .= ", poster_path = '$poster_filename'";
                }
                $sql .= " WHERE movie_id = $movie_id";

                if ($conn->query($sql) === TRUE) {
                    $message = "Movie updated successfully";
                } else {
                    $message = "Error updating movie: " . $conn->error;
                }
            } else {
                // Create new movie
                $sql = "INSERT INTO movies (title, genre, description, language, cast, trailer_link, age_rating, poster_path)
                        VALUES ('$title', '$genre', '$description', '$language', '$cast', '$trailer_link', '$age_rating', '$poster_filename')";

                if ($conn->query($sql) === TRUE) {
                    $message = "New movie created successfully";
                } else {
                    $message = "Error: " . $conn->error;
                }
            }
        }
    }

    // Handle Delete
    if (isset($_GET['action']) && $_GET['action'] == 'delete') {
        $movie_id = intval($_GET['id']);
        // Optional: Delete the poster file from the server
        $get_poster_sql = "SELECT poster_path FROM movies WHERE movie_id = $movie_id";
        $result = $conn->query($get_poster_sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $poster_path = $row['poster_path'];
            if (!empty($poster_path) && file_exists($poster_path)) {
                unlink($poster_path);
            }
        }

        $sql = "DELETE FROM movies WHERE movie_id = $movie_id";
        if ($conn->query($sql) === TRUE) {
            $message = "Movie deleted successfully";
        } else {
            $message = "Error deleting movie: " . $conn->error;
        }
    }

    // Handle Edit (Fetch movie data for editing)
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        $movie_id = intval($_GET['id']);
        $sql = "SELECT * FROM movies WHERE movie_id = $movie_id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $movie_to_edit = $result->fetch_assoc();
        } else {
            $message = "Movie not found";
        }
    }

    // Fetch all movies for the table view
    $sql = "SELECT * FROM movies";
    $movies_result = $conn->query($sql);
    if ($movies_result) {
        while($row = $movies_result->fetch_assoc()) {
            $movies[] = $row;
        }
    } else {
        $message = "Error fetching movies: " . $conn->error;
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie CRUD</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .poster-img {
            max-width: 100px;
            height: auto;
        }
        .upload-button {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Movie Management</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Movie Form (Create/Update) -->
        <div class="card mb-4">
            <div class="card-header">
                <?php echo isset($movie_to_edit) ? 'Edit Movie' : 'Add New Movie'; ?>
            </div>
            <div class="card-body">
                <form action="movies_crud.php" method="POST" enctype="multipart/form-data">
                    <?php if (isset($movie_to_edit)): ?>
                        <input type="hidden" name="movie_id" value="<?php echo $movie_to_edit['movie_id']; ?>">
                        <input type="hidden" name="existing_poster_path" value="<?php echo $movie_to_edit['poster_path']; ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['title']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="genre" class="form-label">Genre</label>
                            <input type="text" class="form-control" id="genre" name="genre" value="<?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['genre']) : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['description']) : ''; ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="language" class="form-label">Language</label>
                            <input type="text" class="form-control" id="language" name="language" value="<?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['language']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="cast" class="form-label">Cast</label>
                            <input type="text" class="form-control" id="cast" name="cast" value="<?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['cast']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="trailer_link" class="form-label">Trailer Link</label>
                            <input type="url" class="form-control" id="trailer_link" name="trailer_link" value="<?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['trailer_link']) : ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="age_rating" class="form-label">Age Rating</label>
                            <input type="text" class="form-control" id="age_rating" name="age_rating" value="<?php echo isset($movie_to_edit) ? htmlspecialchars($movie_to_edit['age_rating']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="poster_path" class="form-label">Poster Upload</label>
                            <input type="file" class="form-control" id="poster_path" name="poster_path" accept="image/*">
                            <?php if (isset($movie_to_edit) && !empty($movie_to_edit['poster_path'])): ?>
                                <img src="<?php echo htmlspecialchars($movie_to_edit['poster_path']); ?>" alt="Current Poster" class="poster-img mt-2">
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($movie_to_edit) ? 'Update Movie' : 'Add Movie'; ?>
                    </button>
                    <?php if (isset($movie_to_edit)): ?>
                        <a href="movies_crud.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Movie Table View -->
        <h2 class="mb-3">Movie List</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Genre</th>
                        <th>Language</th>
                        <th>Cast</th>
                        <th>Trailer</th>
                        <th>Age Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movies)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No movies found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($movie['poster_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($movie['poster_path']); ?>" alt="Poster" class="poster-img">
                                    <?php else: ?>
                                        No Poster
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                                <td><?php echo htmlspecialchars($movie['language']); ?></td>
                                <td><?php echo htmlspecialchars(substr($movie['cast'], 0, 50)) . (strlen($movie['cast']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <?php if (!empty($movie['trailer_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($movie['trailer_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Watch</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($movie['age_rating']); ?></td>
                                <td>
                                    <a href="movies_crud.php?action=edit&id=<?php echo $movie['movie_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="movies_crud.php?action=delete&id=<?php echo $movie['movie_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
