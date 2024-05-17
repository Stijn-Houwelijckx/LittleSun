import { getDaysInMonth } from './utils'; // Import the getDaysInMonth function
import { groupedCalendarItems } from './data'; // Import the groupedCalendarItems variable
  
document.addEventListener("DOMContentLoaded", function () {
    const prevDayBtn = document.getElementById("prevDay");
    const nextDayBtn = document.getElementById("nextDay");
    const currentDateElement = document.getElementById("currentDate");
    const currentDayElement = document.getElementById("currentDay");
    const currentDateInput = document.querySelector(".currentDateInput");

    let currentDate = new Date(currentDateInput.value);

    prevDayBtn.addEventListener("click", function () {
        currentDate.setDate(currentDate.getDate() - 1);
        updateDay(currentDate);
    });

    nextDayBtn.addEventListener("click", function () {
        currentDate.setDate(currentDate.getDate() + 1);
        updateDay(currentDate);
    });

    function updateDay(date) {
        const formattedDate = date.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
        const formattedDay = date.toLocaleDateString("en-GB", { weekday: "long" });

        currentDateElement.textContent = formattedDate;
        currentDayElement.textContent = formattedDay;

        // Bijwerken van de hele 'day' div
        const dayNumber = date.getDate();
        const dayKey = date.toISOString().split("T")[0]; // Converteer naar 'YYYY-MM-DD' formaat
        const dayContainer = document.getElementById("dayItems");
        // Verwijder bestaande items
        while (dayContainer.firstChild) {
            dayContainer.removeChild(dayContainer.firstChild);
        }

        // Voeg nieuwe items toe voor de geselecteerde dag
        if (
            groupedCalendarItems[dayKey] &&
            groupedCalendarItems[dayKey].length > 0
        ) {
            groupedCalendarItems[dayKey].forEach((item) => {
                const userId = item.user_id; // Gebruik de gebruikers-ID om de kleur te bepalen
                const red = (userId * 70) % 256;
                const green = (userId * 120) % 256;
                const blue = (userId * 170) % 256;
                const itemColor = `rgb(${red}, ${green}, ${blue})`;

                const p = document.createElement("p");
                p.className = "calendarItem";
                p.style.backgroundColor = itemColor;
                p.textContent = `${item.start_time.slice(
                    0,
                    -3
                )} - ${item.end_time.slice(0, -3)} : ${item.task}`;

                dayContainer.appendChild(p);
            });
        } else {
            const p = document.createElement("p");
            p.textContent = "No calendar items for this day.";
            dayContainer.appendChild(p);
        }
    }
});

// Fetch users by availability AJAX
eventDatePicker.addEventListener("change", function () {
    var date = this.value;
    var formData = new FormData();
    formData.append("date", date);
    console.log("Date: " + date);

    fetch("../ajax/fetchUsersByAvailability.php", {
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

                // Enable the user selector
                userSelector.disabled = false;

                userSelector.innerHTML = "";

                var option = document.createElement("option");
                option.value = "";
                option.text = "--- select user ---";
                userSelector.appendChild(option);

                result.employees.forEach((employee) => {
                    var option = document.createElement("option");
                    option.value = employee.id;
                    option.text = employee.firstname + " " + employee.lastname;
                    userSelector.appendChild(option);
                });
            }
        })
        .catch((error) => {
            console.error("Error retreiving the users:", error);
        });
});

// Fetch tasks by user AJAX
userSelector.addEventListener("change", function () {
    var userId = this.value;
    var formData = new FormData();
    formData.append("userId", userId);
    console.log("userId: " + userId);

    // Fetch tasks by user
    fetch("../ajax/fetchTasksByUser.php", {
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

                // Enable the task selector
                taskSelector.disabled = false;

                taskSelector.innerHTML = "";

                var option = document.createElement("option");
                option.value = "";
                option.text = "--- select task ---";
                taskSelector.appendChild(option);

                result.tasks.forEach((task) => {
                    var option = document.createElement("option");
                    option.value = task.id;
                    option.text = task.name;
                    taskSelector.appendChild(option);
                });
            }
        })
        .catch((error) => {
            console.error("Error retrieving the tasks:", error);
        });
});

// Validate event form submission
eventForm.addEventListener("submit", function (event) {
    const eventDatePicker = document.getElementById("eventDatePicker");
    const taskSelector = document.getElementById("taskSelector");
    const selectedTimeslots = document.querySelectorAll('input[name="timeslots"]:checked');

    if (!eventDatePicker.value || !taskSelector.value || selectedTimeslots.length === 0) {
        event.preventDefault();
        alert("You didn't fill out all the fields.");
    }
});

