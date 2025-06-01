# Elderly Care Monitoring System
**Overview**

This project is an Elderly Care Monitoring System designed to simulate and collect health data from elderly patients using MQTT and store the data in multiple databases (SQLite, MongoDB, Neo4j). It also features role-based PHP dashboards for Doctors, Nurses, and Admins to view and manage patient data.

This project is an Elderly Care Monitoring System designed to simulate and collect health data from elderly patients using MQTT and store the data in multiple databases (SQLite, MongoDB, Neo4j). It also features role-based PHP dashboards for Doctors, Nurses, and Admins to view and manage patient data.


**Features**
Simulated IoT data (heart rate, steps, body temperature, blood pressure, medication taken)

MQTT broker integration for real-time data ingestion

Data storage in:

SQLite (local relational database)

MongoDB (NoSQL document store)

Neo4j (graph database for relationships)

Mail alerting using smtplib for abnormal vital signs

User management with role-based login (Doctor, Nurse, Admin)

Dashboards:

Doctor dashboard: View patient stats

Nurse dashboard: View assigned patient records

Admin dashboard: Manage users and patient assignments

Secure login with password hashing and session management

Patient assignment functionality for nurses

**Technologies Used**
Python with paho-mqtt and database connectors

SQLite (via PDO in PHP and sqlite3 in Python)

MongoDB (via PyMongo)

Neo4j (via official Neo4j Python driver)

PHP for backend logic and dashboards

MySQL for user authentication and assignments

MQTT broker (e.g., Mosquitto)


**Setup and Installation**

Prerequisites

Python 3.x installed

MongoDB Atlas account and cluster created

Neo4j Aura cloud database setup

MQTT broker installed and running locally (e.g., Mosquitto)

Install Python dependencies



**Installation and Setup**

1. MQTT Broker Setup (Mosquitto)

Install Mosquitto:

sudo apt update
sudo apt install mosquitto mosquitto-clients

Start the broker:

mosquitto -v

2. Python Environment

Install dependencies:

pip install paho-mqtt pymongo mysql-connector-python py2neo

Set up email settings using Gmail App Passwords for SMTP login.

3. MongoDB Setup

Create a cluster on MongoDB Atlas.

Whitelist your IP and create a user.

Connect using the URI:

MongoClient("your_mongodb_connection_string")

4. SQLite and Neo4j Setup

Neo4j: Provide connection URIs and credentials in the Python MQTT subscriber script.

SQLite (sensor_data)

The elderly_care.db SQLite file stores simulated patient sensor data.

Schema includes columns like timestamp, name, age, heart_rate, steps, etc.

5. Set up databases
MySQL (users, assignments)

Create a database named health_care_project.

Import users and assignment tables via phpMyAdmin or SQL scripts.

Insert users with roles (doctor, nurse, admin). Passwords should be hashed using PHP's password_hash().


6. Deploy PHP backend
Place PHP files (login.php, doctor_dashboard.php, nurse_dashboard.php, admin_dashboard.php, etc.) in your Xampp root (i.e htdocs).

Update database connection details if needed.

Access the login page via browser and log in with valid credentials.


**Execution Flow**

Data Simulation: A Python script simulates sensor data and publishes it to MQTT topics.

Message Reception: The Python MQTT client subscribes to these topics.

**Data Processing:**

Transforms received messages.

Stores vital signs in MongoDB.

Stores patient profiles in MySQL.

Stores patient relationships and co-location data in Neo4j.

Sends Email Alerts:

Triggered when thresholds (e.g., high heart rate, low steps, abnormal BP) are breached.

Email includes timestamp, patient name, and vital details.


**How to Run**

1. Start MQTT Broker
use mosqulitto -v
Make sure your MQTT broker is running locally on port 1883.

2. Run send_data

The send_data script simulates sending sensor data every few seconds:

python send_data.py


3. Run receive_data / Data Processor

The receive_data script listens for incoming data, saves it to databases, and triggers SMS alerts:

python receive_data.py

4. Run alert checker

Checks for alert conditions and sends alerts through mails.

python alert_checker


5. Database Setup

To recreate the database, import the `database/your_dump_file.sql` in phpMyAdmin:

1. Open phpMyAdmin.
2. Select or create a new database.
3. Go to the **Import** tab.
4. Choose the SQL file from the repository.
5. Click **Go** to import the database.

5. The dashboard is a separate component and requires a web server (e.g., Apache, XAMPP for PHP; or Flask/Django if Python backend).
Access Web Dashboards:
After setting up phpmyadmin go to your web browser and type '(http://localhost/elderly_care_monitoring/login.php)'
Login as admin, nurse, or doctor to manage and view patient data.

PHP files serve the user interface (host on Apache or any web server with PHP support)


**Configuration**

MongoDB connection string is stored in the subscriber script (replace with your credentials).

Neo4j connection details (URI, username, password) are configured in the subscriber script.

Remember to keep sensitive credentials secure and avoid pushing them to public repositories.


**Mail Alerting**
Alerts are sent when:

Heart rate exceeds a defined threshold (e.g., > 100 bpm)
Blood pressure exceeds or is less than the threshold.
If the medications are not taken.
If the patients does not make much movements.

Other conditions can be added easily in the subscriber script.

**Project Structure**

|- send_data.py       
|- receive_data.py      
|- elderly_care.db    
|- README.md           
|-admin_dashboard.
|-dashboard.php 
|-db_config.php 
|-doctor_dashboard.
|-login.php 
|-nurse_dashboard.php

**Testing**

Unit Testing: Simulated multiple message payloads with edge cases.

Integration Testing: Verified message reception, parsing, and DB insertion.

Performance: Sustained message flow every 2 seconds for load testing.


**Future Improvements**

Add a web dashboard for live monitoring

Implement user authentication and data privacy features

Expand sensor types and alert criteria

Dockerize the project for easy deployment
