// Система уведомлений для замены alert
class NotificationSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Создаем контейнер для уведомлений
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;

        // Иконки для разных типов уведомлений
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        // Стили для разных типов
        const styles = {
            success: {
                backgroundColor: '#d4edda',
                borderColor: '#c3e6cb',
                color: '#155724',
                borderLeft: '4px solid #28a745'
            },
            error: {
                backgroundColor: '#f8d7da',
                borderColor: '#f5c6cb',
                color: '#721c24',
                borderLeft: '4px solid #dc3545'
            },
            warning: {
                backgroundColor: '#fff3cd',
                borderColor: '#ffeaa7',
                color: '#856404',
                borderLeft: '4px solid #ffc107'
            },
            info: {
                backgroundColor: '#d1ecf1',
                borderColor: '#bee5eb',
                color: '#0c5460',
                borderLeft: '4px solid #17a2b8'
            }
        };

        notification.style.cssText = `
            background: ${styles[type].backgroundColor};
            border: 1px solid ${styles[type].borderColor};
            border-left: ${styles[type].borderLeft};
            color: ${styles[type].color};
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            pointer-events: auto;
            position: relative;
            max-width: 100%;
            word-wrap: break-word;
        `;

        // Создаем содержимое уведомления
        notification.innerHTML = `
            <span style="font-size: 18px; flex-shrink: 0;">${icons[type]}</span>
            <span style="flex: 1;">${message}</span>
            <button class="notification-close" style="
                background: none;
                border: none;
                color: inherit;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                margin-left: 10px;
                opacity: 0.7;
                transition: opacity 0.2s ease;
                flex-shrink: 0;
            ">&times;</button>
        `;

        // Добавляем уведомление в контейнер
        this.container.appendChild(notification);

        // Анимация появления
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });

        // Обработчик закрытия
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.remove(notification);
        });

        // Автоматическое удаление
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }

        return notification;
    }

    remove(notification) {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    // Методы для разных типов уведомлений
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }

    // Метод для замены стандартного alert
    alert(message, type = 'info') {
        return this.show(message, type, 0); // Без автозакрытия для важных сообщений
    }
}

// Создаем глобальный экземпляр
window.notifications = new NotificationSystem();

// Заменяем стандартный alert
window.originalAlert = window.alert;
window.alert = function (message) {
    // Используем нашу систему уведомлений
    window.notifications.alert(message, 'warning');

    // Для критически важных сообщений можно оставить оригинальный alert
    // window.originalAlert(message);
};

// Функции для удобного использования
window.showSuccess = (message) => window.notifications.success(message);
window.showError = (message) => window.notifications.error(message);
window.showWarning = (message) => window.notifications.warning(message);
window.showInfo = (message) => window.notifications.info(message);

// Анимация при наведении на кнопку закрытия
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('mouseover', function (e) {
        if (e.target.classList.contains('notification-close')) {
            e.target.style.opacity = '1';
        }
    });

    document.addEventListener('mouseout', function (e) {
        if (e.target.classList.contains('notification-close')) {
            e.target.style.opacity = '0.7';
        }
    });
});
