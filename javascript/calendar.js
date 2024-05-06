document.getElementById("taskSelector").addEventListener("change", function () {
  var taskId = this.value;
  var formData = new FormData();
  formData.append("taskId", taskId);
  console.log(taskId);

  fetch("../ajax/store_selected_task_id.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      console.log("Task ID stored in session:", result);
      // Optionally, you can update the UI here based on the response
    })
    .catch((error) => {
      console.error("Error storing task ID:", error);
    });
});
