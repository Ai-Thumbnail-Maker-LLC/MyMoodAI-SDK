# MyMoodAI REST API

Welcome to the **MyMoodAI REST API** documentation! This API enables you to build personalized AI experiences—training custom models with user selfies, generating new images (avatars) based on trained models, and more. Below you will find details on each endpoint, including usage, parameters, and example requests.

---

## Table of Contents

1. [Overview](#overview)  
2. [Base URL](#base-url)  
3. [Authentication](#authentication)  
4. [Endpoints](#endpoints)  
   1. [Order Creation & Management](#order-creation--management)  
   2. [Training Images](#training-images)  
   3. [Order Status & Execution](#order-status--execution)  
   4. [Model & Avatar Listings](#model--avatar-listings)  
   5. [Styles](#styles)  
5. [Example Workflow](#example-workflow)  
6. [Error Handling](#error-handling)  
7. [FAQ](#faq)  
8. [Contributing](#contributing)  
9. [License](#license)  

---

## Overview

The **MyMoodAI REST API** allows developers to:

- **Create** new orders for training or image generation.  
- **Upload** one or more selfies as training images.  
- **Run** training jobs or generation tasks on the backend.  
- **Check** the status of orders.  
- **List** trained models, available styles, and generated avatars.  

This repository also contains language-specific SDKs (like our [Python SDK](./python/README.md)) which wrap the REST API in a more convenient interface.

---

## Base URL

All endpoints below assume a base URL of:

```
https://api.mymoodai.app/rest/api
```

Be sure to adjust if your environment differs (e.g., staging vs. production).

---

## Authentication

The MyMoodAI REST API typically uses Bearer Token authentication. Include your API key in the `Authorization` header of each request:

```
Authorization: Bearer YOUR_API_KEY
```

For example:

```bash
curl -X GET \
     -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     "https://api.mymoodai.app/rest/api/order/list"
```

> **Note:** If your endpoint or environment does not require authentication, you can omit this header.

---

## Endpoints

### Order Creation & Management

1. **Create a New Model (Training Order)**  
   - **Endpoint**: `POST /order/create/model`  
   - **Description**: Creates a new **training** order when `parent=0`.  
   - **Request Body** (JSON):
     ```json
     {
       "styles": [112, 5, 2572],
       "gender": 1,
       "parent": 0
     }
     ```
   - **Response** (JSON):
     ```json
     {
       "id": 123,
       "status": "created",
       "message": "Model order created successfully"
     }
     ```

2. **Create a New Order (Using an Existing Model)**  
   - **Endpoint**: `POST /order/create`  
   - **Description**: Creates a new **generation** order if `parent` is the ID of an existing trained model.  
   - **Request Body** (JSON):
     ```json
     {
       "styles": [112, 5, 2572],
       "gender": 1,
       "parent": 321  // ID of the trained model
     }
     ```
   - **Response** (JSON):
     ```json
     {
       "id": 999,
       "status": "created",
       "message": "Order created successfully"
     }
     ```

3. **List All Orders**  
   - **Endpoint**: `GET /order/list`  
   - **Description**: Returns a list of all orders (both training and generation).  
   - **Response** (JSON):
     ```json
     [
       {
         "id": 123,
         "parent": 0,
         "status": "completed",
         "created_at": "2025-01-01T00:00:00Z"
       },
       ...
     ]
     ```

### Training Images

1. **Upload Training Image**  
   - **Endpoint**: `POST /order/{order_id}/training-images/upload`  
   - **Description**: Uploads a base64-encoded selfie for training.  
   - **Request Body** (JSON):
     ```json
     {
       "gender": "1",
       "image": "data:image/jpeg;base64,..." // or just base64 without prefix
     }
     ```
   - **Response** (JSON):
     ```json
     {
       "status": "success",
       "message": "Training image uploaded"
     }
     ```
   - **Note**: If you’re including a data URI prefix like `data:image/jpeg;base64,`, ensure your backend is prepared to handle it.

2. **List Training Images**  
   - **Endpoint**: `GET /order/{order_id}/training-images/list`  
   - **Description**: Retrieves a list of training images associated with the specified order.  
   - **Response** (JSON):
     ```json
     [
       {
         "selfie_id": 101,
         "gender": 1,
         "selected": false
       },
       {
         "selfie_id": 102,
         "gender": 1,
         "selected": true
       }
     ]
     ```

3. **Select Training Image**  
   - **Endpoint**: `GET /order/{order_id}/training-images/{selfie_id}/select`  
   - **Description**: Marks a specific selfie as the main training image.  
   - **Response** (JSON):
     ```json
     {
       "status": "success",
       "message": "Selfie selected as main image"
     }
     ```

### Order Status & Execution

1. **Run/Execute an Order**  
   - **Endpoint**: `GET /order/{order_id}/run`  
   - **Description**: Initiates training (for a model) or generation (for a new order).  
   - **Response** (JSON):
     ```json
     {
       "status": "running",
       "message": "Order has started processing."
     }
     ```

2. **Get Order Status**  
   - **Endpoint**: `GET /order/{order_id}/status`  
   - **Description**: Retrieves the current status of the order.  
   - **Response** (JSON):
     ```json
     {
       "status": "in_progress",
       "progress": 40,
       "message": "Training is 40% complete."
     }
     ```

### Model & Avatar Listings

1. **List Models**  
   - **Endpoint**: `GET /model/list`  
   - **Description**: Returns all models (orders with `parent=0`) that have been created.  
   - **Response** (JSON):
     ```json
     [
       {
         "id": 123,
         "status": "completed",
         "created_at": "2025-01-01T00:00:00Z"
       },
       ...
     ]
     ```

2. **List Avatars for a Model**  
   - **Endpoint**: `GET /model/{order_id}/avatars/{page_id}`  
   - **Description**: Retrieves generated avatars for a specific model, optionally paginated.  
   - **Response** (JSON):
     ```json
     [
       {
         "avatar_id": 1001,
         "image_url": "https://cdn.mymoodai.app/avatars/1001.jpg"
       },
       ...
     ]
     ```

3. **List Orders for a Model**  
   - **Endpoint**: `GET /model/{order_id}/order/list`  
   - **Description**: Shows all generation orders that were created using a specific model as `parent`.  
   - **Response** (JSON):
     ```json
     [
       {
         "id": 999,
         "parent": 123,
         "status": "completed",
         "created_at": "2025-01-10T00:00:00Z"
       },
       ...
     ]
     ```

### Styles

1. **List Available Styles**  
   - **Endpoint**: `GET /styles/list`  
   - **Description**: Shows a list of styles (IDs, names, etc.) that can be used for training or generation.  
   - **Response** (JSON):
     ```json
     [
       {
         "style_id": 112,
         "name": "Realistic Portrait"
       },
       {
         "style_id": 5,
         "name": "Sketch Drawing"
       },
       ...
     ]
     ```

---

## Example Workflow

Below is a typical sequence of REST calls to train a model and generate images:

1. **Create a Model** (`parent=0`)  
2. **Upload Selfies** to that order  
3. **(Optional)** Select one or more selfies as the “main” training image  
4. **Run the Order** to start model training  
5. **Poll Status** until training is `completed`  
6. **Create a New Order** with the trained model’s ID as `parent`  
7. **Run the New Order** for generation  
8. **List or Retrieve Generated Avatars**

---

## Error Handling

- Non-2xx status codes typically return an error response in JSON with a `message` or `error` field.  
- For instance, if you try to create an order with invalid style IDs:
  ```json
  {
    "status": "error",
    "message": "Invalid style IDs provided."
  }
  ```
- If your token is missing or invalid, you may see:
  ```json
  {
    "status": "error",
    "message": "Unauthorized access."
  }
  ```

Always handle these gracefully on the client side.

---

## FAQ

1. **How many selfies should I upload for a good model?**  
   Ideally, 5–10 high-quality, varied selfies to achieve optimal results.

2. **Do I need an API key?**  
   In most production environments, yes. Contact MyMoodAI support if you do not have one.

3. **How long does training take?**  
   Training time can vary depending on server load, the number of images, and the complexity of styles.

4. **Where can I see the list of style IDs?**  
   Use `GET /styles/list` to retrieve an up-to-date list of styles.

5. **What image formats are supported?**  
   Typically JPEG and PNG. The API just expects a base64-encoded string.

---

## Contributing

We welcome contributions to improve the MyMoodAI API and its documentation. If you find issues or have ideas for new features:

1. [Open an issue](https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK/issues).  
2. Fork the repo and make changes on a new branch.  
3. Submit a pull request for review.

---

## License

This project is under the [MIT License](LICENSE). You are free to use and modify it in personal or commercial applications.

---

**Happy Building!**  
If you have questions or encounter issues, feel free to open an issue on [GitHub](https://github.com/Ai-Thumbnail-Maker-LLC/MyMoodAI-SDK).  