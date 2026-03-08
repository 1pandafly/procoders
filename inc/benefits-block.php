<?php
/**
 * Benefits block helpers and AJAX handlers.
 *
 * @package Procoders
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build query args for benefits list.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param int    $term_id  Selected term ID.
 * @param int    $page     Pagination page.
 * @param int    $per_page Posts per page.
 *
 * @return array<string, mixed>
 */
function procoders_get_benefits_query_args( $taxonomy, $term_id, $page, $per_page ) {
	$args = array(
		'post_type'           => 'benefit',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, $per_page ),
		'paged'               => max( 1, $page ),
		'ignore_sticky_posts' => true,
	);

	if ( $term_id > 0 && taxonomy_exists( $taxonomy ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		);
	}

	return $args;
}

/**
 * Get terms for benefits tabs.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param string $post_type Post type slug.
 *
 * @return array<int, WP_Term>
 */
function procoders_get_benefits_terms( $taxonomy, $post_type = 'benefit' ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$post_ids = get_posts(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $post_ids ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'object_ids' => $post_ids,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return $terms;
}

/**
 * Render single benefit card HTML.
 *
 * @param int  $post_id     Post ID.
 * @param bool $is_featured Whether card should be highlighted.
 *
 * @return string
 */
function procoders_render_benefit_card( $post_id, $is_featured = false ) {
	$card_classes = 'procoders-benefits__card';

	if ( $is_featured ) {
		$card_classes .= ' is-featured';
	}

	$title = get_the_title( $post_id );
	$text  = '';
	$icon  = '';

	if ( function_exists( 'get_field' ) ) {
		$text    = (string) get_field( 'text', $post_id );
		$icon_id = (int) get_field( 'icon', $post_id );

		if ( $icon_id > 0 ) {
			$icon = wp_get_attachment_image(
				$icon_id,
				'medium',
				false,
				array(
					'class'   => 'procoders-benefits__icon-image',
					'loading' => 'lazy',
				)
			);
		}
	}

	if ( '' === trim( $text ) ) {
		$text = $title;
	}

	ob_start();
	?>
	<article class="<?php echo esc_attr( $card_classes ); ?>">
		<?php if ( $icon ) : ?>
			<div class="procoders-benefits__icon"><?php echo wp_kses_post( $icon ); ?></div>
		<?php endif; ?>
		<div class="procoders-benefits__text"><?php echo esc_html( $text ); ?></div>
	</article>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render benefits cards from a query.
 *
 * @param WP_Query $query          Query object.
 * @param bool     $featured_first Highlight first card.
 *
 * @return string
 */
function procoders_render_benefits_cards_html( $query, $featured_first = false ) {
	if ( ! ( $query instanceof WP_Query ) || ! $query->have_posts() ) {
		return '<div class="procoders-benefits__empty">' . esc_html__( 'No benefits found.', 'procoders' ) . '</div>';
	}

	$html  = '';
	$index = 0;

	while ( $query->have_posts() ) {
		$query->the_post();

		$html .= procoders_render_benefit_card(
			get_the_ID(),
			( $featured_first && 0 === $index )
		);

		$index++;
	}

	wp_reset_postdata();

	return $html;
}

/**
 * Handle AJAX benefits filtering and pagination.
 *
 * @return void
 */
function procoders_ajax_load_benefits() {
	$nonce_ok = check_ajax_referer( 'procoders_benefits', 'nonce', false );

	if ( ! $nonce_ok ) {
		wp_send_json_error(
			array(
				'message' => esc_html__( 'Invalid request.', 'procoders' ),
			),
			403
		);
	}

	$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : 'benefits-category';
	$term_id  = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;
	$page     = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;
	$per_page = isset( $_POST['per_page'] ) ? absint( wp_unslash( $_POST['per_page'] ) ) : 3;

	if ( $per_page < 1 ) {
		$per_page = 3;
	}

	if ( $per_page > 24 ) {
		$per_page = 24;
	}

	if ( ! taxonomy_exists( $taxonomy ) ) {
		$taxonomy = 'benefits-category';
	}

	$query = new WP_Query(
		procoders_get_benefits_query_args(
			$taxonomy,
			$term_id,
			max( 1, $page ),
			$per_page
		)
	);

	$max_pages = (int) $query->max_num_pages;

	wp_send_json_success(
		array(
			'html'        => procoders_render_benefits_cards_html( $query, ( 1 === $page ) ),
			'currentPage' => max( 1, $page ),
			'maxPages'    => $max_pages,
			'hasMore'     => max( 1, $page ) < $max_pages,
		)
	);
}
add_action( 'wp_ajax_procoders_load_benefits', 'procoders_ajax_load_benefits' );
add_action( 'wp_ajax_nopriv_procoders_load_benefits', 'procoders_ajax_load_benefits' );
