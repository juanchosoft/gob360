<!-- LUPA PARA ZOOM  -->
<div id="lupa-toggle-container">
  <button id="toggleLupa" class="btn-lupa" title="Hacer Zoom">
    <i class="bi bi-zoom-in"></i> <!-- Bootstrap icon lupa -->
  </button>
</div>

<div id="lupa-container">
    <div id="lupa"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const lupaContainer = document.getElementById("lupa-container");
    const lupa = document.getElementById("lupa");
    const btnLupa = document.getElementById("toggleLupa");
    let lupaActiva = false;
    let imagenCapturada = false;

    function capturarPantalla() {
        if (imagenCapturada) return; 

        html2canvas(document.body, { scale: 3, useCORS: true }).then(canvas => {
            lupa.style.backgroundImage = `url(${canvas.toDataURL("image/png", 1.0)})`; 
            lupa.style.backgroundSize = `${document.body.scrollWidth * 4}px ${document.body.scrollHeight * 4}px`; 
            imagenCapturada = true;
        });
    }

    btnLupa.addEventListener("click", function () {
        lupaActiva = !lupaActiva;

        if (lupaActiva) {
            capturarPantalla();
            lupaContainer.style.display = "block";
        } else {
            lupaContainer.style.display = "none";
        }
    });

    document.addEventListener("mousemove", function (event) {
    if (!lupaActiva) return;

    let x = event.pageX;
    let y = event.pageY;

    let lupaSize = 150;  
    let offsetX = -lupaSize - 150; 
    let offsetY = -50; 

    lupaContainer.style.left = `${x + offsetX}px`;  
    lupaContainer.style.top = `${y + offsetY}px`;

    let scaleFactor = 4;
    lupa.style.backgroundPosition = `-${x * scaleFactor - lupaSize / 2}px -${y * scaleFactor - lupaSize / 2}px`;
});


    document.addEventListener("mouseleave", function () {
        if (lupaActiva) {
            lupaContainer.style.display = "none";
        }
    });

    document.addEventListener("mouseenter", function () {
        if (lupaActiva) {
            lupaContainer.style.display = "block";
        }
    });
});
</script>

<!-- LIBRERIA PARA QUE EL ZOOM QUEDE DE ALTA CALIDAD -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>

#lupa-toggle-container {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
}


.btn-lupa {
    width: 55px;
    height: 55px;
    background-color: #007bff;
    color: white;
    border: 2px solid white;
    border-radius: 5px; 
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}


.btn-lupa::after {
    content: "Zoom"; 
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    background-color: black;
    color: white;
    font-size: 12px;
    padding: 5px;
    border-radius: 5px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease-in-out;
}

.btn-lupa:hover::after {
    opacity: 1;
    visibility: visible;
}


.btn-lupa.active {
    background-color: #ff0000; 
    border-color: white;
    box-shadow: 0px 0px 10px rgba(255, 0, 0, 0.5);
}


#lupa-container {
    position: absolute;
    display: none;
    width: 150px;
    height: 150px;
    border: 2px solid black;
    overflow: hidden;
    pointer-events: none;
    z-index: 1000;
    background-color: white;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
}

#lupa {
    position: absolute;
    width: 400%;
    height: 400%;
    transform-origin: center;
    background-repeat: no-repeat;
}
@media (max-width: 1200px) {
    .btn-lupa {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }

    #lupa-container {
        width: 130px;
        height: 130px;
    }
}

@media (max-width: 1024px) {
    .btn-lupa {
        width: 45px;
        height: 45px;
        font-size: 20px;
    }

    .btn-lupa::after {
        font-size: 10px;
        padding: 4px;
        bottom: -22px;
    }

    #lupa-container {
        width: 120px;
        height: 120px;
    }
}

@media (max-width: 768px) {
    #lupa-toggle-container {
        top: 8px;
        left: 8px;
    }

    .btn-lupa {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .btn-lupa::after {
        font-size: 9px;
        padding: 3px;
        bottom: -20px;
    }

    #lupa-container {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 480px) {
    #lupa-toggle-container {
        top: 5px;
        left: 5px;
    }

    .btn-lupa {
        width: 35px;
        height: 35px;
        font-size: 16px;
        top:20px;
    }

    .btn-lupa::after {
        font-size: 8px;
        padding: 2px;
        bottom: -18px;
    }

    #lupa-container {
        width: 90px;
        height: 90px;
    }
}

@media (max-width: 360px) {
    #lupa-toggle-container {
        top: 3px;
        left: 3px;
    }

    .btn-lupa {
        width: 30px;
        height: 30px;
        font-size: 14px;
    }

    .btn-lupa::after {
        font-size: 7px;
        padding: 2px;
        bottom: -16px;
    }

    #lupa-container {
        width: 80px;
        height: 80px;
    }
}

</style>
