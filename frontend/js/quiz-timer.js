const timer = document.querySelector('#quiz-timer');
const deadline = Number(timer.dataset.deadline);
const serverTime = Number(timer.dataset.serverTime);
const startedAt = performance.now();
const submit = document.querySelector('#submit-quiz');
const status = document.querySelector('#quiz-timer-status');

function twoDigits(number) {
    if (number < 10) {
        return '0' + number;
    }
    return number;
}

function updateCountdown() {
    const elapsed = (performance.now() - startedAt) / 1000;
    const remaining = Math.max(0, Math.ceil(deadline - serverTime - elapsed));

    if (remaining === 0) {
        timer.textContent = 'Time remaining: 00:00';
        status.textContent = 'The submission window has closed.';
        submit.disabled = true;
        return;
    }

    const hours = Math.floor(remaining / 3600);
    const minutes = Math.floor(remaining / 60) % 60;
    const seconds = remaining % 60;
    timer.textContent = 'Time remaining: ' + twoDigits(hours) + ':' + twoDigits(minutes) + ':' + twoDigits(seconds);
    setTimeout(updateCountdown, 1000);
}

updateCountdown();
