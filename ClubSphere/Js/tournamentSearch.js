// Search box on the tournament list (the "Search events..." field in the Figma).
// It hides the cards that do not match what is typed.

const searchBox = document.getElementById("searchBox");

if (searchBox) {
    searchBox.addEventListener("keyup", searchTournaments);
}

function searchTournaments() {
    const text = searchBox.value.toLowerCase();

    const cards = document.getElementsByClassName("tour-card");

    for (let i = 0; i < cards.length; i++) {
        const label = cards[i].getAttribute("data-search").toLowerCase();

        if (label.indexOf(text) > -1) {
            cards[i].style.display = "flex";
        } else {
            cards[i].style.display = "none";
        }
    }
}
