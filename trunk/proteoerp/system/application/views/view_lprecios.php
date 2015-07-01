<div data-theme="a" data-role="header" data-id="mainHeader"><h3>Consulta de precios</h3></div>

<div data-role="content">

	<h3>Art&iacute;culos</h3>

	<!--<a href="#presumen" class="ui-btn ui-icon-bars ui-btn-icon-left ui-shadow-icon" >Ver Res&uacute;men</a>-->
	<form class="ui-filterable">
		<input id="autocomplete-input" data-type="search" placeholder="Buscar art&iacute;culo...">
		<ul id="autocomplete" data-role="listview" data-inset="true" data-filter="true" data-input="#autocomplete-input"></ul>
	</form>

</div>

<div data-theme="a" data-role="footer" data-position="fixed">
	<?php echo (isset($footer))? $footer:''; ?>
</div>

<script type="text/javascript">

$(document).on("pagecreate", "#mainpage", function(){


	$("#autocomplete").on("filterablebeforefilter", function(e, data){
		var $ul    = $(this),
			$input = $( data.input ),
			value  = $input.val(),
			html   = "";
		var base, precio;
		$ul.html("");
		if(value && value.length >= 4){
			$ul.html( "<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>" );
			$ul.listview( "refresh" );
			$.ajax({
				url: "<?php echo site_url('inventario/lprecios/buscasinv'); ?>",
				dataType: "json",
				type: "POST",
				crossDomain: false,
				data: { q : $input.val() }
			}).then(function(response){
				$.each(response, function ( i, val ){
console.log(val);
					base   = val.base1;
					precio = base*(1+(val.iva/100));
					iva    = precio-base;

					html += "<li><a href='#'>";
					html += "<h2>"+$('<span/>').text(val.descrip).html()+"";
					html += "</h2>";
					html += "<p>C&oacute;digo: <b>"+$('<span/>').text(val.codigo).html()+"</b> Precio: <b>"+nformat(base,2)+"</b> IVA: <b>"+nformat(iva,2)+"</b></p>";
					html += "Precio de venta: <b style='font-size:1.2em'>"+nformat(precio,2)+"</b>";
					html += "</li>";
				});
				console.log(html);
				$ul.html(html);
				$ul.listview( "refresh" );
				$ul.trigger( "updatelayout");
			});
		}
	});


	if($('#autocomplete-input').val()!=''){
		$('#autocomplete-input').keyup();
	}

});
</script>
