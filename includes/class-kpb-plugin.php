<?php
/**
 * Core plugin class: assets, shared renderer, shortcode and Elementor bootstrap.
 *
 * @package Karnisha_PowerBy
 */

defined( 'ABSPATH' ) || exit;

final class KPB_Plugin {

	const HANDLE    = 'kpb-powerby';
	const SHORTCODE = 'karnisha_powerby';

	/** @var KPB_Plugin|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );

		// Elementor integration (only when Elementor is loaded).
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );

		add_action( 'admin_notices', array( $this, 'maybe_notice_missing_elementor' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'karnisha-powerby', false, dirname( plugin_basename( KPB_FILE ) ) . '/languages' );
	}

	/**
	 * Assets are only registered here. They are enqueued on demand — by the
	 * Elementor widget (get_style_depends / get_script_depends) or the
	 * shortcode — so pages without the badge load nothing extra.
	 */
	public function register_assets(): void {
		wp_register_style(
			self::HANDLE,
			KPB_URL . 'assets/css/powerby.css',
			array(),
			KPB_VERSION
		);

		wp_register_script(
			self::HANDLE,
			KPB_URL . 'assets/js/powerby.js',
			array(),
			KPB_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	public function enqueue_assets(): void {
		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			$this->register_assets();
		}
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}

	/* --------------------------------------------------------------------
	 * Defaults & rendering
	 * ------------------------------------------------------------------ */

	/**
	 * URL of the bundled logo, or empty string when the file is missing.
	 */
	public static function default_logo_url(): string {
		foreach ( array( 'logo.svg', 'logo.webp', 'logo.png' ) as $file ) {
			if ( file_exists( KPB_PATH . 'assets/images/' . $file ) ) {
				return KPB_URL . 'assets/images/' . $file;
			}
		}
		return '';
	}

	/**
	 * Default badge content and behaviour. Filterable so the texts can be
	 * changed for every site at once: add_filter( 'kpb_defaults', ... ).
	 */
	public static function defaults(): array {
		return apply_filters(
			'kpb_defaults',
			array(
				'logo'     => self::default_logo_url(),
				'logo_alt' => 'کارنیشا',
				'line_1'   => 'طراحی و توسعه',
				'line_2'   => 'با افتخار توسط',
				'name'     => 'کارنیشا',
				'slogan'   => 'کارنیشا، همیشه با شما',
				'link'     => '',
				'new_tab'  => true,
				'nofollow' => true,     // Sitewide footer credit links should not pass SEO weight.
				'trigger'  => 'click',  // click | hover
				'position' => 'top',    // top | bottom
				'align'    => 'center', // start | center | end
				'size'     => 25,       // logo size in px
			)
		);
	}

	/**
	 * Shared renderer used by both the shortcode and the Elementor widget.
	 *
	 * @param array $args See defaults().
	 */
	public static function render( array $args = array() ): string {
		$a = wp_parse_args( array_filter( $args, static function ( $v ) {
			return null !== $v;
		} ), self::defaults() );

		$trigger  = in_array( $a['trigger'], array( 'click', 'hover' ), true ) ? $a['trigger'] : 'click';
		$position = in_array( $a['position'], array( 'top', 'bottom' ), true ) ? $a['position'] : 'top';
		$align    = in_array( $a['align'], array( 'start', 'center', 'end' ), true ) ? $a['align'] : 'center';
		$size     = max( 8, min( 200, (int) $a['size'] ) );
		$box_id   = wp_unique_id( 'kpb-box-' );
		$link     = esc_url( (string) $a['link'] );
		$rel      = array();
		if ( $a['nofollow'] ) {
			$rel[] = 'nofollow';
		}
		if ( $a['new_tab'] ) {
			$rel[] = 'noopener';
		}
		$link_attrs = ( $a['new_tab'] ? ' target="_blank"' : '' ) . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' );

		$classes = array(
			'krn__powerby',
			'kpb',
			'kpb--' . $trigger,
			'kpb--pos-' . $position,
			'kpb--align-' . $align,
		);

		$label = trim( $a['line_1'] . ' ' . $a['line_2'] . ' ' . $a['name'] );

		// The shortcode sets the logo size inline; the Elementor widget sets it via its own CSS.
		$style = ( ! empty( $a['inline_size'] ) && 25 !== $size ) ? '--kpb-logo-size:' . $size . 'px' : '';

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo '' !== $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>
			<div class="karnisha">
				<button type="button" class="karnisha__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $box_id ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
					<?php if ( '' !== $a['logo'] ) : ?>
						<img class="karnisha__logo" src="<?php echo esc_url( $a['logo'] ); ?>" alt="<?php echo esc_attr( $a['logo_alt'] ); ?>" width="<?php echo (int) $size; ?>" height="<?php echo (int) $size; ?>" loading="lazy" decoding="async">
					<?php else : ?>
						<span class="karnisha__logo karnisha__logo--fallback" aria-hidden="true">K</span>
					<?php endif; ?>
				</button>

				<div class="karnisha__power_box" id="<?php echo esc_attr( $box_id ); ?>">
					<div class="karnisha__power_text">
						<?php if ( '' !== $a['line_1'] ) : ?>
							<p><?php echo esc_html( $a['line_1'] ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $a['line_2'] ) : ?>
							<p><?php echo esc_html( $a['line_2'] ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $a['name'] ) : ?>
							<div class="karnisha__name">
								<?php if ( '' !== $link ) : ?>
									<a href="<?php echo $link; // Already escaped with esc_url(). ?>"<?php echo $link_attrs; // Built from fixed strings above. ?>><?php echo esc_html( $a['name'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $a['name'] ); ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $a['slogan'] ) : ?>
						<div class="karnisha__slogan"><?php echo esc_html( $a['slogan'] ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/* --------------------------------------------------------------------
	 * Shortcode: [karnisha_powerby trigger="hover" position="top" align="center" size="25"]
	 * ------------------------------------------------------------------ */

	public function register_shortcode(): void {
		add_shortcode( self::SHORTCODE, array( $this, 'shortcode' ) );
	}

	public function shortcode( $atts ): string {
		$defaults = self::defaults();
		$atts     = shortcode_atts( $defaults, (array) $atts, self::SHORTCODE );

		foreach ( array( 'new_tab', 'nofollow' ) as $flag ) {
			if ( is_string( $atts[ $flag ] ) ) {
				$atts[ $flag ] = ! in_array( strtolower( $atts[ $flag ] ), array( '0', 'false', 'no', '' ), true );
			}
		}
		$atts['inline_size'] = true;

		$this->enqueue_assets();
		return self::render( $atts );
	}

	/* --------------------------------------------------------------------
	 * Elementor
	 * ------------------------------------------------------------------ */

	public function register_elementor_category( $elements_manager ): void {
		$elements_manager->add_category(
			'karnisha',
			array(
				'title' => esc_html__( 'Karnisha', 'karnisha-powerby' ),
				'icon'  => 'eicon-site-logo',
			)
		);
	}

	public function register_elementor_widget( $widgets_manager ): void {
		require_once KPB_PATH . 'includes/class-kpb-elementor-widget.php';
		$widgets_manager->register( new KPB_Elementor_Widget() );
	}

	public function maybe_notice_missing_elementor(): void {
		if ( did_action( 'elementor/loaded' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'plugins' !== $screen->id ) {
			return;
		}
		printf(
			'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
			esc_html__( 'Karnisha Power By: Elementor is not active, so the widget is unavailable. You can still use the [karnisha_powerby] shortcode.', 'karnisha-powerby' )
		);
	}
}
