/* =====================================================================
   FR12 - helpers for the result submission form
   Bibek Howlader (23-54606-3)

   The size and type checks below are repeated on the server in
   Controls/resultControls.php. These exist only so the member gets an
   instant answer instead of uploading 5 MB and then being told no.
   ===================================================================== */

document.addEventListener("DOMContentLoaded", function () {

    var matchSelect = document.getElementById("match_id");
    var label1 = document.getElementById("label1");
    var label2 = document.getElementById("label2");
    var fileInput = document.getElementById("screenshot");
    var preview = document.getElementById("preview");
    var fileNote = document.getElementById("fileNote");
    var form = document.getElementById("resultForm");

    var MAX_BYTES = 2 * 1024 * 1024;
    var ALLOWED = ["image/jpeg", "image/png", "image/webp"];


    /* rename "Score - team 1" to the real team names as soon as a match
       is picked, so nobody enters the scores the wrong way round */
    if (matchSelect) {

        matchSelect.addEventListener("change", function () {

            var option = matchSelect.options[matchSelect.selectedIndex];

            if (!option || option.value === "") {
                label1.textContent = "Score - team 1";
                label2.textContent = "Score - team 2";
                return;
            }

            label1.textContent = "Score - " + option.dataset.team1;
            label2.textContent = "Score - " + option.dataset.team2;
        });
    }


    /* preview the screenshot and check it before uploading */
    if (fileInput) {

        fileInput.addEventListener("change", function () {

            preview.hidden = true;
            fileNote.className = "hint";

            var file = fileInput.files[0];

            if (!file) {
                return;
            }

            if (ALLOWED.indexOf(file.type) === -1) {
                fileNote.textContent = "Only JPG, PNG or WEBP images are accepted.";
                fileNote.className = "hint bad";
                fileInput.value = "";
                return;
            }

            if (file.size > MAX_BYTES) {
                fileNote.textContent = "That file is " +
                    (file.size / 1024 / 1024).toFixed(1) + " MB. The limit is 2 MB.";
                fileNote.className = "hint bad";
                fileInput.value = "";
                return;
            }

            fileNote.textContent = file.name + " (" + (file.size / 1024).toFixed(0) + " KB)";

            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    }


    /* a result goes to a moderator and cannot be edited afterwards, so
       confirm the scores are the right way round */
    if (form) {

        form.addEventListener("submit", function (event) {

            var s1 = document.getElementById("score_team1").value;
            var s2 = document.getElementById("score_team2").value;

            var t1 = label1.textContent.replace("Score - ", "");
            var t2 = label2.textContent.replace("Score - ", "");

            var ok = confirm("Submit " + t1 + " " + s1 + " - " + s2 + " " + t2 +
                "?\n\nA moderator will review this and it cannot be edited once sent.");

            if (!ok) {
                event.preventDefault();
            }
        });
    }
});
