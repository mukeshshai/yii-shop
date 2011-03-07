<?php
Shop::register('shop.css');
if(!isset($products)) 
	$products = Shop::getCartContent();

if(!isset($this->breadcrumbs))
	$this->breadcrumbs = array(
			Shop::t('Shop') => array('//shop/products/'),
			Shop::t('Shopping Cart'));
	?>
	<h2> <?php echo Shop::t('Shopping cart'); ?> </h2>


<?php
if($products) {
	echo '<table class="shopping_cart">';
	printf('<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>',
			Shop::t('Image'),
			Shop::t('Amount'),
			Shop::t('Product'),
			Shop::t('Variation'),
			Shop::t('Price Single'),
			Shop::t('Price Total'),
			Shop::t('Actions')
);

	foreach($products as $position => $product) {
		if(@$model = Products::model()->findByPk($product['product_id'])) {
			$variations = '';
			if(isset($product['Variations'])) {
				foreach($product['Variations'] as $specification => $variation) {
					$specification = ProductSpecification::model()->findByPk($specification);
					if($specification->is_user_input)
						$variation = $variation[0];
					else
						$variation = ProductVariation::model()->findByPk($variation);

					$variations .= $specification . ': ' . $variation . '<br />';
				}
			}

			printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td class="price_'.$position.'">%s</td><td>%s</td></tr>',
					$model->getImage(),
					CHtml::textField('amount_'.$position,
						$product['amount'], array(
							'size' => 4,
							'class' => 'amount_'.$position,
							)
						),
					$model->title,
					$variations,
					Shop::priceFormat($model->getPrice(@$product['Variations'])),
					Shop::priceFormat($model->getPrice(@$product['Variations'], @$product['amount'])),
					CHtml::link(Shop::t('Remove'), array(
							'//shop/shoppingCart/delete',
							'id' => $position), array(
								'confirm' => Shop::t('Are you sure?')))
					);

			Yii::app()->clientScript->registerScript('amount_'.$position,"
					$('.amount_".$position."').keyup(function() {
						$.ajax({
							url:'".$this->createUrl('//shop/shoppingCart/updateAmount')."',
							data: $('#amount_".$position."'),
							success: function(result) {
							$('.amount_".$position."').css('background-color', 'lightgreen');
							$('.widget_amount_".$position."').css('background-color', 'lightgreen');
							$('.widget_amount_".$position."').html($('.amount_".$position."').val());
							$('.price_".$position."').html(result);	
							$('.price_total').load('".$this->createUrl(
							'//shop/shoppingCart/getPriceTotal')."');
							},
							error: function() {
							$('#amount_".$position."').css('background-color', 'red');
							},

							});
				});
					");
			}
}
	if($shippingMethod = Shop::getShippingMethod()) {
		printf('<tr>
				<td></td>
				<td>1</td>
				<td>%s</td>
				<td></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td></tr>',
				Shop::t('Shipping costs'),
				Shop::priceFormat($shippingMethod->price),
				Shop::priceFormat($shippingMethod->price),
				CHtml::link(Shop::t('edit'), array('//shop/shippingMethod/choose'))
				);
	}

	echo '</table>';

	echo '<h2 class="price_total">'.shop::getPriceTotal().'</h2>';
?>
<hr />

<?php
echo Shop::t('All prices are gross') . '<br />';
echo Shop::t('All prices excluding shipping costs') . '<br />';
 if(Yii::app()->controller->id != 'order') {
echo CHtml::link(Shop::t('Buy additional Products'), array(
			'//shop/products')) . '<br />'; 
echo CHtml::link(Shop::t('Buy this products'), array(
			'//shop/order/create')); 
}

} else echo Shop::t('Your shopping cart is empty'); ?>
