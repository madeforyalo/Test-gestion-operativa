<?php
// DECLARAMOS EL SITIO Y EL VENDEDOR
$SITE_ID = 'MLA';
$SELLER_ID = 179571326;
// Corroboramos si el usuario nos ha proporcionado la información
if(isset($_POST)){

$SITE_ID = $_POST["site_id"];
$SELLER_ID = $_POST["seller_id"];

// REALIZAMOS LA PETICIÓN
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/sites/$SITE_ID/search?seller_id=$SELLER_ID");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$RESULTS = curl_exec($curl);
curl_close($curl);
// AGREMAOS CORCHETES PARA TRANSFORMARLO EN JSON
$RESULTS = "[$RESULTS]";
// CONVERTIMOS LOS RESULTADOS EN ARRAY
$RESULTS = json_decode($RESULTS,True);
// DECLARAMOS LA VARIABLE PRODUCTS CON LOS PRODUCTOS ENCONTRADOS
$PRODUCTS = $RESULTS[0]["results"];
// AVERIGUAMOS CUANTOS PRODUCTOS HAY EN TOTAL
$PAGING = $RESULTS[0]["paging"]["total"];
// CREAMOS UN ARRAY VACÍO DONDE LUEGO EXPORTAREMOS LA INFORMACIÓN
$PRODUCTS_LOG = [];

// RECORREMOS LOS PRIMEROS PRODUCTOS OBTENIDOS Y TOMAMOS LA INFORMACIÓN SOLICITADA
foreach( $PRODUCTS AS $PRODUCT){
	// Buscamos el NAME de la categoría
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/categories/".$PRODUCT['category_id']."");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$RESULTS = curl_exec($curl);
	curl_close($curl);
	// AGREMAOS CORCHETES PARA TRANSFORMARLO EN JSON
	$RESULTS = "[$RESULTS]";
	// CONVERTIMOS LOS RESULTADOS EN ARRAY
	$RESULTS = json_decode($RESULTS,True);
	// DECLARAMOS LA VARIABLE NAME
	$NAME = $RESULTS[0]["name"];
	// INSERTAMOS TODOS LOS DATOS EN EL ARRAY
	$PRODUCTS_LOG[]= [
		"Id del ítem" => $PRODUCT["id"],
		"Título del ítem" => $PRODUCT["title"],
		"Categoría_ID donde está publicado" => $PRODUCT["category_id"],
		"Nombre de la categoría" => $NAME
	];
};

// DECLARAMOS LA VARIABLE OFFSET
$offset = 50;
for ($i=0; $i < ($PAGING/50); $i++) {
	// REPETIMOS PROCESO DE BUSQUEDA AUMENTANDO EL OFFSET
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/sites/$SITE_ID/search?seller_id=$SELLER_ID&offset=$offset");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$RESULTS = curl_exec($curl);
	curl_close($curl);
	// AGREMAOS CORCHETES PARA TRANSFORMARLO EN JSON
	$RESULTS = "[$RESULTS]";
	// CONVERTIMOS LOS RESULTADOS EN ARRAY
	$RESULTS = json_decode($RESULTS,True);
	// DECLARAMOS LA VARIABLE PRODUCTS CON LOS PRODUCTOS ENCONTRADOS
	$PRODUCTS = $RESULTS[0]["results"];

	// RECORREMOS LOS PRODUCTOS OBTENIDOS Y TOMAMOS LA INFORMACIÓN SOLICITADA
	foreach( $PRODUCTS AS $PRODUCT){
		// Buscamos el NAME de la categoría
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/categories/".$PRODUCT['category_id']."");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$RESULTS = curl_exec($curl);
		curl_close($curl);
		// AGREGAMOS CORCHETES PARA TRANSFORMARLO EN JSON
		$RESULTS = "[$RESULTS]";
		// CONVERTIMOS LOS RESULTADOS EN ARRAY
		$RESULTS = json_decode($RESULTS,True);
		// DECLARAMOS LA VARIABLE NAME
		$NAME = $RESULTS[0]["name"];
		// INSERTAMOS TODOS LOS DATOS EN EL ARRAY $PRODUCTS_LOG
		$PRODUCTS_LOG[]= [
			"Id del ítem" => $PRODUCT["id"],
			"Título del ítem" => $PRODUCT["title"],
			"Categoría_ID donde está publicado" => $PRODUCT["category_id"],
			"Nombre de la categoría" => $NAME
		];
	};
	$offset = $offset+50;
}

// EXPORTAMOS
// NO OLVIDAR JSON_UNESCAPED_UNICODE PARA EVITAR ERRORES DE FORMATO CON Ñ Y TILDES
$PRODUCTS_JSON = json_encode($PRODUCTS_LOG,JSON_UNESCAPED_UNICODE);
// CREAMOS EL ARCHIVO DE LOG
$file = 'productos.txt';
// SOBRESCRIBIMOS
file_put_contents($file, $PRODUCTS_JSON);

//RESPONDEMOS LA PETICIÓN
$response = "Se ha creado el archivo correctamente";
echo json_encode("Se ha creado el archivo log exitosamente");
};
?>