<div data-theme="a" data-role="header" data-id="mainHeader"><h3>Consulta de precios</h3></div>

<div data-role="content">

	<h3>Art&iacute;culos</h3>

	<!--<a href="#presumen" class="ui-btn ui-icon-bars ui-btn-icon-left ui-shadow-icon" >Ver Res&uacute;men</a>-->
	<form class="ui-filterable">
		<input id="autocomplete-input" data-type="search" placeholder="Buscar art&iacute;culo...">
		<ul id="autocomplete" data-role="listview" data-inset="true" data-filter="false" data-input="#autocomplete-input"></ul>
	</form>


	<table id='tabladata' data-role="table" class="ui-responsive table-stroke">
		<thead>
		<tr>
			<th data-priority="2">C&oacute;digo</th>
			<th>Descripci&oacute;n</th>
			<th data-priority="3" style='text-align:right'>Precio</th>
			<th data-priority="1" style='text-align:right'><abbr title="Rotten Tomato Rating">IVA</abbr></th>
			<th data-priority="5" style='text-align:right'>Precio de Venta</th>
		</tr>
		</thead>
		<tbody></tbody>
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

		if(value && value.length >= 4){
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
