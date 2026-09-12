/* =====================================================================
   FR14 - client side sorting for the standings table
   Bibek Howlader (23-54606-3)

   The server already sends the table in the official order (points,
   then round difference, then rounds won). This only lets a viewer
   re-sort by any column to explore the data - it never changes a
   number, and reloading the page restores the official order.
   ===================================================================== */

document.addEventListener("DOMContentLoaded", function () {

    var table = document.getElementById("standingsTable");

    if (!table) {
        return;
    }

    var headers = table.querySelectorAll("thead th");
    var tbody = table.querySelector("tbody");
    var ascending = false;

    headers.forEach(function (header, columnIndex) {

        /* the "#" rank column is not sortable */
        if (columnIndex === 0) {
            return;
        }

        header.style.cursor = "pointer";
        header.title = "Click to sort";

        header.addEventListener("click", function () {

            var rows = Array.prototype.slice.call(tbody.querySelectorAll("tr"));

            ascending = !ascending;

            rows.sort(function (rowA, rowB) {

                var a = rowA.cells[columnIndex].textContent.trim();
                var b = rowB.cells[columnIndex].textContent.trim();

                var numA = parseFloat(a.replace("+", ""));
                var numB = parseFloat(b.replace("+", ""));

                /* numbers compare as numbers, text compares as text */
                if (!isNaN(numA) && !isNaN(numB)) {
                    return ascending ? numA - numB : numB - numA;
                }

                return ascending ? a.localeCompare(b) : b.localeCompare(a);
            });

            /* re-attaching a row MOVES it, so the table is rebuilt in the
               new order without any row being duplicated */
            rows.forEach(function (row) {
                tbody.appendChild(row);
            });

            /* renumber the rank column to match what is on screen */
            tbody.querySelectorAll("tr").forEach(function (row, index) {
                row.cells[0].textContent = index + 1;
            });
        });
    });
});
