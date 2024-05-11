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
    updateWeek(currentWeekStartDate, groupedCalendarItems);
  });

  nextWeekBtn.addEventListener("click", function () {
    document.querySelector(".thisWeek").style.display = "none";
    currentWeekStartDate.setDate(currentWeekStartDate.getDate() + 7);
    updateWeek(currentWeekStartDate, groupedCalendarItems);
    console.log(groupedCalendarItems);
  });

  function getCurrentWeekStartDate() {
    const currentDate = new Date();
    const currentDayOfWeek = currentDate.getDay(); // 0 (Sunday) to 6 (Saturday)
    const difference = currentDayOfWeek === 0 ? -6 : 1 - currentDayOfWeek; // Calculates the difference between current day and Monday of the current week
    return new Date(currentDate.setDate(currentDate.getDate() + difference));
  }

  function updateWeek(startDate, groupedCalendarItems) {
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
      const currentDayKey = currentDate.toLocaleDateString("en-GB", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
      });

      // Wis de inhoud van het dagelement voordat nieuwe kalenderitems worden toegevoegd
      dayElement.innerHTML = "";

      // Update de tekstinhoud van het dagelement met de dag van de maand
      const dayParagraph = document.createElement("p");
      dayParagraph.textContent = dayNumber;
      dayElement.appendChild(dayParagraph);

      // Voeg kalenderitems toe aan het dagelement voor de huidige dag
      if (groupedCalendarItems[currentDayKey]) {
        groupedCalendarItems[currentDayKey].forEach((item) => {
          const userId = item.user_id; // Gebruik de gebruikers-ID om de kleur te bepalen
          const red = (userId * 70) % 256;
          const green = (userId * 120) % 256;
          const blue = (userId * 170) % 256;
          const itemColor = `rgb(${red}, ${green}, ${blue})`;

          const calendarItemElement = document.createElement("p");
          calendarItemElement.className = "calendarItem";
          calendarItemElement.style.backgroundColor = itemColor;
          calendarItemElement.textContent = `${item.startTime} - ${item.endTime}: ${item.task}`;

          dayElement.appendChild(calendarItemElement);
        });
      } else {
        const p = document.createElement("p");
        p.textContent = "No calendar items for this day.";
        dayElement.appendChild(p);
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
    document.querySelector(".thisMonth").style.display = "none";
    currentMonth.setMonth(currentMonth.getMonth() - 1);
    updateMonth(currentMonth);
  });

  nextMonthBtn.addEventListener("click", function () {
    document.querySelector(".thisMonth").style.display = "none";
    currentMonth.setMonth(currentMonth.getMonth() + 1);
    updateMonth(currentMonth);
  });

  function updateMonth(month) {
    const formattedMonth = month.toLocaleDateString("en-GB", {
      month: "long",
      year: "numeric",
    });

    currentMonthElement.textContent = formattedMonth;

    const daysInMonth = getDaysInMonth(
      month.getFullYear(),
      month.getMonth() + 1
    );

    // Voeg hier de logica toe om de weergave van de maand te updaten
    const dayContainer = document.getElementById("monthItems");
    dayContainer.innerHTML = ""; // Wis de inhoud van de container voordat nieuwe items worden toegevoegd

    for (let i = 1; i <= daysInMonth; i++) {
      const dayElement = document.createElement("div");
      dayElement.className = "day";

      const dayNumber = document.createElement("p");
      dayNumber.textContent = i < 10 ? "0" + i : i;
      dayElement.appendChild(dayNumber);

      // Voeg kalenderitems toe aan het dagelement voor de huidige dag
      const currentDayKey = `${month.getFullYear()}-${(month.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${i.toString().padStart(2, "0")}`;

      if (groupedCalendarItems[currentDayKey]) {
        groupedCalendarItems[currentDayKey].forEach((item) => {
          const userId = item.user_id;
          const red = (userId * 70) % 256;
          const green = (userId * 120) % 256;
          const blue = (userId * 170) % 256;
          const itemColor = `rgb(${red}, ${green}, ${blue})`;

          const calendarItemElement = document.createElement("p");
          calendarItemElement.className = "calendarItem";
          calendarItemElement.style.backgroundColor = itemColor;
          calendarItemElement.textContent = `${item.startTime} - ${item.endTime}: ${item.task}`;

          dayElement.appendChild(calendarItemElement);
        });
      } else {
        const noItemElement = document.createElement("p");
        dayElement.appendChild(noItemElement);
      }

      dayContainer.appendChild(dayElement);
    }
  }

  // Functie om het aantal dagen in een maand te krijgen
  function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
  }
});
