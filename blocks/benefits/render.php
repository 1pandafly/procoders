<?php
/**
 * Benefits block server render template.
 *
 * @package Procoders
 */

$attributes = is_array( $attributes ) ? $attributes : array();

$title          = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : '';
$taxonomy       = isset( $attributes['taxonomy'] ) ? sanitize_key( $attributes['taxonomy'] ) : 'benefits-category';
$posts_per_page = isset( $attributes['postsPerPage'] ) ? absint( $attributes['postsPerPage'] ) : 3;

if ( $posts_per_page < 1 ) {
	$posts_per_page = 3;
}

if ( ! taxonomy_exists( $taxonomy ) ) {
	$taxonomy = 'benefits-category';
}

$terms = procoders_get_benefits_terms( $taxonomy );

$active_term_id = ! empty( $terms ) ? (int) $terms[0]->term_id : 0;
$query          = new WP_Query(
	procoders_get_benefits_query_args(
		$taxonomy,
		$active_term_id,
		1,
		$posts_per_page
	)
);

$cards_html = procoders_render_benefits_cards_html( $query, true );
$max_pages  = (int) $query->max_num_pages;
$has_more   = $max_pages > 1;

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'              => 'procoders-benefits',
		'data-ajax-url'      => admin_url( 'admin-ajax.php' ),
		'data-nonce'         => wp_create_nonce( 'procoders_benefits' ),
		'data-taxonomy'      => $taxonomy,
		'data-term-id'       => (string) $active_term_id,
		'data-page'          => '1',
		'data-max-pages'     => (string) max( 1, $max_pages ),
		'data-per-page'      => (string) $posts_per_page,
	)
);
?>
<section <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<div class="procoders-benefits__inner">
		<?php if ( '' !== $title ) : ?>
			<h2 class="procoders-benefits__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $terms ) ) : ?>
			<div class="procoders-benefits__tabs-wrap">
				<div class="procoders-benefits__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Benefits categories', 'procoders' ); ?>">
					<?php foreach ( $terms as $index => $term ) : ?>
						<?php
						$is_active = 0 === $index;
						?>
						<button
							type="button"
							class="procoders-benefits__tab<?php echo $is_active ? ' is-active' : ''; ?>"
							data-term-id="<?php echo esc_attr( $term->term_id ); ?>"
							role="tab"
							aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $term->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="procoders-benefits__grid" data-grid>
			<?php echo wp_kses_post( $cards_html ); ?>
		</div>

		<div class="procoders-benefits__actions">
			<button type="button" class="procoders-benefits__load-more" <?php echo $has_more ? '' : 'hidden'; ?>>
				<?php esc_html_e( 'Load more...', 'procoders' ); ?>
			</button>
		</div>
	</div>
</section>
