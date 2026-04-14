# Gender Predictor API Wrapper

A lightweight PHP-based API that wraps the Genderize.io service. This script validates user input, fetches gender predictions based on names, and returns a structured JSON response with confidence scoring.

## 🚀 Features

- **Input Validation**: Ensures the `name` parameter is present, non-empty, and a valid string.
- **Error Handling**: Custom helper functions to return standard HTTP status codes (400, 422, 502).
- **Confidence Logic**: Automatically calculates an `is_confident` flag based on probability (≥ 0.7) and sample size (≥ 100).
- **CORS Enabled**: Includes headers to allow requests from any origin.
- **Timeout Protection**: Prevents script hanging with a 5-second connection timeout for upstream requests.

## 🛠️ Installation

1.  **Requirement**: A local server environment (XAMPP, WAMP, MAMP) or a web server with PHP 7.4+ installed.
2.  **Setup**:
    - Create a folder in your web root (e.g., `htdocs/gender-api`).
    - Save the PHP script as `index.php` in that folder.
3.  **Start Server**: Ensure Apache is running.

## 📡 API Usage

### Endpoint
`GET /index.php`

