function placeholderAvatarUrl(username) {
  let firstChar = username.charAt(0).toUpperCase();
  let background = "";
  let textColor = "";

  if (firstChar >= "A" && firstChar <= "G") {
    background = "E2E2FD";
    textColor = "6366F1";
  } else if (firstChar >= "H" && firstChar <= "N") {
    background = "F8ECC7";
    textColor = "F5B800";
  } else if (firstChar >= "O" && firstChar <= "U") {
    background = "E6FAF5";
    textColor = "00CC99";
  } else if (firstChar >= "V" && firstChar <= "Z") {
    background = "F1E6FB";
    textColor = "6F05D6";
  } else {
    // Handle non-alphabetic characters or other cases
    background = "F1E6FB";
    textColor = "6F05D6";
  }

  return (
    "https://api.dicebear.com/5.x/initials/svg?seed=" +
    username +
    "&backgroundColor=" +
    background +
    "&textColor=" +
    textColor +
    "&fontSize=38"
  );
}

function formatDate(dateTimeString) {
  const utcTimestamp = new Date(dateTimeString).getTime();
  const localTimestamp = utcTimestamp + 8 * 60 * 60 * 1000; // 8 hours in milliseconds

  const localDateTime = new Date(localTimestamp);
  const now = new Date();

  const diffSeconds = Math.floor((now - localDateTime) / 1000);
  const diffMinutes = Math.floor(diffSeconds / 60);
  const diffHours = Math.floor(diffMinutes / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffSeconds < 60) {
    return "just now";
  } else if (diffMinutes < 60) {
    return `${diffMinutes}m ago`;
  } else if (diffHours < 24) {
    return `${diffHours}h ago`;
  } else if (diffDays < 7) {
    return `${diffDays}d ago`;
  } else {
    const options = { month: "short", day: "numeric" };
    return localDateTime.toLocaleDateString("en-US", options);
  }
}

function getCurrentUserAvatarUrl() {
  if (!session) {
    return;
  }

  if (session.avatar_url) {
    return session.avatar_url;
  }

  return placeholderAvatarUrl(session.username);
}
