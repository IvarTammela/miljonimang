const firstInput = document.querySelector('#first');
const secondInput = document.querySelector('#second');
const operatorInput = document.querySelector('#operator');
const result = document.querySelector('#result');

document.querySelector('#calculate').addEventListener('click', () => {
    const first = Number(firstInput.value);
    const second = Number(secondInput.value);
    const operator = operatorInput.value;

    if (Number.isNaN(first) || Number.isNaN(second)) {
        result.textContent = 'Sisesta mõlemad arvud.';
        return;
    }

    if (operator === 'divide' && second === 0) {
        result.textContent = 'Nulliga jagada ei saa.';
        return;
    }

    const answer = {
        add: first + second,
        subtract: first - second,
        multiply: first * second,
        divide: first / second,
    }[operator];

    result.textContent = `Tulemus: ${answer}`;
});
