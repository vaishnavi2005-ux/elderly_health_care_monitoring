import ast
import json
from datetime import datetime
from pymongo import MongoClient
import sqlite3
from neo4j import GraphDatabase
import paho.mqtt.client as mqtt


mongo_client = MongoClient("mongodb+srv://vaishnavi2005:vaishnavi@cluster0.ddzlgcu.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0")
mongo_db = mongo_client["elderly_care"]
mongo_collection = mongo_db["sensor_data"]


sqlite_conn = sqlite3.connect("elderly_care.db")
sqlite_cursor = sqlite_conn.cursor()
sqlite_cursor.execute("DROP TABLE IF EXISTS sensor_data")
sqlite_cursor.execute('''
    CREATE TABLE IF NOT EXISTS sensor_data (
        patient_id TEXT,
        timestamp TEXT,
        name TEXT,
        age INTEGER,
        heart_rate INTEGER,
        steps INTEGER,
        body_temperature REAL,
        blood_pressure TEXT,
        medication_taken BOOLEAN
    )
''')
sqlite_conn.commit()

neo4j_uri = "neo4j+s://8cba7edf.databases.neo4j.io"
neo4j_user = "neo4j"
neo4j_password = "xJIgo3J7EAlHLjSGKz4FKTx8oLOFVZpVkQvETTlgxUA"
neo4j_driver = GraphDatabase.driver(neo4j_uri, auth=(neo4j_user, neo4j_password))

def save_to_neo4j(data):
    with neo4j_driver.session() as session:
        session.run("""
            MERGE (e:Elderly {patient_id: $patient_id})
            SET e.name = $name, e.age = $age
            CREATE (e)-[:HAS_READING]->(r:Reading {
                timestamp: $timestamp,
                heart_rate: $heart_rate,
                steps: $steps,
                body_temperature: $body_temperature,
                blood_pressure: $blood_pressure,
                medication_taken: $medication_taken
            })
        """,
        patient_id=data["patient_id"],
        name=data["name"],
        age=data["age"],
        timestamp=data["timestamp"],
        heart_rate=data["heart_rate"],
        steps=data["steps"],
        body_temperature=data["body_temperature"],
        blood_pressure=data["blood_pressure"],
        medication_taken=data["medication_taken"])



def on_connect(client, userdata, flags, rc):
    print("Connected with result code", rc)
    client.subscribe("sensor/health")

def on_message(client, userdata, msg):
    print(f"Received message on {msg.topic}: {msg.payload.decode()}")
    try:
        data = json.loads(msg.payload.decode())
        data["timestamp"] = datetime.strptime(data["timestamp"], "%Y-%m-%d %H:%M:%S")
        mongo_collection.insert_one(data) # MongoDB

       
        sqlite_cursor.execute("""
            INSERT INTO sensor_data VALUES (?,?,?,?,?,?,?,?,?)
        """, (
            data["patient_id"],
            data["timestamp"],
            data["name"],
            data["age"],
            data["heart_rate"],
            data["steps"],
            data["body_temperature"],
            data["blood_pressure"],
            data["medication_taken"]
        ))
        sqlite_conn.commit()

        
        save_to_neo4j(data)

        print("Data saved to MongoDB, SQLite, and Neo4j")

    except Exception as e:
        print("Error:", e)

mqtt_client = mqtt.Client()
mqtt_client.on_connect = on_connect
mqtt_client.on_message = on_message

mqtt_client.connect("localhost", 1883, 60)
mqtt_client.loop_forever()
