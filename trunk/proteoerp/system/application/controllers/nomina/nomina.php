<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Nomina extends Controller {
	var $mModulo = 'NOMI';
	var $titp    = 'NOMINAS GUARDADAS';
	var $tits    = 'NOMINAS GUARDADAS';
	var $url     = 'nomina/nomina/';

	function Nomina(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'NOMI', $ventana=0 );
	}

	function index(){
		if ( !$this->db->table_exists('view_nomina') ) {
			$mSQL = "
			CREATE ALGORITHM = UNDEFINED VIEW view_nomina AS
			SELECT a.contrato, a.trabaja, b.nombre, a.numero, a.frecuencia, a.fecha, a.fechap,
				a.estampa, a.usuario, a.transac,
				SUM(a.valor*(MID(concepto,1,1)<>'9' AND valor>0 AND concepto<>'PRES')) asigna,
				SUM(a.valor*(MID(concepto,1,1)<>'9' AND valor<0 AND concepto<>'PRES')) deduc,
				SUM(a.valor*(MID(concepto,1,1)<>'9')) total,
				SUM(a.valor*(concepto='PRES')) presta,
				SUM(a.valor*(MID(concepto,1,1)='9')) patronal
			FROM nomina a join noco b on a.contrato = b.codigo
			GROUP BY a.numero";
			$this->db->query($mSQL);
		}

		if ( !$this->datasis->iscampo('nomina','id') ) {
			$this->db->simple_query('ALTER TABLE nomina DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE nomina ADD INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE nomina ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		//$this->datasis->creaintramenu(array('modulo'=>'000','titulo'=>'<#titulo#>','mensaje'=>'<#mensaje#>','panel'=>'<#panal#>','ejecutar'=>'<#ejecuta#>','target'=>'popu','visible'=>'S','pertenece'=>'<#pertenece#>','ancho'=>900,'alto'=>600));
		$this->datasis->modintramenu( 900, 600, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//******************************************************************
	// Layout en la Ventana
	//
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname']);

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'regene' , 'img'=>'images/repara.png',  'alt' => 'Regenerar Nomina', 'label'=>'Regenerar Nomina', 'tema'=>'anexos'));
		$grid->wbotonadd(array('id'=>'imprime', 'img'=>'assets/default/images/print.png','alt' => 'Imprimir recibos', 'label'=>'Imprimir Prenomina'));
		$WestPanel = $grid->deploywestp();

		$adic = array(
			array('id'=>'fedita',  'title'=>'Agregar/Editar Registro'),
			array('id'=>'fshow' ,  'title'=>'Mostrar Registro'),
			array('id'=>'fborra',  'title'=>'Eliminar Registro')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$funciones = '
		function ltransac(el, val, opts){
			var meco=\'<div><a href="#" onclick="tconsulta(\'+"\'"+el+"\'"+\');">\' +el+ \'</a></div>\';
			return meco;
		};
		';

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('NOMI', 'JQ');
		$param['otros']       = $this->datasis->otros('NOMI', 'JQ');
		$param['funciones']   = $funciones;
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$param['tamano']      = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//******************************************************************
	// Funciones de los Botones
	//
	function bodyscript( $grid0 ){
		$bodyscript = '<script type="text/javascript">';

		$bodyscript .= '
		function tconsulta(transac){
			if (transac)	{
				window.open(\''.site_url('contabilidad/casi/localizador/transac/procesar').'/\'+transac, \'_blank\', \'width=800, height=600, scrollbars=yes, status=yes, resizable=yes,screenx=((screen.availHeight/2)-300), screeny=((screen.availWidth/2)-400)\');
			} else {
				$.prompt("<h1>Transaccion invalida</h1>");
			}
		};';

		$bodyscript .= '
		function nominaadd(){
			$.post("'.site_url($this->url.'dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function nominaedit(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/modify').'/"+id, function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function nominashow(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/show').'/"+id, function(data){
					$("#fshow").html(data);
					$("#fshow").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';


		$bodyscript .= '
		$("#regene").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				var mrege =
				{
					state0: {
						html: "<h1>Regenerar Nomina "+ret.numero+"</h1>Util para asegurar que todos los movimientos relacionados estan efectivamente cargados en el sistema administrativo.",
						buttons: { Regenerar: true, Cancelar: false },
						submit: function(e,v,m,f){
							if (v) {
								mId = id;
								$.post("'.site_url($this->url.'nomirege').'/"+ret.numero, function(data){
									try{
										var json = JSON.parse(data);
										if (json.status == "A"){
											$(\'#in_prome2\').text(json.mensaje);
											$.prompt.goToState(\'state1\');
											jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
										}else{
											$(\'#in_prome2\').text(json.mensaje);
											$.prompt.goToState(\'state1\');
										}
									}catch(e){
										$("#fborra").html(data);
										$("#fborra").dialog( "open" );
									}
								});
								return false;
							}
						}
					},
					state1: {
						html: "<h1>Resultado</h1><span id=\'in_prome2\'></span>",
						focus: 1,
						buttons: { Ok:true }
					}
				};

				$.prompt(mrege);
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		});';

		$bodyscript .= '
		function nominadel() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				var mrever =
				{
					state0: {
						html: "<h1>Reverar Pre-Nomina "+ret.numero+"</h1>",
						buttons: { Reversar: true, Cancelar: false },
						submit: function(e,v,m,f){
							if (v) {
								mId = id;
								$.post("'.site_url($this->url.'nomirev').'/"+ret.numero, function(data){
									try{
										var json = JSON.parse(data);
										if (json.status == "A"){
											$(\'#in_prome2\').text(json.mensaje);
											$.prompt.goToState(\'state1\');
											jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
										}else{
											$(\'#in_prome2\').text(json.mensaje);
											$.prompt.goToState(\'state1\');
											apprise("Registro no se puede eliminado");
										}
									}catch(e){
										$("#fborra").html(data);
										$("#fborra").dialog( "open" );
									}
								});
								return false;
							}
						}
					},
					state1: {
						html: "<h1>Resultado</h1><span id=\'in_prome2\'></span>",
						focus: 1,
						buttons: { Ok:true }
					}
				};

				$.prompt(mrever);
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};
		';

		//Wraper de javascript
		$bodyscript .= '
		$(function(){
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = jQuery("#newapi'.$grid0.'");
			var s;
			var allFields = $( [] ).add( ffecha );
			var tips = $( ".validateTips" );
			s = grid.getGridParam(\'selarrrow\');
			';

		$bodyscript .= '
		jQuery("#imprime").click( function(){
			window.open(\''.site_url('formatos/descargar/RECIBO/').'/\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes\');
		});';

		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Guardar": function() {
					var bValid = true;
					var murl = $("#df1").attr("action");
					allFields.removeClass( "ui-state-error" );
					$.ajax({
						type: "POST", dataType: "html", async: false,
						url: murl,
						data: $("#df1").serialize(),
						success: function(r,s,x){
							try{
								var json = JSON.parse(r);
								if (json.status == "A"){
									apprise("Registro Guardado");
									$( "#fedita" ).dialog( "close" );
									grid.trigger("reloadGrid");
									'.$this->datasis->jwinopen(site_url('formatos/ver/NOMINA').'/\'+res.id+\'/id\'').';
									return true;
								} else {
									apprise(json.mensaje);
								}
							}catch(e){
								$("#fedita").html(r);
							}
						}
					})
				},
				"Cancelar": function() {
					$("#fedita").html("");
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				$("#fedita").html("");
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});';

		$bodyscript .= '
		$("#fshow").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fshow").html("");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#fshow").html("");
			}
		});';

		$bodyscript .= '
		$("#fborra").dialog({
			autoOpen: false, height: 300, width: 400, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fborra").html("");
					jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
				$("#fborra").html("");
			}
		});';

		$bodyscript .= '});';
		$bodyscript .= '</script>';

		return $bodyscript;
	}

	//******************************************************************
	// Definicion del Grid y la Forma
	//
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('numero');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('contrato');
		$grid->label('Contrato');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));

		$grid->addField('frecuencia');
		$grid->label('Frecuencia');
		$grid->params(array(
			'align'         => '"center"',
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));

		$grid->addField('total');
		$grid->label('Total');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('asigna');
		$grid->label('Asignaciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('deduc');
		$grid->label('Deduciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('presta');
		$grid->label('Prestamos');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('patronal');
		$grid->label('Patronal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('fechap');
		$grid->label('Fec.Pago');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('trabaja');
		$grid->label('Trabajador');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('transac');
		$grid->label('Transaci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
			'formatter'     => 'ltransac'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');


		$grid->setOnSelectRow('
			function(id){
				if(id){
					var ret = jQuery(this).jqGrid(\'getRowData\',id);
					var num = ret.numero;
					$.ajax({
						url: "'.site_url($this->url).'/tabla/"+encodeURIComponent(num),
						success: function(msg){
							$("#ladicional").html(msg);
						}
					});
				}
			}'
		);

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    false ); //$this->datasis->sidapuede('NOMI','INCLUIR%' ));
		$grid->setEdit(   false ); //$this->datasis->sidapuede('NOMI','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('NOMI','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('NOMI','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions('addfunc: nominaadd, editfunc: nominaedit, delfunc: nominadel, viewfunc: nominashow');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/*******************************************************************
	* Busca la data en el Servidor por json
	*/
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('view_nomina');

		$response   = $grid->getData('view_nomina', array(array()), array(), false, $mWHERE, 'numero', 'DESC' );
		$rs = $grid->jsonresult( $response);

		echo $rs;
	}

	//******************************************************************
	// Guarda la Informacion
	//
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			echo 'Deshabilitado';
		}elseif($oper == 'edit'){
			echo 'Deshabilitado';
		}elseif($oper == 'del'){
			echo 'Deshabilitado';
		}
	}

	//******************************************************************
	//  REVERSAR NOMINA
	//
	function nomirev( $nomina = 0 ) {
		if ( $nomina == 0)
			$nomina = $this->uri->segment($this->uri->total_segments());

		if ( $nomina == 0) {
			$rt=array(
				'status' =>'B',
				'mensaje'=>'Numero de nomina invalido',
				'pk'     => $nomina
			);
			echo json_encode($rt);
			return true;
		}

		if (intval($this->datasis->dameval('SELECT COUNT(*) AS cana FROM nomina WHERE numero="'.$nomina.'"')) == 0 ) {
			$rt=array(
				'status' =>'B',
				'mensaje'=>'NO EXISTE NINGUNA NOMINA CON ESE NUMERO!',
				'pk'     => $nomina
			);
			echo json_encode($rt);
			return true;
		}

		if (intval($this->datasis->dameval('SELECT COUNT(*) AS cana FROM nomina')) == 0 ){
			$rt=array(
				'status' =>'B',
				'mensaje'=>'No hay ninguna Nomina Generada; genere una primero',
				'pk'     => $nomina
			);
			echo json_encode($rt);
			return true;
		}

		$mSQL  = "SELECT fecha, fechap, contrato, trabaja, transac FROM nomina WHERE numero=".$nomina." LIMIT 1";
		$mreg  = $this->datasis->damereg($mSQL);

		$fecha    = $mreg['fecha'];
		$fechap   = $mreg['fechap'];
		$contrato = $mreg['contrato'];
		$trabaja  = $mreg['trabaja'];
		$transac  = $mreg['transac'];
		$gsernum  = '';

		$mSQL = "SELECT b.tipo FROM prenom a JOIN noco b ON a.contrato=b.codigo LIMIT 1";
		$frec = $this->datasis->dameval($mSQL);

		// VERIFICA SI NO ESTA PAGADA
		$mSQL = "SELECT abonos FROM sprm WHERE transac='".$transac."' AND tipo_doc IN ('ND') ";
		if ($this->datasis->dameval($mSQL) > 0) {
			echo "NOMINA TIENE CANCELACIONES;NO SE PUEDE ELIMINAR ";
			return true;
		}

		// ELIMINA POR TRANSACCION GENERAR ITEMS
		$this->db->query("DELETE FROM gser   WHERE transac='".$transac."'");
		$this->db->query("DELETE FROM gitser WHERE transac='".$transac."'");

		// GENERA CXP
		$this->db->query("DELETE FROM sprm WHERE transac='".$transac."'");

		// PRESTAMOS
		$mSQL = "SELECT * FROM smov WHERE num_ref=".$nomina." AND transac='".$transac."' ";
		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){

				$COD_CLI  = $row->cod_cli;
				$TIPO_DOC = $row->tipo_doc;
				$NUMERO   = $row->numero;
				$FECHA    = $row->fecha;
				$MONTO    = $row->monto;

				$mSQL= "DELETE FROM smov
						WHERE cod_cli='".$COD_CLI."' AND
							tipo_doc='".$TIPO_DOC."' AND fecha='".$FECHA."'
							AND numero='".$NUMERO."' AND transac='".$transac."' ";
				$this->db->query($mSQL);

				// DESCARGA EL MOVIMIENTO EN ITCCLI
				$mSQL= "SELECT abono, tipo_doc, numero, cod_cli, fecha FROM itccli
						WHERE numccli='".$NUMERO."' AND tipoccli='".$TIPO_DOC."'
						AND cod_cli='".$COD_CLI."' AND transac='".$transac."' ";
				$mREG = $this->datasis->damereg($mSQL);

				$mSQL= "DELETE FROM itccli
						WHERE numccli='".$NUMERO."' AND tipoccli='".$TIPO_DOC."'
						AND cod_cli='".$COD_CLI."' AND transac='".$transac."' ";
				$this->db->query($mSQL);

				// ACTUALIZA EL DOCUMENTO ORIGEN
				$mSQL = "UPDATE smov SET abonos=abonos-".$mREG['abono']."
				WHERE tipo_doc='".$mREG['tipo_doc']."' AND
				numero='".$mREG['numero']."' AND
				cod_cli='".$mREG['cod_cli']."' AND fecha='".$mREG['fecha']."'";

				$this->db->query($mSQL);
			}
		}

		$this->nomprenom($nomina);

		$mSQL  = "DELETE FROM nomina WHERE numero='${nomina}'";
		$this->db->query($mSQL);

		logusu('nomi',"NOMINA ${nomina} REVERSADA");

		$rt=array(
			'status' =>'A',
			'mensaje'=>'Nomina Reversada',
			'pk'     => $nomina
		);
		echo json_encode($rt);


	}

	//***************************************
	//
	//  DEVUELVE LA NOMINA A PRENOMINA
	//
	function nomprenom( $nomina) {

		$mreg = $this->datasis->damereg("SELECT fecha, fechap, contrato, trabaja, frecuencia FROM nomina WHERE numero='".$nomina."' LIMIT 1");

		$fecha    = $mreg['fecha'];
		$fechap   = $mreg['fechap'];
		$contrato = $mreg['contrato'];
		$trabaja  = $mreg['trabaja'];
		$frec     = $mreg['frecuencia'];

		$dbcontrato= $this->db->escape($contrato);
		$dbtrabaja = $this->db->escape($trabaja);
		$dbnomina  = $this->db->escape($nomina);
		$dbfecha   = $this->db->escape($fecha);
		$dbfechap  = $this->db->escape($fechap);
		$dbfrec    = $this->db->escape($frec);

		// MANDA LA NOMINA DE REGRESO A PRENOM
		$mSQL = "TRUNCATE prenom ";
		$this->db->query($mSQL);

		$mSQL ="INSERT IGNORE INTO prenom (contrato, codigo, nombre, concepto, tipo, descrip, grupo, formula, monto, fecha, valor,fechap, trabaja )
			SELECT contrato, codigo, nombre, concepto, tipo, descrip, grupo, formula, monto, fecha, valor, fechap, trabaja
			FROM nomina
			WHERE numero=${dbnomina} AND concepto<>'PRES' ";
		$this->db->query($mSQL);

		$mSQL = "INSERT IGNORE INTO prenom (contrato, codigo, nombre, concepto, grupo, tipo, descrip, formula, monto, fecha, fechap )
			SELECT ${dbcontrato}, b.codigo, CONCAT(RTRIM(b.apellido),'/',b.nombre) nombre,
			a.concepto, c.grupo, a.tipo, a.descrip, a.formula, 0, ${dbfecha}, ${dbfechap}
			FROM asig a JOIN pers b ON a.codigo=b.codigo
			JOIN conc c ON a.concepto=c.concepto
			WHERE b.tipo=${dbfrec} AND b.contrato=${dbcontrato} AND b.status='A' ";
		$this->db->query($mSQL);

		$mSQL = "INSERT IGNORE INTO prenom (contrato, codigo,nombre, concepto, grupo, tipo, descrip, formula, monto, fecha, fechap )
			SELECT ${dbcontrato}, b.codigo, CONCAT(RTRIM(b.apellido),'/',b.nombre) nombre,
				a.concepto, a.grupo, a.tipo, a.descrip, a.formula, 0, ${dbfecha}, ${dbfecha}
			FROM conc a JOIN itnoco c ON a.concepto=c.concepto
			JOIN pers b ON b.contrato=c.codigo
			WHERE c.codigo=${dbcontrato} AND b.status='A' ";
		$this->db->query($mSQL);

		$this->db->query("UPDATE prenom SET trabaja=${dbtrabaja}");

		$this->load->library('pnomina');
		$this->pnomina->creapretab();
		$this->pnomina->llenapretab();
	}



	//******************************************************************
	//  REGENERA LA NOMINA SIN BORRARLA
	//
	function nomirege( $nomina ) {

		$mNOMI  = $this->datasis->dameval("SELECT ctaac FROM conc WHERE tipo='A' AND ctaac IS NOT NULL AND ctaac<>'' LIMIT 1");
		$dbmNOMI= $this->db->escape($mNOMI);

		$dbnomina  = $this->db->escape($nomina);

		//COLOCAR + LAS NOMINAS
		$mSQL = "UPDATE nomina SET valor=ABS(valor) WHERE MID(concepto,1,1)='9' ";
		$this->db->query($mSQL);

		$mSQL = "SELECT COUNT(*) AS val FROM nomina WHERE numero=${dbnomina}";
		$val  = intval($this->datasis->dameval($mSQL));
		if($val == 0){
			$rt=array(
				'status' =>'B',
				'mensaje'=>'NO EXISTE NINGUNA NOMINA CON EL NUMERO '.$nomina,
				'pk'     => $nomina
			);
			echo json_encode($rt);
			return true;
		}

		$mreg = $this->datasis->damereg("SELECT transac, fecha FROM nomina WHERE numero=${dbnomina}");
		$mTRANSAC = $mreg['transac'];
		$FECHA    = $mreg['fecha'];
		$dbtransac= $this->db->escape($mTRANSAC);

		//GENERA EL ENCABEZADO DE GSER
		$mSQL = "DELETE FROM gser WHERE  transac=${dbtransac}";
		$this->db->query($mSQL);

		$mSQL= "INSERT INTO gser (fecha, numero, proveed, nombre, vence, totpre,  totiva, totbruto, reten, totneto, codb1, tipo1, cheque1, monto1, credito, anticipo, orden, tipo_doc, usuario, estampa, transac)
				SELECT a.fechap fecha, a.numero numero, b.ctaac proveed, d.nombre, a.fechap vence, SUM(a.valor) totpre, 0 totiva, SUM(a.valor) totbruto, 0 reten, SUM(a.valor) totneto, '' codb1, '' tipo1, '' cheque1,
					(
						SELECT ABS(SUM(bbb.monto)) FROM (SELECT sum(d.valor) monto FROM nomina d JOIN conc e ON d.concepto=e.concepto JOIN pers f ON d.codigo=f.codigo WHERE d.valor<>0 AND e.tipod!='G' AND d.numero=${dbnomina}
						UNION ALL
						SELECT SUM(valor) monto FROM nomina g WHERE g.numero=${dbnomina} AND g.concepto='PRES' ) bbb)*(b.ctaac=${dbmNOMI}
					) monto1,
					SUM(a.valor)+(
						SELECT SUM(valor) FROM (SELECT SUM(valor) valor FROM nomina a JOIN conc b ON a.concepto=b.concepto JOIN pers c ON a.codigo=c.codigo WHERE valor<>0 AND tipod!='G' AND a.numero=${dbnomina}
						UNION ALL
						SELECT SUM(valor) FROM nomina a WHERE a.numero=${dbnomina} AND concepto='PRES' ) aaa)*(b.ctaac=${dbmNOMI}) credito,
					0 anticipo, '', 'GA', a.usuario, a.estampa, a.transac FROM nomina a JOIN conc b ON a.concepto=b.concepto
				JOIN pers c ON a.codigo=c.codigo JOIN sprv d ON ctaac=d.proveed
				WHERE valor<>0 AND tipod='G' AND a.numero=${dbnomina} GROUP BY ctaac ";
		$this->db->query($mSQL);

		//GENERA EL DETALLE DE GSER
		$mSQL = "DELETE FROM gitser WHERE transac=${dbtransac}";
		$this->db->query($mSQL);

		$mSQL= "INSERT INTO gitser (fecha, numero, proveed, codigo, descrip, precio,   iva, importe, unidades, fraccion, almacen, departa, sucursal, usuario, estampa, transac)
				SELECT fechap, a.numero,ctaac, ctade,   CONCAT(RTRIM(b.descrip),' ',COALESCE(d.depadesc,'')), SUM(valor), 0, SUM(valor), 0,        0,        '',     d.enlace, c.sucursal, a.usuario, a.estampa, a.transac
				FROM nomina a
				JOIN conc b ON a.concepto=b.concepto
				JOIN pers c ON a.codigo=c.codigo
				LEFT JOIN depa d ON c.depto=d.departa
				WHERE a.valor<>0 AND b.tipod='G' AND a.numero=${dbnomina}
				GROUP BY ctaac,ctade, d.enlace ";

		$this->db->query($mSQL);

		//Borras los que empiezan por N
		$mSQL = "DELETE FROM sprm WHERE transac=${dbtransac} AND numero='N".substr($nomina,1,7)."' ";
		$this->db->query($mSQL);

		// GENERA LAS ND EN PROVEEDORES SPRM
		// debe revisar cuales esta
		$mSQL= "SELECT b.ctaac, a.fechap fecha, SUM(valor) valor, d.nombre, b.descrip
				FROM nomina a JOIN conc b ON a.concepto=b.concepto
				JOIN pers c ON a.codigo=c.codigo
				JOIN sprv d ON b.ctaac=d.proveed
				WHERE a.valor<>0 AND b.tipod='P' AND b.tipoa='P' AND a.numero=${dbnomina}
				GROUP BY b.ctaac ";

		$query = $this->db->query($mSQL);
		if($query->num_rows() > 0){
			foreach ($query->result() as $row){

				$data = array();
				$data['cod_prv']  = $row->ctaac;
				$data['nombre']   = $row->nombre;
				$data['tipo_doc'] = 'ND';
				$data['fecha']    = $row->fecha;
				$data['monto']    = abs($row->valor);
				$data['vence']    = $row->fecha;
				$data['tipo_ref'] = 'GA';
				$data['num_ref']  = $nomina;
				$data['observa1'] = $row->descrip;
				$data['observa2'] = 'NOMINA';
				$data['reteiva']  = 0;
				$data['codigo']   = 'NOCON';
				$data['descrip']  = 'NOMINA';
				$data['impuesto'] = 0;

				$dbctaac  = $this->db->escape($row->ctaac);
				$itdbfecha= $this->db->escape($row->fecha);
				$mSQL = "SELECT COUNT(*) AS val FROM sprm WHERE transac=${dbtransac} AND cod_prv=${dbctaac} AND tipo_doc='ND' AND fecha=${itdbfecha} AND numero<>${dbnomina}";
				$val  = intval($this->datasis->dameval($mSQL));
				if($val == 0){
					//SI NO ESTA
					$mCONTROL = $this->datasis->fprox_numero('nsprm');
					$mNOTADEB = $this->datasis->fprox_numero('num_nd');

					$data['numero']  = $mNOTADEB;
					$data['abonos']  = 0;
					$data['control'] = $mCONTROL;

					$this->db->insert('sprm', $data);
				}else{
					$mSQL = "SELECT numero, abonos, control, id FROM sprm WHERE transac=${dbtransac} AND cod_prv=${dbctaac} AND tipo_doc='ND' AND fecha=${itdbfecha}";
					$mREG = $this->datasis->damereg($mSQL);
					$data['numero']  = $mREG['numero'];
					$data['abonos']  = $mREG['abonos'];
					$data['control'] = $mREG['control'];

					$this->db->update('sprm', $data, 'id = '.$mREG['id']);
				}
				$this->db->query($mSQL);
			}
		}

		$mSQL = "DELETE FROM sprm WHERE transac=${dbtransac} AND numero=${dbnomina}";
		$this->db->query($mSQL);

		$mSQL= "INSERT IGNORE INTO sprm (tipo_doc, fecha, numero, cod_prv, nombre, vence, monto, impuesto, tipo_ref, num_ref, codigo, descrip, usuario, estampa, transac, observa1 )
				SELECT 'ND' tipo_doc, fecha, ${dbnomina}  numero, proveed, nombre, vence, credito, 0, 'GA', '' ,'NOCON', 'NOMINA', usuario, estampa, transac, 'NOMINA '
				FROM gser WHERE tipo_doc='GA' AND numero=${dbnomina}";
		$this->db->query($mSQL);

		// PRESTAMOS
		$mSQL= "SELECT
					c.cod_cli, c.nombre, c.tipo_doc, c.numero, c.fecha, a.fechap,
					a.codigo, b.cuota, a.valor, a.estampa, a.usuario, a.hora, a.transac,
					IF(c.monto-c.abonos-b.cuota>0,b.cuota,c.monto-c.abonos) cuotac, c.monto
				FROM nomina a
				JOIN pres   b ON a.codigo=b.codigo
				JOIN smov   c ON b.cod_cli=c.cod_cli AND b.tipo_doc=c.tipo_doc AND b.numero=c.numero
				AND c.monto<>c.abonos
				WHERE a.concepto='PRES' AND a.numero=${dbnomina} AND b.cuota = ABS(a.valor)";
		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				//Busca si ya existe
				$mSQL = "SELECT COUNT(*) AS val FROM smov WHERE cod_cli='".$row->cod_cli."' AND tipo_doc='NC' AND transac=${dbtransac} AND monto=".abs($row->valor);
				$val  = intval($this->datasis->dameval($mSQL));
				if($val == 0){
					$mCONTROL = $this->datasis->fprox_numero('nsmov');
					$mNOTACRE = 'I'.$this->datasis->fprox_numero('ncint',-1);

					$data = array();
					$data['cod_cli']  = $row->cod_cli;
					$data['nombre']   = $row->nombre;
					$data['tipo_doc'] = 'NC';
					$data['numero']   = $mNOTACRE;
					$data['fecha']    = $row->fechap;
					$data['monto']    = abs($row->valor);
					$data['impuesto'] = 0;
					$data['vence']    = $row->fechap;
					$data['abonos']   = abs($row->valor);
					$data['tipo_ref'] = 'GA';
					$data['num_ref']  = $nomina;
					$data['observa1'] = 'PAGO A '.$row->tipo_doc.$row->numero;
					$data['observa2'] = 'POR DESCUENTO DE NOMINA';
					$data['control']  = $mCONTROL;
					$data['codigo']   = 'NOCON';
					$data['descrip']  = 'NOMINA';
					$data['transac']  = $mTRANSAC;
					$data['estampa']  = $row->estampa;
					$data['hora']     = $row->hora;
					$data['usuario']  = $row->usuario;
					$this->db->insert('smov', $data );

					// ACTUALIZA EL DOCUMENTO ORIGEN
					$mSQL = "
						UPDATE smov SET abonos=abonos+".abs($row->valor)."
						WHERE tipo_doc='$row->tipo_doc' AND numero='".$row->numero."'
						AND cod_cli='".$row->cod_cli."' AND fecha=".$row->fecha." LIMIT 1";
					$this->db->query($mSQL);

					// CARGA EL MOVIMIENTO EN ITCCLI
					$data = array();
					$data['numccli']  = $mNOTACRE;
					$data['tipoccli'] = 'NC';
					$data['cod_cli']  = $row->cod_cli;
					$data['tipo_doc'] = $row->tipo_doc;
					$data['numero']   = $row->numero;
					$data['fecha']    = $row->fecha;
					$data['monto']    = $row->monto;
					$data['abono']    = abs($row->valor);
					$data['transac']  = $mTRANSAC;
					$data['estampa']  = $row->estampa;
					$data['hora']     = $row->hora;
					$data['usuario']  = $row->usuario;
					$this->db->insert('itccli', $data );
				}
			}
		}

		logusu('nomi',"NOMINA ${nomina} REGENERADA");

		$rt=array(
			'status' =>'A',
			'mensaje'=>'NOMINA REGENERADA '.$nomina,
			'pk'     => $nomina
		);
		echo json_encode($rt);
	}

	function tabla($numero = 0){
		$dbnumero = $this->db->escape($numero);
		$salida   = '';

		$rrow = $this->datasis->damerow('SELECT transac,fechap FROM nomina WHERE numero='.$dbnumero);
		if(!empty($rrow)){
			$dbtransac = $this->db->escape($rrow['transac']);
			$dbfechap  = $this->db->escape($rrow['fechap']);

			$mSQL = "SELECT cod_prv, nombre, tipo_doc, numero, monto FROM sprm WHERE transac=${dbtransac} AND fecha=${dbfechap} ORDER BY cod_prv";
			$query = $this->db->query($mSQL);
			if($query->num_rows() > 0){
				$salida .= '<br><table width=\'100%\' border=\'1\'>';
				$salida .= '<tr bgcolor=\'#E7E3E7\'><td colspan=\'3\'>Cuentas por pagar</td></tr>';
				$salida .= '<tr bgcolor=\'#E7E3E7\'><td>Prov.</td><td align=\'center\'>N&uacute;mero</td><td align=\'center\'>Monto</td></tr>';
				foreach ($query->result_array() as $row){
					$salida .= '<tr>';
					$salida .= '<td>'.$row['cod_prv'].'</td>';
					$salida .= '<td>'.$row['tipo_doc'].$row['numero'].'</td>';
					$salida .= '<td align=\'right\'>'.nformat($row['monto']).'</td>';
					$salida .= '</tr>';
				}
				$salida .= '</table>';
			}

			$mSQL = "SELECT proveed AS cod_prv, nombre, tipo_doc, numero, totneto AS monto FROM gser WHERE transac=${dbtransac} AND tipo_doc='GA' ORDER BY proveed";
			$query = $this->db->query($mSQL);
			if($query->num_rows() > 0){
				$salida .= '<br><table width=\'100%\' border=\'1\'>';
				$salida .= '<tr bgcolor=\'#E7E3E7\'><td colspan=\'3\'>Gastos relacionados</td></tr>';
				$salida .= '<tr bgcolor=\'#E7E3E7\'><td>Prov.</td><td align=\'center\'>N&uacute;mero</td><td align=\'center\'>Monto</td></tr>';
				foreach ($query->result_array() as $row){
					$salida .= '<tr>';
					$salida .= '<td>'.$row['cod_prv'].'</td>';
					$salida .= '<td>'.$row['tipo_doc'].$row['numero'].'</td>';
					$salida .= '<td align=\'right\'>'.nformat($row['monto']).'</td>';
					$salida .= '</tr>';
				}
				$salida .= '</table>';
			}


			$mSQL = "SELECT cod_cli, nombre, monto FROM smov WHERE transac=${dbtransac} AND tipo_ref='GA' ORDER BY cod_cli";
			$query = $this->db->query($mSQL);
			if($query->num_rows() > 0){
				$salida .= '<br><table width=\'100%\' border=\'1\'>';
				$salida .= '<tr bgcolor=\'#E7E3E7\'><td colspan=\'2\'>Pr&eacute;stamos descontados</td></tr>';
				$salida .= '<tr bgcolor=\'#E7E3E7\'><td align=\'center\'>N&uacute;mero</td><td align=\'center\'>Monto</td></tr>';
				foreach ($query->result_array() as $row){
					$salida .= '<tr>';
					$salida .= '<td>'.$row['nombre'].'</td>';
					$salida .= '<td align=\'right\'>'.nformat($row['monto']).'</td>';
					$salida .= '</tr>';
				}
				$salida .= '</table>';
			}
		}

		echo $salida;
	}
}
/*
Vacaciones
Periodo correspondiente al Ano 2011
Dias habiles vacaciones 15 + anos de servicio hasta 30
Dias de Descanso 6
Dias Feriados    1

Paga sobre la ultima nomina

sueldo integral * 15+anos
sueldo integral * descanso
sueldo integral * feriados


dias


*/
