const backupBtn = document.getElementById("backupBtn");
const message = document.getElementById("message");

backupBtn.addEventListener("click", function()
{
    message.textContent = "Creating backup...";

    fetch("../Controls/backupControls.php")
    .then(response => response.json())
    .then(data => {

        message.textContent = data.message;

        if(data.success)
        {
            message.textContent += " File: " + data.file;
        }
    })
    .catch(() => {
        message.textContent = "Backup failed";
    });
});