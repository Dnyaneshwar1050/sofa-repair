<!-- Chatbot Floating UI -->
<div id="ai-chatbot" class="fixed bottom-6 right-6 z-50">
    <!-- Chat Icon Button -->
    <button id="chatbot-toggle" 
        class="bg-orange-600 hover:bg-orange-700 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-2xl transition-transform hover:scale-110">
        <i class="fa-solid fa-robot text-2xl"></i>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="hidden absolute bottom-20 right-0 w-80 md:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col h-[450px]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-600 to-orange-500 text-white p-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-orange-600">
                    <i class="fa-solid fa-robot text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold">Silva Support</h4>
                    <p class="text-xs text-orange-100">AI Assistant - Online</p>
                </div>
            </div>
            <button id="chatbot-close" class="text-white hover:text-gray-200 transition">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chatbot-messages" class="flex-grow p-4 overflow-y-auto bg-gray-50 flex flex-col gap-3">
            <!-- Initial Greeting -->
            <div class="flex gap-2 w-full">
                <div class="bg-white border border-gray-200 text-gray-800 p-3 rounded-2xl rounded-tl-none max-w-[85%] shadow-sm text-sm inline-block self-start">
                    Hi there! 👋 I'm Silva's AI assistant. I can help you with pricing, service details, or booking queries. How can I help you today?
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 border-t bg-white">
            <form id="chatbot-form" class="flex items-center gap-2">
                <input type="text" id="chatbot-input" placeholder="Type your message..." required autocomplete="off"
                    class="flex-grow bg-gray-100 border-none rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                <button type="submit" 
                    class="bg-orange-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-orange-700 transition flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const window = document.getElementById('chatbot-window');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');
    
    // Auto-generate session ID
    let sessionId = localStorage.getItem('chatbot_session');
    if (!sessionId) {
        sessionId = 'session_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('chatbot_session', sessionId);
    }

    toggleBtn.addEventListener('click', () => {
        window.classList.toggle('hidden');
        if(!window.classList.contains('hidden')) {
            input.focus();
        }
    });

    closeBtn.addEventListener('click', () => {
        window.classList.add('hidden');
    });

    function addMessage(text, isUser = false) {
        const msgDiv = document.createElement('div');
        msgDiv.className = "flex gap-2 w-full " + (isUser ? "justify-end" : "");
        
        const innerDiv = document.createElement('div');
        let classes = "p-3 rounded-2xl max-w-[85%] shadow-sm text-sm inline-block ";
        
        if (isUser) {
            classes += "bg-orange-600 text-white rounded-tr-none self-end";
        } else {
            classes += "bg-white border border-gray-200 text-gray-800 rounded-tl-none self-start";
            // Check for intents/specific formatting later if needed
        }
        
        innerDiv.className = classes;
        innerDiv.innerHTML = text; // Allow HTML for line breaks if needed
        
        msgDiv.appendChild(innerDiv);
        messages.appendChild(msgDiv);
        
        // Scroll to bottom
        messages.scrollTop = messages.scrollHeight;
    }

    function addTypingIndicator() {
        const id = 'typing-' + Date.now();
        const msgDiv = document.createElement('div');
        msgDiv.id = id;
        msgDiv.className = "flex gap-2 w-full";
        msgDiv.innerHTML = `
            <div class="bg-white border border-gray-200 text-gray-500 p-3 rounded-2xl rounded-tl-none shadow-sm text-sm flex gap-1 items-center">
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
            </div>
        `;
        messages.appendChild(msgDiv);
        messages.scrollTop = messages.scrollHeight;
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if(el) el.remove();
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if(!text) return;

        // Add user message
        addMessage(text, true);
        input.value = '';
        input.focus();

        // Show typing
        const typingId = addTypingIndicator();

        try {
            const resp = await fetch('/api/ChatbotController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text, session_id: sessionId })
            });
            const data = await resp.json();
            
            removeTypingIndicator(typingId);
            
            if(data.response) {
                addMessage(data.response, false);
            } else {
                addMessage("Sorry, I'm having trouble connecting to my brain right now.", false);
            }
        } catch (err) {
            removeTypingIndicator(typingId);
            addMessage("Service temporarily unavailable.", false);
            console.error(err);
        }
    });

});
</script>
