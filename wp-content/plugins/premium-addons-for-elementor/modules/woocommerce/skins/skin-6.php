<?php
/**
 * PA Skin 6
 *
 * @package PA
 */

namespace PremiumAddons\Modules\Woocommerce\Skins;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;


use PremiumAddons\Modules\Woocommerce\TemplateBlocks\Skin_Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Skin_6
 *
 * @property Products $parent
 */
class Skin_6 extends Skin_Base {

	/**
	 * Get ID.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function get_id() {
		return 'grid-6';
	}

	/**
	 * Get title.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Skin 5', 'premium-addons-for-elementor' );
	}

	/**
	 * Register control actions.
	 *
	 * @since 4.7.0
	 * @access protected
	 */
	protected function _register_controls_actions() {

		// Content Controls.
		add_action( 'elementor/element/premium-woo-products/section_pagination_options/after_section_end', array( $this, 'register_display_options_controls' ) );

		// Quick View Controls.
		add_action( 'elementor/element/premium-woo-products/section_pagination_options/after_section_end', array( $this, 'register_quick_view_controls' ) );

		// Image Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_product_content_style' ) );

		// Product Description Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_product_excerpt_style' ) );

		// Product CTA Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_product_cta_style' ) );

		// Product Featured Ribbon Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_quick_style_controls' ), 30 );

		parent::_register_controls_actions();
	}

	/**
	 * Register content control section.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param Widget_Base $widget widget object.
	 */
	public function register_display_options_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->start_controls_section(
			'section_content_field',
			array(
				'label' => __( 'Display Options', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'product_image',
			array(
				'label'   => __( 'Image', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'product_title',
			array(
				'label'   => __( 'Title', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'title_above_img',
			array(
				'label'        => __( 'Place Title Above Image', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-woo-title-above-',
				'render_type'  => 'template',
				'condition'    => array(
					$this->get_control_id( 'product_image' ) => 'yes',
				),
			)
		);

		$this->add_control(
			'product_category',
			array(
				'label'   => __( 'Category', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'product_rating',
			array(
				'label'   => __( 'Rating', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'product_price',
			array(
				'label'   => __( 'Price', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'product_excerpt',
			array(
				'label' => __( 'Excerpt', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'Excerpt Length', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => array(
					$this->get_control_id( 'product_excerpt' ) => 'yes',
				),
			)
		);

		$this->add_control(
			'product_cta',
			array(
				'label'   => __( 'Add To Cart', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_responsive_control(
			'alignment',
			array(
				'label'        => __( 'Alignment', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'   => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'      => 'left',
				'toggle'       => false,
				'prefix_class' => 'premium-woo-product-align-',
				'selectors'    => array(
					'{{WRAPPER}} .premium-woo-products-details-wrap, {{WRAPPER}} .premium-woo-product__link'    => 'text-align: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Content Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param Widget_Base $widget widget object.
	 */
	public function register_product_content_style( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->start_controls_section(
			'section_product_style',
			array(
				'label' => __( 'Product', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'content_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-woo-product-wrapper',
			)
		);

		$this->add_control(
			'product_lq_effect',
			array(
				'label'       => __( 'Liquid Glass Effect', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => sprintf(
					/* translators: 1: `<a>` opening tag, 2: `</a>` closing tag. */
					esc_html__( 'Important: Make sure this element has a semi-transparent background color to see the effect. See all presets from %1$shere%2$s.', 'premium-addons-for-elementor' ),
					'<a href="https://premiumaddons.com/liquid-glass/" target="_blank">',
					'</a>'
				),
				'options'     => array(
					'none'   => __( 'None', 'premium-addons-for-elementor' ),
					'glass1' => __( 'Preset 01', 'premium-addons-for-elementor' ),
					'glass2' => __( 'Preset 02', 'premium-addons-for-elementor' ),
					'glass3' => apply_filters( 'pa_pro_label', __( 'Preset 03 (Pro)', 'premium-addons-for-elementor' ) ),
					'glass4' => apply_filters( 'pa_pro_label', __( 'Preset 04 (Pro)', 'premium-addons-for-elementor' ) ),
					'glass5' => apply_filters( 'pa_pro_label', __( 'Preset 05 (Pro)', 'premium-addons-for-elementor' ) ),
					'glass6' => apply_filters( 'pa_pro_label', __( 'Preset 06 (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'none',
				'label_block' => true,
				'render_type' => 'template',
			)
		);

		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-products-details-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register product excerpt style.
	 * Register product excerpt style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param Widget_Base $widget widget object.
	 */
	public function register_product_excerpt_style( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->start_controls_section(
			'section_desc_style',
			array(
				'label' => __( 'Description', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-product-desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'desc_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-product-desc',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'desc_text_shadow',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-product-desc',
			)
		);

		$this->add_responsive_control(
			'desc_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-product-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register product CTA style.
	 * Register product CTA style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param Widget_Base $widget widget object.
	 */
	public function register_product_cta_style( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->start_controls_section(
			'section_button_style',
			array(
				'label' => __( 'Add To Cart', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'cta_typography',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-products-details-wrap .premium-woo-atc-button .button',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
			)
		);

		$this->add_responsive_control(
			'cta_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-products-details-wrap .premium-woo-atc-button .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'cta_style_tabs' );

		$this->start_controls_tab(
			'cta_style_tab_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'cta_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button, {{WRAPPER}} .premium-woo-cart-btn .premium-woo-add-cart-icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'cta_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button, {{WRAPPER}} .premium-woo-cart-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'cta_shadow',
				'selector' => '{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button, {{WRAPPER}} .premium-woo-cart-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'cta_border',
				'selector' => '{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button, {{WRAPPER}} .premium-woo-cart-btn',
			)
		);

		$this->add_control(
			'cta_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button, {{WRAPPER}} .premium-woo-cart-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cta_style_tab_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'cta_color_hover',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button:hover, {{WRAPPER}} .premium-woo-cart-btn:hover .premium-woo-add-cart-icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'cta_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-products-details-wrap .premium-woo-atc-button .button:hover, {{WRAPPER}} .premium-woo-cart-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'cta_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button:hover, {{WRAPPER}} .premium-woo-cart-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'cta_border_hover',
				'selector' => '{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button:hover, {{WRAPPER}} .premium-woo-cart-btn:hover',
			)
		);

		$this->add_control(
			'cta_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-products-details-wrap .premium-woo-atc-button .button:hover, {{WRAPPER}} .premium-woo-cart-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Quick View Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param Widget_Base $widget widget object.
	 */
	public function register_quick_view_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->start_controls_section(
			'section_content_quick_view',
			array(
				'label' => __( 'Quick View', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'quick_view_notice',
			array(
				'raw'             => __( 'Please make sure that Display Options includes CTA', 'premium-addons-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		$this->add_control(
			'quick_view',
			array(
				'label'   => __( 'Enable Quick View', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Quick View Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param Widget_Base $widget widget object.
	 */
	public function register_quick_style_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->start_controls_section(
			'section_quick_view_style',
			array(
				'label'     => __( 'Quick View Trigger Button', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$this->get_control_id( 'quick_view' ) => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'qv_typography',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
			)
		);

		$this->add_responsive_control(
			'qv_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'qv_style_tabs' );

		$this->start_controls_tab(
			'qv_style_tab_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'qv_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'qv_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'qv_shadow',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'qv_border',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn',
			)
		);

		$this->add_control(
			'qv_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'qv_style_tab_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'qv_color_hover',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'qv_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'qv_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'qv_border_hover',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn:hover',
			)
		);

		$this->add_control(
			'qv_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-qv-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render Main HTML.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	public function render() {

		$settings = $this->parent->get_settings();

		$skin = Skin_Init::get_instance( $this->get_id() );

		echo wp_kses_post( sanitize_text_field( $skin->render( $this->get_id(), $settings, $this->parent->get_id() ) ) );
	}
}
