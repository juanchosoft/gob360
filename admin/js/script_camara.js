const tieneSoporteUserMedia = () =>
    !!(navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia);
const _getUserMedia = (...arguments) =>
    (navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia).apply(navigator, arguments);

const $video = document.querySelector("#video"),
    $canvas = document.querySelector("#canvas"),
    $estado = document.querySelector("#estadoFotosTomada"),
    $boton = document.querySelector("#boton"),
    $listaDeDispositivos = document.querySelector("#listaDeDispositivos"),
    $contenedorFotos = document.querySelector("#contenedorFotosTomadas");

let fotosTomadas = [];

const limpiarSelect = () => {
    for (let x = $listaDeDispositivos.options.length - 1; x >= 0; x--) {
        $listaDeDispositivos.remove(x);
    }
};

const obtenerDispositivos = () => navigator.mediaDevices.enumerateDevices();

const llenarSelectConDispositivosDisponibles = () => {
    limpiarSelect();
    obtenerDispositivos().then(dispositivos => {
        dispositivos.filter(d => d.kind === "videoinput").forEach(dispositivo => {
            const option = document.createElement('option');
            option.value = dispositivo.deviceId;
            option.text = dispositivo.label;
            $listaDeDispositivos.appendChild(option);
        });
    });
};

(function () {
    if (!tieneSoporteUserMedia()) {
        alert("Tu navegador no soporta esta característica");
        $estado.innerHTML = "Actualiza tu navegador para usar esta función.";
        return;
    }

    let stream;

    const mostrarStream = idDispositivo => {
        _getUserMedia({
            video: { deviceId: idDispositivo }
        }, (streamObtenido) => {
            llenarSelectConDispositivosDisponibles();
            $listaDeDispositivos.onchange = () => {
                if (stream) stream.getTracks().forEach(track => track.stop());
                mostrarStream($listaDeDispositivos.value);
            };
            stream = streamObtenido;
            $video.srcObject = stream;
            $video.play();

            $boton.addEventListener("click", () => {
                $video.pause();
                const contexto = $canvas.getContext("2d");
                $canvas.width = 900;
                $canvas.height = 600;
                contexto.drawImage($video, 0, 0, $canvas.width, $canvas.height);

                const foto = $canvas.toDataURL();
                $estado.innerHTML = "Enviando foto...";

                fetch("guardar_foto.php", {
                    method: "POST",
                    body: encodeURIComponent(foto),
                    headers: {
                        "Content-type": "application/x-www-form-urlencoded",
                    }
                })
                .then(r => r.text())
                .then(nombre => {
                    const route = "./assets/img/pae";
                    const url = `${route}/${nombre}`;
                    $estado.innerHTML = `Foto tomada con éxito. Puedes verla <a target='_blank' href='${url}'> <font color="#9900FF"> aquí </font></a>`;
                    $("#foto").val(nombre);

                    // Agregar al array y renderizar
                    fotosTomadas.push({ nombre, url });
                    renderizarFotos();
                });

                $video.play();
            });
        }, (error) => {
            console.log("Permiso denegado o error: ", error);
            $estado.innerHTML = "No se puede acceder a la cámara.";
        });
    };

    obtenerDispositivos().then(dispositivos => {
        const dispositivosDeVideo = dispositivos.filter(d => d.kind === "videoinput");
        if (dispositivosDeVideo.length > 0) {
            mostrarStream(dispositivosDeVideo[0].deviceId);
        }
    });
})();

function renderizarFotos() {
    $contenedorFotos.innerHTML = "";

    fotosTomadas.forEach((foto, index) => {
        const div = document.createElement("div");
        div.className = "foto-tomada";
        div.innerHTML = `
            <img src="${foto.url}" width="150" style="margin:5px;display:block;" />
            <a target="_blank" href="${foto.url}">Ver imagen</a>
            <button data-index="${index}" class="eliminar-foto">Eliminar</button>
        `;
        $contenedorFotos.appendChild(div);
    });

    document.querySelectorAll(".eliminar-foto").forEach(btn => {
        btn.addEventListener("click", function () {
            const index = parseInt(this.dataset.index);
            fotosTomadas.splice(index, 1);
            renderizarFotos();
        });
    });
}

var VALIDACIONFOTO = {
    opcionDeFoto: function () {
        const isTomarFoto = $("#optionImagen").val() === "TomarFoto";
        $("#divSubirImagen").toggle(!isTomarFoto);
        $("#divTomarFoto").toggle(isTomarFoto);
    }
};
// Mostrar u ocultar el contenedor según el radio seleccionado
$(document).ready(function () {
    $("input[name='radio_select']").change(function () {
        if ($("#radiotfoto").is(":checked")) {
            $("#contenedor-video-preview").show();
        } else {
            $("#contenedor-video-preview").hide();
        }
    });

    // Ocultarlo al iniciar por seguridad
    $("#contenedor-video-preview").hide();
});
