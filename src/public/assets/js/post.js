let isProfilePage = window.location.pathname.startsWith("/profile");

$(function () {
  let createPostModal = null;
  let quill = null;

  if (document.querySelector("#createPostModal")) {
    createPostModal = new bootstrap.Modal("#createPostModal");
  }

  //Disable the auto show create post modal if needed
  // if (DEBUG) {
  //   createPostModal.show();
  // }
  if (document.querySelector("#editor")) {
    quill = new Quill("#editor", {
      theme: "snow",
      placeholder: `What are your thoughts , ${session.username}?`,
      //link placeholder
    });

    // change the link placeholder to www.github.com
    const qTooltip = quill.theme.tooltip;
    const qInput = qTooltip.root.querySelector("input[data-link]");
    qInput.dataset.link = "https://google.com";

    //listen on quill editor for text change event
    quill.on("text-change", () => {
      //check if the text is empty or not
      if (quill.getText().trim().length > 0) {
        $("#postSubmitBtn").prop("disabled", false);
      } else {
        $("#postSubmitBtn").prop("disabled", true);
      }
    });
  }

  const restoreSubmitBtn = () => {
    $("#postSubmitBtn").prop("disabled", true);
    $("#postBtnSpinner").hide();
  };

  restoreSubmitBtn();

  $("#postSubmitBtn").click(() => {
    const content = quill.root.innerHTML;

    //disable the button
    $("#postSubmitBtn").prop("disabled", true);
    $("#postBtnSpinner").show();

    //Transform quill editor content to html
    console.log(content);
    // trigger ajax request to create post
    let haveMedia = "false";
    const formData = new FormData();
    if (selectedMedia) {
      haveMedia = "true";
      //Print media type
      console.log("selectedMedia.type", selectedMedia.type);
      formData.append("media", selectedMedia);
    }
    formData.append("haveMedia", haveMedia);

    //Avoid empty content
    formData.append("content", quill.getText().trim() == "" ? "" : content);

    //print form data
    for (const pair of formData.entries()) {
      console.log(pair[0] + ", " + pair[1]);
    }

    $.ajax({
      url: "/post_handler",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        // Handle success response
        console.log("Upload successful:", response);
        //  alert("Media uploaded successfully!");
        restoreSubmitBtn();
        createPostModal.hide();
        //Append the new post
        appendPostCardElement(JSON.parse(response), true);
      },
      error: function (xhr, status, error) {
        // Handle error response
        console.error("Upload failed:", error);
        alert("Post failed, please try again!");
        restoreSubmitBtn();
      },
    });
    console.log("selectedMedia", selectedMedia);
  });

  /**
   * Handle media upload
   */
  let selectedMedia = null;
  const selectImageBtn = $("#selectImageBtn");
  const selectVideoBtn = $("#selectVideoBtn");
  const mediaInput = $("#mediaInput");

  selectImageBtn.click(() => {
    mediaInput.click();
  });

  selectVideoBtn.click(() => {
    mediaInput.click();
  });

  /**
   * Handle upload media
   */
  $("#mediaInput").change(function (e) {
    //Store the media file
    selectedMedia = e.target.files[0];
    var filesize = (selectedMedia.size / 1024 / 1024).toFixed(4);
    if (filesize > 50) {
      alert("File size must be less than 50 MB");
      return;
    }
    //Open the modal
    createPostModal.show();

    if (!selectedMedia) return;

    $("#preview").empty();

    $("#postSubmitBtn").prop("disabled", false);

    const previewContainer = $(
      '<div class="preview-container mx-auto rounded-3 overflow-hidden border">'
    );
    const ratio = $('<div class="ratio ratio-16x9">');
    previewContainer.append(ratio);

    if (selectedMedia.type.startsWith("image")) {
      const img = $("<img>").attr("src", URL.createObjectURL(selectedMedia));

      img.addClass("object-fit-contain");
      ratio.append(img);
      img.on("load", function () {
        URL.revokeObjectURL(this.src);
      });
    } else if (selectedMedia.type.startsWith("video")) {
      const video = $("<video controls>").attr(
        "src",
        URL.createObjectURL(selectedMedia)
      );
      ratio.append(video);
      video.on("loadedmetadata", function () {
        URL.revokeObjectURL(this.src);
      });
    } else {
      console.error("Unsupported media type:", selectedMedia.type);
    }

    $("#preview").append(previewContainer);
  });

  //Handle if modal close
  $("#createPostModal").on("hidden.bs.modal", (e) => {
    console.log("modal closed");
    quill.setText("");
    $("#postSubmitBtn").prop("disabled", true);
    //reset preview
    $("#preview").empty();
    //reset selected media
    selectedMedia = null;
    //reset media input
    mediaInput.val("");
  });

  /**
   * Handle post loading
   */

  // How many posts are loaded
  const postContainer = $("#postContainer");
  let loadedPosts = 0;

  if (posts) {
    loadedPosts = 10;
  }

  let noMorePosts = false;
  let loadingPosts = false;
  const limit = 10;

  const loadMorePosts = () => {
    if (noMorePosts || loadingPosts) return;

    loadingPosts = true;

    let data = {
      offset: loadedPosts,
      limit,
    };

    if (isProfilePage) {
      //get the username from the url query
      data.username = targetUsername;
    }

    $.ajax({
      url: isProfilePage
        ? "load_target_posts_handler.php"
        : "load_posts_handler.php",
      method: "POST",
      data,
      success: function (response) {
        loadingPosts = false;
        response = JSON.parse(response);

        if (response.length == 0) {
          noMorePosts = true;
          $("#postLoadingSpinner").hide();
        }

        console.log(response);

        appendNewPostCards(response);
        loadedPosts += limit;
      },
      error: function (error) {
        loadingPosts = false;
        console.log("Error loading more posts: " + error);
      },
    });
  };

  //For infinite scroll
  const intersectionObserver = new IntersectionObserver((entries) => {
    if (entries[0].intersectionRatio <= 0) return;

    console.log("Loading more posts...");
    loadMorePosts();
  });

  //initial load
  loadMorePosts();

  // Start observing
  intersectionObserver.observe(document.querySelector(".more"));

  //create a post card in html
  const appendPostCardElement = (post, prepend = false) => {
    const html = commonTags.html`<!-- Post start here -->
    <div
      class="social-card p-0 overflow-hidden"
      data-post-id="${post.post_id}"
    >
      <div class="d-flex gap-2 align-items-center social-card-header">
        <a href="profile?username=${post.username}">
        <img
          id="avatarImg"
          src="${
            post.profile_picture_url ?? placeholderAvatarUrl(post.username)
          }"
          alt="Avatar"
          class="avatar avatar--sm"
        />
        </a>

        <div class="d-flex flex-column">
          <a
          style="color: black;"
          href="profile?username=${post.username}" class="fw-semibold"> ${
      post.username
    } </a>

          <!-- Post Time -->
          <span class="text-dimmed"> ${formatDate(post.created_at)} </span>
        </div>

        ${
          post.user_id == session.user_id &&
          commonTags.html` <!-- For delete related -->
         <div class="ml-auto">
          <div class="dropdown">
            <button class="btn btn-icon btn-icon-only btn-transparent" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-three-dots"></i>
            </button>
            
            <ul class="dropdown-menu">
              <li>
                <button class="dropdown-item text-danger" data-button-type="delete">Delete Post</button>
              </li>
            </ul>
          </div>
        </div>`
        }
      </div>

      ${
        post.caption &&
        `<div class="social-card-content mb-2">${post.caption}</div>`
      }
    

      ${
        post.media_url &&
        commonTags.html`<div class="mx-auto overflow-hidden border" style="margin-bottom: 11px;">
       <div class="ratio ratio-16x9">
         <!-- for video -->
         ${
           post.type.startsWith("video") == true
             ? commonTags.html`<video controls="" src="${post.media_url}"></video>`
             : commonTags.html`<img class="object-fit-cover" src="${post.media_url}" alt="Image" />`
         }
       </div>
     </div>`
      }

      <!-- Like comment count -->
      <div class="d-flex mx-3 pb-2 mt-2 gap-3" style="border-bottom: solid 1px #f6f6f6;">
         <span class="text-dimmed like-count">${post.likes} likes</span>
         <span class="text-dimmed comment-count">${
           post.comments
         } comments</span>
      </div>

  
      <!-- Toolbar -->
      <div class="d-flex px-3 mt-3 mb-3 gap-3">
        <button
          class="toolbar-rounded-btn like-btn ${post.liked ? "active" : ""}" 
          data-button-type="like" 
        >
          <svg
            width="18"
            height="18"
            viewBox="0 0 18 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M4.81519 8.27499L8.00289 1C8.63696 1 9.24506 1.25549 9.69342 1.71027C10.1418 2.16504 10.3937 2.78185 10.3937 3.425V6.65833H14.9043C15.1353 6.65567 15.3641 6.704 15.5749 6.79996C15.7857 6.89592 15.9734 7.03722 16.125 7.21407C16.2766 7.39091 16.3885 7.59908 16.4529 7.82414C16.5174 8.04921 16.5328 8.28579 16.4981 8.51749L15.3983 15.7925C15.3407 16.178 15.1477 16.5294 14.8548 16.7819C14.5619 17.0344 14.1889 17.1711 13.8045 17.1666H4.81519M4.81519 8.27499V17.1666M4.81519 8.27499H2.42442C2.0017 8.27499 1.5963 8.44532 1.29739 8.7485C0.998489 9.05168 0.830566 9.46289 0.830566 9.89165V15.55C0.830566 15.9787 0.998489 16.39 1.29739 16.6931C1.5963 16.9963 2.0017 17.1666 2.42442 17.1666H4.81519"
              stroke="#F97316"
              stroke-width="1.61666"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>

        <button class="toolbar-rounded-btn" data-button-type="comment">
          <svg
            width="18"
            height="18"
            viewBox="0 0 18 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16.5476 8.58336C16.5504 9.68325 16.2971 10.7683 15.8082 11.75C15.2285 12.9265 14.3373 13.916 13.2346 14.6078C12.1318 15.2995 10.8609 15.6662 9.56422 15.6667C8.47985 15.6696 7.41015 15.4126 6.44224 14.9167L1.75928 16.5L3.32027 11.75C2.83137 10.7683 2.57802 9.68325 2.58085 8.58336C2.58135 7.26815 2.94284 5.97907 3.62484 4.86048C4.30683 3.7419 5.28239 2.838 6.44224 2.25002C7.41015 1.75413 8.47985 1.49716 9.56422 1.50002H9.975C11.6874 1.59585 13.3049 2.32899 14.5176 3.55907C15.7303 4.78915 16.4531 6.42973 16.5476 8.16669V8.58336Z"
              stroke="#A1A1AA"
              stroke-width="1.66667"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>
      </div>

      <!-- For Comment -->
      <div class="d-flex px-3 mb-4 gap-2 w-full">
        <img
          src="${session.avatar_url ?? placeholderAvatarUrl(session.username)}"
          alt="Avatar"
          class="avatar avatar--sm"
        />
        

        <form class="d-flex" style="flex: 1;" data-form-type="comment" >
           <input
             required
             data-input-type="comment"
             type="text"
             class="form-control form-control-sm px-3 w-full"
             placeholder="Write a comment as ${session.username}..."
             style="border-radius: 20px;"
           />

           <button type="submit" style="color: #A0A0A0;" class="btn btn-icon btn-icon-only btn-transparent">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
      </div>


      <!-- Comment box -->
      <div id="commentContainer" class="d-flex flex-column gap-3 px-3">
     ${
       post.comments_array.length > 0 &&
       post.comments_array.map((comment) => getCommentHTML(comment))
     }
      </div>
    </div>`;

    //create a html instance of the post card
    const card = $(html);

    if (prepend) {
      $(card).insertAfter("#createPost");
    } else {
      postContainer.append(card);
    }

    //Handle like
    card.find("[data-button-type='like']").click(function () {
      console.log("like");
      //get post id
      handleLike(post.post_id, $(this), card);
    });

    //Handle comment
    card.find("[data-button-type='comment']").click(function () {
      //focus on the comment input
      card.find("input").focus();
    });

    //Delete post
    card.find("[data-button-type='delete']").click(function () {
      //delete post
      handleDeletePost(post.post_id, card);
    });

    //Handle comment form
    card.find("[data-form-type='comment']").submit(function (e) {
      e.preventDefault();
      const commentInput = card.find("[data-input-type='comment']");
      const comment = commentInput.val();
      if (comment) {
        handleComment(post.post_id, comment, card);
      }
      commentInput.val("");
    });
  };

  const getCommentHTML = (comment) => {
    return `<div class="d-flex px-3 gap-3 mb-3">
    <img
      src="${comment.avatar ?? placeholderAvatarUrl(comment.username)}"
      alt="Avatar"
      class="avatar avatar--sm "
    />
    <div class="comment-box d-flex flex-column">
      <span class="comment-box__title"> ${comment.username} </span>
      <span class="comment-box__content ">${comment.comment}</span>
    </div>
  </div>`;
  };

  const handleComment = (postId, comment, card) => {
    const commentCountEl = card.find(".comment-count");
    let commentCount = parseInt(commentCountEl.text()) || 0;
    console.log("handleComment", postId, comment);
    const updateCommentCount = (count) => {
      commentCountEl.text(count + " comments");
    };
    $.ajax({
      url: "post_comment_handler.php",
      type: "POST",
      data: {
        post_id: postId,
        comment: comment,
      },
      success: (data, status, xhr) => {
        console.log("comment success", data);
        // const comment = data.comment;
        const comment = JSON.parse(data);
        data.username = session.username;
        data.avatar = session.avatar_url;

        updateCommentCount(comment.comment_count);
        card.find("#commentContainer").prepend(getCommentHTML(comment));
      },
      error: function (xhr, status, error) {
        iziToast.error({
          title: "Error",
          message: xhr.responseText,
        });
      },
    });
  };

  const handleDeletePost = (postId, card) => {
    console.log("handleDeletePost", postId);
    $.ajax({
      url: "post_delete_handler.php",
      type: "POST",
      data: {
        post_id: postId,
      },
      success: (data, status, xhr) => {
        console.log("delete success", data);
        card.remove();
      },
      error: function (xhr, status, error) {
        iziToast.error({
          title: "Error",
          message: xhr.responseText,
        });
      },
    });
  };

  const handleLike = (postId, likeButtonEl, cardEl) => {
    const likeCountEl = cardEl.find(".like-count");
    let likeCount = parseInt(likeCountEl.text()) || 0;
    console.log("handleLike", postId);

    console.log("likeCountEl", likeCountEl);
    console.log("likeCount", likeCount);

    const updateLikeCount = (count) => {
      likeCountEl.text(count + " likes");
    };

    if (likeButtonEl.hasClass("active")) {
      //optimistic update
      //update like count
      likeCount--;

      //unlike
      $.ajax({
        url: "post_dislike_handler.php",
        type: "POST",
        data: {
          post_id: postId,
        },
        success: (data, status, xhr) => {
          console.log("unlike success", data);

          //update like count=
          updateLikeCount(data);

          //set like to inactive
          likeButtonEl.removeClass("active");
        },
        error: function (xhr, status, error) {
          iziToast.error({
            title: "Error",
            message: xhr.responseText,
          });

          //set like to active
          likeButtonEl.addClass("active");

          //revert like count
          likeCount++;
          updateLikeCount(likeCount);
        },
      });
    } else {
      //optimistic update
      //update like count
      likeCount++;

      //like
      $.ajax({
        url: "post_like_handler.php",
        type: "POST",
        data: {
          post_id: postId,
        },
        success: (data, status, xhr) => {
          console.log("like success", data);

          //update like count
          updateLikeCount(data);

          //set like to active
          likeButtonEl.addClass("active");
        },
        error: function (xhr, status, error) {
          iziToast.error({
            title: "Error",
            message: xhr.responseText,
          });

          //set like to inactive
          likeButtonEl.removeClass("active");

          //revert like count
          likeCount--;
          updateLikeCount(likeCount);
        },
      });
    }
    //set active class
    likeButtonEl.toggleClass("active");
    updateLikeCount(likeCount);
  };

  //Handle load list of post
  const appendNewPostCards = (posts) => {
    posts.forEach((post) => {
      //Sample data {"post_id":"0d8fa00f-b13b-48be-9a0e-6544ada45c04","user_id":"7a774f43-3508-4f87-a936-63a3ff43a5da","caption":"<p>tyd</p>","media_name":null,"type":null,"username":"elvincth@gmail.com","profile_picture_url":"https://soctify-bucket.s3.ap-northeast-1.amazonaws.com/post_media/main-qimg-217015358349186e0e382cb15c5d7c63-lq.jpeg","likes":0,"comments":0,"liked":0,"media_url":"https://soctify-bucket.s3.ap-northeast-1.amazonaws.com/post_media/?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIA57QGC5BAZZ5AYO5Y%2F20230328%2Fap-northeast-1%2Fs3%2Faws4_request&X-Amz-Date=20230328T103334Z&X-Amz-SignedHeaders=host&X-Amz-Expires=600&X-Amz-Signature=c1baa2a3f52bf7c2e3c8ae0b3f15b219ca909d0694802f7b8615d2726e8334ee"}
      appendPostCardElement(post);
    });
  };

  if (typeof posts !== "undefined") {
    appendNewPostCards(posts);
  }

  const showRecommendUser = (response) => {
    let result = "";
    for (let i = 0; i < response.length; i++) {
      avatar = response[i].profile_picture_url;
      if (
        response[i].profile_picture_url == null ||
        response[i].profile_picture_url == ""
      ) {
        avatar = placeholderAvatarUrl(response[i].username);
      }
      result += `<div class="card">
                <a href="/profile?username=${response[i].username}">
                  <img src="${avatar}" alt="Avatar" class="avatar avatar--md ">
                </a>
                <span class="fw-bold my-1 widget-label">
                  ${response[i].username}
                </span>
                <input type="hidden" value="${response[i].id}">
                <!-- Pill button -->
                <button type="button" value="${i}" class="btn rounded-pill btn-primary follow-btn " style="padding: 1px 15px;
                font-size: 0.9rem;">Follow</button>
              </div>`;
    }
    document.getElementById("recommendUser").innerHTML = result;
  };

  //handle button click in class card
  $(document).on("click", ".card button", function (e) {
    let index = e.target.value;
    let following_id = $(".card input")[index].value;
    const followButton = $(this);
    console.log(following_id);

    if (followButton.hasClass("follow-btn")) {
      follow(following_id, followButton);
    } else {
      unfollow(following_id, followButton);
    }
  });

  if (window.location.pathname == "/") {
    showRecommendUser(recommendUser);
  }
});

function follow(following_id, followButton) {
  const username = $(this).data("follow-username");
  $.ajax({
    url: "follow_handler.php",
    method: "POST",
    data: {
      following_id: following_id,
    },
    success: function (response) {
      console.log(response);
      if (response == "followed") {
        followButton.removeClass("follow-btn");
        //add btn outline
        followButton.addClass("following-btn");
        //change text to following
        followButton.text("Following");
      } else {
        console.log("error to follow");
      }
    },
    error: function (error) {
      console.log("Error to get user status");
    },
  });
}

function unfollow(following_id, followButton) {
  $.ajax({
    url: "unfollow_handler.php",
    method: "POST",
    data: {
      following_id: following_id,
    },
    success: function (response) {
      console.log(response);
      if (response == "unfollowed") {
        followButton.removeClass("following-btn");
        //add btn outline
        followButton.addClass("follow-btn");
        //change text to following
        followButton.text("Follow");
      } else {
        console.log("error to follow");
      }
    },
    error: function (error) {
      console.log("Error to unfollow");
    },
  });
}
