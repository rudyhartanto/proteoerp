<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Common extends controller {

	function _tabuladorfield($par){
		$this->rapyd->load('fields');

		$tipo = 'inputField';
		$rule = $scriptadd = '';
		$options= array();
		switch($par['tipo']){
			case 'date':
				$tipo = 'dateonlyField';
				$rule = 'chdate';

				break;
			case 'textarea':
				$tipo = 'textareaField';
				$rule = '';
				break;
			case 'dropdown':
				$tipo = 'dropdownField';
				$rule = '';

				$arr = json_decode($par['tipoadc'],true);
				if(is_array($arr)){
					$options=$arr;
				}

			case 'integer':
				$rule='integer';
			case '':
				$rule='numeric';
		}

		$campo = new $tipo($par['nombre'], $par['obj']);
		$campo->size        = 30;
		$campo->maxlength   = 255;

		if($par['tipo']=='date'){
			$nobj = str_replace('[', '\\\\[', $par['obj']);
			$nobj = str_replace(']', '\\\\]', $nobj);
			$scriptadd .= "\t\t\t$('#${nobj}').datepicker({dateFormat:'dd/mm/yy'});\n";
			$campo->size     = 12;
			$campo->calendar = false;
			$campo->maxlength= 10;
			$campo->dbformat = 'Y-m-d';
		}elseif(in_array($par['tipo'], array('integer','numeric'))){
			$campo->css_class='inputnum';
		}elseif($par['tipo']=='dropdown'){
			$campo->options($options);
		}elseif($par['tipo']=='textarea'){
			$campo->cols = 60;
			$campo->rows = 4;
		}

		return array($campo,$scriptadd);
	}
}
