$(document).ready(function () {
  const submitBtn = $("#submitBtn");
  const spinner = $("#spinner");
  const editProfileForm = $("#editProfileForm");
  const avatarImg = $("#avatarImg");
  console.log("avatarImg", avatarImg);

  const restoreBtn = () => {
    submitBtn.prop("disabled", false);
    spinner.hide();
  };

  const disableBtn = () => {
    submitBtn.prop("disabled", true);
    spinner.show();
  };

  restoreBtn();

  editProfileForm.submit(function (event) {
    event.preventDefault();
    const username = $("#username").val();
    const password = $("#password").val();
    const confirmPassword = $("#confirmPassword").val();
    if (username === "") {
      iziToast.error({
        title: "Error",
        message: "Username cannot be empty",
      });
      return;
    }

    if (password !== "" && password !== confirmPassword) {
      iziToast.error({
        title: "Error",
        message: "Passwords do not match",
      });
      return;
    }
    handleUpdateProfile(username, password);
  });


  const handleUpdateProfile = (username, password) => {
    const formData = new FormData();
    let haveAvatar = "false";
    console.log("hi");
    const avatarFile = $("#avatar").prop("files")[0];
    if (avatarFile) {
      haveAvatar = "true";
      console.log("avatar file", avatarFile);
      formData.append("avatar", avatarFile);
    }
    formData.append("haveAvatar", haveAvatar);
    formData.append("username", username);
    formData.append("password", password);

    disableBtn();
    $.ajax({
      url: "/profile_update_handler",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        console.log("response", response);
        if (response == "successful") {
          location.reload();
        }
        else {
          restoreBtn();
          iziToast.error({
            title: "Error",
            message: response,
          });
        }
      },
      error: function (xhr) {
        iziToast.error({
          title: "Error",
          message: xhr.responseText,
        });

        restoreBtn();
        console.log("error", error);
      },
    });
  };
});
