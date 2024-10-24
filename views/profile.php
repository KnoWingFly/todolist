<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :user_id");
$stmt->execute(["user_id" => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

function isValidPassword($password) {
    return preg_match('/[A-Z]/', $password) &&     
           preg_match('/[a-z]/', $password) &&  
           preg_match('/\d/', $password) &&     
           preg_match('/[^A-Za-z0-9]/', $password) && 
           strlen($password) >= 12;                
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $new_password = $_POST["password"];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "UPDATE users SET username = :username, email = :email, password = :password WHERE id = :user_id"
        );
        $stmt->execute([
            "username" => $username,
            "email" => $email,
            "password" => $hashed_password,
            "user_id" => $user_id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "UPDATE users SET username = :username, email = :email WHERE id = :user_id"
        );
        $stmt->execute([
            "username" => $username,
            "email" => $email,
            "user_id" => $user_id,
        ]);
    }

    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :user_id");
    $stmt->execute(["user_id" => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $_SESSION['profile_updated'] = true;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css" />
     <link href="../css/output.css" rel="stylesheet">
	<style>
@import url('https://fonts.googleapis.com/css?family=Nunito:400,900|Montserrat|Roboto');
 body {
	 background: linear-gradient(to right, #3fb6a8, #7ed386);
}
.container {
    background: #fff;
    width: 90%; 
    max-width: 540px; 
    height: 420px;
    margin: 0 auto;
    position: relative;
    margin-top: 10%;
    box-shadow: 2px 5px 20px rgba(119, 119, 119, .5);
    padding-right: 250px;
}

.popup {
display: none;
position: absolute;
top: 100%;
left: 0;
z-index: 50;
width: 100%;
padding: 1rem;
border-radius: 0.5rem;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
background-color: white;
}

.popup.active {
display: block;
}

#password-requirements-list {
list-style: none;
padding: 0;
margin: 0;
}

#password-requirements-list li {
margin: 5px 0;
padding-left: 25px;
position: relative;
transition: color 0.3s ease;
}

#password-requirements-list li::before {
content: '✕';
position: absolute;
left: 0;
color: red;
}

#password-requirements-list li.valid {
color: #2ecc71;
}

#password-requirements-list li.valid::before {
    content: '✓';
    color: #2ecc71;
}

.logo {
    position: absolute;
    top: 10px;
    right: 20px;
    font-family: 'Nunito Sans', sans-serif;
    color: #3dbb3d;
    font-weight: 900;
    font-size: 1.5em;
    z-index: 5; 
}
.CTA {
    width: 100px; 
    height: 40px;
    right: -20px;
    bottom: -20px; 
    margin-bottom: 90px;
    position: absolute;
    z-index: 1;
    background: #7ed386;
    font-size: 1em;
    transform: rotate(-90deg);
    transition: all 0.5s ease-in-out;
    cursor: pointer;
}

 .CTA h1 {
	 color: #fff;
	 margin-top: 10px;
	 margin-left: 9px;
}
 .CTA:hover {
	 background: #3fb6a8;
	 transform: scale(1.1);
}

 .leftbox {
	 float: left;
	 top: -5%;
	 left: 5%;
	 position: absolute;
	 width: 15%;
	 height: 110%;
	 background: #7ed386;
	 box-shadow: 3px 3px 10px rgba(119, 119, 119, .5);
}
 nav a {
	 list-style: none;
	 padding: 35px;
	 color: #fff;
	 font-size: 1.1em;
	 display: block;
	 transition: all 0.3s ease-in-out;
}
 nav a:hover {
	 color: #3fb6a8;
	 transform: scale(1.2);
	 cursor: pointer;
}
 nav a:first-child {
	 margin-top: 7px;
}
 .active {
	 color: #3fb6a8;
}
 .rightbox {
	 float: right;
	 width: 60%;
	 height: 100%;
}
 .profile{
	 transition: opacity 0.5s ease-in;
	 position: absolute;
	 width: 50%;
}
 h1 {
	 font-family: 'Montserrat', sans-serif;
	 color: #7ed386;
	 font-size: 1em;
	 margin-top: 40px;
	 margin-bottom: 35px;
}

 h2 {
	 color: #777;
	 font-family: 'Roboto', sans-serif;
	 width: 80%;
	 text-transform: uppercase;
	 font-size: 8px;
	 letter-spacing: 1px;
	 margin-left: 2px;
}
 p {
	 border-width: 1px;
	 border-style: solid;
	 border-image: linear-gradient(to right, #3fb6a8, rgba(126, 211, 134, .5)) 1 0%;
	 border-top: 0;
	 width: 100%;
	 font-family: 'Montserrat', sans-serif;
	 font-size: 0.7em;
	 padding: 7px 0;
	 color: #070707;
}
 span {
	 font-size: 0.5em;
	 color: #777;
}
 .btn {
	 float: right;
	 font-family: 'Roboto', sans-serif;
	 text-transform: uppercase;
	 font-size: 10px;
	 border: none;
	 color: #3fb6a8;
}
 .btn:hover {
	 text-decoration: underline;
	 font-weight: 900;
}
 input {
	 border: 1px solid #ddd;
	 font-family: 'Roboto', sans-serif;
	 padding: 2px;
	 margin: 0;
}
 .privacy h2 {
	 margin-top: 25px;
}
 .settings h2 {
	 margin-top: 25px;
}
 .noshow {
	 opacity: 0;
}
.dashboard-link {
  text-decoration: none; 
  color: inherit; 
}
@media (max-width: 768px) {
    .container {
        width: 95%; 
        padding-right: 100px; 
    }

    .logo {
        right: 10px; 
        font-size: 1.2em; 
    }

    .CTA {
        width: 100px; 
        height: 30px;
        right: -10px;
        margin-bottom: 60px;
    }

    .leftbox {
        width: 10%; 
        height: 110%;
        top: -5%;
        left: 5%;
        box-shadow: 3px 3px 10px rgba(119, 119, 119, .5);
    }

    .rightbox {
        width: 75%; 
    }

    h1 {
        font-size: 0.8em; 
    }
    h2 {
        font-size: 6px; 
    }

    p {
        font-size: 0.6em;
    }
}

@media (max-width: 480px) {
    .container {
        width: 100%; 
        height: 620px;
        padding-right: 50px;
    }
    .profile {
    transition: opacity 0.5s ease-in;
    width: 90%;
    margin: 0 auto;
    padding: 10px;
    box-shadow: none;
    }

    .leftbox {
        display: none; 
    }
    .logo {
        font-size: 1em; 
    }

    .CTA {
    width: auto;
    height: auto;
    right: 0;
    bottom: 0;
    margin: 10px auto;
    transform: none;
    transition: none;
    position: relative;
    display: block;
}


    h1 {
        font-size: 0.7em;
    }

    h2 {
        font-size: 5px;
    }
}
    </style>

</head>
<body class="bg-light">
<div class="container">
  <div id="logo"><h1 class="logo">TODOLIST</h1>
  <a href="../index.php" class="CTA dashboard-link">
  <h1>Dashboard</h1>
</a>
  </div>
  <div class="leftbox">
  </div>
  <div class="rightbox">
    <div class="profile">
        <h1>my profile</h1>
      <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user["username"]) ?>" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user["email"]) ?>" required>
        </div>

        <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control">
                <div id="password-popup" class="popup bg-base-100">
                    <ul id="password-requirements-list" class="list-disc list-inside">
                        <li id="min-length" class="requirement-not-met">At least 12 characters</li>
                        <li id="uppercase" class="requirement-not-met">At least one uppercase letter</li>
                        <li id="lowercase" class="requirement-not-met">At least one lowercase letter</li>
                        <li id="number" class="requirement-not-met">At least one number</li>
                        <li id="special-char" class="requirement-not-met">At least one special character</li>
                    </ul>
                </div>
            </div>
        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
      </form>
    </div>
  </div>
  </div>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', () => {
        <?php if (isset($_SESSION['profile_updated']) && $_SESSION['profile_updated']): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Your profile has been updated successfully.',
            confirmButtonColor: '#333'
        }).then(() => {
            <?php unset($_SESSION['profile_updated']); ?>
        });
        <?php endif; ?>

        const passwordInput = document.getElementById('password');
        const popup = document.getElementById('password-popup');
        const requirements = {
            'min-length': {
                regex: /.{12,}/,
                element: document.getElementById('min-length')
            },
            'uppercase': {
                regex: /[A-Z]/,
                element: document.getElementById('uppercase')
            },
            'lowercase': {
                regex: /[a-z]/,
                element: document.getElementById('lowercase')
            },
            'number': {
                regex: /[0-9]/,
                element: document.getElementById('number')
            },
            'special-char': {
                regex: /[^A-Za-z0-9]/,
                element: document.getElementById('special-char')
            }
        };

        passwordInput.addEventListener('focus', () => {
            popup.style.display = 'block';
        });

        passwordInput.addEventListener('blur', (e) => {
            setTimeout(() => {
                if (!popup.matches(':hover')) {
                    popup.style.display = 'none';
                }
            }, 100);
        });

        popup.addEventListener('mouseleave', () => {
            if (!passwordInput.matches(':focus')) {
                popup.style.display = 'none';
            }
        });

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            
            for (const [key, requirement] of Object.entries(requirements)) {
                const isValid = requirement.regex.test(password);
                if (isValid) {
                    requirement.element.classList.add('valid');
                } else {
                    requirement.element.classList.remove('valid');
                }
            }
        });
    });
</script>
</body>
</html>
