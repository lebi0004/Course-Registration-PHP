<?php
// include("./common/header.php");
// include_once 'Functions.php';
// include_once 'EntityClassLib.php';
// session_start();

// if (!isset($_SESSION['user'])) {
//     header("Location: Login.php");
//     exit();
// }

// $user = $_SESSION['user'];

include_once 'EntityClassLib.php';  // Load class definition first
include_once 'Functions.php';        // Include any helper functions
session_start();                     // Start session after including class files

include("./common/header.php");      // Include header

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: Login.php");
    exit();
}

// Retrieve the user object from the session
$user = $_SESSION['user'];
$errorMessages = [];

// Fetch registered courses for the user
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT S.Year, S.Term, C.CourseCode, C.Title, C.WeeklyHours 
        FROM Registration R 
        JOIN Course C ON R.CourseCode = C.CourseCode 
        JOIN Semester S ON R.SemesterCode = S.SemesterCode 
        WHERE R.StudentId = :studentId 
        ORDER BY S.Year DESC, S.Term ASC
    ");
    $stmt->bindValue(':studentId', $user->getUserId());
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("The system is currently not available, try again later.");
}

// Handle delete selected action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['deleteSelected'])) {
    if (isset($_POST['selectedCourses'])) {
        try {
            $pdo->beginTransaction();
            foreach ($_POST['selectedCourses'] as $courseCode) {
                $stmt = $pdo->prepare("DELETE FROM Registration WHERE StudentId = :studentId AND CourseCode = :courseCode");
                $stmt->bindValue(':studentId', $user->getUserId());
                $stmt->bindValue(':courseCode', $courseCode);
                $stmt->execute();
            }
            $pdo->commit();
            header("Location: CurrentRegistration.php"); // Refresh the page
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("The system is currently not available, try again later.");
        }
    } else {
        $errorMessages[] = "Please select at least one course to delete.";
    }
}

?>

<div class="container" style="padding-top: 70px;">
    <h1 class="text-center">Current Registrations</h1>
    <p>Hello <?php echo htmlspecialchars($user->getName()); ?> (not you? <a href="Logout.php">change user here</a>)</p>
    <p>The following are your current registrations:</p>

    <?php
    // Display error messages if any
    if (!empty($errorMessages)) {
        echo '<div class="text-danger">';
        foreach ($errorMessages as $errorMessage) {
            echo "<p>$errorMessage</p>";
        }
        echo '</div>';
    }
    ?>

    <form action="CurrentRegistration.php" method="post" id="registrationForm">
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Term</th>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Hours</th>
                    <th>Select</th>
                </tr>
            </thead>
            <tbody>
    <?php
    if (!empty($registrations)) {
        $currentYearTerm = ''; // Track the current semester
        $semesterTotalHours = 0; // Accumulate hours for the semester

        foreach ($registrations as $registration) {
            $yearTerm = $registration['Year'] . ' ' . $registration['Term'];

            // Check if we are in a new semester
            if ($currentYearTerm !== $yearTerm && $currentYearTerm !== '') {
                // Display total for the previous semester
                echo "<tr class='font-weight-bold'><td colspan='4' class='text-end'>Total Weekly Hours</td><td>{$semesterTotalHours}</td><td></td></tr>";
                $semesterTotalHours = 0; // Reset total for the new semester
            }

            // Update the current semester tracker
            $currentYearTerm = $yearTerm;

            // Display course row
            echo "<tr>";
            echo "<td>{$registration['Year']}</td>";
            echo "<td>{$registration['Term']}</td>";
            echo "<td>{$registration['CourseCode']}</td>";
            echo "<td>{$registration['Title']}</td>";
            echo "<td>{$registration['WeeklyHours']}</td>";
            echo "<td><input type='checkbox' name='selectedCourses[]' value='{$registration['CourseCode']}'></td>";
            echo "</tr>";

            // Add hours to the semester total
            $semesterTotalHours += $registration['WeeklyHours'];
        }

        // Display total for the last semester
        echo "<tr class='font-weight-bold'><td colspan='4' class='text-end'>Total Weekly Hours</td><td>{$semesterTotalHours}</td><td></td></tr>";
    } else {
        echo "<tr><td colspan='6' class='text-center'>No registrations found.</td></tr>";
    }
    ?>
</tbody>

        </table>

        <div class="text-center">
            <button type="button" class="btn btn-primary" onclick="confirmDeletion()">Delete Selected</button>
            <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
        <input type="hidden" name="deleteSelected" value="1">
    </form>
</div>

<script>
function confirmDeletion() {
    if (confirm("The selected registrations will be deleted!")) {
        document.getElementById('registrationForm').submit();
    }
}
</script>

<?php include('./common/footer.php'); ?>
