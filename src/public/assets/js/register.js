$(document).ready(function () {
  $("#register-form").on("submit", function (event) {
    event.preventDefault();
    var formData = $(this).serialize();
    // trigger ajax request to create post
    $.ajax({
      url: "/register_handler",
      type: "POST",
      data: formData,
      success: (postData) => {
        console.log(postData);
        // if (postData == "Registration successful, Please login") {go to login page}
        if (postData == "Registration successful, Please login") {
          // can show registration successful message here before redirecting to login page
          // redirect to home page
          document.location.href = "/login";
        }
        // if (postData == "Passwords do not match || This email or username is already registered || Registration failed, Please try again") {print error message}
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText);
        iziToast.error({
          title: "Error",
          message: xhr.responseText,
        });
      },
    });
  });
});
