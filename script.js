
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.number-input').forEach((element) => {
        element.querySelector('.down').addEventListener('click', function () {
            element.querySelector('input[type=number]').stepDown()
            element.querySelector('input[type=number]').dispatchEvent(new Event('change'));
        });
        element.querySelector('.up').addEventListener('click', function () {
            element.querySelector('input[type=number]').stepUp()
            element.querySelector('input[type=number]').dispatchEvent(new Event('change'));
        });
        element.querySelector('input[type=number]').addEventListener('change', function () {
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

        element.addEventListener('wheel', function (event) {
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
});


class FormBuilder {
    form;

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

    addCustomNumberField(id, min, max, step, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'custom-number', min, max, step, startValue, onChange });
    }

    addRangeField(id, min, max, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'range', min, max, startValue, onChange });
    }

    addFileField(id, accept, onChange = () => {}) {
        this.fields.push({ id, type: 'file', accept, onChange });
    }

    addSelectField(id, options, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'select', options, startValue, onChange });
    }

    addHeader(Name) {
        this.fields.push({ type: 'header', Name });
    }

    addHTML(html) {
        this.fields.push({ type: 'html', html });
    }

    addButton(text, onKlick = () => {}) {
        this.fields.push({ type: 'button', text, onKlick });
    }

    addDateInput(id, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'date', startValue, onChange });
    }

    addQuillField(id, placeholder, startValue, onChange = () => {}) {
        this.fields.push({ id, type: 'quill', placeholder, startValue, onChange });
    }

    closeForm() {
        this.onCancel();

        this.background.style.pointerEvents = 'none';

        this.container.style.animation = 'form-container-out-animation 0.25s forwards';
        this.background.style.animation = 'form-background-out-animation 0.25s forwards ease';

        setTimeout(() => {
            document.body.removeChild(this.background);
        }, 500);

        delete this;
    }

    fucus(id) {
        document.getElementById(id).focus();
    }

    select(menge) {
        document.getElementById(menge).select();
    }

    submitForm() {
        const formData = {};
        this.fields.forEach((field) => {
            if (field.type === 'header' || field.type === 'html') return;

            const input = this.form.querySelector(`#${field.id}`);


            if (field.type === 'button') {
            } else if (field.type === 'color') {
                formData[field.id] = input.value;
            } else if (field.type === 'checkbox') {
                formData[field.id] = input.checked;
            } else if (field.type === 'file') {
                formData[field.id] = input.files[0];
            } else if (field.type === 'quill') {
                formData[field.id] = "\"" + field.quill.root.innerHTML + "\"";
            } else {
                formData[field.id] = input.value;
            }
        });
        this.onSubmit(formData);
        this.background.style.pointerEvents = 'none';

        this.container.style.animation = 'form-container-out-animation 0.25s forwards';
        this.background.style.animation = 'form-background-out-animation 0.25s forwards ease';

        setTimeout(() => {
            document.body.removeChild(this.background);
        }, 500);

        delete this;
    }

    renderForm(showButtons = true) {
        // Clear previous content
        this.container.innerHTML = '';
        const form = document.createElement('form');

        // Add title
        const title = document.createElement('h2');
        title.textContent = this.title;
        form.appendChild(title);
        this.container.appendChild(form);
        this.form = form;

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
                    element.autocomplete = 'off';
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
                case 'header':
                    element = document.createElement('h3');
                    element.textContent = field.Name;
                    break;
                case 'html':
                    element = document.createElement('div');
                    element.innerHTML = field.html;
                    break;
                case 'select':
                    element = document.createElement('select');
                    element.id = field.id;
                    field.options.forEach((option) => {
                        const optionElement = document.createElement('option');
                        optionElement.value = option.value;
                        optionElement.textContent = option.text;
                        element.appendChild(optionElement);
                    });
                    element.value = field.startValue || field.options[0].value;
                    element.addEventListener('change', (e) => field.onChange(e.target.value));
                    break;
                case 'button':
                    element = document.createElement('button');
                    element.type = 'button';
                    element.textContent = field.text;
                    element.addEventListener('click', field.onKlick);
                    break;
                case 'date':
                    element = document.createElement('input');
                    element.type = 'date';
                    element.id = field.id;
                    element.value = field.startValue || new Date().toISOString().split('T')[0];
                    break;
                case 'quill':
                    element = document.createElement('div');
                    element.id = field.id;
                    break;
                case 'custom-number':

                    element = document.createElement('div');
                    element.className = 'number-input';

                    const downButton = document.createElement('button');
                    downButton.type = 'button';
                    downButton.className = 'down';
                    downButton.innerHTML = '<i class="fas fa-minus"></i>';
                    downButton.addEventListener('click', () => {
                        const input = element.querySelector('input[type=number]');
                        input.stepDown();
                        input.dispatchEvent(new Event('change'));
                    });
                    element.appendChild(downButton);

                    const input = document.createElement('input');
                    input.type = 'number';
                    input.id = field.id;
                    input.min = field.min;
                    input.max = field.max;
                    input.step = field.step;
                    input.value = field.startValue || field.min;
                    input.addEventListener('change', (e) => field.onChange(e.target.value));
                    element.appendChild(input);

                    const upButton = document.createElement('button');
                    upButton.type = 'button';
                    upButton.className = 'up';
                    upButton.innerHTML = '<i class="fas fa-plus"></i>';
                    upButton.addEventListener('click', () => {
                        const input = element.querySelector('input[type=number]');
                        input.stepUp();
                        input.dispatchEvent(new Event('change'));
                    });
                    element.appendChild(upButton);

                    break;
                default:
                    console.error(`Unknown field type: ${field.type}`);
            }

            if (field.type !== 'checkbox') {
                element.addEventListener('input', (e) => field.onChange(e.target.value));
            }
            form.appendChild(element);
            form.appendChild(document.createElement('br'));
        });

        //register quill
        this.fields.forEach((field) => {
            if (field.type === 'quill') {
                var settings = {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'blockquote'],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            ['link'],
                            ['clean']
                        ]
                    },
                    theme: 'snow'
                };

                const quill = new Quill(`#${field.id}`, {
                    theme: 'snow',
                    placeholder: field.placeholder,
                    modules: {
                        toolbar: settings.modules.toolbar
                    },
                });

                quill.root.innerHTML = field.startValue || '';

                quill.root.addEventListener('input', (e) => field.onChange(quill.root.innerHTML));

                field.quill = quill;
            }
        });

        // Add buttons
        if (showButtons) {
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'button-container';

            const saveButton = document.createElement('button');
            saveButton.type = 'button';
            saveButton.textContent = 'Speichern';
            saveButton.addEventListener('click', () => {
                this.submitForm();
            });

            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.textContent = 'Abbrechen';
            cancelButton.addEventListener('click', () => {
                this.closeForm();
            });
            buttonContainer.appendChild(saveButton);
            buttonContainer.appendChild(cancelButton);
            form.appendChild(buttonContainer);
        }

        this.background.addEventListener('click', (e) => {
            if (e.target === this.background) {
                this.background.style.pointerEvents = 'none';
                this.container.style.animation = 'form-container-out-animation 0.25s forwards';
                this.background.style.animation = 'form-background-out-animation 0.25s forwards ease';

                setTimeout(() => {
                    document.body.removeChild(this.background);
                }, 500);

                delete this;
            }
        });

    }
}
class SystemMessage {
    constructor(text) {
        this.text = text;
        this.duration = 5000;
    }

    setDuration(duration) {
        this.duration = duration;
    }

    show() {

        //wenn system-messages noch nicht existieren, erstellen
        let systemMessages = document.querySelector('.system-messages');
        if (!systemMessages) {
            systemMessages = document.createElement('div');
            systemMessages.className = 'system-messages';
            document.body.appendChild(systemMessages);
        }

        const message = document.createElement('div');
        message.className = 'system-message';

        const text = document.createElement('p');
        text.textContent = this.text;
        message.appendChild(text);

        const timebar = document.createElement('div');
        timebar.className = 'timebar';
        message.appendChild(timebar);
        timebar.style.animation = `timebar-animation ${this.duration}ms linear forwards`;

        systemMessages.appendChild(message);

        setTimeout(() => {
            message.style.animation = 'system-message-out-animation 0.25s forwards';
            setTimeout(() => {
                systemMessages.removeChild(message);
            }, 500);
        }, this.duration);
    }

}
class KiChat {
    constructor() {
        this.messages = [];
        this.kontextParts = [];
        this.timestamp = new Date().getTime();

        this.container = document.createElement('div');
        this.container.className = 'ki-chat';
        this.container.classList.add('no-print');

        this.header = document.createElement('div');
        this.header.className = 'ki-chat-header';

        this.headerChatsSelect = document.createElement('select');
        this.headerChatsSelect.className = 'ki-chat-header-chats-select';

        this.updateChatsSelect();

        this.headerChatsSelect.addEventListener('change', (e) => {
            if (e.target.value === 'new') {
                this.messages = [];
                this.kontextParts = [];
                this.timestamp = new Date().getTime();
                this.renderMessages();
                return;
            }

            const chats = JSON.parse(localStorage.getItem('chats'));
            const chat = chats[e.target.value];
            this.messages = chat.messages;
            this.kontextParts = chat.kontextParts;
            this.timestamp = e.target.value;
            this.renderMessages();
        });

        this.header.appendChild(this.headerChatsSelect);

        const headerClosed = document.createElement('div');
        headerClosed.className = 'ki-chat-header-closed';
        this.header.appendChild(headerClosed);



        this.headerClose = document.createElement('i');
        this.headerClose.className = 'ki-chat-header-close';
        this.headerClose.classList.add('fas', 'fa-robot');
        this.headerClose.style.fontSize = '1.5em';

        this.headerClose.addEventListener('click', () => {
            if (this.container.classList.contains('open')) {
                const editChatForm = new FormBuilder('Chat bearbeiten', (formData) => {}, () => {});

                //löschen button
                editChatForm.addButton('Löschen', () => {
                    const chats = JSON.parse(localStorage.getItem('chats'));
                    delete chats[this.timestamp];
                    localStorage.setItem('chats', JSON.stringify(chats));
                    this.messages = [];
                    this.kontextParts = [];
                    this.updateChatsSelect();
                    editChatForm.closeForm();
                    this.renderMessages();
                });

                editChatForm.renderForm(false);
            }
        });

        this.container.appendChild(this.header);
        this.header.appendChild(this.headerClose);

        this.chat = document.createElement('div');
        this.chat.className = 'ki-chat-chat';
        this.container.appendChild(this.chat);
        this.chat.scrollTop = this.chat.scrollHeight;

        this.chatInput = document.createElement('div');
        this.chatInput.className = 'ki-chat-input';
        this.container.appendChild(this.chatInput);

        this.input = document.createElement('textarea');
        this.input.placeholder = 'Nachricht';
        this.chatInput.appendChild(this.input);

        this.send = document.createElement('button');
        this.send.textContent = 'Senden';
        this.chatInput.appendChild(this.send);

        this.send.addEventListener('click', () => {
            this.sendMessage(this.input.value);
        });

        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage(this.input.value);
            }
        });

        this.container.addEventListener('click', () => {
            if (!this.container.classList.contains('open')) {
                this.container.classList.add('open');
                this.container.style.width = 'min(calc(100% - 20px), 500px)';
                setTimeout(() => {
                    this.container.style.height = 'min(calc(100% - 150px), 700px)';
                }, 100);

                setTimeout(() => {
                    this.input.focus();
                }, 500);
            }
        });

        //wenn ausserhalb des chat-bereichs geklickt wird, wird der chat geschlossen
        document.addEventListener('click', (e) => {
            if (e.target !== this.container && !this.container.contains(e.target)) {

                //wenn in einem formular geklickt wird, wird der chat nicht geschlossen
                if (document.querySelector('.form-background')) return;

                this.container.style.width = '65px';
                setTimeout(() => {
                    this.container.style.height = '65px';
                    this.container.classList.remove('open');
                }, 100);
            }
        });

        document.body.appendChild(this.container);

        this.renderMessages();

    }

    updateChatsSelect() {
        this.headerChatsSelect.innerHTML = '';

        const newChatOption = document.createElement('option');
        newChatOption.value = 'new';
        newChatOption.textContent = 'Neuer Chat';
        this.headerChatsSelect.appendChild(newChatOption);

        const chats = JSON.parse(localStorage.getItem('chats')) || {};
        Object.keys(chats).forEach((timestamp) => {
            const chat = chats[timestamp];
            const option = document.createElement('option');
            option.value = timestamp;
            option.textContent = new Date(parseInt(timestamp)).toLocaleString('de-DE', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'numeric', year: 'numeric' }).split(', ').reverse().join(', ');

            //füge dem textContent die ersten 100 zeichen des ersten parts hinzu
            if (chat.messages[0].parts[0].text.length > 30) {
                option.textContent += ': ' + chat.messages[0].parts[0].text.substring(0, 30) + '...';
            } else {
                option.textContent += ': ' + chat.messages[0].parts[0].text;
            }

            this.headerChatsSelect.appendChild(option);
        });

        //wenn es kein chat mit dem timestamp gibt, wird der new chat ausgewählt, sonst der letzte chat
        if (!chats[this.timestamp]) {
            this.headerChatsSelect.value = 'new';
        } else {
            this.headerChatsSelect.value = this.timestamp;
        }
    }

    saveToStorage() {
        //in localstorage bei chats array den neuen chat speichern mit timestamp als key und value mit messages und kontextParts
        let chats = JSON.parse(localStorage.getItem('chats')) || {};
        chats[this.timestamp] = {messages: this.messages, kontextParts: this.kontextParts};
        localStorage.setItem('chats', JSON.stringify(chats));
    }

    async sendMessage(prompt) {

        if (prompt === '') return;

        this.input.value = '';
        //close keyboard on mobile
        this.input.blur();

        this.messages.push({
            role: 'user',
            parts: [
                { text: prompt }
            ]
        });

        this.renderMessages();

        this.showTypingIndicator();

        const response = await this.generateResponse(this.messages);

        this.messages.push({
            role: 'model',
            parts: [
                { text: response }
            ]
        });

        this.renderMessages();

        this.saveToStorage();
        this.updateChatsSelect();

    }

    renderMessages() {
        this.chat.innerHTML = '';

        //add start message "Hallo, ich bin CookMate, dein persönlicher Rezept-Assistent. Wie kann ich dir helfen?"
        this.addRenderedMessage([{ text: "Hallo, ich bin CookMate, dein persönlicher Rezept-Assistent. Wie kann ich dir helfen?" }], 'model');

        this.messages.forEach((message) => {
            this.addRenderedMessage(message.parts, message.role);
        });

    }

    addRenderedMessage(messages, role) {
        const messageElement = document.createElement('div');
        messageElement.className = `ki-chat-message ki-chat-message-${role}`;

        messages.forEach((message) => {
            const partElement = document.createElement('p');
            partElement.innerHTML = marked.parse(message.text);
            messageElement.appendChild(partElement);
        });

        this.chat.appendChild(messageElement);
        this.chat.scrollTop = this.chat.scrollHeight;
    }

    addKontext(kontext) {
        if (kontext.name === 'Rezept') {

            let response = "'" + kontext.value.Name + "' ist für " + kontext.value.Portionen + " Portionen und dauert " + kontext.value.Zeit + " Minuten. Die Zutaten sind: ";
            kontext.value.Zutaten_JSON.forEach((zutat) => {
                response += zutat.Menge + " " + zutat.unit + " " + zutat.Name + ", ";
            });

            response += "Die Zubereitung ist: " + kontext.value.Zubereitung;

            this.kontextParts.push({
                role: 'model',
                parts: [
                    { text: response }
                ]
            });
        }
        this.renderMessages();
    }

    async generateResponse(prompt) {
        try {
            const url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyBPOErHLPc9r0G2b1_D8PtkjrA9jEkWvI0";

            //bei prompt ganz oben die kontextParts hinzufügen
            prompt = this.kontextParts.concat(prompt);

            const jsonInput = JSON.stringify({
                contents: prompt,
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

            console.log(JSON.parse(jsonInput));

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

    showTypingIndicator() {
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'ki-chat-typing-indicator';

        const dots = document.createElement('div');
        dots.className = 'ki-chat-typing-indicator-dots';
        typingIndicator.appendChild(dots);

        const dot1 = document.createElement('div');
        dot1.className = 'ki-chat-typing-indicator-dot';
        dot1.style.animationDelay = '0s';
        dots.appendChild(dot1);

        const dot2 = document.createElement('div');
        dot2.className = 'ki-chat-typing-indicator-dot';
        dot2.style.animationDelay = '0.20s';
        dots.appendChild(dot2);

        const dot3 = document.createElement('div');
        dot3.className = 'ki-chat-typing-indicator-dot';
        dot3.style.animationDelay = '0.4s';
        dots.appendChild(dot3);

        this.chat.appendChild(typingIndicator);

        this.chat.scrollTop = this.chat.scrollHeight;
    }
}

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

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.setAttribute('theme', localStorage.getItem('theme') || 'light');
});
