<?php
// Include the PHP script to test
include 'signup.php';

// Define a function to simulate form submission with given data
function simulate_registration($fullname, $username, $email, $password, $confirm_password) {
    $_POST["fullname"] = $fullname;
    $_POST["username"] = $username;
    $_POST["email"] = $email;
    $_POST["password"] = $password;
    $_POST["confirm_password"] = $confirm_password;
    
    // Call the function that handles form submission
    register_user();
}

// Define test cases
function run_test_cases() {
    // Test case 1: Valid registration
    simulate_registration("John Doe", "johndoe", "johndoe@example.com", "password123", "password123");
    
    // Test case 2: Invalid email format
    simulate_registration("Jane Smith", "janesmith", "invalidemail", "password456", "password456");
    
    // Test case 3: Email already taken
    simulate_registration("Alice Johnson", "alicejohnson", "johndoe@example.com", "password789", "password789");
    
    // Test case 4: Password and confirm password mismatch
    simulate_registration("Bob Brown", "bobbrown", "bobbrown@example.com", "password123", "password456");
    
    // Test case 5: Empty fields
    simulate_registration("", "", "", "", "");
}

// Execute the test cases
run_test_cases();
