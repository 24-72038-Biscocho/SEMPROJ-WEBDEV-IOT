<section id="app">
    <script>
        window.greenPulseSensors = <?= json_encode($thresholds, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>.map((sensor) => ({ ...sensor, value: ({temperature:23.4, humidity:64, ph:6.1, tds:720})[sensor.key], minDay: null, maxDay: null }));
    </script>
    <aside id="sidebar">
        <div class="brand">
            <svg width="26" height="26" viewBox="0 0 30 30" aria-hidden="true">
                <path d="M15 27C6 24 4 14 6 5c9 0 18 4 18 12 0 5-4 8-9 10z" fill="#A8D672"/>
            </svg>
            GreenPulse
        </div>
        <button class="nav on" type="button" data-v="dash" aria-current="page">Dashboard</button>
        <button class="nav" type="button" data-v="act">Actuators</button>
        <button class="nav" type="button" data-v="alerts">Alert log</button>
        <button class="nav" type="button" data-v="reports">Reports / History</button>
        <button class="nav" type="button" data-v="devices">Devices</button>
        <?php if ($isAdmin): ?><button class="nav" id="admin-nav" type="button" data-v="admin">Admin management</button><?php endif; ?>
        <button class="nav" type="button" data-v="profile">Profile</button>
        <button class="nav" type="button" data-v="settings">Settings</button>
        <div class="spacer"></div>
        <a class="nav" href="<?= e(url('logout')) ?>">Sign out</a>
    </aside>
    <main class="container-fluid">
        <div class="top">
            <div>
                <button class="mobile-menu" type="button" id="mobile-menu" aria-label="Toggle navigation" aria-expanded="false">&#9776;</button>
                <h2 id="title">Greenhouse A</h2>
                <span class="live"><span class="dot"></span><span id="upd">Demo readings</span></span>
            </div>
            <div class="user">
                <button class="notification-bell" type="button" id="notification-bell" aria-label="Unread alerts">♢ <span id="unread-count">0</span></button>
                <div class="user-copy">
                    <div class="user-name"><?= e($user['full_name']) ?></div>
                    <div class="account-role"><?= e($user['role'] ?? 'Farm Staff') ?></div>
                </div>
                <div class="av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
                <button class="btn btn-outline-success btn-sm theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
            </div>
        </div>
        <p class="demo-note dashboard-part" role="status"><strong>Prototype mode:</strong> readings, alerts, actuator controls, and device status are sample data only. No live sensors or relay controls are connected.</p>
        <?php if ($notice !== ''): ?><p class="notice dashboard-notice" role="status"><?= e($notice) ?></p><?php endif; ?>

        <section class="dashboard-part" aria-label="Environmental monitoring">
            <div class="section-heading">
                <div><h3>Environmental monitoring</h3><p class="sub">Lettuce growing ranges · Greenhouse A</p></div>
                <span class="tag warn" id="device-status"><span class="status-dot"></span>Device offline · demo data</span>
            </div>
            <div class="row g-3" id="gauges"></div>
        </section>

        <section class="row g-3 dashboard-part" aria-label="Telemetry and actuator control">
            <div class="col-12 col-xl-7" id="chart-column">
                <div class="card h-100">
                    <div class="ch">
                        <div><h3>Historical telemetry</h3><p class="sub">Sample readings by sensor</p></div>
                        <div class="chart-controls">
                            <div class="seg" id="sensor-select" aria-label="Select chart sensor">
                                <button class="on" type="button" data-sensor="Temperature">Temp</button><button type="button" data-sensor="Humidity">Humidity</button><button type="button" data-sensor="pH level">pH</button><button type="button" data-sensor="TDS">TDS</button>
                            </div>
                            <div class="seg" id="rng" aria-label="Chart time range"><button class="on" type="button" data-h="24">24 h</button><button type="button" data-h="168">7 d</button><button type="button" data-h="720">30 d</button></div>
                            <label class="date-range">From <input id="chart-date-from" type="date" aria-label="Start date"></label>
                            <label class="date-range">To <input id="chart-date-to" type="date" aria-label="End date"></label>
                            <button class="btn btn-outline-success btn-sm" id="export-chart" type="button">Export CSV</button>
                        </div>
                    </div>
                    <svg class="chart" id="chart" viewBox="0 0 640 260" role="img" aria-label="Sample sensor readings over time"></svg>
                    <div class="leg"><span id="chart-legend"><i style="background:#2F6B3A"></i>Temperature (&deg;C)</span></div>
                </div>
            </div>
            <div class="col-12 col-xl-5" id="actuator-column">
                <div class="card h-100" id="actuators">
                    <h3>Actuator control</h3>
                    <div class="act" id="act"></div>
                </div>
            </div>
        </section>

        <section class="card dashboard-part" id="alert-log">
            <div class="ch"><h3>Recent alerts</h3><button class="text-btn" type="button" data-v="alerts">View all</button></div>
            <label class="filter-label">Level <select id="alert-filter"><option value="all">All levels</option><option value="w">Warning</option><option value="b">Critical</option><option value="i">Info</option></select></label>
            <div class="wrap">
                <table>
                    <thead><tr><th>Date &amp; time</th><th>Level</th><th>Sensor / device</th><th>Event</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody id="alerts"></tbody>
                </table>
            </div>
        </section>

        <?php if ($isAdmin): ?>
        <section class="card admin-panel hide" id="admin-panel">
            <div class="ch">
                <div><h3>Admin management</h3><p class="sub">Manage demo accounts for the UI prototype.</p></div>
                <button class="btn btn-success small-btn" id="add-user" type="button">Add user</button>
            </div>
            <div class="prototype-warning">Account rows are demo-only and do not save to the database. Admin visibility and threshold updates are role-checked.</div>
            <div class="wrap">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Access</th><th>Actions</th></tr></thead>
                    <tbody id="users"></tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <section class="card hide view-panel" id="profile-panel" aria-labelledby="profile-heading">
            <h3 id="profile-heading">Profile</h3>
            <p class="sub">Your GreenPulse account details.</p>
            <div class="profile-details">
                <p><strong>Full name: </strong><span><?= e($user['full_name']) ?></span></p>
                <p><strong>Email: </strong><span><?= e($user['email']) ?></span></p>
                <p><strong>Role: </strong><span><?= e($user['role'] ?? 'Farm Staff') ?></span></p>
            </div>
        </section>

        <section class="card hide view-panel" id="settings-panel" aria-labelledby="settings-heading">
            <h3 id="settings-heading">Settings</h3>
            <p class="sub">Manage greenhouse sensor thresholds and appearance.</p>
            <?php if ($isAdmin): ?>
            <form class="threshold-form" method="post" action="<?= e(url('dashboard/thresholds')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <h4>Sensor thresholds</h4>
                <?php foreach ($thresholds as $sensor): ?>
                <div class="threshold-row"><strong><?= e($sensor['name']) ?><?= $sensor['unit'] !== '' ? ' (' . e($sensor['unit']) . ')' : '' ?></strong>
                    <label>Minimum<input name="<?= e($sensor['key']) ?>_min" type="number" step="any" min="<?= e($sensor['min']) ?>" max="<?= e($sensor['max']) ?>" value="<?= e($sensor['low']) ?>" required></label>
                    <label>Maximum<input name="<?= e($sensor['key']) ?>_max" type="number" step="any" min="<?= e($sensor['min']) ?>" max="<?= e($sensor['max']) ?>" value="<?= e($sensor['high']) ?>" required></label>
                </div>
                <?php endforeach; ?>
                <button class="btn btn-success small-btn" type="submit">Save thresholds</button>
            </form>
            <?php else: ?><p class="sub">Threshold editing is available to administrators only.</p><?php endif; ?>
            <div class="settings-option">
                <div><strong>Appearance</strong><p class="sub">Switch between light and dark mode.</p></div>
                <button class="btn btn-outline-success btn-sm theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
            </div>
        </section>

        <section class="card hide view-panel" id="devices-panel"><h3>Devices</h3><p class="sub">Registered greenhouse hardware and sensor status.</p>
            <div class="prototype-warning" role="status">Offline · ESP32/MQTT integration is not connected in this prototype.</div>
            <div class="wrap"><table><thead><tr><th>Device</th><th>Type</th><th>Status</th><th>Calibration</th></tr></thead><tbody><tr><td>Greenhouse A controller</td><td>ESP32</td><td><span class="tag critical">Offline · demo</span></td><td>Not connected</td></tr><tr><td>Temperature / humidity</td><td>Sensor</td><td>Sample data</td><td>Not available</td></tr><tr><td>pH / TDS probe</td><td>Sensor</td><td>Sample data</td><td>Not available</td></tr></tbody></table></div>
        </section>
        <section class="card hide view-panel" id="reports-panel"><div class="ch"><div><h3>Reports / History</h3><p class="sub">Sample time-series records for the selected sensor.</p></div><button class="btn btn-outline-success btn-sm" id="print-report" type="button">Print / Save PDF</button></div>
            <div class="wrap"><table><thead><tr><th>Sample</th><th>Sensor</th><th>Reading</th><th>Range</th></tr></thead><tbody id="history-rows"></tbody></table></div>
            <button class="btn btn-outline-success btn-sm" id="export-history" type="button">Export CSV</button>
        </section>
    </main>
</section>
<div class="notification-popover hide" id="notification-popover" role="status"></div>
<dialog id="user-dialog">
    <form id="user-form" method="dialog">
        <h2 id="user-dialog-title">Add demo user</h2>
        <label for="user-name">Full name<input class="form-control" id="user-name" name="name" required></label>
        <label for="user-email">Email<input class="form-control" id="user-email" name="email" type="email" required></label>
        <div class="dialog-actions">
            <button class="btn btn-outline-secondary" id="cancel-user" type="button">Cancel</button>
            <button class="btn btn-success small-btn" type="submit">Save demo user</button>
        </div>
    </form>
</dialog>
