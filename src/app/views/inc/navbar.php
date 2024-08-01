<?php
$current_url = $_SERVER['REQUEST_URI'];
$is_chat_page = (strpos($current_url, 'chat') !== false);
$is_home_page = ($_SERVER['REQUEST_URI'] == '/');
$is_profile_page = (strpos($current_url, 'profile') !== false);
require_once __DIR__ . '/search.php';

?>

<nav class="navbar navbar-expand-lg bg-body px-3 border-bottom sticky-top"
    style="position: fixed; top: 0; right: 0; left: 0;">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/">
            <img src="/assets/images/logo.svg" alt="Logo" width="20" height="24" class="d-inline-block align-text-top">
            <span class="fw-bold text-primary">SOCTIFY</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse flex-1" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($is_home_page) ? 'active text-primary fw-bold' : ''; ?>"
                        aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($is_chat_page) ? 'active text-primary fw-bold' : ''; ?>"
                        href="/chat">Chat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($is_profile_page) ? 'active text-primary fw-bold' : ''; ?>" href=<? echo "/profile?username=" . $_SESSION['username'] ?>>My Profile</a>
                </li>
            </ul>
        </div>
        <!-- <button id="testAPI">test</button> -->
        <button id="searchUserBtn" type="button" class="btn btn-icon btn-icon-only btn-transparent text-primary"
            data-bs-toggle="modal" data-bs-target="#searchModal">
            <i class="bi bi-binoculars-fill"></i>
        </button>
        <div class="d-flex align-items-center">
            <div class="dropdown ms-3">
                <button class="btn text-primary btn-link text-decoration-none dropdown-toggle " type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-fill"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/settings">Settings</a></li>
                    <li><a class="dropdown-item text-danger btn-link" href="/logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>