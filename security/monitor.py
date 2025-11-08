import time, json, os
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
from datetime import datetime

with open("config.json", "r") as f:
    config = json.load(f)

WATCH_PATH = config["scan_path"]
LOG_FILE = config["log_path"]

class Watcher(FileSystemEventHandler):
    def log(self, message):
        os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)
        with open(LOG_FILE, "a", encoding="utf-8") as f:
            f.write(f"[{datetime.now()}] [MONITOR] {message}\n")
        print(message)

    def on_modified(self, event):
        if event.is_directory:
            return
        if event.src_path.endswith(".php"):
            self.log(f"🟡 File modified: {event.src_path}")

    def on_created(self, event):
        if event.is_directory:
            return
        self.log(f"🟢 File created: {event.src_path}")

    def on_deleted(self, event):
        if event.is_directory:
            return
        self.log(f"🔴 File deleted: {event.src_path}")

if __name__ == "__main__":
    event_handler = Watcher()
    observer = Observer()
    observer.schedule(event_handler, WATCH_PATH, recursive=True)
    observer.start()
    print("👁️ File monitor started...")
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()
