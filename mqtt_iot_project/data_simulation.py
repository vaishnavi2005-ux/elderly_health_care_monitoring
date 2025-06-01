# data_simulation.py
import random
from datetime import datetime

def process_data():
    heart_rate = random.randint(60, 100)
    steps = random.randint(0, 10000)
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    return {
        "timestamp": timestamp,
        "heart_rate": heart_rate,
        "steps": steps
    }
