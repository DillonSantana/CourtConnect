<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Login</title>
  </head>
  <body>
    <?php
        include("pageFormat.php");
        renderHeader("Login", "login", "register");
    ?>

    <div class="form-container">
            <form action="./login.php" method="POST" onsubmit="return validate()">
                <label for="email">Email:</label><br>
                <input type="text" id="email" name="email" onblur="validateEmail(this)" placeholder="Enter email here"><br>
                <small id="emailErr"></small><br>
                <label for="pwd">Password:</label><br>
                <input type="password" id="pwd" name="pwd" onblur="validatePassword(this)" placeholder="Enter password here"><br>
                <small id="pwdErr"></small><br><br>
                <input type="submit" value="Submit">
            </form>
        </div>
    </div>

    <?php
        require_once "db_connect.php";
        if(isset($_POST['email'])) {
            $email = $_POST['email'];
            $pwd = $_POST['pwd'];

            $conn = connectDB();
            $sql = "SELECT * FROM `users` WHERE `email`=\"$email\" AND `password`=\"$pwd\"";
            $result = $conn->query($sql);
            $arr = $result->fetchAll();

            if(count($arr) == 1) {
                session_start();
                $_SESSION['user_id'] = $arr[0]['user_id'];
                $_SESSION['name'] = $arr[0]['name'];
                $_SESSION['role'] = $arr[0]['role'];
                if ($_SESSION['role'] == "member") {
                    header("Location: ./dashboard.php");
                } else if ($_SESSION['role'] == "admin") {
                    $_SESSION['admin'] = true;
                    header("Location: ./event_admin.php");
                }
            } else {
                echo '<h4>Login Failed!</h4>';
            }
        }
    ?>

    <?php
        renderFooter();
    ?>

    <script type="text/javascript" src="./js/validation.js"></script>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>