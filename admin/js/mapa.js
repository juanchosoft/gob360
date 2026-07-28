function initMap(){

	q = {};
	q.op = "data_map";
	const map = new google.maps.Map(document.getElementById("map"), {
	    zoom: 7,
	    center: { lat: 7.1508903, lng: -77.7486203 },
	  });
	
	UTIL.callAjaxRqstPOST(q, function(resp){
		var data = resp.output.response.armada;
		var dataSoc = resp.output.response.social;
		var dataEcono = resp.output.response.economia;
		for(var i in data){
			var address2 = "Vereda "+data[i].nombre_vereda+" "+data[i].municipio+" "+data[i].departamento;
			var address = "Vereda "+data[i].nombre_vereda+" "+data[i].departamento;
			callData(address,map,data[i].total,address2);
		}

		for(var i in dataSoc){
			var address2 = "Vereda "+dataSoc[i].nombre_vereda+" "+dataSoc[i].municipio+" "+dataSoc[i].departamento;
			var address = "Vereda "+dataSoc[i].nombre_vereda+" "+dataSoc[i].departamento;
			callDataSocial(address,map,dataSoc[i].total,address2);
		}

		for(var i in dataEcono){
			var address2 = "Vereda "+dataEcono[i].nombre_vereda+" "+dataEcono[i].municipio+" "+dataEcono[i].departamento;
			var address = "Vereda "+dataEcono[i].nombre_vereda+" "+dataEcono[i].departamento;
			callDataEcono(address,map,dataEcono[i].total,address2);
		}
	});
}

function getIconArmada(total){
	if (total>=0 && total <= 166) {
		return 'armada1.png';
	}else if (total>=167 && total <= 167) {
		return 'armada2.png';
	}else if (total>=334 && total <= 500) {
		return 'armada3.png';
	}else if (total>=501 && total <= 667) {
		return 'armada4.png';
	}else if (total>=668 && total <= 834) {
		return 'armada5.png';
	}else if (total>=835 && total <= 1000000) {
		return 'armada6.png';
	}
}

function getIconEcono(total){
	if (total>=0 && total <= 166) {
		return 'economia1.png';
	}else if (total>=167 && total <= 167) {
		return 'economia2.png';
	}else if (total>=334 && total <= 500) {
		return 'economia3.png';
	}else if (total>=501 && total <= 667) {
		return 'economia4.png';
	}else if (total>=668 && total <= 834) {
		return 'economia5.png';
	}else if (total>=835 && total <= 1000000) {
		return 'economia6.png';
	}
}

function getIconSocial(total){
	if (total>=0 && total <= 166) {
		return 'social1.png';
	}else if (total>=167 && total <= 167) {
		return 'social2.png';
	}else if (total>=334 && total <= 500) {
		return 'social3.png';
	}else if (total>=501 && total <= 667) {
		return 'social4.png';
	}else if (total>=668 && total <= 834) {
		return 'social5.png';
	}else if (total>=835 && total <= 1000000) {
		return 'social6.png';
	}
}

function callData(address,map,total,dataFinal){
	var icon = getIconArmada(total);

	const contentString =
    '<div id="content">' +
    '<div id="siteNotice">' +
    "</div>" +
    '<h1 id="firstHeading" class="firstHeading">Puntos resultados armados: '+total+'</h1>' +
    '<div id="bodyContent">' +
    "<p><b>Resultados armados, "+dataFinal+"</b>, <br> <b>Total puntos: </b> "+total+" </p>"+
    "</div>" +
    "</div>";

    const infowindow = new google.maps.InfoWindow({
	    content: contentString,
	  });

	$.get("https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&key="+KEY_VALUE, function(results) {
		 map.setCenter(results.results[0].geometry.location);
		 	results.results[0].geometry.location.lat+=0.009;
		 	results.results[0].geometry.location.lng+=0.009;
		      const marker = new google.maps.Marker({
		        map: map,
		        position: results.results[0].geometry.location,
		        icon: 'admin/icons/'+icon
		      });

		      marker.addListener("click", () => {
			    infowindow.open(map, marker);
			  });

	},'json');
}

function callDataSocial(address,map,total,dataFinal){
	var icon = getIconSocial(total);

	const contentString =
    '<div id="content">' +
    '<div id="siteNotice">' +
    "</div>" +
    '<h1 id="firstHeading" class="firstHeading">Puntos resultados social: '+total+'</h1>' +
    '<div id="bodyContent">' +
    "<p><b>Resultados social, "+dataFinal+"</b>, <br> <b>Total puntos: </b> "+total+" </p>"+
    "</div>" +
    "</div>";

    const infowindow = new google.maps.InfoWindow({
	    content: contentString,
	  });

	$.get("https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&key="+KEY_VALUE, function(results) {
		 map.setCenter(results.results[0].geometry.location);
		      const marker = new google.maps.Marker({
		        map: map,
		        position: results.results[0].geometry.location,
		        icon: 'admin/icons/'+icon
		      });

		      marker.addListener("click", () => {
			    infowindow.open(map, marker);
			  });

	},'json');
}

function callDataEcono(address,map,total,dataFinal){
	var icon = getIconEcono(total);

	const contentString =
    '<div id="content">' +
    '<div id="siteNotice">' +
    "</div>" +
    '<h1 id="firstHeading" class="firstHeading">Puntos resultados economía: '+total+'</h1>' +
    '<div id="bodyContent">' +
    "<p><b>Resultados economía, "+dataFinal+"</b>, <br> <b>Total puntos: </b> "+total+" </p>"+
    "</div>" +
    "</div>";

    const infowindow = new google.maps.InfoWindow({
	    content: contentString,
	  });

	$.get("https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&key="+KEY_VALUE, function(results) {
		 map.setCenter(results.results[0].geometry.location);
		 	  results.results[0].geometry.location.lat+=0.015;
		 	results.results[0].geometry.location.lng+=0.015;
		      const marker = new google.maps.Marker({
		        map: map,
		        position: results.results[0].geometry.location,
		        icon: 'admin/icons/'+icon
		      });

		      marker.addListener("click", () => {
			    infowindow.open(map, marker);
			  });

	},'json');
}

function sleep(milliseconds) {
 var start = new Date().getTime();
 for (var i = 0; i < 1e7; i++) {
  if ((new Date().getTime() - start) > milliseconds) {
   break;
  }
 }
}
