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
 * Return sanitized inline SVG for attachment ID.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $class_name    Class for root svg.
 * @return string
 */
function procoders_get_inline_svg_attachment( $attachment_id, $class_name = '' ) {
	$attachment_id = absint( $attachment_id );

	if ( $attachment_id < 1 ) {
		return '';
	}

	$mime_type = get_post_mime_type( $attachment_id );

	if ( 'image/svg+xml' !== $mime_type ) {
		return '';
	}

	$file_path = get_attached_file( $attachment_id );

	if ( ! $file_path || ! file_exists( $file_path ) ) {
		return '';
	}

	$svg = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( ! is_string( $svg ) || '' === trim( $svg ) ) {
		return '';
	}

	if ( '' !== $class_name ) {
		$svg = preg_replace(
			'/<svg\b([^>]*)>/i',
			'<svg$1 class="' . esc_attr( $class_name ) . '" aria-hidden="true" focusable="false">',
			$svg,
			1
		);
	}

	$allowed_svg = array(
		'svg'            => array(
			'class'               => true,
			'id'                  => true,
			'xmlns'               => true,
			'xmlns:xlink'         => true,
			'viewbox'             => true,
			'width'               => true,
			'height'              => true,
			'fill'                => true,
			'stroke'              => true,
			'stroke-width'        => true,
			'stroke-linecap'      => true,
			'stroke-linejoin'     => true,
			'stroke-miterlimit'   => true,
			'fill-rule'           => true,
			'clip-rule'           => true,
			'opacity'             => true,
			'transform'           => true,
			'preserveaspectratio' => true,
			'role'                => true,
			'aria-hidden'         => true,
			'focusable'           => true,
		),
		'g'              => array(
			'class'             => true,
			'id'                => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'fill-rule'         => true,
			'clip-rule'         => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'path'           => array(
			'class'             => true,
			'id'                => true,
			'd'                 => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'fill-rule'         => true,
			'clip-rule'         => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'rect'           => array(
			'class'             => true,
			'id'                => true,
			'x'                 => true,
			'y'                 => true,
			'width'             => true,
			'height'            => true,
			'rx'                => true,
			'ry'                => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'circle'         => array(
			'class'             => true,
			'id'                => true,
			'cx'                => true,
			'cy'                => true,
			'r'                 => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'ellipse'        => array(
			'class'             => true,
			'id'                => true,
			'cx'                => true,
			'cy'                => true,
			'rx'                => true,
			'ry'                => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'line'           => array(
			'class'             => true,
			'id'                => true,
			'x1'                => true,
			'y1'                => true,
			'x2'                => true,
			'y2'                => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'polyline'       => array(
			'class'             => true,
			'id'                => true,
			'points'            => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'polygon'        => array(
			'class'             => true,
			'id'                => true,
			'points'            => true,
			'fill'              => true,
			'stroke'            => true,
			'stroke-width'      => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'opacity'           => true,
			'transform'         => true,
		),
		'use'            => array(
			'class'      => true,
			'id'         => true,
			'href'       => true,
			'xlink:href' => true,
			'x'          => true,
			'y'          => true,
		),
		'defs'           => array(),
		'lineargradient' => array(
			'id'                => true,
			'x1'                => true,
			'x2'                => true,
			'y1'                => true,
			'y2'                => true,
			'gradientunits'     => true,
			'gradienttransform' => true,
		),
		'radialgradient' => array(
			'id'                => true,
			'cx'                => true,
			'cy'                => true,
			'r'                 => true,
			'fx'                => true,
			'fy'                => true,
			'gradientunits'     => true,
			'gradienttransform' => true,
		),
		'stop'           => array(
			'offset'       => true,
			'stop-color'   => true,
			'stop-opacity' => true,
		),
		'clippath'       => array(
			'id' => true,
		),
		'mask'           => array(
			'id'                  => true,
			'x'                   => true,
			'y'                   => true,
			'width'               => true,
			'height'              => true,
			'maskunits'           => true,
			'maskcontentunits'    => true,
		),
		'title'          => array(),
		'desc'           => array(),
	);

	return wp_kses( $svg, $allowed_svg );
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
			$inline_svg = procoders_get_inline_svg_attachment( $icon_id, 'procoders-benefits__icon-svg' );

			if ( '' !== $inline_svg ) {
				$icon = $inline_svg;
			} else {
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
	}

	if ( '' === trim( $text ) ) {
		$text = $title;
	}

	ob_start();
	?>
	<article class="<?php echo esc_attr( $card_classes ); ?>">
		<?php if ( $icon ) : ?>
			<div class="procoders-benefits__icon">
				<?php
				// SVG is sanitized in procoders_get_inline_svg_attachment(); image fallback is wp_get_attachment_image().
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
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
