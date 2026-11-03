<style>
/* --- CSS CHO HIỆU ỨNG THẺ LẬT 3D --- */
.flashcard-container {
    perspective: 1000px;
    width: 100%;
    max-width: 350px;
    height: 350px;
    margin: 0 auto 20px;
}
.flashcard {
    width: 100%;
    height: 100%;
    position: relative;
    transition: transform 0.6s cubic-bezier(0.4, 0.2, 0.2, 1);
    transform-style: preserve-3d;
    cursor: pointer;
}
.flashcard.is-flipped {
    transform: rotateY(180deg);
}
.flashcard-face {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 20px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    text-align: center;
}
.flashcard-front {
    background: #fff;
    border: 3px solid #2ecc71;
}
.flashcard-front img {
    max-width: 90%;
    max-height: 160px;
    border-radius: 12px;
    margin-bottom: 20px;
    object-fit: cover;
}
.flashcard-back {
    background: #2ecc71;
    color: white;
    transform: rotateY(180deg);
    border: 3px solid #27ae60;
}
</style>

<div class="white-card" id="practice-menu">
    <h2 style="margin-bottom:20px;">Khu vực Luyện tập ✍️</h2>
    <div class="grid-cards">
        <div class="lesson-card" style="border-left: 5px solid #2ecc71;">
            <h3>Flashcard Từ vựng</h3>
            <p>Ghi nhớ từ vựng qua hình ảnh.</p>
            <button class="btn-main" id="btn-start-flashcard">Học ngay</button>
        </div>
        <div class="lesson-card" style="border-left: 5px solid #3498db;">
            <h3>Trắc nghiệm N5</h3>
            <p>Luyện đề thi thử chuẩn N5.</p>
            <button class="btn-main btn-start-test" data-level="N5">Bắt đầu N5</button>
        </div>
        <div class="lesson-card" style="border-left: 5px solid #9b59b6;">
            <h3>Trắc nghiệm N4</h3>
            <p>Luyện đề thi thử chuẩn N4.</p>
            <button class="btn-main btn-start-test" data-level="N4" >Bắt đầu N4</button>
        </div>
    </div>
</div>

<div class="white-card" id="quiz-area" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 id="quiz-title">Đề thi thử JLPT</h2>
        <button id="btn-back-menu" style="background: none; border: none; color: #888; cursor: pointer; font-weight: bold;"><i class="fas fa-arrow-left"></i> Quay lại</button>
    </div>
    
    <div id="quiz-content" style="margin-bottom: 20px;"></div>
    
    <button class="btn-main" id="btn-submit-test" style="display: none; width: 100%;">Nộp bài</button>
    <div id="test-result" style="margin-top: 15px; font-size: 18px; font-weight: bold; color: var(--pink-primary); display: none; text-align: center; padding: 15px; background: var(--pink-light); border-radius: 12px;"></div>
    <button class="btn-main" id="btn-retry-test" style="margin-top: 15px; background: #c7ead5; display: none; width: 100%;"><i class="fas fa-sync-alt"></i> Làm đề khác</button>
</div>

<div class="white-card" id="flashcard-area" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Flashcard Từ Vựng 🎴</h2>
        <button id="btn-back-menu-fc" style="background: none; border: none; color: #888; cursor: pointer; font-weight: bold;"><i class="fas fa-arrow-left"></i> Quay lại</button>
    </div>
    
    <div id="flashcard-content" style="text-align: center; margin-bottom: 20px;"></div>
    
    <div style="display: flex; justify-content: center; gap: 15px;">
        <button class="btn-main" id="btn-prev-fc" style="background: #95a5a6; display: none; width: 130px;"><i class="fas fa-arrow-left"></i> Trước</button>
        <button class="btn-main" id="btn-next-fc" style="background: #2ecc71; display: none; width: 130px;">Tiếp <i class="fas fa-arrow-right"></i></button>
    </div>
    <p id="flashcard-progress" style="text-align: center; margin-top: 15px; color: #888; font-weight: bold;"></p>
</div>

<script>
// Hàm đọc tiếng Nhật tự động bằng AI (Web Speech API)
window.playAudio = function(event, text) {
    // Ngăn chặn sự kiện click lan ra ngoài để không bị lật thẻ
    event.stopPropagation(); 
    
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel(); 
        
        let msg = new SpeechSynthesisUtterance();
        msg.text = text;
        msg.lang = 'ja-JP';
        msg.rate = 0.9;
        
        window.speechSynthesis.speak(msg);
    } else {
        alert("Trình duyệt của bạn không hỗ trợ tính năng đọc tự động.");
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const practiceMenu = document.getElementById('practice-menu');
    
    // --- BIẾN CHO QUIZ ---
    const btnStartTests = document.querySelectorAll('.btn-start-test');
    const quizArea = document.getElementById('quiz-area');
    const quizContent = document.getElementById('quiz-content');
    const btnSubmitTest = document.getElementById('btn-submit-test');
    const testResult = document.getElementById('test-result');
    const btnRetryTest = document.getElementById('btn-retry-test');
    const btnBackMenu = document.getElementById('btn-back-menu');
    let currentTestData = [];

    // --- BIẾN CHO FLASHCARD ---
    const btnStartFc = document.getElementById('btn-start-flashcard');
    const fcArea = document.getElementById('flashcard-area');
    const btnBackFc = document.getElementById('btn-back-menu-fc');
    const fcContent = document.getElementById('flashcard-content');
    const btnPrevFc = document.getElementById('btn-prev-fc');
    const btnNextFc = document.getElementById('btn-next-fc');
    const fcProgress = document.getElementById('flashcard-progress');
    let flashcardsData = [];
    let currentFcIndex = 0;


    // ==========================================
    // LOGIC XỬ LÝ TRẮC NGHIỆM JLPT (QUIZ)
    // ==========================================
    if (btnStartTests.length > 0) {
        btnStartTests.forEach(btn => {
            btn.addEventListener('click', function() {
                let level = this.getAttribute('data-level') || 'N5';
                document.getElementById('quiz-title').innerText = `Đề thi thử JLPT ${level}`;
                btnRetryTest.setAttribute('data-level', level); 
                loadNewTest(level);
            });
        });

        btnBackMenu.addEventListener('click', function() {
            quizArea.style.display = 'none';
            practiceMenu.style.display = 'block';
        });

        function loadNewTest(level) {
            practiceMenu.style.display = 'none';
            quizArea.style.display = 'block';
            
            quizContent.innerHTML = '<p style="text-align:center; color:#888; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Đang tải đề thi...</p>';
            btnSubmitTest.style.display = 'none';
            testResult.style.display = 'none';
            btnRetryTest.style.display = 'none';

            fetch(`api_get_quiz.php?level=${level}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        currentTestData = data.data;
                        renderTestContent(currentTestData);
                    } else {
                        quizContent.innerHTML = '<p style="color:red; text-align:center;">Chưa có đủ câu hỏi trong Database cho cấp độ này.</p>';
                    }
                })
                .catch(err => {
                    console.error("Lỗi:", err);
                    quizContent.innerHTML = '<p style="color:red; text-align:center;">Lỗi kết nối máy chủ.</p>';
                });
        }

        function renderTestContent(questions) {
            let html = '';
            questions.forEach((q, index) => {
                html += `
                    <div class="quiz-item" style="margin-bottom: 25px; padding: 15px; background: #fafafa; border-radius: 12px; border: 1px solid #eee;">
                        <p style="font-weight: 700; margin-bottom: 15px; font-size: 16px;">
                            <span style="color: var(--pink-primary);">Câu ${index + 1}:</span> ${q.question}
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <label style="cursor: pointer; padding: 8px 12px; background: white; border: 1px solid #ddd; border-radius: 8px; display: block; transition: 0.2s;">
                                <input type="radio" name="question_${q.id}" value="A" style="margin-right: 8px;"> ${q.option_a}
                            </label>
                            <label style="cursor: pointer; padding: 8px 12px; background: white; border: 1px solid #ddd; border-radius: 8px; display: block; transition: 0.2s;">
                                <input type="radio" name="question_${q.id}" value="B" style="margin-right: 8px;"> ${q.option_b}
                            </label>
                            <label style="cursor: pointer; padding: 8px 12px; background: white; border: 1px solid #ddd; border-radius: 8px; display: block; transition: 0.2s;">
                                <input type="radio" name="question_${q.id}" value="C" style="margin-right: 8px;"> ${q.option_c}
                            </label>
                            <label style="cursor: pointer; padding: 8px 12px; background: white; border: 1px solid #ddd; border-radius: 8px; display: block; transition: 0.2s;">
                                <input type="radio" name="question_${q.id}" value="D" style="margin-right: 8px;"> ${q.option_d}
                            </label>
                        </div>
                    </div>
                `;
            });
            quizContent.innerHTML = html;
            btnSubmitTest.style.display = 'block';
        }

        btnSubmitTest.addEventListener('click', function() {
            let score = 0;
            currentTestData.forEach(q => {
                let selectedOption = document.querySelector(`input[name="question_${q.id}"]:checked`);
                let allRadios = document.querySelectorAll(`input[name="question_${q.id}"]`);
                allRadios.forEach(radio => {
                    radio.disabled = true;
                    if (radio.value === q.correct_answer) {
                        radio.parentElement.style.background = '#d4edda';
                        radio.parentElement.style.borderColor = '#2ecc71';
                    }
                });
                if (selectedOption) {
                    if (selectedOption.value === q.correct_answer) {
                        score++;
                    } else {
                        selectedOption.parentElement.style.background = '#f8d7da';
                        selectedOption.parentElement.style.borderColor = '#e74c3c';
                    }
                }
            });

            let percentage = Math.round((score / currentTestData.length) * 100);
            let message = percentage >= 80 ? 'Xuất sắc! 🌸' : (percentage >= 50 ? 'Khá tốt! Cố lên nhé 💪' : 'Bạn cần ôn tập thêm! 📚');
            
            testResult.innerHTML = `Kết quả: ${score} / ${currentTestData.length} câu (${percentage}%)<br><span style="font-size: 14px; font-weight: normal; color: #555;">${message}</span>`;
            testResult.style.display = 'block';
            btnSubmitTest.style.display = 'none';
            btnRetryTest.style.display = 'block';
        });

        btnRetryTest.addEventListener('click', function() {
            let level = this.getAttribute('data-level') || 'N5'; 
            loadNewTest(level);
        });
    }

    // ==========================================
    // LOGIC XỬ LÝ FLASHCARD
    // ==========================================
    if (btnStartFc) {
        btnStartFc.addEventListener('click', function() {
            practiceMenu.style.display = 'none';
            fcArea.style.display = 'block';
            loadFlashcards();
        });

        btnBackFc.addEventListener('click', function() {
            fcArea.style.display = 'none';
            practiceMenu.style.display = 'block';
        });

        function loadFlashcards() {
            fcContent.innerHTML = '<p style="color:#888;"><i class="fas fa-spinner fa-spin"></i> Đang tải thẻ lật...</p>';
            btnPrevFc.style.display = 'none';
            btnNextFc.style.display = 'none';
            fcProgress.innerText = '';

            fetch('api_get_flashcards.php')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        flashcardsData = data.data;
                        currentFcIndex = 0;
                        renderFlashcard();
                    } else {
                        fcContent.innerHTML = '<p style="color:red;">Chưa có dữ liệu Flashcard trong database.</p>';
                    }
                })
                .catch(err => {
                    console.error("Lỗi Flashcard:", err);
                    fcContent.innerHTML = '<p style="color:red;">Lỗi kết nối máy chủ.</p>';
                });
        }

        function renderFlashcard() {
            if (flashcardsData.length === 0) return;
            
            let card = flashcardsData[currentFcIndex];
            // Nếu có ảnh thì hiển thị, không thì bỏ trống
            let imageHtml = card.image_url ? `<img src="${card.image_url}" alt="Minh họa">` : '';
            
            fcContent.innerHTML = `
                <div class="flashcard-container" onclick="this.querySelector('.flashcard').classList.toggle('is-flipped')">
                    <div class="flashcard">
                        <div class="flashcard-face flashcard-front">
                            ${imageHtml}
                            <div style="font-size: 36px; color: #2ecc71; font-weight: 900; display: flex; align-items: center; justify-content: center;">
                                ${card.japanese_word}
                                <i class="fas fa-volume-up" onclick="playAudio(event, '${card.japanese_word}')" title="Nghe phát âm" style="color: #3498db; margin-left: 15px; font-size: 28px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'"></i>
                            </div>
                            <div style="font-size: 14px; color: #aaa; margin-top: 15px;"><i class="fas fa-hand-pointer"></i> Chạm ra ngoài để xem nghĩa</div>
                        </div>
                        
                        <div class="flashcard-face flashcard-back">
                            <div style="font-size: 32px; font-weight: 900;">${card.vietnamese_meaning}</div>
                        </div>
                    </div>
                </div>
            `;

            fcProgress.innerText = `Thẻ ${currentFcIndex + 1} / ${flashcardsData.length}`;
            
            btnPrevFc.style.display = currentFcIndex > 0 ? 'inline-block' : 'none';
            btnNextFc.style.display = currentFcIndex < flashcardsData.length - 1 ? 'inline-block' : 'none';
        }

        btnPrevFc.addEventListener('click', () => {
            if (currentFcIndex > 0) {
                currentFcIndex--;
                renderFlashcard();
            }
        });

        btnNextFc.addEventListener('click', () => {
            if (currentFcIndex < flashcardsData.length - 1) {
                currentFcIndex++;
                renderFlashcard();
            }
        });
    }
});
</script>