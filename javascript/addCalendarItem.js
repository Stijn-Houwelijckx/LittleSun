// !!!!! DO NOT REMOVE COMMENTED CODE !!!!!

var userSelector = document.getElementById("userSelector");
var taskSelector = document.getElementById("taskSelector");
var eventDatePicker = document.getElementById("eventDatePicker");
var timeSlots = document.querySelectorAll(".timeslot");

// taskSelector.addEventListener("change", function () {
//   var taskId = this.value;
//   var formData = new FormData();
//   formData.append("taskId", taskId);
//   console.log("taskId: " + taskId);

//   fetch("../ajax/fetchUsersByTask.php", {
//     method: "POST",
//     body: formData,
//   })
//     .then((response) => response.json())
//     .then((result) => {
//       if (result.status === "error") {
//         console.log("Error: ", result);
//         return;
//       } else {
//         console.log("Success: ", result);
//         userSelector.innerHTML = "";

//         var option = document.createElement("option");
//         option.value = "";
//         option.text = "--- select user ---";
//         userSelector.appendChild(option);

//         result.employees.forEach((employee) => {
//           var option = document.createElement("option");
//           option.value = employee.id;
//           option.text = employee.firstname + " " + employee.lastname;
//           userSelector.appendChild(option);
//         });
//       }
//     })
//     .catch((error) => {
//       console.error("Error retreiving the users:", error);
//     });
// });

// !!!!! DO NOT REMOVE COMMENTED CODE !!!!!

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

        // Enable the user selector
        taskSelector.disabled = false;

        taskSelector.innerHTML = "";

        var option = document.createElement("option");
        option.value = "";
        option.text = "--- select task ---";
        taskSelector.appendChild(option);

        result.tasks.forEach((task) => {
          var option = document.createElement("option");
          option.value = task.id;
          option.text = task.task;
          taskSelector.appendChild(option);
        });
      }
    })
    .catch((error) => {
      console.error("Error retreiving the tasks:", error);
    });

  // Fetch available time slots

  console.log("Fetching available time slots");

  var date = eventDatePicker.value;
  formData.append("date", date);

  fetch("../ajax/fetchAvailableTimeSlots.php", {
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

        // Enable the time slot checkboxes where user is available

        // timeslot_1 = 08:00 - 09:00
        // timeslot_2 = 09:00 - 10:00
        // timeslot_3 = 10:00 - 11:00
        // timeslot_4 = 11:00 - 12:00
        // timeslot_5 = 12:00 - 13:00
        // timeslot_6 = 13:00 - 14:00
        // timeslot_7 = 14:00 - 15:00
        // timeslot_8 = 15:00 - 16:00
        // timeslot_9 = 16:00 - 17:00
        // timeslot_10 = 17:00 - 18:00
        // timeslot_11 = 18:00 - 19:00
        // timeslot_12 = 19:00 - 20:00
        // timeslot_13 = 20:00 - 21:00

        timeSlots.forEach((timeSlot) => {
          timeSlot.checked = false;
          timeSlot.disabled = true;
        });

        if (!result.timeOffRequests) {
          console.log("User is available all day");
          timeSlots.forEach((timeSlot) => {
            timeSlot.disabled = false;
          });
        } else {
          let startDate = result.timeOffRequests.start_date;
          let endDate = result.timeOffRequests.end_date;

          // Splot into date and time
          let startDateArray = startDate.split(" ");
          let endDateArray = endDate.split(" ");

          // Store date in variables
          let startDateDate = startDateArray[0];
          let endDateDate = endDateArray[0];

          // Store time in variables
          let startTime = startDateArray[1];
          let endTime = endDateArray[1];

          if (startDateDate == date && endDateDate != date) {
            console.log("Start date is today on " + startTime);

            timeSlots.forEach((timeSlot) => {
              let timeSlotValue = timeSlot.value;
              let timeSlotArray = timeSlotValue.split(" - ");

              // Enable all timeslots before the start time
              if (
                timeSlotArray[0] < startTime &&
                timeSlotArray[1] < startTime
              ) {
                timeSlot.disabled = false;
              }
            });
          } else if (endDateDate == date && startDateDate != date) {
            console.log("End date is today on " + endTime);

            timeSlots.forEach((timeSlot) => {
              let timeSlotValue = timeSlot.value;
              let timeSlotArray = timeSlotValue.split(" - ");

              // Assuming endTime is in the format HH:mm:ss
              var endTimeParts = endTime.split(":");
              var endDate = new Date();
              endDate.setHours(parseInt(endTimeParts[0], 10)); // Set hours
              endDate.setMinutes(parseInt(endTimeParts[1], 10)); // Set minutes
              endDate.setSeconds(parseInt(endTimeParts[2], 10)); // Set seconds

              // Subtract 1 hour
              endDate.setHours(endDate.getHours() - 1);

              // Format the new endTime as HH:mm:ss
              var newEndTime = endDate.toTimeString().split(" ")[0];

              console.log(newEndTime);

              // Enable all timeslots after the end time
              if (timeSlotArray[0] > newEndTime) {
                timeSlot.disabled = false;
              }
            });
          } else if (endDateDate == date && startDateDate == date) {
            console.log(
              "Start and end date is today on " + startTime + " - " + endTime
            );

            timeSlots.forEach((timeSlot) => {
              let timeSlotValue = timeSlot.value;
              let timeSlotArray = timeSlotValue.split(" - ");

              // Assuming endTime is in the format HH:mm:ss
              var endTimeParts = endTime.split(":");
              var endDate = new Date();
              endDate.setHours(parseInt(endTimeParts[0], 10)); // Set hours
              endDate.setMinutes(parseInt(endTimeParts[1], 10)); // Set minutes
              endDate.setSeconds(parseInt(endTimeParts[2], 10)); // Set seconds

              // Subtract 1 hour
              endDate.setHours(endDate.getHours() - 1);

              // Format the new endTime as HH:mm:ss
              var newEndTime = endDate.toTimeString().split(" ")[0];

              console.log(newEndTime);

              // Enable all timeslots after the end time
              if (timeSlotArray[0] > newEndTime) {
                timeSlot.disabled = false;
              }

              // Enable all timeslots before the start time
              if (
                timeSlotArray[0] < startTime &&
                timeSlotArray[1] < startTime
              ) {
                timeSlot.disabled = false;
              }
            });
          }
        }
      }
    })
    .catch((error) => {
      console.error("Error retreiving the tasks:", error);
    });
});
