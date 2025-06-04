import json
import random
import time
from datetime import datetime
import paho.mqtt.client as mqtt

mqtt_client = mqtt.Client()
mqtt_client.connect("localhost", 1883, 60)


name_to_id = {}
next_patient_id = 1

def get_patient_id(name):
    global next_patient_id
    if name not in name_to_id:
        name_to_id[name] = next_patient_id
        next_patient_id += 1
    return name_to_id[name]


all_possible_names = ["Alice", "Bob", "Charlie", "Diana", "Maria", "Ethan", "Grace", "Helen"]


def generate_data():
    name = random.choice(all_possible_names)
    patient_id = get_patient_id(name)

    return {
        "patient_id": patient_id,
        "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "name": name,
        "heart_rate": random.randint(40, 140),
        "steps": random.randint(0, 5000),
        "age": random.choice([65, 70, 75, 80, 85]),
        "medication_taken": random.choice(["Yes", "No"]),
        "blood_pressure": f"{random.randint(90, 140)}/{random.randint(40, 90)}",
        "body_temperature": round(random.uniform(36.0, 38.0), 1)
    }

while True:
    data = generate_data()
    mqtt_client.publish("sensor/health", json.dumps(data))
    print("Published:", data)
    time.sleep(2)
