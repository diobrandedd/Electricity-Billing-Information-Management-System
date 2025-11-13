<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['admin']);
$page_title = 'Support Chats';
include __DIR__ . '/../includes/header.php';
?>
<style>
    .chat-list { height: 70vh; overflow-y: auto; }
    .chat-box { height: 60vh; overflow-y: auto; background: #f8f9fa; padding: 1rem; border-radius: .5rem; }
    .msg-row { display: flex; align-items: flex-end; gap: .5rem; margin-bottom: .5rem; width: 100%; }
    .msg-row.left { flex-direction: row; }
    .msg-row.right { flex-direction: row-reverse; }
    .avatar {
        width: 28px; height: 28px; border-radius: 50%; background: #6c757d; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700;
    }
    .avatar.customer { background: #0d6efd; }
    .avatar.admin { background: #198754; }
    .content { max-width: 85%; display: flex; flex-direction: column; }
    .bubble { display: inline-block; max-width: 100%; padding: .5rem .75rem; border-radius: .5rem; font-size: 0.95rem; width: auto; overflow-wrap: break-word; word-break: normal; white-space: pre-wrap; hyphens: auto; }
    .bubble.customer { background: #e9ecef; }
    .bubble.admin { background: #0d6efd; color: #fff; }
    .bubble.customer.long { background: #dee2e6; }
    .bubble.admin.long { background: #0b5ed7; }
    @media (max-width: 576px) {
        .content { max-width: 95%; }
    }
    .meta { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }
    .meta.admin { text-align: right; }
</style>

<div class="container-fluid py-3">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><strong>Active Chats</strong></div>
                <div id="sessionList" class="list-group list-group-flush chat-list"></div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <strong id="chatTitle">Select a chat</strong>
                        <span id="totalUnread" class="badge text-bg-danger d-none">0</span>
                    </div>
                    <div>
                        <button id="closeBtn" class="btn btn-sm btn-outline-danger" disabled>Close Chat</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chatBox" class="chat-box"></div>
                    <form id="chatForm" class="d-flex gap-2 mt-3 align-items-end">
                        <textarea id="messageInput" class="form-control" placeholder="Type a message..." rows="1" required disabled style="resize: none; overflow: hidden;"></textarea>
                        <button class="btn btn-primary" type="submit" disabled id="sendBtn">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
<script>
let currentSessionId = null;
let lastMessageId = 0;

const listEl = document.getElementById('sessionList');
const chatBox = document.getElementById('chatBox');
const form = document.getElementById('chatForm');
const input = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const closeBtn = document.getElementById('closeBtn');
const titleEl = document.getElementById('chatTitle');
const totalUnreadEl = document.getElementById('totalUnread');

function loadSessions() {
    axios.get('<?php echo url('ajax/chat.php'); ?>', { params: { action: 'admin_list_sessions' }})
        .then(res => {
            const sessions = res.data.sessions || [];
            listEl.innerHTML = '';
            let totalUnread = 0;
            sessions.forEach(s => {
                const a = document.createElement('a');
                a.href = '#';
                a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                a.textContent = `${s.first_name} ${s.last_name} (${s.account_number})`;
                if (s.unread_count > 0) {
                    const badge = document.createElement('span');
                    badge.className = 'badge text-bg-danger rounded-pill';
                    badge.textContent = s.unread_count;
                    a.appendChild(badge);
                }
                totalUnread += (parseInt(s.unread_count) || 0);
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    openSession(s);
                });
                listEl.appendChild(a);
            });
            if (totalUnread > 0) {
                totalUnreadEl.classList.remove('d-none');
                totalUnreadEl.textContent = totalUnread;
            } else {
                totalUnreadEl.classList.add('d-none');
            }
        });
}

function openSession(s) {
    currentSessionId = s.session_id;
    lastMessageId = 0;
    titleEl.textContent = `${s.first_name} ${s.last_name} • ${s.account_number}`;
    input.disabled = false; sendBtn.disabled = false; closeBtn.disabled = false;
    // reset composer height
    input.style.height = 'auto';
    chatBox.innerHTML = '';
    fetchMessages();
}

function formatTime(ts) {
    try { return new Date(ts.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); } catch(e) { return ''; }
}

function ensureMessageElement(m) {
    let row = document.getElementById('msg-' + m.message_id);
    const isAdmin = m.sender_type === 'admin';
    if (!row) {
        row = document.createElement('div');
        row.id = 'msg-' + m.message_id;
        row.className = 'msg-row ' + (isAdmin ? 'right' : 'left');

        const avatar = document.createElement('div');
        avatar.className = 'avatar ' + (isAdmin ? 'admin' : 'customer');
        avatar.textContent = isAdmin ? 'ADMIN' : 'CUSTOMER';

        const contentWrap = document.createElement('div');
        contentWrap.className = 'content';
        const bubble = document.createElement('div');
        const isLong = (m.message || '').length > 120;
        bubble.className = 'bubble ' + (isAdmin ? 'admin' : 'customer') + (isLong ? ' long' : '');
        bubble.textContent = m.message;

        const meta = document.createElement('div');
        meta.className = 'meta ' + (isAdmin ? 'admin' : '');
        meta.dataset.role = isAdmin ? 'you' : 'them';

        contentWrap.appendChild(bubble);
        contentWrap.appendChild(meta);

        row.appendChild(avatar);
        row.appendChild(contentWrap);
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
    if (!currentSessionId) return;
    axios.get('<?php echo url('ajax/chat.php'); ?>', { params: { action: 'fetch_messages', session_id: currentSessionId, since_id: lastMessageId }})
        .then(res => {
            (res.data.messages || []).forEach(m => {
                renderOrUpdateMessage(m);
                lastMessageId = Math.max(lastMessageId, parseInt(m.message_id));
            });
            if ((res.data.messages || []).length) {
                chatBox.scrollTop = chatBox.scrollHeight;
                // mark customer messages as read
                axios.post('<?php echo url('ajax/chat.php'); ?>', new URLSearchParams({ action: 'mark_read', session_id: currentSessionId }));
            }
        });
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = input.value.trim();
    if (!message || !currentSessionId) return;
    axios.post('<?php echo url('ajax/chat.php'); ?>', new URLSearchParams({ action: 'send_message', session_id: currentSessionId, message, sender: 'admin' }))
        .then(() => { input.value = ''; input.style.height = 'auto'; fetchMessages(); });
});

closeBtn.addEventListener('click', function() {
    if (!currentSessionId) return;
    axios.post('<?php echo url('ajax/chat.php'); ?>', new URLSearchParams({ action: 'close_session', session_id: currentSessionId }))
        .then(() => { currentSessionId = null; input.disabled = true; sendBtn.disabled = true; closeBtn.disabled = true; titleEl.textContent = 'Select a chat'; chatBox.innerHTML = ''; loadSessions(); });
});

loadSessions();
setInterval(() => { loadSessions(); fetchMessages(); }, 2000);

// Auto-grow textarea
function autoResize() {
    input.style.height = 'auto';
    input.style.height = (input.scrollHeight) + 'px';
}
input.addEventListener('input', autoResize);
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>


