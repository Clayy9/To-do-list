<?php
include "config/security.php";
include "config/connection.php";

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$act = $_POST['act']; // membedakan prosesnya
$id = $_POST['id'] ?? '';

if ($act == "set_done") {
    $sql = "UPDATE tb_tasks SET status_id = 2 WHERE id = '$id'";
    $query = mysqli_query($conn, $sql);

} else if ($act == "uncheck") {
    $sql = "UPDATE tb_tasks SET status_id = 1 WHERE id = '$id'";
    $query = mysqli_query($conn, $sql);

} else if ($act == "deleteTask") {
    // Lakukan penghapusan record di dalam tb_reminders
    $deleteRemindersQuery = "DELETE FROM tb_reminders WHERE task_id = '$id'";
    mysqli_query($conn, $deleteRemindersQuery);

    // Lakukan penghapusan record di dalam tb_reminders
    $deleteRemindersQuery = "DELETE FROM tb_collaborators WHERE task_id = '$id'";
    mysqli_query($conn, $deleteRemindersQuery);

    $sql = "DELETE FROM tb_tasks WHERE id = '$id'";
    $query = mysqli_query($conn, $sql);

} else if ($act == "checkDateTime") {
    $user_id = $_SESSION['id'];

    // Menggunakan timezone Asia Jakarta
    date_default_timezone_set('Asia/Jakarta');

    // Mendapatkan tanggal dan waktu hari ini
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:00', time());

    $sql = "SELECT tb_reminders.*, tb_tasks.*
                FROM tb_reminders 
                LEFT JOIN tb_tasks ON tb_reminders.task_id = tb_tasks.id
                WHERE reminder_date = '$currentDate' 
                AND reminder_time = '$currentTime' 
                AND tb_tasks.status_id = 1
                AND (tb_tasks.user_id = '$user_id' OR tb_tasks.id IN (SELECT task_id FROM tb_collaborators WHERE user_id = '$user_id'))";


    $query = mysqli_query($conn, $sql);
    $numRows = mysqli_num_rows($query);

    if ($numRows > 0) {
        $result = mysqli_fetch_array($query);

        // Memanggil Ringtone
        $sqlRingtone = "SELECT tb_users.ringtone_id, tb_ringtones.* FROM tb_users LEFT JOIN tb_ringtones ON tb_users.ringtone_id = tb_ringtones.id WHERE tb_users.id = '$user_id'";
        $queryRingtone = mysqli_query($conn, $sqlRingtone);
        $resultRingtone = mysqli_fetch_array($queryRingtone);

        ?>
                    <script>
                        // Menutup modal reminder
                        const reminderModal = document.getElementById("reminder");
                        const reminderModalButton = document.getElementById("button_close_reminder");

                        reminderModalButton.addEventListener('click', function () {
                            reminderModal.style.display = "none";
                            reminderModal.style.visibility = "hidden";
                            reminderModal.style.opacity = "0";
                        });

                        // Mengecek apakah fitur Autoplay didukung oleh peramban
                        function isAutoplaySupported() {
                            // Periksa apakah peramban mendukung fitur Autoplay
                            if ("autoplay" in document.createElement("audio")) {
                                return true;
                            } else {
                                return false;
                            }
                        }

                        // Memainkan ringtone
                        function playRingtone() {
                            var ringtone = new Audio("./assets/ringtones/<?php echo $resultRingtone['sound']; ?>");
                            ringtone.play();
                            ringtone.autoplay = true;
                        }

                        // Fungsi untuk memulai pemutaran ringtone setelah interaksi pengguna
                        function startAutoplay() {
                            if (isAutoplaySupported()) {
                                playRingtone();
                            } else {
                                // Jika Autoplay tidak didukung, tampilkan pesan untuk mengingatkan pengguna
                                alert("Reminder: ");
                            }
                        }

                        // Menambahkan event listener untuk deteksi interaksi pengguna
                        document.addEventListener('onload', function () {
                            startAutoplay();
                        });


                        // Memulai pemutaran otomatis saat halaman dimuat
                        startAutoplay();
                    </script>

                    <div class="reminder_container">
                        <div class="reminder_title">
                            <p>Task Reminder</p>
                        </div>
                        <div class="reminder_desc">
                            <p>You have an important task to complete</p>
                            <p>Title:
                    <?php echo $result['task_name']; ?> (
                    <?php echo $result['task_date']; ?>)
                            </p>
                        </div>
                        <div class="button_close_reminder">
                            <td colspan="2"><input class="reminder_button" type="button" value="CLOSE" id="button_close_reminder">
                            </td>
                        </div>
                    </div>

            <?php

            header("Refresh:0");
    }

    // Mengecek apakah user ditambahkan pada task collaboration
    $sqlCheckCollab = "SELECT c.*, t.*, t.task_name AS task_name, t.task_desc AS task_desc FROM tb_collaborators c
                            LEFT JOIN tb_tasks t ON c.task_id = t.id
                            WHERE added_date = '$currentDate' 
                            AND added_time = '$currentTime' 
                            AND t.status_id = 1 
                            AND t.id IN (SELECT task_id FROM tb_collaborators WHERE user_id = '$user_id')";

    $queryCheckCollab = mysqli_query($conn, $sqlCheckCollab);
    $numRowsCheckCollab = mysqli_num_rows($queryCheckCollab);

    if ($numRowsCheckCollab > 0) {
        $resultCheckCollab = mysqli_fetch_array($queryCheckCollab);
        $task_name = $resultCheckCollab['task_name'];
        ?>
                    <div class="reminder_container">
                        <div class="reminder_title">
                            <p>Hello
                    <?php echo $username; ?>!
                            </p>
                        </div>
                        <div class="reminder_desc">
                            <p>You got a new collaboration task!</p>
                            <p>Title:
                    <?php echo $task_name; ?>
                            </p>
                        </div>
                    </div>
        <?php
    }

} else if ($act == "loadingPET") {
    $sql = "UPDATE tb_users
                    SET pet_phases_id = (
                    SELECT tb_pet_phases.id
                    FROM tb_pet_phases
                    LEFT JOIN tb_pets ON tb_pet_phases.pet_id = tb_pets.id
                    LEFT JOIN tb_users ON tb_users.pet_id = tb_pet_phases.pet_id
                    WHERE tb_users.id = '$user_id'
                    AND (
                    (xp >= min_xp AND xp <= max_xp)
                    OR (xp > max_xp AND max_xp = 100)
                    )
                    ORDER BY max_xp DESC
                    LIMIT 1
                    )
                    WHERE id = '$user_id'";

    $query = mysqli_query($conn, $sql);


    $sqlGetData = "SELECT tb_users.*, tb_pets.*, tb_pet_phases.*,
                            (SELECT COUNT(*) FROM tb_tasks WHERE user_id='$user_id' AND status_id=2) AS task_completed
                            FROM tb_users
                            LEFT JOIN tb_pet_phases ON tb_users.pet_phases_id = tb_pet_phases.id
                            LEFT JOIN tb_pets ON tb_users.pet_id = tb_pets.id
                            WHERE tb_users.id='$user_id' AND
                            ((xp >= min_xp AND xp <= max_xp) OR (xp > max_xp OR max_xp = 100))
                            AND tb_users.pet_id = (SELECT tb_users.pet_id FROM tb_users WHERE id='$user_id' LIMIT 1)
                            ORDER BY tb_pet_phases.max_xp DESC LIMIT 1";

    $query = mysqli_query($conn, $sqlGetData);
    $resultUpdatePET = mysqli_fetch_array($query);
    $task_completed = $resultUpdatePET['task_completed'];

    ?>

                    <div class="pet_display">
                        <img class="pet_display_img" id="pet_display_img"
                            src="./assets/images/pet/<?php echo $resultUpdatePET['phase_img']; ?>"
                            alt="<?php echo $resultUpdatePET['phase_img']; ?>" />
                    </div>

                    <div class="pet_info">
                        <div class="pet_info_name">
                            <p>
                <?php echo $resultUpdatePET['pet_name']; ?>
                            </p>
                        </div>

                        <div class="pet_info_task_completed">
                            <div id="pet_score">
                                <div class="pet_info_task_completed">
                                    <div class="pet_info_task_completed_track">
                                        <p>
                            <?php echo $task_completed ?> Task Completed
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="pet_info_task_completed_percentage" id="pet_xp">
                                <p>
                    <?php echo $resultUpdatePET['xp'] ?> XP
                                </p>
                            </div>
                        </div>
                    </div>
                    </div>

    <?php
} else if ($act == "addXP") {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['id'];
    ?>

        <?php
        $checkPriority = "SELECT priority_id from tb_tasks WHERE id = '$task_id' AND user_id = '$user_id'";
        $resultPriority = mysqli_query($conn, $checkPriority);

        $priority_value = mysqli_fetch_array($resultPriority)['priority_id'];

        $checkXP = "SELECT xp FROM tb_users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $checkXP);
        $currentXP = mysqli_fetch_array($result)['xp'];

        $newScore = $currentXP + $priority_value;

        $sqlUpdate = "UPDATE tb_users SET xp = '$newScore' WHERE id = '$user_id'";
        $queryUpdate = mysqli_query($conn, $sqlUpdate);

        ?>
                        <p>
        <?php echo $newScore; ?> XP
                        </p>
    <?php

} else if ($act == "add") {
    $task_name = $_POST['task_name'];
    $task_date = $_POST['task_date'];
    if (empty($task_date)) {
        $task_date = date('Y-m-d');
    }

    $task_time = $_POST['task_time'];
    if (empty($task_time)) {
        $task_time = "00:00";
    }

    $task_desc = $_POST['task_desc'];
    if (empty($task_desc)) {
        $task_desc = "Tidak ada deskripsi";
    }

    $priority_id = $_POST['priority_id'];
    if (empty($priority_id)) {
        $priority_id = "1";
    }

    $user_id = $_SESSION['id'];

    $category_id = $_POST['category_id'];
    if (empty($category_id)) {
        $category_id = "0";
    }

    $reminder_number = $_POST['reminder_number'];
    $reminder_type = $_POST['reminder_type'];

    $collaborators = $_POST['collaborator'];

    // Untuk memproses reminder
    $totalTime = 0;
    $execReminder = false;

    // Olah data dari multiple input reminder
    $arrayLength = count($reminder_number);

    if ($reminder_number != "") {

        // Periksa apakah $reminder_number dan $reminder_type adalah array
        if (is_array($reminder_number) && is_array($reminder_type)) {
            $arrayReminderLength = count($reminder_number); // atau count($reminder_type), keduanya harus memiliki panjang yang sama

            // Buat array untuk menyimpan nilai yang dihitung
            $totalTime = array();
            $execReminder = false;

            for ($i = 0; $i < $arrayReminderLength; $i++) {
                if (!empty($reminder_number[$i]) && !empty($reminder_type[$i])) {
                    // Proses reminder_number jika memiliki nilai
                    if ($reminder_number[$i] != "") {
                        if ($reminder_type[$i] == "minutes") {
                            $totalTime[$i] = $reminder_number[$i];
                        } else if ($reminder_type[$i] == "hours") {
                            $totalTime[$i] = $reminder_number[$i] * 60;
                        } else if ($reminder_type[$i] == "days") {
                            $totalTime[$i] = $reminder_number[$i] * 1440;
                        }
                        $execReminder = true;
                    }
                }
            }
        }
    }

    // Input task baru ke database
    $status_id = 1;
    $sql_insert = "INSERT INTO tb_tasks(task_name, task_date, task_time, task_desc, priority_id, user_id, category_id, status_id) VALUES ('$task_name', '$task_date', '$task_time', '$task_desc', '$priority_id', '$user_id', '$category_id', '$status_id')";
    $run_query_check = mysqli_query($conn, $sql_insert);

    // Jika ada reminder, jalankan code ini
    if ($execReminder) {
        // Mendapatkan id dari task yang baru dibuat
        $sqlGetTaskId = "SELECT id FROM tb_tasks WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 1";
        $queryGetTaskId = mysqli_query($conn, $sqlGetTaskId);
        $resultGetTaskId = mysqli_fetch_array($queryGetTaskId);
        $task_id = $resultGetTaskId['id'];

        // Melakukan pengulangan untuk input semua reminder ke database
        for ($i = 0; $i < $arrayReminderLength; $i++) {
            $reminder_date[$i] = date('Y-m-d', strtotime($task_date . ' ' . $task_time . ' - ' . $totalTime[$i] . ' minutes'));
            $reminder_time[$i] = date('H:i', strtotime($task_time . ' - ' . $totalTime[$i] . ' minutes'));

            $sqlReminder = "INSERT INTO tb_reminders(task_id, reminder_date, reminder_time, reminder_number, reminder_type) VALUES('$task_id', '$reminder_date[$i]', '$reminder_time[$i]', '$reminder_number[$i]', '$reminder_type[$i]')";
            mysqli_query($conn, $sqlReminder);
        }
    }

    if ($collaborators != "") {
        if (is_array($collaborators)) {
            // Hitung Array
            $arrayCollaboratorsLength = count($collaborators);

            // Mengambil Id task terakhir
            $sqlGetTaskId = "SELECT id FROM tb_tasks WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 1";
            $queryGetTaskId = mysqli_query($conn, $sqlGetTaskId);
            $resultGetTaskId = mysqli_fetch_array($queryGetTaskId);
            $task_id = $resultGetTaskId['id'];

            $sqlCheckProfile = "SELECT id, username FROM tb_users WHERE id = '$user_id'";
            $queryCheckProfile = mysqli_query($conn, $sqlCheckProfile);
            $resultCheckProfile = mysqli_fetch_array($queryCheckProfile);
            $username = $resultCheckProfile['username'];
            $user_collaborator_id = $resultCheckProfile['id'];

            for ($i = 0; $i < $arrayCollaboratorsLength; $i++) {

                $sqlCheckProfile = "SELECT id, username FROM tb_users WHERE id = '$collaborators[$i]'";
                $queryCheckProfile = mysqli_query($conn, $sqlCheckProfile);
                $resultCheckProfile = mysqli_fetch_array($queryCheckProfile);
                $username = $resultCheckProfile['username'];
                $user_collaborator_id = $resultCheckProfile['id'];

                // Menggunakan timezone Asia Jakarta
                date_default_timezone_set('Asia/Jakarta');

                // Mendapatkan tanggal dan waktu hari ini
                $currentDate = date('Y-m-d');
                $currentTime = date('H:i:00', time());

                // Menambahkan data ke tabel kolaborator berdasarkan id
                $sqlCollaborator = "INSERT INTO tb_collaborators(collaborator_username, task_id, user_id, added_date, added_time) VALUES('$username', '$task_id', '$user_collaborator_id', '$currentDate', '$currentTime')";
                mysqli_query($conn, $sqlCollaborator);
            }
        }
    }


} else if ($act == "edit") {
    $id = $_POST['id'];

    $sql = "SELECT * FROM tb_tasks WHERE tb_tasks.id = '$id'";

    $query = mysqli_query($conn, $sql);
    $result = mysqli_fetch_array($query);

    $task_id = $result['id'];
    $task_name = $result['task_name'];
    $task_desc = $result['task_desc'];
    $category_id = $result['category_id'];
    $priority_id = $result['priority_id'];
    $task_date = $result['task_date'];
    $task_time = $result['task_time'];
    $status_id = $result['status_id'];

    // Mengirimkan objek reminder dalam bentuk JSON ke script.js
    $sqlGetReminders = "SELECT * FROM tb_reminders WHERE task_id = '$id'";
    $queryGetReminders = mysqli_query($conn, $sqlGetReminders);
    $reminderDetail = array(); // Inisialisasi array untuk reminderDetail

    if (mysqli_num_rows($queryGetReminders) > 0) {
        while ($rowReminders = mysqli_fetch_assoc($queryGetReminders)) {
            $reminderDetail[] = array(
                'reminder_number' => $rowReminders['reminder_number'],
                'reminder_type' => $rowReminders['reminder_type']
            );
        }

        // Mengubah array menjadi JSON dan mengirimkan ke script.js
        $reminderDetailJson = json_encode($reminderDetail);
    } else {
        // Mengubah array menjadi JSON dan mengirimkan ke script.js
        $reminderDetailJson = json_encode(array());
    }

    // Mengirimkan objek collaborator dalam bentuk JSON ke script.js
    $sqlGetSelectedCollaborators = "SELECT * FROM tb_collaborators WHERE task_id = '$id'";
    $queryGetSelectedCollaborators = mysqli_query($conn, $sqlGetSelectedCollaborators);
    $collaborators = array(); // Inisialisasi array untuk Collaborators

    if (mysqli_num_rows($queryGetSelectedCollaborators) > 0) {

        while ($rowCollaborators = mysqli_fetch_assoc($queryGetSelectedCollaborators)) {
            $collaborators[] = array(
                'user_id' => $rowCollaborators['user_id'],
                'collaborator_username' => $rowCollaborators['collaborator_username']
            );
        }

        // Mengubah array menjadi JSON dan mengirimkan ke script.js
        $collaboratorsJson = json_encode($collaborators);
    } else {
        // Mengubah array menjadi JSON dan mengirimkan ke script.js
        $collaboratorsJson = json_encode(array());
    }

    // Mengirimkan objek all collaborator dalam bentuk JSON ke script.js
    $sqlGetAllCollaborators = "SELECT id, username FROM tb_users WHERE id != '$user_id'";
    $queryGetAllCollaborators = mysqli_query($conn, $sqlGetAllCollaborators);
    $allCollaborators = array(); // Inisialisasi array untuk All Collaborators

    if (mysqli_num_rows($queryGetAllCollaborators) > 0) {

        while ($rowAllCollaborators = mysqli_fetch_assoc($queryGetAllCollaborators)) {
            $allCollaborators[] = array(
                'user_id' => $rowAllCollaborators['id'],
                'collaborator_username' => $rowAllCollaborators['username']
            );
        }

        // Mengubah array menjadi JSON dan mengirimkan ke script.js
        $allCollaboratorsJson = json_encode($allCollaborators);
    } else {
        // Mengubah array menjadi JSON dan mengirimkan ke script.js
        $allCollaboratorsJson = json_encode(array());
    }

    // Echo data dalam bentuk JSON dengan pemisah '|' untuk digunakan di JavaScript
    echo "|" . $task_id . "|" . $task_name . "|" . $task_desc . "|" . $category_id . "|" . $priority_id . "|" . $task_date . "|" . $task_time . "|" . $status_id . "|" . $reminderDetailJson . "|" . $collaboratorsJson . "|" . $allCollaboratorsJson . "|";

} else if ($act == "update") {

    $user_id = $_SESSION['id'];
    $task_id = $_POST['id'];
    $task_name = $_POST['task_name'];
    $task_desc = $_POST['task_desc'];
    $category_id = $_POST['category_id'];
    $priority_id = $_POST['priority_id'];
    $task_date = $_POST['task_date'];
    $task_time = $_POST['task_time'];
    $status_id = $_POST['status_id'];
    $reminder_number = $_POST['reminder_number'];
    $reminder_type = $_POST['reminder_type'];
    $collaborators = $_POST['collaborator'];

    $sql = "UPDATE tb_tasks SET task_name = '$task_name', task_time = '$task_time', task_date = '$task_date', task_desc = '$task_desc', priority_id = '$priority_id', user_id = '$user_id', category_id = '$category_id', status_id = '$status_id' WHERE id = '$task_id' AND user_id = '$user_id'";
    $query = mysqli_query($conn, $sql);

    if ($reminder_number != "") {

        // Periksa apakah $reminder_number dan $reminder_type adalah array
        if (is_array($reminder_number) && is_array($reminder_type)) {

            // Delete Old Reminder
            $sqlDeleteOldReminder = "DELETE FROM tb_reminders WHERE task_id = '$task_id'";
            mysqli_query($conn, $sqlDeleteOldReminder);

            $arrayReminderLength = count($reminder_number); // atau count($reminder_type), keduanya harus memiliki panjang yang sama

            // Buat array untuk menyimpan nilai yang dihitung
            $totalTime = array();

            for ($i = 0; $i < $arrayReminderLength; $i++) {
                if (!empty($reminder_number[$i]) && !empty($reminder_type[$i])) {
                    // Proses reminder_number jika memiliki nilai
                    if ($reminder_number[$i] != "") {
                        if ($reminder_type[$i] == "minutes") {
                            $totalTime[$i] = $reminder_number[$i];
                        } else if ($reminder_type[$i] == "hours") {
                            $totalTime[$i] = $reminder_number[$i] * 60;
                        } else if ($reminder_type[$i] == "days") {
                            $totalTime[$i] = $reminder_number[$i] * 1440;
                        }
                    }

                    // Melakukan pengulangan untuk input semua reminder ke database
                    $reminder_date[$i] = date('Y-m-d', strtotime($task_date . ' ' . $task_time . ' - ' . $totalTime[$i] . ' minutes'));
                    $reminder_time[$i] = date('H:i', strtotime($task_time . ' - ' . $totalTime[$i] . ' minutes'));

                    $sqlReminder = "INSERT INTO tb_reminders(task_id, reminder_date, reminder_time, reminder_number, reminder_type) VALUES('$task_id', '$reminder_date[$i]', '$reminder_time[$i]', '$reminder_number[$i]', '$reminder_type[$i]')";
                    mysqli_query($conn, $sqlReminder);
                }
            }
        }
    }

    // Cek apakah ada input di $collaborators
    if ($collaborators != "") {

        // Cek apakah $collaborators adalah array
        if (is_array($collaborators)) {

            // Delete Old Reminder
            $sqlDeleteOldCollaborator = "DELETE FROM tb_collaborators WHERE task_id = '$task_id'";
            mysqli_query($conn, $sqlDeleteOldCollaborator);

            // Hitung panjang array collaborators
            $arrayCollaboratorsLength = count($collaborators);

            // Input collaborators ke database
            for ($i = 0; $i < $arrayCollaboratorsLength; $i++) {

                $sqlCheckProfile = "SELECT id, username FROM tb_users WHERE id = '$collaborators[$i]'";
                $queryCheckProfile = mysqli_query($conn, $sqlCheckProfile);
                $resultCheckProfile = mysqli_fetch_array($queryCheckProfile);
                $username = $resultCheckProfile['username'];
                $user_collaborator_id = $resultCheckProfile['id'];

                // Menggunakan timezone Asia Jakarta
                date_default_timezone_set('Asia/Jakarta');

                // Mendapatkan tanggal dan waktu hari ini
                $currentDate = date('Y-m-d');
                $currentTime = date('H:i:00', time());

                // Menambahkan data ke tabel kolaborator berdasarkan id
                $sqlCollaborator = "INSERT INTO tb_collaborators(collaborator_username, task_id, user_id, added_date, added_time) VALUES('$username', '$task_id', '$user_collaborator_id', '$currentDate', '$currentTime')";
                mysqli_query($conn, $sqlCollaborator);
            }

        }
    }

    if ($query) {
        echo $sql;
        echo "data berhasil diperbarui";
    }

} else if ($act == "loading") {
    $sql = "SELECT t.*, c.category_name, c.category_img, u.username AS owner_username 
                    FROM tb_tasks t 
                    LEFT JOIN tb_categories c ON t.category_id = c.id 
                    LEFT JOIN tb_collaborators collab ON t.id = collab.task_id
                    LEFT JOIN tb_users u ON t.user_id = u.id
                    WHERE (t.user_id = '$user_id' OR collab.user_id = '$user_id') AND t.status_id = 1 
                    GROUP BY t.id
                    ORDER BY t.task_date";



    $query = mysqli_query($conn, $sql);
    while ($result = mysqli_fetch_array($query)) {
        $user_id = $_SESSION['id'];
        $owner_id = $result['user_id'];
        $task_id = $result['id'];
        $task_title = $result['task_name'];
        $task_date = $result['task_date'] == date('Y-m-d') ? 'Today' : date('d-m-Y', strtotime($result['task_date']));
        $task_time = $result['task_time'] == "00:00:00" ? '' : date('H:i', strtotime($result['task_time']));
        $task_desc = $result['task_desc'];
        $category = $result['category_name'];
        $category_img = $result['category_img'];
        $priority_id = $result['priority_id'];
        $owner_username = $result['owner_username'];

        $task_date = $result['task_date'] == date('Y-m-d') ? 'Today' : date('d-m-Y', strtotime($result['task_date']));

        // Menentukan tag pada title task
        $priority_tag = "";
        if ($priority_id == 1) {
            $priority_tag = "Low";
        } else if ($priority_id == "2") {
            $priority_tag = "Medium";
        } else if ($priority_id == "3") {
            $priority_tag = "High";
        }

        if ($result['task_date'] < date('Y-m-d')) {
            $task_date = '<span style="color: red; font-family: \'Satoshi-Bold\';">' . $task_date . '</span>';
            $status_id = 3;
        }

        // Cek apakah task termasuk collaborator atau bukan
        $sqlCheckTask = "SELECT * FROM tb_collaborators WHERE task_id = '$task_id'";
        $queryCheckTask = mysqli_query($conn, $sqlCheckTask);
        $numRowsCheckTask = mysqli_num_rows($queryCheckTask);
        ?>

                                            <div class="task_active_card">
                                                <div class="task_category">
                                                    <img class="task_category_img" src="./assets/images/category/<?php echo $category_img ?>" alt="">
                                                </div>
                                                <div class=" task_info">
                                                    <div class="task_subtitle">
                                                        <p>
                            <?php
                            if ($numRowsCheckTask > 0) {
                                ?>
                                                                <span class="team_icon">
                                                                    <i class="fa-solid fa-people-group" style="color: #ffffff;"></i> </span>
                            <?php
                            }
                            ?>
                        <?php echo $task_title; ?>
                                                            <span class="priority_tag">
                            <?php echo $priority_tag; ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="task_deadline">
                                                        <i class="fa-solid fa-clock" style="color: white;"></i>
                                                        <p>
                        <?php echo $task_date; ?>
                                                        </p>
                                                    </div>
                                                    <div class="task_desc">
                                                        <p>
                        <?php echo $task_desc; ?>
                                                        </p>
                                                    </div>
                    <?php
                    if ($numRowsCheckTask > 0) {
                        ?>
                                                        <div class="creator_info">
                                                            <p><span class="creator_icon"><i class="fa-solid fa-user-gear" style="color: #ffffff;"></i></span>Created by
                            <?php echo $owner_username; ?>
                                                            </p>
                                                        </div>
                    <?php
                    }
                    ?>
                                                </div>

                <?php

                if ($user_id == $owner_id) {
                    // Jika user adalah owner (pembuat task), tampilkan tombol edit, delete, dan checkbox
                    ?>
                                                    <div class="task_checkbox">
                                                        <input type="checkbox" id="done<?php echo $task_id; ?>" onclick="check_task(<?php echo $task_id; ?>)" />
                                                        <button type="button" id="delete_done<?php echo $task_id; ?>" onclick="delete_task(<?php echo $task_id; ?>)"
                                                            class="button_delete" value="Delete">
                                                            <p style="cursor:pointer">Delete</p>
                                                        </button>
                                                        <button type="button" style="cursor:pointer" id="update_undone<?php echo $task_id; ?>"
                                                            onclick="editTask(<?php echo $task_id; ?>)" id="edit_task_button<?php echo $task_id; ?>"
                                                            class="button_update" value="Edit">
                                                            <p style="cursor:pointer">Edit</p>
                                                        </button>
                                                    </div>
                <?php
                } else {
                    // Jika user adalah collaborator, tampilkan hanya task checkbox tanpa tombol edit dan delete
                    ?>
                                                    <div class="task_checkbox">
                                                        <input type="checkbox" id="done<?php echo $task_id; ?>" onclick="check_task(<?php echo $task_id; ?>)"
                                                            disabled />
                                                    </div>
                <?php
                }
                ?>
                                            </div>
                                            <br>
        <?php
    }

} else if ($act == "completed") {
    $sql = "SELECT t.*, c.category_name, c.category_img FROM tb_tasks t LEFT JOIN tb_categories c ON t.category_id = c.id WHERE user_id = '$user_id' AND status_id = 2";
    $query = mysqli_query($conn, $sql);
    while ($result = mysqli_fetch_array($query)) {
        $task_id = $result['id'];
        $task_title = $result['task_name'];
        $task_date = $result['task_date'] == date('Y-m-d') ? 'Today' : date('d-m-Y', strtotime($result['task_date']));
        $task_time = $result['task_time'] == "00:00:00" ? '' : date('H:i', strtotime($result['task_time']));
        $task_desc = $result['task_desc'];
        $category = $result['category_name'];
        $category_img = $result['category_img'];

        ?>
                                                <div class="task_active_card">
                                                    <div class="task_category">
                                                        <img class="task_category_img" src="./assets/images/category/<?php echo $category_img ?>" alt="">
                                                    </div>
                                                    <div class=" task_info">
                                                        <div class="task_subtitle">
                                                            <p>
                        <?php echo $task_title; ?>
                                                            </p>
                                                        </div>
                                                        <div class="task_deadline">
                                                            <i class="fa-solid fa-clock" style="color: white;"></i>
                                                            <p>
                        <?php echo $task_date; ?>
                                                            </p>
                                                        </div>
                                                        <div class="task_desc">
                                                            <p>
                        <?php echo $task_desc; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="task_checkbox">
                                                        <input type="checkbox" id="done<?php echo $task_id; ?>" onclick="uncheck_task(<?php echo $task_id; ?>)"
                                                            checked />
                                                        <button type="button" id="delete_done<?php echo $task_id; ?>" onclick="delete_task(<?php echo $task_id; ?>)"
                                                            class="button_delete" value="Delete">
                                                            <p style="cursor:pointer">Delete</p>
                                                        </button>
                                                        <button type="button" style="cursor:pointer" id="update_undone<?php echo $task_id; ?>"
                                                            onclick="editTask(<?php echo $task_id; ?>)" id="edit_task_button<?php echo $task_id; ?>"
                                                            class="button_update" value="Edit">
                                                            <p style="cursor:pointer">Edit</p>
                                                        </button>
                                                    </div>
                                                </div>
                                                <br>
        <?php
    }
} else if ($act == "filter") {
    $fromDate = $_POST['task_date_filter_from'];
    $toDate = $_POST['task_date_filter_to_date'];
    $statusFilter = $_POST['status_id_filter'];

    $sql = "SELECT t.*, c.category_name, c.category_img FROM tb_tasks t LEFT JOIN tb_categories c ON t.category_id = c.id WHERE user_id = '$user_id'";

    if ($statusFilter == "") {
        $sql .= " AND status_id = '$id'";
    } else if ($statusFilter == "1") {
        $sql .= " AND status_id = 1";
    } else if ($statusFilter == "2") {
        $sql .= " AND status_id = 2";
    } else if ($statusFilter == "3") {
        $sql .= " AND status_id = 3";
    }


    if ($fromDate !== '' && $toDate !== '') {
        $sql .= "AND (t.task_date BETWEEN DATE('$fromDate') AND DATE('$toDate'))";
    } else if ($fromDate !== '' && $toDate == '') {
        $sql .= "AND (t.task_date BETWEEN DATE('$fromDate') AND DATE(0000-00-00))";
    } else if ($fromDate == '' && $toDate !== '') {
        $sql .= "AND (t.task_date BETWEEN DATE(0000-00-00) AND DATE('$toDate'))";
    }

    $sql .= " ORDER BY t.task_date ASC";

    $query = mysqli_query($conn, $sql);
    while ($result = mysqli_fetch_array($query)) {
        $task_id = $result['id'];
        $task_title = $result['task_name'];
        $task_date = $result['task_date'] == date('Y-m-d') ? 'Today' : date('d-m-Y', strtotime($result['task_date']));
        $task_time = $result['task_time'] == "00:00:00" ? '' : date('H:i', strtotime($result['task_time']));
        $task_desc = $result['task_desc'];
        $category = $result['category_name'];
        $category_img = $result['category_img'];
        ?>

                                                    <div class="task_active_card">
                                                        <div class="task_category">
                                                            <img class="task_category_img" src="./assets/images/category/<?php echo $category_img ?>" alt="">
                                                        </div>
                                                        <div class="task_info">
                                                            <div class="task_subtitle">
                                                                <p>
                        <?php echo $task_title; ?>
                                                                </p>
                                                            </div>
                                                            <div class="task_deadline">
                                                                <i class="fa-solid fa-clock" style="color: white;"></i>
                                                                <p>
                        <?php echo $task_date; ?>
                                                                </p>
                                                                <p>⠀
                        <?php echo $task_time; ?>
                                                                </p>
                                                            </div>
                                                            <div class="task_desc">
                                                                <p>
                        <?php echo $task_desc; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="task_checkbox">
                                                            <input type="checkbox" id="undone<?php echo $task_id; ?>" onclick="check_task(<?php echo $task_id; ?>)"
                                                                selected />
                                                            <button type="button" style="cursor:pointer" id="delete_undone<?php echo $task_id; ?>"
                                                                onclick="delete_task(<?php echo $task_id; ?>)" class="button_delete" value="Delete">
                                                                <p style="cursor:pointer">Delete</p>
                                                            </button>
                                                            <button type="button" style="cursor:pointer" id="update_undone<?php echo $task_id; ?>"
                                                                onclick="editTask(<?php echo $task_id; ?>)" class="button_update" value="Edit">
                                                                <p style="cursor:pointer">Edit</p>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <br>
        <?php
    }
}
?>