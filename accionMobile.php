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
        <h2 style="color: #5b5a58;">ACCIÓN UNIFICADA</h2>
        <div class="search-bar-mobile">
            <input type="text" placeholder="¿Qué modulo necesita?">
        </div>
    </div>
    <!-- Sección de acción unificada -->
    <div class="menu-section">
        <div class="section-header">
            <h2>Acción unificada</h2>
            <div class="toggle-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </div>
        </div>
        <div class="menu-grid">
            <a href="departamentos.php?pilar=<?php echo Util::getIdentificadorPilarPrincipal(); ?>" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                        <path d="M12 10v4" stroke-dasharray="2 2" />
                    </svg>
                </div>
                <div class="label-mobile">Estado departamento</div>
            </a>
            <a href="municipios.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>&pilar=<?php echo Util::codigoTodos(); ?>" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z" />
                        <path d="M12 10v12M3 10h18" stroke-dasharray="3 2" />
                    </svg>
                </div>
                <div class="label-mobile">Estado municipios</div>
            </a>
            <a href="veredas_criticas.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 22h20L12 2z" />
                        <path d="M12 16v-4m0 0V8m0 4h4m-4 0H8" />
                    </svg>
                </div>
                <div class="label-mobile">Veredas críticas</div>
            </a>
            <a href="avances.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18" />
                        <path d="M7 15l4-6 4 3.5 4-5" />
                    </svg>
                </div>
                <div class="label-mobile">Avances</div>
            </a>
            <a href="imagenes.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                        <circle cx="12" cy="13" r="4" />
                    </svg>
                </div>
                <div class="label-mobile">Imagenes</div>
            </a>
            <a href="accionunificada.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" />
                        <rect x="14" y="3" width="7" height="5" />
                        <rect x="3" y="15" width="7" height="6" />
                        <rect x="14" y="11" width="7" height="10" />
                    </svg>
                </div>
                <div class="label-mobile">Acción unificada</div>
            </a>
        </div>
    </div>
    <!-- Fin de sección de acción unificada -->
    <div class="divider"></div>
    
    <!-- Sección de configuración accion unificada -->
    <div class="menu-section">
        <div class="section-header">
            <h2>Configuración acción unificada</h2>
            <div class="toggle-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </div>
        </div>
        <div class="menu-grid">
            <a href="areas.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="16" rx="1" />
                        <rect x="8" y="9" width="8" height="8" rx="1" />
                        <path d="M12 5v4m0 0l2-2-2-2" />
                    </svg>
                </div>
                <div class="label-mobile">Ingreso áreas</div>
            </a>
            <a href="ingreso_factores.php" class="menu-item-mobile">
                <div class="icon-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="rgb(122, 102, 13)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="1" />
                    <line x1="7" y1="9" x2="17" y2="9" />
                    <line x1="7" y1="13" x2="17" y2="13" />
                    <line x1="7" y1="17" x2="12" y2="17" />
                    <path d="M19 3v4m-2-2h4" />
                </svg>
                </div>
                <div class="label-mobile">Ingreso factores</div>
            </a>
            <a href="actores.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <path d="M20 8v6m3-3h-6"></path>
                        <circle cx="17" cy="7" r="1"></circle>
                        <circle cx="20" cy="12" r="1"></circle>
                    </svg>
                </div>
                <div class="label-mobile">Ingreso actores</div>
            </a>
            <a href="ingreso_informacion.php" class="menu-item-mobile">
                <div class="icon-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                    fill="none" stroke="rgb(122, 102, 13)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                    <line x1="8" y1="16" x2="16" y2="16"></line>
                    <path d="M18 6v3h3"></path>
                    <path d="M15 9l3-3 3 3"></path>
                </svg>
                </div>
                <div class="label-mobile">Ingreso información</div>
            </a>
            <a href="ingreso_informacion_listado.php" class="menu-item-mobile">
                <div class="icon-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                    fill="none" stroke="rgb(122, 102, 13)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M5 8h14M5 16h10"/>
                    <path d="M19 16v3m0 0l-1.5-1.5M19 19l1.5-1.5"/>
                    <path d="M17 5l2-2 2 2"/>
                    <circle cx="19" cy="8" r="1" fill="rgb(122, 102, 13)" stroke="none"/>
                </svg>
                </div>
                <div class="label-mobile">Listado ingreso información</div>
            </a>
            <a href="actualizacion_informacion.php" class="menu-item-mobile">
                <div class="icon-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                    fill="none" stroke="rgb(122, 102, 13)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                    <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/>
                    <line x1="9" y1="9" x2="15" y2="9"/>
                    <line x1="9" y1="13" x2="15" y2="13"/>
                    <line x1="9" y1="17" x2="13" y2="17"/>
                    <path d="M18 15l2 2-2 2m2-4h-4"/>
                </svg>
                </div>
                <div class="label-mobile">Actualización información</div>
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
    margin: 79px auto; // Ajusta el margen superior según sea necesario
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
.search-bar-mobile {
    position: relative;
    margin-bottom: 15px;
}

.search-bar-mobile input {
    width: 100%;
    padding: 12px 15px 12px 40px;
    border: none;
    background-color: #f7f7f7;
    border-radius: 25px;
    font-size: 14px;
    color: #5b5a58;
}

.search-bar-mobile input::placeholder {
    color: #5b5a58;
}

.search-bar-mobile::before {
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