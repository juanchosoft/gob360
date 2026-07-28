$(document).ready(initVereda);

let MUN = "", DEP = "", ID_VEREDA = "", PILAR = "";
let countMun = 0;
let isUpdating = false; // Flag para evitar loop infinito
function initVereda() {
	VEREDAS.init();
}

const VEREDAS = {
	init: function () {
		const { mun, dep, vereda, pilar } = UTIL.getParamsFromUrlDepartamentoMunicipio();
		MUN = mun;
		DEP = dep;
		ID_VEREDA = vereda;
		PILAR = pilar;

		$("#tbl_departamento_id").val(dep).trigger('change'); // Actualizar departamento
		$("#pilarId").val(pilar).trigger('change'); // Actualizar pilar

		// Obtener municipios y veredas
		DEPARTAMENTO.getMunicipiosConDepartamentoPrincipal();
		DEPARTAMENTO.getVeredasByMunicipioIdInformacionMapa(MUN, ID_VEREDA);

		// Verificar la carga del select de municipios y actualizarlo
		this.waitForElement("#tbl_municipio_id", () => {
			this.updateSelectWithoutTrigger("#tbl_municipio_id", mun);
		});
		this.waitForElement("#pilarId", () => {
			this.updateSelectWithoutTrigger("#pilarId", pilar);
		});
	},

	waitForElement: function (selector, callback) {
		const interval = setInterval(() => {
			if ($(selector).find("option").length > 0) {
				clearInterval(interval);
				callback();
			}
		}, 100);

		// Límite máximo de espera
		setTimeout(() => {
			clearInterval(interval);
			console.warn(`El select ${selector} no cargó las opciones a tiempo.`);
		}, 5000);
	},
	updateUrlPilar: function (item) {
		const pilarSelectedValue = item.value || PILAR;

		if (!pilarSelectedValue) {
			UTIL.mostrarMensajeError("Valor de pilar no seleccionado.");
			return;
		}

		const currentUrl = this.updateUrlParam("pilar", pilarSelectedValue);
		this.loadContentidoMapa(currentUrl);
	},
	updateUrlVereda: function (item) {
		const veredaSelectedValue = item.value || ID_VEREDA;

		if (!veredaSelectedValue) {
			UTIL.mostrarMensajeError("Valor de vereda no seleccionado.");
			return;
		}

		const currentUrl = this.updateUrlParam("id", veredaSelectedValue);
		this.loadContentidoMapa(currentUrl);
	},
	updateUrlParam: function (param, value) {
		if (value > 0 && value !== "") {
			const currentUrl = new URL(window.location.href);
			currentUrl.searchParams.set(param, value);
			window.history.pushState({}, "", currentUrl);
			return currentUrl;
		} else {
			UTIL.mostrarMensajeError("Información seleccionada no es correcta para mostrar datos del mapa.");
		}
	},

	updateSelectWithoutTrigger: function (selectId, value) {
		const selectElement = $(selectId);
		selectElement.off("change"); // Desactivar temporalmente eventos onchange
		selectElement.val(value).trigger("change"); // Actualizar valor
		selectElement.on("change", () => {
			this.updateUrlMunicipio(selectElement[0]); // Restaurar evento
		});
	},

	loadContentidoMapa: function (url) {
		if (this.isLoadingMap) return; // Evitar múltiples cargas simultáneas
		this.isLoadingMap = true;

		$.ajax({
			url: url.toString(),
			type: "GET",
			success: (response) => {
				["#contenido-mapa", "#divConsolidado", "#tbodyCompromisos"].forEach((selector) => {
					const updatedContent = $(response).find(selector).html();
					$(selector).html(updatedContent);
				});
			},
			error: (xhr, status, error) => {
				console.error("Error al cargar contenido del mapa:", error);
			},
			complete: () => {
				this.isLoadingMap = false;
			},
		});
	},

	abrirModalCompromiso: function (factorId, cantidadActual) {
		$("#factorIdModal").val(factorId);
		$("#cantidadActual").val(cantidadActual);
	},
	guardarCompromiso: function () {
		const cantidad = $("#cantidadCompromiso").val();
		const actor = $("#actoresId").val();
		const observaciones = $("#observacionesCompromiso").val();
		const factorId = $("#factorIdModal").val();
		const cantidadActual = $("#cantidadActual").val();
		const tbl_vereda_id = $("#veredaId").val();
		const codigo_municipio = $("#municipioId").val();
		const codigo_departamento = $("#departamentoId").val();
	
		if (!factorId) {
			mostrarAlerta("error", "❌ No se encontró un Factor válido.");
			return;
		}
		if (!cantidad || isNaN(cantidad) || cantidad <= 0) {
			mostrarAlerta("error", "❌ Debes ingresar una cantidad válida.");
			return;
		}
		if (!cantidadActual || isNaN(cantidadActual) || cantidadActual <= 0) {
			mostrarAlerta("error", "❌ Debes ingresar una cantidad válida.");
			return;
		}
		if (!actor || actor === "") {
			mostrarAlerta("error", "❌ Debes seleccionar un actor.");
			return;
		}
	
		const datos = {
			op: "guardarCompromiso",
			tbl_vereda_id: tbl_vereda_id,
			codigo_municipio: codigo_municipio,
			codigo_departamento: codigo_departamento,
			factorId: factorId,
			cantidadActual: cantidadActual,
			cantidad: cantidad,
			actor: actor,
			observaciones: observaciones || ""
		};
	
		$.ajax({
			url: "admin/ajax/rqst.php",
			type: "POST",
			data: datos,
			dataType: "json",
			success: function (response) {
				if (response.output.valid) {
					// Mostrar mensaje de éxito
					mostrarAlerta("success", "✅ Compromiso guardado correctamente.");
	
					// Esperar 2 segundos antes de cerrar el modal
					setTimeout(() => {
						$("#modalSeleccionar").modal("hide"); // Cerrar el modal
						$(".modal-backdrop").remove(); // Eliminar el fondo gris
						$("body").removeClass("modal-open"); // Restaurar el scroll de la página
	
						// Esperar 2 segundos más antes de recargar la página
						setTimeout(() => {
							location.reload();
						}, 2000);
					}, 2000);
	
				} else {
					mostrarAlerta("error", response.output.response.content);
				}
			},
			error: function () {
				mostrarAlerta("error", "❌ Error en la comunicación con el servidor.");
			}
		});
	}
	
};