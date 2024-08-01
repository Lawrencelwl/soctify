<?php
require_once __DIR__ . '/../../config.php';
require_once CONTROLLER_PATH . '/AuthController.php';
require_once CONTROLLER_PATH . '/PostController.php';
?>

<!DOCTYPE html>
<html>

<head>
    <?php include VIEW_INC_PATH . '/head.php'; ?>
    <link rel="stylesheet" href="assets/css/home.css" />
    <script src="assets/js/post.js" defer></script>
    <script src="assets/js/profile.js" defer></script>

    <script type="text/javascript">
        // Get the username from the URL
        const targetUsername = "<?php echo $_GET['username'] ?>";
        const userStatData = <?php
                                $authInstance = new AuthController($mysqli);
                                $statData = $authInstance->get_user_status($_GET['username']);
                                echo $statData;
                                ?>;
        const posts = <?php
                        $postInstance = new PostController($mysqli);
                        $posts = $postInstance->load_target_posts($_GET['username']);
                        echo $posts;
                        ?>;

        console.log(posts);
    </script>
</head>



<body class="bg-body-tertiary">

    <?php include VIEW_INC_PATH . '/navbar.php'; ?>

    <!-- For media upload -->
    <input type="file" id="mediaInput" accept="image/png,image/jpeg,video/mp4" style="display: none;">

    <div class="page-wrapper container my-5 px-3 d-flex justify-content-center flex-column gap-4 align-items-center">
        <!-- Profile header -->
        <div class="social-card d-flex justify-content-center flex-column gap-4 align-items-center w-100">
            <div class="d-flex gap-4 align-items-center flex-wrap sm:flex-nowrap">
                <img id="avatarImg" src="" alt="Avatar" class="avatar avatar--lg"> </img>

                <div class="d-flex flex-column gap-1">

                    <div class="d-flex gap-3 align-items-center flex-wrap">
                        <h3 id="username" class="mt-2" style="word-break: break-word; max-width: 250px;"></h3>

                        <!--  Follow button -->
                        <?php
                        if (isset($_SESSION['username'])) {
                            if ($_SESSION['username'] != $_GET['username']) {
                                echo '<button id="followBtn" class="btn btn-primary btn-md follow-btn"
                                data-follow-username="' . $_SESSION['username'] . '">Follow</button>';
                            }
                        }
                        ?>
                    </div>

                    <div class="d-flex gap-4 align-items-center mt-2">
                        <p class="text-muted"><span id="postCount">0</span> posts</p>
                        <p class="text-muted"><span id="followerCount">0</span> followers</p>
                        <p class="text-muted"><span id="followingCount">0</span> following</p>
                    </div>

                </div>
            </div>
        </div>



        <div id="postContainer" class="d-flex justify-content-center flex-column gap-4 align-items-center w-100">

        </div>



        <!-- For infintie scroll -->
        <div class="more d-flex justify-content-center align-items-center mt-5">
            <p class="virtual"></p>
            <span id="postLoadingSpinner" class="spinner-border spinner-border-md pl-1 text-primary" role="status" aria-hidden="true"></span>
        </div>
    </div>



</body>

</html>