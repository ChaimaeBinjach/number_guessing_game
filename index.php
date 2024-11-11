<?php
/*
Name : Chaimae Binjach
Neptun Code: GV6WH9
Date : 29/10/2024
Assignment 3 : Task3
*/


/*Task3: Make a game, based on the lesson examples, that:
use session
make a 5-digit number (the digits can be repeated)
5 input fields to guess the number per number
if a digit is in the right place, indicate it
if a digit is part of the number, also indicate it in a different way.
If you hit the number, the task is over.
In all cases, give the possibility to generate a new number
Save all steps!
Use wordle as a sample
*/


/*
The game is a number guessing game where the player has to guess a 5-digit number. 
The player can enter 5 digits in the input fields and submit the guess. 
The game provides feedback for each digit in the guess, indicating whether the digit is correct, misplaced, or wrong. 
The player wins the game by guessing the number correctly. 
The game allows the player to start a new game at any time. All guesses and feedback are saved and displayed to the player.
*/


/*
This game leverages PHP for server-side processing and session management.
The user interface is crafted using HTML and styled with CSS to ensure a visually appealing experience.
The core game logic, implemented in PHP, manages the generation of the target number, processes player guesses, and provides feedback based on those guesses.
All guesses and associated feedback are stored in the session, allowing for real-time display to the player.
Players have the option to start a new game at any time by clicking the "New Game" button, facilitating an uninterrupted gaming experience.
Feedback for each digit in the guess indicates whether it is correct, misplaced, or incorrect, enhancing player understanding of their attempts.
A success message is displayed when the player successfully guesses the target number, providing a rewarding experience.
Input validation is performed using JavaScript to ensure that only numeric digits are accepted, enhancing the game's reliability.
CSS is employed for comprehensive styling, including color schemes, font choices, and engaging animations.
The game features a responsive design, ensuring optimal performance across various screen sizes.
Emojis are utilized to enhance user engagement and provide visual feedback throughout the gameplay.
A gradient background is integrated to create an aesthetically pleasing interface that attracts users.
Feedback messages and buttons are animated to create a more engaging and interactive experience for players.
Flexbox is used for layout purposes, effectively centering content both vertically and horizontally for a balanced look.
The code is well-commented to improve readability and help explain the functionality of various sections.
PHP prepared statements are utilized for securely inserting guesses and feedback into the database, promoting security and efficiency.
A new database table is created for storing guesses and feedback if it does not already exist, ensuring data persistence.
Error logging is implemented to track any issues encountered during database operations, aiding in debugging and maintenance.
Feedback is stored as a JSON array, which is converted to a string for seamless storage in the database.
The game enhances security by regenerating the session ID, preventing session fixation attacks and ensuring player safety.
A function is utilized to generate a 5-digit random number, ensuring it is appropriately padded to maintain a consistent format.
A new game session is initialized whenever the player starts a new game or resets the current game, providing a fresh start.
 */


?>

<?php
session_start(); // Start or resume the session to track game data across page loads

// Database credentials
define('DB_HOST', 'localhost'); // Define the hostname for the database server
define('DB_USER', 'root'); // Define the username for the database connection
define('DB_PASS', ''); // Define the password for the database connection
define('DB_NAME', 'number_guessing_game'); // Define the name of the database to connect to

// Create a new connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); // Instantiate a MySQLi object with database credentials

// Check the connection
if ($mysqli->connect_error) { // Check if there is a connection error
    die("Connection failed: " . $mysqli->connect_error); // Stop the script and output the error if connection fails
}

// Create table if it doesn't exist
if (!$mysqli->query("CREATE TABLE IF NOT EXISTS guesses ( 
        id INT AUTO_INCREMENT PRIMARY KEY, 
        guess VARCHAR(5) NOT NULL, 
        feedback JSON NOT NULL, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
    )")) {
    die("Table creation failed: " . $mysqli->error); // Output an error if table creation fails and stop script execution
}

// Function to generate a 5-digit number
function generateNumber() { // Define a function to create a 5-digit random number
    return str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT); // Generate a number, pad it to ensure 5 digits, and return it
}

// Initialize a new game
function initializeGame() { // Define a function to set up a new game session
    $_SESSION['target_number'] = generateNumber(); // Store the generated number in session as the target to guess
    $_SESSION['guesses'] = []; // Initialize an empty array to store guesses and feedback
    session_regenerate_id(true); // Regenerate the session ID to enhance security
}

// Handle the user's guess and provide feedback
function processGuess($guess) { // Define a function to handle the guess and generate feedback
    global $mysqli; // Use the global $mysqli database connection
    $target = $_SESSION['target_number']; // Retrieve the target number from the session
    $feedback = []; // Initialize an empty array to store feedback for each digit

    // Provide feedback for each digit of the guess
    for ($i = 0; $i < 5; $i++) { // Loop through each digit in the guess
        if ($guess[$i] === $target[$i]) { // Check if the guessed digit matches the target at the same position
            $feedback[$i] = "correct"; // Mark it as "correct" if it matches
        } elseif (strpos($target, $guess[$i]) !== false) { // Check if the guessed digit exists elsewhere in the target
            $feedback[$i] = "misplaced"; // Mark it as "misplaced" if it exists but is in the wrong position
        } else {
            $feedback[$i] = "wrong"; // Mark it as "wrong" if it does not exist in the target
        }
    }

    // Save the guess and feedback in the session
    $_SESSION['guesses'][] = ['guess' => $guess, 'feedback' => $feedback]; // Append guess and feedback to session guesses

    // Store in database
    $feedback_json = json_encode($feedback); // Convert the feedback array to JSON format for storage
    $stmt = $mysqli->prepare("INSERT INTO guesses (guess, feedback) VALUES (?, ?)"); // Prepare an SQL statement for insertion
    $stmt->bind_param("ss", $guess, $feedback_json); // Bind guess and feedback JSON to the statement
    
    // Execute and check for errors
    if (!$stmt->execute()) { // Execute the SQL statement and check for success
        error_log("Database insert error: " . $stmt->error); // Log an error message if the insertion fails
    }
    $stmt->close(); // Close the statement to free resources
}

// Start a new game or reset if requested
if (!isset($_SESSION['target_number']) || isset($_POST['new_game'])) { // Check if a game needs to be started or reset
    initializeGame(); // Call the function to initialize a new game
}

// Check if the user submitted a guess
$guess = ''; // Initialize an empty string for the user's guess
if (isset($_POST['guess'])) { // Check if the form was submitted with a guess
    $guess = implode('', array_map('trim', $_POST['digit'])); // Collect the digits, trim whitespace, and combine into a single string

    // Ensure the guess is exactly 5 digits
    if (preg_match('/^\d{5}$/', $guess)) { // Validate the guess format to ensure it is exactly 5 digits
        processGuess($guess); // Call the processGuess function if the guess is valid
    } else {
        echo "<script>alert('Please enter exactly 5 digits.');</script>"; // Show an alert if the guess is invalid
    }
}

// Close the database connection
$mysqli->close(); // Close the database connection to free up resources
?>


<!DOCTYPE html>
<html lang="en"> <!-- Declares the document type as HTML and sets the language to English -->
<head>
    <meta charset="UTF-8"> <!-- Sets the character encoding for the document to UTF-8 for broad character support -->
    <title>Number Guessing Game</title> <!-- Specifies the title of the page, displayed in the browser tab -->
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box; /* Ensures consistent box-sizing for all elements */
        }
        body {
            display: flex; /* Enables flexbox layout for centering */
            justify-content: center; /* Horizontally centers content */
            align-items: center; /* Vertically centers content */
            min-height: 100vh; /* Sets minimum height to full viewport */
            background: linear-gradient(135deg, #a8e0ff 0%, #e0e7ff 100%); /* Applies a gradient background */
            font-family: 'Arial', sans-serif; /* Sets the font */
            color: #2d3748; /* Base color for text */
        }
        .container {
            background: #ffffff; /* Background color for the main container */
            border-radius: 20px; /* Rounds container corners */
            padding: 50px; /* Sets padding around container content */
            max-width: 700px; /* Maximum width for the container */
            width: 90%; /* Container width at 90% of its parent */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); /* Adds shadow effect */
            text-align: center; /* Centers text inside the container */
            transition: transform 0.3s ease; /* Adds a smooth transition on hover */
        }
        .container:hover {
            transform: translateY(-5px); /* Slightly lifts the container on hover */
        }
        h1 {
            font-size: 2.8rem; /* Sets font size for the main heading */
            color: #4a5568; /* Color for the heading */
            margin-bottom: 30px; /* Space below the heading */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1); /* Adds subtle shadow to heading */
        }
        form {
            display: flex; /* Flex container for form elements */
            flex-direction: column; /* Arranges children in a column */
            align-items: center; /* Centers form elements */
            gap: 20px; /* Space between form elements */
        }
        .input-group {
            display: flex; /* Flex container for input fields */
            justify-content: center; /* Centers input fields */
            gap: 10px; /* Space between inputs */
        }
        .input-group input[type="text"] {
            width: 70px; /* Width of each input */
            height: 70px; /* Height of each input */
            font-size: 32px; /* Font size for input text */
            text-align: center; /* Centers text in input */
            border-radius: 15px; /* Rounds input corners */
            border: 2px solid #4a5568; /* Border color and width */
            background-color: #edf2f7; /* Background color for inputs */
            color: #2d3748; /* Text color */
            transition: all 0.3s ease; /* Smooth transition on focus */
        }
        .input-group input[type="text"]:focus {
            border-color: #38a169; /* Changes border color on focus */
            outline: none; /* Removes default outline */
            background-color: #f7fafc; /* Changes background color on focus */
        }
        .btn {
            padding: 15px 30px; /* Padding around button text */
            background: #38a169; /* Background color for button */
            color: #ffffff; /* Text color */
            font-size: 1.2rem; /* Font size */
            font-weight: bold; /* Bold text */
            border-radius: 15px; /* Rounds button corners */
            border: none; /* Removes default border */
            cursor: pointer; /* Changes cursor to pointer */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth hover effects */
        }
        .btn:hover {
            background: #2f855a; /* Darker background on hover */
            transform: translateY(-3px); /* Lifts button on hover */
        }
        .feedback-section {
            margin-top: 30px; /* Space above feedback section */
            text-align: left; /* Left-aligns feedback */
        }
        .feedback-section h2 {
            font-size: 1.6rem; /* Font size for feedback heading */
            margin-bottom: 15px; /* Space below heading */
            color: #4a5568; /* Color for feedback heading */
        }
        .feedback-section ul {
            list-style: none; /* Removes default bullet points */
            padding: 0; /* Removes default padding */
        }
        .feedback-section li {
            display: flex; /* Flex container for feedback items */
            align-items: center; /* Centers items vertically */
            justify-content: space-between; /* Space between feedback text and icon */
            padding: 15px; /* Padding around feedback */
            margin: 10px 0; /* Space between feedback items */
            background: #e2e8f0; /* Background color for feedback item */
            border-radius: 10px; /* Rounds feedback item corners */
            font-size: 1.1rem; /* Font size for feedback */
            color: #2d3748; /* Text color */
            border-left: 6px solid transparent; /* Placeholder for colored border */
        }
        .correct {
            color: #2ecc71; /* Green color for correct feedback */
            border-color: #2ecc71; /* Green border for correct feedback */
        }
        .misplaced {
            color: #f39c12; /* Orange color for misplaced feedback */
            border-color: #f39c12; /* Orange border for misplaced feedback */
        }
        .wrong {
            color: #e74c3c; /* Red color for wrong feedback */
            border-color: #e74c3c; /* Red border for wrong feedback */
        }
        .result-message {
            margin-top: 30px; /* Space above result message */
            color: #2ecc71; /* Green color for success message */
            font-size: 2rem; /* Font size for result message */
            font-weight: bold; /* Bold text */
            animation: fadeIn 1s ease; /* Adds fade-in animation */
        }

        /* Animations */
        @keyframes fadeIn {
            0% {
                opacity: 0; /* Starts with transparent */
                transform: scale(0.9); /* Starts slightly scaled down */
            }
            100% {
                opacity: 1; /* Fully visible at end */
                transform: scale(1); /* Scales to normal size */
            }
        }
    </style>
</head>

<body> <!-- Start of the body section of the HTML document -->
    <div class="container"> <!-- Container div to hold the content with styling -->
        <h1>🔢 Number Guessing Game</h1> <!-- Main heading for the game with an emoji -->

        <?php if ($guess && $guess === $_SESSION['target_number']): ?> <!-- Check if the guess is made and if it matches the target number -->
            <p class="result-message">🎉 Congratulations! You guessed the number <strong><?php echo htmlspecialchars($_SESSION['target_number']); ?></strong> correctly!</p> <!-- Message displayed when the correct guess is made, showing the guessed number -->
            <form method="post"> <!-- Form for submitting the play again request -->
                <button class="btn" type="submit" name="new_game">Play Again</button> <!-- Button to start a new game -->
            </form>
        <?php else: ?> <!-- If the guess is not correct, display the following form -->
            <form method="post" onsubmit="return validateInputs();"> <!-- Form for making a guess, with a validation function on submission -->
                <div class="input-group"> <!-- Input group div for styling input fields -->
                    <?php for ($i = 0; $i < 5; $i++): ?> <!-- Loop to create 5 input fields for the guess -->
                        <input type="text" name="digit[]" maxlength="1" required pattern="\d" oninput="this.value = this.value.replace(/[^0-9]/g, '');"> <!-- Input field for entering one digit, with restrictions on input -->
                    <?php endfor; ?> <!-- End of the loop -->
                </div>
                <button class="btn" type="submit" name="guess">Submit Guess</button> <!-- Button to submit the current guess -->
                <button class="btn" type="submit" name="new_game">New Game</button> <!-- Button to start a new game -->
            </form>

            <div class="feedback-section"> <!-- Section to display feedback about guesses -->
                <h2>Your Guesses</h2> <!-- Subheading for the guesses feedback -->
                <ul> <!-- Unordered list to display the guesses -->
                    <?php if (empty($_SESSION['guesses'])): ?> <!-- Check if there are no previous guesses -->
                        <li>No guesses yet.</li> <!-- Message displayed if no guesses have been made -->
                    <?php else: ?> <!-- If there are guesses, display them -->
                        <?php foreach ($_SESSION['guesses'] as $attempt): ?> <!-- Loop through each guess in the session -->
                            <li> <!-- List item for each guess -->
                                <span>Guess: <?php echo htmlspecialchars($attempt['guess']); ?></span> <!-- Display the guess -->
                                <span> <!-- Span for feedback icons -->
                                    <?php foreach ($attempt['feedback'] as $result): ?> <!-- Loop through feedback for each guess -->
                                        <span class="feedback-icon <?php echo $result; ?>"> <!-- Span for displaying the feedback icon with class based on result -->
                                            <?php echo $result === "correct" ? "✅" : ($result === "misplaced" ? "⚠️" : "❌"); ?> <!-- Conditional rendering of feedback icons based on the result -->
                                        </span> <!-- End of feedback icon span -->
                                    <?php endforeach; ?> <!-- End of the feedback loop -->
                                </span> <!-- End of the feedback span -->
                            </li> <!-- End of the list item for the guess -->
                        <?php endforeach; ?> <!-- End of the guesses loop -->
                    <?php endif; ?> <!-- End of the empty guesses check -->
                </ul> <!-- End of the unordered list -->
            </div> <!-- End of feedback section -->
        <?php endif; ?> <!-- End of the correct guess check -->
    </div> <!-- End of container div -->

    <script> <!-- Start of the JavaScript section -->
        // Validate inputs to ensure only numbers are entered
        function validateInputs() { // Function to validate inputs
            const inputs = document.querySelectorAll('input[name="digit[]"]'); // Select all input fields for digits
            for (const input of inputs) { // Loop through each input
                if (!/^\d$/.test(input.value)) { // Check if the input value is not a digit
                    alert('Please enter valid digits (0-9).'); // Alert user to enter valid digits
                    return false; // Prevent form submission
                }
            }
            return true; // Allow form submission if all inputs are valid
        }
    </script> <!-- End of JavaScript section -->
</body> <!-- End of body section -->
</html> <!-- End of the HTML document -->

