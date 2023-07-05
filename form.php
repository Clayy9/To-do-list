<?php   
include "config/security.php";
include "config/connection.php";
    $user_id = $_SESSION['id'];
    $email = $_SESSION['email'];
    $username = $_SESSION['username'];
    $profile_img = $_SESSION['profile_img']
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>

    <!-- Link -->
    <link rel="stylesheet" href="./assets/css/style_form.css">
</head>


<body>
<div id="add_task_form_container">
        <div class="form_container">
        <h1>NEW TASK</h1>
            <form method="post" action="sv_task.php">
                <h3>Title</h3>
                <div class="inputForm">
                    <input class="textField" type="text" name="email" id="email" placeholder="Type your email..."
                        required />
                    </div>
                    <h3>Description</h3>
                    <div class="inputForm">
                    <input class="textField" type="text" name="email" id="email" placeholder="Type your email..."
                    required />
                </div>
                <div class="inputForm">
                <h3>Category</h3>
                    <div class="customSelect">
                        <select name="category" id="category" required>
                        <option value="" selected disabled>Select a category</option>
                        <option value="sport">Sport</option>
                        <option value="study">Study</option>
                        <option value="meeting">Meeting</option>
                        <option value="medic">Medic</option>
                        </select>
                        <span class="arrow"></span>
                    </div>
                </div>

                <div class="inputForm">
                <h3>Priority</h3>
                    <div class="customSelect">
                        <select name="category" id="category" required>
                        <option value="" selected disabled>Select a priority</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        </select>
                        <span class="arrow"></span>
                    </div>
                </div>

                <div class="button_submit">
                    <center>
                        <td colspan="2"><input class="form_button" type="submit" value="ADD TASK" name="submit"></td>
                    </center>
                </div>
            </form>
        </div>
</div>

<!-- Script -->
<script src="./assets/js/jquery-3.7.0.js"></script> 
<script src="./assets/js/script.js"></script> 
<script src="https://kit.fontawesome.com/67a87c1aef.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        get_data();
        completed_data();
    });
</script>
</body>

</html>