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