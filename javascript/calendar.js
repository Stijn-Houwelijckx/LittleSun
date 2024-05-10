document.addEventListener("DOMContentLoaded", function () {
  const prevDayBtn = document.getElementById("prevDay");
  const nextDayBtn = document.getElementById("nextDay");
  const currentDateElement = document.getElementById("currentDate");
  const currentDayElement = document.getElementById("currentDay");
  const currentDateInput = document.getElementById("currentDateInput");

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
    const newDayElement = document.querySelector(".day p");
    newDayElement.textContent = dayNumber < 10 ? "0" + dayNumber : dayNumber;
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
        p.textContent = `${item.start_time} - ${item.end_time} : ${item.task}`;

        dayContainer.appendChild(p);
      });
    } else {
      const p = document.createElement("p");
      p.textContent = "No calendar items for this day.";
      dayContainer.appendChild(p);
    }
  }
});
