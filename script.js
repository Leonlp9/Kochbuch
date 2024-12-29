document.querySelectorAll('.number-input').forEach((element) => {
    element.querySelector('.down').addEventListener('click', function() {
        element.querySelector('input[type=number]').stepDown()
        element.querySelector('input[type=number]').dispatchEvent(new Event('change'));
    });
    element.querySelector('.up').addEventListener('click', function() {
        element.querySelector('input[type=number]').stepUp()
        element.querySelector('input[type=number]').dispatchEvent(new Event('change'));
    });
    element.querySelector('input[type=number]').addEventListener('change', function() {
        if (this.value > parseInt(this.max)) {
            this.value = this.max;
            this.dispatchEvent(new Event('change'));
        }
        if (this.value < parseInt(this.min)) {
            this.value = this.min;
            this.dispatchEvent(new Event('change'));
        }
    });
});

document.querySelectorAll('.horizontalScrollBarJS').forEach((element) => {
    let scrollAmount = 0;
    let isScrolling = false;
    let isDragging = false;
    let startX;
    let scrollLeft;

    element.addEventListener('wheel', function(event) {
        if (event.deltaY !== 0) {
            event.preventDefault();
            scrollAmount += event.deltaY;
            if (!isScrolling) {
                isScrolling = true;
                requestAnimationFrame(smoothScroll);
            }
        }
    });

    element.addEventListener('mousedown', (event) => {
        isDragging = true;
        startX = event.pageX - element.offsetLeft;
        scrollLeft = element.scrollLeft;
        element.classList.add('active');
    });

    element.addEventListener('mouseleave', () => {
        isDragging = false;
        element.classList.remove('active');
    });

    element.addEventListener('mouseup', () => {
        isDragging = false;
        element.classList.remove('active');
    });

    element.addEventListener('mousemove', (event) => {
        if (!isDragging) return;
        event.preventDefault();
        const x = event.pageX - element.offsetLeft;
        const walk = (x - startX) * 1.5; // The multiplier can be adjusted for sensitivity
        element.scrollLeft = scrollLeft - walk;
    });

    function smoothScroll() {
        if (scrollAmount !== 0) {
            const scrollStep = scrollAmount / 10;
            element.scrollBy({
                left: scrollStep,
                behavior: 'auto'
            });
            scrollAmount -= scrollStep;
            if (Math.abs(scrollAmount) < 1) {
                scrollAmount = 0;
            }
            requestAnimationFrame(smoothScroll);
        } else {
            isScrolling = false;
        }
    }
});

document.documentElement.setAttribute('theme', "pink-rounder");

async function generateResponse(prompt) {
    try {
        const url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=";

        const jsonInput = JSON.stringify({
            contents: [
                {
                    role: "user",
                    parts: [
                        { text: prompt }
                    ]
                }
            ],
            systemInstruction: {
                role: "user",
                parts: [
                    {
                        text: "Dein Name ist CookMate, du bist ein Unterstützer für eine Rezepte-Kochwebseite. Antworte nur auf die Fragen, die etwas mit Zutaten, Rezepten oder Kochen zu tun haben. Wenn die Frage nicht zu deinem Fachgebiet passt, antworte mit 'Das liegt nicht in meinem Fachgebiet, oder gebe mir mehr Informationen'. Aber auf Höflichkeitsfragen darfst du antworten. Der Entwickler der Seite ist Leon Rabe, wenn der benutzer Hilfe bezüglich der Webseite benötigt, soll er sich an ihn wenden. Schreib das aber nur, wenn du dir sicher bist, dass es eine Frage zu der Webseite ist, also das ausdrücklich erwähnt wird."
                    }
                ]
            },
            generationConfig: {
                temperature: 1,
                topK: 40,
                topP: 0.95,
                maxOutputTokens: 8192,
                responseMimeType: "text/plain"
            }
        });

        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: jsonInput
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const jsonResponse = await response.json();

        return jsonResponse.candidates[0].content.parts[0].text;
    } catch (error) {
        console.error(error);
        return `Error: ${error.message}`;
    }
}
class FormBuilder {
    constructor(title, onSubmit, onCancel) {
        this.background = document.createElement('div');
        this.container = document.createElement('div');

        this.fields = [];
        this.title = title;
        this.onSubmit = onSubmit;
        this.onCancel = onCancel;

        this.background.className = 'form-background';
        this.container.className = 'form-container';

        this.background.appendChild(this.container);
        document.body.appendChild(this.background);
    }

    addInputField(id, placeholder, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'input', placeholder, startValue, onChange });
    }

    addColorField(id, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'color', startValue, onChange });
    }

    addCheckbox(id, label, checked, onChange = () => {}) {
        this.fields.push({ id, type: 'checkbox', label, checked, onChange });
    }

    addNumberField(id, min, max, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'number', min, max, startValue, onChange });
    }

    addRangeField(id, min, max, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'range', min, max, startValue, onChange });
    }

    addFileField(id, accept, onChange = () => {}) {
        this.fields.push({ id, type: 'file', accept, onChange });
    }

    renderForm(showButtons = true) {
        // Clear previous content
        this.container.innerHTML = '';
        const form = document.createElement('form');

        // Add title
        const title = document.createElement('h2');
        title.textContent = this.title;
        form.appendChild(title);

        // Generate fields
        this.fields.forEach((field) => {
            let element;
            switch (field.type) {
                case 'input':
                    element = document.createElement('input');
                    element.type = 'text';
                    element.id = field.id;
                    element.placeholder = field.placeholder;
                    element.value = field.startValue || '';
                    break;
                case 'color':
                    element = document.createElement('input');
                    element.type = 'color';
                    element.id = field.id;
                    element.value = field.startValue || '#000000';
                    break;
                case 'checkbox':
                    element = document.createElement('label');
                    element.htmlFor = field.id;
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = field.id;
                    checkbox.checked = field.checked || false;
                    checkbox.addEventListener('change', (e) => field.onChange(e.target.checked));
                    element.appendChild(checkbox);
                    element.appendChild(document.createTextNode(field.label));
                    break;
                case 'number':
                    element = document.createElement('input');
                    element.type = 'number';
                    element.id = field.id;
                    element.min = field.min;
                    element.max = field.max;
                    element.value = field.startValue || field.min;
                    break;
                case 'range':
                    element = document.createElement('input');
                    element.type = 'range';
                    element.id = field.id;
                    element.min = field.min;
                    element.max = field.max;
                    element.value = field.startValue || field.min;
                    break;
                case 'file':
                    element = document.createElement('input');
                    element.type = 'file';
                    element.id = field.id;
                    element.accept = field.accept;
                    element.addEventListener('change', (e) => field.onChange(e.target.files[0]));
                    break;
            }

            if (field.type !== 'checkbox') {
                element.addEventListener('input', (e) => field.onChange(e.target.value));
            }
            form.appendChild(element);
            form.appendChild(document.createElement('br'));
        });

        // Add buttons
        if (showButtons) {
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'button-container';

            const saveButton = document.createElement('button');
            saveButton.type = 'button';
            saveButton.textContent = 'Speichern';
            saveButton.addEventListener('click', () => {
                const formData = {};
                this.fields.forEach((field) => {
                    const input = form.querySelector(`#${field.id}`);
                    if (field.type === 'checkbox') {
                        formData[field.id] = input.checked;
                    } else {
                        formData[field.id] = input.value;
                    }
                });
                this.onSubmit(formData);

                this.container.style.animation = 'form-container-out-animation 0.25s forwards';
                this.background.style.animation = 'form-background-out-animation 0.25s forwards ease';

                setTimeout(() => {
                    document.body.removeChild(this.background);
                }, 500);

                delete this;
            });

            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.textContent = 'Abbrechen';
            cancelButton.addEventListener('click', () => {
                this.onCancel();

                this.container.style.animation = 'form-container-out-animation 0.25s forwards';
                this.background.style.animation = 'form-background-out-animation 0.25s forwards ease';

                setTimeout(() => {
                    document.body.removeChild(this.background);
                }, 500);

                delete this;
            });
            buttonContainer.appendChild(saveButton);
            buttonContainer.appendChild(cancelButton);
            form.appendChild(buttonContainer);
        }

        this.background.addEventListener('click', (e) => {
            if (e.target === this.background) {
                this.container.style.animation = 'form-container-out-animation 0.25s forwards';
                this.background.style.animation = 'form-background-out-animation 0.25s forwards ease';

                setTimeout(() => {
                    document.body.removeChild(this.background);
                }, 500);

                delete this;
            }
        });

        this.container.appendChild(form);
    }
}

/*

let builder = new FormBuilder("GHG", (data) => {
    console.log('Formular gesendet:', data);
}, () => {
    console.log('Abbrechen gedrückt');
});

document.addEventListener('DOMContentLoaded', () => {
    builder.addInputField('name', 'Name eingeben', '', (value) => console.log('Name geändert:', value));
    builder.addNumberField('age', 0, 100, 18, (value) => console.log('Alter geändert:', value));
    builder.renderForm();
});

*/
