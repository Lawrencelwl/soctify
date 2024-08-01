$(document).ready(function () {
  $("#login-form").on("submit", function (event) {
    event.preventDefault();
    var formData = $(this).serialize();
    // trigger ajax request to create post
    $.ajax({
      url: "/login_handler",
      type: "POST",
      data: formData,
      success: (postData) => {
        console.log("Success", postData);
        if (postData == "Success") {
          // can show login successful message here before redirecting to home page
          // redirect to home page
          document.location.href = "/";
        }
        // if (postData == "Invalid password || Invalid email") {print error message}
      },
      error: function (xhr, status, error) {
        iziToast.error({
          title: "Error",
          message: xhr.responseText,
        });

        console.log("Err", xhr.responseText);
      },
    });
  });
});
