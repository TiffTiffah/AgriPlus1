<?php
    // Connect to your database (modify database credentials as needed)
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['taskId'])) {
        // Retrieve task ID from the AJAX request
        $taskId = $_POST['taskId'];
    
        // Connect to the database (modify database credentials as needed)
        $conn = new mysqli("localhost", "root", "", "agri");
    
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        // Prepare and execute SQL UPDATE statement to update task status
        $update_query = "UPDATE tasks SET status = 'Completed' WHERE taskId = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
    
        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            // Fetch the updated status from the database
            $fetch_query = "SELECT status FROM tasks WHERE taskId = ?";
            $stmt_fetch = $conn->prepare($fetch_query);
            $stmt_fetch->bind_param("i", $taskId);
            $stmt_fetch->execute();
            $result = $stmt_fetch->get_result();
            $row = $result->fetch_assoc();
            $newStatus = $row['status'];
    
            // Send JSON response with the new status
            $response = array('success' => true, 'newStatus' => $newStatus);
        } else {
            // If the update failed, return false in the response
            $response = array('success' => false);
        }
    
        // Close database connection and statements
        $stmt->close();
        $stmt_fetch->close();
        $conn->close();
    
        // Send JSON response
        header('Content-type: application/json');
        echo json_encode($response);
    } else {
        // Invalid request method or missing parameters
        $response = array('success' => false);
        header('Content-type: application/json');
        echo json_encode($response);
    }
    ?>