const questions = document.querySelector('#questions');
const template = document.querySelector('#question-template');
const add = document.querySelector('#add');
const remove = document.querySelector('#remove');

function updateButtons() {
    add.disabled = questions.children.length >= 30;
    remove.disabled = questions.children.length <= 1;
}

add.onclick = function () {
    const index = questions.children.length;
    const fields = template.content.cloneNode(true);
    fields.querySelector('legend').textContent = 'Question ' + (index + 1);
    const inputs = fields.querySelectorAll('[name]');
    for (let i = 0; i < inputs.length; i++) {
        inputs[i].name = inputs[i].name.replace('questions[0]', 'questions[' + index + ']');
    }
    questions.append(fields);
    questions.lastElementChild.querySelector('textarea').focus();
    updateButtons();
};

remove.onclick = function () {
    questions.lastElementChild.remove();
    updateButtons();
};

updateButtons();
