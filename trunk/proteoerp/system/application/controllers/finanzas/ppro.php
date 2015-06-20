<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/

/*Moduo no completado*/
/*Moduo no completado*/

class Ppro extends Controller {
	var $mModulo='PPRO';
	var $titp='Pago a Proveedor';
	var $tits='Pago a Proveedor';
	var $url ='finanzas/ppro/';

	function Ppro(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		$this->datasis->creaintramenu(array('modulo'=>'528','titulo'=>'Pago a Proveedor','mensaje'=>'Pago a Proveedor','panel'=>'PROVEEDORES','ejecutar'=>'finanzas/ppro','target'=>'popu','visible'=>'S','pertenece'=>'5','ancho'=>900,'alto'=>600));
		$this->datasis->modintramenu( 900, 600, 'finanzas/ppro' );
		redirect($this->url.'jqdatag');
	}

	//******************************************************************
	// Layout en la Ventana
	//
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$bodyscript = $this->bodyscript($param['grids'][0]['gridname']);

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'edocta',   'img'=>'images/pdf_logo.gif',  'alt' => 'Formato PDF',     'label'=>'Estado de Cuenta'));
		$grid->wbotonadd(array('id'=>'preapro',  'img'=>'images/pdf_logo.gif',  'alt' => 'Formato PDF',     'label'=>'Lsta de Preaprobados'));
		$grid->wbotonadd(array('id'=>'preabono', 'img'=>'images/checklist.png', 'alt' => 'Pre Abonar',      'label'=>'Preparar Pago'));
		$grid->wbotonadd(array('id'=>'abonos',   'img'=>'images/check.png',     'alt' => 'Abonos',          'label'=>'Pago o Abono'));
		$grid->wbotonadd(array('id'=>'ncredito', 'img'=>'images/star.png',      'alt' => 'Nota de Credito', 'label'=>'Notas de Credito'));
		$grid->wbotonadd(array('id'=>'addprv',   'img'=>'images/star.png',      'alt' => 'Agrega Proveedor', 'label'=>'Agrega Proveedor'));
		$WestPanel = $grid->deploywestp();

		//Panel de pie de forma
		$adic = array(
			array('id'=>'fpreabono', 'title'=>'Autorizar Abonos'),
			array('id'=>'fabono',    'title'=>'Pagos y Abonos'),
			array('id'=>'fedita',    'title'=>'Agregar/Editar Registro'),
			array('id'=>'fncredito', 'title'=>'Notas de Creditos')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('PPRO', 'JQ');
		$param['otros']       = $this->datasis->otros('PPRO', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}


	//******************************************************************
	// Funciones de botones en javascript
	//
	function bodyscript($grid){

		$bodyscript = '<script type="text/javascript">';

		// Agrega Proveedor
		$bodyscript .= '
		$("#addprv").click( function() {
			$.post("'.site_url('compras/sprv/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		});';

		//Imprimir Estado de Cuenta
		$bodyscript .= '
		$("#edocta").click( function(){
			var id = jQuery("#newapi'. $grid.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'. $grid.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('reportes/ver/SPRMECU/SPRM/').'/\'+ret.cod_prv').';
			} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
		});';

		//Imprimir Estado de Cuenta
		$bodyscript .= '
		jQuery("#preapro").click( function(){
			var id = jQuery("#newapi'. $grid.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'. $grid.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('reportes/ver/SPRMPRE').'/\'+ret.id').';
			} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
		});';

		//Wraper de javascript
		$bodyscript .= '
		$(function() {
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = $("#newapi'.$grid.'");
			var s;
			var allFields = $( [] ).add( ffecha );

			var tips = $( ".validateTips" );

			s = grid.getGridParam(\'selarrrow\');
			//$( "input:submit, a, button", ".otros" ).button();';

		//Prepara Pago o Abono
		$bodyscript .= '
			$("#preabono").click(function() {
				var id     = jQuery("#newapi'.$grid.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret    = $("#newapi'.$grid.'").getRowData(id);
					mId = id;
					$.post("'.site_url('finanzas/ppro/formapabono').'/"+id, function(data){
						$("#fabono").html("");
						$("#fpreabono").html(data);
					});
					$( "#fpreabono" ).dialog( "open" );
				} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
			});
			$( "#fpreabono" ).dialog({
				autoOpen: false, height: 470, width: 790, modal: true,
				buttons: {
					"Aprobar Pago": function() {
						var bValid = true;
						var rows = $("#aceptados").jqGrid("getGridParam","data");
						var paras = new Array();
						for(var i=0;i < rows.length; i++){
							var row=rows[i];
							paras.push($.param(row));
						}
						// Coloca el Grid en un input
						$("#fgrid").val(JSON.stringify(paras));
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url:"'.site_url("finanzas/ppro/pabono").'",
								data: $("#abonopforma").serialize(),
								success: function(r,s,x){
									var res = $.parseJSON(r);
									if ( res.status == "A"){
										alert(res.mensaje);
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('reportes/ver/PPROABC').'/\'+res.id').';
										$( "#fpreabono" ).dialog( "close" );
										return [true, a ];
									} else {
										apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
									}
								}
							});
						}
					},
					Cancel: function() { $( this ).dialog( "close" ); }
				},
				close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
			});';


		//Abonos
		$bodyscript .= '
			$( "#abonos" ).click(function() {
				var id     = jQuery("#newapi'.$grid.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret    = $("#newapi'.$grid.'").getRowData(id);
					mId = id;
					$.post("'.site_url('finanzas/ppro/formaabono').'/"+id, function(data){
						$("#fpreabono").html("");
						$("#fabono").html(data);
					});
					$( "#fabono" ).dialog( "open" );
				} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
			});

			$( "#fabono" ).dialog({
				autoOpen: false, height: 470, width: 790, modal: true,
				buttons: {
					"Abonar": function() {
						var bValid = true;
						var rows = $("#abonados").jqGrid("getGridParam","data");
						var paras = new Array();
						for(var i=0;i < rows.length; i++){
							var row=rows[i];
							paras.push($.param(row));
						}
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							// Coloca el Grid en un input
							$("#fgrid").val(JSON.stringify(paras));
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url:"'.site_url("finanzas/ppro/abono").'",
								data: $("#abonoforma").serialize(),
								success: function(r,s,x){
									var res = $.parseJSON(r);
									if ( res.status == "A"){
										apprise(res.mensaje);
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('formatos/ver/PPROABC').'/\'+res.id').';
										$( "#fabono" ).dialog( "close" );
										return [true, a ];
									} else {
										apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
									}
								}
							});
						}
					},
					Cancel: function() { $( this ).dialog( "close" ); }
				},
				close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
			});';


		//Notas de Credito
		$bodyscript .= '
			$( "#ncredito" ).click(function() {
				var id     = jQuery("#newapi'.$grid.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret    = $("#newapi'.$grid.'").getRowData(id);
					mId = id;
					$.post("'.site_url('finanzas/ppro/formancredito').'/"+id, function(data){
						$("#fpreabono").html("");
						$("#fabono").html("");
						$("#fncredito").html(data);
					});
					$( "#fncredito" ).dialog( "open" );
				} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
			});

			$( "#fncredito" ).dialog({
				autoOpen: false, height: 470, width: 690, modal: true,
				buttons: {
					"Abonar": function() {
						var bValid = true;
						var rows = $("#abonados").jqGrid("getGridParam","data");
						var paras = new Array();
						for(var i=0;i < rows.length; i++){
							var row=rows[i];
							paras.push($.param(row));
						}
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							// Coloca el Grid en un input
							$("#fgrid").val(JSON.stringify(paras));
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url:"'.site_url("finanzas/ppro/ncredito").'",
								data: $("#ncreditoforma").serialize(),
								success: function(r,s,x){
									var res = $.parseJSON(r);
									if ( res.status == "A"){
										apprise(res.mensaje);
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('formatos/ver/PPRONC').'/\'+res.id').';
										$( "#fabono" ).dialog( "close" );
										return [true, a ];
									} else {
										apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
									}
								}
							});
						}
					},
					Cancel: function() { $( this ).dialog( "close" ); }
				},
				close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
			});
		});';

		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 520, width: 720, modal: true,
			buttons: {
			"Guardar": function() {
				var murl = $("#df1").attr("action");
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								$("#fedita").dialog( "close" );
								grid.trigger("reloadGrid");
								$.prompt("<h1>Registro Guardado</h1>",{
									submit: function(e,v,m,f){
									}}
								);
								idactual = json.pk.id;
								return true;
							} else {
								$.prompt("Error: "+json.mensaje);
							}
						} catch(e){
							$("#fedita").html(r);
						}
					}
				})
			},
			"Cancelar": function(){
				$("#fedita").html("");
				$(this).dialog("close");
			},
			"SENIAT":   function(){ consulrif("rifci"); },
			"URL":   function() { iraurl(); },
			},
			close: function(){
				$("#fedita").html("");
			}
		});';


		$bodyscript .= "\n</script>\n";
		return $bodyscript;

	}

	//******************************************************************
	// Definicion del Grid y la Forma
	//
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('cod_prv');
		$grid->label('C&oacute;digo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));

		$grid->addField('rif');
		$grid->label('RIF');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->addField('nombre');
		$grid->label('Proveedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));

		$grid->addField('saldo');
		$grid->label('Saldo');
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

		$grid->addField('cantidad');
		$grid->label('Cant.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));

		$grid->addField('nueva');
		$grid->label('Nueva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('vieja');
		$grid->label('Vieja');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('dias');
		$grid->label('D&iacute;as');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
		$grid->setSearch(false);
		$grid->setOndblClickRow('');
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		#Set url
		//$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	//******************************************************************
	// Busca la data en el Servidor por json
	//
	function getdata(){
		$grid = $this->jqdatagrid;
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('view_ppro');
		$response   = $grid->getData('view_ppro', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	//******************************************************************
	// Guarda la Informacion
	//
	function setdata(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('sprv', $data);
				echo "Registro Agregado";
				logusu('SPRV',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('sprv', $data);
			logusu('SPRV',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sprv WHERE id=$id ");
				logusu('SPRV',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//******************************************************************
	// Forma de Abono
	//
	function formapabono(){
		$id      = $this->uri->segment($this->uri->total_segments());
		$proveed = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");

		$reg = $this->datasis->damereg("SELECT proveed, nombre, rif FROM sprv WHERE id=$id");

		$salida = '
<script type="text/javascript">
	var lastcell = 0;
	var totalapa = 0;
	var grid1 = jQuery("#aceptados");
	$("#aceptados").jqGrid({
		datatype: "local",
		height: 250,
		colNames:["id","Tipo","Numero","Fecha","Vence","Monto","Saldo", "Faltante","Abonar","P.Pago"],
		colModel:[
			{name:"id",       index:"id",       width:10, hidden:true},
			{name:"tipo_doc", index:"tipo_doc", width:40},
			{name:"numero",   index:"numero",   width:90},
			{name:"fecha",    index:"fecha",    width:90},
			{name:"vence",    index:"vence",    width:90},
			{name:"monto",    index:"monto",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"saldo",    index:"saldo",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"faltan",   index:"faltan",   width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"abonar",   index:"abonar",   width:80, align:"right", edittype:"text", editable:true,  formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"ppago",    index:"ppago",    width:80, align:"right", edittype:"text", editable:true,  formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } }
		],
		cellEdit : true,
		cellsubmit : "clientArray",
		afterSaveCell: function (id,name,val,iRow,iCol){
			var row;
			if ( val=="" ){
				row = grid1.jqGrid(\'getRowData\', id );
				grid1.jqGrid("setCell",id,"abonar", Number(row["saldo"])-Number(row["faltan"]));
			}
			sumatot();
		},
		editurl: "clientArray"
	});

	var mefectos = [
';

		$mSQL  = "SELECT a.id, a.tipo_doc, a.numero, a.fecha, a.vence, a.monto, a.monto-a.abonos saldo, round(if(sum(d.devcant*d.costo) is null,0.00,sum(d.devcant*d.costo)),2) AS faltan, preabono abonar, preppago ppago ";
		$mSQL .= "FROM sprm a ";
		$mSQL .= 'LEFT JOIN scst   c ON a.transac=c.transac AND a.tipo_doc=c.tipo_doc AND a.cod_prv=c.proveed ';
		$mSQL .= 'LEFT JOIN itscst d ON c.control=d.control AND d.devcant is NOT NULL ';
		$mSQL .= "WHERE a.monto > a.abonos AND a.tipo_doc IN ('FC','ND','GI') AND a.cod_prv=".$this->db->escape($reg['proveed']);
		$mSQL .= ' GROUP BY a.cod_prv, a.tipo_doc, a.numero ';
		$mSQL .= "ORDER BY a.fecha ";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0 ){
			foreach( $query->result() as $row ){
				$salida .= "\t\t".'{id:"'.$row->id.'",';
				$salida .= 'tipo_doc:"'.$row->tipo_doc.'",';
				$salida .= 'numero:"'.  $row->numero.'",';
				$salida .= 'fecha:"'.   $row->fecha.'",';
				$salida .= 'vence:"'.   $row->vence.'",';
				$salida .= 'monto:"'.   $row->monto.'",';
				$salida .= 'saldo:"'.   $row->saldo.'",';
				$salida .= 'faltan:"'.  $row->faltan.'",';
				$salida .= 'abonar:"'.  $row->abonar.'",';
				$salida .= 'ppago:"'.   $row->ppago.'"},'."\n";
			}
		}
		$mSQL  = "SELECT codbanc, CONCAT(codbanc, ' ', banco, numcuent) banco ";
		$mSQL .= "FROM banc ";
		$mSQL .= "WHERE activo='S' ";
		$mSQL .= "ORDER BY (tbanco='CAJ'), codbanc ";
		$salida .= '
	];
	for(var i=0;i<=mefectos.length;i++) jQuery("#aceptados").jqGrid(\'addRowData\',i+1,mefectos[i]);
	$("#ffecha").datepicker({dateFormat:"dd/mm/yy"});
	function sumatot()
        {
		var grid = jQuery("#aceptados");
		var s;
		var total = 0;
		var rowcells = new Array();
		var entirerow;
		s = grid.jqGrid("getGridParam","data");
		if(s.length)
		{
			for(var i=0; i< s.length; i++)
			{
				entirerow = s[i];
				if ( Number(entirerow["abonar"])>Number(entirerow["saldo"]) ){
					grid.jqGrid("setCell",s[i]["id"],"abonar", entirerow["saldo"]);
					total += Number(entirerow["saldo"]);
					total -= Number(entirerow["faltan"]);
				} else {
					total += Number(entirerow["abonar"]);
				}
				//Calcula el descuento
				if (  Number(entirerow["ppago"]) < 0 ){
					if (Number(entirerow["abonar"]) == 0 ){
						grid1.jqGrid("setCell",s[i]["id"],"abonar", Number(entirerow["saldo"])-Number(entirerow["faltan"]));
					}
					total -= Number(entirerow["abonar"])*Math.abs(Number(entirerow["ppago"]))/100;
					grid.jqGrid("setCell",s[i]["id"],"ppago", Number(entirerow["abonar"])*Math.abs(Number(entirerow["ppago"]))/100);
				} else {
					total -= Number(entirerow["ppago"]);
				}
			}
			total = Math.round(total*100)/100;
			$("#grantotal").html("Total a Pagar: "+nformat(total,2));
			$("input#fmonto").val(total);
			montotal = total;
		} else {
			total = 0;
			$("#grantotal").html("Sin seleccion");
			$("input#fmonto").val(total);
			montotal = total;
		}
	};
	sumatot();

</script>
	<div style="background-color:#D0D0D0;font-weight:bold;font-size:14px;text-align:center"><table width="100%"><tr><td>Codigo: '.$reg['proveed'].'</td><td>'.$reg['nombre'].'</td><td>RIF: '.$reg['rif'].'</td></tr></table></div>
	<p class="validateTips"></p>
	<form id="abonopforma">
	<table width="80%" align="center">
	<tr>
		<td class="CaptionTD" align="right">Fecha</td>
		<td>&nbsp;'.date('d/m/Y').'</td>
		<td  class="CaptionTD"  align="right">Comprobante Externo</td>
		<td>&nbsp;<input name="fcomprob" id="fcomprob" type="text" value="" maxlengh="6" size="8"  /></td>
	</tr>
	</table>
	<input id="fmonto"   name="fmonto"   type="hidden">
	<input id="fsele"    name="fsele"    type="hidden">
	<input id="fid"      name="fid"      type="hidden" value="'.$id.'">
	<input id="fgrid"    name="fgrid"    type="hidden">
	<br>
	<center><table id="aceptados"><table></center>
	<table width="100%">
	<tr>
		<td align="center"><div id="grantotal" style="font-size:20px;font-weight:bold">Monto a pagar: 0.00</div></td>
	</tr>
	</table>
	</form>
';


		echo $salida;
	}


	//*********************************************************
	// Forma de Abono
	//
	function formaabono(){
		$id      = $this->uri->segment($this->uri->total_segments());
		$proveed = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");

		$reg = $this->datasis->damereg("SELECT proveed, nombre, rif FROM sprv WHERE id=$id");

		$salida = '

<script type="text/javascript">
	var lastcell = 0;
	var totalapa = 0;
	var grid1    = $("#abonados");
	$("#abonados").jqGrid({
		datatype: "local",
		height: 240,
		colNames:["id","Tipo","Numero","Fecha","Vence","Monto","Saldo", "Abonar","P.Pago"],
		colModel:[
			{name:"id",       index:"id",       width:10, hidden:true},
			{name:"tipo_doc", index:"tipo_doc", width:40},
			{name:"numero",   index:"numero",   width:90},
			{name:"fecha",    index:"fecha",    width:90},
			{name:"vence",    index:"vence",    width:90},
			{name:"monto",    index:"monto",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"saldo",    index:"saldo",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"abonar",   index:"abonar",   width:80, align:"right", edittype:"text", editable:true,  formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"ppago",    index:"ppago",    width:80, align:"right", edittype:"text", editable:true,  formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } }
		],
		cellEdit : true,
		cellsubmit : "clientArray",
		afterSaveCell: function (id,name,val,iRow,iCol){
			var row;
			if ( val=="" ){
				row = grid1.jqGrid(\'getRowData\', id );
				grid1.jqGrid("setCell",id,"abonar", Number(row["saldo"]));
			}
			sumabo();
		},
		editurl: "clientArray"
	});
	var mefectos = [
';

		$mSQL  = "SELECT a.id, a.tipo_doc, a.numero, a.fecha, a.vence, a.monto, a.monto-a.abonos saldo, preabono abonar, preppago ppago ";
		$mSQL .= "FROM sprm a ";
		$mSQL .= 'LEFT JOIN scst   c ON a.transac=c.transac AND a.tipo_doc=c.tipo_doc AND a.cod_prv=c.proveed ';
		$mSQL .= 'LEFT JOIN itscst d ON c.control=d.control AND d.devcant is NOT NULL ';
		$mSQL .= "WHERE a.monto > a.abonos AND a.tipo_doc IN ('FC','ND','GI') AND a.cod_prv=".$this->db->escape($reg['proveed']);
		$mSQL .= ' GROUP BY a.cod_prv, a.tipo_doc, a.numero ';
		$mSQL .= "ORDER BY a.fecha ";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0 ){
			foreach( $query->result() as $row ){
				$salida .= "\t\t".'{id:"'.$row->id.'",';
				$salida .= 'tipo_doc:"'.$row->tipo_doc.'",';
				$salida .= 'numero:"'.  $row->numero.'",';
				$salida .= 'fecha:"'.   $row->fecha.'",';
				$salida .= 'vence:"'.   $row->vence.'",';
				$salida .= 'monto:"'.   $row->monto.'",';
				$salida .= 'saldo:"'.   $row->saldo.'",';
				$salida .= 'abonar:"'.  $row->abonar.'",';
				$salida .= 'ppago:"'.   $row->ppago.'"},'."\n";
			}
		}
		$mSQL  = "SELECT codbanc, CONCAT(codbanc, ' ', banco, numcuent) banco ";
		$mSQL .= "FROM banc ";
		$mSQL .= "WHERE activo='S' ";
		$mSQL .= "ORDER BY (tbanco='CAJ'), codbanc ";

		$mSQL  = "SELECT codbanc, CONCAT(codbanc, ' ', TRIM(banco), IF(tbanco='CAJ',' ',numcuent) ) banco FROM banc WHERE activo='S' ORDER BY tbanco='CAJ', codbanc ";
		$cajas = $this->datasis->llenaopciones($mSQL, true, 'fcodbanc');


		$salida .= '
	];
	for(var i=0;i<=mefectos.length;i++) $("#abonados").jqGrid(\'addRowData\',i+1,mefectos[i]);
	$("#ffecha").datepicker({dateFormat:"dd/mm/yy"});
	function sumabo(){
		var grid = $("#abonados");
		var s;
		var total = 0;
		var rowcells = new Array();
		var entirerow;
		s = grid.jqGrid("getGridParam","data");
		if(s.length){
			for(var i=0; i< s.length; i++){
				entirerow = s[i];
				if ( Number(entirerow["abonar"])>Number(entirerow["saldo"]) ){
					grid.jqGrid("setCell",s[i]["id"],"abonar", entirerow["saldo"]);
					total += Number(entirerow["saldo"]);
				} else {
					total += Number(entirerow["abonar"]);
				}
				//Calcula el descuento
				if (  Number(entirerow["ppago"]) < 0 ){
					if (Number(entirerow["abonar"]) == 0 ){
						grid1.jqGrid("setCell",s[i]["id"],"abonar", Number(entirerow["saldo"]));
					}
					total -= Number(entirerow["abonar"])*Math.abs(Number(entirerow["ppago"]))/100;
					grid.jqGrid("setCell",s[i]["id"],"ppago", Number(entirerow["abonar"])*Math.abs(Number(entirerow["ppago"]))/100);
				} else {
					total -= Number(entirerow["ppago"]);
				}
			}
			total = Math.round(total*100)/100;
			$("#grantotal").html("Total a Pagar: "+nformat(total,2));
			$("input#fmonto").val(total);
			montotal = total;
		} else {
			total = 0;
			$("#grantotal").html("Sin seleccion");
			$("input#fmonto").val(total);
			montotal = total;
		}
	};
	sumabo();

</script>
	<div style="background-color:#D0D0D0;font-weight:bold;font-size:14px;text-align:center">
	<table width="100%">
		<tr>
			<td>Codigo: '.$reg['proveed'].'</td>
			<td>'.$reg['nombre'].'</td>
			<td>RIF: '.$reg['rif'].'</td>
		</tr>
	</table></div>
	<p class="validateTips"></p>
	<form id="abonoforma">
	<table width="90%" align="center" border="0">
	<tr>
		<td class="CaptionTD" align="right">Banco/Caja</td>
		<td>&nbsp;'.$cajas.'</td>

		<td class="CaptionTD" align="right">Tipo</td>
		<td>&nbsp;<select name="ftipo" id="ftipo" value="CH"><option value="CH">Cheque</option><option value="ND">Nota debito</option> </select></td>

		<td  class="CaptionTD"  align="right">Numero</td>
		<td>&nbsp;<input name="fcomprob" id="fcomprob" type="text" value="" maxlengh="12" size="12"  /></td>
	</tr>
	<tr>
		<td class="CaptionTD" align="right">Beneficiario:</td>
		<td colspan="3">&nbsp;<input name="fbenefi" id="fbenefi" type="text" value="" maxlengh="60" size="50"  /></td>
		<td class="CaptionTD" align="right">Fecha</td>
		<td>&nbsp;<input name="ffecha" id="ffecha" maxlength="10" size="10" value=\''.date('d/m/Y').'\'/></td>
	</tr>
	</table>
	<input id="fmonto"   name="fmonto"   type="hidden">
	<input id="fsele"    name="fsele"    type="hidden">
	<input id="fid"      name="fid"      type="hidden" value="'.$id.'">
	<input id="fgrid"    name="fgrid"    type="hidden">
	<br>
	<center><table id="abonados"><table></center>
	<table width="600">
	<tr>
		<td align="center"><div id="grantotal" style="font-size:20px;font-weight:bold">Monto a pagar: 0.00</div></td>
	</tr>
	</table>
	</form>
';


		echo $salida;
	}


	//******************************************************************
	// Forma de Notas de Credito
	//
	function formancredito(){
		$id      = $this->uri->segment($this->uri->total_segments());
		$proveed = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");
		$reg = $this->datasis->damereg("SELECT proveed, nombre, rif FROM sprv WHERE id=$id");

		$salida = '
<script type="text/javascript">
	var lastcell = 0;
	var totalapa = 0;
	var grid1 = jQuery("#abonados");
	jQuery("#abonados").jqGrid({
		datatype: "local",
		height: 240,
		colNames:["id","Tipo","Numero","Fecha","Vence","Monto","Saldo", "Abonar","Impuesto"],
		colModel:[
			{name:"id",       index:"id",       width:10, hidden:true},
			{name:"tipo_doc", index:"tipo_doc", width:40},
			{name:"numero",   index:"numero",   width:90},
			{name:"fecha",    index:"fecha",    width:90},
			{name:"vence",    index:"vence",    width:90},
			{name:"monto",    index:"monto",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"saldo",    index:"saldo",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"abonar",   index:"abonar",   width:80, align:"right", edittype:"text", editable:true,  formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"impuesto", index:"impuesto", width:80, align:"right", edittype:"text", editable:true,  formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }, "hidden":true }
		],
		cellEdit : true,
		cellsubmit : "clientArray",
		afterSaveCell: function (id,name,val,iRow,iCol){
			var row;
			if ( val=="" ){
				row = grid1.jqGrid(\'getRowData\', id );
				grid1.jqGrid("setCell",id,"abonar", Number(row["saldo"]));
			}
			sumanc();
		},
		editurl: "clientArray"
	});

	var mefectos = [
';

		$mSQL  = "SELECT a.id, a.tipo_doc, a.numero, a.fecha, a.vence, a.monto, a.monto-a.abonos saldo, 0 abonar, impuesto ";
		$mSQL .= "FROM sprm a ";
		$mSQL .= 'LEFT JOIN scst   c ON a.transac=c.transac AND a.tipo_doc=c.tipo_doc AND a.cod_prv=c.proveed ';
		$mSQL .= 'LEFT JOIN itscst d ON c.control=d.control AND d.devcant is NOT NULL ';
		$mSQL .= "WHERE a.monto > a.abonos AND a.tipo_doc IN ('FC','ND','GI') AND a.cod_prv=".$this->db->escape($reg['proveed']);
		$mSQL .= ' GROUP BY a.cod_prv, a.tipo_doc, a.numero ';
		$mSQL .= "ORDER BY a.fecha ";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0 ){
			foreach( $query->result() as $row ){
				$salida .= "\t\t".'{id:"'.$row->id.'",';
				$salida .= 'tipo_doc:"'.$row->tipo_doc.'",';
				$salida .= 'numero:"'.  $row->numero.'",';
				$salida .= 'fecha:"'.   $row->fecha.'",';
				$salida .= 'vence:"'.   $row->vence.'",';
				$salida .= 'monto:"'.   $row->monto.'",';
				$salida .= 'saldo:"'.   $row->saldo.'",';
				$salida .= 'abonar:"'.  $row->abonar.'",';
				$salida .= 'impuesto:"'.$row->impuesto.'"},'."\n";
			}
		}
		$mSQL  = "SELECT codbanc, CONCAT(codbanc, ' ', banco, numcuent) banco ";
		$mSQL .= "FROM banc ";
		$mSQL .= "WHERE activo='S' ";
		$mSQL .= "ORDER BY (tbanco='CAJ'), codbanc ";

		$mSQL = "SELECT codigo, TRIM(nombre) descrip FROM botr WHERE tipo='P' ORDER BY codigo ";
		$botr = $this->datasis->llenaopciones($mSQL, true, 'fcodigo');

		$salida .= '
	];
	for(var i=0;i<=mefectos.length;i++) jQuery("#abonados").jqGrid(\'addRowData\',i+1,mefectos[i]);
	$("#ffecha").datepicker({dateFormat:"dd/mm/yy"});
	$("#femision").datepicker({dateFormat:"dd/mm/yy"});
	function sumanc()
        {
		var grid = jQuery("#abonados");
		var s;
		var total    = 0;
		var tiva     = 0;
		var texento  = 0;
		var rowcells = new Array();
		var entirerow;
		s = grid.jqGrid("getGridParam","data");
		if(s.length)
		{
			for(var i=0; i< s.length; i++)
			{
				entirerow = s[i];
				if ( Number(entirerow["abonar"])>Number(entirerow["saldo"]) ){
					grid.jqGrid("setCell",s[i]["id"],"abonar", entirerow["saldo"]);
					total += Number(entirerow["saldo"]);
					tiva  += Number(entirerow["impuesto"])*Number(entirerow["saldo"])/Number(entirerow["monto"]);
				} else {
					total += Number(entirerow["abonar"]);
					if ( Number(entirerow["abonar"])>0 )
						tiva  += Number(entirerow["impuesto"])*Number(entirerow["abonar"])/Number(entirerow["monto"]) ;
				}
			}
			total = Math.round(total*100)/100;
			tiva  = Math.round(tiva*100)/100;
			$("#grantotal").html("Total: "+nformat(total,2));
			$("input#fmonto").val(total);
			$("input#fiva").val(tiva);
			montotal = total;
		} else {
			total = 0;
			tiva  = 0;
			$("#grantotal").html("Sin seleccion");
			$("input#fmonto").val(total);
			$("input#fiva").val(0);
			montotal = total;
		}
	};
	sumanc();

</script>
	<div style="background-color:#D0D0D0;font-weight:bold;font-size:14px;text-align:center"><table width="100%"><tr><td>Codigo: '.$reg['proveed'].'</td><td>'.$reg['nombre'].'</td><td>RIF: '.$reg['rif'].'</td></tr></table></div>
	<p class="validateTips"></p>
	<form id="ncreditoforma">
	<table width="90%" align="center" border="0">
	<tr>
		<td  class="CaptionTD"  align="right">Numero</td>
		<td>&nbsp;<input name="fnumero" id="fnumero" type="text" value="" maxlengh="12" size="12"  /></td>

		<td  class="CaptionTD"  align="right">Nro Fiscal</td>
		<td>&nbsp;<input name="fnfiscal" id="fnfiscal" type="text" value="" maxlengh="12" size="12"  /></td>

		<td class="CaptionTD" align="right">Motivo:</td>
		<td>&nbsp;'.$botr.'</td>
	</tr>

	<tr>
		<td class="CaptionTD" align="right">Fecha</td>
		<td>&nbsp;<input name="ffecha" id="ffecha" maxlength="10" size="10" value=\''.date('d/m/Y').'\'/></td>

		<td class="CaptionTD" align="right">Emision</td>
		<td>&nbsp;<input name="femision" id="femision" maxlength="10" size="10" value=\''.date('d/m/Y').'\'/></td>

		<td class="CaptionTD" align="right">&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
	<input id="fmonto"   name="fmonto"   type="hidden">
	<input id="fsele"    name="fsele"    type="hidden">
	<input id="fid"      name="fid"      type="hidden" value="'.$id.'">
	<input id="fgrid"    name="fgrid"    type="hidden">
	<br>
	<center><table id="abonados"><table></center>
	<table width="100%">
	<tr>
		<td align="center"><div id="grantotal"  style="font-size:16px;font-weight:bold">Monto a pagar: 0.00</div></td>
		<td align="center"><div id="graniva"    style="font-size:16px;font-weight:bold">I.V.A.: <input name="fiva" id="fiva" type="text" value="0.00" maxlengh="12" size="10"  /></div></td>
		<td align="center"><div id="granexento" style="font-size:16px;font-weight:bold">Exento: <input name="fexento" id="fexento" type="text" value="0.00" maxlengh="12" size="10"/></div></td>
	</tr>
	</table>
	</form>
';


		echo $salida;
	}

	//**************************************************
	//Guarda el preabono
	//
	//**************************************************
	function pabono(){
		$comprob   = $this->input->post('fcomprob');
		$fecha     = $this->input->post('ffecha');
		$grid      = $this->input->post('fgrid');
		$id        = $this->input->post('fid');
		$monto     = $this->input->post('fmonto');
		$fsele     = $this->input->post('fsele');
		$check     = 0;
		$meco      = json_decode($grid);

		foreach( $meco as $row ){
			parse_str($row,$linea[]);
		}

		$cod_prv = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");
		foreach( $linea as $efecto ){
			//actualiza los movimientos
			$this->db->where('cod_prv',   $cod_prv);
			$this->db->where('numero',    $efecto['numero']);
			$this->db->where('tipo_doc',  $efecto['tipo_doc']);
			$this->db->where('fecha',     $efecto['fecha']);
			if ( $efecto['abonar'] == 0 ) $efecto['ppago'] = "0";
			$data = array("preabono"=>$efecto['abonar'], "preppago"=>$efecto['ppago'], "comprob"=>$comprob);
			$this->db->update('sprm', $data);
		}
		logusu('SPRM',"Aprobacion de pagos CREADO $cod_prv "+$grid);
		echo '{"status":"A","id":"'.$id.'","mensaje":"Aprobacion Guardada"}';
	}

	//******************************************************************
	// Guarda el Abono
	//
	function abono(){
		$numche  = $this->input->post('fcomprob');
		$tipo_op = $this->input->post('ftipo');
		$benefi  = $this->input->post('fbenefi');
		$codbanc = $this->input->post('fcodbanc');
		$fecha   = $this->input->post('ffecha');
		$grid    = $this->input->post('fgrid');
		$id      = $this->input->post('fid');
		$monto   = $this->input->post('fmonto');
		$fsele   = $this->input->post('fsele');
		$check   = 0;
		$meco    = json_decode($grid);
		$dbcodban= $this->db->escape($codbanc);

		//Convierte la fecha a YYYYmmdd
		$fecha = substr($fecha,6,4).substr($fecha,3,2).substr($fecha,0,2);

		// Validacion
		if( $codbanc == '' ){
			echo '{"status":"E","id":"'.$id.'" ,"mensaje":"Debe seleccionar un Banco o Caja "}';
			return;
		}

		if( $this->datasis->dameval('SELECT count(*) FROM banc WHERE codbanc='.$dbcodban)==0 ){
			echo '{"status":"E","id":"'.$id.'" ,"mensaje":"Debe seleccionar un Banco o Caja "}';
			return;
		}

		$tbanco = $this->datasis->dameval('SELECT tbanco FROM banc WHERE codbanc='.$dbcodban);

		if ( $tbanco <> 'CAJ' && $numche == ''  ){
			echo '{"status":"E","id":"'.$id.'" ,"mensaje":"Falta colocar el numero de Documento"}';
			return;
		}

		foreach( $meco as $row ){
			parse_str($row,$linea[]);
		}
		$cod_prv = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");
		$nombre  = $this->datasis->dameval("SELECT nombre  FROM sprv WHERE id=$id");

		$totalab  = 0;
		$ppago    = 0;
		$impuesto = 0;
		$observa1 = 'ABONA A: ';
		$observa2 = '';
		$mTempo = "SELECT impuesto FROM sprm WHERE cod_prv=".$this->db->escape($cod_prv);
		foreach( $linea as $efecto ){
			if(is_numeric($efecto['abonar'])){
				$efecto['abonar']=floatval($efecto['abonar']);
				if ($efecto['abonar'] > 0 ){
					$totalab  += $efecto['abonar'] - $efecto['ppago'];
					$ppago    += $efecto['ppago'];
					$observa1 .= $efecto['tipo_doc'].$efecto['numero'].', ';

					$dbittipo   = $this->db->escape($efecto['tipo_doc']);
					$dbitnumero = $this->db->escape($efecto['numero']);

					$impuesto += $efecto['abonar']*$this->datasis->dameval($mTempo." AND tipo_doc=${dbittipo} AND numero=".$dbitnumero)/$efecto['monto'];
				}
			}else{
				$rt=array(
					'status' => 'E',
					'id'     => $id,
					'mensaje'=> 'Efecto '.$efecto['tipo_doc'].$efecto['numero'].' tiene la cantidad errada.'
				);
				echo json_encode($rt);
				return ;
			}
		}

		$observa2 = '';
		if(strlen($observa1)>50){
			$observa2 = substr($observa1, 49);
			$observa1 = substr($observa1, 0, 50);
		}

		if($totalab <= 0){
			$rt=array(
				'status' => 'E',
				'id'     => $id,
				'mensaje'=> 'Seleccione los efectos a abonar.'
			);
			echo json_encode($rt);
			return ;
		}

		//Crea el Abono
		$transac  = $this->datasis->prox_sql('ntransa',8);
		$mnroegre = $this->datasis->prox_sql('nroegre',8);
		$tipo_doc = 'AB';
		$xnumero  = $this->datasis->prox_sql('num_ab',8);
		$mcontrol = $this->datasis->prox_sql('nsprm' ,8);

		$data = array();
		$data['tipo_doc'] = $tipo_doc;
		$data['numero']   = $xnumero;
		$data['cod_prv']  = $cod_prv;
		$data['nombre']   = $nombre;
		$data['fecha']    = $fecha;
		$data['monto']    = $totalab;
		$data['impuesto'] = $impuesto;
		$data['vence']    = $fecha;
		$data['observa1'] = $observa1;
		$data['observa2'] = $observa2;

		$data['banco']    = $codbanc;
		$data['tipo_op']  = $tipo_op;
		$data['numche']   = $numche;
		$data['benefi']   = $benefi;
		$data['reten']    = 0;
		$data['reteiva']  = 0;
		$data['ppago']    = $ppago ;
		$data['control']  = $mcontrol ;
		$data['cambio']   = 0 ;
		$data['nfiscal']  = '' ;
		$data['mora']     = 0 ;

		$data['comprob']  = '' ;
		$data['abonos']   = $totalab ;

		$data['usuario']  = $this->secu->usuario();
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['transac']  = $transac;

		$this->db->insert('sprm',$data);
		$idab = $this->db->insert_id();

		// Si tiene prontopago genera la NC
		if ( $ppago > 0 ){
			$mnumero   = $this->datasis->prox_sql("num_nc",8);
			$mcontrol  = $this->datasis->prox_sql("nsprm",8);
			$mcdppago  = $mcontrol;

			$data = array();
			$data['tipo_doc'] =  'NC';
			$data['numero']   =  $mnumero;
			$data['cod_prv']  =  $cod_prv;
			$data['nombre']   =  $nombre;
			$data['fecha']    =  $fecha;
			$data['monto']    =  $ppago;

			$data['impuesto'] = round($ppago*$impuesto/$totalab,2) ;

			$data['vence']    = $fecha;
			$data['observa1'] = 'DESC. P.PAGO A '.$tipo_doc.$xnumero;
			$data['codigo']   = 'DESPP';
			$data['descrip']  = 'DESCUENTO PRONTO PAGO';
			$data['abonos']   = $ppago;
			$data['control']  = $mcontrol;

			$data['usuario']  = $this->secu->usuario();
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('H:i:s');
			$data['transac']  = $transac;

			$this->db->insert('sprm',$data);

			// DEBE DEVOLVER EL IVA EN CASO DE CONTRIBUYENTE
			/*
			IF TRAEVALOR("CONTRIBUYENTE") = 'ESPECIAL'

			ENDIF
			*/
		}

		//Crea Movimiento en Bancos
		$mndebito = '';

		if ( $tbanco  == 'CAJ' ) $tipo_op = 'ND';
		if ( $tipo_op == 'ND' && $tbanco != 'CAJ' ) $mndebito = $this->datasis->prox_sql("ndebito",8);

		$data = array();

		$data['codbanc']  = $codbanc;

		$mTempo = ' FROM banc WHERE codbanc='.$dbcodban;
		$data['numcuent'] = $this->datasis->dameval('SELECT numcuent '.$mTempo);
		$data['banco']    = $this->datasis->dameval('SELECT banco    '.$mTempo);
		$data['saldo']    = $this->datasis->dameval('SELECT saldo    '.$mTempo);

		$data['fecha']    = $fecha;
		$data['tipo_op']  = $tipo_op;
		$data['numero']   = $numche;

		$data['concepto'] = $observa1;
		$data['concep2']  = $observa2;
		$data['monto']    = $totalab;
		$data['clipro']   = 'P' ;

		$data['codcp']    = $cod_prv;
		$data['nombre']   = $nombre;
		$data['benefi']   = $benefi;

		$data['negreso']  = $mnroegre;
		$data['ndebito']  = $mndebito;

		$data['usuario']  = $this->secu->usuario();
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['transac']  = $transac;

		$this->db->insert('bmov',$data);
		$this->datasis->actusal($codbanc, $fecha, -$totalab);

		foreach( $linea as $efecto ){
			if ( $efecto['abonar'] > 0 ) {
				// Guarda en itppro
				$data = array();
				$data['numppro']  = $xnumero;
				$data['tipoppro'] = $tipo_doc;
				$data['cod_prv']  = $cod_prv;
				$data['numero']   = $efecto['numero'];
				$data['tipo_doc'] = $efecto['tipo_doc'];
				$data['fecha']    = $fecha;
				$data['monto']    = $efecto['numero'];
				$data['abono']    = $efecto['abonar'];
				$data['breten']   = 0;
				$data['creten']   = '';
				$data['reten']    = 0;
				$data['reteiva']  = 0;
				$data['ppago']    = 0;
				$data['cambio']   = 0;
				$data['mora']     = 0;

				$data['usuario']  = $this->secu->usuario();
				$data['estampa']  = date('Ymd');
				$data['hora']     = date('H:i:s');
				$data['transac']  = $transac;
				$this->db->insert('itppro',$data);

				// Actualiza sprm
				$data = array($efecto['abonar'], $efecto['tipo_doc'], $efecto['numero'], $cod_prv, $efecto['fecha']);
				$mSQL = "UPDATE sprm SET abonos=abonos+?, preabono=0, preppago=0 WHERE tipo_doc=? AND numero=? AND cod_prv=? AND fecha=?";
				$this->db->query($mSQL, $data);
			}
		}
		logusu('PPRO',"Abono a proveedor CREADO Prov=$cod_prv  Numero=$xnumero Detalle=".$grid);
		echo '{"status":"A","id":"'.$idab.'" ,"mensaje":"Abono Guardado '.$codbanc.'"}';
	}

	//**************************************************
	// Guarda la NC
	//
	//**************************************************
	function ncredito(){
		$numero   = $this->input->post('fnumero');
		$observa  = $this->input->post('fobserva');
		$fecha    = $this->input->post('ffecha');
		$femision = $this->input->post('femision');
		$codigo   = $this->input->post('fcodigo');
		$nfiscal  = $this->input->post('fnfiscal');
		$iva      = $this->input->post('fiva');
		$exento   = $this->input->post('fexento');

		$grid    = $this->input->post('fgrid');
		$id      = $this->input->post('fid');
		$monto   = $this->input->post('fmonto');
		$fsele   = $this->input->post('fsele');
		$check   = 0;
		$meco    = json_decode($grid);
		$merror  = "";

		//Convierte la fecha a YYYYmmdd
		$fecha = substr($fecha,6,4).substr($fecha,3,2).substr($fecha,0,2);

		// Validacion
		if ( $codigo == '-'  ) {
			$merror .= "Falta colocar el Motivo<br>";
			$check++;
		}

		if ( $numero == ''  ) {
			$merror .= "Falta colocar el Numero<br>";
			$check++;
		}

		if ( $nfiscal == ''  ) {
			$merror .= "Falta colocar el Numero Fiscal<br>";
			$check++;
		}

		if ( $monto == 0  ) {
			$merror .= "No selecciono ningun efecto";
			$check++;
		}

		if ( $check > 0 ){
			echo '{"status":"E","id":"'.$id.'" ,"mensaje":"'.$merror.'"}';
			return;
		}

		foreach( $meco as $row ){
			parse_str($row,$linea[]);
		}

// echo '{"status":"E","id":"'.$id.'" ,"mensaje":"Mas Fino"}';
// return;

		$cod_prv  = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");
		$nombre   = $this->datasis->dameval("SELECT nombre  FROM sprv WHERE id=$id");

		$totalab  = 0;
		$ppago    = 0;
		$impuesto = 0;
		$observa1 = 'CREDITO A: ';
		$observa2 = '';
		$fecdoc   = $fecha;
		$mTempo = "SELECT impuesto FROM sprm WHERE cod_prv=".$this->db->escape($cod_prv);
		foreach( $linea as $efecto ){
			if ($efecto['abonar'] > 0 ){
				$fecdoc    = $efecto['fecha'];
				$totalab  += $efecto['abonar'];
				$observa1 .= $efecto['tipo_doc'].$efecto['numero'].', ';
				$impuesto += $efecto['abonar']*$this->datasis->dameval($mTempo." AND tipo_doc='".$efecto['tipo_doc']."' AND numero='".$efecto['numero']."'" )/$efecto['monto'];
			}
		}

		$observa2 = '';
		if ( strlen($observa1)>50) {
			$observa2 = substr($observa1, 49);
			$observa1 = substr($observa1, 0, 50);
		}

		if ( $totalab <= 0) {
			echo '{"status":"E","id":"'.$id.'" ,"mensaje":"Seleccione los efectos a abonar"}';
			return;
		}

		//Crea la NC
		$transac  = $this->datasis->prox_sql('ntransa',8);
		$mnroegre = ''; //$this->datasis->prox_sql("nroegre",8);
		$tipo_doc = 'NC';
		$xnumero  = $numero;
		$mcontrol = $this->datasis->prox_sql("nsprm",8);

		$data = array();
		$data['tipo_doc']  = $tipo_doc;
		$data['numero']    = $numero;
		$data['cod_prv']   = $cod_prv;
		$data['nombre']    = $nombre;
		$data['fecha']     = $fecha;
		$data['monto']     = $totalab;
		$data['impuesto']  = $impuesto;
		$data['vence']     = $fecha;
		$data['observa1']  = $observa1;
		$data['observa2']  = $observa2;

		$data['banco']     = "";
		$data['tipo_op']   = "";
		$data['numche']    = "";
		$data['benefi']    = "";
		$data['reten']     = 0;
		$data['reteiva']   = 0;
		$data['ppago']     = 0;
		$data['control']   = $mcontrol ;
		$data['cambio']    = 0 ;
		$data['nfiscal']   = $nfiscal ;
		$data['mora']      = 0 ;

		$data['comprob']   = '' ;
		$data['abonos']    = $totalab;

		$data['codigo']    = $codigo ;
		$data['descrip']   = $this->datasis->dameval("SELECT nombre FROM botr WHERE codigo='$codigo'");
		$data['fecapl']    = $fecha;

		$data['fecdoc']    = $fecdoc;
		$data['fecapl']    = $femision;
		$data['montasa']   = $totalab-$iva-$exento;
		$data['monredu']   = 0;
		$data['monadic']   = 0;
		$data['tasa']      = $iva;
		$data['reducida']  = 0;
		$data['sobretasa'] = 0;
		$data['exento']    = $exento;
		$data['causado']   = $this->datasis->prox_sql("ncausado",8);

		$data['usuario']   = $this->secu->usuario();
		$data['estampa']   = date('Ymd');
		$data['hora']      = date('H:i:s');
		$data['transac']   = $transac;
		$data['serie']     = $numero;

		$this->db->insert('sprm',$data);
		$idab = $this->db->insert_id();

		foreach( $linea as $efecto ){
			if ( $efecto['abonar'] > 0 ) {
				// Guarda en itppro
				$data = array();
				$data['numppro']  = $xnumero;
				$data['tipoppro'] = $tipo_doc;
				$data['cod_prv']  = $cod_prv;
				$data['numero']   = $efecto['numero'];
				$data['tipo_doc'] = $efecto['tipo_doc'];
				$data['fecha']    = $fecha;
				$data['monto']    = $efecto['numero'];
				$data['abono']    = $efecto['abonar'];
				$data['breten']   = 0;
				$data['creten']   = '';
				$data['reten']    = 0;
				$data['reteiva']  = 0;
				$data['ppago']    = 0;
				$data['cambio']   = 0;
				$data['mora']     = 0;

				$data['usuario']  = $this->secu->usuario();
				$data['estampa']  = date('Ymd');
				$data['hora']     = date('H:i:s');
				$data['transac']  = $transac;
				$this->db->insert('itppro',$data);

				// Actualiza sprm
				$data = array($efecto['abonar'], $efecto['tipo_doc'], $efecto['numero'], $cod_prv, $efecto['fecha']);
				$mSQL = "UPDATE sprm SET abonos=abonos+?, preabono=0, preppago=0 WHERE tipo_doc=? AND numero=? AND cod_prv=? AND fecha=?";
				$this->db->query($mSQL, $data);
			}
		}
		logusu('PPRO',"Nota de Credito a Proveedor CREADO Prov=$cod_prv  Numero=$xnumero Detalle=".$grid);
		echo '{"status":"A","id":"'.$idab.'" ,"mensaje":"Nota de Credito Guardada "}';
	}

	function instalar(){
		$campos=$this->db->list_fields('sprm');

		if(!in_array('preabono',$campos)){
			$this->db->simple_query('ALTER TABLE sprm ADD preabono DECIMAL(17,2) NULL DEFAULT 0 AFTER causado ');
		}

		if(!in_array('preppago',$campos)){
			$this->db->simple_query('ALTER TABLE sprm ADD preppago DECIMAL(17,2) NULL DEFAULT 0 AFTER preabono ');
		}

		if(!$this->datasis->istabla('view_ppro')){
			$mSQL= 'CREATE ALGORITHM=UNDEFINED
					DEFINER=datasis@localhost
					SQL SECURITY
					DEFINER VIEW view_ppro AS
					SELECT trim(a.cod_prv) AS cod_prv, b.rif AS rif, b.nombre AS nombre, sum((a.monto - a.abonos)) AS saldo, max(a.fecha) AS nueva, min(a.fecha) AS vieja, count(0) AS cantidad, sum((a.vence-curdate())) AS dias, b.id AS id from (sprm a join sprv b on((a.cod_prv = b.proveed)))
					WHERE ((a.monto > a.abonos) and (a.tipo_doc in ("FC","ND","GI")))
					GROUP BY a.cod_prv';
			$this->db->query($mSQL);
		}

	}

}
