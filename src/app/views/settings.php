<?php
require_once __DIR__ . '/../../config.php';
?>

<!DOCTYPE html>
<html>

<head>
    <?php include VIEW_INC_PATH . '/head.php'; ?>
    <script src="assets/js/settings.js" defer></script>
</head>

<body class="bg-body-tertiary">
    <?php include VIEW_INC_PATH . '/navbar.php'; ?>

    <div class="container page-wrapper  my-5">
        <h1 class="display-3 text-center fw-semibold text-primary mb-4">
            Settings
        </h1>

        <div class="d-flex justify-content-center mb-4">
            <img id="avatarImg" src="
            <?php
            $src = '';
            if (!isset($_SESSION['avatar_url']))
                $src = avatarUrl($_SESSION['username']);
            else
                $src = $_SESSION['avatar_url'];
            echo $src;
            ?>
            " alt="Avatar" class="avatar avatar--lg">
        </div>


        <div class="text-center mb-4">
            <?php if (isset($_SESSION['username'])): ?>
                <h5 class="fw-semibold ">
                    <?php echo $_SESSION['username'] ?>
                </h5>
            <?php endif; ?>
        </div>


        <!--  A form for edit profile -->
        <div style="max-width: 500px; margin: auto;">
            <form id="editProfileForm" class="mb-4">
                <!-- File upload for avatar -->
                <div class="mb-3">
                    <label for="avatar" class="form-label">Avatar</label>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/png,image/jpeg">
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?php if (isset($_SESSION['username']))
                            echo $_SESSION['username']; ?>">
                </div>

                <!-- <div class=" mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $_SESSION['email'] ?>">
                </div> -->

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" minlength="8" maxlength="20" class="form-control" id="password"
                        name="password">
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" minlength="8" maxlength="20" class="form-control" id="confirmPassword"
                        name="confirmPassword">
                </div>

                <button id="submitBtn" type="submit" class="btn btn-primary">
                    <span>Submit</span>
                    <span id="spinner" class="spinner-border spinner-border-sm pl-1" role="status"
                        aria-hidden="true"></span></button>
            </form>
        </div>
    </div>
</body>

</html>