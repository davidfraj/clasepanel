<?php
/*
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

*/

Class Panel{
	//Propiedades
	public $tabla; //Nombre de la tabla de la bbdd
	public $campos; //Array con los campos de la bbdd
	public $tipos; //Array con los tipos de datos
	public $titulos; //Array con los titulos
	public $conexion; //Objeto mysqli, con la conexion
	public $id; //El campo ID de esta tabla
	public $indice; //Contendra el fichero indice de la web (index.php por ejemplo)
	public $fichero; //Nombre del fichero al que llamo con 'p'
	public $registrosPorPagina; //Numero de registros por pagina para la paginacion
	public $camposPorPestaña; //Numero de campos por pestaña en la accion Ver
	public $nump; //Le digo el numero de pagina que estoy mostrando
	public $limitarCamposEnMostrar; //Array con los campos 
	public $orden; //Establezco el orden por defecto

	//Metodos
	//Método constructor
	public function __construct($tabla, $campos, $tipos, $titulos){
		//Llamo a la variable FUERA de la Class
		//Lo hago con la palabra "global"
		global $conexion;
		$this->conexion=$conexion;//Establezco la conexion
		//Guardo los datos para la tabla de la bbdd
		$this->tabla=$tabla;
		$this->campos=$campos;
		$this->tipos=$tipos;
		$this->titulos=$titulos;
		
		$this->id=$campos[0]; //El campo 0 es el id

		//Obtengo el fichero indice que llama a todo
		$partes=explode('/',$_SERVER['PHP_SELF']);
		$this->indice=$partes[count($partes)-1];

		//Obtengo el fichero al que llamo con 'p'
		global $p; //Lo he recogido previamente con $_GET['p']
		$this->fichero=$p;

		//Vamos a comprobar, que la tabla de mi variable $this->tabla, existe en la base de datos. Si no existe, la crearemos
		$sql="SHOW TABLES LIKE '$this->tabla'";
		$consulta = $this->conexion->query($sql);
		if(!$consulta->fetch_array()) {
			//Si entro en esta condicion, la tabla no existe.
			//Creo la tabla
			$sql='CREATE TABLE '.$this->tabla.' ('.$this->campos[0].' INT AUTO_INCREMENT PRIMARY KEY';
			//Bucle para crear los diferentes campos
			for($i=1;$i<count($this->campos);$i++){
				//Empieza el Switch
				switch($this->tipos[$i]){
					case 'textolargo':
						$sql.=', '.$this->campos[$i].' LONGTEXT';
						break;
					case 'decimal':
						$sql.=', '.$this->campos[$i].' FLOAT';
						break;
					case 'numero':
						$sql.=', '.$this->campos[$i].' INT(11)';
						break;	
					case 'select':
						$sql.=', '.$this->campos[$i][0].' INT(11)';
						break;	
					case 'fecha':
						$sql.=', '.$this->campos[$i].' DATE';
						break;
					case 'checkbox':
						$sql.=', '.$this->campos[$i].' INT(2)';
						break;	
					case 'imagen':
					case 'textocorto':
					default:
						$sql.=', '.$this->campos[$i].' VARCHAR(250)';
						break;
				}
				//Fin del switch
			}	
			$sql.=')';
			//Muestro (Solo cuando creo la tabla)
			//TODO: Tapar la linea siguiente al pasar a producción. 
			echo $sql;
			$this->conexion->query($sql);
		}
		//Establezco el numero de registros por pagina
		$this->registrosPorPagina=20;
		//Establezco el numero de campos por pestaña en Ver
		$this->camposPorPestaña=20;
		//Establezco la primera pagina a mostrar de la paginacion
		$this->nump=0;
		//Inicializo el vector de campos en mostrar
		$this->limitarCamposEnMostrar=array();
		//El orden por defecto sera DESC
		$this->orden='DESC';
	}	

	//////////////////////////////////////////////////////////////
	//Este metodo lo uso para mostrar una tabla con los contenidos
	public function mostrar($idFiltrado=0, $campoFiltrado=''){
		//Variables recogidas:
		//Contemplamos la posibilidad de filtrar por algun id. No es obligatorio
		//$idFiltrado: valor del id para filtrar
		//$campoFiltrado: Campo que tiene el id para filtrar. Normalmente una FOREIGN KEY

		echo '<a href="'.$this->indice.'?p='.$this->fichero.'&accion=insertar&nump='.$this->nump.'">';
		echo '<button type="button" class="btn btn-default">';
		echo '<span class="glyphicon glyphicon-plus"></span> Añadir';
		echo '</button>';
		echo '</a>';

		echo '<table class="table table-hover table-striped">';
		echo '<thead>';
		echo '<tr>';
		for($i=0;$i<count($this->titulos);$i++){
			//Preguntamos a ver si debemos o no mostrar el campo
			if(count($this->limitarCamposEnMostrar)>0){
				if(in_array($this->campos[$i],$this->limitarCamposEnMostrar)){
					$mostrarCampo=true;
				}else{
					$mostrarCampo=false;
				}
			}else{
				$mostrarCampo=true;
			}

			if($mostrarCampo){
				echo '<th>';
				echo $this->titulos[$i];
				echo '</th>';
			}
		}
		echo '<th>Acciones</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		//Si filtramos o no filtramos
		if($idFiltrado==0){
			$sql="SELECT * FROM ".$this->tabla." ORDER BY ".$this->id." ".$this->orden;
		}else{
			$sql="SELECT * FROM ".$this->tabla." WHERE ".$campoFiltrado."=".$idFiltrado." ORDER BY ".$this->id." ".$this->orden;
		}
		$consulta=$this->conexion->query($sql);

		//Extraigo datos para realizar la paginacion
		$totalRegistros=$consulta->num_rows;
		$numeroDePaginas=ceil($totalRegistros/$this->registrosPorPagina);
		$pagInicial=$this->nump*$this->registrosPorPagina;

		//Si filtramos o no filtramos
		if($idFiltrado==0){
			$sql="SELECT * FROM ".$this->tabla." ORDER BY ".$this->id." ".$this->orden." LIMIT ".$pagInicial.",".$this->registrosPorPagina;
		}else{
			$sql="SELECT * FROM ".$this->tabla." WHERE ".$campoFiltrado."=".$idFiltrado." ORDER BY ".$this->id." ".$this->orden." LIMIT ".$pagInicial.",".$this->registrosPorPagina." ORDER BY ".$this->id." ".$this->orden;
		}
		$consulta=$this->conexion->query($sql);
		while($fila=$consulta->fetch_array()){
			//No se cuantas columnas hay, habría que contarlas
			echo '<tr>';
			for($i=0;$i<$consulta->field_count;$i++){

				if(count($this->limitarCamposEnMostrar)>0){
					if(in_array($this->campos[$i],$this->limitarCamposEnMostrar)){
						$mostrarCampo=true;
					}else{
						$mostrarCampo=false;
					}
				}else{
					$mostrarCampo=true;
				}

				if($mostrarCampo){

					echo '<td>';
					switch($this->tipos[$i]){
						case 'imagen':
							echo '<img src="fotos/'.$fila[$this->campos[$i]].'" width="50" >';
							break;
						case 'fecha':
							$fecha = new DateTime($fila[$this->campos[$i]]);
							//Contamos el numero de caracteres
							if($fila[$this->campos[$i]]!='0000-00-00'){
								echo $fecha->format('d/m/Y');
							}else{
								echo '';
							}
							break;
						case 'select':
							$sql2="SELECT * FROM ".$this->campos[$i][2]." WHERE ".$this->campos[$i][0]."=".$fila[$this->campos[$i][0]];
							$consulta2=$this->conexion->query($sql2);
							$fila2=$consulta2->fetch_array();
							echo $fila2[$this->campos[$i][1]];
							break;	
						case 'checkbox':
							if($fila[$this->campos[$i]]=='1'){
								echo 'si';
							}else{
								echo 'no';
							}
							break;
						case 'textolargo':
						case 'textocorto':
						case 'numero':
						case 'decimal':
						default:
							echo $fila[$this->campos[$i]];
							break;
					}
					//Fin del switch
					echo '</td>';

				}

			}
			echo '<td>';
				//Llamo al método para mostrar acciones. Recibirá la fila que esta mostrando en ese momento
				$this->mostrarAcciones($fila);
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo '<ul class="pagination">';
			if($this->nump==0){
				$disabled=' class="disabled"';
			}else{
				$disabled='';
			}
			echo '<li '.$disabled.'><a href="'.$this->indice.'?p='.$this->fichero.'&accion=mostrar&nump='.($this->nump-1).'">&laquo;</a></li>';

			for ($i=0; $i < $numeroDePaginas; $i++) { 
				if($this->nump==$i){
					$active=' class="active"';
				}else{
					$active='';
				}
				echo '<li '.$active.'><a href="'.$this->indice.'?p='.$this->fichero.'&accion=mostrar&nump='.$i.'">'.($i+1).'</a></li>';
			}

			if($this->nump==($numeroDePaginas-1)){
				$disabled=' class="disabled"';
			}else{
				$disabled='';
			}
			echo '<li '.$disabled.'><a href="'.$this->indice.'?p='.$this->fichero.'&accion=mostrar&nump='.($this->nump+1).'">&raquo;</a></li>';
		echo '</ul>';

	}

	/////////////////////////////////////////////////
	// Metodo mostrarAcciones
	public function mostrarAcciones($fila){
		echo '<a href="'.$this->indice.'?p='.$this->fichero.'&accion=modificar&id='.$fila[$this->id].'&nump='.$this->nump.'">';
			echo '<span class="glyphicon glyphicon-wrench"></span>';
		echo '</a> ';
		echo '<a href="'.$this->indice.'?p='.$this->fichero.'&accion=borrar&id='.$fila[$this->id].'&nump='.$this->nump.'" onClick="return confirm(\'Estas seguro?\');">';
			echo '<span class="glyphicon glyphicon-remove-circle"></span>';
		echo '</a> ';
		echo '<a href="'.$this->indice.'?p='.$this->fichero.'&accion=ver&id='.$fila[$this->id].'&nump='.$this->nump.'">';
			echo '<span class="glyphicon glyphicon-search"></span>';
		echo '</a> ';
	}

	/////////////////////////////////////////////////
	// Metodo insertar
	public function insertar(){
		?>
		<form class="form-horizontal" role="form" method="post" action="<?php echo $this->indice;?>?p=<?php echo $this->fichero;?>&accion=insercion&nump=<?php echo $this->nump;?>" enctype="multipart/form-data">
		<div class="campos">
			<?php
			for($i=1;$i<count($this->campos);$i++){
			?>
			<div class="campo">
				<div class="form-group">
				<label for="<?php echo $this->campos[$i][0];?>" class="col-xs-2 control-label">
					<?php echo $this->titulos[$i];?>
				</label>
				<div class="col-xs-6">
					<?php
					switch($this->tipos[$i]){
						case 'imagen':
							?>
							<input type="file" class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" placeholder="<?php echo $this->titulos[$i];?>">
							<?php
							break;
						case 'textolargo':
							?>
							<textarea class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>"></textarea>
							<?php
							break;
						case 'fecha':
							?>
							<input type="text" class="form-control input-append date datepicker" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>">
							<?php
							break;
						case 'checkbox':
							?>
							<input type="checkbox" class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" value="1">
							<?php
							break;	
						case 'select':
							echo '<select name="'.$this->campos[$i][0].'" class="form-control" id="'.$this->campos[$i][0].'">';
							$sql2="SELECT * FROM ".$this->campos[$i][2]." ORDER BY ".$this->campos[$i][1]." ASC";
							$consulta2=$this->conexion->query($sql2);
							while($fila2=$consulta2->fetch_array()){
								echo '<option value="'.$fila2[$this->campos[$i][0]].'">';
								echo $fila2[$this->campos[$i][1]];
								echo '</option>';
							}
							echo '</select>';
							break;	
						case 'textocorto':
						case 'numero':
						case 'decimal':
						default:
							?>
							<input type="text" class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" placeholder="<?php echo $this->titulos[$i];?>">
							<?php
							break;
						}
					?>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	  <div class="form-group">
	    <div class="col-lg-12">
	    	<button type="submit" class="btn btn-default">Añadir</button>
	    	<a href="<?php echo $this->indice;?>?p=<?php echo $this->fichero;?>&accion=mostrar&nump=<?php echo $this->nump;?>">Cancelar</a>
	    </div>
	  </div>

	</form>

		<?php
	}

	/////////////////////////////////////////////////
	// Metodo insercion
	public function insercion(){	
		$sql="INSERT INTO ".$this->tabla."(";
		for($i=1;$i<count($this->campos);$i++){
			if($i>1){
				$sql.=", ";
			}

			switch($this->tipos[$i]){
				case 'select':
					$sql.=$this->campos[$i][0];
					break;	
				case 'imagen':
				case 'numero':
				case 'decimal':
				case 'fecha':
				case 'textolargo':
				case 'textocorto':
				default:
					$sql.=$this->campos[$i];
					break;
			}
			
		}
		$sql.=")VALUES(";
		for($i=1;$i<count($this->campos);$i++){
			if($i>1){
				$sql.=", ";
			}

			switch($this->tipos[$i]){
				case 'imagen':
					$nombreimg=time().'_'.$_FILES[$this->campos[$i]]['name'];
					move_uploaded_file($_FILES[$this->campos[$i]]['tmp_name'], 'fotos/'.$nombreimg);
					$sql.="'".$nombreimg."'";
					break;
				case 'numero':
				case 'decimal':
					$sql.=$_POST[$this->campos[$i]];
					break;
				case 'select':
					$sql.=$_POST[$this->campos[$i][0]];
					break;	
				case 'checkbox':	
					if($_POST[$this->campos[$i]]=='1'){
						$sql.="'1'";
					}else{
						$sql.="'0'";
					}
					break;
				case 'fecha':
				case 'textolargo':
				case 'textocorto':
				default:
					$sql.="'".$_POST[$this->campos[$i]]."'";
					break;
			}
			//Fin del switch

		}
		$sql.=")";
		echo $sql;

		//Ejecutare la consulta
		$consulta=$this->conexion->query($sql);

		//echo $sql;

		//Muestro un mensaje de resultado
		if($consulta==true){
			header('Location:'.$this->indice.'?p='.$this->fichero.'&accion=mostrar&nump='.$this->nump);
		}else{
			echo 'Error al realizar la inserción';
		}
	}

	/////////////////////////////////////////////////
	// Metodo borrar
	public function borrar(){
		//REcojemos el id del elemento que quiero borrar
		$id=$_GET['id'];
		//Establezco la consulta
		$sql="DELETE FROM ".$this->tabla." WHERE ".$this->id."=$id";
		//Ejecuto la consulta
		$consulta=$this->conexion->query($sql);
		//Muestro resultados
		if($consulta==true){
			header('Location:'.$this->indice.'?p='.$this->fichero.'&accion=mostrar&nump='.$this->nump);
		}else{
			echo 'Error al eliminar';
		}
	}

	/////////////////////////////////////////////////
	// Metodo modificar
	public function modificar(){
		//Recojo el id de la barra de direcciones
		$id=$_GET['id'];
		?>
		<form class="form-horizontal" role="form" method="post" action="<?php echo $this->indice;?>?p=<?php echo $this->fichero;?>&accion=modificacion&id=<?php echo $id;?>&nump=<?php echo $this->nump;?>" enctype="multipart/form-data">
			<?php
			//Establezco la consulta
			$sql="SELECT * FROM ".$this->tabla." WHERE ".$this->id."=$id";
			//Ejecuto la consulta
			$consulta=$this->conexion->query($sql);
			//Extraigo el unico registro de la base de datos
			$fila=$consulta->fetch_array();
			?>
			<div class="campos">
			<?php
			for($i=1;$i<count($this->campos);$i++){
				?>
				<div class="campo">
					<div class="form-group">
						<label for="<?php echo $this->campos[$i][0];?>" class="col-xs-2 control-label">
							<?php echo $this->titulos[$i];?>
						</label>
						<div class="col-xs-6">
							<?php
							switch($this->tipos[$i]){
								case 'imagen':
									echo '<img src="fotos/'.$fila[$this->campos[$i]].'" width="100" class="img-circle">';
									?>
									<input type="file" class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" placeholder="<?php echo $this->titulos[$i];?>"><input type="checkbox" name="<?php echo $this->campos[$i];?>_quitar">  Marcar para quitar imagen
									<?php
									break;
								case 'textolargo':
								?>
									<textarea class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>"><?php echo $fila[$this->campos[$i]]; ?></textarea>
									<?php
									break;
								case 'fecha':
									?>
									<input type="text" class="form-control input-append date datepicker" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" value="<?php echo $fila[$this->campos[$i]];?>">
									<?php
									break;
								case 'checkbox':
									if($fila[$this->campos[$i]]=='1'){
										$checked=' checked';
									}else{
										$checked='';
									}
									?>
									<input type="checkbox" class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" value="si" <?php echo $checked;?>>
									<?php
									break;		
								case 'select':
									echo '<select name="'.$this->campos[$i][0].'" class="form-control" id="'.$this->campos[$i][0].'">';
									$sql2="SELECT * FROM ".$this->campos[$i][2]." ORDER BY ".$this->campos[$i][1]." ASC";
									$consulta2=$this->conexion->query($sql2);
									while($fila2=$consulta2->fetch_array()){
										if($fila[$this->campos[$i][0]]==$fila2[$this->campos[$i][0]]){
											$selected=' selected';
										}else{
											$selected='';
										}
										echo '<option '.$selected.' value="'.$fila2[$this->campos[$i][0]].'">';
										echo $fila2[$this->campos[$i][1]];
										echo '</option>';
									}
									echo '</select>';
									break;		
								case 'textocorto':
								case 'numero':
								case 'decimal':
								default:
									?>
									<input type="text" class="form-control" id="<?php echo $this->campos[$i];?>" name="<?php echo $this->campos[$i];?>" placeholder="<?php echo $this->titulos[$i];?>" value="<?php echo $fila[$this->campos[$i]];?>">
									<?php
									break;
								}
								?>
						</div>
					</div>
				</div>
				<?php
				}
				?>
			</div>
			<div class="form-group">
				<div class="col-lg-12">
					<button type="submit" class="btn btn-default">Guardar</button>
					<a href="<?php echo $this->indice;?>?p=<?php echo $this->fichero;?>&accion=mostrar&nump=<?php echo $this->nump;?>">Cancelar</a>
				</div>
			</div>
		</form>
		<?php
	}

	/////////////////////////////////////////////////
	// Metodo modificacion
	public function modificacion(){
		//Recojo el id de la barra de direcciones
		$id=$_GET['id'];
		//Establezco la consulta
		$sql="UPDATE ".$this->tabla." SET ";
		for($i=1;$i<count($this->campos);$i++){
			
			switch($this->tipos[$i]){
				case 'imagen':
					$nombreimg=$_FILES[$this->campos[$i]]['name'];
					if(strlen($nombreimg)>0){
						if($i>1){
							$sql.=', ';
						}
						$nombreimg=time().'_'.$_FILES[$this->campos[$i]]['name'];
						move_uploaded_file($_FILES[$this->campos[$i]]['tmp_name'], 'fotos/'.$nombreimg);
						$sql.=$this->campos[$i];
						$sql.='=';
						$sql.="'".$nombreimg."'";
					}elseif(isset($_POST[$this->campos[$i].'_quitar'])){
						if($i>1){
							$sql.=', ';
						}
						$sql.=$this->campos[$i];
						$sql.='=';
						$sql.="''";
					}
					break;
				case 'numero':
				case 'decimal':
					if($i>1){
						$sql.=', ';
					}
					$sql.=$this->campos[$i];
					$sql.='=';
					$sql.=$_POST[$this->campos[$i]];
					break;
				case 'select':
					if($i>1){
						$sql.=', ';
					}
					$sql.=$this->campos[$i][0];
					$sql.='=';
					$sql.=$_POST[$this->campos[$i][0]];
					break;
				case 'checkbox':
					if($i>1){
						$sql.=', ';
					}
					$sql.=$this->campos[$i];
					$sql.='=';	
					if($_POST[$this->campos[$i]]=='1'){
						$sql.="'1'";
					}else{
						$sql.="'0'";
					}
					break;	
				case 'fecha':
				case 'textocorto':
				case 'textolargo':
				default:
					if($i>1){
						$sql.=', ';
					}
					$sql.=$this->campos[$i];
					$sql.='=';
					$sql.="'".$_POST[$this->campos[$i]]."'";
					break;
			}
			//Fin del Switch
			
		}
		$sql.=' WHERE '.$this->id.'='.$id;
		//Ejecuto la consulta
		$consulta=$this->conexion->query($sql);
		//Muestro resultados o redirecciono
		if($consulta==true){
			header('Location:'.$this->indice.'?p='.$this->fichero.'&accion=mostrar&nump='.$this->nump);
		}else{
			echo 'Error al modificar';
			echo $sql;
		}
		
	}

	/////////////////////////////////////////////////
	// Metodo ver
	public function ver(){
		//Recojo el id de la barra de direcciones
		$id=$_GET['id'];
		//Establezco la consulta
		$sql="SELECT * FROM ".$this->tabla." WHERE ".$this->id."=$id";
		//Ejecuto la consulta
		$consulta=$this->conexion->query($sql);
		//Extraigo el unico registro de la base de datos
		$fila=$consulta->fetch_array();
		//Hacemos un calculo para saber cuantas pestañas queremos mostrar:
		$numeroDeCampos=count($this->campos);
		$numeroPestañas=ceil($numeroDeCampos/$this->camposPorPestaña);
		if(isset($_GET['numpes'])){
			$numpes=$_GET['numpes'];
		}else{
			$numpes=1;
		}
		?>
		<ul class="nav nav-tabs">
			<li><a href="#">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
			<?php
			for ($i=1; $i <= $numeroPestañas; $i++) { 
				if($i==$numpes){
					$active='active';
				}else{
					$active='';
				}
				echo '<li class="'.$active.'"><a href="'.$this->indice.'?p='.$this->fichero.'&accion=ver&id='.$id.'&nump='.$this->nump.'&numpes='.$i.'">Pagina '.$i.'</a></li>';
			}
			?>
		</ul>
		<br>
		<?php
		//Calculamos que campos mostrar en que pestaña
		$inicio=(($numpes-1)*$this->camposPorPestaña)+1;
		$final=$inicio+$this->camposPorPestaña;
		if($final>count($this->campos)){
			$final=count($this->campos);
		}
		?>
		<div class="campos">
			<?php
			for($i=$inicio;$i<$final;$i++){
			?>
			<div class="campo">
				<span class="col-xs-2">
					<?php echo $this->titulos[$i];?>
				</span>
				<span class="">
				<?php
				switch($this->tipos[$i]){
					case 'imagen':
						echo '<img src="fotos/'.$fila[$this->campos[$i]].'" width="100" class="img-circle">';
						break;
					case 'checkbox':
						if($fila[$this->campos[$i]]=='1'){
							$checked=' si';
						}else{
							$checked='no';
						}
						echo $checked;
						break;		
					case 'select':
						$sql2="SELECT * FROM ".$this->campos[$i][2]." ORDER BY ".$this->campos[$i][1]." ASC";
						$consulta2=$this->conexion->query($sql2);
						$fila2=$consulta2->fetch_array();
						echo $fila2[$this->campos[$i][1]];
						break;	
					case 'textolargo':
					case 'fecha':	
					case 'textocorto':
					case 'numero':
					case 'decimal':
					default:
						echo $fila[$this->campos[$i]];
						break;
					}
					?>
					<br>
				</span>
			</div>
			<?php
			}
			?>
		</div>
		<br>
		<a href="<?php echo $this->indice;?>?p=<?php echo $this->fichero;?>&accion=mostrar&nump=<?php echo $this->nump;?>">Volver</a>
		<?php
	}

	/////////////////////////////////////////////////
	// Metodo accion: Para saber, que accion realiza el panel
	public function accion($idFiltrado=0, $campoFiltrado=''){
		//La variable filtradoMostrar, servirá para filtrar por el id del campo relacionado
		if(isset($_GET['accion'])){
			$accion=$_GET['accion'];
		}else{
			$accion='mostrar';
		}
		//Esto es para mantenernos en un numero de pagina de la paginacion u otro
		if(isset($_GET['nump'])){
			$this->nump=$_GET['nump'];
		}else{
			$this->nump=0;
		}
		switch($accion){
			case 'mostrar':
				$this->mostrar($idFiltrado, $campoFiltrado);
				break;
			case 'insertar':
				$this->insertar();
				break;
			case 'insercion':
				$this->insercion();
				break;
			case 'borrar':
				$this->borrar();
				break;
			case 'modificar':
				$this->modificar();
				break;
			case 'modificacion':
				$this->modificacion();
				break;	
			case 'ver':
				$this->ver();
				break;
		}
	}

}

?>