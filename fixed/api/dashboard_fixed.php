<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURE IoT Home Sensor Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eef2f3; margin: 0; padding: 20px; }
        h1   { color: #2c3e50; border-bottom: 2px solid #27ae60; padding-bottom: 10px; }
        .card { background: white; border-radius: 8px; padding: 20px;
                margin: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #27ae60; color: white; border-radius: 4px 4px 0 0; }
        tr:hover { background: #f9f9f9; }
        .badge-fixed { background: #27ae60; color: white; padding: 4px 10px;
                      border-radius: 4px; font-size: 13px; font-weight: bold; }
        .search-box { margin-bottom: 20px; }
        input[type=text] { padding: 10px 14px; width: 300px; border: 2px solid #ddd;
                           border-radius: 6px; font-size: 15px; transition: border-color 0.3s; }
        input[type=text]:focus { border-color: #27ae60; outline: none; }
        button { padding: 10px 20px; background: #27ae60; color: white;
                 border: none; border-radius: 6px; cursor: pointer; font-size: 15px; font-weight: bold; }
        button:hover { background: #219150; }
        .success-msg { color: #27ae60; font-size: 13px; margin-top: 6px; font-weight: 500; }
    </style>
</head>
<body>

<h1>🛡️ Secure IoT Dashboard <span class="badge-fixed">SECURE VERSION</span></h1>

<div class="card">
    <h2>Search Sensor Readings</h2>
    <div class="search-box">
        <input type="text" id="deviceInput" placeholder="Enter Device ID (e.g. SENSOR_01)"
               value="SENSOR_01">
        <button onclick="fetchReadings()">Search Securely</button>
        <p class="success-msg">✔ PROTECTED: Input uses Prepared Statements – SQL Injection is blocked</p>
    </div>

    <table id="resultsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Temperature (°C)</th>
                <th>Humidity (%)</th>
                <th>Recorded At</th>
            </tr>
        </thead>
        <tbody id="resultsBody">
            <tr><td colspan="5" style="text-align:center;">Click Search to load data from the secure API</td></tr>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Raw Secure API Response</h2>
    <pre id="rawResponse" style="background:#1e1e1e;color:#d4d4d4;padding:15px;
         border-radius:4px;overflow-x:auto;">Response from sensors_fixed.php will appear here...</pre>
</div>

<script>
    function fetchReadings() {
        const deviceId = document.getElementById('deviceInput').value;
        const url = 'sensors_fixed.php?device_id=' + encodeURIComponent(deviceId);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                document.getElementById('rawResponse').textContent =
                    JSON.stringify(data, null, 2);

                const tbody = document.getElementById('resultsBody');
                tbody.innerHTML = '';

                if (data.status === 'success' && data.readings && data.readings.length > 0) {
                    data.readings.forEach(r => {
                        const row = `<tr>
                            <td>${r.id}</td>
                            <td>${r.device_id}</td>
                            <td>${r.temperature}</td>
                            <td>${r.humidity}</td>
                            <td>${r.recorded_at}</td>
                        </tr>`;
                        tbody.innerHTML += row;
                    });
                } else if (data.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="5" style="color:#e74c3c;font-weight:bold;">API ERROR: ${data.message}</td></tr>`;
                } else {
                    tbody.innerHTML = '<tr><td colspan="5">No secure readings found for this device.</td></tr>';
                }
            })
            .catch(err => {
                document.getElementById('rawResponse').textContent = 'Error: ' + err;
                document.getElementById('resultsBody').innerHTML = '<tr><td colspan="5" style="color:red;">Failed to connect to secure API</td></tr>';
            });
    }
</script>

</body>
</html>
