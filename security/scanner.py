## 🧠 Contoh isi `scanner.py`
import os

def scan_php_files(base_dir):
    suspicious = ['eval(', 'base64_decode', 'system(', 'exec(', 'shell_exec']
    for root, _, files in os.walk(base_dir):
        for f in files:
            if f.endswith('.php'):
                path = os.path.join(root, f)
                with open(path, 'r', encoding='utf-8', errors='ignore') as file:
                    content = file.read()
                    for s in suspicious:
                        if s in content:
                            print(f"[⚠️] Found suspicious code in {path}: {s}")

if __name__ == "__main__":
    scan_php_files("../")