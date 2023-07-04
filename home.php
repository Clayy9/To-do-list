<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>

    <!-- Link -->
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

    <script src="https://kit.fontawesome.com/67a87c1aef.js" crossorigin="anonymous"></script>

<body>
    <div class="left">
    <div class="profile">
        <div class="container_profile">
            <img  class="profile_img" src="../assets/images/profile.png" alt="">
        </div>
        <div class="profile_desc">
            <p class="profile_desc_username">Code Blaze</p>
            <p class="profile_desc_email">Codeblaze@gmail.com</p>
        </div>
    </div>

    <div class="container_pet">
        <div class="pet_display">
            <img class="pet_display_img" src="../assets/images/petDisplay.png" alt="">
        </div>
        <div class="pet_info">
            <div class="pet_info_name">
                <p>Rocky</p>
            </div>
            <div class="pet_info_level">

            </div>
            <div class="pet_info_task_completed">
                <div class="pet_info_task_completed_track">
                    <p>30 Task Completed</p>
                </div>
                <div class="pet_info_task_completed_percentage">
                    <p>30%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="right">
    <div class="container_task">
        <div class="task_active">
            <div class="task_title">
            <p>Your task</p>
            </div>
            <div class="task_active_list">
                <div class="task_active_card">
                    <div class="task_category">
                        <img class="task_category_img" src="./assets/images/category/Category=Medic.png" alt="">
                    </div>
                    <div class="task_info">
                        <div class="task_subtitle">
                            <p>Judul</p>
                        </div>
                        <div class="task_deadline">
                            <i class="fa-solid fa-clock" style="color: white;"></i>                        
                            <p>Tanggal</p>
                        </div>
                        <div class="task_desc">
                            <p>Ini adalah deskripsi dari task saya</p>
                        </div>
                        </div>
                    <div class="task_checkbox">
                        <form>
                            <input type="checkbox" id="done" onclick="check_task()"/>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>

        <div class="task_completed">
            <div class="task_title">
                <p>Completed Task</p>
            </div>
            <div class="task_active_list">
                <div class="task_active_card">
                    <div class="task_category">
                        <img class="task_category_img" src="./assets/images/category/Category=Medic.png" alt="">
                    </div>
                    <div class="task_info">
                        <div class="task_subtitle">
                            <p>Judul</p>
                        </div>
                        <div class="task_deadline">
                            <i class="fa-solid fa-clock" style="color: white;"></i>                        
                            <p>Tanggal</p>
                        </div>
                        <div class="task_desc">
                            <p>Ini adalah deskripsi dari task saya</p>
                        </div>
                        </div>
                    <div class="task_checkbox">
                        <form>
                            <input type="checkbox" id="done" onclick="check_task()"/>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>


</body>

<!-- Script -->
<script src="./assets/js/script.js"></script> 
</html>