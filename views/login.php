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

        .password-requirements {
            display: none; /* Hide by default */
            position: absolute;
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 90%; /* Adjust to match the input width */
            left: 5%; /* Center below the input */
            margin-top: 5px; /* Space between input and requirements */
        }

        .password-requirements.active {
            display: block; /* Show when active */
        }

        .requirement-met {
            color: green;
        }

        .requirement-not-met {
            color: red;
        }

        .btn-disabled {
            cursor: not-allowed !important;
            opacity: 1;
        }
    </style>
</head>
<body>
<script src="../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../node_modules/jquery-ui-dist/jquery-ui.min.js"></script>
<script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
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
            <form id="login" action="../actions/login_action.php" method="POST">
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
                <div>
                    <a href="forgot_password.php" class="text-primary">Forgot Password?</a>
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
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" id="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <div id="password-popup" class="password-requirements bg-blue-100 mt-2 p-2 rounded-lg hidden">
                        <ul id="password-requirements-list" class="list-disc list-inside">
                            <li id="min-length" class="requirement-not-met">At least 12 characters</li>
                            <li id="uppercase" class="requirement-not-met">At least one uppercase letter</li>
                            <li id="lowercase" class="requirement-not-met">At least one lowercase letter</li>
                            <li id="number" class="requirement-not-met">At least one number</li>
                            <li id="special-char" class="requirement-not-met">At least one special character</li>
                        </ul>
                    </div>
                </div>
                <div class="submit">
                    <button type="submit" class="dark" disabled>Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Modal for Error -->
<div id="errorModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login Error</h5>
            </div>
            <div class="modal-body">
                <p>Invalid credentials, please try again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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

        // Show password requirements on focus
        $('#password').on('focus', function() {
            $('#password-popup').addClass('active');
        }).on('blur', function() {
            $('#password-popup').removeClass('active');
        }).on('input', function() {
            validatePassword($(this).val());
        });

        function validatePassword(password) {
            const minLength = password.length >= 12;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            $('#min-length').toggleClass('requirement-met', minLength).toggleClass('requirement-not-met', !minLength);
            $('#uppercase').toggleClass('requirement-met', hasUppercase).toggleClass('requirement-not-met', !hasUppercase);
            $('#lowercase').toggleClass('requirement-met', hasLowercase).toggleClass('requirement-not-met', !hasLowercase);
            $('#number').toggleClass('requirement-met', hasNumber).toggleClass('requirement-not-met', !hasNumber);
            $('#special-char').toggleClass('requirement-met', hasSpecialChar).toggleClass('requirement-not-met', !hasSpecialChar);

            // Enable the submit button if all requirements are met
            const allRequirementsMet = minLength && hasUppercase && hasLowercase && hasNumber && hasSpecialChar;
            $('button[type="submit"]').prop('disabled', !allRequirementsMet);
        }
    });
</script>
</body>
</html>
