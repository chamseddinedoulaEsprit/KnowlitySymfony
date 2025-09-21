const chatBody = document.querySelector(".chat-body");
const messageInput = document.querySelector(".message-input");
const sendMessage = document.querySelector("#send-message");
const fileInput = document.querySelector("#file-input");
const fileUploadWrapper = document.querySelector(".file-upload-wrapper");
const fileCancelButton = fileUploadWrapper.querySelector("#file-cancel");
const chatbotToggler = document.querySelector("#chatbot-toggler");
const closeChatbot = document.querySelector("#close-chatbot");

const GROQ_API_KEY = "";
const GROQ_API_URL = "https://api.groq.com/openai/v1/chat/completions";

const userData = {
    message: null,
    file: {
        data: null,
        mime_type: null,
    },
};

let chatHistory = [];

fetch(`/events/api/user/${userinfo}/data`)
    .then(response => response.json())
    .then(data => {
        const userMessageText = `Context: You are an AI assistant for Knowlity. This website is about online education. 
Your job is to help users navigate and understand its features.

Here is some basic information about the current user: 
- ID: ${data.user_id || "N/A"}
- Name: ${data.username || "Unknown"}
- Role: ${data.role || "No role assigned"}

his/her Courses: 
${data.courses?.length > 0 
    ? data.courses.map(course => `- ${course.title} (Price: ${course.price})`).join("\n") 
    : "No courses enrolled."}

his/her Events: 
${data.events?.length > 0 
    ? data.events.map(event => `- ${event.title} (Starts: ${event.start_date}, Ends: ${event.end_date})`).join("\n") 
    : "No registered events."}

Additional Notes:
- If the user needs help with courses, guide them on enrollment, pricing, or available subjects.
- If the user asks about events, provide details about schedules, participation, and deadlines.
- If they ask unrelated questions, you can provide general knowledge, but always prioritize website-related queries.`;

        chatHistory = [
            {
                role: "user",
                content: userMessageText
            }
        ];
    })
    .catch(error => console.error("Error fetching user data:", error));

const generateBotResponse = async (incomingMessageDiv) => {
    const messageElement = incomingMessageDiv.querySelector(".message-text");

    chatHistory.push({
        role: "user",
        content: userData.message
    });

    const requestOptions = {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${GROQ_API_KEY}`
        },
        body: JSON.stringify({
            model: "llama3-70b-8192",
            messages: chatHistory
        }),
    };

    try {
        const response = await fetch(GROQ_API_URL, requestOptions);
        if (!response.ok) throw new Error(`API Error: ${response.status}`);

        const data = await response.json();
        const apiResponseText = data?.choices?.[0]?.message?.content?.trim();

        if (!apiResponseText) throw new Error("Invalid response from API");

        messageElement.innerText = apiResponseText;

        chatHistory.push({
            role: "assistant",
            content: apiResponseText
        });
    } catch (error) {
        console.error(error);
        messageElement.innerText = "Sorry, I'm having trouble responding. Please try again.";
        messageElement.style.color = "#ff0000";
    } finally {
        userData.file = {};
        incomingMessageDiv.classList.remove("thinking");
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });
    }
};

const createMessageElement = (content, ...classes) => {
    const div = document.createElement("div");
    div.classList.add("message", ...classes);
    div.innerHTML = content;
    return div;
};

const handleOutgoingMessage = (e) => {
    e.preventDefault();
    userData.message = messageInput.value.trim();
    if (!userData.message) return;

    messageInput.value = "";
    fileUploadWrapper.classList.remove("file-uploaded");

    const messageContent = `<div class="message-text"></div>
                            ${userData.file.data ? `<img src="data:${userData.file.mime_type};base64,${userData.file.data}" class="attachment" />` : ""}`;

    const outgoingMessageDiv = createMessageElement(messageContent, "user-message");
    outgoingMessageDiv.querySelector(".message-text").innerText = userData.message;
    chatBody.appendChild(outgoingMessageDiv);
    chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });

    setTimeout(() => {
        const messageContent = `<img class="bot-avatar" src="robotic.png" alt="Chatbot Logo" width="50" height="50">
            <div class="message-text">
                <div class="thinking-indicator">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>`;

        const incomingMessageDiv = createMessageElement(messageContent, "bot-message", "thinking");
        chatBody.appendChild(incomingMessageDiv);
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });
        generateBotResponse(incomingMessageDiv);
    }, 600);
};

sendMessage.addEventListener("click", (e) => handleOutgoingMessage(e));
closeChatbot.addEventListener("click", () => document.body.classList.remove("show-chatbot"));
chatbotToggler.addEventListener("click", () => document.body.classList.toggle("show-chatbot"));

fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        fileInput.value = "";
        fileUploadWrapper.querySelector("img").src = e.target.result;
        fileUploadWrapper.classList.add("file-uploaded");

        userData.file = {
            data: e.target.result.split(",")[1],
            mime_type: file.type,
        };
    };

    reader.readAsDataURL(file);
});

fileCancelButton.addEventListener("click", () => {
    userData.file = {};
    fileUploadWrapper.classList.remove("file-uploaded");
});
