 <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ws = new WebSocket('ws://localhost:8080?username=<?php echo $username; ?>');
            const userName = "<?php echo $username; ?>";
            let currentChannel = "General";

            function decryptMessage(encryptedText) {
                let result = '';
                for (let i = 0; i < encryptedText.length; i++) {
                    let charCode = encryptedText.charCodeAt(i);
                    if (charCode >= 65 && charCode <= 90) {
                        result += String.fromCharCode((charCode - 65 - 3 + 26) % 26 + 65); // Uppercase letters
                    } else if (charCode >= 97 && charCode <= 122) {
                        result += String.fromCharCode((charCode - 97 - 3 + 26) % 26 + 97); // Lowercase letters
                    } else {
                        result += encryptedText[i]; // Non-alphabetic characters
                    }
                }
                return result;
            }

            function displayDecryptedMessage(event) {
                const target = event.target;
                const encryptedMessage = target.textContent;

                // Decrypt the message using Caesar cipher with a shift of -3
                const decryptedMessage = decryptMessage(encryptedMessage);

                // Show the decrypted message
                alert(`Decrypted Message: ${decryptedMessage}`);
            }

            function appendMessage(message, userClass) {
                const chatMessages = document.getElementById('chat-messages');
                const messageContainer = document.createElement('div');
                messageContainer.className = `message-container ${userClass}`;
                messageContainer.textContent = message;

                messageContainer.addEventListener('mouseover', displayDecryptedMessage);

                chatMessages.appendChild(messageContainer);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            document.getElementById('send-button').addEventListener('click', function () {
                const messageInput = document.getElementById('message-input');
                const categorySelect = document.getElementById('category-select');
                const message = messageInput.value.trim();
                const category = categorySelect.value || "General";

                if (message !== '') {
                    const fullMessage = `${userName}:${category}:${message}`;
                    ws.send(fullMessage);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'insert_message.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.send(`sender=${encodeURIComponent(userName)}&message=${encodeURIComponent(message)}&category=${encodeURIComponent(category)}`);

                    messageInput.value = '';
                }
            });

            // ... existing code ...

            ws.onmessage = function (event) {
                const parts = event.data.split(':');
                const sender = parts[0];
                const category = parts[1] || "General";
                const message = parts.slice(2).join(':');
                const isCurrentUser = sender === userName;
                const userClass = isCurrentUser ? 'user-message' : 'other-message';
                appendMessage(`${sender} (${category}): ${message}`, userClass);

                if (event.data.startsWith('Online Users:')) {
                    const onlineUsers = event.data.replace('Online Users: ', '');
                    updateOnlineUsers(onlineUsers);
                }
            };

            // ... existing code ...
        });
    </script>