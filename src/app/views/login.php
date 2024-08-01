<?php
require_once(__DIR__ . '/../../config.php');
?>

<!DOCTYPE html>

<html>

<head>
    <?php include(VIEW_INC_PATH . '/head.php'); ?>
    <link rel="stylesheet" href="assets/css/auth.css" />
    <script src="assets/js/login.js" defer></script>
</head>

<body>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-6 d-none d-md-flex bg-light-subtle min-vh-100 justify-content-center align-items-center 
                flex-column p-5">
                <div class="d-flex justify-content-center flex-column">
                    <h1 class="fw-bolder text-primary fs-1 ">SOCTIFY</h1>
                    <p class="fs-5 fw-bold">The new age of social media</p>
                    <img class="mt-4" width="500" src="/assets/images/login.svg" alt="login" />
                </div>
            </div>

            <div class="col-12 col-md-6 min-vh-100 d-flex justify-content-center align-items-center 
                flex-column p-5">
                <div class="auth-container">
                    <!-- The brand -->
                    <div class="d-flex gap-2">
                        <img class="mb-3" width="35" src="/assets/images/logo-circle.svg" alt="logo" />
                        <h1 class="fw-bolder text-primary fs-1 d-block d-md-none">SOCTIFY</h1>
                    </div>

                    <p class="fs-4 fw-bold">Sign in</p>

                    <form id="login-form" method="post" action="/login_handler">
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" aria-describedby="emailHelp" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputPassword1" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <!-- The hint -->
                        <div class="mb-5 mt-4 text-center text-muted text-auth-hint">
                            New here ?
                            <a href="/register" class="text-decoration-none text-primary">
                                Create an account
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-rounded">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>