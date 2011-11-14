<?php
class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(201,1);
		$this->back_dataedit='compras/scst/datafilter';
	}

	function index(){
		$this->db->simple_query("DELETE FROM itscst WHERE control IN (SELECT control FROM scst a WHERE LENGTH(a.transac)=0 or a.transac is null)");
		$this->db->simple_query("DELETE FROM scst a WHERE LENGTH(a.transac)=0 or a.transac is null");
		redirect('compras/scst/datafilter');
		//redirect('compras/scst/extgrid');
	}
	
	function extgrid(){
		$this->datasis->modulo_id(201,1);
		//$script = $this->scstextjs();
		$data["script"] = $script;
		$data['title']  = heading('Compras de Productos');
		//$this->load->view('extjs/ventana',$data);
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
		$transac=$edit->get_from_dataobjetct('transac');
		if(!empty($transac))
		$edit->fecha->mode='autohide';

		$edit->vence = new DateonlyField('Vence', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->size = 10;
		$edit->vence->rule ='required';

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
		$edit->proveed->readonly=true;
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
		$edit->tipo->option('NC','Nota de Cr&eacute;dito');
		$edit->tipo->option('NE','Nota de Entrega');
		$edit->tipo->rule = 'required';
		$edit->tipo->style='width:140px;';

		$edit->peso  = new hiddenField('Peso', 'peso');
		$edit->peso->size = 20;
		$edit->peso->css_class='inputnum';

		$edit->orden  = new inputField('Orden', 'orden');
		$edit->orden->when=array('show');
		$edit->orden->size = 15;

		$edit->credito  = new inputField('Cr&eacute;dito', 'credito');
		$edit->credito->size = 20;
		$edit->credito->css_class='inputnum';
		$edit->credito->when=array('show');

		$edit->montotot  = new inputField('Subtotal', 'montotot');
		$edit->montotot->onchange='cmontotot()';
		$edit->montotot->size = 15;
		$edit->montotot->css_class='inputnum';

		$edit->montoiva  = new hiddenField('IVA', 'montoiva');
		$edit->montoiva->size = 20;
		$edit->montoiva->css_class='inputnum';

		$edit->montonet  = new hiddenField('Total', 'montonet');
		//$edit->montonet->size = 20;
		//$edit->montonet->css_class='inputnum';

		$edit->anticipo  = new inputField('Anticipo', 'anticipo');
		$edit->anticipo->size = 20;
		$edit->anticipo->css_class='inputnum';
		$edit->anticipo->when=array('show');

		$edit->inicial  = new inputField('Contado', 'inicial');
		$edit->inicial->size = 20;
		$edit->inicial->css_class='inputnum';
		$edit->inicial->when=array('show');

		$edit->rislr  = new inputField('Retenci&oacute;n ISLR', 'reten');
		$edit->rislr->size = 20;
		$edit->rislr->css_class='inputnum';
		$edit->rislr->when=array('show');

		$edit->riva  = new inputField('Retenci&oacute;n IVA', 'reteiva');
		$edit->riva->size = 20;
		$edit->riva->css_class='inputnum';
		$edit->riva->when=array('show');

		$edit->mdolar  = new inputField('Monto US $', 'mdolar');
		$edit->mdolar->size = 20;
		$edit->mdolar->css_class='inputnum';

		$edit->observa1 = new textareaField('Observaci&oacute;n', 'observa1');
		$edit->observa1->cols=90;
		$edit->observa1->rows=3;

		$edit->observa2 = new textareaField('Observaci&oacute;n', 'observa2');
		$edit->observa2->when=array('show');
		$edit->observa2->rows=3;

		$edit->observa3 = new textareaField('Observaci&oacute;n', 'observa3');
		$edit->observa3->when=array('show');
		$edit->observa3->rows=3;

		//Para CXP
		/*$edit->cexento  = new inputField('Excento', 'cexento');
		$edit->cexento->size = 20;
		$edit->cexento->css_class='inputnum';

		$edit->cgenera  = new inputField('Base imponible tasa General', 'cgenera');
		$edit->cgenera->size = 20;
		$edit->cgenera->css_class='inputnum';

		$edit->civagen  = new inputField('Monto alicuota tasa General', 'civagen');
		$edit->civagen->size = 10;
		$edit->civagen->css_class='inputnum';

		$edit->creduci  = new inputField('Base imponible tasa Reducida', 'creduci');
		$edit->creduci->size = 20;
		$edit->creduci->css_class='inputnum';

		$edit->civared  = new inputField('Monto alicuota tasa Reducida', 'civared');
		$edit->civared->size = 10;
		$edit->civared->css_class='inputnum';

		$edit->cadicio  = new inputField('Base imponible tasa Adicional', 'cadicio');
		$edit->cadicio->size = 20;
		$edit->cadicio->css_class='inputnum';

		$edit->civaadi  = new inputField('Monto alicuota tasa Adicional', 'civaadi');
		$edit->civaadi->size = 10;
		$edit->civaadi->css_class='inputnum';

		$edit->cstotal  = new hiddenField('Sub-total', 'cstotal');
		$edit->cstotal->size = 20;
		$edit->cstotal->css_class='inputnum';

		$edit->cimpuesto  = new hiddenField('Total Impuesto', 'cimpuesto');
		$edit->cimpuesto->size = 10;
		$edit->cimpuesto->css_class='inputnum';

		$edit->ctotal  = new hiddenField('Total', 'ctotal');
		$edit->ctotal ->size = 20;
		$edit->ctotal ->css_class='inputnum';*/
		//Fin de CxP

		//Campos para el detalle
		$edit->codigo = new inputField('C&oacute;digo', 'codigo_<#i#>');
		$edit->codigo->size=10;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$edit->codigo->autocomplete=false;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'required|callback_chcodigoa';
		$edit->codigo->rel_id   = 'itscst';

		$edit->descrip = new hiddenField('Descripci&oacute;n', 'descrip_<#i#>');
		$edit->descrip->size=30;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=12;
		$edit->descrip->rel_id  ='itscst';

		$edit->cantidad = new inputField('Cantidad', 'cantidad_<#i#>');
		$edit->cantidad->size=10;
		$edit->cantidad->db_name='cantidad';
		$edit->cantidad->maxlength=60;
		$edit->cantidad->autocomplete=false;
		$edit->cantidad->onkeyup  ='importe(<#i#>)';
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->rule     = 'require|numeric';
		$edit->cantidad->rel_id   = 'itscst';

		$edit->costo = new inputField('Costo', 'costo_<#i#>');
		$edit->costo->css_class='inputnum';
		$edit->costo->rule   = 'require|numeric';
		$edit->costo->onkeyup='importe(<#i#>)';
		$edit->costo->size=12;
		$edit->costo->autocomplete=false;
		$edit->costo->db_name='costo';
		$edit->costo->rel_id ='itscst';

		$edit->importe = new inputField('Importe', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=15;
		$edit->importe->rel_id='itscst';
		$edit->importe->autocomplete=false;
		$edit->importe->onkeyup='costo(<#i#>)';
		$edit->importe->css_class='inputnum';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name = 'sinvpeso';
		$edit->sinvpeso->rel_id  = 'itscst';
		$edit->sinvpeso->pointer = true;

		$edit->iva = new hiddenField('Impuesto', 'iva_<#i#>');
		$edit->iva->db_name = 'iva';
		$edit->iva->rel_id='itscst';
		//fin de campos para detalle

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$recep =strtotime($edit->get_from_dataobjetct('recep'));
		$fecha =strtotime($edit->get_from_dataobjetct('fecha'));
		$actuali=strtotime($edit->get_from_dataobjetct('actuali'));

		if($actuali < $fecha){
			$control=$this->rapyd->uri->get_edited_id();
			$accion="javascript:window.location='".site_url('compras/scst/actualizar/'.$control)."'";
			$accio2="javascript:window.location='".site_url('compras/scst/cprecios/'.$control)."'";
			$accio3="javascript:window.location='".site_url('compras/scst/montoscxp/modify/'.$control)."'";

			$edit->button_status('btn_actuali','Actualizar'     ,$accion,'TR','show');
			$edit->button_status('btn_precio' ,'Asignar precios',$accio2,'TR','show');
			$edit->button_status('btn_cxp'    ,'Ajuste CxP'     ,$accio3,'TR','show');
			$edit->buttons('modify');
		} else {
			$control=$this->rapyd->uri->get_edited_id();
			$accion="javascript:window.location='".site_url('compras/scst/reversar/'.$control)."'";
			$edit->button_status('btn_reversar','Reversar'     ,$accion,'TR','show');
		}
		$edit->buttons('save', 'undo','delete' ,'back','add_rel');
		$edit->build();

		$smenu['link']  =  barra_menu('201');
		$data['smenu']  =  $this->load->view('view_sub_menu', $smenu,true);
		$conten['form'] =& $edit;
		
		$ffecha=$edit->get_from_dataobjetct('fecha');
		$conten['alicuota']=$this->datasis->ivaplica(($ffecha==false)? null : $ffecha);

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= phpscript('nformat.js');

		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');

		$data['content'] = $this->load->view('view_compras', $conten,true);
		$data['title']   = heading('Compras');

		$this->load->view('view_ventanas', $data);
	}

	function cprecios($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datagrid','fields');

		if($this->input->post('pros') !== false){
			$precio1=$this->input->post('scstp_1');
			$precio2=$this->input->post('scstp_2');
			$precio3=$this->input->post('scstp_3');
			$precio4=$this->input->post('scstp_4');

			$error='';
			foreach(array_keys($precio1) as $ind){
				if($precio1[$ind]>=$precio2[$ind] && $precio2[$ind]>=$precio3[$ind] && $precio3[$ind]>=$precio4[$ind]){
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
					$error='Los precios deben cumplir esta regla (precio 1 >= precio 2 >= precio 3 >= precio 4)';
				}
			}
		}


		$ggrid =form_open('/compras/scst/cprecios/'.$control);
		
		function costo($formcal,$pond,$ultimo,$standard,$existen,$itcana){
			$CI =& get_instance();
			$costo_pond=$CI->_pond($existen,$itcana,$pond,$ultimo);
			//$costo_pond=(($pond*$existen)+($itcana*$ultimo))/($itcana+$existen);
			return $CI->_costos($formcal,$costo_pond,$ultimo,$standard);
		}

		function margen($formcal,$pond,$ultimo,$standard,$existen,$itcana,$precio,$iva){
			$costo=costo($formcal,$pond,$ultimo,$standard,$existen,$itcana);
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
		$select=array('b.codigo','b.descrip','b.formcal','a.costo','b.ultimo','b.pond','b.standard','a.id',
					  'a.precio1 AS scstp_1','a.precio2 AS scstp_2','a.precio3 AS scstp_3','a.precio4 AS scstp_4',
					  'b.precio1 AS sinvp1' ,'b.precio2 AS sinvp2' ,'b.precio3 AS sinvp3' ,'b.precio4 AS sinvp4',
					  'b.ultimo','b.pond','b.standard','b.formcal','a.cantidad','b.existen','b.iva'
					  );
		$grid->db->select($select);
		$grid->db->from('itscst AS a');
		$grid->db->join('sinv AS b','a.codigo=b.codigo');
		$grid->db->where('control' , $control);

		$grid->column('C&oacute;digo'     , 'codigo' );
		$grid->column('Descripci&oacute;n', 'descrip');

		$grid->column('costo' , '<tcosto><#id#>|<#iva#>|<#formcal#>|<#pond#>|<#ultimo#>|<#standard#>|<#existen#>|<#cantidad#></tcosto>','align=\'rigth\'');
		$itt=array('sinvp1','sinvp2','sinvp3','sinvp4');
		foreach ($itt as $id=>$val){
			$grid->column('Precio '.($id+1).' actual', $val,'align=\'right\'');
		}

		$itt=array('scstp_1','scstp_2','scstp_3','scstp_4');
		foreach ($itt as $val){
			$ind = $val;

			$campo = new inputField('Campo', $ind);
			$campo->grid_name=$ind.'[<#id#>]';
			$campo->status   ='modify';
			$campo->size     =8;
			$campo->autocomplete=false;
			$campo->css_class='inputnum';

			$grid->column($ind , $campo,'align=\'center\'');
		}

		$itt=array('margen_1','margen_2','margen_3','margen_4');
		foreach ($itt as $id=>$val){
			$ind = $val;

			$campo = new inputField('Campo', $ind);
			$campo->grid_name=$ind.'[<#id#>]';
			$campo->pattern  ='<margen><#formcal#>|<#pond#>|<#ultimo#>|<#standard#>|<#existen#>|<#cantidad#>|<#scstp_'.($id+1).'#>|<#iva#></margen>';
			$campo->status   ='modify';
			$campo->size     =5;
			$campo->autocomplete=false;
			$campo->css_class='inputnum';

			$grid->column($ind , $campo,'align=\'center\'');
		}

		$action = "javascript:window.location='".site_url('compras/scst/dataedit/show/'.$control)."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');

		$grid->submit('pros', 'Guardar','BR');
		$grid->build();

		$ggrid.=$grid->output;
		$ggrid.=form_close();

		$script='<script language="javascript" type="text/javascript">
		$(function(){

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

		$data['content'] = $ggrid;
		$data['title']   = heading('Cambio de precios');
		$data['script']  = $script;
		$data['script'] .= phpscript('nformat.js');
		$data['head']    = $this->rapyd->get_head().script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
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
		/*$edit->back_save   = true;
		$edit->back_cancel = true;
		$edit->back_cancel_save=true;*/
		//$edit->post_process('update' ,'_post_cxp_update');

		//Para CXP
		$edit->cexento = new inputField('Excento', 'cexento');
		$edit->cexento->size = 15;
		$edit->cexento->onkeyup='ctotales()';
		$edit->cexento->css_class='inputnum';

		$edit->cgenera = new inputField('Base imponible tasa General', 'cgenera');
		$edit->cgenera->size = 15;
		$edit->cgenera->onkeyup='cal_iva('.$alicuota['tasa'].',\'civagen\',this.value)';
		$edit->cgenera->css_class='inputnum';

		$edit->civagen = new inputField('Monto alicuota tasa General', 'civagen');
		$edit->civagen->size = 10;
		$edit->civagen->onkeyup='cal_base('.$alicuota['tasa'].',\'cgenera\',this.value)';
		$edit->civagen->css_class='inputnum';

		$edit->creduci = new inputField('Base imponible tasa Reducida', 'creduci');
		$edit->creduci->size = 15;
		$edit->creduci->onkeyup='cal_iva('.$alicuota['redutasa'].',\'civared\',this.value)';
		$edit->creduci->css_class='inputnum';

		$edit->civared = new inputField('Monto alicuota tasa Reducida', 'civared');
		$edit->civared->size = 10;
		$edit->civared->onkeyup='cal_base('.$alicuota['redutasa'].',\'creduci\',this.value)';
		$edit->civared->css_class='inputnum';

		$edit->cadicio = new inputField('Base imponible tasa Adicional', 'cadicio');
		$edit->cadicio->size = 15;
		$edit->cadicio->onkeyup='cal_iva('.$alicuota['sobretasa'].',\'civaadi\',this.value)';
		$edit->cadicio->css_class='inputnum';

		$edit->civaadi = new inputField('Monto alicuota tasa Adicional', 'civaadi');
		$edit->civaadi->size = 10;
		$edit->civaadi->onkeyup='cal_base('.$alicuota['sobretasa'].',\'cadicio\',this.value)';
		$edit->civaadi->css_class='inputnum';

		$edit->cstotal = new hiddenField('Sub-total', 'cstotal');
		$edit->cstotal->size = 20;
		$edit->cstotal->css_class='inputnum';

		$edit->cimpuesto = new hiddenField('Total Impuesto', 'cimpuesto');
		$edit->cimpuesto->size = 10;
		$edit->cimpuesto->css_class='inputnum';

		$edit->ctotal  = new hiddenField('Total', 'ctotal');
		$edit->ctotal ->size = 20;
		$edit->ctotal ->css_class='inputnum';
		//Fin de CxP

		$edit->buttons('save', 'undo','modify', 'back');
		$edit->build();

		$conten['form'] =& $edit;

		//$ffecha=$edit->get_from_dataobjetct('fecha');
		$ffecha=false;
		$conten['alicuota'] = $alicuota;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
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
		$serie   = $this->uri->segment($this->uri->total_segments());
		$control = $this->uri->segment($this->uri->total_segments()-1);
		if (!empty($serie)) {
			$this->db->simple_query("UPDATE scst SET serie='$serie' WHERE control='$control'");
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

		$form = new DataForm("compras/scst/actualizar/$control/process");

		$form->cprecio = new  dropdownField ('Cambiar precios', 'cprecio');
		//$form->cprecio->option('D','Dejar el precio mayor');
		$form->cprecio->option('S','Si');
		$form->cprecio->option('N','No');
		$form->cprecio->style = 'width:100px;';
		$form->cprecio->rule  = 'required';

		$form->fecha = new dateonlyField('Fecha de recepci&oacute;n de la compra', 'fecha','d/m/Y');
		$form->fecha->insertValue = date('Y-m-d');
		$form->fecha->rule='required|callback_chddate';
		$form->fecha->size=10;

		$form->submit('btnsubmit','Actualizar');
		$accion="javascript:window.location='".site_url('compras/scst/dataedit/show/'.$control)."'";
		$form->button('btn_regre','Regresar',$accion,'BR','show');
		$form->build_form();

		if($form->on_success()){
			$cprecio   = $form->cprecio->newValue;
			$actualiza = $form->fecha->newValue;
			$cambio    = ($cprecio=='S') ? true : false;
			
			$rt=$this->_actualizar($control,$cambio,$actualiza);
			if($rt===false){
				$data['content']  = $this->error_string.br();
			}else{
				$data['content']  = 'Compra actualizada'.br();
			}
			$data['content'] .= anchor('compras/scst/dataedit/show/'.$control,'Regresar');
		}else{
			$data['content'] = $form->output;
		}

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Actualizar compra');
		$this->load->view('view_ventanas', $data);
	}

	function _actualizar($control,$cprecio,$actuali=null){
		$error =0;
		$pasa=$this->datasis->dameval('SELECT COUNT(*) FROM scst WHERE actuali>=fecha AND control='.$this->db->escape($control));

		if($pasa==0){
			$SQL='SELECT transac,depo,proveed,fecha,vence, nombre,tipo_doc,nfiscal,fafecta,reteiva,
			cexento,cgenera,civagen,creduci,civared,cadicio,civaadi,cstotal,ctotal,cimpuesto,numero
			FROM scst WHERE control=?';
			$query=$this->db->query($SQL,array($control));
			
			if($query->num_rows()==1){
				$row     = $query->row_array();
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
							//existen=existen+'.$itrow->cantidad.'
							WHERE codigo='.$dbcodigo;
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }

						//$mSQL='UPDATE itsinv SET existen=existen+'.$itrow->cantidad.' WHERE codigo='.$this->db->escape($itrow->codigo).' AND alma='.$this->db->escape($depo);
						//$ban=$this->db->simple_query($mSQL);
						
						$this->datasis->sinvcarga($itrow->codigo,$depo, $itrow->cantidad );
						
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }

						if($itrow->precio1>0 && $itrow->precio2>0 && $itrow->precio3>0 && $itrow->precio4>0){
							//Cambio de precios
							if(!$cprecio){
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

				//Carga la CxP
				$mSQL='DELETE FROM sprm WHERE transac='.$this->db->escape($transac);
				$ban=$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'scst'); $error++; }

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
				$sprm['abonos']   = 0;
				$sprm['observa1'] = 'FACTURA DE COMPRA';
				$sprm['reteiva']  = $reteiva;
				$sprm['causado']  = $causado;
				$sprm['estampa']  = date('Y-m-d H:i:s');
				$sprm['usuario']  = $this->session->userdata('usuario');
				$sprm['hora']     = date('H:i:s');
				$sprm['transac']  = $transac;
				//$sprm['montasa']  = $row['cimpuesto'];
				//$sprm['impuesto'] = $row['cimpuesto'];
				//$sprm['impuesto'] = $row['cimpuesto'];

				$mSQL=$this->db->insert_string('sprm', $sprm);
				$ban =$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'scst'); $error++; }
				//Fin de la carga de la CxP

				//Inicio de la retencion
				if($reteiva>0){
					$niva    = $this->datasis->fprox_numero('niva');
					$ivaplica=$this->datasis->ivaplica($fecha);

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
					$riva['estampa']    = date('Y-m-d H:i:s');
					$riva['hora']       = date('H:i:s');
					$riva['usuario']    = $this->session->userdata('usuario');

					$mSQL=$this->db->insert_string('riva', $riva);
					$ban =$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }
				}//Fin de la retencion

				$mSQL='UPDATE scst SET `actuali`='.$actuali.', `recep`='.$actuali.' WHERE `control`='.$this->db->escape($control);
				$ban=$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'scst'); $error++; }
			}else{
				$this->error_string='Compra no existe';
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
		$mTRANSAC = $scst["transac"];
		// Si esta actualizada
		$mACTUALI = $scst["actuali"];
		$fecha    = $scst["fecha"];
		$tipo_doc = $scst["tipo_doc"];
		$numero   = $scst["numero"];
		$montonet = $scst["montonet"];
		$reteiva  = $scst["reteiva"];
		$fafecta  = $scst["fafecta"];
		$anticipo = $scst["anticipo"];
		$proveed  = $scst["proveed"];
		$mALMA    = $scst["depo"];

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
			echo "Factura no ha sido cargada";
			return ;
		}

		// ******* Borra de a CxC *******\\
		$mSQL = "DELETE FROM sprm WHERE transac='$mTRANSAC'";
		$this->db->simple_query($mSQL);

		if ( $tipo_doc == 'NC' ){
			$mSQL = "UPDATE sprm SET abonos=abonos-$montonet-$reteiva WHERE numero='$fafecta' AND tipo_doc='FC' AND cod_prv='$proveed' ";
			$this->db->simple_query($mSQL);
		}

		/* los anticipos aqui ya no se usan
		if ( $anticipo > 0 and $tipo_doc == 'FC' ) {
		   // DESACTUALIZA ANTICIPOS
		   mC := DAMECUR("SELECT * FROM itppro WHERE transac='"+mTRANSAC+"'")
		   WHILE !mC:EoF()
		      mTIPO_DOC := mC:FieldGet("tipoppro")
		      mNUMERO   := mC:FieldGet("numppro")
		      mFECHA    := mC:FieldGet("fecha")
		      mABONO    := mC:FieldGet("abono")
		      mSQL := "UPDATE sprm SET abonos=abonos-"+ALLTRIM(STR(mABONO))+" WHERE "
		      mSQL += "tipo_doc='"+mTIPO_DOC+"' AND numero='"+mNUMERO+"' AND cod_prv='"+XPROVEED+"' "
		      EJECUTASQL(mSQL)
		      mC:Skip()
		   ENDDO
		}
		*/

		$mSQL = "DELETE FROM itppro WHERE transac='$mTRANSAC'";
		$this->db->simple_query($mSQL);

		// ANULA LA RETENCION SI TIENE
		if ( $this->datasis->dameval("SELECT COUNT(*) FROM riva WHERE transac='$mTRANSAC+'") > 0 ){
			$mTRANULA = '_'.substr($this->datasis-prox_sql('rivanula'),1,7);
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

		//CMNJ("Compra Reversada en Inventario y CxP")
		//RETURN(.T.)
		echo "<h1>Compra Reversada en Inventario y CxP</h1>";
		echo anchor('compras/scst/dataedit/show/'.$control,'Regresar');
	}

	function creadseri($cod_prov,$factura){
		$cod_prove=$this->db->escape($cod_prov);
		$facturae =$this->db->escape($factura);
		$control=$this->datasis->fprox_numero('ntemp');
		$control=substr($control,1,7).'_';
		$controle=$this->db->escape($control);

		//$transac=$this->datasis->fprox_numero('ntransac');

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
		if(empty($transac)) $transac = $this->datasis->fprox_numero('ntransac');
		
		$fecha   = $do->get('fecha');
		$numero  = substr($do->get('serie'),-8);
		$usuario = $do->get('usuario');
		$proveed = $do->get('proveed');
		$depo    = $do->get('depo');
		$estampa = date('Ymd');
		$hora    = date("H:i:s");
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

			$do->set_rel('itscst','importe' ,$itimporte,$i);
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
		$do->set('numero'  ,$numero);
		$do->set('control' ,$control);
		$do->set('estampa' ,$estampa);
		$do->set('hora'    ,$hora);
		$do->set('transac' ,$transac);
		$do->set('montotot',round($stotal,2));
		$do->set('montonet',round($gtotal,2));
		$do->set('montoiva',round($iva   ,2));

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
		$this->_pre_insert($do);
		
		//return false;
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('scst',"Compra $codigo ELIMINADA");
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;


		$where = "";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where. 
				$where = "numero IS NOT NULL ";
				$qs = "";
				for ($i=0;$i<count($filter);$i++){
					switch($filter[$i]['type']){
					case 'string' : $qs .= " AND ".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'"; 
						Break;
					case 'list' :
						if (strstr($filter[$i]['value'],',')){
							$fi = explode(',',$filter[$i]['value']);
							for ($q=0;$q<count($fi);$q++){
								$fi[$q] = "'".$fi[$q]."'";
							}
							$filter[$i]['value'] = implode(',',$fi);
								$qs .= " AND ".$filter[$i]['field']." IN (".$filter[$i]['value'].")";
						}else{
							$qs .= " AND ".$filter[$i]['field']." = '".$filter[$i]['value']."'";
						}
						Break;
					case 'boolean' : $qs .= " AND ".$filter[$i]['field']." = ".($filter[$i]['value']); 
						Break;
					case 'numeric' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND ".$filter[$i]['field']." != ".$filter[$i]['value']; 
								Break;
							case 'eq' : $qs .= " AND ".$filter[$i]['field']." = ".$filter[$i]['value']; 
								Break;
							case 'lt' : $qs .= " AND ".$filter[$i]['field']." < ".$filter[$i]['value']; 
								Break;
							case 'gt' : $qs .= " AND ".$filter[$i]['field']." > ".$filter[$i]['value']; 
								Break;
						}
						Break;
					case 'date' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND ".$filter[$i]['field']." != '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'eq' : $qs .= " AND ".$filter[$i]['field']." = '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'lt' : $qs .= " AND ".$filter[$i]['field']." < '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'gt' : $qs .= " AND ".$filter[$i]['field']." > '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
						}
						Break;
					}
				}
				$where .= $qs;
			}
		}
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('scst');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'control', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $query->num_rows();

		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function griditscst(){
		$control   = isset($_REQUEST['control'])  ? $_REQUEST['control']   :  0;
		if ($control == 0 ) $control = $this->datasis->dameval("SELECT MAX(control) FROM scst")  ;

		$mSQL = "SELECT a.codigo, a.descrip, a.cantidad, a.costo, a.importe, a.iva, a.ultimo, a.precio1, a.precio2, a.precio3, a.precio4, b.id codid FROM itscst a JOIN sinv b ON a.codigo=b.codigo WHERE a.control='$control' ORDER BY a.codigo";
		$query = $this->db->query($mSQL);
		$results =  0; 
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sprvbu(){
		$control = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM scst a JOIN sprv b ON a.proveed=b.proveed WHERE control='$control'");
		redirect('compras/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$control   = isset($_REQUEST['control'])  ? $_REQUEST['control']   :  0;
		$transac = $this->datasis->dameval("SELECT transac FROM scst WHERE control='$control'");
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codprv = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				if ( $codprv != $row['cod_prv']){
					$codprv = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";	
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		echo $salida;
	}

	function scstextjs() {

		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">COMPRAS DE PRODUCTOS</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
		$listados= $this->datasis->listados('scst');
		$otros=$this->datasis->otros('scst', 'scst');

		$script = "
<script type=\"text/javascript\">		
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';
var modulo = 'scst'

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

var urlApp = '".base_url()."';

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	'Ext.data.*',
	'Ext.util.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var mxs = ((screen.availWidth/2) -400);
var mys = ((screen.availHeight/2)-300);

//var editor = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 2});

//Column Model Presupuestos
var ScstCol = 
	[
		{ header: 'Tipo',             width:  40, sortable: true,  dataIndex: 'tipo_doc', field: { type: 'textfield'  }, filter: { type: 'string' }}, 
		{ header: 'Numero',           width:  60, sortable: true,  dataIndex: 'numero',   field: { type: 'textfield'  }, filter: { type: 'string' }}, 
		{ header: 'Serie',            width:  90, sortable: true,  dataIndex: 'serie',    field: { type: 'textfield'  }, filter: { type: 'string' }, editor: 'textfield' }, 
		{ header: 'Fecha',            width:  70, sortable: false, dataIndex: 'fecha',    field: { type: 'date'       }, filter: { type: 'date'   }}, 
		{ header: 'Recibida',         width:  70, sortable: false, dataIndex: 'recep',    field: { type: 'date'       }, filter: { type: 'date'   }}, 
		{ header: 'Prov.',            width:  50, sortable: true,  dataIndex: 'proveed',  field: { type: 'textfield'  }, filter: { type: 'string' }, renderer: renderSprv }, 
		{ header: 'Nombre Proveedor', width: 200, sortable: true,  dataIndex: 'nombre',   field: { type: 'textfield'  }, filter: { type: 'string' }}, 
		{ header: 'SubTotal',         width: 100, sortable: true,  dataIndex: 'montotot', field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'IVA',              width:  80, sortable: true,  dataIndex: 'montoiva', field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Total',            width: 100, sortable: true,  dataIndex: 'montonet', field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Almacen',          width:  60, sortable: true,  dataIndex: 'depo',     field: { type: 'textfield'  }, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Observacion',      width: 160, sortable: true,  dataIndex: 'observa1', field: { type: 'textfield'  }, filter: { type: 'string' }}, 
		{ header: 'Control',          width:  60, sortable: true,  dataIndex: 'control',  field: { type: 'textfield'  }, filter: { type: 'string' }}, 
		{ header: 'Estampa',          width:  70, sortable: false, dataIndex: 'estampa',  field: { type: 'date'       }, filter: { type: 'date'   }}, 
		{ header: 'Hora',             width:  60, sortable: true,  dataIndex: 'hora',     field: { type: 'textfield'  }, filter: { type: 'string' }}, 
		{ header: 'Usuario',          width:  60, sortable: true,  dataIndex: 'usuario',  field: { type: 'textfield'  }, filter: { type: 'string' }}
	];

//Column Model Detalle de Presupuesto
var ItScstCol = 
	[
		{ header: 'Codigo',      width:  90, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderSinv }, 
		{ header: 'codid',       dataIndex: 'codid',  hidden: true}, 
		{ header: 'Descripcion', width: 250, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Cant',        width:  60, sortable: true, dataIndex: 'cantidad', field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Precio',      width:  80, sortable: true, dataIndex: 'costo',    field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Importe',     width: 100, sortable: true, dataIndex: 'importe',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',         width:  60, sortable: true, dataIndex: 'iva',      field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Ultimo',      width:  60, sortable: true, dataIndex: 'ultimo',   field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio 1',    width:  60, sortable: true, dataIndex: 'precio1',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio 2',    width:  60, sortable: true, dataIndex: 'precio2',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio 3',    width:  60, sortable: true, dataIndex: 'precio3',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio 4',    width:  60, sortable: true, dataIndex: 'precio4',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	];

function renderSprv(value, p, record) {
	var mreto='';
	if ( record.data.proveed == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'compras/scst/sprvbu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.control );
}

function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}

// application main entry point
Ext.onReady(function() {
	Ext.QuickTips.init();
	/////////////////////////////////////////////////
	// Define los data model
	Ext.define('Scst', {
		extend: 'Ext.data.Model',
		fields: ['id', 'tipo_doc', 'numero', 'serie', 'fecha', 'recep', 'proveed', 'nombre',  'montotot', 'montoiva', 'montonet', 'depo', 'observa1', 'control', 'estampa', 'hora', 'usuario'],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'compras/scst/grid',
				update : urlApp + 'compras/scst/modificar',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});	

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeScst = Ext.create('Ext.data.Store', {
		model: 'Scst',
		pageSize: 50,
		remoteSort: true,
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//Filters
	var filters = {
		ftype: 'filters',
		encode: 'json', // json encode the filter query
		local: false
	};    

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridScst = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeScst,
		title: 'Compras',
		iconCls: 'icon-grid',
		frame: true,
		columns: ScstCol,
		dockedItems: [{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlApp+'compras/scst/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridScst.getView().getSelectionModel().getSelection()[0];
						gridScst.down('#delete').setDisabled(selections.length === 0);
						window.open(urlApp+'compras/scst/dataedit/modify/'+selection.data.control, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridScst.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme', 
							msg: 'Seguro que quiere eliminar la compra Nro. '+selection.data.numero, 
							buttons: Ext.MessageBox.YESNO, 
							fn: function(btn){ 
								if (btn == 'yes') { 
									if (selection) {
										//storeScst.remove(selection);
									}
									storeScst.load();
								} 
							}, 
							icon: Ext.MessageBox.QUESTION 
						});  
					}
				}
			]
		}],
		features: [filters],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
		// paging bar on the bottom
		bbar: Ext.create('Ext.PagingToolbar', {
			store: storeScst,
			displayInfo: false,
			displayMsg: 'Pag No. {0} - Reg. {1} de {2}',
			emptyMsg: 'No se encontraron Registros.'
		}),
	});

//////************ MENU DE ADICIONALES /////////////////
".$listados."

".$otros."
//////************ FIN DE ADICIONALES /////////////////

	/////////////////////////////////////////////////
	// Define los data model
	// Compras
	Ext.define('ItScst', {
		extend: 'Ext.data.Model',
		fields: ['codigo', 'codid', 'descrip', 'cantidad', 'costo', 'importe', 'iva', 'ultimo','precio1', 'precio2','precio3', 'precio4' ],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'compras/scst/griditscst',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeItScst = Ext.create('Ext.data.Store', {
		model: 'ItScst',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridItScst = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeItScst,
		title: 'Articulos',
		iconCls: 'icon-grid',
		frame: true,
		columns: ItScstCol
	});

	var scstTplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR COMPRA</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/COMPRA/{control}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/COMPRA/{control}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridScst.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridScst.down('#delete').setDisabled(selectedRecord.length === 0);
			gridScst.down('#update').setDisabled(selectedRecord.length === 0);
			control = selectedRecord[0].data.control;
			gridItScst.setTitle(control+' '+selectedRecord[0].data.nombre);
			storeItScst.load({ params: { control: control }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlApp +'compras/scst/tabla',
				params: { control: selectedRecord[0].data.control, serie: selectedRecord[0].data.serie },
				success: function(response) {
					var vaina = response.responseText;
					scstTplMarkup.pop();
					scstTplMarkup.push(vaina);
					var scstTpl = Ext.create('Ext.Template', scstTplMarkup );
					meco1.setTitle('Imprimir Compra');
					scstTpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
		}
	});

	var viewport = new Ext.Viewport({
		id:'simplevp',
		layout:'border',
		border:false,
		items:[{
			region: 'north',
			preventHeader: true,
			height: 40,
			minHeight: 40,
			html: '".$encabeza."'
		},{
			region:'west',
			width:200,
			border:false,
			autoScroll:true,
			title:'Lista de Opciones',
			collapsible:true,
			split:true,
			collapseMode:'mini',
			layoutConfig:{animate:true},
			layout: 'accordion',
			items: [
				{
					layout: 'fit',
					items:[
						{
							name: 'imprimir',
							id: 'imprimir',
							//preventHeader: true,
							border:false,
							html: 'Para imprimir seleccione una Compra '
						}
					]
				},
				{
					title:'Listados',
					border:false,
					layout: 'fit',
					items: gridListado
				},
				{
					title:'Otras Funciones',
					border:false,
					layout: 'fit',
					items: gridOtros
				}
			]
		},{
			cls: 'irm-column irm-center-column irm-master-detail',
			region: 'center',
			title:  'center-title',
			layout: 'border',
			preventHeader: true,
			border: false,
			items: [{
				itemId: 'viewport-center-master',
				cls: 'irm-master',
				region: 'center',
				items: gridScst
			},{
				itemId: 'viewport-center-detail',
				activeTab: 0,
				region: 'south',
				height: '40%',
				split: true,
				margins: '0 0 0 0',
				preventHeader: true,
				items: gridItScst
			}]	
		}]
	});
	storeScst.load();
	storeItScst.load();
});

</script>
";
		return $script;	
		
	}

}