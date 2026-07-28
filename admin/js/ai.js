$(document).on('ready', initai);

function initai() {
}
var AI = {

    enviarMensajeAI: async function () { // Agregado "async" aquí

        const form = document.getElementById('chat-form');
        const chatBox = document.getElementById('chat-box');

        // Obtener el mensaje del usuario
        const userInput = document.getElementById('user-input').value;

        // Mostrar el mensaje del usuario en el chat
        const userMessage = document.createElement('div');
        userMessage.classList.add('chat-message', 'user');
        userMessage.textContent = `Tú: ${userInput}`;
        chatBox.appendChild(userMessage);

        // Limpiar el campo de entrada
        document.getElementById('user-input').value = '';

        try {
            // Hacer la solicitud al backend
            const response = await fetch('../chatgpt_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: userInput
                })
            });

            if (!response.ok) {
                throw new Error(`Error en la respuesta: ${response.statusText}`);
            }

            const data = await response.json();

            // Mostrar la respuesta de ChatGPT en el chat
            const botMessage = document.createElement('div');
            botMessage.classList.add('chat-message', 'bot');
            botMessage.textContent = `SANTAI: ${data.response}`;
            chatBox.appendChild(botMessage);

            // Desplazar el chat al final
            chatBox.scrollTop = chatBox.scrollHeight;

        } catch (error) {
            console.error('Error en la solicitud:', error);
            // Manejo de errores de la solicitud
            const errorMessage = document.createElement('div');
            errorMessage.classList.add('chat-message', 'error');
            errorMessage.textContent = `Error: No se pudo obtener la respuesta del servidor.`;
            chatBox.appendChild(errorMessage);
        }
    }
};