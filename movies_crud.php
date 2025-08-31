<?php
require_once 'config.php';
require_once 'includes/init.php'; // Assuming init.php is needed for database connection and functions

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
            AND COLUMN_NAME IN ('poster_path', 'language', 'cast', 'trailer_link', 'age_rating', 'release_date');
        ";
        $columns_result = $conn->query($check_columns_sql);
        $columns_data = $columns_result->fetch_assoc();

        if ($columns_data['num'] < 6) { // If any of the required columns are missing (now 6 columns)
            $alter_table_sql = "
                ALTER TABLE movies
                ADD COLUMN poster_path VARCHAR(255) NULL,
                ADD COLUMN language VARCHAR(50) NULL,
                ADD COLUMN cast TEXT NULL,
                ADD COLUMN trailer_link VARCHAR(255) NULL,
                ADD COLUMN age_rating VARCHAR(10) NULL,
                ADD COLUMN release_date DATE NULL;
            ";
            if (!$conn->query($alter_table_sql)) {
                echo "Error altering table: " . $conn->error . "<br>";
            } else {
            echo "Movies table altered successfully.<br>";
        }
    } else {
        // Removed the "Movies table already has the required columns." message
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
            $release_date = $conn->real_escape_string($_POST['release_date']); // New: Get release_date

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
                            age_rating = '$age_rating',
                            release_date = '$release_date'"; // New: Include release_date in update
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
                $sql = "INSERT INTO movies (title, genre, description, language, cast, trailer_link, age_rating, release_date, poster_path)
                        VALUES ('$title', '$genre', '$description', '$language', '$cast', '$trailer_link', '$age_rating', '$release_date', '$poster_filename')"; // New: Include release_date in insert

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

<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container mx-auto px-4 py-8 mt-16">
    <h1 class="text-5xl font-extrabold text-center mb-12 text-red-500">Movie Management</h1>

    <?php if (!empty($message)): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Info!</strong>
            <span class="block sm:inline"><?php echo $message; ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-blue-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.15a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.03a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.15 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </span>
        </div>
    <?php endif; ?>

    <!-- Movie Form (Create/Update) -->
    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-16 border border-gray-700 p-8">
        <h2 class="text-3xl font-bold mb-6 text-white"><?php echo isset($movie_to_edit) ? 'Edit Movie' : 'Add New Movie'; ?></h2>
        <form action="movies_crud.php" method="POST" enctype="multipart/form-data">
            <?php if (isset($movie_to_edit)): ?>
                <input type="hidden" name="movie_id" value="<?php echo $movie_to_edit['movie_id']; ?>">
                <input type="hidden" name="existing_poster_path" value="<?php echo $movie_to_edit['poster_path']; ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="title" class="block text-gray-400 text-sm font-bold mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="title" name="title" value="<?php echo htmlspecialchars($movie_to_edit['title'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="genre" class="block text-gray-400 text-sm font-bold mb-2">Genre</label>
                    <input type="text" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="genre" name="genre" value="<?php echo htmlspecialchars($movie_to_edit['genre'] ?? ''); ?>">
                </div>
            </div>

            <div class="mb-6">
                <label for="description" class="block text-gray-400 text-sm font-bold mb-2">Description</label>
                <textarea class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="description" name="description" rows="3"><?php echo htmlspecialchars($movie_to_edit['description'] ?? ''); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="language" class="block text-gray-400 text-sm font-bold mb-2">Language</label>
                    <input type="text" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="language" name="language" value="<?php echo htmlspecialchars($movie_to_edit['language'] ?? ''); ?>">
                </div>
                <div>
                    <label for="cast" class="block text-gray-400 text-sm font-bold mb-2">Cast</label>
                    <input type="text" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="cast" name="cast" value="<?php echo htmlspecialchars($movie_to_edit['cast'] ?? ''); ?>">
                </div>
                <div>
                    <label for="trailer_link" class="block text-gray-400 text-sm font-bold mb-2">Trailer Link</label>
                    <input type="url" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="trailer_link" name="trailer_link" value="<?php echo htmlspecialchars($movie_to_edit['trailer_link'] ?? ''); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label for="age_rating" class="block text-gray-400 text-sm font-bold mb-2">Age Rating</label>
                    <input type="text" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="age_rating" name="age_rating" value="<?php echo htmlspecialchars($movie_to_edit['age_rating'] ?? ''); ?>">
                </div>
                <div>
                    <label for="release_date" class="block text-gray-400 text-sm font-bold mb-2">Release Date <span class="text-red-500">*</span></label>
                    <input type="date" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-900 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" id="release_date" name="release_date" value="<?php echo htmlspecialchars($movie_to_edit['release_date'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="poster_path" class="block text-gray-400 text-sm font-bold mb-2">Poster Upload</label>
                    <input type="file" class="block w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-red-500 file:text-white
                        hover:file:bg-red-600" id="poster_path" name="poster_path" accept="image/*">
                    <?php if (isset($movie_to_edit) && !empty($movie_to_edit['poster_path'])): ?>
                        <img src="<?php echo htmlspecialchars($movie_to_edit['poster_path'] ?? ''); ?>" alt="Current Poster" class="w-24 h-auto mt-4 rounded-lg shadow">
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                    <?php echo isset($movie_to_edit) ? 'Update Movie' : 'Add Movie'; ?>
                </button>
                <?php if (isset($movie_to_edit)): ?>
                    <a href="movies_crud.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Movie Table View -->
    <h2 class="text-4xl font-extrabold text-center mb-8 text-red-500">Movie List</h2>
    <div class="overflow-x-auto bg-gray-800 rounded-xl shadow-lg border border-gray-700 mb-16">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Poster</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Title</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Genre</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Language</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Cast</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Trailer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Age Rating</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Release Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($movies)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-gray-300">No movies found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($movie['poster_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($movie['poster_path'] ?? ''); ?>" alt="Poster" class="w-16 h-auto rounded-lg shadow">
                                <?php else: ?>
                                    <div class="w-16 h-24 bg-gray-700 flex items-center justify-center text-gray-400 text-xs rounded-lg">No Poster</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?php echo htmlspecialchars($movie['title'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?php echo htmlspecialchars($movie['genre'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?php echo htmlspecialchars($movie['language'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?php
                                $cast_display = $movie['cast'] ?? '';
                                echo htmlspecialchars(substr($cast_display, 0, 50)) . (strlen($cast_display) > 50 ? '...' : '');
                            ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($movie['trailer_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($movie['trailer_link'] ?? ''); ?>" target="_blank" class="text-blue-400 hover:text-blue-300 text-sm">Watch</a>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?php echo htmlspecialchars($movie['age_rating'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?php echo htmlspecialchars($movie['release_date'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="movies_crud.php?action=edit&id=<?php echo $movie['movie_id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <a href="movies_crud.php?action=delete&id=<?php echo $movie['movie_id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
