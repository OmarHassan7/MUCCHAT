<?php
session_start();

// Check if 'idnumber' is set and not empty
if (!isset($_POST['idnumber']) || empty($_POST['idnumber'])) {
    header("Location: index.html");
    exit();
}

// Assuming your database credentials
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "sharkawi_muc";

// Create a connection to the database
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 'idnumber' from the form
$username = $_POST['idnumber'];

// Fetch email from the users table where id equals $username
$sql = "SELECT email FROM users WHERE id = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the first row
    // Fetch the first row
    $row = $result->fetch_assoc();

    // Get the email
    $email = $row['email'];

    // Remove the last 20 characters
    $emailWithoutLast20 = substr($email, 0, -20);

    // Get the remaining characters
    $name = $emailWithoutLast20;
    $user_id = $_POST['idnumber'];
} else {
    echo "";
}

// Close the database connection when you're done
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat [<?php echo    $name; ?>]</title>
    <link rel="stylesheet" href="./chat-style.css" />
    <style>
        .current {
            background-color: #ccc !important;
        }
    </style>
</head>

<body>
    <div id="container">
        <div id="sidebar">
            <div id="welcome-message">Welcome,
                <?php echo    $name; ?>!
            </div>
            <h3>Conversations</h3>
            <div id="channels">

            </div>
            Search by Id
            <input type="text" id="search">
            <input type="submit" value="sub" id="search-btn">

            <div id="online-users-container">
                <h3>Online Users</h3>
                <div id="online-users"></div>
            </div>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div id="chat-container">
            <div id="chat-messages"></div>
            <div>
                <input type="text" id="message-input" placeholder="Type your message..." class="input">
                <label for="shift-input">Shift Value:</label>
                <input type="number" id="shift-input" min="1" value="3" class="input">
                <button id="send-button" class="button">Send</button>
            </div>
        </div>
    </div>

    <script>
        const ws = new WebSocket('ws://localhost:8080?username=<?php echo $name; ?>&user_id=<?php echo $user_id ?> ');
        const userName = "<?php echo $name; ?>";
        const userId = "<?php echo $user_id; ?>";
        window.onload = async function() {
            await loadChannels();
            console.log("aaaa", window.current_channel_id);
            loadMessages(window.current_channel_id);
            document.getElementById('welcome-message').style.display = 'block';
            loadOnlineUsers();
        };



        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const categorySelect = document.getElementById('category-select');
            const shiftInput = document.getElementById('shift-input'); // Get the shift input
            const message = messageInput.value.trim();
            // const category = categorySelect.value || "General";
            shiftValue = parseInt(shiftInput.value) || 3; // Get the shift value or default to 3

            // Function to encrypt a message using Caesar cipher with the specified shift value
            function encryptMessage(text, shift) {
                return [...text]
                    .map(char => {
                        const charCode = char.charCodeAt(0);
                        if (charCode >= 65 && charCode <= 90) {
                            return String.fromCharCode((charCode - 65 + shift) % 26 + 65); // Uppercase letters
                        } else if (charCode >= 97 && charCode <= 122) {
                            return String.fromCharCode((charCode - 97 + shift) % 26 + 97); // Lowercase letters
                        } else {
                            return char; // Non-alphabetic characters
                        }
                    })
                    .join('');
            }
            if (message !== '') {
                const fullMessage = encryptMessage(message, shiftValue)
                const data = {
                    sender: encodeURIComponent(userName),
                    message: encodeURIComponent(fullMessage),
                    channel_id: encodeURIComponent(window.current_channel_id)
                };
                fetch('functions/insert_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-type': 'application/x-www-form-urlencoded'
                        },
                        body: Object.keys(data).map(key => `${key}=${data[key]}`).join('&')
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    }).then(() => {
                        const x = JSON.stringify({
                            event: `message`,
                            data: {
                                sender: userName,
                                channel_id: window.current_channel_id,
                                message: message
                            }
                        })
                        ws.send(x);
                    })
                messageInput.value = '';
                loadChannels();
            }
        }
        // assume model is DB
        // model

        // view 
        function clearChatMessages() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
        }
        onlineUsers = [];
        const searchInput = document.getElementById('search');
        let searchVal = ''
        const searchBtn = document.getElementById("search-btn");
        // controller
        searchInput.addEventListener("keyup", function(e) {
            searchVal = e.target.value;
            const filteredOnlineUsers = onlineUsers.filter(user => {
                console.log("c1", user.user_id);
                console.log("c2", searchVal);
                return user.user_id.includes(searchVal);
            });
            console.log("A7a", filteredOnlineUsers);
            updateOnlineUsers(filteredOnlineUsers);
        })



        function updateOnlineUsers(onlineUsers) {
            const onlineUsersElement = document.getElementById('online-users');
            onlineUsersElement.innerHTML = '<ul id="online-users-list"></ul>';

            const onlineUsersList = document.getElementById('online-users-list');
            const categorySelect = document.getElementById('category-select');
            console.log("a7a", {
                onlineUsers
            });
            console.log(onlineUsers[1]);
            onlineUsers.filter(x => x.name !== userName).forEach(({
                name: username,
                user_id
            }) => {
                const listItem = document.createElement('li');
                const link = document.createElement('li');

                link.setAttribute('data-channel', username);
                link.setAttribute('data-username', username);
                link.setAttribute('data-userid', user_id);
                link.textContent = username + " " + user_id;
                link.setAttribute('class', 'online-user');

                link.addEventListener('click', function(e) {
                    const elem = e.target;
                    fetch("functions/create_channel.php", {
                        method: "POST",
                        body: JSON.stringify({
                            partcipants: [{
                                id: <?php echo $username; ?>,
                                name: "<?php echo $name; ?>"
                            }, {
                                id: +elem.getAttribute("data-userid"),
                                name: elem.getAttribute("data-username")
                            }]
                        })
                    }).then(response => response.json()).then(response => {
                        console.log({
                            response
                        })
                        if (response.message === "Already exists") {
                            window.current_channel_id = response.channel_id;
                            console.log(window.current_channel_id);
                            loadChannels();
                            loadMessages(window.current_channel_id);
                        } else {
                            loadChannels();
                            loadMessages()
                        }
                    })

                    clearChatMessages();

                });

                listItem.appendChild(link);
                onlineUsersList.appendChild(listItem);
            });
        }



        function appendMessage(message, userClass) {
            const chatMessages = document.getElementById('chat-messages');
            const messageContainer = document.createElement('div');
            messageContainer.className = `message-container ${userClass}`;

            const idChannelContainer = document.createElement('div');
            idChannelContainer.className = 'id-channel-container';

            const idChannelText = document.createElement('span');
            idChannelText.className = 'id-channel-text';
            idChannelText.textContent = message.split(': ')[0];

            const messageText = document.createElement('span');
            messageText.className = 'message-text';
            const encryptedMessage = message.split(': ').slice(1).join(':');
            messageText.textContent = encryptedMessage;

            messageContainer.addEventListener('click', toggleMessage);

            function toggleMessage() {

                if (messageText.textContent === encryptedMessage) {
                    let decrypt_shift = +prompt('Enter shift value');
                    // If the current content is the encrypted message, decrypt it
                    messageText.textContent = decryptMessage(encryptedMessage, decrypt_shift);
                } else {
                    // If the current content is decrypted, revert to the encrypted message
                    messageText.textContent = encryptedMessage;
                }
            }
            idChannelContainer.appendChild(idChannelText);
            idChannelContainer.appendChild(document.createTextNode(':'));
            messageContainer.appendChild(idChannelContainer);
            messageContainer.appendChild(messageText);
            chatMessages.appendChild(messageContainer);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function decryptMessage(text, shift) {
            return [...text]
                .map(char => {
                    const charCode = char.charCodeAt(0);
                    if (charCode >= 65 && charCode <= 90) {
                        QqQ
                        return String.fromCharCode(((charCode - 65 - shift + 26 * 999999999) % 26) + 65); // Uppercase letters
                    } else if (charCode >= 97 && charCode <= 122) {
                        return String.fromCharCode(((charCode - 97 - shift + 26 * 999999999) % 26) + 97); // Lowercase letters
                    } else {
                        return char; // Non-alphabetic characters
                    }
                })
                .join('');
        }


        document.getElementById('send-button').addEventListener('click', sendMessage);

        document.getElementById('message-input').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
        // ws.onopen(function() {
        //     console.log("Clinet Connected");
        // })
        ws.onmessage = function(e) {
            const {
                event,
                data
            } = JSON.parse(e.data);
            console.log("WebSocket Message Received:", e.data);

            switch (event) {
                case "online_users":
                    onlineUsers = data;
                    return updateOnlineUsers(data);
                case "message":
                    const {
                        sender, channel_id, message
                    } = data;
                    // appendMessage(`${sender} (${category}): ${message}`);
                    loadMessages(window.current_channel_id);
                    break;
                default:
                    throw new Error("unhandled case");
            }
            // const parts = event.data.split(':');
            // const sender = parts[0];
            // const category = parts[1] || "General";
            // const message = parts.slice(2).join(':');
            // const isCurrentUser = sender === userName;
            // const userClass = isCurrentUser ? 'user-message' : 'other-message';
            // appendMessage(`${sender} (${category}): ${message}`, userClass);

            // Check if the message belongs to the current channel
            // if (category === currentChannel) {
            //     appendMessage(`${sender} (${category}): ${message}`, userClass);
            // }

            // if (event.data.startsWith('Online Users:')) {
            //     const onlineUsers = event.data.replace('Online Users: ', '');

            // }
        };


        // اشتغلت بطريقة وسخه 


        function renderChannels(channels) {


            const x = channels.filter(chann => chann.name != "");
            console.log(x);
            const channelsContainer = document.getElementById("channels");
            channelsContainer.innerHTML = "";
            x.forEach(channel => {
                const elem = document.createElement("div");
                elem.className = window.current_channel_id === channel.id ? "conversation-link current" : "conversation-link ";
                elem.textContent = channel.name.split("_").filter(x => x != userName)[0] || "self";
                elem.setAttribute("data-channel-id", channel.id);
                elem.addEventListener('click', function(event) {

                    event.preventDefault();
                    const channel_id = this.getAttribute('data-channel-id');
                    window.current_channel_id = channel_id;
                    loadChannels();
                    loadMessages(channel_id);
                    window.current_channel_id = channel_id;
                });
                channelsContainer.appendChild(elem);
            })
        }

        function loadChannels() {
            return fetch(`functions/get_channels.php`).then(res => res.json()).then(channels => {
                // Added Logic to filer Channels 
                console.log({
                    userId: +userId,
                    channels
                })
                const getMajorById = (userId) => {
                    switch (userId.substring(0, 4)) {
                        case "2211":
                            return "Physical Therapy";
                        case "2212":
                            return "Engineering";
                        case "2213":
                            return "Business";

                        default:
                            return "Employess";
                    }
                }
                const y = channels.filter(ch => ch.is_private ?
                    ch.participants.includes(+userId) :
                    ch.name === "General" ? true :
                    ch.name === getMajorById(userId) ? true : false
                );
                console.log("bbbbb", channels[0], channels[0].id)
                if (!window.current_channel_id)
                    window.current_channel_id = channels[0].id;
                renderChannels(y);
                // loadMessages(window.current_channel_id)
            })
        }

        function loadMessages(channel_id) {
            clearChatMessages();
            console.log(channel_id);
            fetch(`functions/get_messages.php?channel_id=${encodeURIComponent(channel_id)}`).then(res => res.json()).then(messages =>
                messages.forEach(function(message) {
                    const isCurrentUser = message.sender === userName;
                    const userClass = isCurrentUser ? 'user-message' : 'other-message';
                    appendMessage(`${message.sender} : ${message.message}`, userClass);
                })
            )
            // document.getElementById('category-select').value = channel;
        }


        function loadOnlineUsers() {
            ws.send("Get Online Users");
        }
    </script>
</body>

</html>