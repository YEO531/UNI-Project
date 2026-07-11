from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import base64
import io
from PIL import Image
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for frontend-backend communication

# Load your trained model AND PCA transformer
try:
    model = joblib.load('trained_model.pkl')
    pca = joblib.load('pca_transformer.pkl')  # Load the PCA transformer
    print("Model and PCA transformer loaded successfully!")
except Exception as e:
    print(f"Error loading model/PCA: {e}")
    model = None
    pca = None

# Class names mapping
class_names = {
    0: 'A Famosa',
    1: 'Christ Church',
    2: 'Christ The Redeemer Statue', 
    3: 'Malacca Clock Tower',
    4: 'St Paul\'s Hill'
}

def preprocess_image(image_data):
    """Preprocess image same as training data"""
    try:
        # Decode base64 image
        if ',' in image_data:
            image_data = image_data.split(',')[1]
        
        # Convert to PIL Image
        image_bytes = base64.b64decode(image_data)
        image = Image.open(io.BytesIO(image_bytes))
        
        # Convert to grayscale
        if image.mode != 'L':
            image = image.convert('L')
        
        # Resize to 200x200 (same as training)
        image = image.resize((200, 200))
        
        # Convert to numpy array and flatten
        image_array = np.array(image)
        image_flat = image_array.flatten()
        
        # Apply PCA transformation (CRITICAL: This was missing!)
        if pca is not None:
            image_pca = pca.transform(image_flat.reshape(1, -1))
            return image_pca
        else:
            raise Exception("PCA transformer not loaded")
        
    except Exception as e:
        raise Exception(f"Image preprocessing failed: {str(e)}")

@app.route('/')
def home():
    """Serve the main page"""
    return "Tourist Attraction Recognition API is running!"

@app.route('/predict', methods=['POST'])
def predict():
    """Predict tourist attraction from uploaded image"""
    try:
        if not model or not pca:
            return jsonify({'error': 'Model or PCA transformer not loaded properly'}), 500
        
        # Get image data from request
        data = request.json
        if 'image_data' not in data:
            return jsonify({'error': 'No image data provided'}), 400
        
        # Preprocess image (now includes PCA transformation)
        image_processed = preprocess_image(data['image_data'])
        
        # Make prediction (now with correct 100 features)
        prediction = model.predict(image_processed)[0]
        probabilities = model.predict_proba(image_processed)[0]
        
        # Format results
        all_predictions = []
        for i, prob in enumerate(probabilities):
            all_predictions.append({
                'class': class_names.get(i, f'Class_{i}'),
                'confidence': float(prob)
            })
        
        # Sort by confidence (highest first)
        all_predictions.sort(key=lambda x: x['confidence'], reverse=True)
        
        response = {
            'success': True,
            'predicted_class': class_names.get(prediction, f'Class_{prediction}'),
            'confidence': float(probabilities[prediction]),
            'all_predictions': all_predictions
        }
        
        return jsonify(response)
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'model_loaded': model is not None,
        'pca_loaded': pca is not None
    })

if __name__ == '__main__':
    print("Starting Tourist Attraction Recognition Server...")
    print("Server will be available at: http://localhost:5000")
    print("API endpoint: http://localhost:5000/predict")
    
    # Check if required files exist
    if not os.path.exists('trained_model.pkl'):
        print("Warning: trained_model.pkl not found!")
    if not os.path.exists('pca_transformer.pkl'):
        print("Warning: pca_transformer.pkl not found!")
    
    app.run(debug=True, host='0.0.0.0', port=5000)