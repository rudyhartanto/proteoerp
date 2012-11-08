<?php
class Scst extends Controller {
	var $mModulo = 'SCST';
	var $titp    = 'Compras de Productos';
	var $tits    = 'Compras de Productos';
	var $url     = 'compras/scst/';
	var $genesal = true;
	var $solo    = false;

	function Scst(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->back_dataedit='compras/scst/datafilter';
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		if ( !$this->datasis->iscampo('scst','id') ) {
			$this->db->simple_query('ALTER TABLE scst DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE scst ADD UNIQUE INDEX control (control)');
			$this->db->simple_query('ALTER TABLE scst ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		$this->datasis->modintramenu( 900, 650, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$param['grids'][] = $grid1->deploy();

		$readyLayout = $grid->readyLayout2( 212, 220, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"imprimir", "img"=>"images/pdf_logo.gif", "alt" => 'Formato PDF',         "label"=>"Reimprimir Documento"));
		$grid->wbotonadd(array("id"=>"cprecios", "img"=>"images/precio.png",   "alt" => 'Precios',             "label"=>"Cambiar Precios"));
		$grid->wbotonadd(array("id"=>"serie",    "img"=>"images/editar.png",   "alt" => 'Cambiar Numero',      "label"=>"Cambiar Numero "));
		$grid->wbotonadd(array("id"=>"reversar", "img"=>"images/arrow_up.png", "alt" => 'Actualizar/Reversar', "label"=>"Actualizar Reversar"));
		$WestPanel = $grid->deploywestp();

		//Panel Central y Sur
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Panel de pie de forma
		$adic = array(
			array("id"=>"fcompra",  "title"=>"Modificar Compra"),
			array("id"=>"factuali", "title"=>"Actualizar"),
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);


		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('SCST', 'JQ');
		$param['otros']        = $this->datasis->otros('SCST', 'JQ');

		$param['centerpanel']  = $centerpanel;
		//$param['funciones']    = $funciones;

		$param['temas']        = array('proteo','darkness','anexos1');

		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;

		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '<script type="text/javascript">';

		// Imprime Compra
		$bodyscript .= '
		jQuery("#imprimir").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('formatos/ver/COMPRA').'/\'+id+"/id"').';
			} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
		});';

		$bodyscript .= '
		jQuery("#serie").click( function(){
			var gr = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if( gr != null )
				jQuery("#newapi'.$grid0.'").jqGrid(\'editGridRow\',gr,
				{
				closeAfterEdit:true,
				mtype: "POST",
				height:200,
				width: 350,
				closeOnEscape: true,
				top: 50,
				left:20,
				recreateForm:true,
				afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},
				reloadAfterSubmit:false
				});
			else
				$.prompt("<h1>Por favor Seleccione un Movimiento</h1>");
		});';
		
		$bodyscript .= '
		function scstadd() {
			$.post("'.site_url('compras/scst/solo/create').'",
			function(data){
				$("#factuali").html("");
				$("#fcompra").html(data);
				$( "#fcompra" ).dialog( "open" );
			})
		};';

		$bodyscript .= '
		function scstedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				if ( ret.actuali >= ret.fecha ) {
					$.prompt("<h1>Compra ya Actualizada</h1>Debe reversarla si desea hacer modificaciones");
				} else {
					mId = id;
					$.post("'.site_url('compras/scst/solo/modify').'/"+id, function(data){
						$("#factuali").html("");
						$("#fcompra").html(data);
						$("#fcompra" ).dialog( "open" );
					});
				}
			} else { $.prompt("<h1>Por favor Seleccione una compra</h1>");}
		};';

		
		//Wraper de javascript
		$bodyscript .= '
		$(function() {
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

		//Reversar
		$bodyscript .= '
			$("#reversar").click( function(){
				var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
					mid = id;
					if ( ret.actuali < ret.fecha ){
						$.post("'.site_url('compras/scst/solo/actualizar').'/"+ret.control,
						function(data){
							$("#fcompra").html("");
							$("#factuali").html(data);
							$("#factuali").dialog( "open" );
						})
					} else {
						$.prompt( "<h1>Reversar Compra Nro. "+ret.control+" ?</h1>", {
							buttons: { Reversar: true, Cancelar: false },
							submit: function(e,v,m,f){
								if (v){
									$.get("'.site_url('compras/scst/solo/reversar').'/"+ret.control,
									function(data){
										apprise("<h1>"+data+"</h1>");
										grid.trigger("reloadGrid");
									});
								}
							}
						});
					}
				} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
			});';

		$bodyscript .= '
			$( "#factuali" ).dialog({
				autoOpen: false, height: 300, width: 450, modal: true,
				buttons: {
					"Actualizar": function() {
						var bValid = true;
						var murl = $("#df1").attr("action");
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url: murl,
								data: $("#df1").serialize(),
								success: function(r,s,x){
									var res = $.parseJSON(r);
									if ( res.status == "A"){
										apprise(res.mensaje);
										$( "#factuali" ).dialog( "close" );
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('formatos/ver/COMPRA').'/\'+mid+"/id"').';
										return true;
									} else {
										apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
									}
								}
							});
						}
					},
					Cancelar: function() { $( this ).dialog( "close" ); }
				},
				close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
			});';

		//Cambiar Precios
		$bodyscript .= '
			$( "#cprecios" ).click(function() {
				var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					if ( ret.actuali >= ret.fecha ) {
						$.prompt("<h1>Compra ya Actualizada</h1>Debe reversarla si desea hacer modificaciones");
					} else {
						mId = id;
						$.post("'.site_url('compras/scst/solo/cprecios').'/"+ret.control, function(data){
							$("#factuali").html("");
							$("#fcompra").html(data);
							$( "#fcompra" ).dialog( "open" );
						});
					}
				} else { $.prompt("<h1>Por favor Seleccione una compra no actualizada</h1>");}
			});
		';


		$bodyscript .= '
			$( "#fcompra" ).dialog({
				autoOpen: false, height: 570, width: 860, modal: true,
				buttons: {
					"Guardar": function() {
						var bValid = true;
						var murl = $("#df1").attr("action");
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url: murl,
								data: $("#df1").serialize(),
								success: function(r,s,x){
									var res = $.parseJSON(r);
									if ( res.status == "A"){
										apprise(res.mensaje);
										$( "#fcompra" ).dialog( "close" );
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('formatos/ver/COMPRA').'/\'+res.id+\'/id\'').';
										return true;
									} else if ( res.status == "C"){
										apprise("<div style=\"font-size:16px;font-weight:bold;background:green;color:white\">Mensaje:</div> <h1>"+res.mensaje);
									} else {
										apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
									}
								}
							});
						}
					},
					Cancelar: function() { $( this ).dialog( "close" ); }
				},
				close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
			});
		});';

		$bodyscript .= "\n</script>\n";

		return $bodyscript;

	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;


		$grid->addField('tipo_doc');
		$grid->label('Tipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('serie');
		$grid->label('Serie');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 20 }',
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

		$grid->addField('recep');
		$grid->label('Recep');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('actuali');
		$grid->label('Actualizada');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('proveed');
		$grid->label('Proveed');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('depo');
		$grid->label('Depo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 4 }',
		));


		$grid->addField('orden');
		$grid->label('Orden');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('ncont');
		$grid->label('Ncont');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('montotot');
		$grid->label('Montotot');
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


		$grid->addField('montoiva');
		$grid->label('Montoiva');
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


		$grid->addField('montonet');
		$grid->label('Montonet');
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

		$grid->addField('vence');
		$grid->label('Vence');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('nfiscal');
		$grid->label('Nfiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 12 }',
		));

/*
		$grid->addField('anticipo');
		$grid->label('Anticipo');
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
*/

		$grid->addField('control');
		$grid->label('Control');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

/*
		$grid->addField('flete');
		$grid->label('Flete');
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


		$grid->addField('otros');
		$grid->label('Otros');
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


		$grid->addField('reten');
		$grid->label('Reten');
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


		$grid->addField('ppago');
		$grid->label('Ppago');
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


		$grid->addField('peaje');
		$grid->label('Peaje');
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

		$grid->addField('mdolar');
		$grid->label('Mdolar');
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


		$grid->addField('moriginal');
		$grid->label('Moriginal');
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


		$grid->addField('msubtotal');
		$grid->label('Msubtotal');
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
*/

		$grid->addField('peso');
		$grid->label('Peso');
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


		$grid->addField('transac');
		$grid->label('Transac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->addField('exento');
		$grid->label('Exento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('sobretasa');
		$grid->label('Sobretasa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('reducida');
		$grid->label('Reducida');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('tasa');
		$grid->label('Tasa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));
/*
		$grid->addField('preauto');
		$grid->label('Preauto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));
*/
		$grid->addField('reteiva');
		$grid->label('Retencion IVA');
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

		$grid->addField('fafecta');
		$grid->label('F. Afectada');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ label:"Factura Afectada", size:10, maxlength: 8 }',
		));

/*
		$grid->addField('cexento');
		$grid->label('Cexento');
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

		$grid->addField('cgenera');
		$grid->label('Cgenera');
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

		$grid->addField('civagen');
		$grid->label('Civagen');
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

		$grid->addField('creduci');
		$grid->label('Creduci');
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

		$grid->addField('civared');
		$grid->label('Civared');
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

		$grid->addField('cadicio');
		$grid->label('Cadicio');
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

		$grid->addField('civaadi');
		$grid->label('Civaadi');
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

		$grid->addField('cstotal');
		$grid->label('Cstotal');
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

		$grid->addField('ctotal');
		$grid->label('Ctotal');
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

		$grid->addField('cimpuesto');
		$grid->label('Cimpuesto');
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
*/

		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('notae');
		$grid->label('Nota E.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

/*
		$grid->addField('factura');
		$grid->label('Factura');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));
*/

		$grid->addField('consigna');
		$grid->label('Consigna');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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
		$grid->setHeight('210');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
			if (id){
				var ret = $(gridId1).getRowData(id);
				jQuery(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
				jQuery(gridId2).trigger("reloadGrid");
				$.ajax({
					url: "'.base_url().$this->url.'tabla/"+id,
					success: function(msg){
						$("#ladicional").html(msg);
					}
				});
			}},
			afterInsertRow:
			function( rid, aData, rowe){
				if ( aData.fecha >  aData.actuali ){
					$(this).jqGrid( "setCell", rid, "tipo_doc","", {color:"#FFFFFF", background:"#166D05" });
				} else {
					$(this).jqGrid( "setCell", rid, "tipo_doc", "", {color:"#FFFFFF", background:"#06276B" });
				}
			}
		');


		$grid->setOndblClickRow("");

		$grid->setFormOptionsE('
				       closeAfterEdit:true,
				       mtype: "POST",
				       width: 350,
				       height:200,
				       closeOnEscape: true,
				       top: 50,
				       left:20,
				       recreateForm:true,
				       afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];}
		');
		
		$grid->setFormOptionsA('-');

		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: scstadd,\n\t\teditfunc: scstedit");

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

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('scst');

		$response   = $grid->getData('scst', array(array()), array(), false, $mWHERE, 'actuali', 'asc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			//if(false == empty($data)){
			//	$this->db->insert('scst', $data);
			//	echo "Registro Agregado";
			//	logusu('SCST',"Registro ????? INCLUIDO");
			//} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('scst', $data);
			logusu('SCST',"Registro $id MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//Si no esta actualizado permite borrar
			$check   = $this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE id='$id' AND actuali>=fecha ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; debe reversarlo ";
			} else {
				$control =  $this->datasis->dameval("SELECT control FROM scst WHERE id='$id' ");
				$this->db->simple_query("DELETE FROM scst WHERE id=$id ");
				$this->db->simple_query("DELETE FROM itscst WHERE control=".$this->db->escape($control) );
				logusu('SCST',"Registro ".$control." ELIMINADO");
				echo "Registro Eliminado";
			}
		};

	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));

		$grid->addField('descrip');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 45 }',
		));

		$grid->addField('cantidad');
		$grid->label('Cantidad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('costo');
		$grid->label('Costo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('importe');
		$grid->label('Importe');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('devcant');
		$grid->label('Faltante');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('iva');
		$grid->label('IVA');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 40,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('montoiva');
		$grid->label('Monto IVA');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('control');
		$grid->label('Control');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('ultimo');
		$grid->label('Ultimo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('precio1');
		$grid->label('Precio1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('precio2');
		$grid->label('Precio2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('precio3');
		$grid->label('Precio3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('precio4');
		$grid->label('Precio4');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
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
		$grid->setHeight('170');
		//$grid->setTitle($this->titp);
		$grid->setfilterToolbar(false);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){if (id){var ret = $("#titulos").getRowData(id);}},
				cellEdit: true,
				cellsubmit: "remote",
				cellurl: "'.site_url($this->url.'setdatait/').'"
		');
		$grid->setOndblClickRow("");


		$grid->setFormOptionsE('');
		$grid->setFormOptionsA('');
		$grid->setAfterSubmit('');

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		#Set url
		//$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdatait()
	{
		$id = $this->uri->segment(4);
		if ($id == false ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM scst");
		}
		$control = $this->datasis->dameval("SELECT control FROM scst WHERE id=$id");
		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM itscst WHERE control='$control' ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;

	}

	/**
	* Busca la data en el Servidor por json
	*/
	function setdatait()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			//if(false == empty($data)){
			//	$this->db->insert('scst', $data);
			//	echo "Registro Agregado";
			//	logusu('SCST',"Registro ????? INCLUIDO");
			//} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('itscst', $data);
			logusu('SCST',"Registro $id MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE id='$id' ");
			//if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			//} else {
			//	$this->db->simple_query("DELETE FROM scst WHERE id=$id ");
			//	logusu('SCST',"Registro ????? ELIMINADO");
			//	echo "Registro Eliminado";
			//}
		};

	}

	//*************************************************
	//
	//  Informacion Adicional
	//
	//*************************************************
	function tabla() {
		$id = $this->uri->segment($this->uri->total_segments());
		$proveed = $this->datasis->dameval("SELECT proveed FROM scst WHERE id='$id'");
		$transac = $this->datasis->dameval("SELECT transac FROM scst WHERE id='$id'");
		$salida = '';

		// Cuentas por Cobrar
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE cod_prv = '$proveed' AND abonos <> monto AND tipo_doc <> 'AB' ORDER BY fecha DESC LIMIT 5";
		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento Pendientes en CxC</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			$i = 1;
			foreach ($query->result_array() as $row)
			{
				if ( $i < 6 ) {
					$salida .= "<tr>";
					$salida .= "<td>".$row['tipo_doc']."</td>";
					$salida .= "<td>".$row['numero'].  "</td>";
					$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
					$salida .= "</tr>";
				}
				if ( $i == 6 ) {
					$salida .= "<tr>";
					$salida .= "<td colspan=3>Mas......</td>";
					$salida .= "</tr>";
				}
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
				$i ++;
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();
		echo $salida;
	}


/*
class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(201,1);
		$this->back_dataedit='compras/scst/datafilter';
	}

	function index(){
		redirect('compras/scst/extgrid');
	}

	function datafilter(){
		//redirect('compras/scst/extgrid');

		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$atts = array(
		  'width'      => '800',
		  'height'     => '600',
		  'scrollbars' => 'yes',
		  'status'     => 'yes',
		  'resizable'  => 'yes',
		  'screenx'    => '0',
		  'screeny'    => '0'
		);

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Compras');
		$filter->db->select=array('numero','fecha','recep','vence','depo','nombre','montoiva','montonet','montotot','reiva','proveed','control','serie','usuario');
		$filter->db->from('scst');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';
		$filter->fechah->group='Fecha Emisi&oacute;n';
		$filter->fechad->group='Fecha Emisi&oacute;n';

		$filter->tipo = new dropdownField('Tipo Doc.', 'tipo_doc');
		$filter->tipo->option('','Todos');
		$filter->tipo->option('FC','Factura a Cr&eacute;dito');
		$filter->tipo->option('NC','Nota de Cr&eacute;dito');
		$filter->tipo->option('NE','Nota de Entrega');
		$filter->tipo->style='width:140px;';

		$filter->numero = new inputField('Factura', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor','proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri  = anchor('compras/scst/dataedit/show/<#control#>','<#tipo_doc#><#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/COMPRA/<#control#>','Ver HTML',$atts);
		$uri3 = anchor_popup('compras/scst/dataedit/show/<#control#>','<#serie#>',$atts);

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 30;


		$uri_2  = "<a href='javascript:void(0);' ";
		$uri_2 .= 'onclick="window.open(\''.base_url()."compras/scst/serie/<#control#>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$uri_2 .= img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12','title'=>'Consultar'));
		$uri_2 .= "</a>";

		$uri_2  = anchor('compras/scst/dataedit/show/<#control#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'16','title'=>'Editar')));
		$uri_2 .= "&nbsp;";
		$uri_2 .= anchor('formatos/verhtml/COMPRA/<#control#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'HTML')));

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:scstserie(\"<#control#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Serie', 'title' => 'Modifica Nro. de Serie','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";

		$grid->column('Acci&oacute;n',$uri_2);
		$grid->column_orderby('Factura',$uri,'numero');
		$grid->column_orderby('Serie',$uri_3.'<#serie#>','serie');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Recep','<dbdate_to_human><#recep#></dbdate_to_human>','recep','align=\'center\'');
		$grid->column_orderby('Vence','<dbdate_to_human><#vence#></dbdate_to_human>','vence','align=\'center\'');
		$grid->column_orderby('Alma','depo','depo');
		$grid->column_orderby('Prv.','proveed','proveed');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Base' ,'<nformat><#montotot#></nformat>','montotot','align=\'right\'');
		$grid->column_orderby('IVA'   ,'<nformat><#montoiva#></nformat>','montoiva','align=\'right\'');
		$grid->column_orderby('Importe' ,'<nformat><#montonet#></nformat>','montonet','align=\'right\'');
		$grid->column_orderby('Ret.IVA' ,'<nformat><#reteiva#></nformat>','reteiva','align=\'right\'');
		$grid->column_orderby('Control','control','control');
		$grid->column_orderby('Usuario','usuario','usuario');
		//$grid->column('Vista',$uri2,'align=\'center\'');

		$grid->add('compras/scst/dataedit/create');
		$grid->build('datagridST');

		//************ SUPER TABLE *************
		$extras = '
		<script type="text/javascript">
		//<![CDATA[
		(function() {
			var mySt = new superTable("demoTable", {
			cssSkin : "sSky",
			fixedCols : 1,
			headerRows : 1,
			onStart : function () {	this.start = new Date();},
			onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
			});
		})();
		//]]>
		</script>';

		$style ='
		<style type="text/css">
		.fakeContainer {
		    margin: 5px;
		    padding: 0px;
		    border: none;
		    width: 740px;
		    height: 320px;
		    overflow: hidden;
		}
		</style>';
		//****************************************

		$script ='<script type="text/javascript">
		function scstserie(mcontrol){
			//var mserie=Prompt("Numero de Serie");
			//jAlert("Cancelado","Informacion");
			jPrompt("Numero de Serie","" ,"Cambio de Serie", function(mserie){
				if( mserie==null){
					jAlert("Cancelado","Informacion");
				} else {
					$.ajax({ url: "'.site_url().'compras/scst/scstserie/"+mcontrol+"/"+mserie,
						success: function(msg){
							jAlert("Cambio Finalizado "+msg,"Informacion");
							location.reload();
							}
					});
				}
			})
		}
		</script>';

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']  .= style('jquery.alerts.css');

		$data['extras']  = $extras;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery.alerts.js');
		$data['script'] .= script('superTables.js');
		$data['script'] .= $script;

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['head']    = $this->rapyd->get_head();

		$data['title']   =heading('Compras');
		$this->load->view('view_ventanas', $data);

	}
*/


	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$this->rapyd->uri->keep_persistence();

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>','pond'=>'costo_<#i#>','iva'=>'iva_<#i#>','peso'=>'sinvpeso_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'script'  => array('post_modbus_sinv(<#i#>)'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'where'   =>'activo = "S"');

		$sprvbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  => array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'proveed', 'nombre'=>'nombre'),
			'script'  => array('post_modbus_sprv()'),
			'titulo'  =>'Buscar Proveedor');

		$do = new DataObject('scst');
		$do->rel_one_to_many('itscst', 'itscst', 'control');
		$do->pointer('sprv' ,'sprv.proveed=scst.proveed','sprv.nombre AS sprvnombre','left');
		$do->rel_pointer('itscst','sinv','itscst.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Compras',$do);
		$edit->set_rel_title('itscst','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->pre_process('delete' ,'_pre_delete');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->back_url = $this->back_dataedit;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;
		$edit->fecha->rule ='required';
		$edit->fecha->calendar=false;
		$transac=$edit->get_from_dataobjetct('transac');

		$edit->vence = new DateonlyField('Vence', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->size = 10;
		$edit->vence->rule ='required';
		$edit->vence->calendar=false;

		$edit->actuali = new DateonlyField('Actualizado', 'actuali','d/m/Y');
		//$edit->actuali->insertValue = date('Y-m-d');
		$edit->actuali->when=array('show');
		$edit->actuali->size = 10;
		$edit->actuali->mode ='autohide';
		$edit->actuali->calendar=false;


		$edit->recep = new DateonlyField('recibido', 'v','d/m/Y');
		//$edit->recep->insertValue = date('Y-m-d');
		$edit->recep->size = 10;
		$edit->recep->mode = 'autohide';
		$edit->recep->when=array('show');
		$edit->recep->calendar=false;


		$edit->serie = new inputField('N&uacute;mero', 'serie');
		$edit->serie->size = 15;
		$edit->serie->autocomplete=false;
		$edit->serie->rule = 'required';
		$edit->serie->mode = 'autohide';
		$edit->serie->maxlength=12;

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size     = 7;
		$edit->proveed->maxlength= 5;
		$edit->proveed->autocomplete=false;
		$edit->proveed->rule     = 'required';
		$edit->proveed->append($this->datasis->modbus($sprvbus));

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->cfis = new inputField('N&uacute;mero f&iacute;scal', 'nfiscal');
		$edit->cfis->size = 15;
		$edit->cfis->autocomplete=false;
		$edit->cfis->rule = 'required';
		$edit->cfis->maxlength=12;

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'depo');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:145px;';

		$edit->tipo = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo->option('FC','Factura a Cr&eacute;dito');
		//$edit->tipo->option('NC','Nota de Cr&eacute;dito'); //Falta implementar los metodos post para este caso
		//$edit->tipo->option('NE','Nota de Entrega');        //Falta implementar los metodos post para este caso
		$edit->tipo->rule = 'required';
		$edit->tipo->style='width:140px;';

		$edit->peso  = new hiddenField('Peso', 'peso');
		$edit->peso->size = 20;
		$edit->peso->css_class='inputnum';

		$edit->orden  = new inputField('Orden', 'orden');
		$edit->orden->when=array('show');
		$edit->orden->size = 15;

		$edit->credito  = new inputField('Cr&eacute;dito', 'credito');
		$edit->credito->size = 12;
		$edit->credito->css_class='inputnum';
		$edit->credito->when=array('show');

		$edit->montotot  = new inputField('Subtotal', 'montotot');
		$edit->montotot->onkeyup='cmontotot()';
		$edit->montotot->size = 12;
		$edit->montotot->autocomplete=false;
		$edit->montotot->css_class='inputnum';

		$edit->montoiva  = new inputField('IVA', 'montoiva');
		$edit->montoiva->onkeyup='cmontoiva()';
		$edit->montoiva->size = 12;
		$edit->montoiva->autocomplete=false;
		$edit->montoiva->css_class='inputnum';

		$edit->montonet  = new hiddenField('Total', 'montonet');
		//$edit->montonet->size = 20;
		//$edit->montonet->css_class='inputnum';

		$edit->anticipo  = new inputField('Anticipo', 'anticipo');
		$edit->anticipo->size = 12;
		$edit->anticipo->css_class='inputnum';
		$edit->anticipo->when=array('show');

		$edit->inicial  = new inputField('Contado', 'inicial');
		$edit->inicial->size = 12;
		$edit->inicial->css_class='inputnum';
		$edit->inicial->when=array('show');

		$edit->rislr  = new inputField('Retenci&oacute;n ISLR', 'reten');
		$edit->rislr->size = 12;
		$edit->rislr->css_class='inputnum';
		$edit->rislr->when=array('show');

		$edit->riva  = new inputField('Retenci&oacute;n IVA', 'reteiva');
		$edit->riva->size = 11;
		$edit->riva->css_class='inputnum';
		//$edit->riva->when=array('show');

		$edit->mdolar  = new inputField('Monto US $', 'mdolar');
		$edit->mdolar->size = 12;
		$edit->mdolar->css_class='inputnum';
		$edit->mdolar->when=array('show');

		$edit->observa1 = new textareaField('Observaci&oacute;n', 'observa1');
		$edit->observa1->cols=90;
		$edit->observa1->rows=3;

		$edit->observa2 = new textareaField('Observaci&oacute;n', 'observa2');
		$edit->observa2->when=array('show');
		$edit->observa2->rows=3;

		$edit->observa3 = new textareaField('Observaci&oacute;n', 'observa3');
		$edit->observa3->when=array('show');
		$edit->observa3->rows=3;

		//****************************
		//Campos para el detalle
		//****************************
		$edit->codigo = new inputField('C&oacute;digo', 'codigo_<#i#>');
		$edit->codigo->size=10;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$edit->codigo->autocomplete=false;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'required|callback_chcodigoa';
		$edit->codigo->rel_id   = 'itscst';

		$edit->descrip = new hiddenField('Descripci&oacute;n', 'descrip_<#i#>');
		$edit->descrip->size     =30;
		$edit->descrip->db_name  ='descrip';
		$edit->descrip->maxlength=12;
		$edit->descrip->rel_id  ='itscst';

		$edit->cantidad = new inputField('Cantidad', 'cantidad_<#i#>');
		$edit->cantidad->db_name      = 'cantidad';
		$edit->cantidad->css_class    = 'inputnum';
		$edit->cantidad->rel_id       = 'itscst';
		$edit->cantidad->maxlength    = 10;
		$edit->cantidad->size         =  8;
		$edit->cantidad->autocomplete = false;
		$edit->cantidad->onkeyup      = 'importe(<#i#>)';
		$edit->cantidad->rule         = 'required|positive';
		$edit->cantidad->showformat   = 'decimal';

		$edit->costo = new inputField('Costo', 'costo_<#i#>');
		$edit->costo->css_class       = 'inputnum';
		$edit->costo->rule            = 'required|positive';
		$edit->costo->onkeyup         = 'importe(<#i#>)';
		$edit->costo->size            = 10;
		$edit->costo->autocomplete    = false;
		$edit->costo->db_name         = 'costo';
		$edit->costo->rel_id          = 'itscst';
		$edit->costo->showformat      = 'decimal';

		$edit->importe = new inputField('Importe', 'importe_<#i#>');
		$edit->importe->db_name       = 'importe';
		$edit->importe->size          = 12;
		$edit->importe->rel_id        = 'itscst';
		$edit->importe->autocomplete  = false;
		$edit->importe->onkeyup       = 'costo(<#i#>)';
		$edit->importe->css_class     = 'inputnum';
		$edit->importe->showformat    = 'decimal';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name      = 'sinvpeso';
		$edit->sinvpeso->rel_id       = 'itscst';
		$edit->sinvpeso->pointer      = true;
		$edit->sinvpeso->showformat   = 'decimal';

		$edit->iva = new hiddenField('Impuesto', 'iva_<#i#>');
		$edit->iva->db_name           = 'iva';
		$edit->iva->rel_id            = 'itscst';
		$edit->iva->showformat        = 'decimal';

		//fin de campos para detalle

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$recep  =strtotime($edit->get_from_dataobjetct('recep'));
		$fecha  =strtotime($edit->get_from_dataobjetct('fecha'));
		$actuali=strtotime($edit->get_from_dataobjetct('actuali'));

		if($actuali < $fecha){
			$control=$this->rapyd->uri->get_edited_id();
			$accion="javascript:window.location='".site_url('compras/scst/actualizar/'.$control)."'";
			$accio2="javascript:window.location='".site_url('compras/scst/cprecios/'.$control)."'";
			$accio3="javascript:window.location='".site_url('compras/scst/montoscxp/modify/'.$control)."'";

			$edit->button_status('btn_actuali','Actualizar'     ,$accion,'TR','show');
			$edit->button_status('btn_precio' ,'Asignar precios',$accio2,'TR','show');
			$edit->button_status('btn_cxp'    ,'Ajuste CxP'     ,$accio3,'TR','show');
			$edit->buttons('save', 'delete','modify', 'exit','add_rel','add');
		} else {
			$control=$this->rapyd->uri->get_edited_id();
			$accion="javascript:window.location='".site_url('compras/scst/reversar/'.$control)."'";
			$edit->button_status('btn_reversar','Reversar'     ,$accion,'TR','show');
			$edit->buttons('save', 'exit','add_rel');
		}

		if($this->genesal){
			$edit->build();
			$smenu['link']  =  barra_menu('201');
			$data['smenu']  =  $this->load->view('view_sub_menu', $smenu,true);
			$conten['form'] =& $edit;
			$conten['solo'] = $this->solo;
			$ffecha=$edit->get_from_dataobjetct('fecha');
			$conten['alicuota']=$this->datasis->ivaplica(($ffecha==false)? null : $ffecha);
			if (  $this->solo ){
				$this->load->view('view_compras', $conten);
			} else {
				$data['script']  = script('jquery.js');
				$data['script'] .= script('jquery-ui.js');
				$data['script'] .= script('plugins/jquery.numeric.pack.js');
				$data['script'] .= script('plugins/jquery.floatnumber.js');
				$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
				$data['script'] .= phpscript('nformat.js');

				$data['head']    = $this->rapyd->get_head();
				$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
				
				$data['content'] = $this->load->view('view_compras', $conten, true);
				$data['title']   = heading('Compras');
				$this->load->view('view_ventanas', $data);
			}
		}else{
			$edit->on_save_redirect=false;
			$edit->build();
			if($edit->on_success()){
				$this->claves=$edit->_dataobject->pk;
				$this->claves['control']=$edit->_dataobject->get('control');
				$rt= 'Compra Guardada';
			}elseif($edit->on_error()){
				$rt= html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}
			return $rt;
		}
		
	}

	function solo() {
		$this->solo = true;
		$id = $this->uri->segment($this->uri->total_segments());

		//Creando Compra
		if ( $id == 'create'){
			$this->dataedit();
		} elseif ( $id == 'insert'){
			$this->genesal = false;
			$rt = $this->dataedit();
			$id = $this->claves["id"];
			if ( strlen($rt) > 0 )
				echo '{"status":"A","id":"'.$id.'" ,"mensaje":"'.$rt.'"}';
		} elseif ( $id == 'process'){
			$control = $this->uri->segment($this->uri->total_segments()-1);
			$rt = $this->actualizar($control);
			if ( strlen($rt[1]) > 0 )
				if ( $rt[0] === false ) $p = 'E'; else $p='A';
				echo '{"status":"'.$p.'","id":"'.$control.'" ,"mensaje":"<h1>'.$rt[1].'</h1>"}';
		} else {
			$modo = $this->uri->segment($this->uri->total_segments()-1);

			if ( $modo == 'actualizar' ){
				$this->actualizar($id);
			} elseif ( $modo == 'reversar' ){
				$rt = $this->reversar($id);
				echo $rt;
			} elseif ( $modo == 'cprecios' ){
				$rt = $this->cprecios($id);
				echo $rt;
			} else {
				if ( $modo == 'update' ) $this->genesal = false;
				$rt = $this->dataedit();
				if ( strlen($rt) > 0 )
					echo '{"status":"A","id":"'.$id.'" ,"mensaje":"'.$rt.'"}';
			}
		}
	}

	// Precios 
	function cprecios($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datagrid','fields');

		$error='';
		$msj='';

		//if($this->input->post('pros') !== false){
		if($this->input->post('scstp_1') !== false){
			$precio1=$this->input->post('scstp_1');
			$precio2=$this->input->post('scstp_2');
			$precio3=$this->input->post('scstp_3');
			$precio4=$this->input->post('scstp_4');

			foreach(array_keys($precio1) as $ind){
				$pt1 = $precio1[$ind]>=$precio2[$ind] && $precio2[$ind]>=$precio3[$ind] && $precio3[$ind]>=$precio4[$ind];
				$pt2 = $precio1[$ind]>0 && $precio2[$ind]>0 && $precio3[$ind]>0 && $precio4[$ind]>0;
				if($pt1 && $pt2){
					$data=array(
						'precio1'=>$precio1[$ind],
						'precio2'=>$precio2[$ind],
						'precio3'=>$precio3[$ind],
						'precio4'=>$precio4[$ind]
					);

					$where = 'id = '.$this->db->escape($ind);
					$mSQL = $this->db->update_string('itscst',$data,$where);
					$ban=$this->db->simple_query($mSQL);
				}else{
					$error='Los precios deben cumplir esta regla (precio 1 >= precio 2 >= precio 3 >= precio 4) y mayores a cero';
				}
			}
			if(strlen($error)==0){
				$msj='Nuevos Precios guardados';
			}
			if ( $this->solo )
				return '{"status":"C","id":"'.$control.'" ,"mensaje":"'.$msj.$error.'"}';
			
		}

		if ( $this->solo )
			$ggrid =form_open('/compras/scst/solo/cprecios/'.$control, array("id" => "df1"));
		else
			$ggrid =form_open('/compras/scst/cprecios/'.$control);


		function costo($formcal,$pond,$ultimo,$standard,$existen,$itcana){
			$CI =& get_instance();
			$costo_pond=$CI->_pond($existen,$itcana,$pond,$ultimo);
			//echo "_pond($existen,$itcana,$pond,$ultimo);".br();
			//$costo_pond=(($pond*$existen)+($itcana*$ultimo))/($itcana+$existen);
			return $CI->_costos($formcal,$costo_pond,$ultimo,$standard);
		}

		function margen($formcal,$pond,$ultimo,$standard,$existen,$itcana,$precio,$iva){
			$costo=costo($formcal,$pond,$ultimo,$standard,$existen,$itcana);
			if($precio==0) return 0;
			return round(100-(($costo*100)/($precio/(1+($iva/100)))),2);
		}

		function tcosto($id,$iva,$formcal,$pond,$ultimo,$standard,$existen,$itcana){
			$costo=costo($formcal,$pond,$ultimo,$standard,$existen,$itcana);
			$rt = nformat($costo);

			$rt.= '<input type="hidden" id="costo['.$id.']" name="costo['.$id.']" value="'.$costo.'" />';
			$rt.= '<input type="hidden" id="iva['.$id.']" name="iva['.$id.']" value="'.$iva.'" />';
			return $rt;
		}

		$grid = new DataGrid('Precios de art&iacute;culos');
		$grid->use_function('costo','margen','tcosto');
		$grid->order_by('descrip');
		$select=array('b.codigo','b.descrip','b.formcal','a.costo','b.ultimo','b.pond','b.standard','a.id',
					  'a.precio1 AS scstp_1','a.precio2 AS scstp_2','a.precio3 AS scstp_3','a.precio4 AS scstp_4',
					  'b.precio1 AS sinvp1' ,'b.precio2 AS sinvp2' ,'b.precio3 AS sinvp3' ,'b.precio4 AS sinvp4',
					  'b.formcal','a.cantidad','b.existen','b.iva'
					);
		$grid->db->select($select);
		$grid->db->from('itscst AS a');
		$grid->db->join('sinv AS b','a.codigo=b.codigo');
		$grid->db->where('control' , $control);

		//$grid->column('C&oacute;digo'     , '' );
		$grid->column_orderby('Descripci&oacute;n', '<b class=\'mininegro\'><#codigo#></b><br><#descrip#>', 'descrip');

		$ittt=array('sinvp1','sinvp2','sinvp3','sinvp4');
		$itt=array('scstp_1','scstp_2','scstp_3','scstp_4');
		foreach ($itt as $id=>$val){
			$ind = $val;

			$campo = new inputField('Campo', $ind);
			$campo->grid_name=$ind.'[<#id#>]';
			$campo->status   ='modify';
			$campo->size     =8;
			$campo->autocomplete=false;
			$campo->css_class='inputnum';
			$campo->append('<#'.$ittt[$id].'#>');
			$campo->disable_paste=true;

			$grid->column('Precio '.($id+1) , $campo,'align=\'center\'');
		}

		$itt=array('margen_1','margen_2','margen_3','margen_4');
		foreach ($itt as $id=>$val){
			$ind = $val;

			$campo = new inputField('Campo', $ind);
			$campo->grid_name=$ind.'[<#id#>]';
			$campo->pattern  ='<margen><#formcal#>|<#pond#>|<#costo#>|<#standard#>|<#existen#>|<#cantidad#>|<#scstp_'.($id+1).'#>|<#iva#></margen>';
			$campo->status   ='modify';
			$campo->size     =3;
			$campo->autocomplete=false;
			$campo->css_class='inputnum';
			$campo->disable_paste=true;

			$grid->column('Marg.'.($id+1) , $campo,'align=\'center\'');
		}
		$grid->column('Costo' , '<tcosto><#id#>|<#iva#>|<#formcal#>|<#pond#>|<#costo#>|<#standard#>|<#existen#>|<#cantidad#></tcosto>','align=\'right\'');
		if ( !$this->solo ){
			$action = "javascript:window.location='".site_url('compras/scst/dataedit/show/'.$control)."'";
			$grid->button('btn_regresa', 'Regresar', $action, 'TR');
			$grid->submit('pros', 'Guardar','BR');
		}

		$grid->build();

		$ggrid.=$grid->output;
		$ggrid.=form_close();

		$script='<script language="javascript" type="text/javascript">
		$(function(){
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
			$(\'input[name^="margen_"]\').keyup(function() {
				nom=this.name;
				pos0=this.name.lastIndexOf("_");
				pos1=this.name.lastIndexOf("[");
				pos2=this.name.lastIndexOf("]");
				if(pos0>0 && pos1>0 && pos2>0){
					idp = this.name.substring(pos0+1,pos1);
					ind = this.name.substring(pos1+1,pos2);

					costo  = Number($("#costo\\\["+ind+"\\\]").val());
					iva    = Number($("#iva\\\["+ind+"\\\]").val());
					margen = Number($(this).val());

					precio = roundNumber((costo*100/(100-margen))*(1+(iva/100)),2);
					$("#scstp_"+idp+"\\\["+ind+"\\\]").val(precio);
				}
			});

			$(\'input[name^="scstp_"]\').keyup(function() {
				nom=this.name;
				pos0=this.name.lastIndexOf("_");
				pos1=this.name.lastIndexOf("[");
				pos2=this.name.lastIndexOf("]");
				if(pos0>0 && pos1>0 && pos2>0){
					idp = this.name.substring(pos0+1,pos1);
					ind = this.name.substring(pos1+1,pos2);

					precio = Number($(this).val());
					costo  = Number($("#costo\\\["+ind+"\\\]").val());
					iva    = Number($("#iva\\\["+ind+"\\\]").val());

					margen=roundNumber(100-((costo*100)/(precio/(1+(iva/100)))),2);
					$("#margen_"+idp+"\\\["+ind+"\\\]").val(margen);
				}
			});
		});
		</script>';
		$data['content']  = '<div class="alert">'.$error.'</div>';
		$data['content'] .= '<div>'.$msj.'</div>';
		$data['content'] .= $ggrid;

		if ( $this->solo ){
			$mensaje = "<table><tr><td>Mensaje: ".$msj."</td><td>Error: ".$error."</td></tr></table>\n";
			return $script."\n".$data['content'];
		} else {
			$data['title']    = heading('Cambio de precios');
			$data['script']   = $script;
			$data['script']  .= phpscript('nformat.js');
			$data['head']     = $this->rapyd->get_head();
			$data['head']     = script('jquery.pack.js');
			$data['head']     = script('plugins/jquery.numeric.pack.js');
			$data['head']     = script('plugins/jquery.floatnumber.js');
			$data['head']    .= style('estilos.css');
			$this->load->view('view_ventanas', $data);
		}
	}

	function montoscxp(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();
		$control=$this->rapyd->uri->get_edited_id();

		//$ffecha=$edit->get_from_dataobjetct('fecha');
		$ffecha=false;
		$alicuota=$this->datasis->ivaplica(($ffecha==false)? null : $ffecha);

		$edit = new DataEdit('Compras','scst');
		$edit->back_url = 'compras/scst/dataedit/show/'.$control;
		//$edit->post_process('update' ,'_post_cxp_update');

		//Para CXP
		$edit->cexento = new inputField('Excento', 'cexento');
		$edit->cexento->size = 15;
		$edit->cexento->autocomplete=false;
		$edit->cexento->showformat= 'decimal';
		$edit->cexento->onkeyup='ctotales()';
		$edit->cexento->rule='numeric';
		$edit->cexento->css_class='inputnum';

		$edit->cgenera = new inputField('Base imponible tasa General', 'cgenera');
		$edit->cgenera->size = 15;
		$edit->cgenera->onkeyup='cal_iva('.$alicuota['tasa'].',\'civagen\',this.value)';
		$edit->cgenera->css_class='inputnum';
		$edit->cgenera->showformat= 'decimal';
		$edit->cgenera->rule='numeric';
		$edit->cgenera->autocomplete=false;

		$edit->civagen = new inputField('Monto alicuota tasa General', 'civagen');
		$edit->civagen->size = 10;
		$edit->civagen->autocomplete=false;
		$edit->civagen->showformat= 'decimal';
		$edit->civagen->onkeyup='cal_base('.$alicuota['tasa'].',\'cgenera\',this.value)';
		$edit->civagen->rule='numeric';
		$edit->civagen->css_class='inputnum';

		$edit->creduci = new inputField('Base imponible tasa Reducida', 'creduci');
		$edit->creduci->size = 15;
		$edit->creduci->autocomplete=false;
		$edit->creduci->showformat= 'decimal';
		$edit->creduci->onkeyup='cal_iva('.$alicuota['redutasa'].',\'civared\',this.value)';
		$edit->creduci->rule='numeric';
		$edit->creduci->css_class='inputnum';

		$edit->civared = new inputField('Monto alicuota tasa Reducida', 'civared');
		$edit->civared->size = 10;
		$edit->civared->autocomplete=false;
		$edit->civared->showformat= 'decimal';
		$edit->civared->onkeyup='cal_base('.$alicuota['redutasa'].',\'creduci\',this.value)';
		$edit->civared->css_class='inputnum';

		$edit->cadicio = new inputField('Base imponible tasa Adicional', 'cadicio');
		$edit->cadicio->size = 15;
		$edit->cadicio->autocomplete=false;
		$edit->cadicio->showformat= 'decimal';
		$edit->cadicio->onkeyup='cal_iva('.$alicuota['sobretasa'].',\'civaadi\',this.value)';
		$edit->cadicio->css_class='inputnum';

		$edit->civaadi = new inputField('Monto alicuota tasa Adicional', 'civaadi');
		$edit->civaadi->size = 10;
		$edit->civaadi->autocomplete=false;
		$edit->civaadi->showformat= 'decimal';
		$edit->civaadi->rule='numeric';
		$edit->civaadi->onkeyup='cal_base('.$alicuota['sobretasa'].',\'cadicio\',this.value)';
		$edit->civaadi->css_class='inputnum';

		$edit->cstotal = new hiddenField('Sub-total', 'cstotal');
		$edit->cstotal->size = 20;
		$edit->cstotal->rule='numeric';
		$edit->cstotal->css_class='inputnum';

		$edit->riva = new inputField('Retenci&oacute;n IVA', 'reteiva');
		$edit->riva->size = 10;
		$edit->riva->showformat= 'decimal';
		$edit->riva->rule='numeric';
		$edit->riva->autocomplete=false;
		$edit->riva->css_class='inputnum';
		$contribu= $this->datasis->traevalor('CONTRIBUYENTE');
		$rif     = $this->datasis->traevalor('RIF');
		if(!($contribu=='ESPECIAL' && strtoupper($rif[0])!='V')){
			$edit->riva->when=array('show');
		}

		$edit->cimpuesto = new hiddenField('Total Impuesto', 'cimpuesto');
		$edit->cimpuesto->size = 10;
		$edit->cimpuesto->rule='numeric';
		$edit->cimpuesto->autocomplete=false;
		$edit->cimpuesto->css_class='inputnum';

		$edit->ctotal  = new hiddenField('Total', 'ctotal');
		$edit->ctotal->size = 20;
		$edit->ctotal->rule='numeric';
		$edit->ctotal->css_class='inputnum';
		//Fin de CxP

		$edit->buttons('save', 'undo','modify', 'back');
		$edit->build();

		$conten['form'] =& $edit;

		//$ffecha=$edit->get_from_dataobjetct('fecha');
		$ffecha=false;
		$conten['alicuota'] = $alicuota;

		$proveed=$edit->get_from_dataobjetct('proveed');
		$conten['priva']   = $this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($proveed));
		$conten['priva']   = $conten['priva']/100;
		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery.layout.js');
		$data['script'] .= script('grid.locale-sp.js');
		$data['script'] .= script('ui.multiselect.js');
		$data['script'] .= script('jquery.jqGrid.min.js');
		$data['script'] .= script('jquery.tablednd.js');
		$data['script'] .= script('jquery.contextmenu.js');
		$data['script'] .= style('ui.jqgrid.css');
		$data['script'] .= style('ui.multiselect.css');

		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= phpscript('nformat.js');

		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		$data['content'] = $this->load->view('view_compras_cmontos', $conten,true);
		$data['title']   = heading('Compras');

		$this->load->view('view_ventanas', $data);
	}

	function chcodigoa($codigo){
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM sinv WHERE activo=\'S\' AND codigo='.$this->db->escape($codigo));
		if(empty($cana) || $cana==0){
			$this->validation->set_message('chcodigoa', 'El campo %s contiene un codigo no v&aacute;lido o inactivo');
			return false;
		}
		return true;
	}

	function dpto() {
		$this->rapyd->load("dataform");
		$campo='ccosto'.$this->uri->segment(4);
 		$script='
 		function pasar(){
			if($F("departa")!="-!-"){
				window.opener.document.getElementById("'.$campo.'").value = $F("departa");
				window.close();
			}else{
				alert("Debe elegir un departamento");
			}
		}';

		$form = new DataForm('');
		$form->script($script);
		$form->fdepar = new dropdownField("Departamento", "departa");
		$form->fdepar->option('-!-','Seleccion un departamento');
		$form->fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$form->fdepar->onchange='pasar()';
		$form->build_form();

		$data['content'] =$form->output;
		$data['head']    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   =heading('Seleccione un departamento');
		$this->load->view('view_detalle', $data);
	}

	function scstserie(){
		$serie      = $this->uri->segment($this->uri->total_segments());
		$control    = $this->uri->segment($this->uri->total_segments()-1);
		$dbserie    = $this->db->escape($serie);
		$dbconstrol = $this->db->escape($control);
		if (!empty($serie)) {
			$this->db->simple_query("UPDATE scst SET serie=$dbserie WHERE control=$dbcontrol");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('SCST',"Cambia Nro. Serie $control ->  $serie ");
	}

	function autocomplete($campo,$cod=FALSE){
		if($cod!==false){
			$cod=$this->db->escape_like_str($cod);
			$qformato=$this->datasis->formato_cpla();
			$data['control']="SELECT control AS c1,fecha AS c2,numero AS c3,nombre AS c4 FROM scst WHERE control LIKE '$cod%' ORDER BY control DESC LIMIT 10";
			if(isset($data[$campo])){
				$query=$this->db->query($data[$campo]);
				if($query->num_rows() > 0){
					foreach($query->result_array() AS $row){
						echo $row['c1'].'|'.dbdate_to_human($row['c2']).'|'.$row['c3'].'|'.$row['c4']."\n";
					}
				}
			}
		}
	}

	function actualizar($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataform');

		if ( $this->solo )
			$form = new DataForm("compras/scst/solo/actualizar/$control/process");
		else 
			$form = new DataForm("compras/scst/actualizar/$control/process");
	
		$proveed = $this->datasis->dameval("SELECT CONCAT('Proveedor: </td><td><b>(<b>',proveed,'</b>)</td><td colspan=5><b>',nombre,'</b>') nombre FROM scst WHERE control=$control");
		$compra  = $this->datasis->dameval("SELECT CONCAT('Compra: </td><td><b>', serie,'</b></td><td> Fecha: </td><td><b>', fecha, '</b></td><td>Vence: </td><td><b>', vence,'</b>') factura FROM scst WHERE control=$control");
		$montos  = $this->datasis->dameval("SELECT CONCAT('Sub Total: </td><td><b>', format(montotot,2),'</b></td><td> I.V.A.: </td><td><b>', format(montoiva,2), '</b></td><td>Monto: </td><td><b>', format(montonet,2),'</b>') factura FROM scst WHERE control=$control");
		
		$script = '<script>$(function() {$("#rafecha").datepicker({ dateFormat: "dd/mm/yy" });})</script>';

		$form->aaaa = new containerField('iii',$script."\n<table width='100%' style='background-color:#FBEC88;text-align:center;font-size:12px'><tr><td>".$proveed."</td></tr><tr><td>".$compra."</td></tr><tr><td>".$montos."</td></tr></table><br>&nbsp;");

		$form->cprecio = new  dropdownField ('Cambiar precios', 'cprecio');
		//$form->cprecio->option('D','Dejar el precio mayor');
		$form->cprecio->option('N','No');
		$form->cprecio->option('S','Si');
		$form->cprecio->style = 'width:100px;';
		$form->cprecio->rule  = 'required';

		$form->fecha = new dateonlyField('Fecha de recepci&oacute;n de la compra', 'fecha','d/m/Y');
		//$form->fecha->db_name = 'fecha';
		$form->fecha->insertValue = date('Y-m-d');
		$form->fecha->rule='required|callback_chddate';
		$form->fecha->calendar = false;
		$form->fecha->size=10;

		if ( !$this->solo ) {
			$form->submit('btnsubmit','Actualizar');
			$accion="javascript:window.location='".site_url('compras/scst/dataedit/show/'.$control)."'";
			$form->button('btn_regre','Regresar',$accion,'BR','show');
		}

		$form->build_form();

		if($form->on_success()){
			$cprecio   = $form->cprecio->newValue;
			$actualiza = $form->fecha->newValue;
			$cambio    = ($cprecio=='S') ? true : false;

			$id = $this->datasis->dameval("SELECT id FROM scst WHERE control=$control");
			$rt = $this->_actualizar($id,$cambio,$actualiza);
			if($rt === false){
				$data['content']  = $this->error_string.br();
			}else{
				$data['content']  = 'Compra actualizada'.br();
			}
			if ($this->solo ){
				return array( $rt, $data['content'] );
			} else {
				$data['content'] .= anchor('compras/scst/dataedit/show/'.$control,'Regresar');
				$data['head']    = $this->rapyd->get_head();
				$data['title']   = heading('Actualizar compra');
				$this->load->view('view_ventanas', $data);
			}
		}else{
			$data['content'] = $form->output;
			if ($this->solo ){
				echo $data['content'];
			} else {
				$data['head']    = $this->rapyd->get_head();
				$data['title']   = heading('Actualizar compra');
				$this->load->view('view_ventanas', $data);
			}
		}
	}

	function _actualizar($id, $cprecio, $actuali=null){
		$error = 0;
		$pasa  = $this->datasis->dameval('SELECT COUNT(*) FROM scst WHERE actuali>=fecha AND id= '.$id );

		if( $pasa==0 ){
			$control=$this->datasis->dameval('SELECT control FROM scst WHERE  id='.$id);
			$SQL='SELECT tipo_doc,transac,depo,proveed,fecha,vence, nombre,tipo_doc,nfiscal,fafecta,reteiva,
			cexento,cgenera,civagen,creduci,civared,cadicio,civaadi,cstotal,ctotal,cimpuesto,numero
			FROM scst WHERE control=?';
			$query=$this->db->query($SQL,array($control));

			if($query->num_rows()==1){
				$estampa = date('Y-m-d');
				$hora    = date('H:i:s');
				$usuario = $this->session->userdata('usuario');
				$row     = $query->row_array();

				if($row['tipo_doc']=='FC'){
					$transac = $row['transac'];
					$depo    = $row['depo'];
					$proveed = $row['proveed'];
					$fecha   = str_replace('-','',$row['fecha']);
					$vence   = $row['vence'];
					$reteiva = $row['reteiva'];
					if(empty($actuali)) $actuali=date('Ymd');

					$itdata=array();
					$sql='SELECT a.codigo,a.cantidad,a.importe,a.importe/a.cantidad AS costo,
						a.precio1,a.precio2,a.precio3,a.precio4,b.formcal,b.ultimo,b.standard,b.pond,b.existen
						FROM itscst AS a JOIN sinv AS b ON a.codigo=b.codigo WHERE a.control=?';
					$qquery=$this->db->query($sql,array($control));
					if($qquery->num_rows()>0){
						foreach ($qquery->result() as $itrow){
							$pond     = $this->_pond($itrow->existen,$itrow->cantidad,$itrow->pond,$itrow->costo);

							$costo    = $this->_costos($itrow->formcal,$pond,$itrow->costo,$itrow->standard);
							$dbcodigo = $this->db->escape($itrow->codigo);
							//Actualiza el inventario
							$mSQL='UPDATE sinv SET
								pond='.$pond.',
								ultimo='.$itrow->costo.',
								prov3=prov2, prepro3=prepro2, pfecha3=pfecha2, prov2=prov1, prepro2=prepro1, pfecha2=pfecha1,
								prov1='.$this->db->escape($proveed).',
								prepro1='.$itrow->costo.',
								pfecha1='.$this->db->escape($fecha).'
								WHERE codigo='.$dbcodigo;
							$ban=$this->db->simple_query($mSQL);
							if(!$ban){ memowrite($mSQL,'scst'); $error++; }

							$this->datasis->sinvcarga($itrow->codigo,$depo, $itrow->cantidad );

							if(!$ban){ memowrite($mSQL,'scst'); $error++; }

							if($itrow->precio1>0 && $itrow->precio2>0 && $itrow->precio3>0 && $itrow->precio4>0){
								//Cambio de precios
								if($cprecio){
									$mSQL='UPDATE sinv SET
									precio1='.$this->db->escape($itrow->precio1).',
									precio2='.$this->db->escape($itrow->precio2).',
									precio3='.$this->db->escape($itrow->precio3).',
									precio4='.$this->db->escape($itrow->precio4).'
									WHERE codigo='.$dbcodigo;
									$ban=$this->db->simple_query($mSQL);
									if(!$ban){ memowrite($mSQL,'scst'); $error++; }
								}//Fin del cambio de precios
							}

							//Actualiza los margenes y bases
							$mSQL='UPDATE sinv SET
								base1=ROUND(precio1*10000/(100+iva))/100,
								base2=ROUND(precio2*10000/(100+iva))/100,
								base3=ROUND(precio3*10000/(100+iva))/100,
								base4=ROUND(precio4*10000/(100+iva))/100,
								margen1=ROUND(10000-('.$costo.'*10000/base1))/100,
								margen2=ROUND(10000-('.$costo.'*10000/base2))/100,
								margen3=ROUND(10000-('.$costo.'*10000/base3))/100,
								margen4=ROUND(10000-('.$costo.'*10000/base4))/100,
								activo="S"
							WHERE codigo='.$dbcodigo;
							$ban=$this->db->simple_query($mSQL);
							if(!$ban){ memowrite($mSQL,'scst'); $error++; }
							//Fin de la actualizacion de inventario
						}
					}

					//Limpia primero la data
					$mSQL='DELETE FROM sprm WHERE transac='.$this->db->escape($transac);
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }

					//Inicio de la retencion
					if($reteiva>0){
						//Crea la nota de credito
						$mnumnc = $this->datasis->fprox_numero('num_nc');
						$sprm=array();
						$sprm['cod_prv']    = $proveed;
						$sprm['nombre']     = $row['nombre'];
						$sprm['tipo_doc']   = 'NC';
						$sprm['numero']     = $mnumnc;
						$sprm['fecha']      = $actuali;
						$sprm['monto']      = $reteiva;
						$sprm['impuesto']   = 0;
						$sprm['abonos']     = $reteiva;
						$sprm['vence']      = $actuali;
						$sprm['tipo_ref']   = 'FC';
						$sprm['num_ref']    = $row['numero'];
						$sprm['observa1']   = 'RET/IVA CAUSADA A FC'.$row['numero'];
						$sprm['estampa']    = $estampa;
						$sprm['hora']       = $hora;
						$sprm['transac']    = $transac;
						$sprm['usuario']    = $usuario;
						$sprm['codigo']     = 'NOCON';
						$sprm['descrip']    = 'NOTA DE CONTABILIDAD';
						$mSQL = $this->db->insert_string('sprm', $sprm);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }

						//Aplica la NC a la FC
						$itppro=array();
						$itppro['numppro']    = $mnumnc;
						$itppro['tipoppro']   = 'NC';
						$itppro['cod_prv']    = $proveed;
						$itppro['tipo_doc']   = 'FC';
						$itppro['numero']     = $row['numero'];
						$itppro['fecha']      = $actuali;
						$itppro['monto']      = $reteiva;
						$itppro['abono']      = $reteiva;
						$itppro['ppago']      = 0;
						$itppro['reten']      = 0;
						$itppro['cambio']     = 0;
						$itppro['mora']       = 0;
						$itppro['transac']    = $transac;
						$itppro['estampa']    = $estampa;
						$itppro['hora']       = $hora;
						$itppro['usuario']    = $usuario;
						$itppro['preten']     = 0;
						$itppro['creten']     = 0;
						$itppro['breten']     = 0;
						$itppro['reteiva']    = 0;
						$mSQL = $this->db->insert_string('itppro', $itppro);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++;}

						//Crea la nota de debito
						$mnumnd = $this->datasis->fprox_numero('num_nd');
						$sprm=array();
						$sprm['cod_prv']   = 'REIVA';
						$sprm['nombre']    = 'RETENCION DE I.V.A. POR COMPENSAR';
						$sprm['tipo_doc']  = 'ND';
						$sprm['numero']    = $mnumnd;
						$sprm['fecha']     = $actuali;
						$sprm['monto']     = $reteiva;
						$sprm['impuesto']  = 0;
						$sprm['abonos']    = 0;
						$sprm['vence']     = $actuali;
						$sprm['tipo_ref']  = 'FC';
						$sprm['num_ref']   = $row['numero'];
						$sprm['observa1']  = 'RET/IVA DE '.$proveed.' A DOC. FC'.$row['numero'];
						$sprm['estampa']   = $estampa;
						$sprm['hora']      = $hora;
						$sprm['transac']   = $transac;
						$sprm['usuario']   = $usuario;
						$sprm['codigo']    = 'NOCON';
						$sprm['descrip']   = 'NOTA DE CONTABILIDAD';
						$mSQL = $this->db->insert_string('sprm', $sprm);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++;}

						//Crea la retencion
						$niva    = $this->datasis->fprox_numero('niva');
						$ivaplica= $this->datasis->ivaplica($fecha);

						$riva['nrocomp']    = $niva;
						$riva['emision']    = ($fecha > $actuali) ? $fecha : $actuali;
						$riva['periodo']    = substr($riva['emision'],0,6) ;
						$riva['tipo_doc']   = $row['tipo_doc'];
						$riva['fecha']      = $fecha;
						$riva['numero']     = $row['numero'];
						$riva['nfiscal']    = $row['nfiscal'];
						$riva['afecta']     = $row['fafecta'];
						$riva['clipro']     = $proveed;
						$riva['nombre']     = $row['nombre'];
						$riva['rif']        = $this->datasis->dameval('SELECT rif FROM sprv WHERE proveed='.$this->db->escape($proveed));
						$riva['exento']     = $row['cexento'];
						$riva['tasa']       = $ivaplica['tasa'];
						$riva['tasaadic']   = $ivaplica['sobretasa'];
						$riva['tasaredu']   = $ivaplica['redutasa'];
						$riva['general']    = $row['cgenera'];
						$riva['geneimpu']   = $row['civagen'];
						$riva['adicional']  = $row['cadicio'];
						$riva['adicimpu']   = $row['civaadi'];
						$riva['reducida']   = $row['creduci'];
						$riva['reduimpu']   = $row['civared'];
						$riva['stotal']     = $row['cstotal'];
						$riva['impuesto']   = $row['cimpuesto'];
						$riva['gtotal']     = $row['ctotal'];
						$riva['reiva']      = $reteiva;
						$riva['transac']    = $transac;
						$riva['estampa']    = $estampa;
						$riva['hora']       = $hora;
						$riva['usuario']    = $usuario;
						$mSQL=$this->db->insert_string('riva', $riva);
						$ban =$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }
					}//Fin de la retencion

					//Carga la CxP
					$sprm=array();
					$causado = $this->datasis->fprox_numero('ncausado');
					$sprm['cod_prv']  = $proveed;
					$sprm['nombre']   = $row['nombre'];
					$sprm['tipo_doc'] = $row['tipo_doc'];
					$sprm['numero']   = $row['numero'];
					$sprm['fecha']    = $actuali;
					$sprm['vence']    = $vence;
					$sprm['monto']    = $row['ctotal'];
					$sprm['impuesto'] = $row['cimpuesto'];
					$sprm['abonos']   = $reteiva;
					$sprm['observa1'] = 'FACTURA DE COMPRA';
					$sprm['reteiva']  = $reteiva;
					$sprm['causado']  = $causado;
					$sprm['estampa']  = $estampa;
					$sprm['usuario']  = $usuario;
					$sprm['hora']     = $hora;
					$sprm['transac']  = $transac;
					//$sprm['montasa']  = $row['cimpuesto'];
					//$sprm['impuesto'] = $row['cimpuesto'];

					$mSQL=$this->db->insert_string('sprm', $sprm);
					$ban =$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }
					//Fin de la carga de la CxP

					$mSQL='UPDATE scst SET `actuali`='.$actuali.', `recep`='.$actuali.' WHERE `control`='.$this->db->escape($control);
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }

				}elseif($row['tipo_doc']=='NC'){
					//Falta implementar
				}elseif($row['tipo_doc']=='NE'){
					//Falta implementar
				}
			}else{
				$this->error_string='Compra no existe '.$id;
				return false;
			}
		}else{
			$this->error_string='No se puede actualizar una compra que ya fue actualizada';
			return false;
		}
	}

	function reversar($control){
		// Condiciones para reversar
		// Si no tiene transaccion vino por migracion desde otro sistema

		$mSQL = "SELECT * FROM scst WHERE control=$control";
		$query=$this->db->query($mSQL);

		if($query->num_rows()==0){
			return;
		}

		$scst     = $query->row_array();
		$mTRANSAC = $scst['transac'];
		// Si esta actualizada
		$mACTUALI = $scst['actuali'];
		$fecha    = $scst['fecha'];
		$tipo_doc = $scst['tipo_doc'];
		$numero   = $scst['numero'];
		$montonet = $scst['montonet'];
		$reteiva  = $scst['reteiva'];
		$fafecta  = $scst['fafecta'];
		$anticipo = $scst['anticipo'];
		$proveed  = $scst['proveed'];
		$mALMA    = $scst['depo'];

		//********************************
		//
		//    Busca si tiene abonos
		//
		//********************************
		$abonado = 0;
		if ($tipo_doc == 'FC'){
			$mSQL  = "SELECT a.abonos -( b.inicial + b.anticipo + b.reten + b.reteiva) ";
			$mSQL .= "FROM sprm a JOIN scst b ON a.transac=b.transac ";
			$mSQL .= "WHERE a.tipo_doc='$tipo_doc' AND a.numero='$numero' AND a.cod_prv=b.proveed AND a.numero=b.numero ";
			$mSQL .= "AND a.transac='$mTRANSAC' ";
			$abonado = $this->datasis->dameval($mSQL);
		};

		// CONDICIONES QUE DEBEN CUMPLIR PARA PODER REVERSAR
		// si esta abonada
		if ($abonado > 0.1 ) {
			echo "Compra abonada, elimine el pago primero!";
			return;
		}
		// si no tiene transaccion
		if (empty($mTRANSAC)){
			echo "Compra sin nro de transaccion, llame a soporte";
			return;
		}
		// si no esta cargada
		if ( $mACTUALI < $fecha ){
			echo 'Factura no ha sido cargada';
			return ;
		}

		// ******* Borra de a CxC *******\\
		$mSQL = "DELETE FROM sprm WHERE transac='$mTRANSAC'";
		$this->db->simple_query($mSQL);

		if ( $tipo_doc == 'NC' ){
			$mSQL = "UPDATE sprm SET abonos=abonos-$montonet-$reteiva WHERE numero='$fafecta' AND tipo_doc='FC' AND cod_prv='$proveed' ";
			$this->db->simple_query($mSQL);
		}


		$mSQL = "DELETE FROM itppro WHERE transac='$mTRANSAC'";
		$this->db->simple_query($mSQL);

		// ANULA LA RETENCION SI TIENE
		if ( $this->datasis->dameval("SELECT COUNT(*) FROM riva WHERE transac='$mTRANSAC'") > 0 ){
			$mTRANULA = '_'.substr($this->datasis->prox_sql('rivanula'),1,7);
			$this->db->simple_query("UPDATE riva SET transac='$mTRANULA' WHERE transac='$mTRANSAC' ");
		}

		// Busca las Ordenes
		$mORDENES = array();
		$query = $this->db->query("SELECT orden FROM scstordc WHERE compra='$control'");
		if ($query->num_rows() > 0 ){
			foreach( $query->result() as $row ) {
				$mORDENES[] = $row->orden;
			}
		}
		//$query->destroy();

		// DESACTUALIZA INVENTARIO
		//
		$query = $this->db->query("SELECT codigo, cantidad FROM itscst WHERE control='$control'");
		foreach ( $query->result() as $row ) {
			$mTIPO = $this->datasis->dameval("SELECT MID(tipo,1,1) FROM sinv WHERE codigo='".$row->codigo."'");

			if ( $tipo_doc == 'FC' or $tipo_doc =='NE' ) {
				//CMNJ(mm_DETA[i,1]+" "+XDEPO+" "+STR( -mm_DETA[i,3]))
				$this->datasis->sinvcarga($row->codigo,  $mALMA, -$row->cantidad);
				//IF mTIPO = 'L'
				//	SINVLOTCARGA( mm_DETA[i,1], XDEPO, mm_DETA[i,8], -mm_DETA[i,3] )
				//ENDIF

				// DEBE ARREGLAR EL PROMEDIO BUSCANDO EN KARDEX
				$mSQL = "SELECT promedio FROM costos WHERE codigo='".$row->codigo."' ORDER BY fecha DESC LIMIT 1";
				$mPROM = $this->datasis->dameval($mSQL);
				if ( !empty($mPROM) ) {
					$mSQL = "UPDATE sinv SET pond=$mPROM WHERE codigo='".$row->codigo."'";
					$this->db->simple_query($mSQL);
				}

				if (count($mORDENES) > 0 ){
					$mSALDO = $row->cantidad;
					foreach( $mORDENES as $orden){
						if ($mSALDO > 0 ) {
							$mSQL   = "SELECT recibido  FROM itordc WHERE numero='".$mORDENE."' AND codigo='".$row->codigo."'";
							$mTEMPO = $this->datasis->dameval($mSQL);
							if ( $mTEMPO > 0 ){
								if ($mTEMPO >= $mSALDO ) {
									$mSQL  = "UPDATE itordc SET recibido=recibido-$mSALDO WHERE numero='$orden' AND codigo='".$row->codigo."'";
									$this->db->simple_query($mSQL);
									$mSQL = "UPDATE sinv SET exord=exord+$mSALDO WHERE codigo='".$row->codigo."' ";
									$this->db->simple_query($mSQL);
									$mSALDO = 0;
								} elseif ($mTEMPO < $mSALDO) {
									$mSQL   = "UPDATE itordc SET recibido=recibido-$mTEMPO WHERE numero='$orden' AND codigo='"+$row->codigo+"'";
									$this->db->simple_query($mSQL);
									//EJECUTASQL(mSQL,{ mTEMPO, mORDENES[m], mm_DETA[i,1] })
									$mSQL = "UPDATE sinv SET exord=exord+$mTEMPO WHERE codigo='".$row->codigo."' ";
									//EJECUTASQL(mSQL,{ mTEMPO, mm_DETA[i,1] })
									$mSALDO -= $mTEMPO;
								}
							}
						}
					}
				}
			} else {
				$this->datasis->sinvcarga($row->codigo, $mALMA, $row->cantidad);
				//if ($mTIPO = 'L' )
				//	SINVLOTCARGA( mm_DETA[i,1], XDEPO, mm_DETA[i,8], mm_DETA[i,3] )
				//ENDIF
			}
		}

		$mSQL = "UPDATE scst SET actuali=0 WHERE control='$control'";
		$this->db->simple_query($mSQL);

		// Carga Ordenes
		if (count($mORDENES) > 0 ) {
			// SUMA A VER SI ESTA COMPLETA
			foreach ( $mORDENES as $orden ) {
				$mSQL = "UPDATE itordc SET recibido=0 WHERE numero='$orden' AND recibido<0 ";
				$this->db->simple_query($mSQL);
				$mSQL = "SELECT COUNT(*) FROM itordc WHERE numero='$orden' AND recibido>0";
				if ($this->datasis->dameval($mSQL) == 0 ){
					$mSQL = "UPDATE ordc SET status='PE' WHERE numero='$orden' ";
				} else {
					$mSQL = "UPDATE ordc SET status='BA' WHERE numero='$orden' ";
				}
				$this->db->simple_query($mSQL);
			}
		}
		if ( $this->solo )
			return 'Compra Reversada en Inventario y CxP';
		else {
			$data['head']    = $this->rapyd->get_head();
			$data['content'] = 'Compra Reversada en Inventario y CxP'.br();
			$data['content'].= anchor('compras/scst/dataedit/show/'.$control,'Regresar');
			$data['title']   = heading('Reverso de compra');
			$this->load->view('view_ventanas', $data);
		}
	}

	function creadseri($cod_prov,$factura){
		$cod_prove=$this->db->escape($cod_prov);
		$facturae =$this->db->escape($factura);
		$control=$this->datasis->fprox_numero('ntemp');
		$control=substr($control,1,7).'_';
		$controle=$this->db->escape($control);

		$query="
		INSERT INTO itscst (`numero`,`proveed`,`codigo`,`descrip`,`cantidad`,`control`,`iva`,`costo`,`importe`)
		SELECT refe2,clipro,b.codigo,b.descrip,SUM(b.cant) cant,$controle,c.iva,c.ultimo,SUM(b.cant)*c.ultimo
		FROM recep a
		JOIN seri b ON a.recep=b.recep
		JOIN sinv c ON b.codigo=c.codigo
		WHERE origen='scst' AND a.refe2=$facturae AND clipro=$cod_prove
		GROUP BY b.codigo";

		$this->db->query($query);

		$query="
		INSERT INTO scst (`numero`,`proveed`,`control`,`serie`)
		VALUES ($facturae,$cod_prove,$controle,$facturae)";
		$this->db->query($query);
		redirect("compras/scst/dataedit/modify/$control");
	}

	function _pre_delete($do){
		$recep  =strtotime($do->get('recep'));
		$fecha  =strtotime($do->get('fecha'));
		$actuali=strtotime($do->get('actuali'));

		if ($actuali >= $fecha){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede eliminar una factura cargada, debe reversarla primero';
			return false;
		}
		return true;
	}

	function _pre_insert($do){

		$control=$do->get('control');
		$transac=$do->get('transac');

		if(substr($control,7,1)=='_') $control = $this->datasis->fprox_numero('nscst');
		if(empty($control)) $control = $this->datasis->fprox_numero('nscst');
		if(empty($transac)) $transac = $this->datasis->fprox_numero('ntransa');

		$fecha   = $do->get('fecha');
		$numero  = substr($do->get('serie'),-8);
		$usuario = $do->get('usuario');
		$proveed = $do->get('proveed');
		$depo    = $do->get('depo');
		$estampa = date('Ymd');
		$hora    = date('H:i:s');
		$alicuota=$this->datasis->ivaplica($fecha);

		$iva=$stotal=0;
		$cgenera=$civagen=$creduci=$civared=$cadicio=$civaadi=$cexento=0;
		$cana=$do->count_rel('itscst');
		for($i=0;$i<$cana;$i++){
			$itcodigo  = $do->get_rel('itscst','codigo'  ,$i);
			$itcana    = $do->get_rel('itscst','cantidad',$i);
			$itprecio  = $do->get_rel('itscst','costo'   ,$i);
			$itiva     = $do->get_rel('itscst','iva'     ,$i);

			$itimporte = $itprecio*$itcana;
			$iiva      = $itimporte*($itiva/100);

			$mSQL='SELECT ultimo,existen,pond,standard,formcal,margen1,margen2,margen3,margen4 FROM sinv WHERE codigo='.$this->db->escape($itcodigo);
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$costo_pond=(($row->pond*$row->existen)+($itcana*$itprecio))/($itcana+$row->existen);
				$costo_ulti=$itprecio;

				$costo=$this->_costos($row->formcal,$costo_pond,$costo_ulti,$row->standard);

			}
			for($o=1;$o<5;$o++){
				$obj='margen'.$o;
				$pp=(($costo*100)/(100-$row->$obj))*(1+($itiva/100));
				$do->set_rel('itscst','precio'.$o ,$pp,$i);
			}

			$itimporte = $do->get_rel('itscst','importe' ,$i);
			//$do->set_rel('itscst','importe' ,$itimporte,$i);
			$do->set_rel('itscst','montoiva',$iiva     ,$i);
			$do->set_rel('itscst','ultimo'  ,$row->ultimo,$i);
			$do->set_rel('itscst','fecha'   ,$fecha    ,$i);
			$do->set_rel('itscst','numero'  ,$numero   ,$i);
			$do->set_rel('itscst','proveed' ,$proveed  ,$i);
			$do->set_rel('itscst','depo'    ,$depo     ,$i);
			$do->set_rel('itscst','control' ,$control  ,$i);
			$do->set_rel('itscst','transac' ,$transac  ,$i);
			$do->set_rel('itscst','usuario' ,$usuario  ,$i);
			$do->set_rel('itscst','hora'    ,$hora     ,$i);
			$do->set_rel('itscst','estampa' ,$estampa  ,$i);

			if($itiva-$alicuota['tasa']==0){
				$cgenera += $itimporte;
				$civagen += $iiva;
			}elseif($itiva-$alicuota['redutasa']==0){
				$creduci += $itimporte;
				$civared += $iiva;
			}elseif($itiva-$alicuota['sobretasa']==0){
				$cadicio += $itimporte;
				$civaadi += $iiva;
			}else{
				$cexento += $itimporte;
			}

			$iva    += $iiva;
			$stotal += $itimporte;
		}
		$gtotal=$stotal+$iva;
		$do->set('numero'   , $numero);
		$do->set('control'  , $control);
		$do->set('estampa'  , $estampa);
		$do->set('hora'     , $hora);
		$do->set('transac'  , $transac);

		//$montonet = $do->get('montonet');
		$montotot = $do->get('montotot');
		$montoiva = $do->get('montoiva');
		$cm=false;
		if(abs($montotot-$stotal)<=0.02){
			$cm     = true;
			$stotal = $montotot;
		}
		if(abs($montoiva-$iva)<=0.02){
			$cm  = true;
			$iva = $montoiva;
		}
		if($cm){
			$gtotal=$stotal+$iva;
		}

		$do->set('montotot' , round($stotal,2));
		$do->set('montonet' , round($gtotal,2));
		$do->set('montoiva' , round($iva   ,2));
		$do->set('cgenera'  , round($cgenera,2));
		$do->set('civagen'  , round($civagen,2));
		$do->set('creduci'  , round($creduci,2));
		$do->set('civared'  , round($civared,2));
		$do->set('cadicio'  , round($cadicio,2));
		$do->set('civaadi'  , round($civaadi,2));
		$do->set('cexento'  , round($cexento,2));
		$do->set('ctotal'   , round($gtotal ,2));
		$do->set('cstotal'  , round($stotal ,2));
		$do->set('cimpuesto', round($iva    ,2));

		//Para la retencion de iva si aplica
		$contribu= $this->datasis->traevalor('CONTRIBUYENTE');
		$rif     = $this->datasis->traevalor('RIF');
		if($contribu=='ESPECIAL' && strtoupper($rif[0])!='V'){
			$por_rete=$this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($proveed));
			if($por_rete!=100){
				$por_rete=0.75;
			}else{
				$por_rete=$por_rete/100;
			}
			$do->set('reteiva', round($iva*$por_rete,2));
		}
		//fin de la retencion

		//$do->set('estampa', 'CURDATE()', FALSE);
		//$do->set('hora'   , 'CURRENT_TIME()', FALSE);

		//Para picar la observacion en varios campos
		$obs=$do->get('observa1');
		$ff = strlen($obs);
		for($i=0; $i<$ff; $i=$i+60){
			$ind=($i % 60)+1;
			$do->set('observa'.$ind,substr($obs,$i,60));
			if($i>180) break;
		}
		return true;
	}

	//Chequea que el dia no sea superior a hoy

	function _post_update($do){
		$codigo  = $do->get('numero');
		$control = $do->get('control');
		logusu('scst',"Compra $codigo control $control MODIFICADA");
	}

	function chddate($fecha){
		$d1 = DateTime::createFromFormat(RAPYD_DATE_FORMAT, $fecha);
		$d2 = new DateTime();

		$control= $this->uri->segment(4);
		$controle=$this->db->escape($control);

		$f=$this->datasis->dameval("SELECT fecha FROM scst WHERE control=$controle");

		$d3 = DateTime::createFromFormat(RAPYD_DATE_FORMAT, dbdate_to_human($f));

		if($d2>=$d1 && $d1>=$d3){
			return true;
		}else{
			$this->validation->set_message('chddate', 'No se puede recepcionar una compra con fecha superior al d&iacute;a de hoy.');
			return false;
		}
	}

	function _pond($existen,$itcana,$pond,$ultimo){
		if($itcana+$existen==0) return $ultimo;
		return (($pond*$existen)+($itcana*$ultimo))/($itcana+$existen);
	}

	function _costos($formcal,$costo_pond,$costo_ulti,$costo_stan){
		switch($formcal){
			case 'P':
				$costo=$costo_pond;
				break;
			case 'U':
				$costo=$costo_ulti;
				break;
			case 'S':
				$costo=$costo_stan;
				break;
			default:
				$costo=($costo_pond>$costo_ulti) ? $costo_pond : $costo_ulti;
		}
		return $costo;
	}

	function _post_insert($do){
		$codigo  = $do->get('numero');
		$control = $do->get('control');
		logusu('scst',"Compra $codigo control $control CREADA");
	}

	function _post_cxp_update($do){
		exit();
		return false;
	}


	function _pre_update($do){
		$actuali= new DateTime($do->get('actuali'));
		$fecha  = new DateTime($do->get('fecha'));

		if($actuali >= $fecha){
			$do->error_message_ar['pre_upd'] = $do->error_message_ar['update']='No se puede modificar una compra actualizada, debe reversarla primero.';
			return false;
		}
		$this->_pre_insert($do);
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('scst',"Compra $codigo ELIMINADA");
	}

}
?>
