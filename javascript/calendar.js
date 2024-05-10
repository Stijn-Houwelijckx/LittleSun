document.getElementById("taskSelector").addEventListener("change", function () {
  var taskId = this.value;
  var formData = new FormData();
  formData.append("taskId", taskId);

  fetch("../ajax/store_selected_task_id.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      console.log("Task ID stored in session:", result);
      // AJAX-verzoek om de lijst met gebruikers op te halen op basis van de geselecteerde taak-ID
      // fetch("../ajax/get_users_by_task.php?taskId=" + taskId)
      //   .then((response) => response.json())
      //   .then((data) => {
      //     // Bijwerken van de gebruikersdropdown met de ontvangen lijst met gebruikers
      //     var userSelector = document.getElementById("userSelector");
      //     userSelector.innerHTML = ""; // Leegmaken van de gebruikersdropdown
      //     data.users.forEach(function (user) {
      //       var option = document.createElement("option");
      //       option.value = user.id;
      //       option.textContent = user.name;
      //       userSelector.appendChild(option);
      //     });
      //   })
      //   .catch((error) => {
      //     console.error("Error fetching users:", error);
      //   });
    })
    .catch((error) => {
      console.error("Error storing task ID:", error);
    });
});
