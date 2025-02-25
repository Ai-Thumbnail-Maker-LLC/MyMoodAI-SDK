<?php
// example_styles.php

// Include the SDK (adjust the path if necessary)
require_once 'mymoodai.php';

// API configuration
$apiBaseUrl = 'https://api.mymoodai.app/rest/api';
$apiKey     = 'your_api_key'; // Replace with your actual API key

// Instantiate the client
$client = new MyMoodAIClient($apiBaseUrl, $apiKey);

// Fetch the list of styles
try {
    $stylesResponse = $client->list_styles();
    // The API might return an associative array with a "styles" key or a plain array.
    if (isset($stylesResponse['styles'])) {
        $styles = $stylesResponse['styles'];
    } else {
        $styles = $stylesResponse;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MyMoodAI Styles</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 20px;
        }
        /* Remove fixed height and object-fit so the full image displays in its natural aspect ratio */
        .card-img-top {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">MyMoodAI Styles</a>
  </div>
</nav>

<div class="container mt-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            Error fetching styles: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <?php if (!empty($styles)): ?>
            <div class="row">
                <?php foreach ($styles as $style): ?>
                    <?php
                        // Determine which image to display based on gender
                        if (isset($style['gender'])) {
                            if ($style['gender'] === 'woman' && !empty($style['image_female_v'])) {
                                $img = $style['image_female_v'];
                            } elseif ($style['gender'] === 'man' && !empty($style['image_male_v'])) {
                                $img = $style['image_male_v'];
                            } else {
                                // Fallback to default image_v if available
                                $img = $style['image_v'] ?? $style['image'] ?? '';
                            }
                        } else {
                            $img = $style['image_v'] ?? $style['image'] ?? '';
                        }
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <?php if ($img): ?>
                                <img src="<?php echo htmlspecialchars($img); ?>" loading="lazy" class="card-img-top" alt="<?php echo htmlspecialchars($style['name'] ?? 'Style Image'); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php
                                    // Use name_male if gender is "man" and available, otherwise use name.
                                    if ($style['gender'] === 'man' && !empty($style['name_male'])) {
                                        echo htmlspecialchars($style['name_male']);
                                    } else {
                                        echo htmlspecialchars($style['name'] ?? 'Unnamed Style');
                                    }
                                    ?>
                                </h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($style['description'] ?? 'No description provided.'); ?>
                                </p>
                                <?php if (!empty($style['category'])): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($style['category']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No styles found.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>