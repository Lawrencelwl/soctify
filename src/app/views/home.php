<?php
require_once __DIR__ . '/../../config.php';
require_once CONTROLLER_PATH . '/AuthController.php';
require_once CONTROLLER_PATH . '/PostController.php';
?>

<!DOCTYPE html>
<html>

<head>
    <?php include VIEW_INC_PATH . '/head.php'; ?>
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
    <link rel="stylesheet" href="assets/css/quill.css" />
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <link rel="stylesheet" href="assets/css/home.css" />
    <script src="assets/js/post.js" defer></script>

    <script type="text/javascript">
        const posts = <?php
        $postInstance = new PostController($mysqli);
        $posts = $postInstance->load_posts();
        echo $posts;
        ?>;

        console.log(posts);

        const recommendUser = <?php
        $authInstance = new AuthController($mysqli);
        $recommendUser = $authInstance->get_recommand_user();
        echo $recommendUser;
        ?>;
    </script>
</head>


<body class="bg-body-tertiary">
    <?php include VIEW_INC_PATH . '/navbar.php'; ?>

    <!-- For media upload -->
    <input type="file" id="mediaInput" accept="image/png,image/jpeg,video/mp4" style="display: none;">

    <div class="page-wrapper container my-5 px-3">
        <div class="row">
            <div id="postContainer"
                class="col-12 col-md-8 d-flex justify-content-center flex-column gap-4 align-items-center">

                <!-- Post creation -->
                <div class="social-card" id="createPost">
                    <div class="d-flex gap-2">
                        <img id="avatarImg" src="
                        <?php
                        $src = '';
                        if (!isset($_SESSION['avatar_url']))
                            $src = avatarUrl($_SESSION['username']);
                        else
                            $src = $_SESSION['avatar_url'];
                        echo $src;
                        ?>
                        " alt="Avatar" class="avatar">

                        <!-- Create post button -->
                        <button id="create-post-btn" class="btn-post-create" data-bs-toggle="modal"
                            data-bs-target="#createPostModal">
                            <span>
                                What are your thoughts,
                                <?php echo $_SESSION['username'] ?>?
                            </span>
                        </button>
                    </div>

                    <hr>

                    <!-- Media creation -->
                    <div class="row">
                        <div class="col-2 d-flex">
                            <button id="selectImageBtn" type="button" class="btn btn-transparent btn-social-media">
                                <i class="bi bi-image btn-social-media__img-icon"></i>
                                <span>Photo</span>
                            </button>

                            <button id="selectVideoBtn" type="button" class="btn btn-transparent btn-social-media">
                                <i class="bi bi-play-btn btn-social-media__rec-icon"></i>
                                <span>Video</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- <button id="post_test">post</button> -->


            </div>

            <div class="col-12 col-md-4 col-lg-4 d-none d-md-block ">
                <div id="recommendUser" class="social-card widget-follow-container d-flex gap-3 flex-wrap">
                    <!-- <div class="card">
                        <img src="<?php echo avatarUrl($_SESSION['username']) ?>" alt="Avatar"
                            class="avatar avatar--md ">
                        <span class="fw-bold my-1">Elvin</span>

                        <button type="button" class="btn rounded-pill btn-primary follow-btn ">Follow</button>
                    </div>

                    <div class="card">

                        <img src="<?php echo avatarUrl($_SESSION['username']) ?>" alt="Avatar" class="avatar avatar--md ">


                        <span class="fw-bold my-1">Elvin</span>

                        <button type="button" class="btn rounded-pill btn-primary follow-btn ">Follow</button>
                    </div> -->


                </div>
            </div>
        </div>

        <!-- For infintie scroll -->
        <div class="more d-flex justify-content-center align-items-center mt-5">
            <p class="virtual"></p>
            <span id="postLoadingSpinner" class="spinner-border spinner-border-md pl-1 text-primary" role="status"
                aria-hidden="true"></span>
        </div>
    </div>

    <!-- Modal open by create post button -->
    <div class="modal fade create-post-modal" id="createPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <!-- Header -->
                <div class="modal-header py-3 border-0 pb-0">
                    <div class="d-flex gap-2 align-items-center">
                        <img id="avatarImg" src="
                        <?php
                        $src = '';
                        if (!isset($_SESSION['avatar_url']))
                            $src = avatarUrl($_SESSION['username']);
                        else
                            $src = $_SESSION['avatar_url'];
                        echo $src;
                        ?>
                        " alt="Avatar" class="avatar avatar--sm ">
                        <span class="fw-semibold">
                            <?php echo $_SESSION['username'] ?>
                        </span>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="editor" class="create-post-modal__editor">

                    </div>
                </div>

                <!-- For media preview -->
                <div id="preview"></div>

                <div class="modal-footer border-0">
                    <button id="postSubmitBtn" type="button" class="btn btn-primary" disabled>
                        <span>Post</span>
                        <span id="postBtnSpinner" class="spinner-border spinner-border-sm pl-1" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>


</body>

</html>