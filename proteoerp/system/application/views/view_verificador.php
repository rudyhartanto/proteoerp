<table width='100%1'>
	<tr>
		</td><td style='height:150px;background:url("<?php echo site_url('images/barcode.jpg'); ?>") no-repeat right;'>
			&nbsp;
		</td>
	</tr>
</table>

<div data-role="content">
	<table width='100%' class="table table-condensed table-hover table-striped">
		<tr>
			<td width='1200'>
			<table id='tabladata'>
				<thead>
				<tr>
					<th data-column-id="codigo"  data-width='150px' data-identifier="true" >C&oacute;digo</th>
					<th data-column-id="descrip" >Descripci&oacute;n</th>
					<th data-column-id="unidad"  data-width='100px'>Medida</th>
					<th data-column-id="marca"   data-width='150px'>Marca</th>
					<th data-column-id="base1"   data-width='130px' data-align='right' data-headerAlign='right'>Precio</th>
					<th data-column-id="iva"     data-width='130px' data-align='right'>I.V.A.</th>
					<th data-column-id="precio1" data-width='140px' data-align='right'>Precio de Venta</th>
				</tr>
				</thead>
			</table>
			</td><td>
				<table width='100%'>
					<tr>
						<td align='center'><h2>INSTRUCCIONES</h2></td>
					</tr><tr>
						<td>
							<p>Coloque un criterio de busqueda de los productos que desea buscar 
							y apareceran en la tabla los resultados de la misma</p>
							<p>Para colocar la lista de todos lo productos que empiecen por una 
							letra coloque la misma en el campo busacar, por ejemplo coloqie la letra "A", 
							si por ejemplo quisiera ver la todas las abrazaderas coloque "AB". </p>
						</td>
					</tr><tr>
						<td>
							<p>Para hacer busquedas mas complejas utilice como comodin el simbolo 
							% por ejemplo para buscar todos los TUBOS PAVCO de 1/2" coloque en
							el campo buscar:</p>
						</td>
					</tr><tr>
						<td align='center'>
							<p>TUBO%PAVCO%1/2"</p>
						</td>
					</tr><tr>
						<td>
							<p>Para ver las paginas siguientes utilice el navegador ubicado en la
							parte inferior izquierda.</p>
						</td>
					</tr>


				</table>
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
		return {id: "b0df282a-0d67-40e5-8558-c9e93b7befed"};
    },
    rowCount: 18,
    columnSelection: false,
    selection: true,
    url: '<?php echo site_url('inventario/verificador/buscasinv')?>',
	formatters: {
		"link": function(column, row){
			return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
		}
	},
	labels: {
		all:        'Todos',
		infos:      'Muestra del {{ctx.start}} al {{ctx.end}} en total {{ctx.total}} entradas',
		loading:    'Cargando...',
		noResults: 	'Consulta vacia',
		refresh:    'Recargar',
		search:     'Buscar'
	}
});

var actObj='';

</script>
