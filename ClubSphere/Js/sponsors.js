const sponsorForm = document.getElementById("sponsorForm");
const sponsorContainer = document.getElementById("sponsorContainer");
const message = document.getElementById("message");
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[c]));

function loadSponsors()
{
    fetch("../Controls/sponsorControls.php")
    .then(response => response.json())
    .then(data => {

        sponsorContainer.innerHTML = "";

        if(!data.success)
        {
            sponsorContainer.innerHTML = "<p>Access denied.</p>";
            return;
        }

        if(data.sponsors.length === 0)
        {
            sponsorContainer.innerHTML = "<p>No sponsors found.</p>";
            return;
        }

        data.sponsors.forEach(sponsor => {

            const card = document.createElement("div");

            card.className = "sponsor-card";

            card.innerHTML = `
                <h2>${esc(sponsor.sponsor_name)}</h2>
                <p>Contact: ${esc(sponsor.contact_info ?? "N/A")}</p>
                <p>Amount: ${esc(sponsor.sponsorship_amount)}</p>
                <p>Contract: ${esc(sponsor.contract_start ?? "N/A")} to ${esc(sponsor.contract_end ?? "N/A")}</p>
                <p class="info">${esc(sponsor.notes)}</p>
                <button class="delete-btn" onclick="deleteSponsor(${sponsor.sponsor_id})">
                    Delete
                </button>
            `;

            sponsorContainer.appendChild(card);
        });
    })
    .catch(() => {
        sponsorContainer.innerHTML = "<p>Failed to load sponsors.</p>";
    });
}

sponsorForm.addEventListener("submit", function(e)
{
    e.preventDefault();

    const formData = new FormData();

    formData.append("action", "add");
    formData.append("name", document.getElementById("name").value);
    formData.append("contact", document.getElementById("contact").value);
    formData.append("amount", document.getElementById("amount").value);
    formData.append("start", document.getElementById("start").value);
    formData.append("end", document.getElementById("end").value);
    formData.append("notes", document.getElementById("notes").value);

    fetch("../Controls/sponsorControls.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        message.textContent = data.message;

        if(data.success)
        {
            sponsorForm.reset();
            loadSponsors();
        }
    });
});

function deleteSponsor(id)
{
    if(!confirm("Delete this sponsor?"))
    {
        return;
    }

    const formData = new FormData();

    formData.append("action", "delete");
    formData.append("id", id);

    fetch("../Controls/sponsorControls.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        alert(data.message);

        if(data.success)
        {
            loadSponsors();
        }
    });
}

loadSponsors();