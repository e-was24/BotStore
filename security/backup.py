import os, shutil, json
from datetime import datetime

with open("config.json", "r") as f:
    config = json.load(f)

SRC = config["scan_path"]
DEST = config["backup_path"]
LOG_FILE = config["log_path"]

def log(msg):
    os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now()}] [BACKUP] {msg}\n")
    print(msg)

def backup():
    os.makedirs(DEST, exist_ok=True)
    filename = f"backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}.zip"
    dest_path = os.path.join(DEST, filename)
    shutil.make_archive(dest_path.replace(".zip", ""), "zip", SRC)
    log(f"💾 Backup created: {dest_path}")

if __name__ == "__main__":
    backup()
