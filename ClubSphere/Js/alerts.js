const alertsContainer = document.getElementById("alertsContainer");
const matchesContainer = document.getElementById("matchesContainer");
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[c]));

function loadAlerts()
{
    fetch("../Controls/alertControls.php")
    .then(response => response.json())
    .then(data => {

        alertsContainer.innerHTML = "";
        matchesContainer.innerHTML = "";

        if(!data.success)
        {
            alertsContainer.innerHTML = "<p>Failed to load alerts.</p>";
            return;
        }

        if(data.alerts.length === 0)
        {
            alertsContainer.innerHTML = "<p>No alerts available.</p>";
        }
        else
        {
            data.alerts.forEach(alert => {

                const card = document.createElement("div");

                card.className = "alert-card";

                if(alert.is_read == 0)
                {
                    card.classList.add("unread");
                }

                card.innerHTML = `
                    <h3>${esc(alert.title)}</h3>
                    <p>${esc(alert.message)}</p>
                    <p class="info">${esc(alert.created_at)}</p>
                    ${alert.is_read == 0 ? `<button onclick="markRead(${alert.alert_id})">Mark as Read</button>` : ""}
                `;

                alertsContainer.appendChild(card);
            });
        }

        if(data.matches.length === 0)
        {
            matchesContainer.innerHTML = "<p>No upcoming matches.</p>";
        }
        else
        {
            data.matches.forEach(match => {

                const card = document.createElement("div");

                card.className = "match-card";

                card.innerHTML = `
                    <h3>${esc(match.tournament)}</h3>
                    <p>${esc(match.team1 ?? "TBD")} vs ${esc(match.team2 ?? "TBD")}</p>
                    <p class="info">Time: ${esc(match.match_time)}</p>
                    <p class="info">Venue: ${esc(match.venue ?? "Not set")}</p>
                `;

                matchesContainer.appendChild(card);
            });
        }
    })
    .catch(() => {
        alertsContainer.innerHTML = "<p>Failed to load alerts.</p>";
    });
}

function markRead(alertId)
{
    const formData = new FormData();

    formData.append("action", "read");
    formData.append("alert_id", alertId);

    fetch("../Controls/alertControls.php", {
        method: "POST",
        body: formData
    })
    .then(() => {
        loadAlerts();
    });
}

loadAlerts();

setInterval(loadAlerts, 10000);