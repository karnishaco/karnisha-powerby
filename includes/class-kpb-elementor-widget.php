<?php
/**
 * Elementor widget: Karnisha Power By badge.
 *
 * @package Karnisha_PowerBy
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class KPB_Elementor_Widget extends Widget_Base {

	public function get_name(): string {
		return 'karnisha_powerby';
	}

	public function get_title(): string {
		return esc_html__( 'Karnisha Power By', 'karnisha-powerby' );
	}

	public function get_icon(): string {
		return 'eicon-site-logo';
	}

	public function get_categories(): array {
		return array( 'karnisha', 'general' );
	}

	public function get_keywords(): array {
		return array( 'karnisha', 'powerby', 'power by', 'footer', 'credit', 'کارنیشا', 'فوتر' );
	}

	public function get_style_depends(): array {
		return array( KPB_Plugin::HANDLE );
	}

	public function get_script_depends(): array {
		return array( KPB_Plugin::HANDLE );
	}

	/** Lighter DOM on Elementor 3.24+ (no extra .elementor-widget-container). */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls(): void {
		$d     = KPB_Plugin::defaults();
		$start = is_rtl() ? 'right' : 'left';
		$end   = is_rtl() ? 'left' : 'right';

		/* ---------------- Content ---------------- */
		$this->start_controls_section(
			'section_content',
			array( 'label' => esc_html__( 'Content', 'karnisha-powerby' ) )
		);

		$this->add_control(
			'logo',
			array(
				'label'   => esc_html__( 'Logo', 'karnisha-powerby' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => $d['logo'] ),
			)
		);

		$this->add_control(
			'logo_alt',
			array(
				'label'   => esc_html__( 'Logo alt text', 'karnisha-powerby' ),
				'type'    => Controls_Manager::TEXT,
				'default' => $d['logo_alt'],
			)
		);

		$this->add_control(
			'line_1',
			array(
				'label'       => esc_html__( 'Line 1', 'karnisha-powerby' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['line_1'],
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'line_2',
			array(
				'label'       => esc_html__( 'Line 2', 'karnisha-powerby' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['line_2'],
				'label_block' => true,
			)
		);

		$this->add_control(
			'name',
			array(
				'label'       => esc_html__( 'Brand name', 'karnisha-powerby' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['name'],
				'label_block' => true,
			)
		);

		$this->add_control(
			'slogan',
			array(
				'label'       => esc_html__( 'Slogan', 'karnisha-powerby' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['slogan'],
				'label_block' => true,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Brand link (optional)', 'karnisha-powerby' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'default'     => array(
					'url'         => $d['link'],
					'is_external' => true,
					'nofollow'    => true,
				),
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();

		/* ---------------- Behaviour ---------------- */
		$this->start_controls_section(
			'section_behavior',
			array( 'label' => esc_html__( 'Behaviour', 'karnisha-powerby' ) )
		);

		$this->add_control(
			'trigger',
			array(
				'label'   => esc_html__( 'Open box on', 'karnisha-powerby' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $d['trigger'],
				'options' => array(
					'click' => esc_html__( 'Click', 'karnisha-powerby' ),
					'hover' => esc_html__( 'Hover (and click on touch)', 'karnisha-powerby' ),
				),
			)
		);

		$this->add_control(
			'position',
			array(
				'label'   => esc_html__( 'Box position', 'karnisha-powerby' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $d['position'],
				'options' => array(
					'top'    => esc_html__( 'Above logo', 'karnisha-powerby' ),
					'bottom' => esc_html__( 'Below logo', 'karnisha-powerby' ),
				),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'   => esc_html__( 'Box alignment', 'karnisha-powerby' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $d['align'],
				'options' => array(
					'start'  => esc_html__( 'Start', 'karnisha-powerby' ),
					'center' => esc_html__( 'Center', 'karnisha-powerby' ),
					'end'    => esc_html__( 'End', 'karnisha-powerby' ),
				),
				'description' => esc_html__( 'On small screens the box is shifted automatically so it never goes off-screen.', 'karnisha-powerby' ),
			)
		);

		$this->add_responsive_control(
			'widget_align',
			array(
				'label'     => esc_html__( 'Logo alignment', 'karnisha-powerby' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Start', 'karnisha-powerby' ),
						'icon'  => 'eicon-text-align-' . $start,
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'karnisha-powerby' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'End', 'karnisha-powerby' ),
						'icon'  => 'eicon-text-align-' . $end,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .krn__powerby' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: Logo ---------------- */
		$this->start_controls_section(
			'section_style_logo',
			array(
				'label' => esc_html__( 'Logo', 'karnisha-powerby' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'logo_size',
			array(
				'label'      => esc_html__( 'Size', 'karnisha-powerby' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => $d['size'] ),
				'selectors'  => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-logo-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: Box ---------------- */
		$this->start_controls_section(
			'section_style_box',
			array(
				'label' => esc_html__( 'Box', 'karnisha-powerby' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'box_width',
			array(
				'label'      => esc_html__( 'Width', 'karnisha-powerby' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 120, 'max' => 420 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-box-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'box_gap',
			array(
				'label'      => esc_html__( 'Distance from logo', 'karnisha-powerby' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'box_bg',
			array(
				'label'     => esc_html__( 'Background', 'karnisha-powerby' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'box_blur',
			array(
				'label'      => esc_html__( 'Background blur', 'karnisha-powerby' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-blur: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => esc_html__( 'Padding', 'karnisha-powerby' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .karnisha__power_box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'box_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'karnisha-powerby' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .karnisha__power_box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .karnisha__power_box',
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: Texts ---------------- */
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Texts', 'karnisha-powerby' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Lines color', 'karnisha-powerby' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-text: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typography',
				'label'    => esc_html__( 'Lines typography', 'karnisha-powerby' ),
				'selector' => '{{WRAPPER}} .karnisha__power_text p',
			)
		);

		$this->add_control(
			'name_color',
			array(
				'label'     => esc_html__( 'Brand name color', 'karnisha-powerby' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .krn__powerby' => '--kpb-name: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'name_typography',
				'label'    => esc_html__( 'Brand name typography', 'karnisha-powerby' ),
				'selector' => '{{WRAPPER}} .karnisha__name',
			)
		);

		$this->add_control(
			'slogan_color',
			array(
				'label'     => esc_html__( 'Slogan color', 'karnisha-powerby' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .karnisha__slogan' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'slogan_typography',
				'label'    => esc_html__( 'Slogan typography', 'karnisha-powerby' ),
				'selector' => '{{WRAPPER}} .karnisha__slogan',
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();

		$logo = ! empty( $s['logo']['url'] ) ? $s['logo']['url'] : KPB_Plugin::default_logo_url();
		$size = ! empty( $s['logo_size']['size'] ) ? (int) $s['logo_size']['size'] : 25;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside render().
		echo KPB_Plugin::render(
			array(
				'logo'     => $logo,
				'logo_alt' => (string) ( $s['logo_alt'] ?? '' ),
				'line_1'   => (string) ( $s['line_1'] ?? '' ),
				'line_2'   => (string) ( $s['line_2'] ?? '' ),
				'name'     => (string) ( $s['name'] ?? '' ),
				'slogan'   => (string) ( $s['slogan'] ?? '' ),
				'link'     => (string) ( $s['link']['url'] ?? '' ),
				'new_tab'  => ! empty( $s['link']['is_external'] ),
				'nofollow' => ! empty( $s['link']['nofollow'] ),
				'trigger'  => (string) ( $s['trigger'] ?? 'click' ),
				'position' => (string) ( $s['position'] ?? 'top' ),
				'align'    => (string) ( $s['align'] ?? 'center' ),
				'size'     => $size,
			)
		);
	}
}
