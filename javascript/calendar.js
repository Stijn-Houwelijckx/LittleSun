// dailyview
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

// weeklyview
document.addEventListener("DOMContentLoaded", function () {
  const prevWeekBtn = document.querySelector("#prevWeekButton");
  const nextWeekBtn = document.getElementById("nextWeek");
  const currentWeekElement = document.getElementById("currentWeek");

  let currentWeekStartDate = getCurrentWeekStartDate();

  prevWeekBtn.addEventListener("click", function () {
    document.querySelector(".thisWeek").style.display = "none";
    currentWeekStartDate.setDate(currentWeekStartDate.getDate() - 7);
    updateWeek(currentWeekStartDate);
  });

  nextWeekBtn.addEventListener("click", function () {
    document.querySelector(".thisWeek").style.display = "none";
    currentWeekStartDate.setDate(currentWeekStartDate.getDate() + 7);
    updateWeek(currentWeekStartDate);
  });

  function getCurrentWeekStartDate() {
    const currentDate = new Date();
    const currentDayOfWeek = currentDate.getDay(); // 0 (Sunday) to 6 (Saturday)
    const difference = currentDayOfWeek === 0 ? -6 : 1 - currentDayOfWeek; // Calculates the difference between current day and Monday of the current week
    return new Date(currentDate.setDate(currentDate.getDate() + difference));
  }

  function updateWeek(startDate) {
    const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const dayElements = document.querySelectorAll("#week .day");
    let currentDate = new Date(startDate);
    let endDate = new Date(currentDate);
    endDate.setDate(endDate.getDate() + 6); // Bereken de einddatum van de week

    // Update de h2 met de start- en einddatums van de week
    const formattedStartDate = currentDate.toLocaleDateString("en-GB", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    const formattedEndDate = endDate.toLocaleDateString("en-GB", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    currentWeekElement.textContent = `${formattedStartDate} - ${formattedEndDate}`;

    dayElements.forEach((dayElement, index) => {
      const dayNumber = currentDate.getDate();
      const dayOfWeek = daysOfWeek[currentDate.getDay()]; // Bepaal de naam van de dag van de week (bijv. "Mon", "Tue", enz.)
      const currentDayKey = currentDate.toLocaleDateString("en-GB", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
      });

      // Update de tekstinhoud van het dagelement met de dag van de maand
      const dayParagraph = dayElement.querySelector("p");
      dayParagraph.textContent = dayNumber;

      // Wis de inhoud van het dagelement voordat nieuwe kalenderitems worden toegevoegd
      dayElement.innerHTML = `<p>${dayNumber}</p>`;

      // Voeg kalenderitems toe aan het dagelement voor de huidige dag
      if (groupedCalendarItems[currentDayKey]) {
        groupedCalendarItems[currentDayKey].forEach((item) => {
          const calendarItemElement = document.createElement("p");
          calendarItemElement.className = "calendarItem";
          calendarItemElement.style.backgroundColor = item.color; // Stel de achtergrondkleur van het kalenderitem in op basis van de gebruiker
          calendarItemElement.textContent = `${item.startTime} - ${item.endTime}: ${item.task}`;
          dayElement.appendChild(calendarItemElement);
        });
      }

      // Verplaats naar de volgende dag
      currentDate.setDate(currentDate.getDate() + 1);
    });
  }
});

// monthlyview
document.addEventListener("DOMContentLoaded", function () {
  const prevMonthBtn = document.getElementById("prevMonth");
  const nextMonthBtn = document.getElementById("nextMonth");
  const currentMonthElement = document.getElementById("currentMonth");

  let currentMonth = new Date();

  prevMonthBtn.addEventListener("click", function () {
    currentMonth.setMonth(currentMonth.getMonth() - 1);
    updateMonth(currentMonth);
  });

  nextMonthBtn.addEventListener("click", function () {
    currentMonth.setMonth(currentMonth.getMonth() + 1);
    updateMonth(currentMonth);
  });

  function updateMonth(month) {
    const formattedMonth = month.toLocaleDateString("en-GB", {
      month: "long",
      year: "numeric",
    });

    currentMonthElement.textContent = formattedMonth;

    // Voeg hier de logica toe om de weergave van de maand te updaten
    // Dit omvat het bijwerken van de kalenderitems voor elke dag van de maand
  }
});
