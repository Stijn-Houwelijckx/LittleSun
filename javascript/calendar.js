// dailyview
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

// weeklyview
document.addEventListener("DOMContentLoaded", function () {
  const prevWeekBtn = document.querySelector("#prevWeekButton");
  const nextWeekBtn = document.getElementById("nextWeek");
  const currentWeekElement = document.getElementById("currentWeek");

  let currentWeekStartDate = getCurrentWeekStartDate();

  // Vullen van de groupedCalendarItems voor de huidige week
  updateGroupedCalendarItems(currentWeekStartDate);

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

  function updateGroupedCalendarItems(startDate) {
    const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    let currentDate = new Date(startDate);

    // Vullen van groupedCalendarItems voor elke dag van de week
    for (let i = 0; i < 7; i++) {
      const currentDayKey = currentDate.toLocaleDateString("en-GB", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
      });

      currentDate.setDate(currentDate.getDate() + 1); // Naar de volgende dag gaan
    }
  }

  function updateWeek(startDate) {
    const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const dayElements = document.querySelectorAll("#week .day");
    let currentDate = new Date(startDate);
    let endDate = new Date(currentDate);
    endDate.setDate(endDate.getDate() + 6); // Calculate the end date of the week

    // Update the h2 with the start and end dates of the week
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
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth() + 1;
      const day = currentDate.getDate();

      const currentDayKey = `${year}-${String(month).padStart(2, "0")}-${String(
        day
      ).padStart(2, "0")}`;

      // console.log(currentDayKey);

      // Clear the content of the day element before adding new calendar items
      dayElement.innerHTML = "";

      const dateNow = new Date();
      const year_now = dateNow.getFullYear().toString();
      const month_now = String(dateNow.getMonth() + 1).padStart(2, "0");
      const day_now = String(dateNow.getDate()).padStart(2, "0");
      const formattedDateNow = `${year_now}-${month_now}-${day_now}`;

      if (currentDayKey == formattedDateNow) {
        dayElement.classList.add("current_day");
      } else {
        dayElement.classList.remove("current_day");
      }

      // Update the text content of the day element with the day of the month
      const dayParagraph = document.createElement("p");
      dayParagraph.textContent = day;
      dayElement.appendChild(dayParagraph);

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
          calendarItemElement.textContent = `${item.start_time.slice(
            0,
            -3
          )} - ${item.end_time.slice(0, -3)} : ${item.task}`;

          dayElement.appendChild(calendarItemElement);
        });
      }

      // Move to the next day
      currentDate.setDate(currentDate.getDate() + 1);
    });
  }
});

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
    const monthContainer = document.getElementById("monthItems");
    monthContainer.innerHTML = ""; // Wis de inhoud van de container voordat nieuwe items worden toegevoegd

    for (let i = 1; i <= daysInMonth; i++) {
      const dayElement = document.createElement("div");
      dayElement.className = "day";

      const dayNumber = document.createElement("p");
      dayNumber.textContent = i < 10 ? "0" + i : i;
      dayElement.appendChild(dayNumber);

      const currentDayKey = `${month.getFullYear()}-${(month.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${i.toString().padStart(2, "0")}`;

      const dateNow = new Date();
      const year_now = dateNow.getFullYear().toString();
      const month_now = String(dateNow.getMonth() + 1).padStart(2, "0");
      const day_now = String(dateNow.getDate()).padStart(2, "0");
      const formattedDateNow = `${year_now}-${month_now}-${day_now}`;

      if (currentDayKey == formattedDateNow) {
        dayElement.classList.add("current_day");
      } else {
        dayElement.classList.remove("current_day");
      }

      // Voeg kalenderitems toe aan het dagelement voor de huidige dag
      if (
        groupedCalendarItems[currentDayKey] &&
        groupedCalendarItems[currentDayKey].length > 0
      ) {
        groupedCalendarItems[currentDayKey].forEach((item) => {
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

          dayElement.appendChild(p);
        });
      }

      monthContainer.appendChild(dayElement);
    }
  }

  // Functie om het aantal dagen in een maand te krijgen
  function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
  }
});
