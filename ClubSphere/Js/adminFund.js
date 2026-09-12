document.addEventListener("DOMContentLoaded", function () {

    var typeSelect = document.getElementById("source_type");
    var sponsorField = document.getElementById("sponsorField");
    var sponsorInput = document.getElementById("sponsor_id");
    var sourceName = document.getElementById("source_name");
    var amount = document.getElementById("amount");
    var form = document.getElementById("incomeForm");

    function syncSponsorField() {

        var isSponsorship = (typeSelect.value === "Sponsorship");

        sponsorField.hidden = !isSponsorship;
        sponsorInput.required = isSponsorship;

        if (!isSponsorship) {
            sponsorInput.value = "";
        }
    }


    if (typeSelect) {

        typeSelect.addEventListener("change", syncSponsorField);

        syncSponsorField();
    }

    if (sponsorInput) {

        sponsorInput.addEventListener("change", function () {

            if (sourceName.value.trim() === "" && sponsorInput.selectedIndex > 0) {
                sourceName.value = sponsorInput.options[sponsorInput.selectedIndex].text.trim();
            }
        });
    }

    if (amount) {

        amount.addEventListener("blur", function () {

            var value = parseFloat(amount.value);

            if (!isNaN(value) && value > 500000) {
                amount.style.borderColor = "#e0a33c";
                amount.title = "That is a large amount - please double check it.";
            } else {
                amount.style.borderColor = "";
                amount.title = "";
            }
        });
    }


    if (form) {

        form.addEventListener("submit", function (event) {

            var value = parseFloat(amount.value);

            if (isNaN(value) || value <= 0) {
                event.preventDefault();
                alert("Please enter an amount greater than zero.");
                amount.focus();
                return;
            }

            var ok = confirm("Record " + value.toFixed(2) + " TK as club income?");

            if (!ok) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll(".delete-form").forEach(function (deleteForm) {

        deleteForm.addEventListener("submit", function (event) {

            var ok = confirm("Delete this income record permanently?\n\nThe club total will change.");

            if (!ok) {
                event.preventDefault();
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
