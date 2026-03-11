CREATE DATABASE IF NOT EXISTS iot_sensors;
USE iot_sensors;

CREATE TABLE IF NOT EXISTS devices (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    device_id   VARCHAR(50)  NOT NULL UNIQUE,
    device_name VARCHAR(100) NOT NULL,
    location    VARCHAR(100) NOT NULL,
    api_key     VARCHAR(64)  NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sensor_readings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    device_id   VARCHAR(50)  NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    humidity    DECIMAL(5,2) NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       VARCHAR(20)  DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO devices (device_id, device_name, location, api_key) VALUES
('SENSOR_01', 'Living Room Sensor',  'Living Room',  'key_abc123plaintext'),
('SENSOR_02', 'Bedroom Sensor',      'Bedroom',      'key_xyz456plaintext'),
('SENSOR_03', 'Kitchen Sensor',      'Kitchen',      'key_def789plaintext');

INSERT INTO users (username, password, role) VALUES
('admin',   'admin123',   'admin'),
('monitor', 'password1',  'viewer');

INSERT INTO sensor_readings (device_id, temperature, humidity) VALUES
('SENSOR_01', 21.5, 55.2),
('SENSOR_02', 19.8, 60.1),
('SENSOR_03', 23.4, 45.7);
