const clockInButton = document.getElementById("clockInButton");
const clockOutButton = document.getElementById("clockOutButton");

const timeTrackerTitle = document.getElementById("time-tracker-title");
const timeTrackerInfo = document.getElementById("time-tracker-info");

// Clock in AJAX
clockInButton.addEventListener("click", function () {
  console.log("clocked in");

  fetch("ajax/clockIn.php", {
    method: "POST",
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.status === "error") {
        console.log("Error: ", result);
        return;
      } else {
        console.log("Success: ", result);

        // Update the time tracker info
        timeTrackerTitle.innerHTML = "Clock Out";

        // Clear the time tracker info
        timeTrackerInfo.innerHTML = "";

        // Create p element
        const p = document.createElement("p");
        p.innerHTML = "You clocked in at: " + result.clockInTime;
        timeTrackerInfo.appendChild(p);

        // Show the clock out button
        clockOutButton.style.display = "block";

        // Hide the clock in button
        clockInButton.style.display = "none";

        // Add a div with class "circle" in the parent from timeTrackerTitle
        const circle = document.createElement("div");
        circle.classList.add("circle");
        timeTrackerTitle.parentNode.appendChild(circle);
      }
    })
    .catch((error) => {
      console.error("Error retreiving the users:", error);
    });
});

// Clock out AJAX
clockOutButton.addEventListener("click", function () {
  console.log("clocked out");

  fetch("ajax/clockOut.php", {
    method: "POST",
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.status === "error") {
        console.log("Error: ", result);
        return;
      } else {
        console.log("Success: ", result);

        // Update the time tracker info
        timeTrackerTitle.innerHTML = "Clock In";

        // Create p element
        const p = document.createElement("p");
        p.innerHTML = "You clocked out at: " + result.clockOutTime;
        timeTrackerInfo.appendChild(p);

        // Show worked time
        const workedTime = document.createElement("p");
        workedTime.innerHTML = "You worked for: " + result.workedTime;
        timeTrackerInfo.appendChild(workedTime);

        // Show the total hours worked
        const totalTime = document.createElement("p");
        totalTime.innerHTML =
          "Total worked time: " + result.fullworktime + " today";
        timeTrackerInfo.appendChild(totalTime);

        // Show overtime
        const overtime = document.createElement("p");
        overtime.innerHTML = "Overtime: " + result.overtime + " hours";
        timeTrackerInfo.appendChild(overtime);

        // Hide the clock out button
        clockOutButton.style.display = "none";

        // Show the clock in button
        clockInButton.style.display = "block";

        // Remove the circle
        const circle = document.querySelector(".circle");
        circle.remove();
      }
    })
    .catch((error) => {
      console.error("Error retreiving the users:", error);
    });
});
