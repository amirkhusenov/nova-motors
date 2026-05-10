(function () {
    const MODAL_ID = 'driveLabModal';

    const STATE = {
        budget: 8500,
        seats: 2,
        days: 3,
        mood: 'business',
        priority: 'comfort',
        tempo: 'balanced',
        need3d: false,
    };

    const TYPE_METRICS = {
        sedan: { comfort: 78, speed: 58, economy: 63, prestige: 66, practicality: 72, business: 88, romance: 60, family: 72, adventure: 42, premium: 70 },
        suv: { comfort: 74, speed: 56, economy: 52, prestige: 72, practicality: 90, business: 68, romance: 58, family: 90, adventure: 86, premium: 74 },
        sport: { comfort: 50, speed: 95, economy: 38, prestige: 86, practicality: 44, business: 58, romance: 84, family: 38, adventure: 78, premium: 86 },
        luxury: { comfort: 96, speed: 70, economy: 35, prestige: 98, practicality: 60, business: 92, romance: 88, family: 62, adventure: 50, premium: 100 },
        electric: { comfort: 80, speed: 72, economy: 90, prestige: 82, practicality: 70, business: 84, romance: 68, family: 74, adventure: 56, premium: 88 },
        muscle: { comfort: 55, speed: 82, economy: 34, prestige: 72, practicality: 48, business: 50, romance: 76, family: 42, adventure: 80, premium: 70 },
        hatchback: { comfort: 64, speed: 60, economy: 74, prestige: 48, practicality: 80, business: 56, romance: 54, family: 74, adventure: 62, premium: 50 },
        generic: { comfort: 60, speed: 60, economy: 60, prestige: 60, practicality: 60, business: 60, romance: 60, family: 60, adventure: 60, premium: 60 },
    };

    const STATUS_TEXTS = [
        'Сканируем автопарк...',
        'Собираем сильные стороны моделей...',
        'Строим финальный рейтинг...',
        'Готово: подбор обновлен',
    ];

    let cars = [];
    let simulationTimer = null;

    function initDriveLab() {
        const data = Array.isArray(window.NOVA_DRIVE_LAB_CARS) ? window.NOVA_DRIVE_LAB_CARS : [];
        cars = data.filter((item) => item && item.id);
        if (cars.length === 0) {
            return;
        }

        bindCoreEvents();
        bindControls();
        updateControlLabels();
        recalcAndRender(false);
    }

    function bindCoreEvents() {
        const modal = document.getElementById(MODAL_ID);
        if (!modal) {
            return;
        }

        document.querySelectorAll('.open-drive-lab').forEach((btn) => {
            btn.addEventListener('click', openModal);
        });

        modal.querySelectorAll('[data-dl-close]').forEach((btn) => {
            btn.addEventListener('click', closeModal);
        });

        window.addEventListener('nova:open-drive-lab', openModal);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    }

    function bindControls() {
        const budgetRange = document.getElementById('dlBudgetRange');
        const seatsRange = document.getElementById('dlSeatsRange');
        const daysRange = document.getElementById('dlDaysRange');
        const need3d = document.getElementById('dlNeed3d');
        const runButton = document.getElementById('dlRunButton');

        if (budgetRange) {
            budgetRange.addEventListener('input', () => {
                STATE.budget = toNumber(budgetRange.value, 8500);
                updateControlLabels();
            });
            budgetRange.addEventListener('change', () => recalcAndRender(true));
        }

        if (seatsRange) {
            seatsRange.addEventListener('input', () => {
                STATE.seats = toNumber(seatsRange.value, 2);
                updateControlLabels();
            });
            seatsRange.addEventListener('change', () => recalcAndRender(true));
        }

        if (daysRange) {
            daysRange.addEventListener('input', () => {
                STATE.days = toNumber(daysRange.value, 3);
                updateControlLabels();
            });
            daysRange.addEventListener('change', () => recalcAndRender(true));
        }

        if (need3d) {
            need3d.addEventListener('change', () => {
                STATE.need3d = need3d.checked;
                recalcAndRender(true);
            });
        }

        document.querySelectorAll('#dlMoodGroup [data-mood]').forEach((btn) => {
            btn.addEventListener('click', () => {
                setActiveChip('#dlMoodGroup [data-mood]', btn);
                STATE.mood = String(btn.dataset.mood || 'business');
                recalcAndRender(true);
            });
        });

        document.querySelectorAll('#dlPriorityGroup [data-priority]').forEach((btn) => {
            btn.addEventListener('click', () => {
                setActiveChip('#dlPriorityGroup [data-priority]', btn);
                STATE.priority = String(btn.dataset.priority || 'comfort');
                recalcAndRender(true);
            });
        });

        document.querySelectorAll('#dlTempoGroup [data-tempo]').forEach((btn) => {
            btn.addEventListener('click', () => {
                setActiveChip('#dlTempoGroup [data-tempo]', btn);
                STATE.tempo = String(btn.dataset.tempo || 'balanced');
                recalcAndRender(true);
            });
        });

        if (runButton) {
            runButton.addEventListener('click', () => recalcAndRender(true));
        }
    }

    function updateControlLabels() {
        setText('dlBudgetValue', formatRub(STATE.budget) + ' ₽');
        setText('dlSeatsValue', String(STATE.seats));
        setText('dlDaysValue', STATE.days + ' ' + pluralDays(STATE.days));
    }

    function setActiveChip(selector, activeBtn) {
        document.querySelectorAll(selector).forEach((btn) => btn.classList.remove('active'));
        activeBtn.classList.add('active');
    }

    function openModal() {
        const modal = document.getElementById(MODAL_ID);
        if (!modal) {
            return;
        }

        modal.hidden = false;
        document.body.classList.add('drive-lab-open');
        recalcAndRender(true);
    }

    function closeModal() {
        const modal = document.getElementById(MODAL_ID);
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove('drive-lab-open');
        stopSimulation();
    }

    function recalcAndRender(withSimulation) {
        const scored = buildScoredCars();

        if (withSimulation) {
            runSimulation(scored);
            return;
        }

        setProgress(100);
        setText('dlStatusText', STATUS_TEXTS[3]);
        renderResults(scored);
    }

    function runSimulation(scoredCars) {
        stopSimulation();
        setProgress(0);
        setText('dlStatusText', STATUS_TEXTS[0]);

        let progress = 0;
        let phase = 0;
        simulationTimer = setInterval(() => {
            progress += 7 + Math.random() * 9;
            if (progress >= 33 && phase === 0) {
                phase = 1;
                setText('dlStatusText', STATUS_TEXTS[1]);
            }
            if (progress >= 66 && phase === 1) {
                phase = 2;
                setText('dlStatusText', STATUS_TEXTS[2]);
            }

            if (progress >= 100) {
                progress = 100;
                stopSimulation();
                setProgress(100);
                setText('dlStatusText', STATUS_TEXTS[3]);
                renderResults(scoredCars);
                return;
            }

            setProgress(progress);
        }, 85);
    }

    function stopSimulation() {
        if (simulationTimer) {
            clearInterval(simulationTimer);
            simulationTimer = null;
        }
    }

    function setProgress(value) {
        const bar = document.getElementById('dlProgressBar');
        if (!bar) {
            return;
        }
        const safe = clamp(Math.round(value), 0, 100);
        bar.style.width = safe + '%';
    }

    function buildScoredCars() {
        const list = cars.map((car) => {
            const typeKey = resolveType(String(car.type || ''));
            const metrics = TYPE_METRICS[typeKey] || TYPE_METRICS.generic;
            const price = toNumber(car.price_per_day, 0);
            const seats = extractSeats(car.seats);

            const budgetFit = computeBudgetFit(price, STATE.budget);
            const seatFit = computeSeatFit(seats, STATE.seats);
            const scenarioFit = toNumber(metrics[STATE.mood], 60);
            const priorityFit = computePriorityFit(metrics, price);
            const tempoFit = computeTempoFit(metrics, price);
            const threeDMod = STATE.need3d ? (car.has_3d ? 10 : -25) : (car.has_3d ? 3 : 0);

            let score =
                budgetFit * 0.28 +
                seatFit * 0.16 +
                scenarioFit * 0.20 +
                priorityFit * 0.20 +
                tempoFit * 0.12 +
                threeDMod;

            score = clamp(score, 0, 100);

            return {
                car,
                score,
                price,
                metrics,
                reasonList: buildReasons({
                    budgetFit,
                    seatFit,
                    scenarioFit,
                    tempoFit,
                    has3d: !!car.has_3d,
                }),
            };
        });

        list.sort((a, b) => b.score - a.score);
        return list;
    }

    function renderResults(scored) {
        if (!Array.isArray(scored) || scored.length === 0) {
            renderEmptyState();
            return;
        }

        const top = scored.slice(0, 3);
        renderHero(top[0]);
        renderTopList(top);
        renderDuel(top[0], top[1] || top[0]);
    }

    function renderEmptyState() {
        setTextNode(document.getElementById('dlHeroName'), 'Подходящие авто не найдены');
        setTextNode(document.getElementById('dlHeroMeta'), 'Измените фильтры и пересчитайте подбор');

        const heroImage = document.getElementById('dlHeroImage');
        if (heroImage) {
            heroImage.src = '';
            heroImage.alt = 'Нет результата';
        }

        const reasonList = document.getElementById('dlHeroReasonList');
        if (reasonList) {
            reasonList.innerHTML = '';
        }

        const topList = document.getElementById('dlTopList');
        if (topList) {
            topList.innerHTML = '';
        }

        const duel = document.getElementById('dlDuel');
        if (duel) {
            duel.innerHTML = '';
        }
    }

    function renderHero(item) {
        if (!item) {
            return;
        }

        const heroImage = document.getElementById('dlHeroImage');
        const heroName = document.getElementById('dlHeroName');
        const heroMeta = document.getElementById('dlHeroMeta');
        const heroOpen = document.getElementById('dlHeroOpen');
        const reasonList = document.getElementById('dlHeroReasonList');

        if (heroImage) {
            heroImage.src = item.car.image || '';
            heroImage.alt = item.car.name || 'Автомобиль';
        }

        setTextNode(heroName, item.car.name || '-');
        setTextNode(heroMeta, (item.car.type || '-') + ' • ' + formatRub(item.price) + ' ₽/день' + ' • рейтинг ' + Math.round(item.score));

        if (reasonList) {
            reasonList.innerHTML = '';
            item.reasonList.forEach((reason) => {
                const li = document.createElement('li');
                li.textContent = reason;
                reasonList.appendChild(li);
            });
        }

        if (heroOpen) {
            heroOpen.href = item.car.car_url || '#';
            heroOpen.classList.remove('disabled');
            heroOpen.removeAttribute('tabindex');
            heroOpen.removeAttribute('aria-disabled');
        }
    }

    function renderTopList(items) {
        const container = document.getElementById('dlTopList');
        if (!container) {
            return;
        }

        container.innerHTML = '';
        items.forEach((item, index) => {
            const card = document.createElement('article');
            card.className = 'dl-top-card';
            card.innerHTML = [
                '<div class="dl-top-rank">#' + (index + 1) + '</div>',
                '<div class="dl-top-main">',
                '  <strong>' + escapeHtml(item.car.name || '-') + '</strong>',
                '  <span>' + escapeHtml((item.car.type || '-') + ' • ' + formatRub(item.price) + ' ₽/день') + '</span>',
                '</div>',
                '<div class="dl-top-score">' + Math.round(item.score) + '</div>',
                '<a class="btn btn-sm btn-outline-light" href="' + escapeHtml(item.car.car_url || '#') + '">Открыть</a>',
            ].join('');
            container.appendChild(card);
        });
    }

    function renderDuel(first, second) {
        const duel = document.getElementById('dlDuel');
        if (!duel || !first || !second) {
            return;
        }

        const metrics = [
            { key: 'comfort', title: 'Комфорт' },
            { key: 'speed', title: 'Драйв' },
            { key: 'prestige', title: 'Престиж' },
            { key: 'practicality', title: 'Практичность' },
            { key: 'economy', title: 'Экономичность' },
        ];

        const rows = metrics.map((metric) => {
            const a = clamp(toNumber(first.metrics[metric.key], 50), 0, 100);
            const b = clamp(toNumber(second.metrics[metric.key], 50), 0, 100);
            return [
                '<div class="dl-duel-row">',
                '  <div class="dl-duel-label">' + metric.title + '</div>',
                '  <div class="dl-duel-values">',
                '    <span class="dl-duel-num">' + Math.round(a) + '</span>',
                '    <div class="dl-duel-track">',
                '      <span class="dl-duel-fill dl-a" style="width:' + a + '%"></span>',
                '      <span class="dl-duel-fill dl-b" style="width:' + b + '%"></span>',
                '    </div>',
                '    <span class="dl-duel-num">' + Math.round(b) + '</span>',
                '  </div>',
                '</div>',
            ].join('');
        }).join('');

        duel.innerHTML = [
            '<div class="dl-duel-head">',
            '  <strong>Дуэль лидеров</strong>',
            '  <span>' + escapeHtml(first.car.name || '-') + ' vs ' + escapeHtml(second.car.name || '-') + '</span>',
            '</div>',
            rows,
        ].join('');
    }

    function computeBudgetFit(price, budget) {
        if (price <= 0 || budget <= 0) {
            return 30;
        }
        const diff = Math.abs(price - budget);
        let fit = 100 - (diff / (budget * 1.15)) * 100;
        if (price <= budget) {
            fit += Math.min(13, (budget - price) / 850);
        }
        return clamp(fit, 8, 100);
    }

    function computeSeatFit(seats, need) {
        if (seats >= need) {
            return clamp(100 - Math.max(0, seats - need) * 7, 45, 100);
        }
        return clamp(100 - (need - seats) * 35, 4, 70);
    }

    function computePriorityFit(metrics, price) {
        const key = STATE.priority;
        const base = toNumber(metrics[key], 60);

        if (key === 'economy') {
            const priceBonus = clamp((12000 - price) / 120, -15, 20);
            return clamp(base + priceBonus, 0, 100);
        }

        if (key === 'prestige') {
            const prestigeBoost = clamp((price - 7000) / 180, -8, 15);
            return clamp(base + prestigeBoost, 0, 100);
        }

        return clamp(base, 0, 100);
    }

    function computeTempoFit(metrics, price) {
        if (STATE.tempo === 'calm') {
            return clamp(metrics.comfort * 0.6 + metrics.economy * 0.4, 0, 100);
        }
        if (STATE.tempo === 'rush') {
            return clamp(metrics.speed * 0.75 + metrics.prestige * 0.25, 0, 100);
        }
        if (STATE.tempo === 'night') {
            const nightBoost = clamp((price - 5500) / 230, -6, 14);
            return clamp(metrics.prestige * 0.56 + metrics.comfort * 0.24 + metrics.speed * 0.2 + nightBoost, 0, 100);
        }
        return clamp(metrics.comfort * 0.36 + metrics.speed * 0.2 + metrics.economy * 0.24 + metrics.practicality * 0.2, 0, 100);
    }

    function buildReasons(ctx) {
        const reasons = [];

        if (ctx.budgetFit >= 75) {
            reasons.push('Отлично вписывается в заданный бюджет.');
        } else if (ctx.budgetFit >= 55) {
            reasons.push('Цена близка к вашему диапазону.');
        } else {
            reasons.push('Цена выше ожиданий, но компенсируется другими плюсами.');
        }

        if (ctx.seatFit >= 75) {
            reasons.push('Комфортно подходит по количеству мест.');
        } else {
            reasons.push('По местам на грани, проверьте формат поездки.');
        }

        if (ctx.scenarioFit >= 72) {
            reasons.push('Сильное попадание в выбранный сценарий поездки.');
        } else {
            reasons.push('Не профильный вариант, но с достойным потенциалом.');
        }

        if (ctx.tempoFit >= 70) {
            reasons.push('Хорошо держит выбранный темп.');
        }

        if (STATE.need3d && ctx.has3d) {
            reasons.push('Есть 3D модель для детального просмотра.');
        } else if (STATE.need3d && !ctx.has3d) {
            reasons.push('3D модель отсутствует.');
        }

        return reasons.slice(0, 4);
    }

    function resolveType(typeRaw) {
        const type = String(typeRaw || '').toLowerCase();
        if (type.indexOf('lux') !== -1) return 'luxury';
        if (type.indexOf('sport') !== -1) return 'sport';
        if (type.indexOf('suv') !== -1) return 'suv';
        if (type.indexOf('elect') !== -1) return 'electric';
        if (type.indexOf('muscle') !== -1) return 'muscle';
        if (type.indexOf('hatch') !== -1) return 'hatchback';
        if (type.indexOf('sedan') !== -1) return 'sedan';
        return 'generic';
    }

    function extractSeats(value) {
        const match = String(value || '').match(/\d+/);
        return match ? toNumber(match[0], 4) : 4;
    }

    function formatRub(value) {
        const num = Math.max(0, Math.round(toNumber(value, 0)));
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function pluralDays(value) {
        const n = Math.abs(toNumber(value, 0));
        const mod100 = n % 100;
        const mod10 = n % 10;
        if (mod100 >= 11 && mod100 <= 14) return 'дней';
        if (mod10 === 1) return 'день';
        if (mod10 >= 2 && mod10 <= 4) return 'дня';
        return 'дней';
    }

    function toNumber(value, fallback) {
        const n = Number(value);
        return Number.isFinite(n) ? n : fallback;
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
        }
    }

    function setTextNode(el, value) {
        if (el) {
            el.textContent = value;
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
        document.addEventListener('DOMContentLoaded', initDriveLab);
    } else {
        initDriveLab();
    }
})();
