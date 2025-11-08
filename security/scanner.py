import os, re, json
from datetime import datetime

with open("config.json", "r") as f:
    config = json.load(f)

LOG_FILE = config["log_path"]
SCAN_PATH = config["scan_path"]

SUSPICIOUS_PATTERNS = [
    r"eval\(",
    r"base64_decode\(",
    r"shell_exec\(",
    r"passthru\(",
    r"system\(",
    r"exec\(",
    r"curl_exec\(",
    r"file_get_contents\(['\"]php://",
    r"assert\("
]

def log(message):
    os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now()}] [SCANNER] {message}\n")
    print(message)

def scan_directory(path):
    for root, _, files in os.walk(path):
        for file in files:
            if file.endswith(".php"):
                full_path = os.path.join(root, file)
                try:
                    with open(full_path, "r", encoding="utf-8", errors="ignore") as f:
                        content = f.read()
                        for pattern in SUSPICIOUS_PATTERNS:
                            if re.search(pattern, content):
                                log(f"⚠️ Suspicious code found in {full_path}")
                                break
                except Exception as e:
                    log(f"❌ Error reading {full_path}: {e}")

if __name__ == "__main__":
    log("🔍 PHP Security Scan Started")
    scan_directory(SCAN_PATH)
    log("✅ PHP Security Scan Finished")
