(function () {
    const MODAL_ID = 'quickActionsModal';
    const STORAGE_KEY = 'nova_cinema_mode';

    function initQuickActions() {
        if (document.getElementById(MODAL_ID)) {
            return;
        }

        injectMarkup();
        bindEvents();
        restoreCinemaMode();
    }

    function injectMarkup() {
        const launcher = document.createElement('button');
        launcher.type = 'button';
        launcher.className = 'quick-actions-launcher';
        launcher.id = 'quickActionsLauncher';
        launcher.innerHTML = '<i class="bi bi-command me-1"></i>Команды';
        document.body.appendChild(launcher);

        const modal = document.createElement('div');
        modal.id = MODAL_ID;
        modal.className = 'quick-actions-modal';
        modal.hidden = true;
        modal.innerHTML = [
            '<div class="quick-actions-backdrop" data-qa-close></div>',
            '<div class="quick-actions-dialog" role="dialog" aria-modal="true" aria-labelledby="quickActionsTitle">',
            '  <div class="quick-actions-head">',
            '    <i class="bi bi-search quick-actions-search-icon"></i>',
            '    <input id="quickActionsInput" class="quick-actions-input" type="text" autocomplete="off" placeholder="Введите команду... (парк, профиль, случайное, 3d, кино, гипердрайв)">',
            '    <button type="button" class="quick-actions-close" data-qa-close aria-label="Закрыть"><i class="bi bi-x-lg"></i></button>',
            '  </div>',
            '  <div class="quick-actions-list" id="quickActionsList"></div>',
            '</div>'
        ].join('');
        document.body.appendChild(modal);
    }

    function bindEvents() {
        const launcher = document.getElementById('quickActionsLauncher');
        const modal = document.getElementById(MODAL_ID);
        const input = document.getElementById('quickActionsInput');
        const list = document.getElementById('quickActionsList');

        const open = () => {
            renderActions('');
            modal.hidden = false;
            document.body.classList.add('quick-actions-open');
            setTimeout(() => input.focus(), 0);
        };

        const close = () => {
            modal.hidden = true;
            document.body.classList.remove('quick-actions-open');
            input.value = '';
        };

        launcher.addEventListener('click', open);

        modal.querySelectorAll('[data-qa-close]').forEach((el) => {
            el.addEventListener('click', close);
        });

        document.addEventListener('keydown', (e) => {
            const isCmdK = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k';
            if (isCmdK) {
                e.preventDefault();
                if (modal.hidden) {
                    open();
                } else {
                    close();
                }
                return;
            }

            if (!modal.hidden && e.key === 'Escape') {
                close();
            }
        });

        input.addEventListener('input', () => renderActions(input.value));
        input.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') {
                return;
            }
            const firstAction = list.querySelector('.quick-actions-item');
            if (firstAction) {
                firstAction.click();
            }
        });

        function renderActions(query) {
            const actions = buildActions()
                .filter((action) => matchesQuery(action, query))
                .slice(0, 9);

            list.innerHTML = '';
            if (actions.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'quick-actions-empty';
                empty.textContent = 'Команды не найдены';
                list.appendChild(empty);
                return;
            }

            actions.forEach((action) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'quick-actions-item';
                button.innerHTML = '<span>' + escapeHtml(action.title) + '</span><small>' + escapeHtml(action.hint || '') + '</small>';
                button.addEventListener('click', () => {
                    close();
                    action.run();
                });
                list.appendChild(button);
            });
        }
    }

    function buildActions() {
        const actions = [];
        const add = (title, hint, run) => actions.push({ title, hint, run });

        add('Перейти к главному экрану', 'прокрутка', () => scrollToSelector('#home'));
        add('Перейти к автопарку', 'прокрутка', () => scrollToSelector('#park'));
        add('Перейти в раздел о компании', 'прокрутка', () => scrollToSelector('#about'));
        add('Перейти к вопросам', 'прокрутка', () => scrollToSelector('.faq-section'));
        add('Перейти к контактам', 'прокрутка', () => scrollToSelector('#contact'));

        add('Открыть профиль', 'навигация', () => { window.location.href = './profile.php'; });
        add('Открыть вход', 'навигация', () => { window.location.href = './login.php'; });
        add('Открыть регистрацию', 'навигация', () => { window.location.href = './register.php'; });
        add('Открыть главную страницу', 'навигация', () => { window.location.href = './index.php'; });

        add('Случайный автомобиль', 'вау', openRandomCar);
        add('Кино-режим: вкл/выкл', 'визуальный режим', toggleCinemaMode);
        if (typeof window.NovaHyperDriveOpen === 'function') {
            add('Открыть Гипердрайв', 'вау режим', () => window.NovaHyperDriveOpen());
        }

        const open3dButton = document.querySelector('.js-open-car-3d');
        if (open3dButton) {
            add('Открыть 3D модель', 'страница авто', () => open3dButton.click());
        }

        return actions;
    }

    function matchesQuery(action, query) {
        const normalized = normalize(query);
        if (!normalized) {
            return true;
        }
        const haystack = normalize(action.title + ' ' + (action.hint || ''));
        return haystack.indexOf(normalized) !== -1;
    }

    function normalize(value) {
        return String(value || '').toLowerCase().trim();
    }

    function scrollToSelector(selector) {
        const el = document.querySelector(selector);
        if (!el) {
            return;
        }
        const offset = 74;
        window.scrollTo({
            top: Math.max(0, el.getBoundingClientRect().top + window.scrollY - offset),
            behavior: 'smooth'
        });
    }

    function openRandomCar() {
        const links = Array.from(document.querySelectorAll('a.rent-btn[href*="car.php?id="]'));
        if (links.length === 0) {
            return;
        }
        const random = links[Math.floor(Math.random() * links.length)];
        window.location.href = random.getAttribute('href');
    }

    function toggleCinemaMode() {
        document.body.classList.toggle('site-cinema-mode');
        const enabled = document.body.classList.contains('site-cinema-mode');
        try {
            localStorage.setItem(STORAGE_KEY, enabled ? '1' : '0');
        } catch (_e) {
            // ignore storage errors
        }
    }

    function restoreCinemaMode() {
        try {
            const enabled = localStorage.getItem(STORAGE_KEY) === '1';
            document.body.classList.toggle('site-cinema-mode', enabled);
        } catch (_e) {
            // ignore storage errors
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuickActions);
    } else {
        initQuickActions();
    }
})();
