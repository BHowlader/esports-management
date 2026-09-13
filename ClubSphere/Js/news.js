const newsContainer = document.getElementById("newsContainer");
const newsForm = document.getElementById("newsForm");
const message = document.getElementById("message");
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[c]));

function loadNews()
{
    fetch("../Controls/newsControls.php")
    .then(response => response.json())
    .then(data => {

        newsContainer.innerHTML = "";

        if(!data.success)
        {
            newsContainer.innerHTML = "<p>Failed to load news.</p>";
            return;
        }

        if(data.news.length === 0)
        {
            newsContainer.innerHTML = "<p>No news available.</p>";
            return;
        }

        data.news.forEach(item => {

            const card = document.createElement("div");

            card.className = "news-card";

            card.innerHTML = `
                <h2>${esc(item.title)}</h2>
                <p>${esc(item.content)}</p>
                <p class="news-info">
                    Posted by ${esc(item.name)} | ${esc(item.created_at)}
                </p>
                ${newsForm ? `<button class="delete-btn" onclick="deleteNews(${item.id})">
                    Delete
                </button>` : ""}
            `;

            newsContainer.appendChild(card);
        });
    })
    .catch(error => {
        newsContainer.innerHTML = "<p>Failed to load news.</p>";
    });
}

if(newsForm)
{
    newsForm.addEventListener("submit", function(e)
    {
        e.preventDefault();

        const title = document.getElementById("title").value;
        const content = document.getElementById("content").value;

        const formData = new FormData();

        formData.append("action", "add");
        formData.append("title", title);
        formData.append("content", content);

        fetch("../Controls/newsControls.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            message.textContent = data.message;

            if(data.success)
            {
                newsForm.reset();
                loadNews();
            }
        });
    });
}

function deleteNews(id)
{
    const formData = new FormData();

    formData.append("action", "delete");
    formData.append("id", id);

    fetch("../Controls/newsControls.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        if(data.success)
        {
            loadNews();
        }
        else
        {
            alert(data.message);
        }
    });
}

loadNews();