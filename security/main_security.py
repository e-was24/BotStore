import os
import time
import threading
import subprocess
from datetime import datetime

# === CONFIG ===
CONFIG = {
    "scanner": "scanner.py",
    "validator": "hash_validator.py",
    "backup": "backup.py",
    "monitor": "monitor.py",
    "log_file": "../storage/logs/security_main.log"
}

def log(message):
    """Simple logger to write messages to log file"""
    os.makedirs(os.path.dirname(CONFIG["log_file"]), exist_ok=True)
    with open(CONFIG["log_file"], "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] {message}\n")
    print(message)

def run_script(script_name, repeat=False, delay=0):
    """Run a Python script (optionally in loop)"""
    path = os.path.join(os.path.dirname(__file__), script_name)
    if not os.path.exists(path):
        log(f"❌ File not found: {script_name}")
        return

    def loop():
        while True:
            log(f"▶ Running {script_name} ...")
            subprocess.run(["python", path], shell=True)
            if not repeat:
                break
            log(f"⏳ Waiting {delay} seconds before next run...")
            time.sleep(delay)

    t = threading.Thread(target=loop, daemon=True)
    t.start()

def main():
    log("=== 🔐 BotStore Security System Started ===")

    # Jalankan scanner sekali di awal
    run_script(CONFIG["scanner"])

    # Jalankan validator berkala tiap 10 menit
    run_script(CONFIG["validator"], repeat=True, delay=600)

    # Jalankan backup tiap 30 menit
    run_script(CONFIG["backup"], repeat=True, delay=1800)

    # Jalankan monitor file real-time
    run_script(CONFIG["monitor"])

    # Biar tetap hidup
    try:
        while True:
            time.sleep(5)
    except KeyboardInterrupt:
        log("🛑 Security system stopped manually.")

if __name__ == "__main__":
    main()
