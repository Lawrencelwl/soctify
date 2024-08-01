# SOCTIFY

A social media platform

Url 1: https://soctify.onrender.com/
or
Url 2: https://soctify.up.railway.app

```
-src
- app
    - controllers
      - HomeController.php
      - AuthController.php
      - UserController.php
      - PostController.php
      - CommentController.php
    - models
      - User.php
      - Post.php
      - Comment.php
    - views
      - home.php
      - login.php
      - register.php
      - dashboard.php
      - profile.php
      - post.php
  - config
    - database.php
    - routes.php
  - public
    - assets
      - css
      - js
      - images
    - index.php
    - .htaccess
- storage
  - logs
  - uploads
- vendor
```

To run our project:

1. Go to root d
2. Run `docker-compose up` to start the server
3. Go to `localhost:80` to see the website
4. Go to `localhost:8080` to see the database

```
Soctify
├─ .vscode
│  └─ settings.json
├─ config
│  ├─ apache2
│  │  └─ 000-default.conf
│  └─ php.ini
├─ src
│  ├─ app
│  │  ├─ config
│  │  │  └─ database.php
│  │  ├─ controllers
│  │  │  ├─ .gitkeep
│  │  │  ├─ AccountController.php
│  │  │  ├─ AuthController.php
│  │  │  ├─ ChatController.php
│  │  │  ├─ MiddlewareController.php
│  │  │  ├─ PostController.php
│  │  │  └─ S3Controller.php
│  │  ├─ middleware
│  │  ├─ utils
│  │  │  └─ ui.php
│  │  └─ views
│  │     ├─ inc
│  │     │  ├─ head.php
│  │     │  ├─ navbar.php
│  │     │  └─ search.php
│  │     ├─ .gitkeep
│  │     ├─ chat.php
│  │     ├─ home.php
│  │     ├─ login.php
│  │     ├─ profile.php
│  │     ├─ register.php
│  │     └─ settings.php
│  ├─ public
│  │  ├─ assets
│  │  │  ├─ css
│  │  │  │  ├─ auth.css
│  │  │  │  ├─ chat.css
│  │  │  │  ├─ home.css
│  │  │  │  ├─ quill.css
│  │  │  │  └─ styles.css
│  │  │  ├─ images
│  │  │  │  ├─ .gitkeep
│  │  │  │  ├─ favicon.ico
│  │  │  │  ├─ login.svg
│  │  │  │  ├─ login_banner.png
│  │  │  │  ├─ logo-circle.svg
│  │  │  │  ├─ logo.svg
│  │  │  │  ├─ photo-icon.svg
│  │  │  │  ├─ register.svg
│  │  │  │  └─ video-icon.svg
│  │  │  └─ js
│  │  │     ├─ .gitkeep
│  │  │     ├─ chat.js
│  │  │     ├─ login.js
│  │  │     ├─ main.js
│  │  │     ├─ post.js
│  │  │     ├─ profile.js
│  │  │     ├─ register.js
│  │  │     ├─ searchUser.js
│  │  │     ├─ settings.js
│  │  │     └─ utils.js
│  │  ├─ .htaccess
│  │  ├─ chat.php
│  │  ├─ chat_createChatRoom_handler.php
│  │  ├─ chat_handler.php
│  │  ├─ chat_sendmessage_handler.php
│  │  ├─ chat_showFollowed_handler.php
│  │  ├─ connect_chatroom_handler.php
│  │  ├─ follow_handler.php
│  │  ├─ get_password_handler.php
│  │  ├─ get_recommand_user_handler.php
│  │  ├─ get_relevent_user_handler.php
│  │  ├─ get_user_status_handler.php
│  │  ├─ index.php
│  │  ├─ load_posts_handler.php
│  │  ├─ load_target_posts_handler.php
│  │  ├─ login.php
│  │  ├─ login_handler.php
│  │  ├─ logout.php
│  │  ├─ post_comment_handler.php
│  │  ├─ post_delete_handler.php
│  │  ├─ post_dislike_handler.php
│  │  ├─ post_handler.php
│  │  ├─ post_like_handler.php
│  │  ├─ profile.php
│  │  ├─ profile_update_handler.php
│  │  ├─ register.php
│  │  ├─ register_handler.php
│  │  ├─ settings.php
│  │  ├─ set_chatroom_handler.php
│  │  └─ unfollow_handler.php
│  ├─ vendor
│  └─ config.php
├─ vendor
├─ .env
├─ .gitignore
├─ composer.json
├─ docker-compose.yml
├─ Dockerfile
├─ makefile
└─ README.md
```
