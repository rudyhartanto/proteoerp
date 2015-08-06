<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ProteoERP <?php if(isset($title)) echo ': '.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?php if (isset($head))   echo $head;   ?>

<?php
	$encabeza = '<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" >'.$encabeza.'</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
	if (!isset($dockedItems)){
		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";
	}
?>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/normal.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/resources/css/ext-all.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/ux/css/CheckHeader.css"/>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/ux/grid/css/GridFilters.css"/> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/ux/grid/css/RangeMenu.css" /> 

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ext/ext-debug.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ext/locale/ext-lang-es.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ext/bootstrap.js"></script>

<script type="text/javascript">
var BASE_URL   = '<?php echo base_url(); ?>';
var BASE_PATH  = '<?php echo base_url(); ?>';
var BASE_ICONS = '<?php echo base_url(); ?>assets/icons/';
var BASE_UX    = '<?php echo base_url(); ?>assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

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

var urlApp  = '<?php echo base_url(); ?>';

<?php if (isset($script)) echo $script; ?>

</script>
<style type="text/css">
#divgrid {
	background: #e9e9e9;
	border: 1px solid #d3d3d3;
	margin: 20px;
	padding: 20px;
}

	.icon-user     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user.png') !important;}
	.icon-user-add { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_add.gif') !important;}
	.icon-save     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/save.gif') !important;}
	.icon-reset    { background-image: url('<?php echo base_url(); ?>assets/icons/fam/stop.png') !important;}
	.icon-grid     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/grid.png') !important;}
	.icon-add      { background-image: url('<?php echo base_url(); ?>assets/icons/fam/add.png') !important;}
	.icon-delete   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/delete.png') !important;}
	.icon-update   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_gray.png') !important;}
</style>

</head>
<body>
<?php if (isset($content)) echo $content; ?>
    
</body>

</html>
