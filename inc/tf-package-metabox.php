<?php
// don't load directly
defined('ABSPATH') || exit;

TF_Metabox::metabox('tf_package_opt', array(
	'title' => esc_html__('Package Settings', 'tourfic-package'),
	'post_type' => 'tf_package',
	'sections'  => apply_filters('tf_package_opt_sections', array(
		// Hotel Section
		'hotels'     => array(
			'title'  => esc_html__('Hotels', 'tourfic-package'),
			'icon'   => 'fa-sharp fa-solid fa-door-open',
			'fields' => array(
				array(
					'id'    => 'hotel-room-heading',
					'type'  => 'heading',
					'title' => esc_html__('Manage Your Package Hotels', 'tourfic-package'),
				),
				array(
					'id'          => 'tf_package_hotels',
					'type'        => 'select2',
					'label'       => esc_html__('Manage your hotels', 'tourfic-package'),
					'subtitle'    => esc_html__('Select an existing hotel, if available. Note: Hotels already assigned to a package cannot be selected.', 'tourfic-package'),
					'placeholder' => esc_html__('Select Hotels', 'tourfic-package'),
					'options'     => 'posts',
					'multiple'    => true,
					'query_args'  => array(
						'post_type'      => 'tf_hotel',
						'posts_per_page' => -1,
					),
				),
			),
		),
		// Tour Section
		'tours'     => array(
			'title'  => esc_html__('Tours', 'tourfic-package'),
			'icon'   => 'fa-solid fa-person-walking-luggage',
			'fields' => array(
				array(
					'id'    => 'tour-room-heading',
					'type'  => 'heading',
					'title' => esc_html__('Manage Your Package Tours', 'tourfic-package'),
				),
				array(
					'id'          => 'tf_package_tours',
					'type'        => 'select2',
					'label'       => esc_html__('Manage your tours', 'tourfic-package'),
					'subtitle'    => esc_html__('Select an existing tour, if available. Note: Tours already assigned to a package cannot be selected.', 'tourfic-package'),
					'placeholder' => esc_html__('Select Tours', 'tourfic-package'),
					'options'     => 'posts',
					'multiple'    => true,
					'query_args'  => array(
						'post_type'      => 'tf_tours',
						'posts_per_page' => -1,
					),
				),
			),
		),
	)),
));
