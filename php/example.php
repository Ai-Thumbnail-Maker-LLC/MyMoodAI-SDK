<?php
// mymoodai.php

// Include the SDK (adjust the path if necessary)
require_once 'mymoodai.php';

// API configuration
$apiBaseUrl = 'https://api.mymoodai.app/rest/api';
$apiKey     = 'your_api_key'; // Replace with your actual API key

// Instantiate the client
$client = new MyMoodAIClient($apiBaseUrl, $apiKey);

$message = '';

// Process the file upload and training request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['training_image'])) {
    if ($_FILES['training_image']['error'] === UPLOAD_ERR_OK) {
        $tmpFilePath = $_FILES['training_image']['tmp_name'];
        // Save the file to a permanent location in the uploads/ directory
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename    = basename($_FILES['training_image']['name']);
        $destination = $uploadDir . $filename;
        if (move_uploaded_file($tmpFilePath, $destination)) {
            try {
                // Step 1: Create a new model (training order with parent = 0)
                $modelPayload = [
                    "styles" => [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
                    "gender" => 1,
                    "parent" => 0
                ];
                $model   = $client->create_model($modelPayload);
                $orderId = $model['id'];

                // Step 2: Upload the training image (selfie) to the new model
                $uploadResponse = $client->upload_training_image($orderId, $destination);

                // Step 3: Run the order (i.e. start the training process)
                $runResponse = $client->run_order($orderId);

                $message = "Model training started successfully for model ID: " . htmlspecialchars($orderId);
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Failed to move uploaded file.";
        }
    } else {
        $message = "File upload error.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MyMoodAI Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin-top: 60px; }
        .avatar { max-width: 150px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">MyMoodAI Dashboard</a>
  </div>
</nav>

<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert alert-info" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-header">
        <h2>Upload Training Image</h2>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="training_image" class="form-label">Select an image:</label>
                <input type="file" class="form-control" name="training_image" id="training_image" required>
            </div>
            <button type="submit" class="btn btn-success">Train Model</button>
        </form>
      </div>
    </div>

    <h2 class="mb-3">Existing Models and Avatars</h2>
    <?php
    try {
        // Retrieve the list of models from the API
        $modelsResponse = $client->list_models();
        // The API may return a list or an associative array with a 'models' key.
        if (is_array($modelsResponse) && isset($modelsResponse['models'])) {
            $models = $modelsResponse['models'];
        } elseif (is_array($modelsResponse)) {
            $models = $modelsResponse;
        } else {
            $models = [];
        }

        if (empty($models)) {
            echo "<div class='alert alert-warning'>No models found.</div>";
        } else {
            // Loop through each model and display its avatars with pagination
            foreach ($models as $model) {
                $modelId = $model['id'];
                echo "<div class='card mb-3'>";
                echo "<div class='card-header'>Model ID: " . htmlspecialchars($modelId) . "</div>";
                echo "<div class='card-body'><div class='row'>";
                $page = 1;
                $avatarsFound = false;
                while (true) {
                    $avatarsResponse = $client->list_model_avatars($modelId, $page);
                    if (is_array($avatarsResponse) && isset($avatarsResponse['avatars'])) {
                        $avatars = $avatarsResponse['avatars'];
                    } elseif (is_array($avatarsResponse)) {
                        $avatars = $avatarsResponse;
                    } else {
                        $avatars = [];
                    }

                    // If no avatars are returned on this page, break out of the loop
                    if (empty($avatars)) {
                        break;
                    }

                    foreach ($avatars as $avatar) {
                        $avatarsFound = true;
                        // Use 'filename_small' as thumbnail and link to 'filename' (or 'filename_large')
                        $thumbUrl = isset($avatar['filename_small']) ? $avatar['filename_small'] : '';
                        $fullUrl  = isset($avatar['filename']) ? $avatar['filename'] : $thumbUrl;

                        if ($thumbUrl) {
                            echo "<div class='col-md-3 col-sm-4 col-6 mb-3'>";
                            echo "<a href='" . htmlspecialchars($fullUrl) . "' target='_blank'>";
                            echo "<img class='avatar img-fluid' src='" . htmlspecialchars($thumbUrl) . "' alt='Avatar'>";
                            echo "</a>";
                            echo "</div>";
                        } else {
                            echo "<div class='col-12'><p>" . htmlspecialchars(json_encode($avatar)) . "</p></div>";
                        }
                    }
                    $page++;
                }
                if (!$avatarsFound) {
                    echo "<div class='col-12'><p>No avatars available for this model.</p></div>";
                }
                echo "</div></div></div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error retrieving models: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
</div>

<!-- Bootstrap JS (including Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>