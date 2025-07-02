require([
    "jquery",
	"domReady!"
], function($){
//<![CDATA[
    $(document).ready(function() {
		var JsonObj = {};
		JsonObj.promotions = [];
		var intCtr = 0;
		$('a[data-promotion]').each(function() {
			if ($(this).data("promotion") == !0) {
				id = $(this).data("id");
				name = $(this).data("name");
				creative = $(this).data("creative");
				position = $(this).data("position");
				var promotion = {
				  'id': id,                         // Name or ID is required.
				  'name': name,
				  'creative': creative,
				  'position': position
				}
				JsonObj.promotions.push(promotion);
				intCtr++;
			}

			$(this).click(function(e) {
				if ($(this).data("promotion") == !0) {
					id = $(this).data("id");
					name = $(this).data("name");
					creative = $(this).data("creative");
					position = $(this).data("position");
					href = $(this).attr('href');
					e.preventDefault();
					window.dataLayer = window.dataLayer || [];
					dataLayer.push({
						'event': 'promotionClick',
						'ecommerce': {
						  'promoClick': {
							'promotions': [
							 {
							   'id': id,                         // Name or ID is required.
							   'name': name,
							   'creative': creative,
							   'position': position
							 }]
						  }
						},
						'eventCallback': function() {
						  document.location = href;
						}
					  });

					//console.log('Clicked -  '+id+' - '+name+' - '+creative+' - '+position);
				}
			})
		});
		//console.log(JsonObj);
		if (intCtr>0){
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
                'event': 'view_promotion',
				'ecommerce': {
					'promoView': JsonObj
				}
			});
		}
    });
//]]>
});
