<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Verificador extends Controller {
	var $mModulo='Verificador';
	var $titp='Verificador de precios ';
	var $tits='Verificador de precios';
	var $url ='inventario/verificador/';
	var $genesal  = true;
	var $_creanfac= false;

	function Verificador(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	//function Sfacmovil(){
	//	parent::Controller();
	//}

	function index(){
		$data=array();
		$data['header']  = '';
		$data['content'] = $this->load->view('view_verificador', $data, true );
		$data['footer']  = '';

		$data['script']  = script('jquery-min.js'); 
		$data['script'] .= phpscript('nformat.js'); 
		$data['script'] .= script('jquery.bootgrid.min.js');
		$data['style']   = style('jquery.bootgrid.min.css');
		$data['panel']   = '';
		$data['title']   = heading('Verificador de precios');
		$this->load->view('view_ventanasjq', $data);

/*
		// Filter grid
		$this->rapyd->load('datafilter','datagrid');
		$filter = new DataFilter('Consulta de Precios', 'sinv');

		$filter->Descripcion = new inputField('Descripcion','decrip');
		$filter->clave->rule      ='max_length[50]';
		$filter->clave->size      =7;
		$filter->clave->maxlength =5;

		$filter->marca = new inputField('Marca','marca');
		$filter->marca->rule      ='max_length[30]';
		$filter->marca->size      =32;
		$filter->marca->maxlength =30;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->db->select('codigo', 'descrip','unidad','base1','precio1-base1 iva','precio1');
		$grid->db->order_by('codigo');

		$grid->db->order_by('codigo');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo'   ,'codigo'  ,'codigo',  'align="left"');
		$grid->column_orderby('Descripcion'     ,'nombre'  ,'descrip', 'align="left"');
		$grid->column_orderby('Medida'          ,'unidad'  ,'unidad',  'align="left"');
		$grid->column_orderby('Precio'          ,'base1'   ,'base1',   'align="right"');
		$grid->column_orderby('I.V.A.'          ,'iva'     ,'iva',     'align="right"');
		$grid->column_orderby('Precio de Venta' ,'precio1' ,'precio1', 'align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.min.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
*/
	}


	function buscasinv(){
		session_write_close();
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('searchPhrase');
		$pagina = $this->input->post('current');
		$limite = $this->input->post('rowCount');
		
		$desde = ($pagina-1)*$limite;
		$vnega  = trim(strtoupper($this->datasis->traevalor('VENTANEGATIVA')));

		if($mid == false) $mid = $this->input->post('term');

		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[]';
		if($mid == false) $mid = 'A';

		if($mid !== false){

			if(strlen($mid)>=4){
				//$fulltext= " OR MATCH(a.descrip) AGAINST (${qba})";
				$fulltext= "WHERE (a.codigo LIKE ${qdb} OR a.barras LIKE ${qdb} OR a.alterno LIKE ${qdb} OR b.suplemen=${qba} OR MATCH(a.descrip) AGAINST (${qba})) ";

			}else{
				//$fulltext= " a.descrip LIKE ${qdb}";
				$fulltext= "WHERE a.descrip LIKE ${qdb} ";
			}

			$mSQL = "
			SELECT DISTINCT TRIM(a.descrip) descrip, TRIM(a.codigo) codigo, a.marca, a.ubica, a.unidad,
			a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo,a.peso, a.ultimo, a.pond, a.barras, 0 AS descufijo, c.margen AS dgrupo,0 AS promo, COALESCE(e.existen,0) existen,a.id
			,g.sinv_id
			FROM sinv AS a
			JOIN itsinv         AS e ON a.codigo=e.codigo
			JOIN caub           AS f ON e.alma = f.ubica AND f.tipo='S'
			LEFT JOIN barraspos AS b ON a.codigo=b.codigo
			LEFT JOIN grup      AS c ON a.grupo=c.grupo
			LEFT JOIN sinvfot   AS g ON a.id=g.sinv_id ${fulltext} ";

			$mSQL .= "
			AND a.activo='S'
			AND e.existen>0
			AND MID(a.tipo,1,1)='A'
			GROUP BY a.codigo
			ORDER BY a.descrip LIMIT ${desde}, ${limite}";

			$retArray = $retorno = array();
			$cana=1;

/*
[
	{"codigo":"TABAI24","base1":1325.89,"descrip":"ABRAZADERA ACERO INOXIDABLE 1.1\/4-2\"","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},
	{"codigo":"TABAI06","base1":550,    "descrip":"ABRAZADERA ACERO INOXIDABLE 1\/2-3\/4\"","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},
	{"codigo":"TABAI6","base1":1455.64,"descrip":"ABRAZADERA ACERO INOXIDABLE 1\/2-3\/4\"","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},
	{"codigo":"TABAI10","base1":1489.29,"descrip":"ABRAZADERA ACERO INOXIDABLE 3\/4-1.1\/16","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},{"codigo":"TABAI16","base1":1125.89,"descrip":"ABRAZADERA ACERO INOXIDABLE 3\/4-1.1\/2","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},{"codigo":"TABAI04","base1":727.68,"descrip":"ABRAZADERA ACERO INOXIDABLE 3\/8-5\/8\"","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},{"codigo":"TABAI4","base1":1000.89,"descrip":"ABRAZADERA ACERO INOXIDABLE 3\/8-5\/8\"","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},{"codigo":"TABAI8","base1":1050.89,"descrip":"ABRAZADERA ACERO INOXIDABLE 5\/8-7\/8","iva":"12.00","marca":"TRUPER","unidad":"BLISTER","foto":"N"},{"codigo":"AAA","base1":17.86,"descrip":"ABRAZADERA P\/CERCA ALFAJOL","iva":"12.00","marca":"OTRAS","unidad":"UNIDAD","foto":"N"},{"codigo":"ABEMT04","base1":1.95,"descrip":"ABRAZADERA TUBO EMT 1 1\/2\"  UNA PATA","iva":"12.00","marca":"OTRAS","unidad":"UNIDAD","foto":"N"}
]




    {
    "current": 1,
    "rowCount": 10,
    "rows": [{
			"id": 19,
			"sender": "123@test.de",
			"received": "2014-05-30T22:15:00"
		},{
			"id": 14,
			"sender": "123@test.de",
			"received": "2014-05-30T20:15:00"
		},
    ...
    ],
    "total": 1123   }

 
*/

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					if($row['descufijo']>0){
						$descufijo=$row['descufijo']/100;
					}elseif($row['promo']>0){
						$descufijo=$row['promo']/100;
					}elseif($row['dgrupo']>0){
						$descufijo=$row['dgrupo']/100;
					}else{
						$descufijo = 0;
					}
					if($descufijo>1) $descufijo = 0;
					//$retArray['label']   = '('.$row['codigo'].')'.$this->en_utf8($row['descrip']).' Bs.'.$row['precio1'].'  '.$row['existen'].'';
					//$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					//$retArray['cana']    = $cana;
					//$retArray['tipo']    = $row['tipo'];
					//$retArray['peso']    = $row['peso'];
					//$retArray['ultimo']  = $row['ultimo'];
					//$retArray['pond']    = $row['pond'];
					$retArray['base1']   = round(($row['precio1']*100/(100+$row['iva']))*(1-$descufijo),2);
					//$retArray['base2']   = round($row['precio2']*100/(100+$row['iva']),2);
					//$retArray['base3']   = round($row['precio3']*100/(100+$row['iva']),2);
					//$retArray['base4']   = round($row['precio4']*100/(100+$row['iva']),2);
					$retArray['descrip'] = $this->en_utf8($row['descrip']);
					//$retArray['barras']  = $row['barras'];
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					//$retArray['existen'] = (empty($row['existen']))? 0 : round($row['existen'],2);
					$retArray['marca']   = $row['marca'];
					//$retArray['ubica']   = $row['ubica'];
					$retArray['unidad']  = $row['unidad'];
					//$retArray['id']      = intval($row['id']);
					$retArray['precio1']   = $row['precio1'];
					if(empty($row['sinv_id'])){
						$retArray['foto']='N';
					}else{
						$retArray['foto']='S';
					}
					array_push($retorno, $retArray);

				}
				$data = json_encode($retorno);
				$data = '{"current": 1, "rowCount": 10, "rows":'.$data.', "total": 1123 }';
			}
		}
		echo $data;
	}

	function en_utf8($str){
		if($this->config->item('charset')=='UTF-8' && $this->db->char_set=='latin1'){
			return utf8_encode($str);
		}else{
			return $str;
		}
	}
}
