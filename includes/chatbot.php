<!-- UrbanBot Chatbot -->
<?php
// Use __DIR__ so the path is always correct regardless of CWD
// Both files are in the same includes/ directory
require_once(__DIR__ . '/env_loader.php');
loadEnv(dirname(__DIR__) . '/.env');

// Use getenv() — works on both Vercel (env vars) and local (.env file)
$hf_token = getenv('HF_API_TOKEN') ?: '';
$hf_model = getenv('HF_MODEL') ?: 'meta-llama/Llama-3.1-8B-Instruct';
?>

<style>
    /* ===== UrbanBot Styles ===== */
    #urbanbot-fab { position: fixed; bottom: 30px; right: 30px; z-index: 9999; cursor: pointer; display: flex; flex-direction: column; align-items: flex-end; }
    #urbanbot-avatar { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%); display: flex; align-items: center; justify-content: center; font-size: 36px; border: 3px solid rgba(255,255,255,0.9); box-shadow: 0 8px 32px rgba(16, 185, 129, 0.45); }
    #urbanbot-window { position: fixed; bottom: 115px; right: 30px; width: 370px; height: 520px; display: none; flex-direction: column; z-index: 10000; border-radius: 24px; overflow: hidden; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 60px rgba(0,0,0,0.4); }
    #urbanbot-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 18px 20px; display: flex; align-items: center; gap: 12px; }
    #urbanbot-header-info h4 { margin: 0; color: white; font-size: 0.95rem; font-weight: 800; }
    #urbanbot-close { background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; }
    #urbanbot-messages { flex: 1; padding: 20px 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
    .ub-msg { max-width: 85%; padding: 12px 16px; border-radius: 18px; font-size: 0.85rem; line-height: 1.5; }
    .ub-msg-bot { background: rgba(255,255,255,0.1); color: #f1f5f9; align-self: flex-start; }
    .ub-msg-user { background: #10b981; color: white; align-self: flex-end; }
    #urbanbot-input-area { padding: 16px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid rgba(255,255,255,0.05); display: flex; gap: 10px; }
    #urbanbot-input { flex: 1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px 16px; color: white; outline: none; }
    #urbanbot-send { width: 44px; height: 44px; border-radius: 12px; background: #10b981; border: none; color: white; cursor: pointer; }
</style>

<div id="urbanbot-fab">
    <div id="urbanbot-avatar">🤖</div>
</div>

<div id="urbanbot-window">
    <div id="urbanbot-header">
        <div id="urbanbot-header-info">
            <h4>UrbanBot ♻️</h4>
        </div>
        <button id="urbanbot-close" style="margin-left:auto;">✕</button>
    </div>
    <div id="urbanbot-messages">
        <div class="ub-msg ub-msg-bot">Hi! I'm Urban 🌿 Ask me anything about waste management!</div>
    </div>
    <div id="urbanbot-input-area">
        <input type="text" id="urbanbot-input" placeholder="Message Urban..." autocomplete="off">
        <button id="urbanbot-send">➤</button>
    </div>
</div>

<script>
(function() {
    const fab      = document.getElementById('urbanbot-fab');
    const chatWindow = document.getElementById('urbanbot-window');
    const closeBtn = document.getElementById('urbanbot-close');
    const msgArea  = document.getElementById('urbanbot-messages');
    const input    = document.getElementById('urbanbot-input');
    const sendBtn  = document.getElementById('urbanbot-send');

    fab.addEventListener('click', () => { chatWindow.style.display = 'flex'; input.focus(); });
    closeBtn.addEventListener('click', () => { chatWindow.style.display = 'none'; });

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        appendMsg(text, 'user');
        input.value = '';

        const typingEl = document.createElement('div');
        typingEl.className = 'ub-msg ub-msg-bot';
        typingEl.textContent = 'Urban thinking...';
        msgArea.appendChild(typingEl);
        msgArea.scrollTop = msgArea.scrollHeight;

        try {
            const response = await fetch('chatbot_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            typingEl.remove();
            appendMsg(data.response || "I'm processing your request...", 'bot');
        } catch (error) {
            typingEl.remove();
            appendMsg("❌ Syncing my routes! Please try again. ♻️", 'bot');
        }
        msgArea.scrollTop = msgArea.scrollHeight;
    }

    function appendMsg(text, type) {
        const div = document.createElement('div');
        div.className = `ub-msg ub-msg-${type}`;
        div.textContent = text;
        msgArea.appendChild(div);
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
})();
</script>