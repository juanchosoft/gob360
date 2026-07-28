<?php
// chatgpt_modal.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = $_POST['message'];

    $apiUrl = 'https://api.openai.com/v1/chat/completions';
    $apiKey = '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';

    $data = [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'Actúa si fueras el gobernador de santander colombia y sepas todo el plan de desarrollo y la informacion detallada de cada uno de sus municipios y veredas .'],
            ['role' => 'user', 'content' => '¿Como puedo saber los problemas mas detallados en el plan de gobierno de santander, segun el plan de gobierno 2024-2027 es tiempo de santander?'],
            ['role' => 'assistant', 'content' => 'Su administración se centra en tres ejes principales:
                Seguridad Multidimensional: Implementar estrategias que aborden desde la delincuencia hasta aspectos sociales que afectan la estabilidad del departamento. 
                Prosperidad: Fomentar el desarrollo económico, incluyendo la transformación del Instituto Financiero para el Desarrollo de Santander (Idesan) en un banco que canalice recursos para alcaldías y ciudadanos, y la reactivación de la producción de licores para generar utilidades. 
                Sostenibilidad: Promover proyectos que impulsen el crecimiento y desarrollo sostenible de Santander'],
                 
            
            ['role' => 'user', 'content' => $userInput]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: application/json');
    echo $response;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatGPT Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }
        .modal-header, .modal-footer {
            background-color: #343a40;
            color: white;
        }
        .chat-box {
            height: 300px;
            overflow-y: auto;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }
        
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">ChatGPT Integration</h1>
    <div class="text-center mt-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#chatModal">Open Chat</button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatModalLabel">ChatGPT Assistant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="chat-box" id="chatBox"></div>
                <form id="chatForm" class="mt-3">
                    <div class="input-group">
                        <input type="text" id="userMessage" class="form-control" placeholder="Type your message here..." required>
                        <button type="submit" class="btn btn-secondary">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('chatForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const messageBox = document.getElementById('chatBox');
        const userMessage = document.getElementById('userMessage').value;

        // Append user message
        const userBubble = document.createElement('div');
        userBubble.textContent = userMessage;
        userBubble.className = 'text-end text-primary mb-2';
        messageBox.appendChild(userBubble);

        // Clear input
        document.getElementById('userMessage').value = '';

        // Send message to the server
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ message: userMessage })
        });

        const data = await response.json();
        const reply = data.choices[0].message.content;

        // Append assistant reply
        const assistantBubble = document.createElement('div');
        assistantBubble.textContent = reply;
        assistantBubble.className = 'text-start text-success mb-2';
        messageBox.appendChild(assistantBubble);

        // Scroll to bottom
        messageBox.scrollTop = messageBox.scrollHeight;
    });
</script>
</body>
</html>
