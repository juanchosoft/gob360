<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/include/header.php';
?>

<div class="menu-container">
    <!-- Inicio boton atrás -->
    <div>
        <button class="back-button">
            <i class="back-icon"></i>
            <span>Atrás</span>
        </button>
    </div>
    <!-- Fin de boton atrás -->
    <div class="menu-header">
        <h2 style="color: #5b5a58;">VISITA GOBERNADOR</h2>
        <div class="search-bar">
            <input type="text" placeholder="¿Qué modulo necesita?">
        </div>
    </div>
    <!-- Sección de registro visita gobernador -->
    <div class="menu-section">
        <div class="section-header">
            <h2>Mapa visitas</h2>
            <div class="toggle-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </div>
        </div>
        <div class="menu-grid">
            <a href="mapa_visitas_departamento.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0 1 18 0z" />
                    <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div class="label-mobile">Departamento</div>
            </a>
        </div>
    </div>
    <!-- Fin de sección de registro visita gobernador -->
    <div class="divider"></div>
    
    <!-- Sección de registro visitas -->
    <div class="menu-section">
        <div class="section-header">
            <h2>Registro visitas</h2>
            <div class="toggle-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </div>
        </div>
        <div class="menu-grid">
            <a href="informacion_visitas.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                        <path d="M16 16a4 4 0 0 1-8 0v-1a3 3 0 0 1 6 0v1z" />
                        <path d="M20 12h-4m2-2l2 2-2 2" />
                    </svg>
                </div>
                <div class="label-mobile">Ingreso visitas</div>
            </a>
            <a href="cuadro_control_visitas.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="8" y1="8" x2="8" y2="8"></line>
                        <line x1="12" y1="8" x2="12" y2="8"></line>
                        <line x1="16" y1="8" x2="16" y2="8"></line>
                        <line x1="8" y1="12" x2="8" y2="12"></line>
                        <line x1="12" y1="12" x2="12" y2="12"></line>
                        <line x1="16" y1="12" x2="16" y2="12"></line>
                        <line x1="8" y1="16" x2="8" y2="16"></line>
                        <line x1="12" y1="16" x2="12" y2="16"></line>
                        <line x1="16" y1="16" x2="16" y2="16"></line>
                    </svg>
                </div>
                <div class="label-mobile">Control visitas</div>
            </a>
            <a href="graficacion_compromisos.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21.21 15.89A10 10 0 1 1 12 2v10z"></path>
                    </svg>
                </div>
                <div class="label-mobile">Grafico compromisos</div>
            </a>
            <a href="cuadro-control-compromisos.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="9" x2="9" y2="9"></line>
                        <line x1="15" y1="9" x2="15" y2="9"></line>
                        <line x1="9" y1="15" x2="9" y2="15"></line>
                        <line x1="15" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div class="label-mobile">Cuadro Control Compromisos</div>
            </a>
            <a href="gestion-cumplimiento.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22,4 12,14.01 9,11.01"></polyline>
                    </svg>
                </div>
                <div class="label-mobile">Gestion Cumplimiento</div>
            </a>
        </div>
        
    </div>
</div>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.menu-container {
    width: 100%;
    max-width: 400px;
    overflow: hidden;
    padding: 20px;
    margin: 100px auto; // Ajusta el margen superior según sea necesario
}

.menu-header {
    margin-bottom: 15px;
}

/*Inicio de diseño del botón de atrás */
.back-button {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    padding: 10px 5px;
    font-size: 14px;
    color: #5b5a58;
    cursor: pointer;
    margin-bottom: 15px;
    transition: color 0.2s;
}

.back-button:hover {
    color: #3b4cd9;
}

.back-icon {
    position: relative;
    display: inline-block;
    width: 16px;
    height: 16px;
}

.back-icon::before {
    content: "";
    position: absolute;
    width: 12px;
    height: 12px;
    border-left: 2px solid currentColor;
    border-bottom: 2px solid currentColor;
    transform: rotate(45deg);
    top: 2px;
    left: 2px;
}
/*Fin de diseño del botón de atrás */

/* Inicio diseño de buscar */
.search-bar {
    position: relative;
    margin-bottom: 15px;
}

.search-bar input {
    width: 100%;
    padding: 12px 15px 12px 40px;
    border: none;
    background-color: #f7f7f7;
    border-radius: 25px;
    font-size: 14px;
    color: #5b5a58;
}

.search-bar input::placeholder {
    color: #5b5a58;
}

.search-bar::before {
    content: "";
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-size: contain;
}
/* Fin de diseño de buscar */

/*Diseño de barra de despliegue*/ 
.menu-section {
    padding: 16px 20px;
    position: relative;
}

.section-header {
    display: flex;
    flex-direction: column;
    position: relative;
    margin-bottom: 16px;
}

.section-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.section-header p {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.toggle-icon {
    position: absolute;
    right: 0;
    top: 10px;
    color: #333;
}

/*Linea de divicion de despliegue*/
.divider {
    height: 1px;
    background-color: #eee;
    margin: 5px 0;
}

.menu-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.menu-item-mobile {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: calc(33.33% - 10px);
    text-decoration: none;
    border-radius: 12px;
    padding: 12px 8px;
    transition: all 0.2s ease;
    background-color: #f5f7fa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.menu-item-mobile:hover {
    background-color: #f9f9f9;
}

.icon-mobile svg { /*color de los iconos */
    stroke: rgb(122 102 13);
}

.label-mobile {
    font-size: 13px;
    color: #333;
    text-align: center;
}

/*Oculta el menu mobile predeterminada de la plantilla*/
.pcoded-header .mobile-menu {
    display: none !important;
}

</style>

<!-- SCRIPT DE FUNCIONAMIENTO -->
<script>

// FUNCIONAMIENTO BOTÓN ATRÁS
document.addEventListener("DOMContentLoaded", function() {
    const backButton = document.querySelector('.back-button');
    if (backButton) {
        backButton.addEventListener('click', function() {
            window.history.back();
        });
    }
});

//FUNCIONAMIENTO DE EXPANDIR Y COLAPSAR SECCIONES
document.addEventListener("DOMContentLoaded", function() {
    const toggleIcons = document.querySelectorAll('.toggle-icon');
    
    toggleIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const section = this.closest('.menu-section');
            const grid = section.querySelector('.menu-grid');
            
            if (grid.style.display === 'none') {
                grid.style.display = 'flex';
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                `;
            } else {
                grid.style.display = 'none';
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                `;
            }
        });
    });
});

</script>

<?php 
include 'admin/include/generic_script.php'; 
?>
