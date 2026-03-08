<?php
/**
 * Mega menu helpers and custom walker.
 *
 * @package Procoders
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom navigation walker with support for ACF-driven mega menu.
 */
class Procoders_Mega_Menu_Walker extends Walker_Nav_Menu {
	/**
	 * Return inline SVG icon markup for mega menu section titles.
	 *
	 * @param string $icon Icon key.
	 * @return string
	 */
	protected function get_title_icon_svg( $icon ) {
		$focus_icon = '<svg class="procoders-mega-menu__title-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M19 19H5V17.687C5 16.474 5.725 15.38 6.846 14.915C7.981 14.445 9.67 14 12 14C14.33 14 16.019 14.445 17.154 14.916C18.275 15.38 19 16.474 19 17.687V19Z" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 5V1H5" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M23 5V1H19" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 19V23H5" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M23 19V23H19" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$list_icon  = '<svg class="procoders-mega-menu__title-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M3 1H21V23H3V1Z" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 14H12" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 14H7" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 18H12" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 18H7" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 10H12" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 10H7" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 6H12" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 6H7" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		return 'focus' === $icon ? $focus_icon : $list_icon;
	}

	/**
	 * Check if top-level menu item has mega menu enabled.
	 *
	 * @param WP_Post $menu_item Menu item object.
	 * @return bool
	 */
	protected function is_mega_enabled( $menu_item ) {
		if ( ! ( $menu_item instanceof WP_Post ) ) {
			return false;
		}

		if ( ! function_exists( 'get_field' ) ) {
			return false;
		}

		if ( (int) $menu_item->menu_item_parent !== 0 ) {
			return false;
		}

		return (bool) get_field( 'mega_enable', 'menu_item_' . $menu_item->ID );
	}

	/**
	 * Convert selected relationship values into ordered list of posts.
	 *
	 * @param mixed              $selected Selected values from ACF relationship field.
	 * @param array<int, string> $post_types Allowed post types.
	 * @return array<int, WP_Post>
	 */
	protected function resolve_selected_posts( $selected, $post_types ) {
		if ( empty( $selected ) || ! is_array( $selected ) ) {
			return array();
		}

		$post_ids = array();

		foreach ( $selected as $raw ) {
			if ( $raw instanceof WP_Post ) {
				$post_ids[] = (int) $raw->ID;
				continue;
			}

			$post_ids[] = absint( $raw );
		}

		$post_ids = array_values( array_filter( array_unique( $post_ids ) ) );

		if ( empty( $post_ids ) ) {
			return array();
		}

		$allowed_post_types = array_values(
			array_filter(
				array_map( 'sanitize_key', (array) $post_types )
			)
		);

		if ( empty( $allowed_post_types ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'           => $allowed_post_types,
				'post_status'         => 'publish',
				'post__in'            => $post_ids,
				'orderby'             => 'post__in',
				'posts_per_page'      => count( $post_ids ),
				'ignore_sticky_posts' => true,
			)
		);
	}

	/**
	 * Render one second/third-column link row with optional description.
	 *
	 * @param array<string, mixed> $row Repeater row.
	 * @return string
	 */
	protected function render_mega_link_row( $row ) {
		if ( ! is_array( $row ) || empty( $row['link'] ) || ! is_array( $row['link'] ) ) {
			return '';
		}

		$link        = $row['link'];
		$url         = isset( $link['url'] ) ? esc_url( $link['url'] ) : '';
		$title       = isset( $link['title'] ) ? trim( wp_strip_all_tags( (string) $link['title'] ) ) : '';
		$target      = isset( $link['target'] ) && '' !== $link['target'] ? $link['target'] : '_self';
		$description = isset( $row['description'] ) ? trim( wp_strip_all_tags( (string) $row['description'] ) ) : '';

		if ( '' === $url || '' === $title ) {
			return '';
		}

		ob_start();
		?>
		<li class="procoders-mega-menu__item">
			<a class="procoders-mega-menu__link" href="<?php echo esc_url( $url ); ?>" target="<?php echo esc_attr( $target ); ?>">
				<?php echo esc_html( $title ); ?>
			</a>
			<?php if ( '' !== $description ) : ?>
				<span class="procoders-mega-menu__description"><?php echo esc_html( $description ); ?></span>
			<?php endif; ?>
		</li>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render first-column links for selected posts.
	 *
	 * @param array<int, WP_Post> $posts Selected posts.
	 * @return string
	 */
	protected function render_subtle_post_items( $posts ) {
		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return '';
		}

		$items = '';

		foreach ( $posts as $selected_post ) {
			if ( ! ( $selected_post instanceof WP_Post ) ) {
				continue;
			}

			$post_title = trim( wp_strip_all_tags( get_the_title( $selected_post ) ) );

			if ( '' === $post_title ) {
				continue;
			}

			$items .= sprintf(
				'<li class="procoders-mega-menu__item"><a class="procoders-mega-menu__subtle-link" href="%s">%s</a></li>',
				esc_url( get_permalink( $selected_post ) ),
				esc_html( $post_title )
			);
		}

		return $items;
	}

	/**
	 * Render mega menu panel for top-level item.
	 *
	 * @param WP_Post $menu_item Menu item object.
	 * @return string
	 */
	protected function render_mega_panel( $menu_item ) {
		if ( ! function_exists( 'get_field' ) || ! ( $menu_item instanceof WP_Post ) ) {
			return '';
		}

		$context        = 'menu_item_' . $menu_item->ID;
		$columns_title  = trim( (string) get_field( 'mega_columns_title', $context ) );
		$learning_title = trim( (string) get_field( 'mega_col_1_learning_title', $context ) );
		$blog_title     = trim( (string) get_field( 'mega_col_1_blog_title', $context ) );
		$col_2_links    = get_field( 'mega_col_2_links', $context );
		$col_3_links    = get_field( 'mega_col_3_links', $context );
		$footer_title   = trim( (string) get_field( 'mega_footer_title', $context ) );
		$footer_text    = trim( (string) get_field( 'mega_footer_text', $context ) );
		$footer_button  = get_field( 'mega_footer_button', $context );

		$learning_posts = $this->resolve_selected_posts(
			get_field( 'mega_col_1_learning_posts', $context ),
			array( 'learning-center', 'learning_center' )
		);
		$blog_posts     = $this->resolve_selected_posts(
			get_field( 'mega_col_1_blog_posts', $context ),
			array( 'post' )
		);

		$has_footer_button = is_array( $footer_button )
			&& ! empty( $footer_button['url'] )
			&& ! empty( $footer_button['title'] );

		$learning_items = $this->render_subtle_post_items( $learning_posts );
		$blog_items     = $this->render_subtle_post_items( $blog_posts );
		$col_2_items    = '';
		$col_3_items    = '';

		if ( is_array( $col_2_links ) ) {
			foreach ( $col_2_links as $row ) {
				$col_2_items .= $this->render_mega_link_row( $row );
			}
		}

		if ( is_array( $col_3_links ) ) {
			foreach ( $col_3_links as $row ) {
				$col_3_items .= $this->render_mega_link_row( $row );
			}
		}

		if ( '' === $learning_title ) {
			$learning_title = __( 'Learning Center', 'procoders' );
		}

		if ( '' === $blog_title ) {
			$blog_title = __( 'Blog', 'procoders' );
		}

		if ( '' === $columns_title ) {
			$columns_title = __( 'Events', 'procoders' );
		}

		ob_start();
		?>
		<div class="procoders-mega-menu" aria-hidden="true">
			<div class="procoders-mega-menu__grid">
				<div class="procoders-mega-menu__column procoders-mega-menu__column--first">
					<?php if ( '' !== $learning_items ) : ?>
						<div class="procoders-mega-menu__group">
							<h3 class="procoders-mega-menu__group-title procoders-mega-menu__group-title--learning"><?php echo $this->get_title_icon_svg( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $learning_title ); ?></span></h3>
							<ul class="procoders-mega-menu__list"><?php echo wp_kses_post( $learning_items ); ?></ul>
						</div>
					<?php endif; ?>

					<?php if ( '' !== $blog_items ) : ?>
						<div class="procoders-mega-menu__group">
							<h3 class="procoders-mega-menu__group-title procoders-mega-menu__group-title--blog"><?php echo $this->get_title_icon_svg( 'focus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $blog_title ); ?></span></h3>
							<ul class="procoders-mega-menu__list"><?php echo wp_kses_post( $blog_items ); ?></ul>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( '' !== $columns_title ) : ?>
					<h3 class="procoders-mega-menu__columns-title"><?php echo $this->get_title_icon_svg( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $columns_title ); ?></span></h3>
				<?php endif; ?>

				<div class="procoders-mega-menu__column procoders-mega-menu__column--second">
					<?php if ( '' !== $col_2_items ) : ?>
						<ul class="procoders-mega-menu__list"><?php echo wp_kses_post( $col_2_items ); ?></ul>
					<?php endif; ?>
				</div>

				<div class="procoders-mega-menu__column procoders-mega-menu__column--third">
					<?php if ( '' !== $col_3_items ) : ?>
						<ul class="procoders-mega-menu__list"><?php echo wp_kses_post( $col_3_items ); ?></ul>
					<?php endif; ?>
				</div>
			</div>

			<div class="procoders-mega-menu__footer">
				<div class="procoders-mega-menu__footer-copy">
					<?php if ( '' !== $footer_title ) : ?>
						<p class="procoders-mega-menu__footer-title"><?php echo esc_html( $footer_title ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $footer_text ) : ?>
						<p class="procoders-mega-menu__footer-text"><?php echo esc_html( $footer_text ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $has_footer_button ) : ?>
					<a
						class="procoders-mega-menu__footer-button"
						href="<?php echo esc_url( $footer_button['url'] ); ?>"
						target="<?php echo esc_attr( ! empty( $footer_button['target'] ) ? $footer_button['target'] : '_self' ); ?>"
					>
						<?php echo esc_html( $footer_button['title'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Override default walker flow for top-level mega menu items.
	 *
	 * @param object $element Element data.
	 * @param array  $children_elements Children elements.
	 * @param int    $max_depth Max depth.
	 * @param int    $depth Current depth.
	 * @param array  $args Arguments.
	 * @param string $output Output html.
	 * @return void
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}

		$id_field = $this->db_fields['id'];
		$id       = $element->$id_field;

		if ( 0 === (int) $depth && $this->is_mega_enabled( $element ) ) {
			$this->has_children = ! empty( $children_elements[ $id ] );

			$this->start_el( $output, $element, $depth, ...array_values( $args ) );
			$output .= $this->render_mega_panel( $element );
			$this->end_el( $output, $element, $depth, ...array_values( $args ) );

			if ( isset( $children_elements[ $id ] ) ) {
				unset( $children_elements[ $id ] );
			}

			return;
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
}

/**
 * Inject custom walker for primary menu.
 *
 * @param array<string, mixed> $args Menu args.
 * @return array<string, mixed>
 */
function procoders_nav_menu_args_with_mega_menu( $args ) {
	if ( empty( $args['theme_location'] ) || 'menu-1' !== $args['theme_location'] ) {
		return $args;
	}

	$args['walker'] = new Procoders_Mega_Menu_Walker();

	return $args;
}
add_filter( 'wp_nav_menu_args', 'procoders_nav_menu_args_with_mega_menu' );

/**
 * Add special classes to top-level mega menu item.
 *
 * @param array<int, string> $classes Classes.
 * @param WP_Post            $item Menu item.
 * @param stdClass           $args Args.
 * @param int                $depth Depth.
 * @return array<int, string>
 */
function procoders_mega_menu_item_classes( $classes, $item, $args, $depth ) {
	if ( ! function_exists( 'get_field' ) || ! ( $item instanceof WP_Post ) ) {
		return $classes;
	}

	if ( ! isset( $args->theme_location ) || 'menu-1' !== $args->theme_location ) {
		return $classes;
	}

	if ( 0 !== (int) $depth ) {
		return $classes;
	}

	$is_mega = (bool) get_field( 'mega_enable', 'menu_item_' . $item->ID );

	if ( ! $is_mega ) {
		return $classes;
	}

	$classes[] = 'menu-item-has-mega-menu';
	$classes[] = 'menu-item-has-children';

	return array_values( array_unique( $classes ) );
}
add_filter( 'nav_menu_css_class', 'procoders_mega_menu_item_classes', 10, 4 );
