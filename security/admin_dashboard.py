import json, os

LOG_PATH = "../storage/logs/security.log"
HASH_PATH = "../storage/logs/hashes.json"

def show_logs():
    print("\n📜 Security Logs:")
    if os.path.exists(LOG_PATH):
        with open(LOG_PATH, "r") as f:
            print(f.read())
    else:
        print("No logs yet.")

def show_hashes():
    print("\n🔍 File Hashes:")
    if os.path.exists(HASH_PATH):
        with open(HASH_PATH, "r") as f:
            data = json.load(f)
            for k, v in data.items():
                print(f"{k}: {v[:32]}...")
    else:
        print("No hash file found.")

if __name__ == "__main__":
    show_logs()
    show_hashes()
