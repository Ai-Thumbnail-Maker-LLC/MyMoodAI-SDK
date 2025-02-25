import os
import requests
from typing import List, Optional, Dict, Any
import base64

class MyMoodAIClient:
    """
    A Python SDK client for interacting with the MyMoodAI API.
    
    Usage example:
        client = MyMoodAIClient(base_url="https://api.mymoodai.app/rest/api", api_key="YOUR_API_KEY")
        
        # Create a new model (training order) with parent=0
        model_payload = {
            "styles": [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
            "gender": 1,
            "parent": 0
        }
        model = client.create_model(model_payload)
        
        # Upload a selfie to the model order for training
        client.upload_training_image(order_id=model["id"], image_path="selfie.jpg")
        
        # Run the order (train the model)
        client.run_order(order_id=model["id"])
        
        # Check status (JSON response)
        status = client.get_order_status(order_id=model["id"])
        print("Order status:", status)
        
        # When done, create a new order using the model order id as parent
        #order_payload = {
        #    "styles": [112, 5, 2572, 1421, 2214, 947, 2570, 94, 356, 43],
        #    "gender": 1,
        #    "parent": model["id"]
        #}
        #new_order = client.create_order(order_payload)
    """
    
    def __init__(self, base_url: str, api_key: Optional[str] = None):
        """
        Initializes the MyMoodAI client.

        Args:
            base_url (str): The base URL of the API (e.g., "https://api.mymoodai.app").
            api_key (Optional[str]): An optional API key for authentication.
        """
        self.base_url = base_url.rstrip("/")
        self.session = requests.Session()
        if api_key:
            # Adjust the header key as needed for your API.
            self.session.headers.update({"Authorization": f"Bearer {api_key}"})
        # Default content-type for JSON payloads
        self.session.headers.update({"Content-Type": "application/json"})

    def _url(self, path: str) -> str:
        """Helper method to build a full URL."""
        return f"{self.base_url}/{path.lstrip('/')}"
    
    def _get(self, path: str, params: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        url = self._url(path)
        response = self.session.get(url, params=params)
        response.raise_for_status()
        return response.json()

    def _post(self, path: str, data: Optional[Dict[str, Any]] = None,
              files: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        url = self._url(path)
        if files:
            # For file uploads, do not use JSON encoding.
            response = self.session.post(url, data=data, files=files)
        else:
            response = self.session.post(url, json=data)
        response.raise_for_status()
        return response.json()

    # -------------- Order and Model Creation --------------

    def create_order(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        """
        Creates an order for either image generation (parent > 0) or training a new model.

        Args:
            payload (Dict[str, Any]): The order payload (e.g., {"styles": [...], "gender": 1, "parent": <id>}).

        Returns:
            Dict[str, Any]: The JSON response from the API.
        """
        return self._post("order/create", data=payload)

    def create_model(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        """
        Creates a new model (i.e. an order with parent = 0).

        Args:
            payload (Dict[str, Any]): The model payload (should have "parent": 0).

        Returns:
            Dict[str, Any]: The JSON response from the API.
        """
        return self._post("order/create/model", data=payload)

    # -------------- Training Image Endpoints --------------

    def upload_training_image(self, order_id: int, image_path: str, gender: int = 1) -> Dict[str, Any]:
        """
        Uploads a training image (selfie) to an order.

        Args:
            order_id (int): The ID of the order.
            image_path (str): The path to the image file.
            gender (int): An integer representing the user's gender/category.

        Returns:
            Dict[str, Any]: The JSON response from the API.
        """
        if not os.path.exists(image_path):
            raise FileNotFoundError(f"Image file {image_path} does not exist.")

        # Read the image file and encode as base64
        with open(image_path, "rb") as f:
            image_bytes = f.read()
        base64_data = base64.b64encode(image_bytes).decode("utf-8")

        # (Optional) wrap in a data URI prefix if you want your API 
        # to extract MIME type automatically:
        data_uri = f"data:image/jpeg;base64,{base64_data}"

        # Build the JSON body as expected by the endpoint
        payload = {
            "gender": str(gender),   # or keep it int, depends on API
            "image": data_uri        # or just `base64_data` if no data URI prefix required
        }

        # Construct the endpoint
        endpoint = f"order/{order_id}/training-images/upload"

        # Send JSON data via POST
        return self._post(endpoint, data=payload)

    def list_training_images(self, order_id: int) -> Dict[str, Any]:
        """
        Lists all training images (selfies) attached to an order.

        Args:
            order_id (int): The order ID.

        Returns:
            Dict[str, Any]: The JSON response containing the list of images.
        """
        endpoint = f"order/{order_id}/training-images/list"
        return self._get(endpoint)

    def select_training_image(self, order_id: int, selfie_id: int) -> Dict[str, Any]:
        """
        Selects a training image to be the main selfie for an order.

        Args:
            order_id (int): The order ID.
            selfie_id (int): The selfie ID to select.

        Returns:
            Dict[str, Any]: The JSON response from the API.
        """
        endpoint = f"order/{order_id}/training-images/{selfie_id}/select"
        return self._get(endpoint)

    # -------------- Order Status and Running --------------

    def get_order_status(self, order_id: int) -> Dict[str, Any]:
        """
        Gets the current status of an order.

        Args:
            order_id (int): The order ID.

        Returns:
            Dict[str, Any]: The JSON response with the status of the order.
        """
        endpoint = f"order/{order_id}/status"
        return self._get(endpoint)

    def run_order(self, order_id: int) -> Dict[str, Any]:
        """
        Launches training (or processing) for an order.

        Args:
            order_id (int): The order ID.

        Returns:
            Dict[str, Any]: The JSON response from the API.
        """
        endpoint = f"order/{order_id}/run"
        return self._get(endpoint)

    # -------------- Model and Order Listings --------------

    def list_model_avatars(self, order_id: int, page_id: int) -> Dict[str, Any]:
        """
        Lists the avatar photos on a model.

        Args:
            order_id (int): The model (order) ID.
            page_id (int): The page number for pagination.

        Returns:
            Dict[str, Any]: The JSON response containing the avatar photos.
        """
        endpoint = f"model/{order_id}/avatars/{page_id}"
        return self._get(endpoint)

    def list_styles(self) -> Dict[str, Any]:
        """
        Lists the public styles of photos that can be generated.

        Returns:
            Dict[str, Any]: The JSON response with the list of styles.
        """
        return self._get("styles/list")

    def list_orders(self) -> Dict[str, Any]:
        """
        Lists all orders.

        Returns:
            Dict[str, Any]: The JSON response with the list of orders.
        """
        return self._get("order/list")

    def list_models(self) -> Dict[str, Any]:
        """
        Lists all models.

        Returns:
            Dict[str, Any]: The JSON response with the list of models.
        """
        return self._get("model/list")

    def list_model_orders(self, order_id: int) -> Dict[str, Any]:
        """
        Lists all the orders on a model.

        Args:
            order_id (int): The model (order) ID.

        Returns:
            Dict[str, Any]: The JSON response from the API.
        """
        endpoint = f"model/{order_id}/order/list"
        return self._get(endpoint)
