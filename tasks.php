<?php

session_start();

// Check if user is logged in (assuming you have a user authentication system)
if (!isset($_SESSION["user_id"])) {
    // Redirect to the sign-in page or display an error message
    header("Location: signin.html");
    exit();
}

// Connect to your database (modify database credentials as needed)
$conn = new mysqli("localhost", "root", "", "agri");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL statement to fetch farm ID associated with the user
$sql_fetch_farm_id = "SELECT FarmID FROM farms WHERE UserID = ?";
$stmt_fetch_farm_id = $conn->prepare($sql_fetch_farm_id);

if ($stmt_fetch_farm_id === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt_fetch_farm_id->bind_param("i", $_SESSION["user_id"]);

// Execute SQL statement
$stmt_fetch_farm_id->execute();

// Get result
$result_fetch_farm_id = $stmt_fetch_farm_id->get_result();

// Fetch FarmID
$row_fetch_farm_id = $result_fetch_farm_id->fetch_assoc();
$farm_id = $row_fetch_farm_id["FarmID"];

// Close statement
$stmt_fetch_farm_id->close();


//
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
  // Retrieve form data
  $taskName = $_POST['taskName'];
  $dueDate = $_POST['dueDate'];

  // echo $taskName;
  // echo $dueDate;

  //insert data into the database
  $insert_task = "INSERT INTO tasks (TaskName, DueDate, FarmID) VALUES (?, ?, ?)";
  $stmt_insert_task = $conn->prepare($insert_task);

  if ($stmt_insert_task === false) {
      die("Error preparing SQL statement: " . $conn->error);
  }

  // Bind parameters
  $stmt_insert_task->bind_param("ssi", $taskName, $dueDate, $farm_id);

  // Execute statement
  $stmt_insert_task->execute();

  // Check if task was added successfully

  // if($stmt_insert_task->affected_rows > 0){
  //     echo "<script>alert('Task added successfully');</script>";
  // }

  // Close statement
  $stmt_insert_task->close();

  // Redirect to the same page to prevent form resubmission
  header("Location: tasks.php");
  exit();


// Close database connection
$conn->close();

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="analytics.css">
    <link rel="stylesheet" href="css/tasks.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="script.js"></script>
    <title>AgriPlus | Tasks</title>
</head>
<body>
       <div class="dash-container">
        <!-- ----------------------------sidebar-------------------------------- -->
        <div class="side-bar">
        <div class="logo">
                <img src="images/logo (1).png" alt="logo">
                <h3>AGRI</h3><span>PLUS</span>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Overview</a></li>
                    <li><a href="crops.php"><i class='fa-solid fa-seedling'></i>Crops</a></li>
                    <li><a href="tasks.php" class="active"><i class='bx bx-task'></i>Tasks</a></li>
                    <li><a href="analytics.php"><i class='bx bxs-report' ></i>Analytics</a></li>
                    <li><a href="help.php"><i class='bx bx-help-circle' ></i></i>Help</a></li>
                    <li><a href="logout.php"><i class='bx bx-exit'></i>Logout</a></li>
                </ul>
        </div>
       </div> 
        <!-- ----------------------------main content-------------------------------- -->
        <div class="main-content">

          <div class="left-side">
            <div class="t-top">

              <div></div>

              <div id="taskModal" class="modal">
                <div class="modal-content">
                  <span class="close" onclick="closeModal()">&times;</span>
                  <h2>Add New Task</h2>
                  <form id="taskForm" method="POST" action="tasks.php">
                      <label for="taskName">Task:</label><br>
                      <input type="text" id="taskName" name="taskName" required><br><br>
                      
                      <label for="dueDate">Due Date:</label><br>
                      <input type="date" id="dueDate" name="dueDate" required><br><br>
                      
                      <button type="submit" name="submit">Add Task</button>
                  </form>
                </div>
              </div>

              <div class="add-task-btn">
                  <button onclick="openModal()"><i class='bx bx-plus'></i>Add Task</button>
              </div>
          </div>


              <div class="page-content">
<?php
// Fetch tasks from the database for the current user
$sql_fetch_tasks = "SELECT * FROM tasks WHERE FarmID = ?";
$stmt_fetch_tasks = $conn->prepare($sql_fetch_tasks);
$stmt_fetch_tasks->bind_param("i", $farm_id);
$stmt_fetch_tasks->execute();
$result_fetch_tasks = $stmt_fetch_tasks->get_result();

// Initialize arrays to store tasks for today, upcoming, and due tasks
$today_tasks = [];
$other_tasks = [];
$due_tasks = [];

// Iterate over the fetched tasks and separate them based on their due dates
while ($row = $result_fetch_tasks->fetch_assoc()) {
    if (date('Y-m-d', strtotime($row['dueDate'])) === date('Y-m-d')) {
        $today_tasks[] = $row;
    } elseif (strtotime($row['dueDate']) < time() && $row['status'] != 'Completed') {
        $due_tasks[] = $row;
    } else if (strtotime($row['dueDate']) > time()){
        $other_tasks[] = $row;
    }
}

// Display tasks for today
echo '<div class="header">Today Tasks</div>';
echo '<div class="tasks-wrapper">';
if (!empty($today_tasks)) {
    foreach ($today_tasks as $task) {
        echo '<div class="task">';
        // Add the 'checked' attribute only if the task is completed
        if ($task['status'] == 'Completed') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo '<input class="task-item" name="task" type="checkbox" id="item-' . $task['taskID'] . '" ' . $checked . '>';
        echo '<label for="item-' . $task['taskID'] . '">';
        echo '<span class="label-text">' . $task['taskName'] . '</span>';
        echo '</label>';
        
        // Add buttons based on task status
        echo '<div class="status-buttons">';
        if ($task['status'] == 'Completed') {
            echo '<button class="status-btn completed">Completed</button>';
        } elseif ($task['status'] == 'pending') {
            echo '<button class="status-btn pending">Pending</button>';
        } else {
            echo '<button class="status-btn due">Due</button>';
        }
        echo '</div>';
        
        echo '</div>';
    }
} else {
    echo '<p>No tasks for today</p>';
}
echo '</div>'; // Close tasks-wrapper



// Display upcoming tasks
echo '<div class="header">Upcoming Tasks</div>';
echo '<div class="tasks-wrapper">';
if (!empty($other_tasks)) {
    foreach ($other_tasks as $task) {
        echo '<div class="task">';
        // Add the 'checked' attribute only if the task is completed
        if ($task['status'] == 'Completed') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo '<input class="task-item" name="task" type="checkbox" id="item-' . $task['taskID'] . '" ' . $checked . '>';
        echo '<label for="item-' . $task['taskID'] . '">';
        echo '<span class="label-text">' . $task['taskName'] . '</span>';
        echo '</label>';
        
        // Add buttons based on task status
        echo '<div class="status-buttons">';
        if ($task['status'] == 'Completed') {
            echo '<button class="status-btn completed">Completed</button>';
        } elseif ($task['status'] == 'pending') {
            echo '<button class="status-btn pending">Pending</button>';
        } else {
            echo '<button class="status-btn due">Due</button>';
        }
        echo '</div>';
        
        echo '</div>';
    }
} else {
    echo '<p>No upcoming tasks</p>';
}
echo '</div>'; // Close tasks-wrapper


// Display due tasks
echo '<div class="header">Due Tasks</div>';
echo '<div class="tasks-wrapper">';
if (!empty($due_tasks)) {
    foreach ($due_tasks as $task) {
        echo '<div class="task">';
        // Add the 'checked' attribute only if the task is completed
        if ($task['status'] == 'Completed') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo '<input class="task-item" name="task" type="checkbox" id="item-' . $task['taskID'] . '" ' . $checked . '>';
        echo '<label for="item-' . $task['taskID'] . '">';
        echo '<span class="label-text">' . $task['taskName'] . '</span>';
        echo '</label>';
        
        // Determine task status
        $task_status = '';
        if ($task['status'] == 'Completed') {
            $task_status = 'completed';
        } elseif ($task['status'] == 'pending') {
            $task_status = 'pending';
        } elseif (strtotime($task['dueDate']) < time()) {
            $task_status = 'due';
        }
        
        // Add buttons based on task status
        echo '<div class="status-buttons">';
        echo '<button class="status-btn ' . $task_status . '">' . ucfirst($task_status) . '</button>';
        echo '</div>';
        
        echo '</div>';
    }
} else {
    echo '<p>No due tasks</p>';
}
echo '</div>'; // Close tasks-wrapper
?>


                  </div>
                  </div>




                <div class="right-sidey">
                  <div class="calendar-container">
                      <div class="calendar-header">
                        <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                        <div id="month-year"></div>
                        <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                      </div>
                      <div id="calendar"></div>
                    </div>
        
                    <div class="task-progress">
                      <h4>Tasks Progress</h4>
                      <div class="chart">
                          <div id="chart">
                          </div>
                      </div>
        
                    </div>
        
              </div>

       

        



        <!-- ----------------------------------Right side-------------------------------- -->




<script>
    // Get the input element for due date
var dueDateInput = document.getElementById('dueDate');

// Get today's date in the format yyyy-mm-dd
var today = new Date().toISOString().split('T')[0];

// Set the minimum date to today
dueDateInput.min = today;


        // Add event listener to checkboxes
        document.querySelectorAll('.task-item').forEach(item => {
            item.addEventListener('click', function() {
                const taskId = this.id.split('-')[1]; // Extract task ID from checkbox ID
                updateTaskStatus(taskId);
            });
        });

// Function to update task status
function updateTaskStatus(taskId) {
    // Send AJAX request to update_task_status.php
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_task_status.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Check if the status is updated successfully
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Update status on the client side
                const statusButton = document.querySelector(`#status-button-${taskId}`);
                if (statusButton) {
                    statusButton.textContent = response.newStatus;
                    // Optionally, you can update the styling of the status button based on the new status
                    statusButton.classList.remove('pending', 'completed', 'due');
                    statusButton.classList.add(response.newStatus.toLowerCase());
                }
            } else {
                console.error('Failed to update task status');
            }
        } else {
            console.error('Error:', xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send(`taskId=${taskId}`);
}



        // Function to periodically check for overdue tasks
function checkForOverdueTasks() {
    // Send AJAX request to overdue_tasks_checker.php
    const xhrTaskChecker = new XMLHttpRequest();
    xhrTaskChecker.open('GET', 'task_checker.php', true);
    xhrTaskChecker.onload = function() {
        if (xhrTaskChecker.status === 200) {
            console.log('Overdue tasks checked successfully');
        } else {
            console.error('Error:', xhrTaskChecker.statusText);
        }
    };
    xhrTaskChecker.onerror = function() {
        console.error('Request failed');
    };
    xhrTaskChecker.send();
}

// Set interval to check for overdue tasks every 24 hours (adjust as needed)
checkForOverdueTasks();





const calendarHeader = document.getElementById('month-year');
const calendarContainer = document.getElementById('calendar');
const prevMonthButton = document.getElementById('prev-month');
const nextMonthButton = document.getElementById('next-month');

let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

// Function to generate calendar
function generateCalendar() {
  const currentDate = new Date();

  const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
  const firstDayOfMonth = new Date(currentYear, currentMonth, 1).getDay();

  const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  let calendarHTML = '';

  // Add month and year to calendar header
  calendarHeader.textContent = getMonthName(currentMonth) + ' ' + currentYear;

  // Add days of the week headers
  calendarHTML += '<div class="day-header">' + daysOfWeek.join('</div><div class="day-header">') + '</div>';

  // Add empty cells for days before the first day of the month
  for (let i = 0; i < firstDayOfMonth; i++) {
    calendarHTML += '<div class="day"></div>';
  }

  // Add days of the month
  for (let day = 1; day <= daysInMonth; day++) {
    const isCurrentDay = day === currentDate.getDate() && currentMonth === currentDate.getMonth() && currentYear === currentDate.getFullYear();
    const classList = isCurrentDay ? 'day current-day' : 'day current-month-day';
    calendarHTML += `<div class="${classList}">${day}</div>`;
  }

  calendarContainer.innerHTML = calendarHTML;
}

// Function to get month name
function getMonthName(month) {
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  return months[month];
}

// Event listener for previous month button
prevMonthButton.addEventListener('click', () => {
  if (currentMonth === 0) {
    currentMonth = 11;
    currentYear--;
  } else {
    currentMonth--;
  }
  generateCalendar();
});

// Event listener for next month button
nextMonthButton.addEventListener('click', () => {
  if (currentMonth === 11) {
    currentMonth = 0;
    currentYear++;
  } else {
    currentMonth++;
  }
  generateCalendar();
});

// Generate calendar when the page loads
generateCalendar();




// Function to fetch task completion percentage and update the chart
function updateTaskProgressChart() {
    // Send AJAX request to fetch task completion percentage
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'calculate_task_completion.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
          console.log(xhr.responseText);
            // Parse response JSON data
            const completionPercentage = JSON.parse(xhr.responseText);

            // Draw the chart
            const options = {
                chart: {
                    height: 280,
                    type: "radialBar"
                },
                labels: ["Tasks Completed"],
                series: [completionPercentage],
                colors: ["#45a049"],
                plotOptions: {
                    radialBar: {
                        hollow: {
                            margin: 10,
                            size: "70%"
                        },
                        dataLabels: {
                            showOn: "always",
                            name: {
                                offsetY: -10,
                                show: true,
                                color: "#888",
                                fontSize: "15px",
                                fontFamily: 'Montserrat'
                            },
                            value: {
                                offsetY: -2,
                                color: "#111",
                                fontSize: "15px",
                                fontWeight: "bold",
                                fontFamily: 'Montserrat',
                                show: true
                            },
                        }
                    }
                },
                stroke: {
                    lineCap: "round",
                },
            };

            const chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        } else {
            console.error('Error:', xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send();
}

// Call the function to update the chart
updateTaskProgressChart();





  

  //task modal
   // Get the modal
   var modal = document.getElementById('taskModal');

// Function to open the modal
function openModal() {
    modal.style.display = 'block';
}

// Function to close the modal
function closeModal() {
    modal.style.display = 'none';
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

// Handle form submission

        </script>
</body>
</html>