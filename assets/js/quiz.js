/**
 * Codeflip Arena - Motor Interativo do Quiz (Engine de Gameplay)
 * Controla cronômetro regressivo, feedback imediato de acerto/erro, áudio e streaks.
 */

class ArenaQuizEngine {
    constructor(config) {
        this.submitUrl = config.submitUrl;
        this.questionDuration = config.questionDuration || 20; // 20 segundos por pergunta
        this.timeLeft = this.questionDuration;
        this.timerInterval = null;
        this.isSubmitting = false;

        this.elements = {
            timerText: document.getElementById('quiz-timer-text'),
            timerBar: document.getElementById('quiz-timer-bar'),
            streakBadge: document.getElementById('quiz-streak-badge'),
            streakText: document.getElementById('quiz-streak-text'),
            scoreText: document.getElementById('quiz-score-text'),
            options: document.querySelectorAll('.quiz-option-btn'),
            feedbackArea: document.getElementById('quiz-feedback-area'),
            feedbackTitle: document.getElementById('quiz-feedback-title'),
            feedbackText: document.getElementById('quiz-feedback-text'),
            nextBtn: document.getElementById('quiz-next-btn')
        };

        this.init();
    }

    init() {
        this.startTimer();
        this.elements.options.forEach(btn => {
            btn.addEventListener('click', () => {
                const optId = btn.getAttribute('data-option-id');
                this.submitAnswer(optId, btn);
            });
        });

        if (this.elements.nextBtn) {
            this.elements.nextBtn.addEventListener('click', () => {
                window.location.reload();
            });
        }
    }

    startTimer() {
        this.updateTimerDisplay();
        this.timerInterval = setInterval(() => {
            this.timeLeft--;
            this.updateTimerDisplay();

            if (this.timeLeft <= 0) {
                clearInterval(this.timerInterval);
                this.timeExpired();
            }
        }, 1000);
    }

    updateTimerDisplay() {
        if (this.elements.timerText) {
            this.elements.timerText.textContent = `${this.timeLeft}s`;
        }
        if (this.elements.timerBar) {
            const percentage = (this.timeLeft / this.questionDuration) * 100;
            this.elements.timerBar.style.width = `${percentage}%`;

            if (this.timeLeft <= 5) {
                this.elements.timerBar.classList.remove('bg-indigo-500', 'bg-amber-500');
                this.elements.timerBar.classList.add('bg-rose-500');
            } else if (this.timeLeft <= 10) {
                this.elements.timerBar.classList.remove('bg-indigo-500', 'bg-rose-500');
                this.elements.timerBar.classList.add('bg-amber-500');
            }
        }
    }

    timeExpired() {
        if (this.isSubmitting) return;
        this.submitAnswer(0, null, true);
    }

    async submitAnswer(optionId, clickedBtn = null, isTimeout = false) {
        if (this.isSubmitting) return;
        this.isSubmitting = true;
        clearInterval(this.timerInterval);

        // Desabilita todas as opções
        this.elements.options.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('cursor-not-allowed', 'opacity-80');
        });

        try {
            const response = await fetch(this.submitUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    opcao_id: optionId,
                    time_left: this.timeLeft
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.handleResponse(data, optionId, clickedBtn, isTimeout);
            } else if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                showToast('error', data.message || 'Falha na comunicação com o servidor.');
            }
        } catch (err) {
            console.error('Erro de envio:', err);
            showToast('error', 'Ocorreu um erro ao registrar sua resposta.');
        }
    }

    handleResponse(data, optionId, clickedBtn, isTimeout) {
        // Atualiza pontuação e streak
        if (this.elements.scoreText) {
            this.elements.scoreText.textContent = `${data.current_score} pts`;
        }

        if (data.streak > 1 && this.elements.streakBadge) {
            this.elements.streakBadge.classList.remove('hidden');
            this.elements.streakText.textContent = `${data.streak}x Combo! 🔥`;
        } else if (this.elements.streakBadge) {
            this.elements.streakBadge.classList.add('hidden');
        }

        // Destaca a opção correta
        this.elements.options.forEach(btn => {
            const btnId = parseInt(btn.getAttribute('data-option-id'));
            if (btnId === data.correct_id) {
                btn.classList.remove('bg-slate-800', 'border-slate-700');
                btn.classList.add('bg-emerald-950/90', 'border-emerald-500', 'text-emerald-100', 'ring-2', 'ring-emerald-500');
                btn.querySelector('.option-indicator').innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i>';
            }
        });

        // Caso o usuário tenha errado
        if (!data.is_correct && clickedBtn) {
            clickedBtn.classList.remove('bg-slate-800', 'border-slate-700');
            clickedBtn.classList.add('bg-rose-950/90', 'border-rose-500', 'text-rose-100', 'ring-2', 'ring-rose-500');
            clickedBtn.querySelector('.option-indicator').innerHTML = '<i class="fa-solid fa-xmark text-rose-400"></i>';
        }

        // Áudio e Toast
        if (data.is_correct) {
            ArenaAudio.success();
            showToast('success', `+${data.earned_points} pontos adicionados!`, 'Resposta Correta!');
        } else {
            ArenaAudio.error();
            if (isTimeout) {
                showToast('warning', 'O tempo se esgotou para esta pergunta.', 'Tempo Esgotado!');
            } else {
                showToast('error', 'Não foi dessa vez!', 'Resposta Incorreta');
            }
        }

        // Exibe área de feedback e explicação
        if (this.elements.feedbackArea) {
            this.elements.feedbackArea.classList.remove('hidden');
            this.elements.feedbackTitle.innerHTML = data.is_correct
                ? '<i class="fa-solid fa-circle-check text-emerald-400 mr-2"></i> Você acertou!'
                : '<i class="fa-solid fa-circle-xmark text-rose-400 mr-2"></i> Resposta Incorreta';
            this.elements.feedbackTitle.className = data.is_correct
                ? 'text-base font-bold text-emerald-400 mb-1'
                : 'text-base font-bold text-rose-400 mb-1';

            this.elements.feedbackText.textContent = data.explanation || 'Excelente questão de fixação!';

            // Ajusta o botão próximo / finalizar
            if (data.is_finished) {
                this.elements.nextBtn.innerHTML = 'Ver Resultado Final <i class="fa-solid fa-trophy ml-2"></i>';
                this.elements.nextBtn.onclick = () => {
                    window.location.href = data.redirect;
                };
            } else {
                this.elements.nextBtn.innerHTML = 'Próxima Pergunta <i class="fa-solid fa-arrow-right ml-2"></i>';
                this.elements.nextBtn.onclick = () => {
                    window.location.reload();
                };
            }
            this.elements.nextBtn.focus();
        }
    }
}

window.ArenaQuizEngine = ArenaQuizEngine;
