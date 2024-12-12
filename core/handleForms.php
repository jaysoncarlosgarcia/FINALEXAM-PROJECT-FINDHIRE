<?php
session_start();
require_once 'dbConfig.php';
require_once 'models.php';

if (isset($_POST['insertNewUserBtn'])) {
	$email = trim($_POST['email']);
	$username = trim($_POST['username']);
	$first_name = trim($_POST['first_name']);
	$last_name = trim($_POST['last_name']);
	$password = trim($_POST['password']);
	$confirm_password = trim($_POST['confirm_password']);

	if (!empty($email) && !empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {

		if ($password == $confirm_password) {

			$insertQuery = insertNewUser($pdo, $email, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
			$_SESSION['message'] = $insertQuery['message'];

			if ($insertQuery['status'] == '200') {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../index.php");
			}

			else {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../register.php");
			}

		}
		else {
			$_SESSION['message'] = "Please make sure both passwords are equal";
			$_SESSION['status'] = '400';
			header("Location: ../register.php");
		}

	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}
}

if (isset($_POST['loginApplicantBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        $loginQuery = checkIfUserExists($pdo, $username);
        $userIDFromDB = $loginQuery['userInfoArray']['user_id'];
        $usernameFromDB = $loginQuery['userInfoArray']['username'];
        $passwordFromDB = $loginQuery['userInfoArray']['password'];
        $roleFromDB = $loginQuery['userInfoArray']['role'];

        if (password_verify($password, $passwordFromDB)) {
            $_SESSION['user_id'] = $userIDFromDB;
            $_SESSION['username'] = $usernameFromDB;
            $_SESSION['role'] = $roleFromDB;

            if ($roleFromDB == 'Applicant') {
                header("Location: ../applicant_dashboard.php");
                exit();
            } else {
                $_SESSION['message'] = "Unauthorized access";
                $_SESSION['status'] = '403';
                header("Location: ../login_applicant.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Username/password invalid";
            $_SESSION['status'] = "400";
            header("Location: ../login_applicant.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../login_applicant.php");
        exit();
    }
}

if (isset($_POST['loginHRBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        $loginQuery = checkIfUserExists($pdo, $username);
        $userIDFromDB = $loginQuery['userInfoArray']['user_id'];
        $usernameFromDB = $loginQuery['userInfoArray']['username'];
        $passwordFromDB = $loginQuery['userInfoArray']['password'];
        $roleFromDB = $loginQuery['userInfoArray']['role'];

        if (password_verify($password, $passwordFromDB)) {
            $_SESSION['user_id'] = $userIDFromDB;
            $_SESSION['username'] = $usernameFromDB;
            $_SESSION['role'] = $roleFromDB; 

            if ($roleFromDB == 'HR') {
                header("Location: ../hr_dashboard.php");
                exit();
            } else {
                $_SESSION['message'] = "Unauthorized access";
                $_SESSION['status'] = '403';
                header("Location: ../login_hr.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Username/password invalid";
            $_SESSION['status'] = "400";
            header("Location: ../login_hr.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../login_hr.php");
        exit();
    }
}

if (isset($_GET['logoutUserBtn'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['role']); 
    session_destroy();
    header("Location: ../index.php");
    exit();
}
