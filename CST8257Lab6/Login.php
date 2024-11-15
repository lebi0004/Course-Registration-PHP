<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("./common/header.php"); 
include_once 'Functions.php';
include_once 'EntityClassLib.php';

extract($_POST);
$loginErrorMsg = '';

if (isset($btnLogin)) {
    try {
        // Fetch the user by ID and password
        $user = getUserByIdAndPassword($txtId, $txtPswd);
        
        if ($user) {
            // If the password matches, log in the user
            $_SESSION['user'] = $user;
            header("Location: CourseSelection.php");
            exit();
        } else {
            // If user is not found or password doesn't match, show error message
            $loginErrorMsg = 'Incorrect User ID and Password Combination!';
        }
    } catch (Exception $e) {
        die("The system is currently not available, try again later.");
    }
}

?>

<div class="container" style="padding-top: 70px;">
    <form action='Login.php' method='post' class="mx-auto" style="max-width: 400px;">
        <h1 class="text-center">Login</h1>
        <p class="text-center">You need to <a href='NewUser.php'>sign up</a> if you are a new user.</p>

        <div class="mb-3">
            <div class="text-danger">
                <?php echo $loginErrorMsg; ?>
            </div>
        </div>

        <div class="row mb-3">
            <label for="studentId" class="col-sm-4 col-form-label text-end">Student ID:</label>
            <div class="col-sm-6">
                <input type='text' class="form-control" id="studentId" name='txtId' value="<?php echo isset($txtId) ? $txtId : ''; ?>">
            </div>
        </div>

        <div class="row mb-3">
            <label for="password" class="col-sm-4 col-form-label text-end">Password:</label>
            <div class="col-sm-6">
                <input type='password' class="form-control" id="password" name='txtPswd'>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6 offset-sm-4">
                <button type="submit" name='btnLogin' class="btn btn-primary">Login</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </div>
    </form>
</div>

<?php include('./common/footer.php'); ?>
