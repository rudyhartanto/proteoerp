<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');
function logusu($modulo,$comentario){
	if(empty($modulo) || empty($comentario)) return false;
	$CI =& get_instance();
	$usr=$CI->session->userdata('usuario');
	if(empty($usr)) $usr='#AU#';
	$dbusr       = $CI->db->escape($usr);
	$dbcomentario= $CI->db->escape($comentario);
	$dbmodulo    = $CI->db->escape($modulo);
	if(!$CI->db->field_exists('conexion', 'logusu')){
		$mSQL = "ALTER TABLE `logusu` ADD COLUMN `conexion` VARCHAR(50) NULL AFTER `comenta`";
		$CI->db->simple_query($mSQL);
	}

	if(isset($_SERVER['REMOTE_ADDR'])){
		$dbip = $CI->db->escape($_SERVER['REMOTE_ADDR']);
	}else{
		$dbip='\'\'';
	}

	$mSQL="INSERT INTO logusu (usuario,fecha,hora,modulo,comenta,conexion) VALUES (${dbusr},CURDATE(),CURTIME(),${dbmodulo},${dbcomentario},${dbip})";
	return $CI->db->simple_query($mSQL);
}

function memowrite($comentario=NULL,$nfile='salida',$modo='wb'){
	if(empty($comentario)) return false;
	$CI =& get_instance();
	$CI->load->helper('file');
	if (!write_file("./system/logs/${nfile}.log", $comentario,$modo)){
		return false;
	}
	return true;
}

function memoborra($nfile='salida'){
	return unlink("./system/logs/${nfile}.log");
}
