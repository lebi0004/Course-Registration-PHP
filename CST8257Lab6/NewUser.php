<?php 
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);


include("./common/header.php"); 
include_once "Functions.php";
include_once "EntityClassLib.php"; 	
// session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

extract($_POST);

$errors = [];
$txtId = isset($txtId) ? $txtId : '';
$txtName = isset($txtName) ? $txtName : '';
$txtPhoneNumber = isset($txtPhoneNumber) ? $txtPhoneNumber : '';
$txtPassword = isset($txtPassword) ? $txtPassword : '';
$txtPasswordConfirm = isset($txtPasswordConfirm) ? $txtPasswordConfirm : '';

if (isset($regSubmit)) {
    // Validate fields
    if (empty($txtId)) {
        $errors['txtId'] = "Student ID is required.";
    }
    
    if (empty($txtName)) {
        $errors['txtName'] = "Name is required.";
    }
    
    if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $txtPhoneNumber)) {
        $errors['txtPhoneNumber'] = "Phone Number must be in the format nnn-nnn-nnnn.";
    }
    
    if (strlen($txtPassword) < 6 || 
        !preg_match("/[A-Z]/", $txtPassword) || 
        !preg_match("/[a-z]/", $txtPassword) || 
        !preg_match("/\d/", $txtPassword)) {
        $errors['txtPassword'] = "Password must be at least 6 characters long, and contain at least one uppercase letter, one lowercase letter, and one digit.";
    }

    if ($txtPassword !== $txtPasswordConfirm) {
        $errors['txtPasswordConfirm'] = "Passwords do not match.";
    }

    // Check if Student ID already exists
    if (empty($errors)) {
        try {
            $existingStudent = getStudentById($txtId);
            if ($existingStudent) {
                $errors['txtId'] = "A student with this ID has already signed up.";
            }
        } catch (Exception $e) {
            die("The system is currently not available, try again later.");
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash the password before storing
            $hashedPassword = password_hash($txtPassword, PASSWORD_DEFAULT);
            addNewUser($txtId, $txtName, $txtPhoneNumber, $hashedPassword);
            header("Location: CourseSelection.php");
            exit();
        } catch (Exception $e) {
            die("The system is currently not available, try again later.");
        }
    }
}
?>
<html>
<head>
    <title>Algonquin College Online Course Registration</title>
</head>
<body>
    <form action="NewUser.php" method="post" class="mx-auto" style="max-width: 400px;">
       <div class="text-center" style="padding-top: 70px;">
        <h1 class="display-4">Sign up</h1>
        <p class="display-7">All fields are required</p>
    </div>

    <div class="row mb-3">
        <label for="studentId" class="col-sm-4 col-form-label text-end">Student ID:</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="studentId" name="txtId" value="<?php echo htmlspecialchars($txtId); ?>">
            <div class="text-danger"><?php echo isset($errors['txtId']) ? $errors['txtId'] : ''; ?></div>
        </div>
    </div>

    <div class="row mb-3">
        <label for="studentName" class="col-sm-4 col-form-label text-end">Name:</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="studentName" name="txtName" value="<?php echo htmlspecialchars($txtName); ?>">
            <div class="text-danger"><?php echo isset($errors['txtName']) ? $errors['txtName'] : ''; ?></div>
        </div>
    </div>

    <div class="row mb-3">
        <label for="phoneNumber" class="col-sm-4 col-form-label text-end">Phone Number:</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="phoneNumber" name="txtPhoneNumber" value="<?php echo htmlspecialchars($txtPhoneNumber); ?>">
            <div class="text-danger"><?php echo isset($errors['txtPhoneNumber']) ? $errors['txtPhoneNumber'] : ''; ?></div>
        </div>
    </div>

    <div class="row mb-3">
        <label for="createPassword" class="col-sm-4 col-form-label text-end">Password:</label>
        <div class="col-sm-8">
            <input type="password" class="form-control" id="createPassword" name="txtPassword">
            <div class="text-danger"><?php echo isset($errors['txtPassword']) ? $errors['txtPassword'] : ''; ?></div>
        </div>
    </div>

    <div class="row mb-3">
        <label for="confirmPassword" class="col-sm-4 col-form-label text-end">Password Again:</label>
        <div class="col-sm-8">
            <input type="password" class="form-control" id="confirmPassword" name="txtPasswordConfirm">
            <div class="text-danger"><?php echo isset($errors['txtPasswordConfirm']) ? $errors['txtPasswordConfirm'] : ''; ?></div>
        </div>
    </div>

    <div class="text-center">
        <button type="submit" name="regSubmit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Clear</button>
    </div>
</form>
</body>
</html>

<?php include('./common/footer.php'); ?>
