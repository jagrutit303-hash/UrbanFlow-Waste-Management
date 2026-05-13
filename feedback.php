<?php 
include('includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
// Fetch only this user's requests that haven't already been reviewed
$reqs = mysqli_query($conn, "SELECT dr.request_id, dr.category, dr.status, dr.created_at 
    FROM disposal_requests dr 
    LEFT JOIN feedback f ON dr.request_id = f.request_id
    WHERE dr.citizen_id = $uid AND f.feedback_id IS NULL
    ORDER BY dr.created_at DESC");
?>

<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="glass-card" data-aos="fade-up">
        <h2>Rate Our Service</h2>

        <?php if (mysqli_num_rows($reqs) > 0): ?>
        <form action="submit_feedback.php" method="POST">
            <label style="font-size: 0.85rem; color: #64748b; margin-bottom: 6px; display: block;">Select Request</label>
            <select name="request_id" required>
                <option value="">-- Choose a request --</option>
                <?php while($r = mysqli_fetch_assoc($reqs)): ?>
                    <option value="<?php echo $r['request_id']; ?>">
                        Request #<?php echo $r['request_id']; ?> — <?php echo htmlspecialchars($r['category']); ?> (<?php echo $r['status']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label style="font-size: 0.85rem; color: #64748b; margin-bottom: 6px; display: block; margin-top: 15px;">Rating</label>
            <select name="rating" required>
                <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                <option value="4">⭐⭐⭐⭐ (Good)</option>
                <option value="3">⭐⭐⭐ (Average)</option>
                <option value="2">⭐⭐ (Below Average)</option>
                <option value="1">⭐ (Poor)</option>
            </select>

            <textarea name="comment" placeholder="Any suggestions for UrbanFlow?"></textarea>

            <div style="margin: 15px 0;">
                <label style="font-size: 0.85rem; color: #64748b; margin-bottom: 6px; display: block;">🎤 Record Voice Feedback (Optional)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" id="recordBtn" class="btn-location" style="background: linear-gradient(135deg, #64748b, #475569); padding: 8px 15px; font-size: 0.8rem;">
                        <span id="micIcon">🎤</span> <span id="recordText">Record</span>
                    </button>
                    <div id="recordingStatus" style="font-size: 0.75rem; color: #ef4444; font-weight: 700; display: none;">
                        🔴 Recording... <span id="timer">0s</span>
                    </div>
                    <audio id="audioPlayback" controls style="display: none; height: 25px;"></audio>
                </div>
                <input type="hidden" name="voice_feedback_data" id="voice_feedback_data">
            </div>

            <button type="submit" class="btn-premium" style="width: 100%;">Submit Review</button>
        </form>

        <script>
            let mediaRecorder;
            let audioChunks = [];
            let isRecording = false;
            let timerInterval;
            let seconds = 0;

            const recordBtn = document.getElementById('recordBtn');
            const recordText = document.getElementById('recordText');
            const micIcon = document.getElementById('micIcon');
            const recordingStatus = document.getElementById('recordingStatus');
            const timerEl = document.getElementById('timer');
            const audioPlayback = document.getElementById('audioPlayback');
            const voiceDataInput = document.getElementById('voice_feedback_data');

            recordBtn.addEventListener('click', async () => {
                if (!isRecording) {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];
                        mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);
                        mediaRecorder.onstop = () => {
                            const blob = new Blob(audioChunks, { type: 'audio/webm' });
                            const reader = new FileReader();
                            reader.readAsDataURL(blob);
                            reader.onloadend = () => {
                                voiceDataInput.value = reader.result;
                                audioPlayback.src = reader.result;
                                audioPlayback.style.display = 'block';
                            };
                        };
                        mediaRecorder.start();
                        isRecording = true;
                        recordBtn.style.background = '#ef4444';
                        recordText.textContent = 'Stop';
                        micIcon.textContent = '⏹️';
                        recordingStatus.style.display = 'block';
                        seconds = 0;
                        timerInterval = setInterval(() => { seconds++; timerEl.textContent = seconds + 's'; }, 1000);
                    } catch (err) { alert('Mic access denied.'); }
                } else {
                    mediaRecorder.stop();
                    isRecording = false;
                    recordBtn.style.background = '#64748b';
                    recordText.textContent = 'Redo';
                    micIcon.textContent = '🎤';
                    recordingStatus.style.display = 'none';
                    clearInterval(timerInterval);
                }
            });
        </script>
        <?php else: ?>
        <div style="text-align: center; padding: 30px; color: #94a3b8;">
            <div style="font-size: 3rem; margin-bottom: 15px;">✨</div>
            <p>No requests awaiting feedback. Submit a disposal request first!</p>
            <a href="dashboard.php" class="btn-premium" style="display: inline-block; margin-top: 15px;">Go to Dashboard</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>