<!DOCTYPE html>
<!-- ============================================================
     dashboard.php – Admin dashboard to view IoT readings (VULNERABLE)
     7026CEM CW2 Piece 2 – IoT Home Sensor REST Service
     ============================================================ -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Home Sensor Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; padding: 20px; }
        h1   { color: #2c3e50; }
        .card { background: white; border-radius: 8px; padding: 20px;
                margin: 15px 0; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge-vuln { background: #e74c3c; color: white; padding: 3px 8px;
                      border-radius: 4px; font-size: 12px; font-weight: bold; }
        .search-box { margin-bottom: 15px; }
        input[type=text] { padding: 8px 12px; width: 280px; border: 1px solid #ccc;
                           border-radius: 4px; font-size: 14px; }
        button { padding: 8px 16px; background: #2980b9; color: white;
                 border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #1a6fa8; }
        .warning { color: #e74c3c; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>

<h1>🏠 IoT Home Sensor Dashboard <span class="badge-vuln">VULNERABLE DEMO</span></h1>

<div class="card">
    <h2>Search Sensor Readings</h2>

    <!--
        VULNERABILITY: The device_id typed here is sent directly
        to sensors.php which embeds it in SQL without sanitisation.
        Try typing:  SENSOR_01' OR '1'='1
    -->
    <div class="search-box">
        <input type="text" id="deviceInput" placeholder="Enter Device ID (e.g. SENSOR_01)"
               value="SENSOR_01">
        <button onclick="fetchReadings()">Search</button>
        <p class="warning">⚠ DEMO: Input is NOT sanitised – SQL Injection possible</p>
    </div>

    <table id="resultsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Password / Temp</th>
                <th>Humidity (%)</th>
                <th>Recorded At</th>
            </tr>
        </thead>
        <tbody id="resultsBody">
            <tr><td colspan="5" style="text-align:center;">Click Search to load data</td></tr>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Raw API Response</h2>
    <pre id="rawResponse" style="background:#1e1e1e;color:#d4d4d4;padding:15px;
         border-radius:4px;overflow-x:auto;">Response will appear here...</pre>
</div>

<script>
    // Sends the device ID to the VULNERABLE endpoint over plain HTTP
    function fetchReadings() {
        const deviceId = document.getElementById('deviceInput').value;
        // VULNERABLE: no input validation before sending
        const url = 'http://localhost/iot_rest/api/sensors.php?device_id=' + encodeURIComponent(deviceId);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                document.getElementById('rawResponse').textContent =
                    JSON.stringify(data, null, 2);

                const tbody = document.getElementById('resultsBody');
                tbody.innerHTML = '';

                if (data.readings && data.readings.length > 0) {
                    data.readings.forEach(r => {
                        const row = `<tr>
                            <td>${r.id}</td>
                            <td>${r.device_id}</td>
                            <td>${r.password}</td>
                            <td>${r.humidity}</td>
                            <td>${r.recorded_at}</td>
                        </tr>`;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5">No readings found</td></tr>';
                }
            })
            .catch(err => {
                document.getElementById('rawResponse').textContent = 'Error: ' + err;
            });
    }
</script>

</body>
</html>
