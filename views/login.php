<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link
      rel="stylesheet"
      href="../node_modules/bootstrap/dist/css/bootstrap.min.css"
    />
    <style>
        .form-container {
            max-width: 400px;
            margin-top: 50px;
        }
        .nav-tabs {
            justify-content: center;
        }
        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-footer {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <!-- Tabs for switching between Login and Register -->
            <ul class="nav nav-tabs" id="authTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="register-tab" data-bs-toggle="tab" href="#register" role="tab" aria-controls="register" aria-selected="false">Register</a>
                </li>
            </ul>
<!-- tes 123 -->
            <!-- Content for the tabs -->
            <div class="tab-content" id="authTabContent">
                <!-- Login Tab -->
                <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                    <h2 class="form-header">Login</h2>
                    <form action="../actions/login_action.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="form-footer">
                        <p>Don't have an account? <a href="#register" id="switchToRegister">Register here</a></p>
                    </div>
                </div>

                <!-- Register Tab -->
                <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                    <h2 class="form-header">Register</h2>
                    <form action="../actions/register_action.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    <div class="form-footer">
                        <p>Already have an account? <a href="#login" id="switchToLogin">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Optional JS to automatically switch tabs based on links -->
    <script>
        // Switch tabs based on links in form-footer
        document.getElementById('switchToRegister').addEventListener('click', function(e) {
            e.preventDefault();
            var registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
            registerTab.show();
        });

        document.getElementById('switchToLogin').addEventListener('click', function(e) {
            e.preventDefault();
            var loginTab = new bootstrap.Tab(document.getElementById('login-tab'));
            loginTab.show();
        });
    </script>
</body>
</html>
