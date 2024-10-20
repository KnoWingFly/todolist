<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up & Log In</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #1f2937;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 400px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .hidden {
            display: none;
        }
        button[type="submit"] {
            margin-top: 24px; 
        }
    </style>
</head>
<body>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <div class="tabs tabs-boxed">
            <a class="tab tab-active" href="#signup">Sign Up</a>
            <a class="tab" href="#login">Log In</a>
        </div>

        <div class="tab-content mt-6">
            
            <div id="signup" class="block">
                <h2 class="card-title justify-center mb-4">Sign Up for Free</h2>
                <form action="" method="post">
                    <input type="hidden" name="action" value="signup">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Email Address</span>
                        </label>
                        <input type="email" name="email" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Set A Password</span>
                        </label>
                        <input type="password" name="password" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">Get Started</button>
                    </div>
                </form>
            </div>

            
            <div id="login" class="hidden">
                <h2 class="card-title justify-center mb-4">Welcome Back!</h2>
                <form action="" method="post">
                    <input type="hidden" name="action" value="login">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Email Address</span>
                        </label>
                        <input type="email" name="email" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Password</span>
                        </label>
                        <input type="password" name="password" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">Log In</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<input type="checkbox" id="error-modal" class="modal-toggle">
<div class="modal">
  <div class="modal-box relative">
    <label for="error-modal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
    <h3 class="text-lg font-bold">Error</h3>
    <p class="py-4">An error occurred. Please try again.</p>
  </div>
</div>

<script>
$(document).ready(function() {
    $('.tabs a').on('click', function(e) {
        e.preventDefault();

        var target = $(this).attr('href');

       
        $('.tab-content > div').addClass('hidden');
        $(target).removeClass('hidden');

        
        $(this).addClass('tab-active').siblings().removeClass('tab-active');
    });
});
</script>

</body>
</html>
