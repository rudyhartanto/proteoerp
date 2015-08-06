<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $encabeza ?></title>


<!-- JQUERY -->
<?php echo style('themes/redmond/jquery-ui-1.8.18.custom.css'); ?>
<?php echo script('jquery-1.7.1.min.js') ?>
<?php echo script('jquery-ui-1.8.18.custom.min.js') ?>


<!-- JQGRID -->
<?php echo style('themes/ui.jqgrid.css') ?>
<?php echo script('i18n/grid.locale-sp.js') ?>
<?php echo script('jquery.jqGrid.min.js')  ?>

<!-- DATAGRID -->
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/default/datagrid/datagrid.css" />
<?php echo script('datagrid/datagrid.js')  ?>

<!-- LAYOUT -->
<?php echo script('jquery.layout.js') ?>

<style>
html, body {margin: 0; padding: 0; overflow: hidden; font-size: 75%;}
/*Splitter style */
#LeftPane {overflow: auto;}
#RightPane {padding: 2px;overflow: auto;}
.ui-tabs-nav li {position: relative;}
.ui-tabs-selected a span {padding-right: 10px;}
.ui-tabs-close {display: none;position: absolute;top: 3px;right: 0px;z-index: 800;width: 16px;height: 14px;font-size: 10px; font-style: normal;cursor: pointer;}
.ui-tabs-selected .ui-tabs-close {display: block;}
.ui-layout-west .ui-jqgrid tr.jqgrow td { border-bottom: 0px none;}
.ui-datepicker {z-index:1200;}
</style>

<script type="text/javascript">
var base_url = '<?php echo base_url() ?>';
var site_url = '<?php echo site_url() ?>';
var url = '';
var mGrid  = '<?php echo $grid['gridname'] ?>';
var mGrid1 = '<?php echo $grid1['gridname'] ?>';

$(document).ready(function() {
	$('body').layout({
		resizerClass: 'ui-state-default',
		west__size:	350,
        west__onresize: function (pane, $Pane) {
            jQuery("#west-grid").jqGrid('setGridWidth',$Pane.innerWidth()-2);
            jQuery("#newapi<?php echo $grid1['gridname'];?>").jqGrid('setGridWidth',$Pane.innerWidth()-2);
            jQuery("#newapi<?php echo $grid1['gridname'];?>").jqGrid('setGridHeight',$Pane.innerHeight()-105);
		},
		center__onresize: function (pane, $Pane) {
            jQuery("#newapi<?php echo $grid['gridname'];?>").jqGrid('setGridWidth',$Pane.innerWidth()-6);
            jQuery("#newapi<?php echo $grid['gridname'];?>").jqGrid('setGridHeight',$Pane.innerHeight()-105);
		}
	});

	var maintab =jQuery('#tabs','#RightPane').tabs({
        add: function(e, ui) {
            // append close thingy
            $(ui.tab).parents('li:first')
                .append('<span class="ui-tabs-close ui-icon ui-icon-close" title="Close Tab"></span>')
                .find('span.ui-tabs-close')
                .click(function() {
                    maintab.tabs('remove', $('li', maintab).index($(this).parents('li:first')[0]));
                });
            // select just added tab
            maintab.tabs('select', '#' + ui.panel.id);
        }
    });

	dtgLoadButton();
	
	var grid = jQuery("#newapi<?php echo $grid['gridname'];?>").jqGrid({
		ajaxGridOptions : {type:"POST"},
			jsonReader : {
				root:"data",
				repeatitems: false
			},
			ondblClickRow: function(id){
				var gridwidth = jQuery("#newapi<?php echo $grid['gridname'];?>").width();
				gridwidth = gridwidth/2;
				grid.editGridRow(id, {closeAfterEdit:true,mtype:'POST'});
				return;
			},
			rowList:[10,20,30],
			viewrecords: true
			<?php echo $grid['table'];?>
	})
	<?php echo $grid['pager'];?>;

	var grid1 = jQuery("#newapi<?php echo $grid1['gridname'];?>").jqGrid({
		ajaxGridOptions : {type:"POST"},
			jsonReader : {
				root:"data",
				repeatitems: false
			},
			ondblClickRow: function(id){
				var gridwidth = jQuery("#newapi<?php echo $grid1['gridname'];?>").width();
				gridwidth = gridwidth/2;
				grid1.editGridRow(id, {closeAfterEdit:true,mtype:'POST'});
				return;
			},
			rowList:[10,20,30],
			viewrecords: true
			<?php echo $grid1['table'];?>
	})
	<?php echo $grid1['pager'];?>;


	
});

</script>

<?php
	$cintu = '<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="80px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);">'.$encabeza.'</h1></td><td align="left" width="100px" nowrap="nowrap"><font style="color:#FFFFFF;font-size:12px">Usuario: '.$this->secu->usuario().'<br/>'.$this->secu->getnombre().'</font></td><td align="right" width="28px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
?>

</head>
<body id="dt_proteo">

<div class="ui-layout-north" ><?php echo $cintu ?></div>

<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
	<table id="newapi<?php echo $grid1['gridname'];?>"></table> 
	<div   id="pnewapi<?php echo $grid1['gridname'];?>"></div>
</div> <!-- #LeftPane -->


<?php 
if(isset($centerpanel) ==true) {
	echo $centerpanel;
} else{?>
<div id="RightPane" class="ui-layout-center ui-helper-reset ui-widget-content" align="center"><!-- Tabs pane -->
	<table id="newapi<?php echo $grid['gridname'];?>"></table> 
	<div   id="pnewapi<?php echo $grid['gridname'];?>"></div>
</div> <!-- #RightPane -->

<?php } ?>

<?php echo (isset($SouthPanel) == true)? $SouthPanel:''; ?>


<div id="dtg_dialog" title="Exporta Datos">
    <ul>
       <?php echo (isset($grid['export']['pdf'])   && $grid['export']['pdf']   == true)?"<li><a href='javascript:void(0);' onClick=\"dtgExport('pdf'," . $grid['querystring'] . ");\" >Pdf</a></li>":''; ?>
       <?php echo (isset($grid['export']['csv'])   && $grid['export']['csv']   == true)?"<li><a href='javascript:void(0);' onClick=\"dtgExport('csv'," . $grid['querystring'] . ");\" >Csv</a></li>":''; ?>
       <?php echo (isset($grid['export']['excel']) && $grid['export']['excel'] == true)?'<li><a href="#">Excel</a></li>':''; ?>
       <?php echo (isset($grid['export']['print']) && $grid['export']['print'] == true)?'<li><a href="#">Print</a></li>':''; ?>
       <?php echo (isset($grid['export']['xml'])   && $grid['export']['xml']   == true)?"<li><a href='javascript:void(0);' onClick=\"dtgExport('xml'," . $grid['querystring'] . ");\" >Xml</a></li>":''; ?>
    </ul>
</div>

<?php if(isset($bodyscript)) echo $bodyscript; ?>

</body>
</html>
