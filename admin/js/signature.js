// Inicializar SignaturePad
const canvasFirma = document.getElementById('signature-pad');
const signaturePad = new SignaturePad(canvasFirma);

// Ajustar el tamaño del canvas para diferentes pantallas sin borrar la firma
function resizeCanvas() {
    // Guardar la firma actual antes de redimensionar
    const dataURL = signaturePad.toDataURL();
    
    // Ajustar el tamaño del canvas
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvasFirma.width = canvasFirma.offsetWidth * ratio;
    canvasFirma.height = canvasFirma.offsetHeight * ratio;
    canvasFirma.getContext('2d').scale(ratio, ratio);
    
    // Restaurar la firma guardada después del redimensionamiento
    signaturePad.fromDataURL(dataURL);
}

// Ajustar el tamaño al cargar la página y al redimensionar la ventana
window.addEventListener('resize', resizeCanvas);
resizeCanvas();

// Botón para limpiar la firma
document.getElementById('clear').addEventListener('click', function() {
    signaturePad.clear();
});
