<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <style>
        .body {
            background: #ff4931;
            transition: all .5s;
            padding: 1px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .veen {
            width: 70%;
            margin: 100px auto;
            background: rgba(255, 255, 255, .5);
            min-height: 400px;
            display: table;
            position: relative;
            box-shadow: 0 0 4px rgba(0, 0, 0, .14), 0 4px 8px rgba(0, 0, 0, .28);
        }
        .veen > div {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            color: #fff;
        }
        .veen button {
            background: transparent;
            display: inline-block;
            padding: 10px 30px;
            border: 3px solid #fff;
            border-radius: 50px;
            position: relative;
            color: #FFF;
            transition: all .25s;
        }
        .veen button.dark {
            border-color: #ff4931;
            background: #ff4931;
        }
        .veen .move button.dark {
            border-color: #e0b722;
            background: #e0b722;
        }
        .veen .splits p {
            font-size: 18px;
        }
        .veen .wrapper {
            position: absolute;
            width: 40%;
            height: 120%;
            top: -10%;
            left: 5%;
            background: #fff;
            transition: all .5s;
            color: #303030;
            overflow: hidden;
        }
        .veen .wrapper > form {
            padding: 15px 30px 30px;
            width: 100%;
            transition: all .5s;
            background: #fff;
            width: 100%;
        }
        .veen .wrapper #login {
            padding-top: 20%;
            visibility: visible;
        }
        .veen .wrapper #register {
            transform: translateY(-80%) translateX(100%);
            visibility: hidden;
        }
        .veen .wrapper.move #register {
            transform: translateY(-80%) translateX(0%);
            visibility: visible;
        }
        .veen .wrapper.move #login {
            transform: translateX(-100%);
            visibility: hidden;
        }
        .veen .wrapper > form > div {
            position: relative;
            margin-bottom: 15px;
        }
        .veen .wrapper label {
            position: absolute;
            top: -7px;
            font-size: 12px;
            white-space: nowrap;
            background: #fff;
            text-align: left;
            left: 15px;
            padding: 0 5px;
            color: #999;
            pointer-events: none;
        }
        .veen .wrapper input {
            height: 40px;
            padding: 5px 15px;
            width: 100%;
            border: solid 1px #999;
        }
        .veen .wrapper input:focus {
            outline: none;
            border-color: #ff4931;
        }
        .veen > .wrapper.move {
            left: 45%;
        }
        .veen > .wrapper.move input:focus {
            border-color: #e0b722;
        }
    </style>
</head>
<body>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<div class="body">
    <div class="veen">
        <div class="login-btn splits">
            <p>Already an user?</p>
            <button class="active">Login</button>
        </div>
        <div class="rgstr-btn splits">
            <p>Don't have an account?</p>
            <button>Register</button>
        </div>
        <div class="wrapper">
            <!-- Login Form -->
            <form id= "login" action="../actions/login_action.php" method="POST">
            <h3>Login</h3>
                <div class="mail">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="passwd">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <div class="submit">
                    <button type="submit" class="dark">Login</button>
                </div>
            </form>
            <!-- Register Form -->
            <form id="register" action="../actions/register_action.php" method="POST">
                <h3>Register</h3>
                <div class="name">
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>
                <div class="mail">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="passwd">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <div class="submit">
                    <button type="submit" class="dark">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- JavaScript for form toggle -->
<script>
    $(document).ready(function(){
        $(".veen .rgstr-btn button").click(function(){
            $('.veen .wrapper').addClass('move');
            $('.body').css('background','#e0b722');
            $(".veen .login-btn button").removeClass('active');
            $(this).addClass('active');
        });
        $(".veen .login-btn button").click(function(){
            $('.veen .wrapper').removeClass('move');
            $('.body').css('background','#ff4931');
            $(".veen .rgstr-btn button").removeClass('active');
            $(this).addClass('active');
        });
    });
</script>
</body>
</html>
