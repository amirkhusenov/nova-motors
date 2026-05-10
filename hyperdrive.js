(function () {
    const MODAL_ID = 'novaHyperdriveModal';
    const LAUNCHER_ID = 'novaHyperdriveLauncher';
    const STAR_COUNT = 140;

    let cars = [];
    let currentIndex = 0;
    let scanTimer = null;
    let starFrame = null;
    let speedTimer = null;
    let isBoost = false;
    let stars = [];
    let resizeHandler = null;

    function init() {
        cars = collectCars();
        if (cars.length === 0) {
            return;
        }

        injectLauncher();
        injectModal();
        bindEvents();
        window.NovaHyperDriveOpen = openModal;
    }

    function collectCars() {
        const cards = Array.from(document.querySelectorAll('.car-card'));
        const list = cards.map((card) => {
            const href = card.querySelector('a.rent-btn[href*="car.php?id="]')?.getAttribute('href') || '';
            if (!href) {
                return null;
            }

            const name = text(card.querySelector('.car-name'));
            const type = text(card.querySelector('.car-type'));
            const priceMain = text(card.querySelector('.price'));
            const pricePeriod = text(card.querySelector('.price-period'));
            const image = card.querySelector('.car-image')?.getAttribute('src') || '';

            return {
                href,
                name: name || 'Неизвестное авто',
                type: type || 'Класс не указан',
                price: (priceMain + (pricePeriod ? ' ' + pricePeriod : '')).trim(),
                image,
            };
        }).filter(Boolean);

        const uniqueByHref = new Map();
        list.forEach((item) => {
            uniqueByHref.set(item.href, item);
        });
        return Array.from(uniqueByHref.values());
    }

    function injectLauncher() {
        const button = document.createElement('button');
        button.type = 'button';
        button.id = LAUNCHER_ID;
        button.className = 'hyperdrive-launcher';
        button.innerHTML = '<i class="bi bi-stars me-1"></i>Гипердрайв';
        document.body.appendChild(button);
    }

    function injectModal() {
        const modal = document.createElement('div');
        modal.id = MODAL_ID;
        modal.className = 'hyperdrive-modal';
        modal.hidden = true;
        modal.innerHTML = [
            '<div class="hyperdrive-backdrop" data-hd-close></div>',
            '<div class="hyperdrive-dialog" role="dialog" aria-modal="true" aria-labelledby="hyperdriveTitle">',
            '  <canvas id="hyperdriveCanvas" class="hyperdrive-canvas"></canvas>',
            '  <div class="hyperdrive-content">',
            '    <div class="hyperdrive-head">',
            '      <h3 id="hyperdriveTitle">NOVA Гипердрайв</h3>',
            '      <button type="button" class="hyperdrive-close" data-hd-close aria-label="Закрыть"><i class="bi bi-x-lg"></i></button>',
            '    </div>',
            '    <div class="hyperdrive-current">',
            '      <img id="hdImage" class="hyperdrive-image" alt="Выбранный автомобиль">',
            '      <div class="hyperdrive-meta">',
            '        <div id="hdName" class="hyperdrive-name">Готово к запуску</div>',
            '        <div id="hdType" class="hyperdrive-type">-</div>',
            '        <div id="hdPrice" class="hyperdrive-price">-</div>',
            '      </div>',
            '    </div>',
            '    <div class="hyperdrive-speed">',
            '      <div class="hyperdrive-speed-track"><div id="hdSpeedBar" class="hyperdrive-speed-bar"></div></div>',
            '      <div id="hdSpeedValue" class="hyperdrive-speed-value">0 км/ч</div>',
            '    </div>',
            '    <div class="hyperdrive-actions">',
            '      <button type="button" id="hdScanBtn" class="btn btn-danger">Старт сканирования</button>',
            '      <button type="button" id="hdLockBtn" class="btn btn-outline-light" disabled>Зафиксировать цель</button>',
            '      <a id="hdOpenBtn" class="btn btn-outline-light disabled" href="#" tabindex="-1" aria-disabled="true">Открыть авто</a>',
            '    </div>',
            '    <div class="hyperdrive-hint">Пробел = Нитро, Enter = открыть выбранное авто</div>',
            '  </div>',
            '</div>'
        ].join('');
        document.body.appendChild(modal);
    }

    function bindEvents() {
        const launcher = document.getElementById(LAUNCHER_ID);
        const modal = document.getElementById(MODAL_ID);
        const scanBtn = document.getElementById('hdScanBtn');
        const lockBtn = document.getElementById('hdLockBtn');
        const openBtn = document.getElementById('hdOpenBtn');

        if (!launcher || !modal || !scanBtn || !lockBtn || !openBtn) {
            return;
        }

        launcher.addEventListener('click', openModal);
        modal.querySelectorAll('[data-hd-close]').forEach((el) => {
            el.addEventListener('click', closeModal);
        });

        scanBtn.addEventListener('click', () => {
            if (scanTimer) {
                stopScan();
            } else {
                startScan();
            }
        });

        lockBtn.addEventListener('click', () => {
            if (!scanTimer) {
                return;
            }
            stopScan();
            unlockOpenButton();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.hidden) {
                closeModal();
                return;
            }

            if (e.key === ' ' && !modal.hidden) {
                e.preventDefault();
                isBoost = true;
            }

            if (e.key === 'Enter' && !modal.hidden && !openBtn.classList.contains('disabled')) {
                e.preventDefault();
                window.location.href = openBtn.getAttribute('href') || '#';
            }
        });

        document.addEventListener('keyup', (e) => {
            if (e.key === ' ') {
                isBoost = false;
            }
        });
    }

    function openModal() {
        const modal = document.getElementById(MODAL_ID);
        if (!modal) {
            return;
        }
        if (!modal.hidden) {
            return;
        }

        modal.hidden = false;
        document.body.classList.add('hyperdrive-open');
        setRandomCurrent();
        resetOpenButton();
        startStarfield();
        startScan();
    }

    function closeModal() {
        const modal = document.getElementById(MODAL_ID);
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove('hyperdrive-open');
        stopScan();
        stopStarfield();
    }

    function startScan() {
        const lockBtn = document.getElementById('hdLockBtn');
        const scanBtn = document.getElementById('hdScanBtn');
        if (!lockBtn || !scanBtn) {
            return;
        }

        stopScan();
        lockBtn.disabled = false;
        scanBtn.textContent = 'Стоп сканирования';

        scanTimer = setInterval(() => {
            const next = Math.floor(Math.random() * cars.length);
            currentIndex = next;
            renderCurrent(cars[currentIndex]);
        }, 140);

        speedTimer = setInterval(() => {
            const base = 130 + Math.floor(Math.random() * 170);
            const boost = isBoost ? 120 + Math.floor(Math.random() * 80) : 0;
            setSpeed(base + boost);
        }, 110);
    }

    function stopScan() {
        const lockBtn = document.getElementById('hdLockBtn');
        const scanBtn = document.getElementById('hdScanBtn');

        if (scanTimer) {
            clearInterval(scanTimer);
            scanTimer = null;
        }
        if (speedTimer) {
            clearInterval(speedTimer);
            speedTimer = null;
        }
        if (lockBtn) {
            lockBtn.disabled = true;
        }
        if (scanBtn) {
            scanBtn.textContent = 'Старт сканирования';
        }
        setSpeed(90);
    }

    function setRandomCurrent() {
        currentIndex = Math.floor(Math.random() * cars.length);
        renderCurrent(cars[currentIndex]);
    }

    function renderCurrent(car) {
        const name = document.getElementById('hdName');
        const type = document.getElementById('hdType');
        const price = document.getElementById('hdPrice');
        const image = document.getElementById('hdImage');

        if (!name || !type || !price || !image || !car) {
            return;
        }

        name.textContent = car.name;
        type.textContent = car.type;
        price.textContent = car.price || 'Цена по запросу';
        image.src = car.image || '';
        image.alt = car.name;
    }

    function unlockOpenButton() {
        const openBtn = document.getElementById('hdOpenBtn');
        const car = cars[currentIndex];
        if (!openBtn || !car) {
            return;
        }

        openBtn.setAttribute('href', car.href);
        openBtn.classList.remove('disabled');
        openBtn.removeAttribute('tabindex');
        openBtn.removeAttribute('aria-disabled');
    }

    function resetOpenButton() {
        const openBtn = document.getElementById('hdOpenBtn');
        if (!openBtn) {
            return;
        }

        openBtn.setAttribute('href', '#');
        openBtn.classList.add('disabled');
        openBtn.setAttribute('tabindex', '-1');
        openBtn.setAttribute('aria-disabled', 'true');
    }

    function setSpeed(value) {
        const speedValue = document.getElementById('hdSpeedValue');
        const speedBar = document.getElementById('hdSpeedBar');
        if (!speedValue || !speedBar) {
            return;
        }

        const safe = Math.max(0, Math.min(520, Math.floor(value)));
        speedValue.textContent = safe + ' км/ч';
        speedBar.style.width = Math.min(100, (safe / 520) * 100) + '%';
    }

    function startStarfield() {
        const canvas = document.getElementById('hyperdriveCanvas');
        const dialog = document.querySelector('.hyperdrive-dialog');
        if (!canvas || !dialog) {
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        function resize() {
            const rect = dialog.getBoundingClientRect();
            canvas.width = Math.max(320, Math.floor(rect.width));
            canvas.height = Math.max(320, Math.floor(rect.height));
        }

        resize();
        if (resizeHandler) {
            window.removeEventListener('resize', resizeHandler);
        }
        resizeHandler = resize;
        window.addEventListener('resize', resizeHandler);

        stars = [];
        for (let i = 0; i < STAR_COUNT; i += 1) {
            stars.push(makeStar(canvas.width, canvas.height));
        }

        function frame() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#05070d';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const cx = canvas.width / 2;
            const cy = canvas.height / 2;

            for (let i = 0; i < stars.length; i += 1) {
                const star = stars[i];
                const speedFactor = isBoost ? 1.9 : 1.1;
                star.z -= speedFactor * 2.1;
                if (star.z <= 0.8) {
                    stars[i] = makeStar(canvas.width, canvas.height);
                    continue;
                }

                const k = 140 / star.z;
                const x = star.x * k + cx;
                const y = star.y * k + cy;
                if (x < 0 || x > canvas.width || y < 0 || y > canvas.height) {
                    stars[i] = makeStar(canvas.width, canvas.height);
                    continue;
                }

                const size = Math.max(0.7, (1 - star.z / 100) * 3.2);
                ctx.beginPath();
                ctx.fillStyle = isBoost ? 'rgba(255,120,80,0.95)' : 'rgba(199,225,255,0.92)';
                ctx.arc(x, y, size, 0, Math.PI * 2);
                ctx.fill();
            }

            starFrame = requestAnimationFrame(frame);
        }

        if (starFrame) {
            cancelAnimationFrame(starFrame);
        }
        starFrame = requestAnimationFrame(frame);
    }

    function stopStarfield() {
        if (starFrame) {
            cancelAnimationFrame(starFrame);
            starFrame = null;
        }
        if (resizeHandler) {
            window.removeEventListener('resize', resizeHandler);
            resizeHandler = null;
        }
    }

    function makeStar(width, height) {
        return {
            x: (Math.random() - 0.5) * width,
            y: (Math.random() - 0.5) * height,
            z: Math.random() * 100 + 1,
        };
    }

    function text(el) {
        return (el && el.textContent ? el.textContent : '').trim();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
