document.addEventListener("DOMContentLoaded", function () {

    var cancelForms = document.querySelectorAll(".cancel-form");

    cancelForms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            var ok = confirm("Cancel this match?\n\nTeams will no longer be able to submit a result for it.");

            if (!ok) {
                event.preventDefault();
            }
        });
    });

    var timeInputs = document.querySelectorAll("input[name=match_time]");

    timeInputs.forEach(function (input) {

        input.addEventListener("change", function () {

            var chosen = new Date(input.value);
            var now = new Date();

            if (input.value !== "" && chosen < now) {
                input.style.borderColor = "#e0a33c";
                input.title = "That time is in the past.";
            } else {
                input.style.borderColor = "";
                input.title = "";
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
