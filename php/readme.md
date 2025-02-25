
# MyMoodAI PHP SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)

A PHP SDK for interacting with the [MyMoodAI API](https://mymoodai.app). This SDK provides a comprehensive set of methods for creating and managing models, uploading training images, processing orders, and retrieving generated avatars.

The SDK is implemented in the `mymoodai.php` file, and a sample client dashboard demonstrating its usage is provided in `example.php`.

---

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [SDK Integration](#sdk-integration)
  - [Detailed SDK Methods](#detailed-sdk-methods)
- [Sample Client Dashboard](#sample-client-dashboard)
- [Configuration](#configuration)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- **Order and Model Creation:**
  - Create orders for image generation or model training.
  - Create new models (training orders with `parent = 0`).

- **Training Image Management:**
  - Upload training images (selfies) with automatic base64 encoding.
  - List all training images attached to an order.
  - Select a training image as the primary image.

- **Order Processing:**
  - Run training or image generation orders.
  - Retrieve order status to monitor processing.

- **Model and Avatar Listings:**
  - Retrieve a list of all models.
  - List orders associated with models.
  - Retrieve generated avatar images (with pagination support).
  - Retrieve a list of available public styles.

- **Utility:**
  - Perform HTTP GET and POST requests with built-in error handling.
  - Supports both JSON and multipart/form-data file uploads.

---

## Installation

Clone the repository using Git:

```bash
git clone https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK.git
```

Navigate to the PHP directory:

```bash
cd MyMoodAI-SDK/php
```

Ensure your environment is running PHP 7.4 or higher.

---

## Usage

### SDK Integration

Include the `mymoodai.php` file in your project:

```php
<?php
require_once 'mymoodai.php';

// Set your API base URL and API key.
$apiBaseUrl = 'https://api.mymoodai.app/rest/api';
$apiKey     = 'your_api_key'; // Replace with your actual API key

// Instantiate the client.
$client = new MyMoodAIClient($apiBaseUrl, $apiKey);

// Example: Create a new model (training order)
$modelPayload = [
    "styles" => [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
    "gender" => 1,
    "parent" => 0
];
$model = $client->create_model($modelPayload);

// Upload a training image.
$orderId = $model['id'];
$imagePath = 'path/to/your/selfie.jpg';
$uploadResponse = $client->upload_training_image($orderId, $imagePath);

// Run the order.
$runResponse = $client->run_order($orderId);

// Retrieve order status.
$status = $client->get_order_status($orderId);
print_r($status);
?>
```

### Detailed SDK Methods

The SDK is defined in `mymoodai.php` and provides the following methods:

#### Initialization & Helpers
- **`__construct(string $baseUrl, ?string $apiKey = null)`**  
  Initializes the client with the API base URL and an optional API key.

- **`_url(string $path): string`**  
  Constructs the full URL for API requests.

- **`_get(string $path, ?array $params = null): array`**  
  Performs a GET request to the API with optional query parameters.

- **`_post(string $path, ?array $data = null, ?array $files = null): array`**  
  Performs a POST request to the API. Supports both JSON payloads and multipart/form-data for file uploads.

#### Order and Model Creation
- **`create_order(array $payload): array`**  
  Creates an order for either image generation (when `parent > 0`) or model training.

- **`create_model(array $payload): array`**  
  Creates a new model (i.e., a training order with `parent = 0`).

#### Training Image Endpoints
- **`upload_training_image(int $order_id, string $image_path, int $gender = 1): array`**  
  Uploads a training image (selfie) to the specified order. The image is base64-encoded (optionally wrapped in a data URI) before being sent.

- **`list_training_images(int $order_id): array`**  
  Retrieves a list of all training images associated with the order.

- **`select_training_image(int $order_id, int $selfie_id): array`**  
  Selects a specific training image to serve as the primary image for the order.

#### Order Processing
- **`get_order_status(int $order_id): array`**  
  Retrieves the current status of the specified order.

- **`run_order(int $order_id): array`**  
  Initiates training or image generation for the specified order.

#### Model and Order Listings
- **`list_model_avatars(int $order_id, int $page_id): array`**  
  Retrieves a paginated list of avatar images associated with the model.

- **`list_styles(): array`**  
  Retrieves a list of available public styles for image generation.

- **`list_orders(): array`**  
  Retrieves a list of all orders.

- **`list_models(): array`**  
  Retrieves a list of all models.

- **`list_model_orders(int $order_id): array`**  
  Retrieves a list of orders associated with the specified model.

---

## Sample Client Dashboard

A full-featured dashboard is provided in [`example.php`](example.php). This sample demonstrates:

- **Uploading a Training Image:**  
  A web form (built with Bootstrap) allows you to upload an image, which is then used to create a new model and initiate training.

- **Listing Existing Models:**  
  The dashboard retrieves and displays all existing models along with their associated avatar images. Avatar images use `filename_small` for thumbnails and link to full-size images.

- **Modern UI:**  
  The dashboard uses Bootstrap 5 for a polished, responsive interface.

### To Run the Sample Dashboard:
1. Update your API credentials and base URL in `example.php`.
2. Place both `mymoodai.php` and `example.php` on your PHP server.
3. Open `example.php` in your browser to access the dashboard.

---

## Configuration

Before using the SDK or the sample dashboard, update the following configuration details:
- **API Base URL:** The endpoint for your MyMoodAI API.
- **API Key:** Your valid API key.
- **File Paths:** Ensure the paths for file uploads and training images are correctly configured on your server.

---

## Contributing

Contributions are welcome! If you'd like to suggest improvements, report issues, or add new features:
1. Fork the repository.
2. Create a new branch for your feature or fix.
3. Submit a pull request with a detailed description of your changes.

For major changes, please open an issue first to discuss your ideas.

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

For more information on using the MyMoodAI API, please refer to the [MyMoodAI API Documentation](https://mymoodai.app).

Happy coding!


This README provides a complete overview of the SDK's capabilities, detailed method documentation, and instructions for using the sample client dashboard located in `example.php` within the [GitHub repository](https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK/tree/main/php).