<?php
require_once __DIR__ . '/../../config.php';
?>

<!DOCTYPE html>
<html>

<head>
    <?php include VIEW_INC_PATH . '/head.php'; ?>
    <link rel="stylesheet" href="assets/css/chat.css" />
    <script src="assets/js/chat.js" defer></script>
</head>

<body>
    <?php include VIEW_INC_PATH . '/navbar.php'; ?>


    <div class="container page-wrapper  overflow-hidden">

        <div class="row rounded">
            <div id="chatList" class="col-md-6 col-lg-5 col-xl-4 mb-4 mb-md-0 border-end border-start">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold  mb-3 text-center text-lg-start mt-3">Chat</h5>
                    <div>
                        <button id="newChat" type="button" class="btn border-0" data-bs-toggle="modal" data-bs-target="#chatModal">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>


                <!-- <div class="card">
                    <div class="card-body p-0">
                        <ul class="list-unstyled mb-0">
                            <li class="p-3" style="background-color: #eee;">
                                <a href="#!" class="d-flex justify-content-between">
                                    <div class="d-flex flex-row gap-3 align-items-center">
                                        <img class="avatar" src="https://api.dicebear.com/5.x/initials/svg?seed=elvincth@gmail.com&backgroundColor=E2E2FD&textColor=6366F1&fontSize=38">
                                        <div>
                                            <p class="fw-bold mb-0 ">John Doe</p>
                                            <p class="small text-muted m-0">Hello, Are you there?</p>
                                        </div>
                                    </div>
                                    <div class="pt-1">
                                        <p class="small text-muted mb-1">Just now</p>
                                        <span class="badge bg-danger float-end">1</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div> -->


                <!-- Placeholder message -->
                <span id="placeholder" class="d-none text text-center d-flex align-items-center justify-content-center h-100 text-muted">
                    You have no messages yet. Please start by clicking the plus button.</span>

            </div>

            <div class="col-md-6 col-lg-7 col-xl-8 position-relative border-end">
                <div class="overflow-auto pt-3" id="chatroom_message" style="height: calc(100vh - 130px);">
                    <span id="chatAreaLoadingSpinner" class="spinner-border p-4 my-3 position-absolute top-50 start-50" role="status" aria-hidden="true">
                    </span>
                    <span id="placeholder-chat" class="text text-center d-flex align-items-center justify-content-center h-100 text-muted">
                        Please select a chat to start messaging.</span>
                    <!--Others send message -->
                    <!-- <div class="d-flex flex-row justify-content-start mb-4">
                        <img class="avatar" src="https://api.dicebear.com/5.x/initials/svg?seed=elvincth@gmail.com&backgroundColor=E2E2FD&textColor=6366F1&fontSize=38">
                        <div class="p-3 ms-3" style="border-radius: 15px; background-color: #394ced1c;">
                            <p class="small mb-0">Hello and thank you for visiting MDBootstrap. Please click the video
                                below.</p>
                        </div>
                    </div> -->
                    <!--Me send message -->
                    <!-- <div class="d-none flex-row justify-content-end mb-4">
                        <div class="p-3 me-3 border" style="border-radius: 15px; background-color: #fbfbfb;">
                            <p class="small mb-0">Thank you, I really like your product.</p>
                        </div>
                        <img class="avatar" src="https://api.dicebear.com/5.x/initials/svg?seed=elvincth@gmail.com&backgroundColor=E2E2FD&textColor=6366F1&fontSize=38">
                    </div> -->

                </div>


                <!-- Message input -->
                <form id="messageForm" class="card-footer text-muted justify-content-start align-items-center p-3 d-none ">
                    <input type="hidden" id="chatroom_id" class="chatroom_id" value=""></input>
                    <input required type="text" class="form-control form-control-md rounded-pill" id="messageInput" placeholder="Type message">
                    <button type="submit" class="btn btn-icon btn-icon-only btn-transparent" href="#">
                        <i class="bi bi-send"></i>
                    </button>

                </form>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade create-post-modal" id="chatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <!-- Header -->
                <div class="modal-header py-3 border-0 pb-0">
                    <div class="d-flex gap-2 align-items-center">
                        <span class="fw-semibold">
                            New Chat
                        </span>
                    </div>
                    <!-- close button -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body p-0 text-center">
                            <span id="chatLoadingSpinner" class="spinner-border p-4 my-3 mx-auto" role="status" aria-hidden="true"></span>

                            <ul class="list-unstyled mb-0" id="create_new_user">
                                <span id="placeholder-card" class="d-none text text-center d-flex align-items-center justify-content-center h-100 text-muted">
                                    You have chating with all your mutual following.
                                </span>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>