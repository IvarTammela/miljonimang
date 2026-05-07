const points = [100, 200, 300, 500, 1000, 2000, 4000, 8000, 16000, 32000, 64000, 125000, 250000, 500000, 1000000];
const safeLevels = {5: 1000, 10: 32000, 15: 1000000};
const letters = ['A', 'B', 'C', 'D'];
const staticGame = {id: 'game', title: 'Miljonimäng'};
const app = document.querySelector('#app');

let bank = null;
let game = null;

document.addEventListener('click', (event) => {
    const action = event.target.dataset.action;
    if (!action) {
        return;
    }

    if (action === 'home') {
        game = null;
        renderHome();
    }
});

fetch('data/question-bank.json')
    .then((response) => response.json())
    .then((data) => {
        bank = data;
        renderHome();
    })
    .catch(() => {
        app.innerHTML = '<div class="notice error">Küsimustepanka ei saanud laadida.</div>';
    });

function renderHome() {
    const rows = leaderboard();
    app.innerHTML = `
        <section class="panel">
            <p class="eyebrow">Üks mäng</p>
            <h1>Kontrolli, kas mõistad lahendust</h1>
            <p class="lead">See GitHub Pages versioon töötab ilma serverita. Küsimused on eelgenereeritud suurde küsimustepanka ja iga mäng valib neist juhuslikult 15.</p>
            <label class="field">
                <span>Mängija nimi</span>
                <input id="player" type="text" maxlength="40" placeholder="Anonüümne">
            </label>
            <div class="start-card">
                <div>
                    <strong>Miljonimäng</strong>
                    <span class="muted">${questionCount()} küsimust pangas</span>
                </div>
                <button class="primary" type="button" data-start>Alusta mängu</button>
            </div>
        </section>
        ${renderLeaderboard(rows)}
    `;

    app.querySelector('[data-start]').addEventListener('click', startGame);
}

function startGame() {
    const questions = combinedQuestions();
    const player = document.querySelector('#player').value.trim() || 'Anonüümne';
    const selected = [
        ...takeRandom(questions.easy, 5),
        ...takeRandom(questions.medium, 5),
        ...takeRandom(questions.hard, 5),
    ].map((question, index) => shuffleQuestion({...question, level: index + 1}));

    game = {
        task: staticGame,
        player,
        questions: selected,
        current: 0,
        earned: 0,
        safe: 0,
        finished: false,
        status: 'playing',
        lifelines: {fifty: true, hint: true, audience: true},
        removedOptions: [],
        hint: null,
        audience: null,
        lastExplanation: null,
        saved: false,
    };

    renderGame();
}

function renderGame() {
    const question = game.questions[game.current];
    const finished = game.finished;
    if (finished && !game.saved) {
        saveResult(game);
        game.saved = true;
    }

    app.innerHTML = `
        <section class="game-grid">
            <aside class="ladder">${renderLadder()}</aside>
            <div class="panel">
                <div class="game-head">
                    <div>
                        <p class="eyebrow">${escapeHtml(game.task.title)}</p>
                        <h1>${finished ? 'Mäng lõppes' : `Küsimus ${game.current + 1} / 15`}</h1>
                    </div>
                    <div class="score">
                        <span>Hetkeseis</span>
                        <strong>${formatPoints(game.earned)} punkti</strong>
                    </div>
                </div>
                ${finished ? renderResult() : renderQuestion(question)}
            </div>
        </section>
        ${finished ? renderLeaderboard(leaderboard()) : ''}
    `;

    if (!finished) {
        app.querySelectorAll('[data-answer]').forEach((button) => {
            button.addEventListener('click', () => answer(Number(button.dataset.answer)));
        });
        app.querySelector('[data-lifeline="fifty"]').addEventListener('click', useFifty);
        app.querySelector('[data-lifeline="hint"]').addEventListener('click', useHint);
        app.querySelector('[data-lifeline="audience"]').addEventListener('click', useAudience);
        app.querySelector('[data-quit]').addEventListener('click', quitGame);
    }
}

function renderQuestion(question) {
    return `
        ${game.lastExplanation ? `<div class="notice success">Õige. ${escapeHtml(game.lastExplanation)}</div>` : ''}
        <div class="question-card">
            <div class="difficulty">${difficulty(game.current + 1)} - ${formatPoints(points[game.current])} punkti</div>
            <h2>${escapeHtml(question.question)}</h2>
        </div>
        <div class="answers">
            ${question.options.map((option, index) => `
                <button class="answer" type="button" data-answer="${index}" ${game.removedOptions.includes(index) ? 'disabled' : ''}>
                    <span>${letters[index]}</span>
                    ${escapeHtml(option)}
                </button>
            `).join('')}
        </div>
        <div class="lifelines">
            <button type="button" data-lifeline="fifty" ${game.lifelines.fifty ? '' : 'disabled'}>50:50</button>
            <button type="button" data-lifeline="hint" ${game.lifelines.hint ? '' : 'disabled'}>AI vihje</button>
            <button type="button" data-lifeline="audience" ${game.lifelines.audience ? '' : 'disabled'}>Publik</button>
            <button class="secondary" type="button" data-quit>Jäta pooleli</button>
        </div>
        ${game.hint ? `<div class="notice">Vihje: ${escapeHtml(game.hint)}</div>` : ''}
        ${game.audience ? renderAudience() : ''}
    `;
}

function renderResult() {
    const status = {
        won: 'Sa võitsid miljoni.',
        quit: 'Jätsid mängu pooleli.',
        wrong: 'Vastus oli vale.',
    }[game.status];

    return `
        <div class="result">
            <p class="result-status">${status}</p>
            <p>Lõpptulemus: <strong>${formatPoints(game.earned)} punkti</strong></p>
            <p>Mängija: <strong>${escapeHtml(game.player)}</strong></p>
            ${game.lastExplanation ? `<div class="notice">${escapeHtml(game.lastExplanation)}</div>` : ''}
            <button class="primary" type="button" data-action="home">Tagasi avalehele</button>
        </div>
    `;
}

function answer(index) {
    const question = game.questions[game.current];
    if (index !== question.correctIndex) {
        game.earned = game.safe;
        game.finished = true;
        game.status = 'wrong';
        game.lastExplanation = `Õige vastus oli ${letters[question.correctIndex]}. ${question.explanation}`;
        renderGame();
        return;
    }

    const level = game.current + 1;
    game.earned = points[game.current];
    if (safeLevels[level]) {
        game.safe = safeLevels[level];
    }
    game.lastExplanation = question.explanation;

    if (level === 15) {
        game.finished = true;
        game.status = 'won';
    } else {
        game.current += 1;
        game.removedOptions = [];
        game.hint = null;
        game.audience = null;
    }

    renderGame();
}

function quitGame() {
    game.finished = true;
    game.status = 'quit';
    renderGame();
}

function useFifty() {
    const question = game.questions[game.current];
    const wrong = [0, 1, 2, 3].filter((index) => index !== question.correctIndex);
    game.removedOptions = takeRandom(wrong, 2);
    game.lifelines.fifty = false;
    renderGame();
}

function useHint() {
    game.hint = game.questions[game.current].hint;
    game.lifelines.hint = false;
    renderGame();
}

function useAudience() {
    const question = game.questions[game.current];
    const correctShare = game.current < 5 ? randomInt(55, 78) : game.current < 10 ? randomInt(42, 66) : randomInt(30, 56);
    const shares = [0, 0, 0, 0];
    shares[question.correctIndex] = correctShare;
    let remaining = 100 - correctShare;
    const wrong = shuffle([0, 1, 2, 3].filter((index) => index !== question.correctIndex));
    wrong.forEach((index, position) => {
        if (position === wrong.length - 1) {
            shares[index] = remaining;
        } else {
            const share = randomInt(3, Math.max(3, remaining - 6));
            shares[index] = share;
            remaining -= share;
        }
    });
    game.audience = shares;
    game.lifelines.audience = false;
    renderGame();
}

function renderAudience() {
    return `<div class="audience">${game.audience.map((share, index) => `
        <div><span>${letters[index]} - ${share}%</span><meter min="0" max="100" value="${share}"></meter></div>
    `).join('')}</div>`;
}

function renderLadder() {
    return points.map((point, index) => ({point, level: index + 1})).reverse().map(({point, level}) => `
        <div class="ladder-row ${level === game.current + 1 && !game.finished ? 'active' : ''} ${safeLevels[level] ? 'safe' : ''}">
            <span>${level}</span>
            <strong>${formatPoints(point)}</strong>
        </div>
    `).join('');
}

function renderLeaderboard(rows) {
    return `
        <section class="panel">
            <p class="eyebrow">Edetabel</p>
            <h2>Parimad tulemused</h2>
            ${rows.length === 0 ? '<p class="muted">Edetabel on veel tühi.</p>' : `
                <div class="leaderboard">
                    ${rows.map((row, index) => `
                        <div class="leaderboard-row">
                            <span class="rank">${index + 1}</span>
                            <strong>${escapeHtml(row.player)}</strong>
                            <span>${escapeHtml(row.taskTitle)}</span>
                            <span>${formatPoints(row.points)} punkti</span>
                        </div>
                    `).join('')}
                </div>
            `}
        </section>
    `;
}

function saveResult(currentGame) {
    const rows = leaderboard();
    rows.push({
        player: currentGame.player,
        taskId: currentGame.task.id,
        taskTitle: currentGame.task.title,
        points: currentGame.earned,
        status: currentGame.status,
        createdAt: new Date().toISOString(),
    });
    localStorage.setItem('miljonimangLeaderboard', JSON.stringify(rows));
}

function leaderboard() {
    const rows = JSON.parse(localStorage.getItem('miljonimangLeaderboard') || '[]');
    return rows.sort((a, b) => b.points - a.points || b.createdAt.localeCompare(a.createdAt)).slice(0, 10);
}

function takeRandom(items, count) {
    return shuffle([...items]).slice(0, count);
}

function shuffleQuestion(question) {
    const correct = question.options[question.correctIndex];
    question.options = shuffle([...question.options]);
    question.correctIndex = question.options.indexOf(correct);
    return question;
}

function shuffle(items) {
    for (let index = items.length - 1; index > 0; index -= 1) {
        const target = Math.floor(Math.random() * (index + 1));
        [items[index], items[target]] = [items[target], items[index]];
    }
    return items;
}

function combinedQuestions() {
    return bank.tasks.reduce((all, task) => {
        all.easy.push(...task.questions.easy);
        all.medium.push(...task.questions.medium);
        all.hard.push(...task.questions.hard);
        return all;
    }, {easy: [], medium: [], hard: []});
}

function questionCount() {
    const questions = combinedQuestions();
    return questions.easy.length + questions.medium.length + questions.hard.length;
}

function difficulty(level) {
    if (level <= 5) {
        return 'easy';
    }
    if (level <= 10) {
        return 'medium';
    }
    return 'hard';
}

function formatPoints(value) {
    return new Intl.NumberFormat('et-EE').format(value);
}

function randomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}
