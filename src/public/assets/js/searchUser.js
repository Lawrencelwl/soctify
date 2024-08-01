$(document).ready(function () {
  $("#searchUserBtn").click(function () {
    $("#seacrhLoadingSpinner").hide();
  });
  const searchModal = new bootstrap.Modal("#searchModal");
  let user = [];
  let userData = [];
  let debounce = null;
  //Search All Users
  const searchSuggest = () => {
    $("#search").autocomplete({
      source: user,
      select: function (event, ui) {
        let search = ui.item.value;
        if (search.length > 0) {
          showRelevantUser(search);
        } else {
          document.getElementById("searchResult").innerHTML = "";
        }
      },
    });
    $("#search").autocomplete("option", "appendTo", ".eventInsForm");
  };

  $("#search").on("input", function (e) {
    searchValue = $(this).val();
    if (searchValue.length == 0) {
      $("#seacrhLoadingSpinner").hide();
    } else {
      $("#seacrhLoadingSpinner").show();
    }
    clearTimeout(debounce);
    document.getElementById("searchResult").innerHTML = "";
    // $("#seacrhLoadingSpinner").show();
    debounce = setTimeout(function (value) {
      $.ajax({
        url: "get_relevent_user_handler.php",
        method: "POST",
        data: {
          value: value,
        },
        success: function (response) {
          $("#seacrhLoadingSpinner").hide();
          response = JSON.parse(response);
          console.log(response);
          user = [];
          for (let i = 0; i < response.length; i++) {
            // console.log(user);
            user.push(response[i].username);
            user.push(response[i].email);
          }
          user = [...new Set(user)];
          userData = response;
          searchSuggest(user);
          showRelevantUser(value);
          // user = response;
        },
        error: function (error) {
          console.log("Error to get user data for search");
        },
      });
    }, 500, searchValue);
  });

  const showRelevantUser = (searchText) => {
    let result = "";
    for (let i = 0; i < userData.length; i++) {
      if (
        ~userData[i].username.indexOf(searchText) ||
        ~userData[i].email.indexOf(searchText)
      ) {
        prefix = `<div class="card">
            <div class="card-body p-0">
                <ul class="list-unstyled mb-0">
                    <li class="p-3">
                            <div class="d-flex flex-row gap-3 align-items-center justify-content-between">
                              <a href="/profile?username=${userData[i].username}" class="d-flex justify-content-between">
                                <div>
                                  <img class="avatar"
                                    src="${userData[i].profile_picture_url ?? placeholderAvatarUrl(userData[i].username)}">
                                </div>
                                <div class="align-self-center ms-3">
                                    <p class="fw-bold mb-0 align-self-center text-break">${userData[i].username}</p>
                                </div>
                              </a>`;
        if (userData[i].following == true) {
          followBtn = `         <div class="pt-1 flex-row-reverse follow-btn-card">
                                <input type="hidden" value="${userData[i].id}"></input>
                                <button type="button" class="btn rounded-pill btn-primary following-btn ">Following</button>
                              </div>`;
        } else {
          followBtn = `         <div class="pt-1 flex-row-reverse follow-btn-card">
                                <input type="hidden" value="${userData[i].id}"></input>
                                <button type="button" class="btn rounded-pill btn-primary follow-btn ">Follow</button>
                              </div>`;
        }
        suffix = `           </div>
                      </li>
                  </ul>
              </div>
          </div>`;
        if (userData[i].id != session.user_id) {
          result += prefix + followBtn + suffix;
        } else {
          result += prefix + suffix;
        }
      }
    }
    document.getElementById("searchResult").innerHTML = result;
  };

  $(document).on("click", ".follow-btn-card button", function (e) {
    const following_id = $(this).prev().val();
    const followButton = $(this);
    console.log(following_id);
    if (followButton.hasClass("follow-btn")) {
      follow(following_id, followButton);
    } else {
      unfollow(following_id, followButton);
    }
  });
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
    }
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