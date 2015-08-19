<?php
//require_once(BASEPATH.'application/controllers/validaciones.php');
class sfacter extends Controller {
	var $mModulo='SFACTER';
	var $titp='Facturaci&oacute;n por cuenta de Terceros';
	var $tits='Facturaci&oacute;n';
	var $url ='ventas/sfacter/';
	var $genesal  = true;
	var $_creanfac= false;

	function sfacter(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'SFACTER', 0, 'Facturacion' );
		$this->vnega  = trim(strtoupper($this->datasis->traevalor('VENTANEGATIVA')));
		//$this->datasis->modulo_id('13D',1);
		$this->instalar();
	}

	function index(){
		$this->instalar();
		$this->datasis->modintramenu( 900, 650, 'ventas/sfacter' );
		redirect($this->url.'jqdatag');

		//redirect($this->url.'filteredgrid');
	}


	//******************************************************************
	//Ventana principal de facturacion
	//
	function jqdatag(){
		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 220, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'fimprime', 'img'=>'assets/default/images/print.png','alt' => 'Reimprimir Documento','tema'=>'anexos', 'label'=>'Imprimir'));
		//$grid->wbotonadd(array('id'=>'precierre','img'=>'images/dinero.png',              'alt' => 'Cierre de Caja',      'tema'=>'anexos', 'label'=>'Cierre de Caja'));
		//$grid->wbotonadd(array('id'=>'fmanual',  'img'=>'images/mano.png',                'alt' => 'Factura Manual',      'tema'=>'anexos', 'label'=>'Factura Manual'));
		//$grid->wbotonadd(array('id'=>'bdevolu',  'img'=>'images/dinero.png',              'alt' => 'Devolver Factura',    'tema'=>'anexos', 'label'=>'Devolver'));
		//$grid->wbotonadd(array('id'=>'nccob',    'img'=>'images/check.png', 'alt' => 'Nota de credito a factura pagada', 'label'=>'NC a Factura Cobrada'));

		$fiscal=$this->datasis->traevalor('IMPFISCAL','Indica si se usa o no impresoras fiscales, esto activa opcion para cierre X y Z');
		if($fiscal=='S'){
			$WpAdic = "<tr><td>
				<div class=\"anexos\">
					<table cellpadding='0' cellspacing='0'>
						<tr>
							<td style='vertical-align:top;'><div class='botones'><a style='width:94px;text-align:left;vertical-align:top;' href='#' id='bcierrex'>".img(array('src'=>'assets/default/images/print.png', 'height'=>15, 'alt'=>'Realizar cierre X', 'title'=>'Cierre X', 'border'=>'0'))." Cierre X</a></div></td>
							<td style='vertical-align:top;'><div class='botones'><a style='width:94px;text-align:left;vertical-align:top;' href='#' id='bcierrez'>".img(array('src'=>'assets/default/images/print.png', 'height'=>15, 'alt'=>'Realizar cierre Z', 'title'=>'Cierre Z', 'border'=>'0'))." Cierre Z</a></div></td>
						</tr>
					</table>
				</div>
			</td></tr>";
			$grid->setWpAdicional($WpAdic);
		}


		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = 'radicional', $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
			array('id'=>'fedita' , 'title'=>'Agregar Factura Fecha '.date('d/m/Y')),
			array('id'=>'scliexp', 'title'=>'Ficha de Cliente' ),
			array('id'=>'fshow'  , 'title'=>'Mostrar registro' ),
			array('id'=>'fborra' , 'title'=>'Anula Factura'    ),
			array('id'=>'fncob'  , 'title'=>'NC a factura cobrada')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);


		$funciones = '
		function ltransac(el, val, opts){
			var link=\'<div><a href="#" onclick="tconsulta(\'+"\'"+el+"\'"+\');">\' +el+ \'</a></div>\';
			return link;
		};';

		$param['WestPanel']    = $WestPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('SFAC', 'JQ');
		$param['otros']        = $this->datasis->otros('SFAC', 'JQ');
		$param['centerpanel']  = $centerpanel;
		$param['funciones']    = $funciones;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//******************************************************************
	// Funciones de los Botones
	//
	function bodyscript($grid0, $grid1){
		$bodyscript = '<script type="text/javascript">';
		$ngrid = '#newapi'.$grid0;

		$bodyscript .= '
		function tconsulta(transac){
			if (transac)	{
				window.open(\''.site_url('contabilidad/casi/localizador/transac/procesar').'/\'+transac, \'_blank\', \'width=800, height=600, scrollbars=yes, status=yes, resizable=yes,screenx=((screen.availHeight/2)-300), screeny=((screen.availWidth/2)-400)\');
			} else {
				$.prompt("<h1>Transacci&oacute;n invalida</h1>");
			}
		};';

		$bodyscript .= $this->jqdatagrid->bsshow('sfac', $ngrid, $this->url );

		$bodyscript .= '
		function sfacadd(){
			$.post(xurl+"/N/create",
			function(data){
				$("#fimpser").html("");
				$("#fedita").dialog({ title:"Agregar Factura Fecha '.date('d/m/Y').'" });
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
				$("#cod_cli").focus();
			})
		};';


		$bodyscript .= '
		function sfacedit() {
			var id     = jQuery("'.$ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("'.$ngrid.'").getRowData(id);
				if(ret.referen=="P"){
					$.post(xurl+"/"+ret.manual+"/modify/"+id, function(data){
						$("#fborra").html("");
						$("#fimpser").html("");
						$("#fedita").html(data);
						$("#fedita").dialog("open");
						$("#cod_cli").focus();
					});
				}else{
					$.prompt("<h1>Solo se pueden modificar las facturas pendientes</h1>");
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';


		$bodyscript .= '
		function sfacdel() {
			var id = jQuery("'.$ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				if(confirm(" Seguro desea anular la Factura o Devolucion?")){
					var ret    = $("'.$ngrid.'").getRowData(id);
					mId = id;
					$.post(xurl+"/do_delete/"+id, function(data){
						$("#fedita").html("");
						$("#fimpser").html("");
						try{
							var json = JSON.parse(data);
							if(json.status == "A"){
								jQuery("'.$ngrid.'").trigger("reloadGrid");
								return true;
							}else{
								apprise(json.mensaje);
							}
						}catch(e){
							$("#fborra").html(data);
							$("#fborra").dialog("open");
							jQuery("'.$ngrid.'").trigger("reloadGrid");
						}
						jQuery("'.$ngrid.'").trigger("reloadGrid");
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';


		$bodyscript .= '
		$("#nccob").click( function(){
			var id  = $("'.$ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
			var id = $("'.$ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = $("'.$ngrid.'").getRowData(id);
				if(ret.numero.substr(0,1)!="_" && ret.tipo_doc=="F"){
					$.post("'.site_url('finanzas/smov/ncfac').'/"+ret.numero+"/create",
						function(data){
							$("#fncob").html(data);
							$("#fncob").dialog("open");
						}
					);
				}else{
					$.prompt("<h1>No puede realizar esta operaci&oacute;n con el documento seleccionado</h1>");
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		});';


		$bodyscript .= '$(function() { ';

		$bodyscript .= '
		$("#bdevolu").click( function() {
			var id = jQuery("'. $ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = $("'.$ngrid.'").getRowData(id);
				if(ret.numero.substr(0,1)=="_"){
					alert("Debe seleccionar una factura.");
					return false;
				}

				if(ret.tipo_doc!="F"){
					alert("Debe seleccionar una factura.");
					return false;
				}
				$.post(xurl+"/N/create",
				function(data){
					$("#fimpser").html("");
					$("#fedita").dialog({ title:"Agregar Devolucion Fecha '.date('d/m/Y').'" });
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
					$("#factura").val(ret.numero);
					$("#tipo_doc").val("D");
					itdevolver(ret.numero);
				});
			}
		});';

		$bodyscript .= '
			$("#fmanual").click( function() {
				$.post(xurl+"/S/create",
				function(data){
					$("#fimpser").html("");
					$("#fedita").html(data);
					$("#fedita").dialog({ title:"Agregar Factura ******** MANUAL ********" });
					$("#fedita").dialog( "open" );
				})
			});';

		// Para imprimir despacho desde aqui
		$ndespa = $this->datasis->dameval('SELECT COUNT(*) FROM formatos WHERE nombre="NDESPACHO" AND proteo IS NOT NULL');
		if ( $ndespa == 0)
			$bodyscript .= '
			$("#fimprime").click( function(){
				var id = jQuery("'.$ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret = jQuery("'.$ngrid.'").jqGrid(\'getRowData\',id);
					window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
				} else {
					$.prompt("<h1>Por favor Seleccione una Factura</h1>");
				}
			});';
		else
			$bodyscript .= '
			$("#fimprime").click( function(){
				var id = jQuery("'.$ngrid.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id){
					var ret = jQuery("'.$ngrid.'").jqGrid(\'getRowData\',id);
					$.prompt("<h1>Imprimir Documento</h1>Cliente: <b>"+ret.nombre+"</b><br>Factura Nro: <b>"+ret.numero+"</b><br><br> ",{
					buttons: { Factura: 1, Despacho: 2, Salir: 0},
					submit: function(e,v,m,f){
						if ( v == 1 ){
							window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
						} else if( v == 2 ){
							window.open(\''.site_url('formatos/ver/NDESPACHO').'/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes\');
						}
					}
					});
				} else {
					$.prompt("<h1>Por favor Seleccione una Factura</h1>");
				}
			});';


		$bodyscript .= '
			$("#boton2").click( function(){
				window.open(\''.site_url('ventas/sfac/dataedit/create').'\', \'_blank\', \'width=900,height=700,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-350)\');
			});';

		$fiscal=$this->datasis->traevalor('IMPFISCAL','Indica si se usa o no impresoras fiscales, esto activa opcion para cierre X y Z');
		if($fiscal=='S'){
			$bodyscript .= '
			$("#bcierrex").click( function(){
				window.open(\''.site_url('formatos/descargartxt/CIERREX').'\', \'_blank\', \'width=300,height=300,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-350)\');
			});';

			$bodyscript .= '
			$("#bcierrez").click( function(){
				window.open(\''.site_url('formatos/descargartxt/CIERREZ').'\', \'_blank\', \'width=300,height=300,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-350)\');
			});';
		}

		//Precierre
		$bodyscript .= '
			$("#precierre").click( function(){
				//$.prompt("<h1>Seguro que desea hacer cierre?</h1>")
				window.open(\''.site_url('ventas/rcaj/precierre/99/').'/'.$this->secu->getcajero().'\', \'_blank\', \'width=900,height=700,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-350)\');
			});';

		//Prepara Pago o Abono
		$bodyscript .= '
			$("#cobroser").click(function() {
				$.post("'.site_url('ventas/sfac/fcobroser').'", function(data){
					$("#fcobroser").html(data);
				});
				$( "#fcobroser" ).dialog( "open" );
			});';

		$bodyscript .= '
			$("#imptxt").click(function(){
				var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					$.post("'.site_url('ventas/sfac/dataprintser/modify').'/"+id, function(data){
						$("#fimpser").html(data);
					});
					$("#fimpser").dialog( "open" );
				}else{
					$.prompt("<h1>Por favor Seleccione un Registro</h1>");
				}
			});';

		$bodyscript .= '
			$("#fimpser").dialog({
				autoOpen: false, height: 420, width: 400, modal: true,
				buttons: {
					"Guardar": function() {
						var bValid = true;
						var murl = $("#df1").attr("action");
						$.ajax({
							type: "POST",
							dataType: "html",
							async: false,
							url: murl,
							data: $("#df1").serialize(),
							success: function(r,s,x){
								try{
									var json = JSON.parse(r);
									if (json.status == "A"){
										$("#fimpser").dialog( "close" );
										jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
										return true;
									} else {
										apprise(json.mensaje);
									}
								}catch(e){
									$("#fimpser").html(r);
								}
							}
						})},
					"Imprimir": function() {
							var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
							location.href="'.site_url('formatos/descargartxt/FACTSER').'/"+id;
					},
					"Cancelar": function() {
						$("#fimpser").html("");
						$( this ).dialog( "close" );
					}
				},
				close: function() {
					$("#fimpser").html("");
				}
			});';

		$bodyscript .= '
			$("#fshow").dialog({
				autoOpen: false, height: 550, width: 870, modal: true,
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
						$( this ).dialog( "close" );
					},
				},
				close: function() {
					$("#fborra").html("");
				}
			});';

		$sfacforma=$this->datasis->traevalor('FORMATOSFAC');
		if(empty($sfacforma)) $sfacforma='descargar';
		//Agregar Factura
		$bodyscript .= '
			$("#fedita").dialog({
				autoOpen: false, height: 550, width: 870, modal: true,
				buttons: {
					"Guardar": function() {
						if($("#scliexp").dialog( "isOpen" )===true) {
							$("#scliexp").dialog("close");
						}

						if($("#df1").length > 0){
							var bValid = true;
							var murl = $("#df1").attr("action");
							limpiavacio();
							$.ajax({
								type: "POST",
								dataType: "html",
								async: false,
								url: murl,
								data: $("#df1").serialize(),
								success: function(r,s,x){
									try {
										var json = JSON.parse(r);
										if(json.status == "A" ) {
											if(json.manual == "N"){
												$("#fedita").dialog("close");
												window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
												$("#newapi'.$grid0.'").trigger("reloadGrid");
												return true;
											}else{
												//$( "#fedita" ).dialog( "close" );
												$.post("'.site_url($this->url.'dataedit/S/create').'",
												function(data){
													$("#fedita").html(data);
												})
												//alert("Factura guardada");
												window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
												$("#newapi'.$grid0.'").trigger("reloadGrid");
												return true;
											}
										} else {
											apprise(json.mensaje);
										}
									} catch(e) {
										$("#fedita").html(r);
									}
								}
							});
						}
					},
					"Guardar y Seguir": function() {
						if($("#scliexp").dialog( "isOpen" )===true) {
							$("#scliexp").dialog("close");
						}

						if($("#df1").length > 0){
							var murl = $("#df1").attr("action");
							limpiavacio();
							$.ajax({
								type: "POST",
								dataType: "html",
								async: false,
								url: murl,
								data: $("#df1").serialize(),
								success: function(r,s,x){
									try {
										var json = JSON.parse(r);
										if(json.status == "A" ) {
											if(json.manual == "N"){
												$.post(xurl+"/create/"+idactual,function(data){$("#fedita").html(data);});
												window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
												jQuery("'.$ngrid.'").trigger("reloadGrid");
												return true;
											}else{
												$.post("'.site_url($this->url.'dataedit/S/create').'",
												function(data){
													$("#fedita").html(data);
												})
												//alert("Factura guardada");
												window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
												return true;
											}
										} else {
											apprise(json.mensaje);
										}
									} catch(e) {
										$("#fedita").html(r);
									}
								}
							});
						}
					},
					"Cancelar": function() {
						$("#fedita").html("");
						$( this ).dialog( "close" );
						$("#newapi'.$grid0.'").trigger("reloadGrid");
						if($("#scliexp").dialog( "isOpen" )===true) {
							$("#scliexp").dialog("close");
						}
					}
				},
				close: function() {
					$("#fedita").html("");
					if($("#scliexp").dialog( "isOpen" )===true) {
						$("#scliexp").dialog("close");
					}
				}
			});';


		$bodyscript .= '
			$("#fcobroser" ).dialog({
				autoOpen: false, height: 430, width: 540, modal: true,
				buttons: {
					"Guardar": function() {
						$.post("'.site_url('ventas/mensualidad/servxmes/insert').'", { cod_cli: $("#fcliente").val(),cana_0: $("#fmespaga").val(),tipo_0: $("#fcodigo").val(),num_ref_0: $("#fcomprob").val(),preca_0: $("#ftarifa").val(),fnombre: $("#fnombre").val(),utribu: $("#utribu").val()},
							function(data) {
								if( data.substr(0,14) == "Venta Guardada"){
									$("#fcobroser").dialog( "close" );
									jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
									//apprise(data);
									$("#fcobroser").html("");
									$.post("'.site_url('ventas/sfac/dataprintser/modify').'/"+data.substr(15,10), function(data){
										$("#fimpser").html(data);
									});
									$("#fimpser").dialog( "open" );
									return true;
								}else{
									apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+data);
								}
							}
						);
					},
					Cancel: function() {
						$("#fcobroser").html("");
						$( this ).dialog( "close" );
					}
				},
				close: function() {
					$("#fcobroser").html("");
				}
			});';

		$bodyscript .= '
			$("#fncob").dialog({
				autoOpen: false, height: 350, width: 500, modal: true,
				buttons: {
					"Guardar": function(){
						var bValid = true;
						var murl = $("#df1").attr("action");
						$.ajax({
							type: "POST",
							dataType: "html",
							async: false,
							url: murl,
							data: $("#df1").serialize(),
							success: function(r,s,x){
								try{
									var json = JSON.parse(r);
									if(json.status == "A"){
										$("#fncob").dialog("close");
										//jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
										window.open(\''.site_url('finanzas/smov/smovprint').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
										return true;
									}else{
										apprise(json.mensaje);
									}
								}catch(e){
									$("#fncob").html(r);
								}
							}
						})
					},
					"Cancelar": function() {
						$("#fncob").html("");
						$( this ).dialog( "close" );
					}
				},
				close: function(){
					$("#fncob").html("");
				}
			});';

		$bodyscript .= '
		$("#scliexp").dialog({
			autoOpen:false, modal:true, width:500, height:350,
			buttons: {
				"Guardar": function(){
					var murl = $("#sclidialog").attr("action");
					$.ajax({
						type: "POST", dataType: "json", async: false,
						url: murl,
						data: $("#sclidialog").serialize(),
						success: function(r,s,x){
							if(r.status=="B"){
								$("#sclidialog").find(".alert").html(r.mensaje);
							}else{
								$("#scliexp").dialog( "close" );

								$("#cod_cli").val(r.data.cliente);

								$("#nombre").val(r.data.nombre);
								$("#nombre_val").text(r.data.nombre);

								$("#rifci").val(r.data.rifci);
								$("#rifci_val").text(r.data.rifci);

								$("#sclitipo").val(r.data.tipo);

								$("#direc").val(r.data.direc);
								$("#direc_val").text(r.data.direc);

								$("#descuento").val("0");
								return true;
							}
						}
					});

				},
				"Cancelar": function(){
					$("#scliexp").html("");
					$(this).dialog("close");
				}
			},
			close: function(){
				$("#scliexp").html("");
			}
		});';

		$bodyscript .= '});';
		$bodyscript .= '</script>';

		return $bodyscript;
	}

	//******************************************************************
	// Definicion del Grid y la Forma
	//
	function defgrid( $deployed = false, $xmes = 'true' ){
		$i      = 1;
		$editar = 'false';

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
			'editoptions'   => '{ size:3, maxlength: 1 }',
			'cellattr'      => 'function(rowId, tv, aData, cm, rdata){
				var tips = "";
				if(aData.numero !== undefined){
					if(aData.tipo_doc=="X"){
						tips = "Factura Anulada";
					}else if(aData.numero.substr(0, 1) == "_"){
						tips = "Factura Pendiente";
					} else if(aData.tipo_doc=="D"){
						tips = "Devolucion";
					}else{
						tips = "Factura Guardada";
					}
				}
				return \'title="\'+tips+\'"\';
			}'
		));

		$grid->addField('numero');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 65,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 75,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }',
			//'searchoptions' => "{ sopt:['eq','ne','le','lt','gt','ge']}"
		));

		$grid->addField('vence');
		$grid->label('Vence');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 75,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$mSQL = "SELECT vendedor, concat( vendedor, ' ',TRIM(nombre)) nombre FROM vend ORDER BY nombre ";
		$avende  = $this->datasis->llenajqselect($mSQL, true );

		$grid->addField('vd');
		$grid->label('Vendedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $xmes,
			'width'         => 50,
			'edittype'      => "'select'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ value: '.$avende.',  style:"width:200px"}',
			'stype'         => "'text'",
		));

		$grid->addField('cod_cli');
		$grid->label('Cliente');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));

		$grid->addField('rifci');
		$grid->label('RIF/CI');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 13 }',
		));

		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 170,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));

		$grid->addField('referen');
		$grid->label('Ref.');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
			'cellattr'      => 'function(rowId, tv, aData, cm, rdata){
				var tips = "";
				if(aData.referen !== undefined){
					if(aData.referen=="P"){
						tips = "Pendiente";
					}else if(aData.referen=="E"){
						tips = "Contado en Efectivo";
					}else if(aData.referen=="M"){
						tips = "Mixto";
					}else{
						tips = aData.referen;
					}
				}
				return \'title="\'+tips+\'"\';
			}'
		));

		$grid->addField('totals');
		$grid->label('Sub Total');
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

		$grid->addField('iva');
		$grid->label('I.V.A.');
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

		$grid->addField('totalg');
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

		$grid->addField('bultos');
		$grid->label('Bultos');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('orden');
		$grid->label('Orden');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 10 }',
		));


		$grid->addField('inicial');
		$grid->label('Inicial');
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



		$grid->addField('status');
		$grid->label('Estatus');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('devolu');
		$grid->label('Devoluci&oacute;n');
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


		$grid->addField('cajero');
		$grid->label('Cajero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('almacen');
		$grid->label('Almac&eacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 4 }',
		));

		$grid->addField('montasa');
		$grid->label('Base G.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('monredu');
		$grid->label('Base R.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('monadic');
		$grid->label('Base A.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('tasa');
		$grid->label('Impuesto G.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('reducida');
		$grid->label('Impuesto R.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('sobretasa');
		$grid->label('Impuesto A.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('exento');
		$grid->label('Exento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

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


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
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


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('transac');
		$grid->label('Transacci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
			'formatter'     => 'ltransac'
		));


		$grid->addField('nfiscal');
		$grid->label('No.Fiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:15, maxlength: 12 }',
		));


		$grid->addField('entregado');
		$grid->label('Entregado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $xmes,
			'width'         => 75,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:false, date:true}',
			'formoptions'   => '{ label:"Fecha de Entrega" }'
		));


		$grid->addField('zona');
		$grid->label('Zona');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('ciudad');
		$grid->label('Ciudad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));

		$grid->addField('comiadi');
		$grid->label('Bono');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('comision');
		$grid->label('Comisi&oacute;n');
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


		$grid->addField('pagada');
		$grid->label('Pagada');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('manual');
		$grid->label('Manual');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

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


		$grid->addField('sepago');
		$grid->label('Sepago');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('dias');
		$grid->label('D&iacute;as');
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

		$grid->addField('maqfiscal');
		$grid->label('Maq.Fiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $xmes,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:15, maxlength: 20 }',
		));


		$grid->addField('dmaqfiscal');
		$grid->label('Devolu.M.Fiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $xmes,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:15, maxlength: 20 }',
		));


		$grid->addField('observa');
		$grid->label('Observaci&oacute;n 1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $xmes,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
			'formoptions'   => '{ label:"Observacion 1" }'
		));

		$grid->addField('observ1');
		$grid->label('Observaci&oacute;n 2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $xmes,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
			'formoptions'   => '{ label:"Observacion 2" }'
		));

		$grid->addField('maestra');
		$grid->label('Maestra');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('reparto');
		$grid->label('Reparto');
		$grid->params(array(
			'search'        => 'true',
			'align'         => "'center'",
			'width'         => 60,
			'editable'      => 'false',
		));

		$grid->addField('entregable');
		$grid->label('Entregable');
		$grid->params(array(
			'search'        => 'true',
			'align'         => "'center'",
			'width'         => 60,
			'editable'      => 'false',
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'hidden'        => 'true',
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('165');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					$(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					$(gridId2).trigger("reloadGrid");
					$.ajax({
						url: "'.base_url().'ventas/sfac/tabla/"+id,
						success: function(msg){
							$("#ladicional").html(msg);
						}
					});
				}
			}
		');

		$grid->setAfterInsertRow('
			function( rid, aData, rowe){
				if(aData.numero !== undefined){
					if(aData.tipo_doc == "X"){
						$(this).jqGrid( "setCell", rid, "tipo_doc","", {color:"#FFFFFF", background:"#C90623" });
					}else if(aData.numero.substr(0, 1) == "_"){
						$(this).jqGrid( "setCell", rid, "tipo_doc","", {color:"#FFFFFF", background:"#FFDD00" });
					}
				}
			}
		');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 450, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 450, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons

		//$grid->setEdit(  true);
		$grid->setEdit(  $this->datasis->sidapuede('SFACTER','MODIFICA%') || $this->datasis->sidapuede('SFAC','SFACMODI%'));
		$grid->setAdd(   $this->datasis->sidapuede('SFACTER','INCLUIR%' ));
		$grid->setDelete($this->datasis->sidapuede('SFACTER','BORR_REG%') || $this->datasis->sidapuede('SFAC','SFACANU%'));
		$grid->setSearch($this->datasis->sidapuede('SFACTER','BUSQUEDA%'));

		$grid->setRowNum(30);
		$grid->setBarOptions('addfunc: sfacadd, editfunc: sfacedit, delfunc: sfacdel, viewfunc: sfacshow');
		$grid->setShrinkToFit('false');

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

	//******************************************************************
	//Busca la data en el Servidor por json
	function getdata(){
		$grid = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('sfac');
		$mWHERE[]= array('','sprv != ""','');
		$mWHERE[]= array('','sprv IS NOT NULL','');

		$response   = $grid->getData('sfac', array(array()), array(), false, $mWHERE, 'id', 'desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	//******************************************************************
	//Busca la data en el Servidor por json
	function getdatam(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('sfac');
		$mWHERE[] = array('', 'fecha', date('Ymd'), '' );
		$mWHERE[] = array('', 'usuario', $this->session->userdata('usuario'),'');

		$response   = $grid->getData('sfac', array(array()), array(), false, $mWHERE, 'id', 'desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	//******************************************************************
	//Guarda la Informacion
	function setData(){
		$oper   = $this->input->post('oper');
		$id     = intval($this->input->post('id'));
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($id>0){
			if($oper == 'edit') {
				if(empty($data['entregado'])) unset($data['entregado']);

				$posibles=array('entregado','bultos','nfiscal','maqfiscal','comiadi','observa','observ1','dmaqfiscal','vd');
				foreach($data as $ind=>$val){
					if(!in_array($ind,$posibles)){
						echo 'Campo no permitido ('.$ind.')';
						return false;
					}
				}

				$row = $this->datasis->damerow("SELECT tipo_doc, numero,vd,transac, cod_cli AS cliente,fecha FROM sfac WHERE id=${id}");
				if(empty($row)){
					echo 'Registro no encontrado';
					return false;
				}

				$this->db->where('id', $id);
				$this->db->update('sfac', $data);

				if($row['vd']!=$data['vd']){
					$this->db->where('id_sfac', $id);
					$this->db->update('sitems', array('vendedor'=>$data['vd']));

					$this->db->where('numero'   , $row['numero']);
					$this->db->where('cod_cli'  , $row['cliente']);
					$this->db->where('transac'  , $row['transac']);
					$this->db->update('smov', array('vendedor'=>$data['vd']));

					$this->db->where('numero'   , $row['numero']);
					$this->db->where('f_factura', $row['fecha']);
					$this->db->where('cod_cli'  , $row['cliente']);
					$this->db->where('transac'  , $row['transac']);
					$this->db->update('sfpa', array('vendedor'=>$data['vd']));
				}

				$numero=$row['numero'];
				logusu('sfac',"Factura ${numero} ${id} MODIFICADO");
				echo 'Registro Modificado';

			} elseif($oper == 'del') {
				echo 'Deshabilitado';
			}
		}
	}

	//******************************************************************
	//Guarda la Informacion
	function setDatam(){
		echo 'Deshabilitado';
	}

	//******************************************************************
	//Definicion del Grid y la Forma
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('codigoa');
		$grid->label('C&oacute;digo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));


		$grid->addField('desca');
		$grid->label('Descripci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('cana');
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


		$grid->addField('preca');
		$grid->label('Precio');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 85,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('tota');
		$grid->label('Total');
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

		//$grid->addField('fecha');
		//$grid->label('Fecha');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'width'         => 70,
		//	'align'         => "'center'",
		//	'edittype'      => "'text'",
		//	'editrules'     => '{ required:true,date:true}',
		//	'formoptions'   => '{ label:"Fecha" }'
		//));


		//$grid->addField('vendedor');
		//$grid->label('Vendedor');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'width'         => 50,
		//	'edittype'      => "'text'",
		//	'editrules'     => '{ required:true}',
		//	'editoptions'   => '{ size:30, maxlength: 5 }',
		//));


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

		$grid->addField('utilidad');
		$grid->label('Utilidad');
		$grid->params(array(
			'search'        => 'false',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('porcen');
		$grid->label('Margen%');
		$grid->params(array(
			'search'        => 'false',
			'editable'      => 'false',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 77,
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

		//$grid->addField('comision');
		//$grid->label('Comisi&oacute;n');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'align'         => "'right'",
		//	'edittype'      => "'text'",
		//	'width'         => 100,
		//	'editrules'     => '{ required:true }',
		//	'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		//	'formatter'     => "'number'",
		//	'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		//));


		//$grid->addField('cajero');
		//$grid->label('Cajero');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'width'         => 50,
		//	'edittype'      => "'text'",
		//	'editrules'     => '{ required:true}',
		//	'editoptions'   => '{ size:30, maxlength: 5 }',
		//));


		$grid->addField('despacha');
		$grid->label('Despacha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('pvp');
		$grid->label('Precio 1');
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

		$grid->addField('precio4');
		$grid->label('Precio 4');
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


		$grid->addField('detalle');
		$grid->label('Detalle');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));


		$grid->addField('fdespacha');
		$grid->label('F.Despacho');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('udespacha');
		$grid->label('U.Despacho');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('combo');
		$grid->label('Combo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
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
		$grid->setHeight('190');
		$grid->setfilterToolbar(false);
		$grid->setToolbar('false', '"top"');

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');
		$grid->setOndblClickRow('');

		//$grid->footerrow=true;
		//$grid->setGridComplete('
		//	function(){
		//		//var cana = $(this).jqGrid("getGridParam", "records")+1;
		//		//$(this).jqGrid("addRowData", cana, {codigoa:"Total:",desca:"",cana:"",costo:"averr"});
        //
		//		var totalutil = $(this).jqGrid("getCol", "utilidad", false, "sum");
		//		var totalsub  = $(this).jqGrid("getCol", "tota"    , false, "sum");
		//		var totalcosto= $(this).jqGrid("getCol", "costo"   , false, "sum");
		//		//	grid.jqGrid("footerData", "set", { DriverEn: "Total FTE:", FTEValue: sum });
		//		$(this).jqGrid("footerData", "set", { codigoa: "TOTALES:",tota:totalsub,costo:totalcosto,utilidad: totalutil});
		//	}'
		//);

		//$grid->setGridComplete('
		//	function(){
		//		//alert($(this).getGridParam("datatype"));
		//		$(this).setGridParam({datatype: "local"});
		//	}
		//');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdatait/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if($deployed){
			return $grid->deploy();
		}else{
			return $grid;
		}
	}

	//******************************************************************
	//Busca la data en el Servidor por json
	function getdatait(){
		$id = $this->uri->segment(4);
		if($id === false){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM sfac");
		}
		if(empty($id)) return '';
		$dbid     = $this->db->escape($id);
		$row      = $this->datasis->damerow("SELECT tipo_doc,numero FROM sfac WHERE id=${dbid}");
		if(empty($row)){
			return null;
		}

		$tipo_doc = $row['tipo_doc'];
		$numero   = $row['numero'];
		$dbtipo_doc = $this->db->escape($tipo_doc);
		$dbnumero   = $this->db->escape($numero);

		$orderby= '';
		$sidx=$this->input->post('sidx');
		if($sidx){
			$campos   = $this->db->list_fields('sitems');
			$campos[] = 'utilidad';
			$campos[] = 'porcen';
			if(in_array($sidx,$campos)){
				$sidx = trim($sidx);
				$sord   = $this->input->post('sord');
				$orderby="ORDER BY `${sidx}` ".(($sord=='asc')? 'ASC':'DESC');
			}
		}

		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT *,(preca-costo)*cana AS utilidad,((preca*100/costo)-100) AS porcen FROM sitems WHERE tipoa=${dbtipo_doc} AND numa=${dbnumero} ${orderby}";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	//******************************************************************
	//Guarda la Informacion
	function setdatait(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;
	}


	//******************************************************************
	// Forma de facturacion
	//
	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$do = new DataObject('sfac');
		$do->rel_one_to_many('sitems', 'sitems', array('id'=>'id_sfac'));
		//$do->rel_one_to_many('sfpa'  , 'sfpa'  , array('numero','transac'));
		$do->pointer('scli' ,'scli.cliente=sfac.cod_cli','scli.tipo AS sclitipo','left');
		$do->pointer('sprv' ,'sprv.proveed=sfac.sprv','sprv.nombre AS sprvnombre, sprv.rif AS sprvrif, sprv.direc1 AS sprvdirec','left');
		$do->rel_pointer('sitems','sinv','sitems.codigoa=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Facturas', $do);
		//$edit->back_url = site_url('ventas/sfacter/filteredgrid');
		$edit->set_rel_title('sitems','Producto <#o#>');

		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;

		$edit->sprv = new inputField('C&oacute;digo','sprv');
		$edit->sprv->size = 6;
		$edit->sprv->maxlength=5;
		$edit->sprv->rule='existesprv';
		//$edit->sprv->append($boton);

		$edit->sprvnombre = new hiddenField('Nombre', 'sprvnombre');
		$edit->sprvnombre->db_name     = 'sprvnombre';
		$edit->sprvnombre->pointer     = true;
		$edit->sprvnombre->maxlength   = 40;
		$edit->sprvnombre->size        = 25;
		$edit->sprvnombre->readonly =true;

		$edit->sprvrif = new hiddenField('RIF', 'sprvrif');
		$edit->sprvrif->db_name     = 'sprvrif';
		$edit->sprvrif->pointer     = true;
		$edit->sprvrif->autocomplete=false;
		$edit->sprvrif->size = 15;
		$edit->sprvrif->readonly =true;

		$edit->sprvdirec= new hiddenField('Direcci&oacute;n', 'sprvdirec');
		$edit->sprvdirec->db_name     = 'sprvdirec';
		$edit->sprvdirec->pointer     = true;
		$edit->sprvdirec->size        = 40;
		$edit->sprvdirec->readonly =true;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->vence = new DateonlyField('Vencimiento', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->rule = 'required';
		$edit->vence->mode = 'autohide';
		$edit->vence->size = 10;

		$edit->tipo_doc = new  dropdownField ('Documento', 'tipo_doc');
		$edit->tipo_doc->option('F','Factura');
		//$edit->tipo_doc->option('D','Devoluci&oacute;n');
		$edit->tipo_doc->style='width:200px;';
		$edit->tipo_doc->size = 5;
		$edit->tipo_doc->rule='required';

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->insertValue=$this->secu->getvendedor();
		$edit->vd->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->autocomplete=false;
		$edit->cliente->rule='required|existescli';
		//$edit->cliente->append($boton);

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->readonly =true;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new hiddenField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->readonly =true;
		$edit->rifci->size = 15;

		$edit->direc = new hiddenField('Direcci&oacute;n','direc');
		$edit->direc->readonly =true;
		$edit->direc->size = 40;

		//***********************************
		//  Campos para el detalle 1 sitems
		//***********************************
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size     = 12;
		$edit->codigoa->db_name  = 'codigoa';
		$edit->codigoa->rel_id   = 'sitems';
		$edit->codigoa->rule     = 'required';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='sitems';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'sitems';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'sitems';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive';
		$edit->preca->readonly  = true;

		$edit->detalle = new hiddenField('', 'detalle_<#i#>');
		$edit->detalle->db_name  = 'detalle';
		$edit->detalle->rel_id   = 'sitems';

		$edit->tota = new inputField('Importe <#o#>', 'tota_<#i#>');
		$edit->tota->db_name='tota';
		$edit->tota->size=10;
		$edit->tota->css_class='inputnum';
		$edit->tota->rel_id   ='sitems';

		for($i=1;$i<4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'sitems';
			$edit->$obj->pointer   = true;
		}

		$edit->precio4 = new hiddenField('', 'precio4_<#i#>');
		$edit->precio4->db_name   = 'precio4';
		$edit->precio4->rel_id    = 'sitems';

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'sitems';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'sitems';
		$edit->sinvpeso->pointer   = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name   = 'sinvtipo';
		$edit->sinvtipo->rel_id    = 'sitems';
		$edit->sinvtipo->pointer   = true;

		$edit->ivat = new hiddenField('I.V.A', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new hiddenField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new hiddenField('Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->observa   = new inputField('Observacion', 'observa');
		$edit->nfiscal   = new inputField('No.Fiscal', 'nfiscal');
		$edit->observ1   = new inputField('Observacion', 'observ1');
		$edit->zona      = new inputField('Zona', 'zona');
		$edit->ciudad    = new inputField('Ciudad', 'ciudad');
		$edit->exento    = new inputField('Exento', 'exento');
		$edit->maqfiscal = new inputField('Mq.Fiscal', 'maqfiscal');
		$edit->cajero    = new inputField('Cajero', 'cajero');
		$edit->referen   = new inputField('Referencia', 'referen');
		$edit->reiva     = new inputField('Retencion de IVA', 'reiva');
		$edit->creiva    = new inputField('Comprobante', 'creiva');
		$edit->freiva    = new inputField('Fecha', 'freiva');
		$edit->ereiva    = new inputField('Emision', 'ereiva');

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		$edit->buttons('add_rel');
		$edit->build();

		if($edit->on_success()) {
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			$conten['form']  =& $edit;
			$this->load->view('view_sfacter', $conten);
		}


/*
		//$data['script'] .= $script;
		//$data['script'] .= $scriptreiva;

		$conten['form']  =&  $edit;

		$data['style']   = style('redmond/jquery-ui.css');
		$data['style']  .= style('gt_grid.css');
		$data['style']	.= style("impromptu.css");

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data["script"] .= script("jquery-impromptu.js");
		$data["script"] .= script("plugins/jquery.blockUI.js");
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= phpscript('nformat.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script("gt_msg_en.js");
		$data['script'] .= script("gt_grid_all.js");
		$data['content'] = $this->load->view('view_sfacter', $conten,true);
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
*/

	}

	function _pre_insert($do){
		$numero  = $this->datasis->fprox_numero('nsfac');
		$transac = $this->datasis->fprox_numero('ntransa');
		$do->set('numero',$numero);
		$do->set('transac',$transac);
		$alma = $this->secu->getalmacen();
		if(strlen($alma)<=0){
			$alma = $this->datasis->traevalor('ALMACEN');
		}
		$do->set('almacen',$alma);
		$con=$this->db->query("SELECT tasa,redutasa,sobretasa FROM civa ORDER BY fecha desc LIMIT 1");
		$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');

		$fecha =$do->get('fecha');
		$vd    =$do->get('vendedor');
		$tipoa =$do->get('tipo_doc');

		$iva=$totals=0;
		$tasa=$montasa=$reducida=$monredu=$sobretasa=$monadic=$exento=0;
		$cana=$do->count_rel('sitems');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('sitems','cana',$i);
			$itpreca   = $do->get_rel('sitems','preca',$i);
			$itiva     = $do->get_rel('sitems','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('sitems','tota'    ,$itimporte,$i);
			$do->set_rel('sitems','mostrado',$itimporte*(1+($itiva/100)),$i);

			$iiva    =$itimporte*($itiva/100);
			$iva    +=$iiva;
			$totals +=$itimporte;

			if($itiva-$t==0) {
				$tasa   +=$iiva;
				$montasa+=$itimporte;
			}elseif($itiva-$rt==0) {
				$reducida+=$iiva;
				$monredu +=$itimporte;
			}elseif($itiva-$st==0) {
				$sobretasa+=$iiva;
				$monadic  +=$itimporte;
			}else{
				$exento+=$itimporte;
			}

			$do->set_rel('sitems','numa'    ,$numero ,$i);
			$do->set_rel('sitems','tipoa'   ,$tipoa  ,$i);
			$do->set_rel('sitems','transac' ,$transac,$i);
			$do->set_rel('sitems','fecha'   ,$fecha  ,$i);
			$do->set_rel('sitems','vendedor',$vd     ,$i);
		}
		$totalg = $totals+$iva;

		$do->set('exento'   ,$exento   );
		$do->set('tasa'     ,$tasa     );
		$do->set('reducida' ,$reducida );
		$do->set('sobretasa',$sobretasa);
		$do->set('montasa'  ,$montasa  );
		$do->set('monredu'  ,$monredu  );
		$do->set('monadic'  ,$monadic  );
		$do->set('referen'  ,'C'       );

		$do->set('inicial',0 );
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));

		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return false;
	}

	function _post_insert($do){
		$numero =$do->get('numero');
		$fecha  =$do->get('fecha');
		$totneto=$do->get('totalg');
		$hora   =$do->get('hora');
		$usuario=$do->get('usuario');
		$transac=$do->get('transac');
		$nombre =$do->get('nombre');
		$cod_cli=$do->get('cod_cli');
		$estampa=$do->get('estampa');
		$sprv   =$do->get('sprv');
		$iva    =$do->get('iva');
		$ref_numero='00000000';
		$error  = 0;

		//Inserta en smov
		$data=array();
		$data['cod_cli']    = $cod_cli;
		$data['nombre']     = $nombre;
		$data['tipo_doc']   = 'FC';
		$data['numero']     = $numero;
		$data['fecha']      = $fecha;
		$data['monto']      = $totneto;
		$data['impuesto']   = $iva;
		$data['abonos']     = 0;
		$data['vence']      = $fecha;
		$data['tipo_ref']   = 'FT';
		$data['num_ref']    = $sprv;
		$data['observa1']   = (!empty($sprv))? 'FACTURA P.CTA DE TERCERO '.$sprv : 'FACTURA A CREDITO';
		$data['estampa']    = $estampa;
		$data['hora']       = $hora;
		$data['transac']    = $transac;
		$data['usuario']    = $usuario;
		$data['codigo']     = 'NOCON';
		$data['descrip']    = 'NOTA DE CONTABILIDAD';

		$sql= $this->db->insert_string('smov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'sfacter'); $error++;}

		//Inserta en sprm
		if(!empty($sprv)){
			$causado  = $this->datasis->fprox_numero('ncausado');
			$sprvnobre= $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($sprv));
			$mnumnc   = $this->datasis->fprox_numero('num_nd');

			$data=array();
			$data['cod_prv']    = $sprv;
			$data['nombre']     = $sprvnobre;
			$data['tipo_doc']   = 'ND';
			$data['numero']     = $mnumnc;
			$data['fecha']      = $fecha;
			$data['monto']      = $totneto;
			$data['impuesto']   = 0;
			$data['abonos']     = 0;
			$data['vence']      = $fecha;
			$data['observa1']   = 'FACTURA P.CTA DE TERCERO '.$cod_cli;
			$data['observa2']   = '';
			$data['tipo_ref']   = 'FT';
			$data['num_ref']    = $numero;
			$data['transac']    = $transac;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['usuario']    = $usuario;
			$data['reteiva']    = 0;
			$data['montasa']    = 0;
			$data['monredu']    = 0;
			$data['monadic']    = 0;
			$data['tasa']       = 0;
			$data['reducida']   = 0;
			$data['sobretasa']  = 0;
			$data['exento']     = 0;
			$data['causado']    = $causado;
			$data['codigo']     = 'NOCON';
			$data['descrip']    = 'NOTA DE CONTABILIDAD';

			$sql=$this->db->insert_string('sprm', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'sfacter'); $error++;}
		}

		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}


	function instalar(){
		if(!$this->datasis->iscampo('sfac','sprv')){
			$mSQL="ALTER TABLE sfac ADD COLUMN sprv VARCHAR(5) NULL DEFAULT NULL COMMENT ''";
			$ban=$this->db->simple_query($mSQL);
		}
	}
}
