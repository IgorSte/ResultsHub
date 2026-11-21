function showToast(message, type = 'info', title = '') {
    // Определяем цвета и иконки в зависимости от типа
    const config = {
        success: {
            bg: 'bg-success',
            icon: 'bi-check-circle-fill',
            title: title || 'Success'
        },
        error: {
            bg: 'bg-danger',
            icon: 'bi-exclamation-triangle-fill',
            title: title || 'Error'
        },
        warning: {
            bg: 'bg-warning',
            icon: 'bi-exclamation-triangle-fill',
            title: title || 'Warning'
        },
        info: {
            bg: 'bg-info',
            icon: 'bi-info-circle-fill',
            title: title || 'Info'
        }
    };
    
    const cfg = config[type] || config.info;
    const toastId = 'toast-' + Date.now();
    
    // Создаём HTML тоста
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${cfg.bg} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-3">
                    <i class="bi ${cfg.icon} fs-5"></i>
                    <div>
                        <strong class="toastr-header me-auto">${cfg.title}</strong>
                        <div class="toastr-message">${message}</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Добавляем тост в контейнер
    const container = document.getElementById('toastContainer');
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    // Инициализируем и показываем
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // Удаляем элемент после скрытия
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Упрощённые вызовы для частых случаев
function showSuccess(message, title = 'Success') {
    showToast(message, 'success', title);
}

function showError(message, title = 'Error') {
    showToast(message, 'error', title);
}

function showWarning(message, title = 'Warning') {
    showToast(message, 'warning', title);
}

function showInfo(message, title = 'Info') {
    showToast(message, 'info', title);
}