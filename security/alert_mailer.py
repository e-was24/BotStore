import smtplib
from email.mime.text import MIMEText

ADMIN_EMAIL = "elan@botstore.local"

def send_alert(message):
    msg = MIMEText(message)
    msg["Subject"] = "⚠️ BotStore Security Alert"
    msg["From"] = ADMIN_EMAIL
    msg["To"] = ADMIN_EMAIL

    try:
        with smtplib.SMTP("localhost") as s:
            s.send_message(msg)
        print("📧 Alert sent successfully.")
    except Exception as e:
        print("❌ Failed to send alert:", e)
