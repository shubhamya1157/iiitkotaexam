const questions = document.querySelector('#questions');
const template = document.querySelector('#question-template');
const add = document.querySelector('#add');
const remove = document.querySelector('#remove');

function updateButtons() {
    add.disabled = questions.children.length >= 30;
    remove.disabled = questions.children.length <= 1;
}

add.addEventListener('click', () => {
    const index = questions.children.length;
    const fields = template.content.cloneNode(true);
    fields.querySelector('legend').textContent = 'Question ' + (index + 1);
    fields.querySelectorAll('[name]').forEach(field => {
        field.name = field.name.replace('questions[0]', 'questions[' + index + ']');
    });
    questions.append(fields);
    questions.lastElementChild.querySelector('textarea').focus();
    updateButtons();
});

remove.addEventListener('click', () => {
    questions.lastElementChild.remove();
    updateButtons();
});

updateButtons();
