/* =====================================================================
   FR11 - helpers for the match scheduling screen
   Bibek Howlader (23-54606-3)

   Everything here is convenience only. The real rules - clash checking,
   "completed matches are locked", date validity - are enforced AGAIN in
   PHP, because JavaScript can be switched off or bypassed entirely.
   ===================================================================== */

document.addEventListener("DOMContentLoaded", function () {

    /* cancelling a match is destructive, so ask first */
    var cancelForms = document.querySelectorAll(".cancel-form");

    cancelForms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            var ok = confirm("Cancel this match?\n\nTeams will no longer be able to submit a result for it.");

            if (!ok) {
                event.preventDefault();
            }
        });
    });


    /* warn about a time in the past before the trip to the server */
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


    /* hide the green banner after a few seconds so the moderator is not
       left staring at a stale message */
    var okBanner = document.querySelector(".msg.ok");

    if (okBanner) {
        setTimeout(function () {
            okBanner.hidden = true;
        }, 6000);
    }
});
