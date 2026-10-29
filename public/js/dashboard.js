(() => {
    const sensors = (window.greenPulseSensors || []).map((sensor) => ({
        ...sensor,
        value: ({ temperature: 23.4, humidity: 64, ph: 6.1, tds: 720 })[sensor.key],
        minDay: null,
        maxDay: null
    }));
    const $ = (selector) => document.querySelector(selector);
    const $$ = (selector) => [...document.querySelectorAll(selector)];
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    })[character]);
    const inRange = (sensor) => sensor.value >= sensor.low && sensor.value <= sensor.high;
    let activeView = "dash";
    let selectedSensor = sensors[0]?.name || "Temperature";
    let selectedRange = 24;
    let customFrom = "", customTo = "";
    const commandFeedback = new Map();

    function renderGauges() {
        $("#gauges").innerHTML = sensors.map((sensor) => {
            const span = sensor.max - sensor.min;
            const progress = Math.max(0, Math.min(100, (sensor.value - sensor.min) / span * 100));
            const low = (sensor.low - sensor.min) / span * 100;
            const high = (sensor.high - sensor.min) / span * 100;
            const margin = (sensor.high - sensor.low) * .15;
            const near = inRange(sensor) && (sensor.value >= sensor.high - margin || sensor.value <= sensor.low + margin);
            const status = !inRange(sensor) ? (sensor.value < sensor.low ? "Too low" : "Too high") : near ? "Near limit" : "In range";
            const tone = !inRange(sensor) ? "critical" : near ? "warn" : "";
            const day = sensorValues(sensor, 24);
            const minDay = sensor.minDay ?? Math.min(...day);
            const maxDay = sensor.maxDay ?? Math.max(...day);
            const change = day.at(-1) - day[0];
            const trend = Math.abs(change) < (sensor.high - sensor.low) * .025 ? ["→", "Stable", "stable"] : change > 0 ? ["↑", "Rising", "rising"] : ["↓", "Falling", "falling"];
            return `<div class="col-12 col-sm-6 col-xxl-3"><article class="card gauge h-100">
                <div class="gn">${escapeHtml(sensor.name)}</div>
                <svg viewBox="0 0 100 60" aria-hidden="true">
                    <path d="M10 55A40 40 0 0 1 90 55" pathLength="100" fill="none" stroke="var(--line)" stroke-width="9" stroke-linecap="round"/>
                    <path d="M10 55A40 40 0 0 1 90 55" pathLength="100" fill="none" stroke="#A8D672" stroke-opacity=".55" stroke-width="9" stroke-dasharray="${high-low} 100" stroke-dashoffset="${-low}"/>
                    <path d="M10 55A40 40 0 0 1 90 55" pathLength="100" fill="none" stroke="${tone === "critical" ? "#C8452F" : tone === "warn" ? "#D98E1F" : "#2F6B3A"}" stroke-width="4" stroke-linecap="round" stroke-dasharray="${progress} 100"/>
                </svg>
                <div class="gv">${sensor.value.toFixed(sensor.decimals)}<small> ${escapeHtml(sensor.unit)}</small></div>
                <span class="tag ${tone}">${status}</span>
                <div class="gauge-trend trend-${trend[2]}" aria-label="Trend ${trend[1]}"><span aria-hidden="true">${trend[0]}</span> ${trend[1]}</div>
                <div class="sub">Ideal ${sensor.low}–${sensor.high}${sensor.unit ? ` ${escapeHtml(sensor.unit)}` : ""}</div>
                <div class="range-meta">Sample min <b>${minDay.toFixed(sensor.decimals)}</b> &middot; max <b>${maxDay.toFixed(sensor.decimals)}</b></div>
            </article></div>`;
        }).join("");
    }

    function sensorValues(sensor, count) {
        const span = sensor.max - sensor.min;
        return Array.from({ length: count }, (_, i) => Math.max(sensor.min, Math.min(sensor.max,
            sensor.value + Math.sin(i / Math.max(2, count / 5)) * span * .1 + Math.sin(i * 1.7) * span * .025)));
    }

    function renderChart() {
        const sensor = sensors.find((item) => item.name === selectedSensor) || sensors[0];
        if (!sensor) return;
        const count = selectedRange === 24 ? 24 : selectedRange === 168 ? 28 : 30;
        const width = 640, height = 260, left = 68, right = 18, top = 20, bottom = 34;
        const values = sensorValues(sensor, count), span = sensor.max - sensor.min;
        const x = (i) => left + i * (width - left - right) / (count - 1);
        const y = (value) => height - bottom - (value - sensor.min) / span * (height - top - bottom);
        const path = values.map((value, i) => `${i ? "L" : "M"}${x(i).toFixed(1)} ${y(value).toFixed(1)}`).join("");
        let grid = "";
        for (let i = 0; i < 5; i++) {
            const value = sensor.max - span * i / 4, yy = top + i * (height - top - bottom) / 4;
            grid += `<line x1="${left}" x2="${width-right}" y1="${yy}" y2="${yy}" stroke="var(--line)"/><text x="${left-8}" y="${yy+4}" text-anchor="end" font-size="11" fill="var(--mute)">${value.toFixed(sensor.decimals)}${sensor.unit ? ` ${escapeHtml(sensor.unit)}` : ""}</text>`;
        }
        const labels = customFrom && customTo ? [customFrom, "1/3", "2/3", customTo] : ["Start", selectedRange === 24 ? "6h" : "1/3", selectedRange === 24 ? "12h" : "2/3", "Now"];
        let ticks = "";
        labels.forEach((label, i) => { ticks += `<text x="${left+i*(width-left-right)/3}" y="${height-8}" text-anchor="middle" font-size="11" fill="var(--mute)">${escapeHtml(label)}</text>`; });
        const color = sensor.key === "humidity" ? "#3C8DAD" : sensor.key === "tds" ? "#8B6FC0" : sensor.key === "ph" ? "#D98E1F" : "#2F6B3A";
        const points = values.map((value, i) => `<circle cx="${x(i)}" cy="${y(value)}" r="3" fill="${color}"><title>${value.toFixed(sensor.decimals)} ${escapeHtml(sensor.unit)} at ${labels[Math.min(3, Math.floor(i*4/count))]}</title></circle>`).join("");
        $("#chart").innerHTML = `${grid}${ticks}<path d="${path}" fill="none" stroke="${color}" stroke-width="2.5"/>${points}`;
        $("#chart").setAttribute("aria-label", `${sensor.name} sample trend, ${customFrom && customTo ? `${customFrom} to ${customTo}` : (selectedRange === 24 ? "24 hours" : selectedRange === 168 ? "7 days" : "30 days")}`);
        $("#chart-legend").innerHTML = `<i style="background:${color}"></i>${escapeHtml(sensor.name)}${sensor.unit ? ` (${escapeHtml(sensor.unit)})` : ""}`;
        renderHistory(sensor, values);
    }

    const events = [
        { at: Date.now()-3*3600000, level: "w", device: "Greenhouse A", text: "Temperature 25.1 °C, above 24 °C", action: "Cooling fan switched on", status: "Active", unread: true },
        { at: Date.now()-5*3600000, level: "w", device: "Nutrient tank", text: "pH 6.8, above 6.5", action: "Alert sent to staff", status: "Active", unread: true },
        { at: Date.now()-8*3600000, level: "i", device: "Nutrient tank", text: "Nutrient tank refilled", action: "Logged by farm staff", status: "Acknowledged", unread: false },
        { at: Date.now()-14*3600000, level: "b", device: "Greenhouse A", text: "Humidity 84%, above 70%", action: "Cooling fan switched on", status: "Active", unread: true }
    ];
    const levels = { w: "Warning", b: "Critical", i: "Info" };
    function renderAlerts() {
        const filter = $("#alert-filter")?.value || "all";
        const filtered = events.filter((event) => filter === "all" || event.level === filter);
        $("#alerts").innerHTML = filtered.length ? filtered.map((event) => {
            const index = events.indexOf(event);
            const action = event.status === "Resolved" ? "Resolved" : `<button class="text-btn" data-alert-ack="${index}">Acknowledge</button><button class="text-btn" data-alert-resolve="${index}">Resolve</button>`;
            return `<tr><td>${new Date(event.at).toLocaleString([], { dateStyle:"medium", timeStyle:"short" })}</td><td class="sev ${event.level}">${levels[event.level]}</td><td>${escapeHtml(event.device)}</td><td>${escapeHtml(event.text)}</td><td><span class="alert-state ${event.status.toLowerCase()}">${event.status}</span></td><td>${action}</td></tr>`;
        }).join("") : `<tr><td colspan="6" class="empty-state">No alerts match this filter.</td></tr>`;
        const unread = events.filter((event) => event.unread).length;
        $("#unread-count").textContent = String(unread);
        $("#notification-bell").setAttribute("aria-label", `${unread} unread alerts`);
    }

    const actuators = [
        { name: "Cooling fan", mode: "auto", on: true, maxSeconds: 120, cooldownSeconds: 15, autoNote: "Running until temperature is 22 °C or lower" },
        { name: "Water pump", mode: "auto", on: false, maxSeconds: 60, cooldownSeconds: 30 }
    ];
    function renderActuators() {
        $("#act").innerHTML = actuators.map((item, index) => {
            const seconds = Math.max(0, Math.ceil(((item.cooldownUntil || 0) - Date.now()) / 1000));
            const feedback = commandFeedback.get(index) || (seconds ? `Cooldown ${seconds}s` : `Max manual run ${item.maxSeconds}s`);
            return `<div class="arow"><div class="actuator-heading"><b>${item.name}</b><span class="act-state ${item.on ? "is-on" : "is-off"}">${item.on ? "ON" : "OFF"}</span>
                <button class="sw" type="button" role="switch" aria-label="${item.name}" aria-checked="${item.on}" ${item.mode === "auto" || seconds ? "disabled" : ""} data-toggle="${index}"></button></div>
                <div class="actuator-details"><span class="sub">${escapeHtml(item.mode === "auto" ? (item.autoNote || "Running by sensor rules") : "Manual override")}</span>
                    <div class="seg" data-mode="${index}"><button type="button" class="${item.mode === "auto" ? "on" : ""}" data-set-mode="auto">Auto</button><button type="button" class="${item.mode === "manual" ? "on" : ""}" data-set-mode="manual">Manual</button></div></div>
                <small class="command-feedback" role="status">${escapeHtml(feedback)}${item.changedAt ? ` · Last changed by ${escapeHtml(item.changedBy)} at ${new Date(item.changedAt).toLocaleTimeString([], {hour:"2-digit",minute:"2-digit"})}` : ""}</small></div>`;
        }).join("");
    }

    function logEvent(message, action, level = "i") {
        events.unshift({ at: Date.now(), level, device: "Actuator", text: message, action, status: "Active", unread: true });
        renderAlerts();
    }
    function setActuator(index, on) {
        const item = actuators[index];
        if (item.mode !== "manual" || (item.cooldownUntil || 0) > Date.now()) return;
        item.on = on;
        item.changedAt = Date.now(); item.changedBy = "You";
        commandFeedback.set(index, "Command sent · relay acknowledged (demo)");
        logEvent(`${item.name} turned ${on ? "on" : "off"} in manual mode`, "Manual control");
        if (on) item.runTimeout = setTimeout(() => {
            item.on = false; item.cooldownUntil = Date.now() + item.cooldownSeconds * 1000;
            item.mode = "auto"; commandFeedback.set(index, "Maximum run time reached · returned to Auto (demo)");
            logEvent(`${item.name} reached its maximum run time and returned to Auto`, "Safety return"); renderActuators();
        }, item.maxSeconds * 1000);
        else { clearTimeout(item.runTimeout); item.cooldownUntil = Date.now() + item.cooldownSeconds * 1000; }
        renderActuators();
    }

    function renderHistory(sensor, values) {
        const rows = values.slice(-12).map((value, index) => `<tr><td>Sample ${index + 1}</td><td>${escapeHtml(sensor.name)}</td><td>${value.toFixed(sensor.decimals)} ${escapeHtml(sensor.unit)}</td><td>${sensor.low}–${sensor.high}</td></tr>`).join("");
        $("#history-rows").innerHTML = rows || `<tr><td colspan="4" class="empty-state">No sample history available.</td></tr>`;
    }

    function exportCsv() {
        const sensor = sensors.find((item) => item.name === selectedSensor) || sensors[0];
        if (!sensor) return;
        const values = sensorValues(sensor, selectedRange === 24 ? 24 : selectedRange === 168 ? 28 : 30);
        const lines = [["Sample", sensor.name, "Unit", "Ideal minimum", "Ideal maximum"], ...values.map((value, i) => [`Sample ${i + 1}`, value.toFixed(sensor.decimals), sensor.unit, sensor.low, sensor.high])];
        const csv = lines.map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(",")).join("\r\n");
        const link = document.createElement("a"); link.href = URL.createObjectURL(new Blob([csv], { type:"text/csv;charset=utf-8" }));
        link.download = `greenpulse-${sensor.key}-${selectedRange}h-sample.csv`; link.click(); URL.revokeObjectURL(link.href);
    }

    const demoUsers = [{ name:"Alex Rivera", email:"alex@example.test" }, { name:"Jamie Santos", email:"jamie@example.test" }];
    let editingUser = null;
    function renderUsers() {
        if (!$("#users")) return;
        $("#users").innerHTML = demoUsers.map((user, index) => `<tr><td>${escapeHtml(user.name)}</td><td>${escapeHtml(user.email)}</td><td>Farm Staff</td><td class="table-actions"><button class="text-btn" data-edit-user="${index}">Edit</button><button class="text-btn danger-btn" data-delete-user="${index}">Delete</button></td></tr>`).join("");
    }
    function openUserDialog(index = null) {
        editingUser = index; const user = index === null ? { name:"", email:"" } : demoUsers[index];
        $("#user-dialog-title").textContent = index === null ? "Add demo user" : "Edit demo user";
        $("#user-name").value = user.name; $("#user-email").value = user.email; $("#user-dialog").showModal();
    }

    function showView(view) {
        activeView = view;
        const titles = { dash:"Greenhouse A", act:"Actuator control", alerts:"Alert log", admin:"Admin management", profile:"Profile", settings:"Settings", devices:"Devices", reports:"Reports / History" };
        $("#title").textContent = titles[view] || "Greenhouse A";
        $$(".nav[data-v]").forEach((button) => {
            const active = button.dataset.v === view;
            button.classList.toggle("on", active);
            if (active) button.setAttribute("aria-current", "page"); else button.removeAttribute("aria-current");
        });
        const secondary = { admin:"admin-panel", profile:"profile-panel", settings:"settings-panel", devices:"devices-panel", reports:"reports-panel" };
        $$(".dashboard-part").forEach((part) => part.classList.toggle("hide", Boolean(secondary[view])));
        Object.entries(secondary).forEach(([key, id]) => { const panel = $(`#${id}`); if (panel) panel.classList.toggle("hide", view !== key); });
        const environment = $("#gauges").closest("section");
        const telemetry = $("#actuators").closest("section[aria-label]");
        const alerts = $("#alert-log");
        const chartColumn = $("#chart-column");
        const actuatorColumn = $("#actuator-column");
        $(".demo-note").classList.toggle("hide", view !== "dash");
        telemetry.classList.remove("actuators-only");
        chartColumn.classList.remove("hide");
        actuatorColumn.classList.remove("hide");
        if (["dash", "act", "alerts"].includes(view)) {
            environment.classList.toggle("hide", view !== "dash");
            telemetry.classList.toggle("hide", view === "alerts");
            alerts.classList.toggle("hide", view !== "alerts" && view !== "dash");
            chartColumn.classList.toggle("hide", view === "act");
            if (view === "act") telemetry.classList.add("actuators-only");
        }
        if (secondary[view] && view === "admin") renderUsers();
        $("#sidebar").classList.remove("open"); $("#mobile-menu").setAttribute("aria-expanded", "false");
    }

    $("#act").addEventListener("click", (event) => {
        const toggle = event.target.closest("[data-toggle]"), mode = event.target.closest("[data-set-mode]");
        if (toggle) setActuator(Number(toggle.dataset.toggle), !actuators[Number(toggle.dataset.toggle)].on);
        if (mode) {
            const index = Number(mode.parentNode.dataset.mode), item = actuators[index];
            if ((item.cooldownUntil || 0) > Date.now()) return;
            clearTimeout(item.runTimeout); item.mode = mode.dataset.setMode; item.changedAt = Date.now(); item.changedBy = "You";
            commandFeedback.set(index, `Command sent · ${item.mode} mode selected (demo)`);
            if (item.mode === "manual" && item.on) {
                item.runTimeout = setTimeout(() => {
                    item.on = false; item.mode = "auto"; item.cooldownUntil = Date.now() + item.cooldownSeconds * 1000;
                    commandFeedback.set(index, "Maximum run time reached · returned to Auto (demo)");
                    logEvent(`${item.name} reached its maximum run time and returned to Auto`, "Safety return"); renderActuators();
                }, item.maxSeconds * 1000);
            }
            if (item.mode === "auto" && index === 1 && item.on) {
                item.on = false; item.cooldownUntil = Date.now() + item.cooldownSeconds * 1000;
                commandFeedback.set(index, "Pump stopped · cooldown started (demo)");
            }
            logEvent(`${item.name} set to ${item.mode} mode`, "Mode change"); renderActuators();
        }
    });
    $("#sensor-select").addEventListener("click", (event) => {
        const button = event.target.closest("[data-sensor]"); if (!button) return;
        selectedSensor = button.dataset.sensor; $$("#sensor-select button").forEach((item) => item.classList.toggle("on", item === button)); renderChart();
    });
    $("#rng").addEventListener("click", (event) => {
        const button = event.target.closest("[data-h]"); if (!button) return;
        $("#chart-date-from").value = ""; $("#chart-date-to").value = ""; customFrom = ""; customTo = "";
        selectedRange = Number(button.dataset.h); $$("#rng button").forEach((item) => item.classList.toggle("on", item === button)); renderChart();
    });
    function updateCustomRange() {
        customFrom = $("#chart-date-from").value; customTo = $("#chart-date-to").value;
        $("#chart-date-to").min = customFrom;
        $("#chart-date-from").max = customTo || "";
        if (!customFrom || !customTo || customTo < customFrom) return;
        const days = Math.max(1, Math.ceil((new Date(customTo) - new Date(customFrom)) / 86400000) + 1);
        selectedRange = days <= 1 ? 24 : days <= 7 ? 168 : 720;
        renderChart();
    }
    $("#chart-date-from").addEventListener("change", updateCustomRange);
    $("#chart-date-to").addEventListener("change", updateCustomRange);
    $("#export-chart").addEventListener("click", exportCsv);
    $("#export-history").addEventListener("click", exportCsv);
    $("#print-report").addEventListener("click", () => window.print());
    $("#alert-filter").addEventListener("change", renderAlerts);
    $("#alerts").addEventListener("click", (event) => {
        const ack = event.target.closest("[data-alert-ack]"), resolve = event.target.closest("[data-alert-resolve]");
        const index = Number((ack || resolve)?.dataset.alertAck ?? (ack || resolve)?.dataset.alertResolve);
        if (!Number.isInteger(index) || !events[index]) return;
        events[index].status = resolve ? "Resolved" : "Acknowledged"; events[index].unread = false; renderAlerts();
    });
    $$(".nav[data-v]").forEach((button) => button.addEventListener("click", () => showView(button.dataset.v)));
    $(".text-btn[data-v='alerts']").addEventListener("click", () => showView("alerts"));
    $("#mobile-menu").addEventListener("click", () => {
        const open = $("#sidebar").classList.toggle("open"); $("#mobile-menu").setAttribute("aria-expanded", String(open));
    });
    $("#notification-bell").addEventListener("click", () => {
        const unread = events.filter((item) => item.unread);
        $("#notification-popover").innerHTML = unread.length ? `<b>${unread.length} unread alerts</b><br>${unread.slice(0,3).map((item) => escapeHtml(item.text)).join("<br>")}` : "No unread alerts.";
        $("#notification-popover").classList.toggle("hide");
    });
    $("#add-user")?.addEventListener("click", () => openUserDialog());
    $("#cancel-user")?.addEventListener("click", () => $("#user-dialog").close());
    $("#user-form")?.addEventListener("submit", (event) => {
        event.preventDefault(); if (!event.currentTarget.reportValidity()) return;
        const user = { name:$("#user-name").value.trim(), email:$("#user-email").value.trim() };
        if (editingUser === null) demoUsers.push(user); else demoUsers[editingUser] = user;
        renderUsers(); $("#user-dialog").close();
    });
    $("#users")?.addEventListener("click", (event) => {
        const edit = event.target.closest("[data-edit-user]"), remove = event.target.closest("[data-delete-user]");
        if (edit) openUserDialog(Number(edit.dataset.editUser));
        if (remove) { demoUsers.splice(Number(remove.dataset.deleteUser), 1); renderUsers(); }
    });

    renderGauges(); renderChart(); renderActuators(); renderAlerts(); renderUsers(); showView("dash");
    const updatedAt = Date.now();
    setInterval(() => {
        const elapsed = Math.floor((Date.now() - updatedAt) / 1000);
        $("#upd").textContent = `Sample readings · updated ${elapsed < 5 ? "just now" : `${elapsed}s ago`}`;
        let actuatorChanged = false;
        actuators.forEach((item) => {
            if (item.cooldownUntil && item.cooldownUntil <= Date.now()) { item.cooldownUntil = 0; actuatorChanged = true; }
            else if (item.cooldownUntil > Date.now()) actuatorChanged = true;
        });
        if (actuatorChanged) renderActuators();
    }, 1000);
})();
