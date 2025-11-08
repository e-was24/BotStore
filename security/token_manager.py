import hmac, hashlib, json, os, time

TOKEN_FILE = "../storage/tmp/temp_tokens.json"
SECRET_KEY = b"lanova_secret_key"

def load_tokens():
    if os.path.exists(TOKEN_FILE):
        with open(TOKEN_FILE, "r") as f:
            return json.load(f)
    return {}

def save_tokens(data):
    os.makedirs(os.path.dirname(TOKEN_FILE), exist_ok=True)
    with open(TOKEN_FILE, "w") as f:
        json.dump(data, f, indent=4)

def create_token(file_name, lifetime=3600):
    timestamp = int(time.time())
    message = f"{file_name}:{timestamp}".encode()
    token = hmac.new(SECRET_KEY, message, hashlib.sha256).hexdigest()
    tokens = load_tokens()
    tokens[token] = {"file": file_name, "expire": timestamp + lifetime}
    save_tokens(tokens)
    return token

def validate_token(token):
    tokens = load_tokens()
    now = int(time.time())
    if token in tokens and now < tokens[token]["expire"]:
        return tokens[token]["file"]
    return None
