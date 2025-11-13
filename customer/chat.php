<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['customer_id'])) {
    redirect('auth/customer_login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Chat - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #FF9A00;
            --secondary-yellow: #FFD93D;
            --dark-blue: #1e3a8a;
            --light-gray: #f8f9fa;
        }

        body { background-color: var(--light-gray); }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            border: none;
            border-radius: 15px 15px 0 0;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-yellow));
            color: #fff;
            padding: 14px 18px;
        }

        .btn-primary {
            background-color: var(--primary-orange);
            border-color: var(--primary-orange);
        }
        .btn-primary:hover {
            background-color: #e68900;
            border-color: #e68900;
        }
        .btn-outline-secondary { border-color: rgba(255,255,255,0.8); color: #fff; }
        .btn-outline-secondary:hover { background-color: rgba(255,255,255,0.15); color: #fff; }

        .chat-box {
            height: 60vh;
            overflow-y: auto;
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .msg-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-end;
        }

        .msg-row.right {
            justify-content: flex-end;
        }

        .msg-row.left {
            justify-content: flex-start;
        }

        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            margin: 0 8px;
            flex-shrink: 0;
        }

        .avatar.you {
            background-color: var(--primary-orange);
            color: white;
        }

        .avatar.admin {
            background-color: var(--dark-blue);
            color: white;
        }

        .content {
            max-width: 70%;
            display: flex;
            flex-direction: column;
        }

        .bubble {
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
        }

        .bubble.you {
            background-color: var(--primary-orange);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .bubble.admin {
            background-color: #e9ecef;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        .bubble.long {
            max-width: 100%;
        }

        .meta {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            text-align: right;
        }

        .msg-row.left .meta {
            text-align: left;
        }

        .chat-input {
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            resize: none;
            min-height: 40px;
            max-height: 120px;
        }

        .chat-input:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 154, 0, 0.25);
        }

        .moderation-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            position: relative;
        }

        .moderation-warning.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .moderation-warning.warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }

        .moderation-warning.info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
        }

        .warning-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .moderation-details {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Support Chat</strong>
                    <button type="button" id="chatBackBtn" class="btn btn-sm btn-outline-secondary">Back</button>
                </div>
                <div class="card-body">
                    
                    <div id="chatBox" class="chat-box"></div>
                    <form id="chatForm" class="d-flex gap-2 mt-3 align-items-end">
                        <textarea id="messageInput" class="form-control chat-input" placeholder="Type your message..." rows="1" required style="resize: none; overflow: hidden;"></textarea>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let sessionId = null;
let lastMessageId = 0;
const chatBox = document.getElementById('chatBox');
const form = document.getElementById('chatForm');
const input = document.getElementById('messageInput');

function ensureSession() {
    return fetch('<?php echo url('ajax/chat.php?action=ensure_session'); ?>')
        .then(r => {
            if (r.status === 401) {
                // Not logged in – redirect top-level to login
                window.top.location = '<?php echo url('auth/customer_login.php'); ?>';
                return Promise.reject(new Error('Unauthorized'));
            }
            if (!r.ok) return Promise.reject(new Error('Failed to ensure session'));
            return r.json();
        })
        .then(data => { 
            if (data && data.session) { 
                sessionId = data.session.session_id; 
            } else {
                console.error('No chat session returned');
            }
        })
        .catch(err => {
            console.error('ensureSession error:', err);
        });
}

function formatTime(ts) {
    try { return new Date(ts.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); } catch(e) { return ''; }
}

function ensureMessageElement(m) {
    let row = document.getElementById('msg-' + m.message_id);
    const isYou = m.sender_type === 'customer';
    if (!row) {
        row = document.createElement('div');
        row.id = 'msg-' + m.message_id;
        row.className = 'msg-row ' + (isYou ? 'right' : 'left');

        const avatar = document.createElement('div');
        avatar.className = 'avatar ' + (isYou ? 'you' : 'admin');
        avatar.textContent = isYou ? 'YOU' : 'ADMIN';

        const contentWrap = document.createElement('div');
        contentWrap.className = 'content';
        const bubble = document.createElement('div');
        const isLong = (m.message || '').length > 120;
        bubble.className = 'bubble ' + (isYou ? 'you' : 'admin') + (isLong ? ' long' : '');
        bubble.textContent = m.message;

        const meta = document.createElement('div');
        meta.className = 'meta';
        meta.textContent = formatTime(m.created_at);

        contentWrap.appendChild(bubble);
        contentWrap.appendChild(meta);
		// For customer messages, place avatar on the right (Messenger style):
		// keep right alignment and append content first, then avatar so the avatar sits at the far right.
		if (isYou) {
			row.appendChild(contentWrap);
			row.appendChild(avatar);
		} else {
			row.appendChild(avatar);
			row.appendChild(contentWrap);
		}
        chatBox.appendChild(row);
    }
    return row;
}

function updateMeta(m) {
    const row = document.getElementById('msg-' + m.message_id);
    if (!row) return;
    const meta = row.querySelector('.meta');
    if (!meta) return;
    const time = formatTime(m.created_at || m.createdAt || m.timestamp || '');
    const isAdmin = m.sender_type === 'admin';
    if (isAdmin) {
        meta.textContent = `${time} • ${m.is_read == 1 ? 'Seen' : 'Sent'}`;
    } else {
        meta.textContent = `${time}`;
    }
}

function renderOrUpdateMessage(m) {
    ensureMessageElement(m);
    updateMeta(m);
}

function fetchMessages() {
    if (!sessionId) return;
    const url = '<?php echo url('ajax/chat.php'); ?>' + `?action=fetch_messages&session_id=${sessionId}&since_id=${lastMessageId}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            (data.messages || []).forEach(m => {
                renderOrUpdateMessage(m);
                lastMessageId = Math.max(lastMessageId, parseInt(m.message_id));
            });
            if ((data.messages || []).length) {
                chatBox.scrollTop = chatBox.scrollHeight;
                // mark admin messages as read
                fetch('<?php echo url('ajax/chat.php'); ?>', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({ action: 'mark_read', session_id: sessionId }) });
            }
        })
        .catch((e) => { console.error('fetchMessages error:', e); });
}

// Handle moderation warnings
function showModerationWarning(error, moderation) {
    const warningDiv = document.createElement('div');
    warningDiv.className = 'alert alert-warning alert-dismissible fade show mt-2';
    warningDiv.innerHTML = `
        <strong>Content Warning:</strong> ${error}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    form.parentNode.insertBefore(warningDiv, form);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (warningDiv.parentNode) {
            warningDiv.remove();
        }
    }, 5000);
}

// Handle postMessage from parent window (quick questions)
window.addEventListener('message', function(event) {
    try {
        if (event.data && event.data.type === 'chat_quick_question') {
            const message = event.data.message;
            if (message && sessionId) {
                input.value = message;
                form.dispatchEvent(new Event('submit'));
            }
        }
    } catch (e) {
        console.error('postMessage handler error:', e);
    }
});

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = input.value.trim();
    if (!message || !sessionId) return;
    const body = new URLSearchParams({ action: 'send_message', session_id: sessionId, message, sender: 'customer' });
    fetch('<?php echo url('ajax/chat.php'); ?>', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body })
        .then(r => {
            if (r.status === 401) {
                window.top.location = '<?php echo url('auth/customer_login.php'); ?>';
                return Promise.reject(new Error('Unauthorized'));
            }
            if (!r.ok) return Promise.reject(new Error('Failed to send'));
            return r.json();
        })
        .then((res) => {
            if (!res || !res.success) {
                // Handle moderation responses
                if (res.moderation) {
                    showModerationWarning(res.error, res.moderation);
                } else {
                    console.error('send_message failed:', res);
                    alert('Error: ' + (res.error || 'Failed to send message'));
                }
                return;
            }
            input.value = '';
            input.style.height = 'auto';
            fetchMessages();
        })
        .catch((e) => { console.error('send error:', e); });
});

ensureSession().then(() => {
    fetchMessages();
    setInterval(fetchMessages, 2000);
});

// Auto-grow textarea
function autoResize() {
    input.style.height = 'auto';
    input.style.height = (input.scrollHeight) + 'px';
}
input.addEventListener('input', autoResize);
window.addEventListener('load', autoResize);

// Back button: if inside offcanvas iframe, close the offcanvas; else go home
document.getElementById('chatBackBtn').addEventListener('click', function(){
    if (window.parent && window.parent !== window) {
        // Inside iframe - close offcanvas
        window.parent.postMessage({ type: 'close_chat' }, '*');
    } else {
        // Direct access - go to customer home
        window.location.href = '<?php echo url('users/userindex.php'); ?>';
    }
});
</script>
</body>
</html>