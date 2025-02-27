# MyMoodAI Python SDK

**A blazing-fast Python client for interacting with [MyMoodAI](https://developer.mymoodai.app/) REST APIs.** This SDK streamlines creating and managing model training orders, uploading training images (selfies), running training or generation jobs, and retrieving statuses — so you can focus on building amazing AI-powered applications!

---

## Table of Contents

1. [Overview](#overview)  
2. [Features](#features)  
3. [Prerequisites](#prerequisites)  
4. [Installation](#installation)  
5. [Usage](#usage)  
   1. [Quickstart](#quickstart)  
   2. [Creating a Model](#creating-a-model)  
   3. [Uploading Training Images](#uploading-training-images)  
   4. [Running Orders](#running-orders)  
   5. [Checking Order Status](#checking-order-status)  
   6. [Generating Images with a Trained Model](#generating-images-with-a-trained-model)  
6. [Advanced Usage](#advanced-usage)  
   1. [Batch Uploading Training Images](#batch-uploading-training-images)  
   2. [Pagination and Listing](#pagination-and-listing)  
   3. [Logging & Error Handling](#logging--error-handling)  
   4. [Environment Variables for Configuration](#environment-variables-for-configuration)  
7. [Examples](#examples)  
8. [API Reference](#api-reference)  
9. [FAQ](#faq)  
10. [Contributing](#contributing)  
11. [License](#license)  

---

## Overview

This repository provides a Python SDK for seamlessly leveraging MyMoodAI's personalization and image generation services. The SDK abstracts away low-level HTTP calls, handling JSON serialization, base64 encoding of images, and endpoint organization — all with a clean, Pythonic interface.

**Common Use Cases**:
- Train AI avatars/models from user-provided selfies.
- Generate images based on your trained models, applying specific styles.
- Programmatically manage orders and track their training or generation status.

---

## Features

- **Order Creation**  
  Create either a brand-new model (for training) or an order based on an existing trained model (for generating new images).

- **Effortless File Uploading**  
  Upload selfies for training via a single function, which handles base64 encoding behind the scenes.

- **Intuitive Status Checks**  
  Quickly check the status of any order to see if training/generation is complete.

- **Rich Listing Endpoints**  
  List available styles, all your orders, models, and model-generated avatars.

- **Minimal Boilerplate**  
  Bring your own data, and let the client handle the rest!

---

## Prerequisites

- Python **3.6+** (for type hints and f-strings).
- A valid MyMoodAI API Key (get one here [MyMoodAI](https://developer.mymoodai.app/)).
- [Requests](https://docs.python-requests.org/en/master/) library.

---

## Installation

Until this package is available via PyPI, you can install it directly from the GitHub repository:

```bash
# 1. Clone this repository
git clone https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK.git

# 2. Navigate to the Python SDK folder
cd MyMoodAI-SDK/python

# 3. Install dependencies
pip install -r requirements.txt
```

Then, in your Python code, you can import and start using the client:

```python
from mymoodai_client import MyMoodAIClient
```

---

## Usage

### Quickstart

```python
from mymoodai_client import MyMoodAIClient

# Initialize the client
client = MyMoodAIClient(
    base_url="https://api.mymoodai.app/rest/api",
    api_key="YOUR_API_KEY"  # or None if the API doesn't require auth
)
```

### Creating a Model

Creating a model is effectively creating a “training order” with `parent=0`. For example:

```python
model_payload = {
    "styles": [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
    "gender": 1,
    "parent": 0  # 0 means it's a new training job
}
model = client.create_model(model_payload)
model_id = model["id"]

print("New model created:", model)
```

### Uploading Training Images

You can upload one or more selfies to train your model:

```python
client.upload_training_image(order_id=model_id, image_path="selfie.jpg")
```

By default, the SDK reads the file from your filesystem, encodes it as Base64, and sends it to the MyMoodAI API. You can repeat this call for multiple selfies.  

> **Tip**: More images typically yield better results.

### Running Orders

To start the training process once you’ve uploaded sufficient selfies:

```python
train_response = client.run_order(order_id=model_id)
print("Training kicked off:", train_response)
```

### Checking Order Status

You can poll the status of your order until it’s ready:

```python
status = client.get_order_status(order_id=model_id)
print("Order status:", status)
```

Statuses may include:
- `pending`
- `in_progress`
- `completed`
- `failed`
- or other custom status codes returned by the API.

### Generating Images with a Trained Model

Once your model is trained, you can create a new order using that model’s `id` as the `parent`. This instructs MyMoodAI to generate images with the trained model:

```python
order_payload = {
    "styles": [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
    "gender": 1,
    "parent": model_id
}
new_order = client.create_order(order_payload)
print("Created a new generation order:", new_order)
```

---

## Advanced Usage

### Batch Uploading Training Images

If you have multiple images to upload for training, you can simply loop over them:

```python
images = ["selfie1.jpg", "selfie2.jpg", "selfie3.jpg"]
for img_path in images:
    client.upload_training_image(order_id=model_id, image_path=img_path)
```

Wait until all files are uploaded before calling `run_order`, to ensure the model has all training data.

### Pagination and Listing

Some endpoints, such as listing avatars or styles, may support pagination. For example:

```python
page_id = 1
avatars_response = client.list_model_avatars(order_id=model_id, page_id=page_id)
print("Avatars on page", page_id, ":", avatars_response)
```

### Logging & Error Handling

- **Logging**: You can integrate Python’s built-in `logging` module to track requests and responses. For example:
  ```python
  import logging
  logging.basicConfig(level=logging.DEBUG)  # DEBUG, INFO, WARNING, etc.
  ```
  This will help you see underlying `requests` logs and debug issues quickly.

- **Error Handling**: The client calls `response.raise_for_status()`, so any non-2xx responses raise a `requests.HTTPError`. Wrap calls in try/except blocks if you need custom handling:
  ```python
  try:
      response = client.run_order(order_id=model_id)
  except requests.HTTPError as e:
      print("Failed to run order:", e)
  ```

### Environment Variables for Configuration

Instead of hardcoding your API key, consider using environment variables:

```python
import os
from mymoodai import MyMoodAIClient

api_key = os.environ.get("MYMOODAI_API_KEY", "")
client = MyMoodAIClient(
    base_url="https://api.mymoodai.app/rest/api", 
    api_key=api_key
)
```

Then set `MYMOODAI_API_KEY` in your shell or `.env` file.

---

## Examples

Below is a full end-to-end usage example:

```python
from mymoodai_client import MyMoodAIClient

# 1. Initialize the client
client = MyMoodAIClient(
    base_url="https://api.mymoodai.app/rest/api",
    api_key="YOUR_API_KEY"
)

# 2. Create a model for training (parent=0)
model_payload = {
    "styles": [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
    "gender": 1,
    "parent": 0
}
model = client.create_model(model_payload)
model_id = model["id"]

# 3. Upload training images
for path in ["selfie1.jpg", "selfie2.jpg"]:
    client.upload_training_image(order_id=model_id, image_path=path)

# 4. Run the training process
train_response = client.run_order(order_id=model_id)
print("Training response:", train_response)

# 5. Check the order status
status = client.get_order_status(order_id=model_id)
print("Training status:", status)

# 6. When training is complete, create a new order to generate images
generation_order_payload = {
    "styles": [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
    "gender": 1,
    "parent": model_id
}
new_order = client.create_order(generation_order_payload)
print("Created new image-generation order:", new_order)
```

---

## API Reference

Below is a concise summary of available methods in `MyMoodAIClient`. Each method returns a JSON-compatible Python dictionary on success.

1. **`create_model(payload: Dict[str, Any]) -> Dict[str, Any]`**  
   Create a new model (training order). `payload` must include `"parent": 0`.

2. **`create_order(payload: Dict[str, Any]) -> Dict[str, Any]`**  
   Create a new order to generate images using an existing model as `parent`.

3. **`upload_training_image(order_id: int, image_path: str, gender: int = 1) -> Dict[str, Any]`**  
   Upload a selfie (Base64-encoded under the hood).

4. **`get_order_status(order_id: int) -> Dict[str, Any]`**  
   Get the status of an order (training/generation).

5. **`run_order(order_id: int) -> Dict[str, Any]`**  
   Kick off training or image generation for an order.

6. **`list_styles() -> Dict[str, Any]`**  
   List all available public styles.

7. **`list_orders() -> Dict[str, Any]`**  
   List all orders you’ve created.

8. **`list_models() -> Dict[str, Any]`**  
   List all models you’ve created.

9. **`list_model_avatars(order_id: int, page_id: int) -> Dict[str, Any]`**  
   List generated avatars on a trained model.

10. **`list_training_images(order_id: int) -> Dict[str, Any]`**  
    List all selfies uploaded to an order.

11. **`select_training_image(order_id: int, selfie_id: int) -> Dict[str, Any]`**  
    Select a main selfie to focus on for training.

12. **`list_model_orders(order_id: int) -> Dict[str, Any]`**  
    List all orders associated with a specific model.

---

## FAQ

1. **How many selfies should I upload for optimal training?**  
   The more varied, high-quality selfies you upload, the better the resulting model. Typically, 5–10 images can give decent results.

2. **Can I use this without an API key?**  
   It depends on whether the MyMoodAI endpoint you’re targeting requires authentication. In most production use-cases, yes, you’ll need an API key.

3. **What image file formats does it support?**  
   JPEG and PNG are most common. The client just reads the file bytes and encodes them in Base64, so if the API supports it, you’re good to go.

4. **What if my order fails?**  
   Check the error messages returned by the API. Training can fail due to insufficient selfies, invalid image data, or style ID issues.

5. **How do I get more styles?**  
   Use the `list_styles()` endpoint to see available styles. If you have custom styles, your MyMoodAI backend might offer a different endpoint.

---

## Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request on [GitHub](https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK/) for:

- Bug fixes  
- Feature requests  
- Documentation improvements  

**Steps to contribute**:
1. Fork the repo  
2. Make your changes on a new branch  
3. Submit a Pull Request  

---

## License

This project is licensed under the [MIT License](LICENSE). You’re free to use, modify, and distribute this software in personal or commercial projects.

---

**Happy building with MyMoodAI!**  
If you have any questions or feedback, [open an issue](https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK/issues) or reach out to the MyMoodAI team. We’re here to help you succeed.
