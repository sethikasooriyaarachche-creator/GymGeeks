// Handle login/register messages
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  if (params.has("msg")) {
    alert(params.get("msg"));
  }
});