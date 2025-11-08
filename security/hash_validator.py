import os, json, hashlib
from datetime import datetime

HASH_DB = "hashes.json"
with open("config.json", "r") as f:
    config = json.load(f)
SCAN_PATH = config["scan_path"]
LOG_FILE = config["log_path"]

def log(message):
    os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now()}] [HASH] {message}\n")
    print(message)

def get_hash(filepath):
    h = hashlib.sha256()
    with open(filepath, "rb") as f:
        h.update(f.read())
    return h.hexdigest()

def validate_files():
    old_hashes = {}
    if os.path.exists(HASH_DB):
        with open(HASH_DB, "r") as f:
            old_hashes = json.load(f)

    new_hashes = {}
    changed = []

    for root, _, files in os.walk(SCAN_PATH):
        for file in files:
            if file.endswith(".php"):
                full_path = os.path.join(root, file)
                new_hashes[full_path] = get_hash(full_path)
                if full_path in old_hashes and old_hashes[full_path] != new_hashes[full_path]:
                    changed.append(full_path)

    with open(HASH_DB, "w") as f:
        json.dump(new_hashes, f, indent=4)

    if changed:
        for c in changed:
            log(f"⚠️ File changed: {c}")
    else:
        log("✅ No file changes detected")

if __name__ == "__main__":
    validate_files()
