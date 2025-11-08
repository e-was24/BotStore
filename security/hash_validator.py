import os, json, hashlib, requests
from datetime import datetime
import requests

HASH_DB = "security/hashes.json"
LOG_FILE = "security/logs/security.log"
CONFIG_FILE = "security/config.json"

requests.post("https://yourdomain.com/api/notify.php", json={
    "type": "alert",
    "message": "File index.php was modified unexpectedly!",
    "product": "System Integrity"
})

# Load konfigurasi
with open(CONFIG_FILE, "r") as f:
    config = json.load(f)

SCAN_PATH = config.get("scan_path", ".")
WEBHOOK_URL = config.get("webhook_url", None)  # optional, bisa None

def log(message):
    os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)
    line = f"[{datetime.now()}] [HASH] {message}"
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(line + "\n")
    print(line)
    # Kirim webhook jika diset
    if WEBHOOK_URL:
        try:
            requests.post(WEBHOOK_URL, json={"text": message})
        except Exception:
            pass

def get_hash(filepath):
    h = hashlib.sha256()
    with open(filepath, "rb") as f:
        while chunk := f.read(8192):
            h.update(chunk)
    return h.hexdigest()

def validate_files():
    old_hashes = {}
    if os.path.exists(HASH_DB):
        with open(HASH_DB, "r") as f:
            old_hashes = json.load(f)

    new_hashes = {}
    changed, new_files = [], []

    for root, _, files in os.walk(SCAN_PATH):
        for file in files:
            if file.endswith(".php"):
                full_path = os.path.join(root, file)
                file_hash = get_hash(full_path)
                new_hashes[full_path] = file_hash
                if full_path not in old_hashes:
                    new_files.append(full_path)
                elif old_hashes[full_path] != file_hash:
                    changed.append(full_path)

    with open(HASH_DB, "w") as f:
        json.dump(new_hashes, f, indent=4)

    if changed or new_files:
        for c in changed:
            log(f"⚠️ File modified: {c}")
        for n in new_files:
            log(f"🆕 New file detected: {n}")
    else:
        log("✅ No file changes detected")

if __name__ == "__main__":
    validate_files()
