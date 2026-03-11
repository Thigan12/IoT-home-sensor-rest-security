CREATE DATABASE IF NOT EXISTS iot_sensors_fixed;
USE iot_sensors_fixed;

CREATE TABLE IF NOT EXISTS devices (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    device_id   VARCHAR(50)  NOT NULL UNIQUE,
    device_name VARCHAR(100) NOT NULL,
    location    VARCHAR(100) NOT NULL,
    api_key_hash VARCHAR(64) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sensor_readings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    device_id   VARCHAR(50)  NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    humidity    DECIMAL(5,2) NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(device_id)
);

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role       VARCHAR(20)  DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO devices (device_id, device_name, location, api_key_hash) VALUES
('SENSOR_01', 'Living Room Sensor', 'Living Room', SHA2('key_abc123plaintext', 256)),
('SENSOR_02', 'Bedroom Sensor',     'Bedroom',     SHA2('key_xyz456plaintext', 256)),
('SENSOR_03', 'Kitchen Sensor',     'Kitchen',     SHA2('key_def789plaintext', 256));

INSERT INTO users (username, password_hash, role) VALUES
('admin',   '$2y$12$QjSHW9S7i8z9.bcP3u4V.eD/N.7gCj1f9rC6H5vG8jI0mN6X4W2',  'admin'),
('monitor', '$2y$12$R.S.T.U.V.W.X.Y.Z.0.1.2.3.4.5.6.7.8.9.A.B.C.D.E.F.G.', 'viewer');
