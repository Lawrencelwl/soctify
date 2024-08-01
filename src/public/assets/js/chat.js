$(document).ready(function () {
  $("#chatAreaLoadingSpinner").hide();

  // Variables
  const messageForm = $("#messageForm");
  const messageInput = $("#messageInput");
  const placeholder = $("#placeholder");
  const placeholder_card = $("#placeholder-card");
  const placeholder_chat = $("#placeholder-chat");
  const chatListEl = $("#chatList");
  const createChatListEl = $("#create_new_user");
  var chatroom_message = document.getElementById("chatroom_message");
  let activeChatId = null;
  let currentMsg = '';
  let firstLoad = true;
  let chatroomId = null;

  $("#newChat").click(() => {
    $(".create-chat-username").remove();
    $("#chatLoadingSpinner").show();
    loadCreateNewChatRoom();
  });

  function loadCreateNewChatRoom() {
    // set a new button listener for #newChat
    $.ajax({
      type: "POST",
      url: "chat_showFollowed_handler.php",
      success: function (response) {
        // Chat history loaded successfully, do something
        let user_id = session.user_id;

        const responses = JSON.parse(response);
        console.log("response", responses);

        if (responses.length > 0) {
          responses.forEach((element) => {
            //user_username chatting_username
            let displayUsername = element.user_name;
            appendUserName(
              element,
              displayUsername,
              element.avatar
            );
            // console.log(element);
          });
        } else {
          placeholder_card.removeClass("d-none");
        }
        $("#chatLoadingSpinner").hide();
      },
      error: function (xhr, status, error) {
        // Error occurred, handle error
      },
    });
  }

  function appendUserName(element, displayUsername, userAvatar) {
    const html = `
      <li class="p-3 create-chat-username" data-chat-id="${element.following_id}">
          <a href="#!" class="d-flex justify-content-between">
              <div class="d-flex flex-row gap-3 align-items-center">
                  <img class="avatar" src="${userAvatar ?? placeholderAvatarUrl(displayUsername)}">
                  <div>
                      <p class="fw-bold mb-0 ">${displayUsername}</p>
                  </div>
              </div>
              <div class="pt-1 add_chatroom">
                  <i class="bi bi-plus-lg" style="font-size: 30px;"></i>
              </div>
          </a>
      </li>
    `;
    const child = $(html);
    createChatListEl.append(child);
    // Add click event listener to every <div class="pt-1 add_chatroom">
    child.on("click", function () {
      // console.log($(this).data("chat-id"));
      $('#chatModal').modal('toggle');
      createChatRoom($(this).data("chat-id"));
    });
  }

  function appendChatItem(element, displayUsername, userAvatar) {
    const html = `<div class="p-3 border-bottom chat-item" data-chat-id="${element.chatroom_id
      }">
        <div role="button" class="d-flex justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <img class="avatar"
                    src="${userAvatar ?? placeholderAvatarUrl(displayUsername)}">
                <div>
                    <p class="fw-bold mb-0 text-primary">${displayUsername}</p>

                </div>
            </div>
        </div>
    </div>`;

    const child = $(html);

    chatListEl.append(child);

    // Add click event listener to the chat item
    child.on("click", function () {
      $("#chatAreaLoadingSpinner").show();
      //set the item as active background color grey
      load_count = 0;
      $(".flex-row.justify-content-start").remove();
      $(".flex-row.justify-content-end").remove();
      $(".p-3.border-bottom").removeClass("active");
      $(this).addClass("active");
      placeholder_chat.addClass("d-none");
      $(".card-footer.text-muted").removeClass("d-none");
      $(".card-footer.text-muted").addClass("d-flex");
      $(".chatroom_id").val($(this).data("chat-id"));
      //set the active chat id
      activeChatId = $(this).data("chat-id");
      chatroomId = $(this).data("chat-id");
      currentMsg = '';
      setChatRoomId(chatroomId, "new");
      firstLoad = true;
    });
  }

  function createChatRoom(chating_id) {
    console.log(chating_id);
    $.ajax({
      type: "POST",
      url: "chat_createChatRoom_handler.php",
      data: {
        chating_id: chating_id
      },
      success: function (response) {
        let user_id = session.user_id;

        console.log("response", response);
        $(".border-bottom.chat-item").remove();
        loadChatRoom();
      },

      error: function (xhr, status, error) {
        // Error occurred, handle error
      },
    });
  }

  function loadChatRoom() {
    //Load the chat history
    $.ajax({
      type: "POST",
      url: "chat_handler.php",
      success: function (response) {
        // Chat history loaded successfully, do something
        let user_id = session.user_id;
        const responses = JSON.parse(response);

        console.log("response", responses);

        if (responses.length > 0) {
          placeholder.addClass("d-none");
          responses.forEach((element) => {
            //user_username chatting_username

            let isUser = user_id == element.user_id;
            let displayUsername = isUser
              ? element.chating_username
              : element.user_username;

            appendChatItem(
              element,
              displayUsername,
              isUser ? element.chating_avatar : element.user_avatar
            );

            // console.log(element);
          });
        } else {
          placeholder.removeClass("d-none");
        }
      },

      error: function (xhr, status, error) {
        // Error occurred, handle error
      },
    });
  }

  // Load chat history
  loadChatRoom();

  // Send message on form submit
  messageForm.on("submit", function (e) {
    e.preventDefault();
    sendMessage();
    //setChatRoomId();
  });

  function clearInput() {
    messageInput.val("");
  }

  function appendMessageElement_right(messageText, user_avatar) {
    const messageElement = $("<div>").addClass(
      "d-flex flex-row justify-content-end mb-4"
    );
    const messageContent = $("<div>")
      .addClass("p-3 me-3 border")
      .css({
        "border-radius": "15px",
        "background-color": "#fbfbfb",
      })
      .text(messageText);

    const avatarElement = $("<img>")
      .addClass("avatar")
      .attr(
        "src",
        user_avatar ?? placeholderAvatarUrl(session.username)
      );
    messageElement.append(messageContent);
    messageElement.append(avatarElement);

    // // Add the message to the chat window
    $("#chatroom_message").append(messageElement);
  }

  function appendMessageElement_left(messageText, user_avatar, username) {
    const messageElement = $("<div>").addClass(
      "d-flex flex-row justify-content-start mb-4"
    );
    const avatarElement = $("<img>")
      .addClass("avatar")
      .attr(
        "src",
        user_avatar ?? placeholderAvatarUrl(username)
      );
    const messageContent = $("<div>")
      .addClass("p-3 ms-3")
      .css({
        "border-radius": "15px",
        "background-color": "#394ced1c",
      })
      .text(messageText);

    messageElement.append(avatarElement);
    messageElement.append(messageContent);

    // // Add the message to the chat window
    $("#chatroom_message").append(messageElement);
  }


  // Function to send the message
  function sendMessage() {
    const messageText = messageInput.val().trim();
    const activeChatId = $(".chatroom_id").val();
    console.log(messageText, "|", activeChatId);
    appendMessageElement_right(messageText, session.avatar_url);
    setChatRoomId(chatroomId, "sendNewMessage");
    if (messageText.length > 0) {
      // currentMsg = messageText;
      scrollToBottom(chatroom_message);
      // When the user types a message and hits enter, send the message to the server
      const input = document.getElementById("chat-input");

      $.ajax({
        type: "POST",
        url: "chat_sendmessage_handler.php",
        data: {
          message: messageText,
          chatroom_id: activeChatId,
        },
        success: function (response) {
          // Message sent successfully, do something
          console.log(response);
          currentMsg = response;
          clearInput();
        },
        error: function (xhr, status, error) {
          // Error occurred, handle error
        },
      });
    }
  }

  $(window).on('beforeunload', function () {
    setChatRoomId("sdadqveq", "leaveChatRoom");
    closeEventSource();
    console.log('leave?');
  });


  if (typeof (EventSource) !== 'undefined') {
    var sse = new EventSource('connect_chatroom_handler.php');
    // sse.addEventListener('open', open, false);
    sse.addEventListener('message', message, false);
    //sse.addEventListener('error', error, false);
  } else {
    alert('browser not support');
  }

  function open(event) {
    console.log('chat connected！');
  }

  let load_count = 0;
  let senderId = '';
  let user_avatar = '';
  let username = '';
  let messageContent = '';
  let perviousMsg = [];
  function message(event) {
    $("#chatAreaLoadingSpinner").hide();
    var pullData = JSON.parse(event.data);
    console.log(pullData);
    user_id = session.user_id;
    // Accessing the message data array
    var messages = pullData.message;
    if (messages.length > 0) {
      for (var i = 0; i < messages.length; i++) {
        if (messages[messages.length - 1].message != currentMsg) {
          // Accessing the properties of each message using dot notation
          var messageId = messages[i].message_id;
          // var chatroomId = messages[i].chatroom_id;
          var senderId = messages[i].sender_id;
          var messageContent = messages[i].message;
          var user_avatar = messages[i].user_avatar;
          var username = messages[i].username;
          var messageDate = messages[i].date;

          // console.log(messageId, chatroomId, senderId, messageContent, messageDate);
          if (senderId == user_id) {
            if (firstLoad) {
              appendMessageElement_right(messageContent, user_avatar);
            }
          } else {
            if (perviousMsg.length == 0) {
              appendMessageElement_left(messageContent, user_avatar, username);
            } else {
              for (var j = 0; j < perviousMsg.length; j++) {
                if (perviousMsg[j] == messageId) {
                  console.log('same');
                  break;
                }
                if (j == perviousMsg.length - 1) {
                  appendMessageElement_left(messageContent, user_avatar, username);
                }
              }
            }
          }
        }
      }
      perviousMsg = [];
      if (!firstLoad) {
        for (var i = 0; i < messages.length; i++) {
          perviousMsg.push(messages[i].message_id);
        }
      }
      currentMsg = messages[messages.length - 1].message_id;
      scrollToBottom(chatroom_message);
    }
    firstLoad = false;
  }

  function scrollToBottom(element) {
    element.scroll({ top: element.scrollHeight, behavior: 'smooth' });
  }

  function closeEventSource() {
    if (typeof (sse) !== 'undefined') {
      sse.close();
      console.log('chat close');
    }
  }

  function setChatRoomId(chatroomId, instruction) {
    // $(".chatroom_id").val(chatroom_id);
    // const chatroomId = "4";
    console.log(chatroomId);
    $.ajax({
      type: "POST",
      url: "set_chatroom_handler.php",
      data: {
        chatroom_id: chatroomId,
        instruction: instruction
      },
      success: function (response) {
        // Message sent successfully, do something
        console.log(response);
      },
      error: function (xhr, status, error) {
        // Error occurred, handle error
        console.log(error);
      }
    });
  }
  setChatRoomId("null", "leaveChatRoom");
});
