Información general:
--------------------
Nombre de la Class: Panel
Descripcion: Class para crear paneles de control
Fecha de creacion: 30/04/2015
Ultima fecha de modificacion: 03/12/2016
Versión: 0.8

Histórico de cambios (CHANGELOG):
---------------------------------
03/12/2016
Añadimos el campo $registrosPorPagina para saber cuantos mostrar por cada pagina
Creamos una paginacion usando bootstrap
Añadimos la propiedad publica limitarCamposEnMostrar que contendra un array con los campos que mostraremos
Añado la propiedad ORDEN. por defecto sera Descendente, mostrando siempre los ultimos registros primero
Añadimos pestañas de navegacion cuando quiero ver un registro mostraremos de 20 en 20 propiedades
Cambio campo checkbox, de texto a numerico. Asi que si es que SI, ponemos 1, y si NO 0
Añadido codigo de cebreado para ver, modificar e insertar
Añadido la posibilidad de quitar una imagen, marcandola en modificar con un checkbox

10/07/2015
Añadimos a la lista de acciones, la posibilidad de ver 1 solo elemento

08/07/2015
Ordenamos los select por orden alfabetico

07/07/2015
Contemplamos la posibilidad de filtrar por algun id. No es obligatorio. En el método mostrar()
$idFiltrado: valor del id para filtrar
$campoFiltrado: Campo que tiene el id para filtrar. Normalmente una FOREIGN KEY
Añadimos el tipo de datos "select"
Para el tipo de datos select, necesitamos la tabla con la que se relaciona, asi como el nombre que se mostrara, y el campo id con el que se relaciona. La class panel NO crea la tabla relacionada, así que debe estar creada.
Añadimos el tipo de datos "decimal"
Creamos el metodo "mostrarAcciones()" para que sea más fácil crear herencias.
Las imágenes a partir de ahora, se subirán con nombre, y prefijo aleatorio.
Añadimos el tipo de datos "checkbox", que sera un varchar con la palabra "si o no"

06/07/2015
Añadimos el tipo de datos "textolargo"
Añadimos el tipo de datos "numero"
Añadimos el tipo de datos "fecha" (En la base de datos se guarda como yyyy-mm-dd)

07/05/2015
Añadimos al constructor, la creacion de la tabla
Añadimos varios tipos de datos (textocorto, imagen)

30/04/2015
Creación de la Class

Instrucciones uso:
------------------
EL método constructor, recibirá OBLIGATORIAMENTE, 4 parámetros, de modo que recibirá, el nombre de tabla, un vector con los campos, un vector con los tipos de datos, y un vector con los nombres de los campos
Cuando creemos el vector de campos, creamos un vector de tipos, donde tendremos los siguientes tipos:
numero -> Para el campo id, y datos numericos sin decimales
decimal -> Para datos con decimales
imagen -> Para el campo para guardar imagenes
textolargo -> Para campos con mucho texto (Más de 250 caracteres)
textocorto -> Para todo lo demas
fecha -> para datos de fecha. (En la base de datos se guarda como yyyy-mm-dd)
select -> para datos que se relacionan 1 a varios con otra tabla.
checkbox -> para datos que guardan si o no.

Notas adicionales:
------------------
Nota: Del vector campos, el primer elemento será SIEMPRE el id autonumérico de la tabla

Ejemplo de uso:
---------------
//Esto va a ser un ejemplo, para todo lo demas.
//Le digo la tabla de la base de datos
$tabla="comerciales";

//Vector de datos de la tabla para relacionar con la tabla 1 a varios. LA TABLA DEBE EXISTIR
$vector=array('id_tipocomision', 'nombre_tipocomision', 'tipo_comision');
$vector2=array('id_tipocomp', 'nombre_tipocomp', 'tipo_companias');

//Le digo los campos de la tabla
$campos=array('id_comercial', 'nombre_comercial', 'apellido_comercial', 'foto_comercial', 'observaciones_comercial', 'sueldo_comercial', 'fecha_comercial', $vector, $vector2, 'activado_comercial');

//Le digo los tipos de campos
$tipos=array('numero', 'textocorto', 'textocorto', 'imagen', 'textolargo', 'numero', 'fecha', 'select', 'select', 'checkbox');
//Le decimos los titulos de los campos como saldran en la web.
$titulos=array('id', 'Nombre', 'Apellidos', 'Foto', 'Observaciones', 'Sueldo Base', 'Fecha de alta', 'Tipo de comision', 'Tipo de compañia', 'En Vigor');

//Llamamos al CONSTRUCTOR DE LA CLASE '"Panel"', y Creamos todo.
$panel=new Panel($tabla, $campos, $tipos, $titulos);

//OPCIONAL: puedo pasarle un vector, diciendole, que campos quiero mostrar
$panel->limitarCamposEnMostrar=array('nombre', 'apellidos', 'username', 'email', 'movil');

//OPCIONAL: Cambio el orden para mostrar
$panel->orden='ASC';

//Mediante el método (funcion) llamada accion, hacemos el resto.
$panel->accion();
