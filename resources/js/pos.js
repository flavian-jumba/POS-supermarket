function startClock(el) {
    const format = () =>
        new Date().toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });

    el.textContent = format();
    setInterval(() => {
        el.textContent = format();
    }, 1000);
}

function focusScanner() {
    const input = document.querySelector('[data-pos-scanner]');
    if (input && document.activeElement === document.body) {
        input.focus();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.body.hasAttribute('data-pos-app')) {
        return;
    }

    document.querySelectorAll('[data-pos-clock]').forEach(startClock);
    focusScanner();
});

document.addEventListener('livewire:navigated', () => {
    if (document.body.hasAttribute('data-pos-app')) {
        focusScanner();
    }
});

document.addEventListener('livewire:init', () => {
    if (!document.body.hasAttribute('data-pos-app')) {
        return;
    }

    Livewire.on('pos-refocus-scanner', () => {
        const input = document.querySelector('[data-pos-scanner]');
        if (input) {
            input.focus();
        }
    });
});
