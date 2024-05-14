var properties = [
  "employee",
  "time_planned",
  "time_worked",
  "time_off",
  "overtime",
  "sick_time",
];

properties.forEach(function (val) {
  var orderClass = "";
  var element = document.getElementById(val);
  if (element) {
    element.addEventListener("click", function (e) {
      e.preventDefault();
      var clickedElement = this;
      document
        .querySelectorAll(".filter__link.filter__link--active")
        .forEach(function (el) {
          if (el !== clickedElement) {
            el.classList.remove("filter__link--active");
          }
        });
      this.classList.toggle("filter__link--active");
      document.querySelectorAll(".filter__link").forEach(function (el) {
        el.classList.remove("asc", "desc");
      });

      if (orderClass == "desc" || orderClass == "") {
        clickedElement.classList.add("asc");
        orderClass = "asc";
      } else {
        clickedElement.classList.add("desc");
        orderClass = "desc";
      }

      var parent = clickedElement.closest(".header__item");
      var index = Array.from(
        document.querySelectorAll(".header__item")
      ).indexOf(parent);
      var $table = document.querySelector(".table-content");
      var rows = Array.from($table.querySelectorAll(".table-row"));

      var isSelected = clickedElement.classList.contains(
        "filter__link--active"
      );
      var isNumber = clickedElement.classList.contains("filter__link--number");

      rows.sort(function (a, b) {
        var x = a.querySelectorAll(".table-data")[index].textContent;
        var y = b.querySelectorAll(".table-data")[index].textContent;

        if (isNumber) {
          if (isSelected) {
            return parseFloat(x) - parseFloat(y);
          } else {
            return parseFloat(y) - parseFloat(x);
          }
        } else {
          if (isSelected) {
            if (x < y) return -1;
            if (x > y) return 1;
            return 0;
          } else {
            if (x > y) return -1;
            if (x < y) return 1;
            return 0;
          }
        }
      });

      rows.forEach(function (row) {
        $table.appendChild(row);
      });

      return false;
    });
  }
});
