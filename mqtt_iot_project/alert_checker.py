import time
from pymongo import MongoClient
from datetime import datetime, timedelta
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart


mongo_client = MongoClient("mongodb+srv://vaishnavi2005:vaishnavi@cluster0.ddzlgcu.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0")
mongo_db = mongo_client["elderly_care"]
mongo_collection = mongo_db["sensor_data"]


EMAIL_ADDRESS = "exampleemail20025"
EMAIL_PASSWORD = "ejilaqdsmpitlcru"
EMAIL_RECEIVER = "exampleproject02@gmail.com"

def send_email(subject, body):
    try:
        msg = MIMEMultipart()
        msg["From"] = EMAIL_ADDRESS
        msg["To"] = EMAIL_RECEIVER
        msg["Subject"] = subject

        msg.attach(MIMEText(body, "plain"))

        with smtplib.SMTP("smtp.gmail.com", 587) as server:
            server.starttls()
            server.login(EMAIL_ADDRESS, EMAIL_PASSWORD)
            server.send_message(msg)
        print("Email sent successfully.")
    except Exception as e:
        print("Failed to send email:", e)

def check_alerts():
    one_min_ago = datetime.now() - timedelta(minutes=1)
    print("Checking data since:", one_min_ago)

    recent_data = mongo_collection.find({
        "timestamp": {"$gte": one_min_ago}
    })

    count = 0
    for data in recent_data:
        count += 1
        print("Processing data:", data)

        name = data.get("name", "Unknown")
        heart_rate = data.get("heart_rate")
        steps = data.get("steps")
        bp = data.get("blood_pressure", "")
        timestamp = data.get("timestamp")
        temp = data.get("body_temperature")
        medication = data.get("medication_taken")

        alerts = []

        if heart_rate is not None:
            if heart_rate < 60:
                alerts.append(f"- Low heart rate: {heart_rate} bpm")
            elif heart_rate > 100:
                alerts.append(f"- High heart rate: {heart_rate} bpm")

        if steps is not None and steps == 0:
            alerts.append(f"- No movement detected")

        if bp:
            try:
                systolic, diastolic = map(int, bp.split("/"))
                if systolic > 130:
                    alerts.append(f"- High blood pressure: {bp}")
                elif systolic < 90:
                    alerts.append(f"- Low blood pressure: {bp}")
            except ValueError:
                print(f"Invalid BP format for patient {name}: {bp}")

        if temp is not None:
            if temp > 37.5:
                alerts.append(f"- High body temperature: {temp}°C")
            elif temp < 36.0:
                alerts.append(f"- Low body temperature: {temp}°C")

        if medication == "No":
            alerts.append(f"- Medication not taken")

        if alerts:
            alert_body = f" Alert for Patient: {name}\nTimestamp: {timestamp}\n\nIssues detected:\n" + "\n".join(alerts)
            print("[ALERT]", alert_body.replace('\n', ' | '))
            send_email("Elderly Care Alert", alert_body)
            print("Mail sent")

if __name__ == "__main__":
    print("Starting alert checker with email alerts...")
    while True:
        check_alerts()
        time.sleep(60)
