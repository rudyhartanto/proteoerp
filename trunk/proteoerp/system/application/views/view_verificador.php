<table width='100%1'>
	<tr>
		<td width='50%'>
			<div style='text-align:center;font-weight:bold;font-size:38pt;color:#004B2C;'>CONSULTE PRECIOS AQUI!</div>			
		</td><td style='height:150px;background:url("<?php echo site_url('images/barcode-label.png'); ?>") no-repeat right;'>
			&nbsp;
		</td>
	</tr>
</table>

<div data-role="content">

	<!--form class="ui-filterable">
		<table width='100%'>
		<tr>
			<td>
				<label>Criterio de Busqueda, ingrese una palabra descrptiva de lo que desea buscar</label>
			</td>
			<td align='center'>
				<label>Filtro de Marcas</label>
			</td>
			<td align='center'>
				<label>Filtro por letra inicial</label>
			</td>
		</tr><tr>
			<td>
				<input id="autocomplete-input" data-type="search" placeholder="Buscar art&iacute;culo..." style='width:200px;'>
				<ul id="autocomplete" data-role="listview" data-inset="true" data-filter="false" data-input="#autocomplete-input"></ul>
			</td>
			<td>
				<?php echo $this->datasis->llenaopciones('SELECT marca, marca FROM marc ORDER BY marca;', true, $id='marca' );?>
			</td>
			<td align='center'>
				<a>A</a>
				<a>B</a>
				<a>C</a>
				<a>D</a>
				<a>E</a>
				<a>F</a>
				<a>G</a>
				<a>H</a>
				<a>I</a>
				<a>J</a>
				<a>K</a>
				<a>L</a>
				<a>M</a>
				<a>N</a>
				<a>O</a>
				<a>P</a>
				<a>Q</a>
				<a>R</a>
				<a>S</a>
				<a>T</a>
				<a>Y</a>
				<a>U</a>
				<a>V</a>
				<a>W</a>
				<a>X</a>
				<a>Y</a>
				<a>Z</a>
			</td>
		</table>
	</form -->

	<table width='100%' class="table table-condensed table-hover table-striped">
		<tr>
			<td width='80%'>
			<table id='tabladata'>
				<thead>
				<tr>
					<th data-column-id="codigo"  >C&oacute;digo</th>
					<th data-column-id="descrip" >Descripci&oacute;n</th>
					<th data-column-id="unidad"  >Medida</th>
					<th data-column-id="base1"   data-type="numeric">Precio</th>
					<th data-column-id="iva"     data-type="numeric">I.V.A.</th>
					<th data-column-id="precio1" data-type="numeric">Precio de Venta</th>
				</tr>
				</thead>
			</table>
			
			</td><td>
				<img src='<?php echo site_url('images/ndisp.jpg')?>' width='200' >
			</td>
	</tr>
	</table>
</div>

<div data-theme="a" data-role="footer" data-position="fixed">
	<?php echo (isset($footer))? $footer:''; ?>
</div>

<script type="text/javascript">

$('#tabladata').bootgrid({
	ajax: true,
	post: function (){
		/* To accumulate custom parameter with the request object */
		return {id: "b0df282a-0d67-40e5-8558-c9e93b7befed"};
    },
    rowCount: 30,
    url: '<?php echo site_url('inventario/verificador/buscasinv')?>',
	formatters: {
		"link": function(column, row){
			return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
		}
	}
});

var actObj='';

</script>
