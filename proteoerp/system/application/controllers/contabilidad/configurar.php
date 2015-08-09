<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Configurar extends Controller {

	function Configurar(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(605,1);
	}

	function index() {
		redirect('contabilidad/configurar/dataedit/show/1');
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Parametros Contables','cemp');
		$edit->back_url = 'contabilidad/configurar';

		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->pre_process( 'delete','_pre_delete' );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->inicio = new DateonlyField('Desde', 'inicio','d/m/Y');
		$edit->inicio->group = 'Ejercicio Fiscal';
		$edit->inicio->rule= 'required';
		$edit->inicio->size= 12;

		$edit->final = new DateonlyField('Hasta', 'final','d/m/Y');
		$edit->final->group = 'Ejercicio Fiscal';
		$edit->final->rule= 'required';
		$edit->final->size= 12;

		$edit->formato = new inputField('Formato', 'formato');
		$edit->formato->group = 'Ejercicio Fiscal';
		//$edit->formato->maxlength =17;
		$edit->formato->rule='trim|strtoupper|callback_chformato|required';
		$edit->formato->size=22;
		$edit->formato->autocomplete=false;

		$edit->resultado = new inputField('Resultado'  , 'resultado');
		$edit->resultado->maxlength =15;
		$edit->resultado->rule='required';
		$edit->resultado->size=20;

		$edit->patrimonio = new dropdownField('Patrimonio'  , 'patrimo');
		$edit->patrimonio->option('','');
		$edit->patrimonio->options("SELECT SUBSTRING_INDEX(codigo, '.', 1) cuenta,SUBSTRING_INDEX(codigo, '.', 1) valor  FROM cpla GROUP BY cuenta");
		$edit->patrimonio->style='width:50px';
		$edit->patrimonio->rule='required';
		$edit->patrimonio->group = 'Ejercicio Fiscal';

		$edit->ordend = new dropdownField('Deudora'  , 'ordend');
		$edit->ordend->group = 'Cuentas de Orden';
		$edit->ordend->option('','');
		$edit->ordend->options("SELECT SUBSTRING_INDEX(codigo, '.', 1) cuenta,SUBSTRING_INDEX(codigo, '.', 1) valor  FROM cpla GROUP BY cuenta");
		$edit->ordend->style='width:50px';

		$edit->ordena = new dropdownField('Acreedora', 'ordena');
		$edit->ordena->option('','');
		$edit->ordena->options("SELECT SUBSTRING_INDEX(codigo, '.', 1) cuenta,SUBSTRING_INDEX(codigo, '.', 1) valor  FROM cpla GROUP BY cuenta");
		$edit->ordena->group = 'Cuentas de Orden';
		$edit->ordena->style='width:50px';

		$edit->buttons('modify', 'save', 'undo');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = '<h1>Configuraci&oacute;n de la Contabilidad</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function chformato($formato){
		if (preg_match("/^X+(\.X+)*$/", $formato)==0){
			$this->validation->set_message('chformato',"El formato '${formato}' introducido no parece valido");
			return false;
		}else{
			return true;
		}
	}

	function arreglaforma(){
		$formato    = trim($this->datasis->formato_cpla());
		$arr_formato= explode(',',$formato);

		$mSQL="SELECT codigo FROM cpla ORDER BY codigo";
		foreach ($query->result() as $row){
			echo $row->codigo;
		}
	}

	function _pre_update($do){
		$formato =trim($this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1'));
		$nformato=trim($do->get('formato'));
		if(strlen($nformato) > strlen($formato)){
			//Arregla la longitud de la cuenta en las tablas
			$length = strlen($nformato);
			if($length>0){
				$tables = $this->db->list_tables();
				foreach($tables as $table){
					if(preg_match("/^view_.*$|^sp_.*$|^viemovinxventas$|^vietodife$/i",$table)) continue;

					if($table=='cpla'){
						$cc = 'codigo';
					}elseif($table=='cemp'){
						$cc = 'formato';
					}else{
						$cc = 'cuenta';
					}

					$fields = $this->db->field_data($table);
					foreach($fields as $field){
						$tbleng=intval($field->max_length);
						if($field->name==$cc && $tbleng<$length){
							$this->db->simple_query("ALTER TABLE `${table}`  CHANGE COLUMN `${cc}` `${cc}` VARCHAR(${length}) NULL DEFAULT NULL");
							break;
						}
					}
				}


			}
		}
		return true;
	}

	function _pre_insert($do){
		$do->error_message_ar['pre_ins'] = 'Deshabilitado';
		return false;
	}

	function _pre_delete($do){
		$do->error_message_ar['pre_del'] = 'Deshabilitado';
		return false;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu('cemp',"Conf. contable ${primary} CREADO");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu('cemp',"Conf. contable ${primary} MODIFICADO");
	}
	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu('cemp',"Conf. contable ${primary} ELIMINADO");
	}
}
