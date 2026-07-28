var localstream, canvas, video, cxt;


const tieneSoporteUserMedia = () =>
    !!(navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia)

function _getUserMedia(){
    return (navigator.getUserMedia || (navigator.mozGetUserMedia ||  navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia).apply(navigator, arguments);
}


let $listaDeDispositivos = document.querySelector("#listaDeDispositivos");

const limpiarSelect = () => {
    for (let x = $listaDeDispositivos.options.length - 1; x >= 0; x--)
        $listaDeDispositivos.remove(x);
};
const obtenerDispositivos = () => navigator
    .mediaDevices
    .enumerateDevices();

const llenarSelectConDispositivosDisponibles = () => {
    limpiarSelect();
    obtenerDispositivos()
        .then(dispositivos => {
            const dispositivosDeVideo = [];
            dispositivos.forEach(dispositivo => {
                const tipo = dispositivo.kind;
                if (tipo === "videoinput") {
                    dispositivosDeVideo.push(dispositivo);
                }
            });

            if (dispositivosDeVideo.length > 0) {
                dispositivosDeVideo.forEach(dispositivo => {
                    const option = document.createElement('option');
                    option.value = dispositivo.deviceId;
                    option.text = dispositivo.label;
                    $listaDeDispositivos.appendChild(option);
                });
            }
        });
}

$video = document.getElementById("video");
let currentStream;
let VIDEO_DATA = false;

function stopMediaTracks(stream) {
  stream.getTracks().forEach(track => {
    track.stop();
  });
}

const mostrarStream = idDeDispositivo => {

    if (typeof currentStream !== 'undefined') {
        stopMediaTracks(currentStream);
      }
    const videoConstraints = {};
    if (idDeDispositivo === '') {
        videoConstraints.facingMode = 'environment';
    } else {
        videoConstraints.deviceId = { exact: idDeDispositivo };
    }
    const constraints = {
        video: videoConstraints,
        audio: false
    };

    navigator.mediaDevices
      .getUserMedia(constraints)
      .then(stream => {
        currentStream = stream;
        VIDEO_DATA = true;
        $video.srcObject = stream;
        return navigator.mediaDevices.enumerateDevices();
      })
      .then(gotDevices)
      .catch(error => {
        VIDEO_DATA = false;
      });

    // _getUserMedia({
    //         video: {
    //             // Justo aquí indicamos cuál dispositivo usar
    //             deviceId: idDeDispositivo,
    //         }
    //     },
    //     (streamObtenido) => {
    //         // Aquí ya tenemos permisos, ahora sí llenamos el select,
    //         // pues si no, no nos daría el nombre de los dispositivos
    //         // llenarSelectConDispositivosDisponibles();

    //         // Escuchar cuando seleccionen otra opción y entonces llamar a esta función
    //         $listaDeDispositivos.onchange = () => {
    //             // Detener el stream
    //             if (stream) {
    //                 stream.getTracks().forEach(function(track) {
    //                     track.stop();
    //                 });
    //             }
    //             // Mostrar el nuevo stream con el dispositivo seleccionado
    //             mostrarStream($listaDeDispositivos.value);
    //         }

    //         // Simple asignación
    //         stream = streamObtenido;

    //         // Mandamos el stream de la cámara al elemento de vídeo
    //         $video.srcObject = stream;
    //         $video.play();
    //     }, (error) => {
    //         console.log("Permiso denegado o error: ", error);
    //     });
}


$("#listaDeDispositivos").on('change', function(event) {
    // alert($listaDeDispositivos.value);
    // Mostrar el nuevo stream con el dispositivo seleccionado
    mostrarStream($listaDeDispositivos.value);
});

// $listaDeDispositivos.onchange = () => {
//     // Detener el stream
//     if (stream) {
//         stream.getTracks().forEach(function(track) {
//             track.stop();
//         });
//     }
//     alert($listaDeDispositivos.value);
//     // Mostrar el nuevo stream con el dispositivo seleccionado
//     mostrarStream($listaDeDispositivos.value);
// }

function turnOnCamera() {

    // llenarSelectConDispositivosDisponibles();

    obtenerDispositivos().then(dispositivos => {
        // Vamos a filtrarlos y guardar aquí los de vídeo
        const dispositivosDeVideo = [];

        // Recorrer y filtrar
        dispositivos.forEach(function(dispositivo) {
            const tipo = dispositivo.kind;
            if (tipo === "videoinput") {
                dispositivosDeVideo.push(dispositivo);
            }
        });

        // Vemos si encontramos algún dispositivo, y en caso de que si, entonces llamamos a la función
        // y le pasamos el id de dispositivo
        if (dispositivosDeVideo.length > 0) {
            // Mostrar stream con el ID del primer dispositivo, luego el usuario puede cambiar
            // alert(dispositivosDeVideo[ (dispositivosDeVideo.length - 1) ].deviceId);
            mostrarStream(dispositivosDeVideo[ (dispositivosDeVideo.length - 1) ].deviceId);
        }
    });
}

function turnOffCamera() {
    video.pause();
    video.srcObject = null;
    localstream.getTracks()[0].stop();
}

$("#radiotfoto").click(function() {
    $(".defaultavatar").addClass("none");
    $("#subirfoto").addClass("none");
    $("#video").removeClass("none");
    $("#selectcamdevice").css("display","block");
    turnOnCamera();
    if ($("#subirfoto").length) {
        document.getElementById("subirfoto").value = null;
    }
});

$("#radiosfoto").click(function() {
    $("#subirfoto").removeClass("none");
    $("#video").addClass("none");
    turnOffCamera();
});
// Función para capturar la imagen con calidad reducida

// Función para capturar la imagen con calidad reducida fin
function gotDevices(mediaDevices) {
  $listaDeDispositivos.innerHTML = '';
  // $listaDeDispositivos.appendChild(document.createElement('option'));
  let count = 1;
  var cammeraId = null;
  mediaDevices.forEach(mediaDevice => {
    // console.log("Se ejecuta");
    if (mediaDevice.kind === 'videoinput') {
      const option = document.createElement('option');
      option.value = mediaDevice.deviceId;
      const label = mediaDevice.label || `Camera ${count++}`;
      const textNode = document.createTextNode(label);
      option.appendChild(textNode);
      $listaDeDispositivos.appendChild(option);
      if(count == 1){
        cammeraId = mediaDevice.deviceId;
      }
      if(mediaDevice.label.toLowerCase().indexOf("back") != -1 || mediaDevice.label.toLowerCase().indexOf("trasera") != -1 ){
        cammeraId = mediaDevice.deviceId;
        $listaDeDispositivos.value = mediaDevice.deviceId;
      }
    }
  });
}

navigator.mediaDevices.enumerateDevices().then(gotDevices);