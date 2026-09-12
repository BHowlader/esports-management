document.addEventListener("DOMContentLoaded", function () {

    var forms = document.querySelectorAll(".verify-form");

    forms.forEach(function (form) {

        var chosenAction = null;

        form.querySelectorAll("button[type=submit]").forEach(function (button) {

            button.addEventListener("click", function () {
                chosenAction = button.name;
            });
        });


        form.addEventListener("submit", function (event) {

            var remarks = form.querySelector("textarea[name=remarks]");

            if (chosenAction === "reject") {

                if (remarks.value.trim() === "") {
                    event.preventDefault();
                    remarks.style.borderColor = "#ed7777";
                    remarks.focus();
                    alert("Please write why you are rejecting this result.\n\nThe team needs to know what to fix.");
                    return;
                }
            }

            if (chosenAction === "verify") {

                var ok = confirm("Verify this result?\n\n" +
                    "This finalizes the match, sets the winner, and rebuilds the leaderboard. " +
                    "It cannot be undone from this screen.");

                if (!ok) {
                    event.preventDefault();
                }
            }
        });
    });


    var okBanner = document.querySelector(".msg.ok");

    if (okBanner) {
        setTimeout(function () {
            okBanner.hidden = true;
        }, 6000);
    }
});
