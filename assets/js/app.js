/**
 * Codeflip Arena - JavaScript Global & Utilitários
 * Requisitos:
 * - TOAST nativo elegante para notificações
 * - MODAL que substitui as caixas de mensagem nativas (alert/confirm) do navegador
 */

// Sintetizador Web Audio API para efeitos sonoros imersivos
const ArenaAudio = {
    ctx: null,
    init() {
        if (!this.ctx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) this.ctx = new AudioCtx();
        }
    },
    playTone(freq, type = 'sine', duration = 0.15, vol = 0.1) {
        try {
            this.init();
            if (!this.ctx) return;
            if (this.ctx.state === 'suspended') this.ctx.resume();

            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            gain.gain.setValueAtTime(vol, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + duration);

            osc.connect(gain);
            gain.connect(this.ctx.destination);
            osc.start();
            osc.stop(this.ctx.currentTime + duration);
        } catch (e) {
            // Silencioso em caso de restrição de autoplay
        }
    },
    success() {
        this.playTone(523.25, 'triangle', 0.1, 0.15); // C5
        setTimeout(() => this.playTone(659.25, 'triangle', 0.1, 0.15), 100); // E5
        setTimeout(() => this.playTone(783.99, 'triangle', 0.2, 0.2), 200); // G5
    },
    error() {
        this.playTone(220, 'sawtooth', 0.15, 0.2); // A3
        setTimeout(() => this.playTone(185, 'sawtooth', 0.25, 0.2), 120); // F#3
    },
    click() {
        this.playTone(800, 'sine', 0.05, 0.05);
    }
};

// ========================================================
// SISTEMA DE TOAST NOTIFICATIONS
// ========================================================
const Toast = {
    container: null,
    init() {
        if (!this.container) {
            let el = document.getElementById('toast-container');
            if (!el) {
                el = document.createElement('div');
                el.id = 'toast-container';
                el.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none p-4 sm:p-0';
                document.body.appendChild(el);
            }
            this.container = el;
        }
    },
    show(type = 'info', message = '', title = '') {
        this.init();

        const config = {
            success: {
                bg: 'bg-emerald-950/90 border-emerald-500/40 text-emerald-200',
                icon: 'fa-circle-check text-emerald-400',
                defaultTitle: 'Sucesso!'
            },
            error: {
                bg: 'bg-rose-950/90 border-rose-500/40 text-rose-200',
                icon: 'fa-circle-xmark text-rose-400',
                defaultTitle: 'Atenção!'
            },
            warning: {
                bg: 'bg-amber-950/90 border-amber-500/40 text-amber-200',
                icon: 'fa-triangle-exclamation text-amber-400',
                defaultTitle: 'Aviso'
            },
            info: {
                bg: 'bg-indigo-950/90 border-indigo-500/40 text-indigo-200',
                icon: 'fa-circle-info text-indigo-400',
                defaultTitle: 'Informação'
            }
        }[type] || {
            bg: 'bg-slate-900/90 border-slate-700 text-slate-200',
            icon: 'fa-bell text-slate-400',
            defaultTitle: 'Notificação'
        };

        const toastEl = document.createElement('div');
        toastEl.className = `pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-xl backdrop-blur-md transition-all duration-300 transform translate-y-4 opacity-0 ${config.bg}`;

        toastEl.innerHTML = `
            <div class="text-xl mt-0.5"><i class="fa-solid ${config.icon}"></i></div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-white tracking-wide">${title || config.defaultTitle}</div>
                <div class="text-xs mt-0.5 leading-relaxed break-words">${message}</div>
            </div>
            <button type="button" class="text-slate-400 hover:text-white transition p-1 ml-1" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        this.container.appendChild(toastEl);

        // Animação de entrada
        requestAnimationFrame(() => {
            toastEl.classList.remove('translate-y-4', 'opacity-0');
            toastEl.classList.add('translate-y-0', 'opacity-100');
        });

        // Som discreto
        if (type === 'error') {
            ArenaAudio.error();
        } else if (type === 'success') {
            ArenaAudio.success();
        }

        // Auto remoção após 5 segundos
        setTimeout(() => {
            if (toastEl.parentElement) {
                toastEl.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toastEl.remove(), 300);
            }
        }, 5000);
    }
};

window.showToast = (type, message, title) => Toast.show(type, message, title);

// ========================================================
// SISTEMA DE MODAL PARA REQUISITO CORE
// Requisito: Tratar mensagens de caixa de mensagens nativas (alert/confirm) em um MODAL
// ========================================================
const ArenaModal = {
    overlay: null,
    init() {
        if (!this.overlay) {
            let el = document.getElementById('arena-modal-root');
            if (!el) {
                el = document.createElement('div');
                el.id = 'arena-modal-root';
                el.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200';
                el.innerHTML = `
                    <div id="arena-modal-box" class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl transform scale-95 transition-transform duration-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div id="arena-modal-icon-container" class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"></div>
                            <h3 id="arena-modal-title" class="text-lg font-bold text-white tracking-wide"></h3>
                        </div>
                        <p id="arena-modal-text" class="text-sm text-slate-300 leading-relaxed mb-6"></p>
                        <div id="arena-modal-actions" class="flex items-center justify-end gap-3"></div>
                    </div>
                `;
                document.body.appendChild(el);
            }
            this.overlay = el;
        }
    },
    open({ title, message, type = 'info', confirmText = 'OK', cancelText = null }) {
        this.init();
        return new Promise((resolve) => {
            const iconContainer = document.getElementById('arena-modal-icon-container');
            const titleEl = document.getElementById('arena-modal-title');
            const textEl = document.getElementById('arena-modal-text');
            const actionsEl = document.getElementById('arena-modal-actions');
            const box = document.getElementById('arena-modal-box');

            const typeStyles = {
                danger: {
                    bg: 'bg-rose-500/20 text-rose-400',
                    icon: 'fa-triangle-exclamation',
                    btn: 'bg-rose-600 hover:bg-rose-500 text-white'
                },
                warning: {
                    bg: 'bg-amber-500/20 text-amber-400',
                    icon: 'fa-circle-exclamation',
                    btn: 'bg-amber-600 hover:bg-amber-500 text-white'
                },
                info: {
                    bg: 'bg-indigo-500/20 text-indigo-400',
                    icon: 'fa-circle-info',
                    btn: 'bg-indigo-600 hover:bg-indigo-500 text-white'
                }
            }[type] || {
                bg: 'bg-indigo-500/20 text-indigo-400',
                icon: 'fa-circle-info',
                btn: 'bg-indigo-600 hover:bg-indigo-500 text-white'
            };

            iconContainer.className = `w-10 h-10 rounded-xl flex items-center justify-center text-xl ${typeStyles.bg}`;
            iconContainer.innerHTML = `<i class="fa-solid ${typeStyles.icon}"></i>`;
            titleEl.textContent = title;
            textEl.textContent = message;

            actionsEl.innerHTML = '';

            if (cancelText) {
                const cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'px-4 py-2 text-sm font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 rounded-xl transition';
                cancelBtn.textContent = cancelText;
                cancelBtn.onclick = () => {
                    this.close();
                    resolve(false);
                };
                actionsEl.appendChild(cancelBtn);
            }

            const confirmBtn = document.createElement('button');
            confirmBtn.type = 'button';
            confirmBtn.className = `px-5 py-2 text-sm font-bold rounded-xl transition shadow-lg ${typeStyles.btn}`;
            confirmBtn.textContent = confirmText;
            confirmBtn.onclick = () => {
                this.close();
                resolve(true);
            };
            actionsEl.appendChild(confirmBtn);

            // Abre modal
            this.overlay.classList.remove('hidden');
            requestAnimationFrame(() => {
                this.overlay.classList.remove('opacity-0');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
                confirmBtn.focus();
            });
        });
    },
    close() {
        if (!this.overlay) return;
        const box = document.getElementById('arena-modal-box');
        this.overlay.classList.add('opacity-0');
        if (box) box.classList.remove('scale-100');
        setTimeout(() => {
            this.overlay.classList.add('hidden');
        }, 200);
    },
    alert(message, title = 'Aviso da Arena') {
        return this.open({
            title,
            message,
            type: 'info',
            confirmText: 'Entendido'
        });
    },
    confirm(message, title = 'Confirmação Necessária', type = 'warning') {
        return this.open({
            title,
            message,
            type,
            confirmText: 'Confirmar',
            cancelText: 'Cancelar'
        });
    }
};

window.ArenaModal = ArenaModal;

// Substituição padrão das chamadas nativas do navegador para cumprir o requisito estrito
window.alert = function (message) {
    ArenaModal.alert(String(message));
};

// Interceptor global para links e formulários que possuem 'data-confirm'
document.addEventListener('DOMContentLoaded', () => {
    // Menu responsivo mobile
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu-drawer');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Interceptador para links que exigem confirmação (ex: deletar item)
    document.body.addEventListener('click', async (e) => {
        const trigger = e.target.closest('[data-confirm]');
        if (trigger) {
            e.preventDefault();
            const message = trigger.getAttribute('data-confirm');
            const title = trigger.getAttribute('data-title') || 'Tem certeza disso?';
            const isConfirmed = await ArenaModal.confirm(message, title, 'danger');
            if (isConfirmed) {
                if (trigger.tagName === 'A') {
                    window.location.href = trigger.href;
                } else if (trigger.form) {
                    trigger.form.submit();
                }
            }
        }
    });
});
