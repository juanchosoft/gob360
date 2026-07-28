
let video = document.getElementById("video");
let canvas = document.getElementById("canvas");
let fotoPreview = document.getElementById("foto-preview");
let stream;

function activarCamara() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(s => {
            stream = s;
            video.srcObject = s;
            video.style.display = 'block';
            fotoPreview.style.display = 'none';
            document.getElementById('btnGuardar').style.display = 'none';
        })
        .catch(err => alert("No se pudo activar la cámara"));
}

function tomarFoto() {
    const ctx = canvas.getContext("2d");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    fotoPreview.src = canvas.toDataURL("image/jpeg");
    fotoPreview.style.display = "block";
    video.style.display = "none";
    document.getElementById('btnGuardar').style.display = 'inline-block';
}

function guardarFoto() {
    let idDocumento = document.getElementById("id_documento").value;
    canvas.toBlob(blob => {
        let formData = new FormData();
        formData.append("foto", blob, "captura.jpg");
        formData.append("id_documento", idDocumento);

        fetch("guardar_foto.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                alert("✅ Foto guardada correctamente.");
                activarCamara(); // continuar tomando fotos
            } else {
                alert("❌ Error: " + data.message);
            }
        })
        .catch(err => alert("Error al enviar la foto"));
    }, "image/jpeg");
}

