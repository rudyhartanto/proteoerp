<?php
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$tipo_rete=$this->datasis->traevalor('CONTRIBUYENTE');

$ccampos=$form->detail_fields['gitser'];
$campos='<tr id="tr_gitser_<#i#>">';
$campos.=' <td class="littletablerow">'.$ccampos['codigo']['field'].'</td>';
$campos.=' <td class="littletablerow">'.$ccampos['descrip']['field'].'</td>';
$campos.=' <td class="littletablerow" align="right">'.$ccampos['precio']['field'].'</td>';
$campos.=' <td class="littletablerow" align="right">'.$ccampos['tasaiva']['field'].'</td>';
$campos.=' <td class="littletablerow" align="right">'.$ccampos['iva']['field'].'</td>';
$campos.=' <td class="littletablerow" align="right">'.$ccampos['importe']['field'].'</td>';
$campos.=' <td class="littletablerow">'.$ccampos['departa']['field'].'</td>';
$campos.=' <td class="littletablerow">'.$ccampos['sucursal']['field'].'</td>';
$campos.=' <td class="littletablerow" align="center"><a href=\'#\' onclick="del_gitser(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos=$form->js_escape($campos);


//foreach($form->detail_fields['gereten'] AS $ind=>$data){
//	if(!empty($data['field'])){
//		$ggereten[]=$data['field'];
//	}
//}


$ccampos=$form->detail_fields['gereten'];
$cgereten ='<tr id="tr_gereten_<#i#>">';
//$cgereten.=' <td class="littletablerow">'.join('</td><td align="right">',$ggereten).'</td>';
$cgereten.=' <td class="littletablerow" nowrap>       '.$ccampos['codigorete']['field'].'</td>';
$cgereten.=' <td class="littletablerow" align="right">'.$ccampos['base']['field']      .'</td>';
$cgereten.=' <td class="littletablerow" align="right">'.$ccampos['porcen']['field']    .'</td>';
$cgereten.=' <td class="littletablerow" align="right">'.$ccampos['monto']['field']     .'</td>';
$cgereten.=' <td class="littletablerow" align="center"><a href=\'#\' onclick="del_gereten(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$cgereten=$form->js_escape($cgereten);

$rete=array();
$mSQL='SELECT TRIM(codigo) AS codigo,TRIM(CONCAT_WS("-",codigo,activida)) AS activida ,base1,tari1,pama1,TRIM(tipo) AS tipo FROM rete ORDER BY codigo';
$query = $this->db->query($mSQL);
if ($query->num_rows() > 0){
	foreach ($query->result() as $row){
		$ind='_'.$row->codigo;
		$rete[$ind]=array($row->activida,$row->base1,$row->tari1,$row->pama1,$row->tipo);
	}
}
$json_rete=json_encode($rete);

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){

$sql='SELECT TRIM(a.codbanc) AS codbanc,tbanco FROM banc AS a';
$query = $this->db->query($sql);
$comis=array();
if ($query->num_rows() > 0){
	foreach ($query->result() as $row){
		$ind='_'.$row->codbanc;
		$comis[$ind]['tbanco']  =$row->tbanco;
	}
}
$json_comis=json_encode($comis);
?>

<script language="javascript" type="text/javascript">
var gitser_cont =<?php echo $form->max_rel_count['gitser']; ?>;
var gereten_cont=<?php echo $form->max_rel_count['gereten'];?>;

var departa  = '';
var sucursal = '';
var comis    = <?php echo $json_comis; ?>;
var rete     = <?php echo $json_rete;  ?>;

$(document).ready(function() {
	$('#_rivaex').change(function () {
		totalizar();
	});
	$(".inputnum").numeric(".");
	totalizar();

	$( "#fecha" ).datepicker({    dateFormat: "dd/mm/yy" });
	$( "#ffactura" ).datepicker({ dateFormat: "dd/mm/yy" });
	$( "#vence" ).datepicker({    dateFormat: "dd/mm/yy" });


	codb1=$('#codb1').val();
	desactivacampo(codb1);
	autocod(0);
	$('#proveed').autocomplete({
		delay: 600,
		autoFocus: true,
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasprv'); ?>",
				type: "POST",
				dataType: "json",
				data: {"q":req.term},
				success:
					function(data){
						var sugiere = [];
						if(data.length==0){
							$('#nombre').val('');
							$('#nombre_val').text('');
							$('#proveed').val('');
						}else{
							$.each(data,
								function(i, val){
									sugiere.push( val );
								}
							);
						}
						add(sugiere);
					},
			})
		},
		minLength: 2,
		select: function( event, ui ) {
			$('#proveed').attr("readonly", "readonly");

			$('#nombre').val(ui.item.nombre);
			$('#nombre_val').text(ui.item.nombre);
			$('#proveed').val(ui.item.proveed);

			setTimeout(function(){ $('#proveed').removeAttr("readonly"); }, 1500);
		}
	});
});

function post_sprv_modbus(){
	nombre=$('#nombre').val();
	$('#nombre_val').text(nombre);
	totalizar();
}

function calcularete(){
	codigos=$('input[name^="codigo_"]');
	precios=$('input[name^="precio_"]');
	proveed=$('#proveed');
	parr=$.param(codigos)+'&'+$.param(precios)+'&'+$.param(proveed);

	$.ajax({
		type: "POST",
		url: "<?php echo site_url('finanzas/gser/calcularete'); ?>",
		dataType: 'json',
		data: parr,
		success: function(cont){
			truncate_gereten();
			i=0;
			if(!cont){
				alert('Retenciones no Aplican');
				return false;
			}
			jQuery.each(cont, function() {
				add_gereten();
				si=i.toString()

				$('#codigorete_'+si).val(this.codigo);
				$('#base_'+si).val(this.base);
				$('#porcen_'+si).val(this.porcen);
				$('#porcen_'+si+'_val').text(nformat(this.porcen,2));
				$('#monto_'+si).val(this.monto);
				$('#monto_'+si+'_val').text(nformat(this.monto,2));
				totalizar();

				i+=1;
			});
		}
	});
}

function truncate_gereten(){
	$('tr[id^="tr_gereten_"]').remove();
	gereten_cont=0;
}

function valida(i){
	alert("Este monto no puede ser modificado manualmente");
	totalizar(i);
}

//Para que el proximo registro tenga el mismo departamento
function gdeparta(val){
	departa=val;
}

//Para que el proximo registro tenga la misma sucursal
function gsucursal(val){
	sucursal=val;
}

//Calcula la retencion del iva
function reteiva(){
	<?php
	$rif = trim($this->datasis->traevalor('RIF'));
	if($tipo_rete=='ESPECIAL' && strtoupper($rif[0])!='V'){ ?>

		if($('#_rivaex').attr('checked')){
			$("#reteiva").val(0.0);
			return 0.0;
		}

		reteval= Number($("#reteiva").val());
		totiva = Number($("#totiva").val());
		preten = Number($("#sprvreteiva").val());
		if(totiva!=0 && reteval-totiva==0){
			$("#sprvreteiva").val(100);
			riva=totiva;
		}else{
			riva = Math.round((totiva*preten).toFixed(2));
			riva = riva/100;
			$("#reteiva").val(riva);
		}
		return riva;
	<?php }else{?>
		$("#reteiva").val(0.0);
		return 0.0;
	<?php } ?>
}

function importe(i){
	ind    = i.toString();
	precio = Number($("#precio_"+ind).val());
	iva    = Number($("#tasaiva_"+ind).val());
	miva   = precio*iva/100;
	impor  = precio+miva;
	$("#iva_"+ind).val(miva);
	$("#importe_"+ind).val(roundNumber(impor,2));
	$("#iva_"+ind+"_val").text(nformat(miva,2));
	$("#importe_"+ind+"_val").text(nformat(impor,2));
	totalizar();
}

function totalizar(){
	tp=tb=ti=ite=0;

	arr=$('input[name^="importe_"]');
	jQuery.each(arr, function() {
		nom=this.name
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind = this.name.substring(pos+1);
			tp1=Number($("#precio_"+ind).val());
			ite=Number(this.value);

			tp=tp+tp1;
			tb=tb+ite;
		}
	});

	$("#totpre").val(roundNumber(tp,2));
	$("#totpre_val").text(nformat(tp,2));
	$("#totbruto").val(roundNumber(tb,2));
	$("#totbruto_val").text(nformat(tb,2));
	totiva=roundNumber(tb-tp,2);
	$("#totiva").val(totiva);
	$("#totiva_val").text(nformat(totiva,2));
	var reten=totalrete();
	var riva =reteiva();
	totneto=roundNumber(tb-riva-reten,2);
	$("#totneto").val(totneto);
	$("#totneto_val").text(nformat(totneto));
	monto1=Number($("#monto1").val());
	$("#credito").val(roundNumber(totneto-monto1,2));
}

function ccredito(){
	credito =Number($("#credito").val());
	montonet=Number($("#totneto").val());
	$("#monto1").val(roundNumber(montonet-credito,2));
}

function contado(){
	monto1  =Number($("#monto1").val());
	montonet=Number($("#totneto").val());
	$("#credito").val(roundNumber(montonet-monto1,2));
}

function esbancaja(codb1){
	if(codb1.length>0){
		desactivacampo(codb1);
		montonet=Number($("#totneto").val());
		$("#credito").val(0);
		$("#monto1").val(roundNumber(montonet,2));
	}
}

function desactivacampo(codb1){
	if(codb1.length>0){
		eval("tbanco=comis._"+codb1+".tbanco;"  );
		if(tbanco=='CAJ'){
			$("#tipo1").val('D');
			$('#tipo1').attr('readonly','readonly');
			$('#cheque1').attr('disabled','disabled');
		}else{
			$('#tipo1').attr('readonly',false);
			$('#cheque1').removeAttr('disabled');
		}
	}
}

function add_gitser(){
	var htm = <?php echo $campos; ?>;
	can = gitser_cont.toString();
	con = (gitser_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#departa_"+can).val(departa);
	$("#sucursal_"+can).val(sucursal);
	autocod(gitser_cont);
	gitser_cont=gitser_cont+1;
}

function importerete(nind){
	var ind=nind.toString();
	var codigo  = $("#codigorete_"+ind).val();
	if(codigo.length>0){
		//var tari1   = Number($("#porcen_"+ind).val());
		var importe = Number($("#base_"+ind).val());
		var base1   = Number(eval('rete._'+codigo+'[1]'));
		var tari1   = Number(eval('rete._'+codigo+'[2]'));
		var pama1   = Number(eval('rete._'+codigo+'[3]'));

		var tt=codigo.substring(0,1);
		if(tt=='1')
			monto=(importe*base1*tari1)/10000;
		else if(importe>pama1)
			monto=((importe-pama1)*base1*tari1)/10000;
		else
			monto = 0;

		$("#monto_"+ind).val(roundNumber(monto,2));
		$("#monto_"+ind+'_val').text(nformat(monto,2));
	}
	totalizar();
}

function totalrete(){
	monto=0;
	arr  =$('input[name^="monto_"]');
	jQuery.each(arr, function() {
		monto=monto+Number(this.value);
	});
	$("#reten").val(monto);
	$("#reten_val").text(nformat(monto,2));
	return monto;
}

function post_codigoreteselec(nind,cod){
	var ind=nind.toString();
	var porcen=eval('rete._'+cod+'[2]');
	var base1 =eval('rete._'+cod+'[1]');
	$("#porcen_"+ind).val(porcen);
	$("#porcen_"+ind+"_val").text(nformat(porcen,2));
	importerete(nind);
}

function add_gereten(){
	var htm = <?php echo $cgereten; ?>;
	var can = gereten_cont.toString();
	var con = (gereten_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__gereten").before(htm);
	gereten_cont=gereten_cont+1;
}

function del_gereten(id){
	id = id.toString();
	obj='#tr_gereten_'+id;
	$(obj).remove();
	totalizar();
}

function del_gitser(id){
	id = id.toString();
	obj='#tr_gitser_'+id;
	$(obj).remove();
	totalizar();
}

//Agrega el autocomplete
function autocod(id){
	$('#codigo_'+id).autocomplete({
		delay: 600,
		autoFocus: true,
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/automgas'); ?>",
				type: "POST",
				dataType: "json",
				data: {"q" :req.term},
				success:
					function(data){
						var sugiere = [];

						if(data.length==0){
							$('#codigo_'+id).val('');
							$('#descrip_'+id).val('');
						}else{
							$.each(data,
								function(i, val){
									sugiere.push( val );
								}
							);
						}
						add(sugiere);
					},
			})
		},
		minLength: 1,
		select: function( event, ui ) {
			$('#codigo_'+id).attr("readonly", "readonly");

			$('#codigo_'+id).val(ui.item.codigo);
			$('#descrip_'+id).val(ui.item.descrip);
			$('#precio_'+id).focus();
			setTimeout(function() {  $('#codigo_'+id).removeAttr("readonly"); }, 1500);
		}
	});
}
</script>
<?php } else { ?>
<script language="javascript" type="text/javascript">
function toggle() {
	var ele = document.getElementById("asociados");
	var text = document.getElementById("mostrasocio");
	if(ele.style.display == "block") {
		ele.style.display = "none";
		text.innerHTML = "Mostrar Complementos ";
	}
	else {
		ele.style.display = "block";
		text.innerHTML = "Ocultar Complementos";
	}
}
</script>
<?php } ?>

<table align='center' width="99%">
<?php if (!$solo){?>
	<tr>
		<td align='right'><?php echo $container_tr?></td>
	</tr>
<?php } ?>
	<tr>
		<td><div class="alert"> <?php if(isset($form->error_string)) echo $form->error_string; ?></div></td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 1px outset #9AC8DA;background: #FFFDE9;'>
		<!-- <legend class="titulofieldset" style='color: #114411;'>Documento</legend> -->
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td width='90' class="littletableheader"><?php echo $form->tipo_doc->label  ?>*&nbsp;</td>
				<td width='115' class="littletablerow">   <?php echo $form->tipo_doc->output ?>&nbsp; </td>
				<td width='90' class="littletableheader"><?php echo $form->proveed->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->proveed->output.$form->sprvtipo->output.$form->sprvreteiva->output  ?>&nbsp; </td>
				<td width='70'  class="littletableheader"><?php echo $form->ffactura->label  ?>*&nbsp;</td>
				<td width='130' class="littletablerow">   <?php echo $form->ffactura->output ?>&nbsp; </td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->numero->label  ?>*</td>
				<td class="littletablerow">   <?php echo $form->numero->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->nombre->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nombre->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->fecha->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output  ?>&nbsp; </td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->nfiscal->label  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nfiscal->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->compra->label   ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->compra->output  ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->vence->label    ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->vence->output   ?>&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		</td>
	</tr>
	<tr>
		<td>
<?php if( !$solo) {?>
		<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
		<legend class="titulofieldset" style='color: #114411;'>Detalle</legend>
<?php } else { ?>
		<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:160px'>
<?php } ?>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet">C&oacute;digo</td>
				<td class="littletableheaderdet">Descripci&oacute;n del Gasto</td>
				<td class="littletableheaderdet" align="right">Precio</td>
				<td class="littletableheaderdet" align="right">Tasa</td>
				<td class="littletableheaderdet" align="right">IVA</td>
				<td class="littletableheaderdet" align="right">Importe</td>
				<td class="littletableheaderdet">Depto.</td>
				<td class="littletableheaderdet">Sucursal</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0; $i < $form->max_rel_count['gitser']; $i++) {
				$obj1 ="codigo_$i";
				$obj2 ="descrip_$i";
				$obj3 ="precio_$i";
				$obj4 ="iva_$i";
				$obj5 ="importe_$i";
				$obj7 ="departa_$i";
				$obj8 ="sucursal_$i";
				$obj11="tasaiva_$i";

				if($form->_status=='show'){
					$ivaval=nformat(round($form->$obj4->value/$form->$obj3->value,2)*100,2);
				}else{
					$ivaval=$form->$obj11->output;
				}
			?>
			<tr id='tr_gitser_<?=$i ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$obj1->output ?></td>
				<td class="littletablerow">       <?php echo $form->$obj2->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj3->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $ivaval ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj4->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj5->output  ?></td>
				<td class="littletablerow"><?php echo $form->$obj7->output  ?></td>
				<td class="littletablerow"><?php echo $form->$obj8->output  ?></td>

				<?php if($form->_status!='show') {?>
					<td class="littletablerow" align="center"><a href='#' onclick='del_gitser(<?php echo $i; ?>);return false;'><?php echo img("images/delete.jpg"); ?></a></td>
				<?php } ?>
			</tr>
			<?php if( $form->_status == 'show') {?>
				<?php if( $form->_dataobject->get('cajachi') == 'S' ) { ?>
			<tr id='tr_gitser_D<?=$i ?>'>
				<td style='font-size:11px;background: #DFEFFF;font-weight: bold;' colspan=8>
				<?php
					echo '* '.$form->_dataobject->get_rel('gitser','rif',$i);
					echo ' '.$form->_dataobject->get_rel('gitser','proveedor',$i);
					echo ' Factura: '.$form->_dataobject->get_rel('gitser','numfac',$i);
					echo ' Fecha: '.dbdate_to_human($form->_dataobject->get_rel('gitser','fechafac',$i));
				?>
				</td>
			</tr>
				<?php } // caja chica  ?>
			<?php } // SHOW ?>
			<?php } ?>

			<tr id='__UTPL__'>
				<td colspan='9' class="littletablerow">&nbsp;</td>
			</tr>
		</table>
<?php if( !$solo) {?>
		</fieldset>
<?php } else { ?>
		</div>
<?php } ?>
		<table width='100%' border='0' cellpadding='0' cellspacing='0'>
			<tr style="background:#DFDFDF;font-size:12px;font-weight:bold">
		<?php if( $form->_status != 'show') {?>
			<td width="90"><input name="btn_add_gitser" value="Agregar Gasto" onclick="add_gitser()" class="button" type="button"></td>
		<?php } else { ?>
			<td width="90">&nbsp;</td>
		<?php } ?>
			<td align="right"><b>Totales Base:</b></td>
			<td align='right'><?php echo $form->totpre->output ?>&nbsp;</td>
			<td align="right"><b>Total I.V.A.:</b></td>
			<td align='right'><?php echo $form->totiva->output  ?>&nbsp;</td>
			<td align="right"><b>Total:</b></td>
			<td align='right'><?php echo $form->totbruto->output ?>&nbsp;</td>
		</table>
		</td>

	</tr>

	<?php if ($form->max_rel_count['gereten']>0); ?>
	<tr>
		<td>
		<table width='100%' border='0' cellpadding='0' cellspacing='0' ><tr>
		<td>
<?php if( !$solo) {?>
		<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
		<legend class="titulofieldset" style='color: #114411;'>Retenciones</legend>
<?php } ?>
		<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:80px'>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet">Retencion</td>
				<td class="littletableheaderdet">Base</td>
				<td class="littletableheaderdet" align="right">Porcentaje</td>
				<td class="littletableheaderdet" align="right">Monto</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0; $i < $form->max_rel_count['gereten']; $i++) {
				$it_codigorete= "codigorete_$i";
				//$it_actividad = "actividad_$i";
				$it_base      = "base_$i";
				$it_porcen    = "porcen_$i";
				$it_monto     = "monto_$i";
			?>
			<tr id='tr_gereten_<?php echo $i; ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$it_codigorete->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_base->output      ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_porcen->output    ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_monto->output     ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow" align="center"><a href='#' onclick='del_gereten(<?php echo $i; ?>);return false;'><?php echo img("images/delete.jpg"); ?></a></td>
				<?php }
			}?>
			</tr>
			<tr id='__UTPL__gereten'>
				<td colspan='9'>&nbsp;</td>
			</tr>
		</table>
		</div>
<?php if( !$solo) {?>
		</fieldset>
<?php } ?>
		</td>
		<td width="100" align='center' style="background:#FFFFFF;" valign='top'>
			<table width='100%'>
		<?php if( $form->_status != 'show') {?>
			<tr><td align='right'><input name="btn_add_gereten" value="Agregar" onclick="add_gereten()" class="button" type="button"></td></tr>
			<tr><td align='right'><input name="btn_creten"      value="Calcular" onclick="calcularete()" class="button" type="button"></td></tr>
			<tr><td align="right" style="font-size:12px;font-weight:bold;background:#EFEFEF">&nbsp;<?php echo $form->reten->output  ?></td></tr>
		<?php } ?>
			</table>
		</td>
		</tr>
		</table>
<?php if( !$solo) {?>
		<?php if( $form->_status != 'show') {?>
			<input name="btn_add_gereten" value="Agregar" onclick="add_gereten()" class="button" type="button">
			<input name="btn_creten"      value="Calcular" onclick="calcularete()" class="button" type="button">
		<?php } ?>
<?php } ?>

		<?php echo $form_end     ?>
		</td>
	</tr>
	<tr>
		<td align='center'>
			<table width='100%'><tr><td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Forma de Pago</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheader"><?php echo $form->codb1->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->codb1->output  ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->tipo1->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->tipo1->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->cheque1->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->cheque1->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->monto1->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->monto1->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->benefi->label   ?>&nbsp;</td>
					<td colspan='3' class="littletablerow">   <?php echo $form->benefi->output  ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
			</td><td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Totales</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheader">           <?php echo $form->reteiva->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php
						if($form->_status!='show'){
							echo '<input type="checkbox" name="_rivaex" id="_rivaex" value="S"> Exonerar';
						}
						echo $form->reteiva->output;
					?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader">           <?php echo $form->credito->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->credito->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader">           <?php echo $form->totneto->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->totneto->output ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
			</td></tr></table>
		</td>
	</tr>

	<?php if($form->_status == 'show'){ ?>
	<tr>
		<td>
			<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Informaci&oacute;n del Registro</legend>
			<table width='100%' cellspacing='1' >
				<tr style='font-size:12px;color:#0B3B0B;background-color: #F7BE81;'>
					<td align='center' >Usuario</td>
					<td align='center' >Nombre </td>
					<td align='center' >Fecha  </td>
					<td align='center' >Hora   </td>
					<td align='center' >Transacci&oacute;n</td>
				</tr>
				<tr>
					<?php
						$mSQL="SELECT us_nombre FROM usuario WHERE us_codigo='".trim($form->_dataobject->get('usuario'))."'";
						$us_nombre = $this->datasis->dameval($mSQL);

					?>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('usuario'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $us_nombre ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo dbdate_to_human($form->_dataobject->get('estampa')); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('hora'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('transac'); ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>

	<tr>
		<td align='center'>
			<a id="mostrasocio" href="javascript:toggle();">Mostrar Complementos</a>
			<div id='asociados' style='display: none'>
				<?php
					$mSQL = "SELECT periodo, nrocomp, emision, impuesto, reiva, round(reiva*100/impuesto,0) porcent FROM riva WHERE transac=? LIMIT 1";
					$query = $this->db->query($mSQL, array(TRIM($form->_dataobject->get('transac'))) );
					if ( $query->num_rows() > 0 ) {
						$row = $query->row();
				?>
			<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Retencion de Impuesto</legend>
			<table width='100%' cellspacing='1' >
				<tr style='font-size:12px;color:#FFEEFF;background-color: #393B0B;'>
					<td align='center'>Periodo &nbsp;</td>
					<td align='center'>Numero &nbsp;</td>
					<td align='center'>Emisi&oacute;n &nbsp;</td>
					<td align='center'>Impuesto &nbsp;</td>
					<td align='center'>Monto &nbsp;</td>
					<td align='center'>% &nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow" align='center'><?php echo $row->periodo ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $row->nrocomp ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo dbdate_to_human($row->emision) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->impuesto) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->reiva) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->porcent) ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
				<?php }; ?>
				<?php
					$mSQL = "SELECT CONCAT(tipo_op, numero) numero, CONCAT(codbanc,'-', banco) codbanc, monto, concepto FROM bmov WHERE transac=? LIMIT 1";
					$query = $this->db->query($mSQL, array(trim($form->_dataobject->get('transac'))) );
					if ( $query->num_rows() > 0 ) {
						$row = $query->row(); ?>
			<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Registro en Bancos</legend>
			<table width='100%' cellspacing='1'>
				<tr>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Numero&nbsp;</td>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Caja/Banco&nbsp;</td>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Monto &nbsp;</td>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Concepto &nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow" align='center'><?php echo $row->numero ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $row->codbanc ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->monto) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $row->concepto ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
				<?php }; ?>

			<?php
				$mSQL = "SELECT CONCAT(tipo_doc, numero) numero, CONCAT(cod_prv,'-',nombre) cod_prv, monto*(tipo_doc IN ('FC','ND','GI')) debe, monto*(tipo_doc NOT IN ('FC','ND','GI')) haber , monto-abonos saldo FROM sprm WHERE transac=? ";
				$query = $this->db->query($mSQL, array(trim($form->_dataobject->get('transac'))) );
				if ( $query->num_rows() > 0 ) { ?>
			<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Estado de Cuenta</legend>
			<table width='100%' cellspacing='1'>
				<tr style='font-size:12px;color:#FFEEFF;background-color: #61380B;'>
					<td align='center'>Numero &nbsp;</td>
					<td align='center'>Proveedor &nbsp;</td>
					<td align='center'>Debe &nbsp;</td>
					<td align='center'>Haber &nbsp;</td>
					<td align='center'>Saldo &nbsp;</td>
				</tr>
						<?php foreach( $query->result() as $row ){ ?>
				<tr>

					<td class="littletablerow" align='center'><?php echo $row->numero ?>&nbsp;</td>
					<td class="littletablerow" align='left'>  <?php echo $row->cod_prv ?>&nbsp;</td>
					<td class="littletablerow" align='right'> <?php echo nformat($row->debe) ?>&nbsp;</td>
					<td class="littletablerow" align='right'> <?php echo nformat($row->haber) ?>&nbsp;</td>
					<td class="littletablerow" align='right'> <?php echo nformat($row->saldo) ?>&nbsp;</td>
				</tr>
						<?php }; ?>
			</fieldset>
			</table>
				<?php }; ?>
			</div>
		</td>
	</tr>
	<?php } ?>
</table>
<?php endif; ?>