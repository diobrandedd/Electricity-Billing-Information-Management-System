<?php
// Floating action button for customer chat
?>
<style>
.chat-fab {
	position: fixed;
	bottom: 24px;
	right: 24px;
	z-index: 1050;
}
.chat-fab .btn-chat {
	width: 105px;
	height: 105px;
	border-radius: 50%;
	background: linear-gradient(135deg, #ff9c04 0%, 	#ffdc3c 100%);
	color: #fff;
	box-shadow: 0 6px 16px rgba(0,0,0,0.2);
	border: none;
}
.chat-fab .btn-chat:hover {
	transform: translateY(-2px);
	box-shadow: 0 10px 24px rgba(0,0,0,0.3);
}
.chat-fab .btn-chat img {
	width: 100px;
	height: 100px;
	object-fit: contain;
}
/* Quick question bubbles */
.quick-bubbles {
	position: fixed;
	bottom: 10px;
	right: 140px; /* button (≈105px) + gap */
	z-index: 1050;
	display: flex;
	flex-direction: column-reverse;
	display: none;
}
.quick-bubbles.show { display: block; }
.quick-bubbles .bubble {
	background: #ffffff;
	border: 1px solid rgba(0,0,0,0.1);
	box-shadow: 0 4px 12px rgba(0,0,0,0.12);
	border-radius: 14px;
	padding: 6px 10px;
	margin: 4px 0;
	cursor: pointer;
	font-size: 0.8rem;
	white-space: nowrap;
	transition: transform .15s ease, box-shadow .15s ease;
}
.quick-bubbles .bubble:hover {
	transform: translateX(-2px);
	box-shadow: 0 10px 24px rgba(0,0,0,0.2);
}
</style>

<div class="chat-fab">
	<button class="btn btn-chat d-flex align-items-center justify-content-center" aria-label="Customer Support Chat"
	        data-bs-toggle="offcanvas" data-bs-target="#customerChatOffcanvas">
		<img src="<?php echo url('img/customersupp.png'); ?>" alt="Support" onerror="this.style.display='none'; this.parentNode.querySelector('i').style.display='inline-block';">
		<i class="fas fa-comments" style="font-size: 1.4rem; display: none;"></i>
	</button>
</div>

<!-- Quick question bubbles -->
<div id="chatQuickBubbles" class="quick-bubbles">
	<div class="bubble" data-msg="What is my current bill amount?">What is my current bill amount?</div>
	<div class="bubble" data-msg="When is my bill due date?">When is my bill due date?</div>
	<div class="bubble" data-msg="How can I make a payment?">How can I make a payment?</div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="customerChatOffcanvas" aria-labelledby="customerChatLabel" style="width: 420px; max-width: 100vw;">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="customerChatLabel"><i class="fas fa-headset me-2"></i> Customer Support</h5>
		<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body p-0">
		<iframe id="chatIframe" src="<?php echo url('customer/chat.php'); ?>" style="border: 0; width: 100%; height: 80vh;" title="Customer Support Chat"></iframe>
	</div>
</div>
<script>
(function(){
	var offcanvasEl = document.getElementById('customerChatOffcanvas');
	var bubbles = document.getElementById('chatQuickBubbles');
	var iframe = document.getElementById('chatIframe');

	function showBubbles(){ if (bubbles) bubbles.classList.add('show'); }
	function hideBubbles(){ if (bubbles) bubbles.classList.remove('show'); }

	if (offcanvasEl) {
		if (window.bootstrap && window.bootstrap.Offcanvas) {
			offcanvasEl.addEventListener('shown.bs.offcanvas', showBubbles);
			offcanvasEl.addEventListener('hide.bs.offcanvas', hideBubbles);
		}
	}

	function sendQuick(msg){
		try {
			if (iframe && iframe.contentWindow) {
				iframe.contentWindow.postMessage({ type: 'chat_quick_question', message: msg }, '*');
			}
		} catch(e) {}
	}

	if (bubbles) {
		bubbles.addEventListener('click', function(e){
			var t = e.target.closest('.bubble');
			if (!t) return;
			var msg = t.getAttribute('data-msg') || t.textContent || '';
			if (!msg) return;
			sendQuick(msg);
		});
	}
})();

// Handle close message from iframe
window.addEventListener('message', function(event) {
	if (event.data && event.data.type === 'close_chat') {
		const offcanvasEl = document.getElementById('customerChatOffcanvas');
		if (offcanvasEl && window.bootstrap && window.bootstrap.Offcanvas) {
			const inst = window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
			inst.hide();
		}
	}
});
</script>


