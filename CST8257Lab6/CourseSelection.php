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
include_once 'EntityClassLib.php';  // Include class definition first
include_once 'Functions.php';        // Include any helper functions
session_start();                     // Start session after including class files

include("./common/header.php");      // Include header

if (!isset($_SESSION['user'])) {
    header("Location: Login.php");
    exit();
}

$user = $_SESSION['user'];
$maxHours = 16; // Maximum hours that can be registered
$errorMessages = []; // Array to store error messages
$registeredHours = 0;
$courses = [];
$selectedSemester = "";

// Fetch all semesters from the database
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM semester");
    $stmt->execute();
    $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("The system is currently not available, try again later.");
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['semester'])) {
        $selectedSemester = $_POST['semester'];

        if (!empty($selectedSemester)) {
            // Calculate registered hours for the selected semester
            try {
                $stmt = $pdo->prepare("
                    SELECT SUM(C.WeeklyHours) AS total_hours
                    FROM Course C
                    INNER JOIN Registration R ON C.CourseCode = R.CourseCode
                    WHERE R.StudentId = :studentId AND R.SemesterCode = :semesterCode
                ");
                $stmt->bindValue(':studentId', $user->getUserId());
                $stmt->bindValue(':semesterCode', $selectedSemester);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $registeredHours = $result['total_hours'] ?? 0;
            } catch (Exception $e) {
                die("The system is currently not available, try again later.");
            }

            // Get courses available for the selected semester, excluding registered courses
            try {
                $stmt = $pdo->prepare("
                    SELECT C.CourseCode, C.Title, C.WeeklyHours 
                    FROM Course C
                    INNER JOIN CourseOffer CO ON C.CourseCode = CO.CourseCode
                    WHERE CO.SemesterCode = :semesterCode
                    AND C.CourseCode NOT IN (
                        SELECT CourseCode FROM Registration 
                        WHERE StudentId = :studentId AND SemesterCode = :semesterCode
                    )
                ");
                $stmt->bindValue(':semesterCode', $selectedSemester);
                $stmt->bindValue(':studentId', $user->getUserId());
                $stmt->execute();
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die("The system is currently not available, try again later.");
            }
        }
    }

    // Check if the form was submitted with selected courses
    if (isset($_POST['selectedCourses']) || isset($_POST['Submit'])) {
        $selectedCourses = $_POST['selectedCourses'] ?? [];
        $selectedHours = 0;

        // Calculate the total hours for selected courses
        foreach ($selectedCourses as $courseCode) {
            foreach ($courses as $course) {
                if ($course['CourseCode'] === $courseCode) {
                    $selectedHours += $course['WeeklyHours'];
                }
            }
        }

        // Validation checks
        if (empty($selectedCourses)) {
            $errorMessages[] = "You need to select at least one course.";
        }
        
        if (($registeredHours + $selectedHours) > $maxHours) {
            $errorMessages[] = "Your selection exceeds the maximum weekly hours limit of 16 hours.";
        }

        // Register the selected courses if there are no errors
        if (empty($errorMessages)) {
            try {
                foreach ($selectedCourses as $courseCode) {
                    $stmt = $pdo->prepare("INSERT INTO registration (StudentId, CourseCode, SemesterCode) VALUES (:studentId, :courseCode, :semesterCode)");
                    $stmt->bindValue(':studentId', $user->getUserId());
                    $stmt->bindValue(':courseCode', $courseCode);
                    $stmt->bindValue(':semesterCode', $selectedSemester);
                    $stmt->execute();
                }
                // Refresh the page to update registered hours and course list
                header("Location: CourseSelection.php");
                exit();
            } catch (Exception $e) {
                die("The system is currently not available, try again later.");
            }
        }
    }
}

$remainingHours = $maxHours - $registeredHours;
?>

<!-- HTML for displaying the form and course list -->
<div class="container" style="padding-top: 70px;">
    <h1 class="text-center">Course Selection</h1>
    <p>Welcome <?php echo htmlspecialchars($user->getName()); ?>! (not you? <a href="Logout.php">change user here</a>)</p>
    <p>You have registered <?php echo $registeredHours; ?> hours for the selected semester.</p>
    <p>You can register <?php echo $remainingHours; ?> more hours of course(s) for the semester.</p>
    <p>Please note that the courses you have registered will not be displayed in the list.</p>

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

    <form action="CourseSelection.php" method="post" id="semesterForm">
        <label for="semester">Semester:</label>
        <select id="semester" name="semester" class="form-control" onchange="submitForm()">
            <option value="">Select Semester</option>
            <?php
            if (!empty($semesters)) {
                foreach ($semesters as $semester) {
                    $selected = (isset($selectedSemester) && $selectedSemester == $semester['SemesterCode']) ? "selected" : "";
                    echo "<option value='{$semester['SemesterCode']}' $selected>{$semester['Term']} {$semester['Year']}</option>";
                }
            }
            ?>
        </select>

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Course Title</th>
                    <th>Hours</th>
                    <th>Select</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($courses)) {
                    foreach ($courses as $course) {
                        echo "<tr>";
                        echo "<td>{$course['CourseCode']}</td>";
                        echo "<td>{$course['Title']}</td>";
                        echo "<td>{$course['WeeklyHours']}</td>";
                        echo "<td><input type='checkbox' name='selectedCourses[]' value='{$course['CourseCode']}'></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No courses available for the selected semester.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="text-center">
            <button type="submit" name="Submit" class="btn btn-primary">Submit</button>
            <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
    </form>
</div>

<script>
function submitForm() {
    document.getElementById('semesterForm').submit();
}
</script>

<?php include('./common/footer.php'); ?>
