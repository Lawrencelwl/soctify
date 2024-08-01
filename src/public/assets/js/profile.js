$(function () {
  const avatar = $("#avatarImg");
  const username = $("#username");
  const postCount = $("#postCount");
  const followerCount = $("#followerCount");
  const followingCount = $("#followingCount");
  const followButton = $("#followBtn");

  console.log("userStatus",userStatData);
  if (userStatData) {
    username.text(userStatData.username);
    avatar.attr(
      "src",
      userStatData.profile_picture_url ??
      placeholderAvatarUrl(userStatData.username)
    );
    postCount.text(userStatData.num_posts);
    followerCount.text(userStatData.num_followers);
    followingCount.text(userStatData.num_followings);
    if (userStatData.following){
      followButton.removeClass("follow-btn");
      followButton.addClass("following-btn");
      followButton.text("Following");
    }
  }

  followButton.click(() => {
    //if follow button class has follow-btn else following-btn
    if (followButton.hasClass("follow-btn")) {
      follow();
    } else {
      unfollow();
    }
  });

  function follow(){
    const username = $(this).data("follow-username");
    $.ajax({
      url: "follow_handler.php",
      method: "POST",
      data: {
        following_id: userStatData.id,
      },
      success: function (response) {
        console.log(response);
        if (response == "followed") {
          followerCount.text(parseInt(followerCount.text()) + 1);
          $(".follow-btn").removeClass("follow-btn");
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

  function unfollow()
  {
    $.ajax({
      url: "unfollow_handler.php",
      method: "POST",
      data: {
        following_id: userStatData.id,
      },
      success: function (response) {
        console.log(response);
        if (response == "unfollowed") {
          followerCount.text(parseInt(followerCount.text()) - 1);
          $(".following-btn").removeClass("following-btn");
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
});
