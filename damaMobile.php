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
        <h2 style="color: #5b5a58;">PRIMERA DAMA</h2>
        <div class="search-bar-mobile">
            <input type="text" placeholder="¿Qué modulo necesita?">
        </div>
    </div>
    <!-- Sección de primera dama -->
    <div class="menu-section">
        <div class="section-header">
            <h2  style="color: #5b5a58">Primera dama</h2>
            <div class="toggle-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </div>
        </div>
        <div class="menu-grid">
            <a href="gestora_social.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <path d="M16 12l-4 4-1 3 3-1 4-4-2-2z"/>
                    </svg>
                </div>
                <div class="label-mobile">Actividades</div>
            </a>
            <a href="visitasgestora.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <polyline points="9 16 11.5 18.5 15 15"></polyline>
                    </svg>
                </div>
                <div class="label-mobile">Registro actividades</div>
            </a>
            <a href="cuadro_control_visitasg.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="9" x2="19" y2="9"></line>
                        <line x1="9" y1="14" x2="19" y2="14"></line>
                        <line x1="9" y1="19" x2="19" y2="19"></line>
                        <circle cx="5" cy="9" r="1"></circle>
                        <circle cx="5" cy="14" r="1" fill="currentColor"></circle>
                        <circle cx="5" cy="19" r="1"></circle>
                    </svg>
                </div>
                <div class="label-mobile">Control actividades</div>
            </a>
        </div>
    </div>
    <!-- Fin de sección de primera dama -->
     <!-- Sección de ASPAS -->
    <div class="menu-section">
        <div class="section-header">
            <h2  style="color: #5b5a58">ASPAS</h2>
            <div class="toggle-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </div>
        </div>
        <div class="menu-grid">
            <a href="aspasactividades.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <path d="M16 12l-4 4-1 3 3-1 4-4-2-2z"/>
                    </svg>
                </div>
                <div class="label-mobile">Actividades</div>
            </a>
            <a href="visitasaspas.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <polyline points="9 16 11.5 18.5 15 15"></polyline>
                    </svg>
                </div>
                <div class="label-mobile">Registro actividades</div>
            </a>
            <a href="cuadro_control_visitasaspas.php" class="menu-item-mobile">
                <div class="icon-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="9" x2="19" y2="9"></line>
                        <line x1="9" y1="14" x2="19" y2="14"></line>
                        <line x1="9" y1="19" x2="19" y2="19"></line>
                        <circle cx="5" cy="9" r="1"></circle>
                        <circle cx="5" cy="14" r="1" fill="currentColor"></circle>
                        <circle cx="5" cy="19" r="1"></circle>
                    </svg>
                </div>
                <div class="label-mobile">Control actividades</div>
            </a>
        </div>
        <!-- Fin de sección ASPAS -->
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

//FUNCIONALIDAD BARRA DE BUSQUEDA
document.addEventListener("DOMContentLoaded", function() {
    // Obtiene referencias a los elementos
    const searchInput = document.querySelector('.search-bar input');
    const menuItems = document.querySelectorAll('.menu-item-mobile');
    
    // Añadir evento de entrada al campo de búsqueda
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        // Si no hay término de búsqueda, mostrar todos los elementos
        if (searchTerm === '') {
            menuItems.forEach(item => {
                item.style.display = 'flex';
            });
            return;
        }
        
        // Filtrar los elementos del menú
        menuItems.forEach(item => {
            const label = item.querySelector('.label-mobile').textContent.toLowerCase();
            
            // Verificar si el texto del elemento contiene el término de búsqueda
            if (label.includes(searchTerm)) {
                item.style.display = 'flex'; // Muestra el elemento
            } else {
                item.style.display = 'none'; // Oculta el elemento
            }
        });
        
        // Comprobar si hay resultados visibles en cada sección
        const sections = document.querySelectorAll('.menu-section');
        sections.forEach(section => {
            const sectionItems = section.querySelectorAll('.menu-item-mobile');
            const visibleItems = Array.from(sectionItems).filter(item => item.style.display !== 'none');
        });
    });
});

</script>

<?php 
include 'admin/include/generic_script.php'; 
?>
