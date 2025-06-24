/**
 * CHAT - JavaScript pour le système de chat en temps réel
 * Quernel Auto
 */

document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const charCount = document.getElementById('charCount');
    
    // Récupérer les URLs depuis les attributs data
    const sendUrl = chatForm.dataset.sendUrl;
    const getMessagesUrl = chatForm.dataset.getMessagesUrl;
    const conversationId = chatForm.dataset.conversationId;
    
    let isTyping = false;
    
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        
        // Update character count
        const count = this.value.length;
        charCount.textContent = count + '/255';
        
        // Disable send button if empty
        sendButton.disabled = count === 0;
    });
    
    // Send message
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;
        
        // Disable form while sending
        sendButton.disabled = true;
        messageInput.disabled = true;
        
        // Add message to chat immediately
        addMessageToChat(content, true, 'Vous');
        
        // Clear input
        messageInput.value = '';
        messageInput.style.height = 'auto';
        charCount.textContent = '0/255';
        
        // Send to server
        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                content: content,
                conversationId: conversationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Message sent successfully
                sendButton.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
            } else {
                // Handle error
                console.error('Erreur:', data.error);
                sendButton.disabled = false;
                messageInput.disabled = false;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            sendButton.disabled = false;
            messageInput.disabled = false;
        });
    });
    
    // Add message to chat
    function addMessageToChat(content, isFromUser, senderName) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isFromUser ? 'message-user' : 'message-admin'}`;
        
        const now = new Date();
        const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-text">${content}</div>
                <div class="message-time">${time}</div>
            </div>
            <div class="message-sender">
                ${isFromUser ? '<i class="fas fa-user"></i> Vous' : '<i class="fas fa-headset"></i> Support'}
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Scroll to bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Initial scroll
    scrollToBottom();
    
    // Poll for new messages every 3 seconds
    if (getMessagesUrl) {
        setInterval(function() {
            fetch(getMessagesUrl)
            .then(response => response.json())
            .then(messages => {
                // Check if there are new messages
                const currentMessageCount = chatMessages.querySelectorAll('.message').length;
                if (messages.length > currentMessageCount) {
                    // Reload the page to show new messages
                    location.reload();
                }
            })
            .catch(error => console.error('Erreur lors de la récupération des messages:', error));
        }, 3000);
    }
}); 