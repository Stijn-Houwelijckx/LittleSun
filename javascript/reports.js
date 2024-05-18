// Update the JavaScript code to send the selected user's ID to the report.php page
const userSelector = document.getElementById("userSelector");
const yearSelector = document.getElementById("yearSelector");
const monthSelector = document.getElementById("monthSelector");
const reportBtn = document.querySelector(".report-btn");

reportBtn.addEventListener("click", function (e) {
  const selectedUserId = userSelector.value;
  const selectedYear = yearSelector.value;
  const selectedMonth = monthSelector.value;

  $locationString = "";

  if (selectedUserId) {
    $locationString += `&userId=${selectedUserId}`;
  }

  if (selectedYear) {
    $locationString += `&year=${selectedYear}`;
  }

  if (selectedMonth) {
    $locationString += `&month=${selectedMonth}`;
  }

  // Remove the first "&" from the string
  $locationString = $locationString.substring(1);

  window.location.href = `report.php?${$locationString}`;
});

// Fetch months by year AJAX

// const yearSelector = document.getElementById("yearSelector");
// const monthSelector = document.getElementById("monthSelector");

yearSelector.addEventListener("change", function () {
  var year = this.value;
  var formData = new FormData();
  formData.append("year", year);
  console.log("year: " + year);

  fetch("../ajax/fetchMonthsByYear.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.status === "error") {
        console.log("Error: ", result);
        return;
      } else {
        console.log("Success: ", result);

        monthSelector.innerHTML = "";

        var option = document.createElement("option");
        option.value = "";
        option.text = "--- select month ---";
        monthSelector.appendChild(option);

        result.months.forEach((month) => {
          var option = document.createElement("option");
          option.value = month.month_number;
          option.text = month.month_name;
          monthSelector.appendChild(option);
        });
      }
    })
    .catch((error) => {
      console.error("Error retreiving the months:", error);
    });
});
