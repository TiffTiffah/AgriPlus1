<?php
// Connect to the database (modify database credentials as needed)
$conn = new mysqli("localhost", "root", "", "agri");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_select = "SELECT * FROM tasks WHERE dueDate < CURRENT_DATE() AND status IN ('pending')";
$result = $conn->query($sql_select);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["taskID"]. " - Name: " . $row["taskName"]. " " . $row["dueDate"]. "<br>";
    }
    echo_json_encode($result);
} else {
    echo "0 results";
}



// Query tasks that have passed their due date and are still pending
$sql = "UPDATE tasks SET status = 'due' WHERE dueDate < CURRENT_DATE() AND status IN ('pending')";
if ($conn->query($sql) === TRUE) {


    echo "Task statuses updated successfully.";
} else {
    echo "Error updating task statuses: " . $conn->error;
}

// Close database connection
$conn->close();
?>
