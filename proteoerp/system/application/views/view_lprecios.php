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
	<form class="ui-filterable">
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
	</form>

	<table width='100%'>
		<tr>
			<td width='80%'>
			<table id='tabladata' data-role="table" class="ui-responsive table-stroke">
				<thead>
				<tr>
					<th data-priority="2">C&oacute;digo</th>
					<th>Descripci&oacute;n</th>
					<th>Medida</th>
					<th data-priority="3" style='text-align:right'>Precio</th>
					<th data-priority="1" style='text-align:right'><abbr title="Impuesto al valor agregado">IVA</abbr></th>
					<th data-priority="5" style='text-align:right'>Precio de Venta</th>
				</tr>
				</thead>
				<tbody></tbody>
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

var actObj='';

$(document).on("pagecreate", "#mainpage", function(){


	$("#autocomplete-input").keyup(function() {

		var $ul    = $("#tabladata tbody"),
			$input = $(this),
			value  = $input.val();
		var xhr;
		var base, precio;

		if(value && value.length >= 1){
			if(typeof xhr == 'object'){
				xhr.abort();
			}

			xhr = $.ajax({
				url: "<?php echo site_url('inventario/lprecios/buscasinv'); ?>",
				dataType: "json",
				type: "POST",
				crossDomain: false,
				data: { q : value }
			}).then(function(response){
				var a = JSON.stringify(response);

				console.log(response);
				if(a !== actObj){
					$ul.html("");
					//$ul.html("<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>");
					//$ul.listview("refresh");

					actObj = a;
					var html   = "";
					$.each(response, function ( i, val ){
						base   = val.base1;
						precio = base*(1+(val.iva/100));
						iva    = precio-base;

						//html += "<li><a href='#'>";
						//if(val.foto=='S'){
						//	html += '<img src="<?php echo site_url('inventario/fotos/obtener'); ?>/'+val.id+'">';
						//}
                        //
						//html += "<h2>"+$('<span/>').text(val.descrip).html()+"";
						//html += "</h2>";
						//html += "<p>C&oacute;digo: <b>"+$('<span/>').text(val.codigo).html()+"</b> Precio: <b>"+nformat(base,2)+"</b> IVA: <b>"+nformat(iva,2)+"</b></p>";
						//html += "Precio de venta: <b style='font-size:1.2em'>"+nformat(precio,2)+"</b>";

						html += "<tr>";
						html += "<td>"+$('<span/>').text(val.codigo).html()+"</td>";
						html += "<td>"+$('<span/>').text(val.descrip).html()+"</td>";
						html += "<td>"+$('<span/>').text(val.unidad).html()+"</td>";
						html += "<td style='text-align:right'>"+nformat(base,2)+"</td>";
						html += "<td style='text-align:right'>"+nformat(iva,2)+"</td>";
						html += "<td style='text-align:right'><b style='font-size:1.2em'>"+nformat(precio,2)+"</b></td>";
						html += "</tr>";

						//html += "</a></li>";
					});

					$ul.html(html);
					//$ul.listview( "refresh" );
					//$ul.trigger( "updatelayout");
				}
			});
		}
	});

	if($('#autocomplete-input').val()!=''){
		$('#autocomplete-input').keyup();
	}

});
</script>
