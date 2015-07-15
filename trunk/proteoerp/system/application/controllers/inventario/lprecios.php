<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Lprecios extends Controller {
	var $mModulo='Lprecios';
	var $titp='Consulta de precios ';
	var $tits='Consulta de precios';
	var $url ='inventario/lprecios/';
	var $genesal  = true;
	var $_creanfac= false;

	function Sfacmovil(){
		parent::Controller();
	}

	function index(){
		$data=array();
		$data['header']  = '';
		$data['content'] = $this->load->view('view_lprecios', $data,true);
		$data['footer']  = '';
		$data['script']  = '';
		$data['panel']   = '';
		$data['title']   = heading('Consulta de precios');
		$this->load->view('view_ventanasjqm', $data);
	}


	function buscasinv(){
		session_write_close();
		$comodin= $this->datasis->traevalor('COMODIN');
		$mid    = $this->input->post('q');
		$vnega  = trim(strtoupper($this->datasis->traevalor('VENTANEGATIVA')));

		if($mid == false) $mid = $this->input->post('term');

		if(strlen($comodin)==1 && $comodin!='%' && $mid!==false){
			$mid=str_replace($comodin,'%',$mid);
		}
		$qdb  = $this->db->escape($mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '[]';
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
			ORDER BY a.descrip LIMIT 100";

			$retArray = $retorno = array();
			$cana=1;

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

					$retArray['label']   = '('.$row['codigo'].')'.$this->en_utf8($row['descrip']).' Bs.'.$row['precio1'].'  '.$row['existen'].'';
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = round(($row['precio1']*100/(100+$row['iva']))*(1-$descufijo),2);
					$retArray['base2']   = round($row['precio2']*100/(100+$row['iva']),2);
					$retArray['base3']   = round($row['precio3']*100/(100+$row['iva']),2);
					$retArray['base4']   = round($row['precio4']*100/(100+$row['iva']),2);
					$retArray['descrip'] = $this->en_utf8($row['descrip']);
					$retArray['barras']  = $row['barras'];
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					$retArray['existen'] = (empty($row['existen']))? 0 : round($row['existen'],2);
					$retArray['marca']   = $row['marca'];
					$retArray['ubica']   = $row['ubica'];
					$retArray['unidad']  = $row['unidad'];
					$retArray['id']      = intval($row['id']);
					if(empty($row['sinv_id'])){
						$retArray['foto']='N';
					}else{
						$retArray['foto']='S';
					}

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
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
