const usersContainer = document.getElementById("usersContainer");
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[c]));

function loadUsers()
{
    fetch("../Controls/removeUserControls.php")
    .then(response => response.json())
    .then(data => {

        usersContainer.innerHTML = "";

        if(!data.success)
        {
            usersContainer.innerHTML = "<p>Access denied.</p>";
            return;
        }

        if(data.users.length === 0)
        {
            usersContainer.innerHTML = "<p>No inactive or banned users.</p>";
            return;
        }

        data.users.forEach(user => {

            const card = document.createElement("div");

            card.className = "user-card";

            card.innerHTML = `
                <h3>${esc(user.name)}</h3>
                <p>University ID: ${esc(user.uni_id)}</p>
                <p>Email: ${esc(user.email_id)}</p>
                <p>Role: ${esc(user.role)}</p>
                <p>Status: ${esc(user.status)}</p>
                <button class="delete-btn" onclick="removeUser(${user.u_id})">
                    Remove User
                </button>
            `;

            usersContainer.appendChild(card);
        });
    })
    .catch(() => {
        usersContainer.innerHTML = "<p>Failed to load users.</p>";
    });
}

function removeUser(u_id)
{
    if(!confirm("Are you sure you want to remove this user?"))
    {
        return;
    }

    const formData = new FormData();

    formData.append("u_id", u_id);

    fetch("../Controls/removeUserControls.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        alert(data.message);

        if(data.success)
        {
            loadUsers();
        }
    });
}

loadUsers();