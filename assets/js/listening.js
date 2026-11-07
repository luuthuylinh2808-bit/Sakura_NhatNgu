/* ==========================================
   SAKURA - YOUTUBE LISTENING PLAYER
========================================== */

let player = null;
let currentSentenceIndex = 0;
let currentSpeed = 1;
let isPlayerReady = false;
let isTranscriptHidden = false;

// Lưu các câu đã trả lời đúng
let correctSentences = new Set();

// ==========================================
// 1. KHỞI TẠO YOUTUBE PLAYER
// ==========================================

function getSentences() {
    return window.listeningSentences || [];
}

// Thư viện YouTube API sẽ tự tìm hàm này để gọi
function onYouTubeIframeAPIReady() {
    if (window.youtubeVideoId) {
        player = new YT.Player('youtube-player', {
            height: '100%',
            width: '100%',
            videoId: window.youtubeVideoId,
            playerVars: {
                'playsinline': 1,
                'rel': 0,
                'modestbranding': 1
            },
            events: {
                'onReady': onPlayerReady
            }
        });
    }
}

function onPlayerReady(event) {
    // ĐÁNH DẤU PLAYER ĐÃ SẴN SÀNG
    isPlayerReady = true;

    // Gán các sự kiện cho nút phát/phát lại video chung
    const playBtn = document.getElementById('playVideoBtn');
    if (playBtn) {
        playBtn.addEventListener('click', () => {
            if (player && typeof player.playVideo === 'function') {
                player.playVideo();
            }
        });
    }
    
    const replayBtn = document.getElementById('replayVideoBtn');
    if (replayBtn) {
        replayBtn.addEventListener('click', () => {
            if (player && typeof player.seekTo === 'function') {
                player.seekTo(0, true);
                player.playVideo();
            }
        });
    }
}

// ==========================================
// 2. LẤY CÂU THOẠI HIỆN TẠI
// ==========================================

function getCurrentSentence() {
    const sentences = getSentences();
    if (!sentences.length) {
        return null;
    }
    return sentences[currentSentenceIndex];
}

// ==========================================
// 3. PHÁT TỪNG CÂU VÀ TỰ DỪNG
// ==========================================

function playCurrentSentence() {
    if (!isPlayerReady || !player || typeof player.playVideo !== 'function') {
        alert("Video đang tải, bạn đợi một chút nhé!");
        return;
    }

    const sentence = getCurrentSentence();
    if (!sentence) {
        alert("Bài nghe chưa có dữ liệu câu thoại!");
        return;
    }

    const startTime = Number(sentence.start_time);
    const endTime = Number(sentence.end_time);

    if (endTime <= startTime) {
        alert("Thời gian câu thoại chưa hợp lệ!");
        return;
    }

    if (typeof player.setPlaybackRate === 'function') {
        player.setPlaybackRate(currentSpeed);
    }

    // Tua đến thời điểm bắt đầu câu & phát video
    player.seekTo(startTime, true);
    player.playVideo();
}

// ==========================================
// 4. KIỂM TRA THỜI GIAN VIDEO (QUÉT MỖI 50MS)
// ==========================================

setInterval(function () {
    if (!isPlayerReady || !player || typeof player.getCurrentTime !== 'function') {
        return;
    }

    const sentence = getCurrentSentence();
    if (!sentence) {
        return;
    }

    const currentTime = player.getCurrentTime();
    const endTime = Number(sentence.end_time);

    if (typeof player.getPlayerState === 'function') {
        const playerState = player.getPlayerState();

        // 1 = YT.PlayerState.PLAYING
        if (playerState === 1 && currentTime >= endTime) {
            player.pauseVideo();
        }
    }
}, 50); // 50ms giúp ngắt chính xác thời gian kết thúc

// ==========================================
// 5. CẬP NHẬT GIAO DIỆN CÂU HIỆN TẠI
// ==========================================

function updateSentenceDisplay() {
    const sentence = getCurrentSentence();
    const sentences = getSentences();
    const numberDisplay = document.getElementById("currentSentenceNumber");

    if (numberDisplay) {
        if (!sentence) {
            numberDisplay.textContent = "Chưa có câu thoại";
            return;
        }
        numberDisplay.textContent = "Câu " + (currentSentenceIndex + 1) + " / " + sentences.length;
    }

    // Highlight câu hiện tại trong danh sách bên phải
    document.querySelectorAll(".transcript-item").forEach(function (item) {
        item.classList.remove("active");
    });

    const activeItem = document.querySelector('.transcript-item[data-index="' + currentSentenceIndex + '"]');
    if (activeItem) {
        activeItem.classList.add("active");
        activeItem.scrollIntoView({
            behavior: "smooth",
            block: "nearest"
        });
    }

    // Xóa ô nhập liệu câu trả lời & thông báo cũ
    const answerInput = document.getElementById("userAnswer");
    if (answerInput) answerInput.value = "";

    const feedback = document.getElementById("answerFeedback");
    if (feedback) feedback.innerHTML = "";
}

// ==========================================
// 6. CHUYỂN CÂU (BẬT MODAL KHI HOÀN THÀNH)
// ==========================================

function nextSentence() {
    const sentences = getSentences();
    if (currentSentenceIndex < sentences.length - 1) {
        currentSentenceIndex++;
        updateSentenceDisplay();
    } else {
        // Hiển thị Modal hoàn thành bài tập
        let correctScore = document.getElementById('scoreDisplay') ? document.getElementById('scoreDisplay').innerText : '0';
        let totalSentences = sentences.length;

        const scoreTextEl = document.getElementById('modalScoreText');
        if (scoreTextEl) {
            scoreTextEl.innerText = correctScore + " / " + totalSentences;
        }

        const modalEl = document.getElementById('sakuraResultModal');
        if (modalEl) {
            modalEl.style.display = 'flex';
        }
    }
}

function previousSentence() {
    if (currentSentenceIndex > 0) {
        currentSentenceIndex--;
        updateSentenceDisplay();
    }
}

// ==========================================
// 7. PHÁT LẠI CÂU HIỆN TẠI / VIDEO
// ==========================================

function replayCurrentSentence() {
    playCurrentSentence();
}

function replayWholeVideo() {
    if (!isPlayerReady || !player) return;
    currentSentenceIndex = 0;
    updateSentenceDisplay();
    player.seekTo(0, true);
    player.playVideo();
}

// ==========================================
// 8. KIỂM TRA ĐÁP ÁN
// ==========================================

function normalizeJapanese(text) {
    if (!text) return "";
    return text
        .normalize("NFKC")
        .replace(/[、。！？!?.,\s]/g, "")
        .trim();
}

function checkAnswer() {
    const sentence = getCurrentSentence();
    if (!sentence) return;

    const answerInput = document.getElementById("userAnswer");
    const userAnswer = answerInput ? answerInput.value : "";
    const correctAnswer = sentence.japanese_text;
    const feedback = document.getElementById("answerFeedback");

    if (normalizeJapanese(userAnswer) === normalizeJapanese(correctAnswer)) {
        if (feedback) {
            feedback.innerHTML = '<div class="feedback-correct" style="color: #2ecc71; font-weight: bold; margin-top: 10px;">🎉 Chính xác! Bạn đã nghe đúng câu thoại.</div>';
        }
        correctSentences.add(currentSentenceIndex);

        const activeItem = document.querySelector('.transcript-item[data-index="' + currentSentenceIndex + '"]');
        if (activeItem) {
            activeItem.classList.add("correct");
        }
    } else {
        if (feedback) {
            feedback.innerHTML = '<div class="feedback-wrong" style="color: #e74c3c; font-weight: bold; margin-top: 10px;">❌ Chưa chính xác. Bạn hãy nghe lại nhé!</div>';
        }
    }

    updateScore();
}

// ==========================================
// 9. HIỆN ĐÁP ÁN
// ==========================================

function showAnswer() {
    const sentence = getCurrentSentence();
    if (!sentence) return;

    const feedback = document.getElementById("answerFeedback");
    if (feedback) {
        feedback.innerHTML =
            '<div class="feedback-correct" style="color: #2b2b2b; background: #fff0f3; padding: 10px; border-radius: 8px; margin-top: 10px;">' +
            'Đáp án: <strong>' + sentence.japanese_text + '</strong><br>' +
            '<span style="color: #666; font-size: 13px;">' + (sentence.vietnamese_text || "") + '</span>' +
            '</div>';
    }
}

// ==========================================
// 10. TÍNH ĐIỂM
// ==========================================

function updateScore() {
    const scoreDisplay = document.getElementById("scoreDisplay");
    if (scoreDisplay) {
        scoreDisplay.textContent = correctSentences.size;
    }
}

// ==========================================
// 11. ẨN / HIỆN CÂU THOẠI
// ==========================================

function toggleTranscript() {
    const transcriptPanel = document.querySelector(".transcript-panel");
    const button = document.getElementById("toggleTranscriptBtn");

    isTranscriptHidden = !isTranscriptHidden;

    if (transcriptPanel && button) {
        if (isTranscriptHidden) {
            transcriptPanel.classList.add("transcript-hidden");
            button.innerHTML = '<i class="fas fa-eye"></i> Hiện lời thoại';
        } else {
            transcriptPanel.classList.remove("transcript-hidden");
            button.innerHTML = '<i class="fas fa-eye-slash"></i> Ẩn lời thoại';
        }
    }
}

// ==========================================
// 12. CHỌN TỐC ĐỘ VIDEO
// ==========================================

function changePlaybackSpeed(speed) {
    currentSpeed = Number(speed);
    if (isPlayerReady && player && typeof player.setPlaybackRate === 'function') {
        player.setPlaybackRate(currentSpeed);
    }
}

// ==========================================
// 13. GẮN SỰ KIỆN KHI DOM READY
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
    // Nút Bắt đầu
    const playVideoBtn = document.getElementById("playVideoBtn");
    if (playVideoBtn) {
        playVideoBtn.addEventListener("click", playCurrentSentence);
    }

    // Nút Phát lại toàn bộ
    const replayVideoBtn = document.getElementById("replayVideoBtn");
    if (replayVideoBtn) {
        replayVideoBtn.addEventListener("click", replayWholeVideo);
    }

    // Nút Phát lại câu hiện tại
    const replaySentenceBtn = document.getElementById("replaySentenceBtn");
    if (replaySentenceBtn) {
        replaySentenceBtn.addEventListener("click", replayCurrentSentence);
    }

    // Nút Tạm dừng
    const pauseSentenceBtn = document.getElementById("pauseSentenceBtn");
    if (pauseSentenceBtn) {
        pauseSentenceBtn.addEventListener("click", function () {
            if (isPlayerReady && player && typeof player.pauseVideo === 'function') {
                player.pauseVideo();
            }
        });
    }

    // Nút Điều hướng câu
    const prevBtn = document.getElementById("previousBtn");
    if (prevBtn) prevBtn.addEventListener("click", previousSentence);

    const nextBtn = document.getElementById("nextSentenceBtn");
    if (nextBtn) nextBtn.addEventListener("click", nextSentence);

    // Nút Kiểm tra & Hiện đáp án
    const checkBtn = document.getElementById("checkAnswerBtn");
    if (checkBtn) checkBtn.addEventListener("click", checkAnswer);

    const showBtn = document.getElementById("showAnswerBtn");
    if (showBtn) showBtn.addEventListener("click", showAnswer);

    // Nút Ẩn/Hiện lời thoại
    const toggleBtn = document.getElementById("toggleTranscriptBtn");
    if (toggleBtn) toggleBtn.addEventListener("click", toggleTranscript);

    // Chọn tốc độ
    const speedSelect = document.getElementById("playbackSpeed");
    if (speedSelect) {
        speedSelect.addEventListener("change", function () {
            changePlaybackSpeed(this.value);
        });
    }

    // Chọn câu trực tiếp từ danh sách
    document.querySelectorAll(".select-sentence-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            currentSentenceIndex = Number(this.dataset.index);
            updateSentenceDisplay();
            playCurrentSentence();
        });
    });

    // Click vào item câu thoại để chuyển câu
    document.querySelectorAll(".transcript-item").forEach(function (item) {
        item.addEventListener("click", function (event) {
            if (event.target.classList.contains("select-sentence-btn")) {
                return;
            }
            currentSentenceIndex = Number(this.dataset.index);
            updateSentenceDisplay();
        });
    });
});