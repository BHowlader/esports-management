document.addEventListener("DOMContentLoaded", function () {

    var table = document.getElementById("standingsTable");

    if (!table) {
        return;
    }

    var headers = table.querySelectorAll("thead th");
    var tbody = table.querySelector("tbody");
    var ascending = false;

    headers.forEach(function (header, columnIndex) {

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

                if (!isNaN(numA) && !isNaN(numB)) {
                    return ascending ? numA - numB : numB - numA;
                }

                return ascending ? a.localeCompare(b) : b.localeCompare(a);
            });

            rows.forEach(function (row) {
                tbody.appendChild(row);
            });

            tbody.querySelectorAll("tr").forEach(function (row, index) {
                row.cells[0].textContent = index + 1;
            });
        });
    });
});
