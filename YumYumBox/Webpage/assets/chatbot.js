document.addEventListener("DOMContentLoaded", () => {
  const chatBtn = document.getElementById("chat-toggle");
  const chatBox = document.getElementById("chat-box");
  const chatForm = document.getElementById("chat-form");
  const chatInput = document.getElementById("chat-input");
  const chatMessages = document.getElementById("chat-messages");

  if (!chatBtn || !chatBox || !chatForm || !chatInput || !chatMessages) {
    console.error("Chatbot elements not found");
    return;
  }

  // Toggle chatbox visibility
  chatBtn.addEventListener("click", () => {
    chatBox.classList.toggle("d-none");
  });

  // Display a message
  const displayMessage = (message, sender = "bot") => {
    const messageClass = sender === "user" ? "text-end" : "text-start";
    const badgeClass = sender === "user" ? "bg-primary" : "bg-secondary";
    chatMessages.innerHTML += `<div class="${messageClass} mb-2"><span class="badge ${badgeClass}">${message}</span></div>`;
    chatMessages.scrollTop = chatMessages.scrollHeight;
  };

  // Display buttons returned from backend
  const displayButtons = (buttons) => {
    if (!buttons || !buttons.length) return;
    const container = document.createElement("div");
    container.className = "button-container mb-2";

    buttons.forEach(btnText => {
      const btn = document.createElement("button");
      btn.className = "btn btn-outline-secondary btn-sm me-1 mb-1";
      btn.textContent = btnText;
      btn.addEventListener("click", () => sendMessage(btnText));
      container.appendChild(btn);
    });

    chatMessages.appendChild(container);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  };

  // Send message to backend
  const sendMessage = async (message) => {
    if (!message.trim()) return;
    displayMessage(message, "user");
    chatInput.value = "";

    try {
      const res = await fetch("../api/chatbot.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message })
      });

      if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
      const data = await res.json();

      displayMessage(data.reply, "bot");
      displayButtons(data.buttons || []);
    } catch (err) {
      console.error("Chatbot error:", err);
      displayMessage("Sorry, I can't connect right now. Please try again later.", "bot");
    }
  };

  // Handle form submit
  chatForm.addEventListener("submit", (e) => {
    e.preventDefault();
    sendMessage(chatInput.value);
  });
});
