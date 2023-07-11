// Memunculkan modal add task
const addButton = document.getElementById("add_task_button");
const editButton = document.getElementById("edit_task_button");
const addTaskForm = document.getElementById("add_task_form_container");
const TaskFormButton = document.getElementById("submit-button");

addButton.addEventListener("click", function () {
  addTaskForm.style.display = "block";
  addTaskForm.style.visibility = "visible";
  addTaskForm.style.opacity = "1";

  get_data();
});

TaskFormButton.addEventListener("click", function () {
  addTaskForm.style.display = "none";
  addTaskForm.style.visibility = "hidden";
  addTaskForm.style.opacity = "0";
});

// Filter
// Filter Modal Reveal
const filterButton = document.getElementById("filter_task_button");
const filterContainer = document.getElementById("filter_container");
const filterSubmitButton = document.getElementById("submit-button-filter");

filterButton.addEventListener("click", function () {
  filterContainer.style.display = "block";
  filterContainer.style.visibility = "visible";
  filterContainer.style.opacity = "1";
});

// Filter Modal Unreveal
const filterButtonBack = document.getElementById("back_to_home_filter_button");

filterButtonBack.addEventListener("click", function () {
  filterContainer.style.display = "none";
  filterContainer.style.visibility = "hidden";
  filterContainer.style.opacity = "0";
});

// Modal Data
function filterTask() {
  var form = $("#filter_form_container"); // Definisikan variabel form
  // Mengumpulkan data formulir menggunakan serialize()
  var formFilter = form.serialize();

  // Menambahkan data "act" secara manual ke dalam data formulir
  formFilter += "&act=filter";
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: formFilter,
    success: function (result) {
      $("#active_tasks").html(result);

      filterContainer.style.display = "none";
      filterContainer.style.visibility = "hidden";
      filterContainer.style.opacity = "0";
    },
  });
}

// Mengambil tanggal hari ini
var now = new Date();
var y = now.getFullYear();
var m = now.getMonth() + 1;
var d = now.getDate();

m = m < 10 ? "0" + m : m;
d = d < 10 ? "0" + d : d;

document.querySelector("input[type=date]").value = y + "-" + m + "-" + d;

function check_task(task_id) {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      id: task_id,
      act: "set_done",
    },
    success: function (result) {
      get_data();
      completed_data();
      loadingPET();
      addXP(task_id);
    },
  });
}

function addXP(task_id) {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      act: "addXP",
      task_id: task_id,
    },
    success: function (result) {
      $("#pet_xp").html(result);
    },
  });
}

function loadingPET() {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      act: "loadingPET",
    },
    success: function (result) {
      $("#container_pet").html(result);
    },
  });
}

function uncheck_task(task_id) {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      id: task_id,
      act: "uncheck",
    },
    success: function (result) {
      get_data();
      completed_data();
      loadingPET();
    },
  });
}

function delete_task(task_id) {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      id: task_id,
      act: "deleteTask",
    },
    success: function (result) {
      get_data();
      completed_data();
      loadingPET();
    },
  });
}

function get_data() {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      act: "loading",
    },
    success: function (result) {
      $("#active_tasks").html(result);
    },
  });
}

function completed_data() {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      act: "completed",
    },
    success: function (result) {
      $("#completed_tasks").html(result);
    },
  });
}

function saveXP() {
  $.ajax({
    url: "sv_score.php",
    method: "POST",
    data: {
      act: "saveXP",
    },
    success: function (result) {
      $("#pet_xp").html(result);
    },
  });
}

// Add Task Baru
$("#submit-button").click(function (e) {
  e.preventDefault();

  var form = $("#add_task_form_container");

  // Mengumpulkan data formulir menggunakan serialize()
  var formData = form.serialize();

  // Menambahkan data "act" secara manual ke dalam data formulir
  formData += "&act=add";

  $.ajax({
    type: "POST",
    url: "sv_task.php",
    data: formData,
    success: function () {
      alert("Data Berhasil");

      get_data();
      completed_data();
      loadingPET();

      // Mengatur ulang form
      $("#add_task_form_container")[0].reset();
    },
  });
});

// Edit Task
function editTask(id) {
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: { id: id, act: "edit" },

    success: function (result) {
      var data = result.split("|");

      var id = $("#id").val(data[1]);
      $("#title_task").html("Edit Task");
      $("#task_name").val(data[2]);
      $("#task_desc").val(data[3]);
      $("#category_id").val(data[4]);
      $("#priority_id").val(data[5]);
      $("#task_date").val(data[6]);
      $("#task_time").val(data[7]);
      $("#reminder_id").val(data[8]);
      $("#status_id").val(data[9]);

      // Tambahkan kode untuk menampilkan modal
      var addTaskForm = document.getElementById("add_task_form_container");
      addTaskForm.style.display = "block";
      addTaskForm.style.visibility = "visible";
      addTaskForm.style.opacity = "1";

      // Mengikat ulang event handler untuk tombol submit
      $("#submit-button").unbind("click");
      $("#submit-button").on("click", function () {
        update_task();
      });
    },
  });
}

function update_task() {
  var id = $("#id").val();
  var task_name = $("#task_name").val();
  var task_desc = $("#task_desc").val();
  var category_id = $("#category_id").val();
  var priority_id = $("#priority_id").val();
  var task_date = $("#task_date").val();
  var task_time = $("#task_time").val();
  var reminder_id = $("#reminder_id").val();
  var status_id = $("#status_id").val();
  $.ajax({
    url: "sv_task.php",
    method: "POST",
    data: {
      id: id,
      task_name: task_name,
      task_desc: task_desc,
      category_id: category_id,
      priority_id: priority_id,
      task_date: task_date,
      task_time: task_time,
      reminder_id: reminder_id,
      status_id: status_id,
      act: "update",
    },
    success: function (result) {
      get_data();
      completed_tasks();
    },
  });
}

$(document).ready(function () {
  get_data();
  completed_data();
  loadingPET();
});
