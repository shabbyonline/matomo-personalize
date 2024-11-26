<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.6.0
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			// $list = [];
			// $file = null;
			// $created = null;
			$userId = null;
			$productid_list = null;
			// $date = date('d-m-y');

			if($order->get_customer_id() == 0){
				$userId = $_COOKIE["guest"];
			}else{
				$userId = $order->get_customer_id();
			}
			
			foreach ( $order_items as $item_id => $item ) {
				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
						)
					);
					$product_id = $item['product_id'];
					$product_name = $item['name'];
					$product_price = $item['total'];
					$product_qty = $item['quantity'];

					$productid_list .= ",$product_id";

					$terms = get_the_terms( $item['product_id'], 'product_cat' );
					foreach ($terms as $term) {
						$product_cat_name = $term->name;
						break;
					}
?>
			<script>
				_paq.push(['addEcommerceItem',
					"<?php echo $product_id; ?>", // (required) SKU: Product unique identifier but ive used product id
					"<?php echo $product_name; ?>", // (optional) Product name,
				  ["<?php echo $product_cat_name; ?>"], // (Optional) productCategory max 5 only
				  <?php echo $product_price; ?>, // (Recommended) Product Price
				  <?php echo $product_qty; ?>, // (Optional, defaults to 1) quantity
				]);
			</script>
<?php
				// $list[] = [
				// 	[$userId, $item->get_product_id(), time(), "Purchase"],
				// ];
			}
?>
		<span class="mtm-order-product-ids" style="display:none;"><?php echo substr($productid_list, 1); ?></span>
<?php
			// if(file_exists(PLUGIN_PATH."$date.csv")){
			// 	$file = fopen(PLUGIN_PATH."$date.csv","a");
			// 	foreach ($list as $line) {
			// 		$created = fputcsv($file, $line[0]);
			// 	}
			// } else {
			// 	$file = fopen(PLUGIN_PATH."$date.csv","w");
			// 	array_unshift($list[0],["USER_ID","ITEM_ID","TIMESTAMP","EVENT_TYPE"]);
			// 	foreach ($list as $line) {
			// 		$created = fputcsv($file, $line[0]);
			// 	}
			// }
			// fclose($file);
			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</tbody>

		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
					<tr>
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
					<?php
			}
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>
			<?php
				// Doc: https://stackoverflow.com/questions/22249615/get-woocommerce-carts-total-amount
				$shipping_cost 	= $order->get_shipping_total();
				$get_total 	  	= $order->get_total();
				$get_subtotal 	= $order->get_subtotal();
			?>
			<span class="get_shipping_cost" style="display:none;"><?php echo floatval($shipping_cost); ?></span>
			<span class="get_total_amount" style="display:none;"><?php echo floatval($get_total); ?></span>
			<span class="get_subtotal_amount" style="display:none;"><?php echo floatval($get_subtotal); ?></span>
			<!-- <script>
				_paq.push(['trackEcommerceOrder',
					<?php //echo $order_id; ?>, // (Required) orderId
					<?php //echo floatval($get_total); ?>, // (Required) grandTotal (revenue)
					<?php //echo floatval($get_subtotal); ?>, // (Optional) subTotal
					0, // (optional) tax
					<?php //echo floatval($shipping_cost); ?>, // (optional) shipping
				]);
			</script> -->


	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
