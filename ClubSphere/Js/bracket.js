// FR10 - small guards around the bracket buttons

const generateForm = document.getElementById("generateForm");

if (generateForm) {
    generateForm.addEventListener("submit", function (e) {

        const teams = parseInt(generateForm.getAttribute("data-teams"));
        const already = generateForm.getAttribute("data-exists");

        if (teams < 2) {
            alert("At least 2 registered teams are needed to generate a bracket.");
            e.preventDefault();
            return;
        }

        if (already === "1") {
            const sure = confirm("A bracket already exists. Generating it again will erase every recorded result. Continue?");

            if (!sure) {
                e.preventDefault();
            }
        }
    });
}


// a winner has to be chosen before the round can move forward

const winnerForms = document.getElementsByClassName("winner-form");

for (let i = 0; i < winnerForms.length; i++) {

    winnerForms[i].addEventListener("submit", function (e) {

        const select = e.currentTarget.getElementsByTagName("select")[0];

        if (select.value === "") {
            alert("Select the winning team first.");
            e.preventDefault();
        }
    });
}
