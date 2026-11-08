<?php
/**
 * Self-updater: reads a small JSON manifest (info.json) from Karnisha's
 * download host and hands new versions to WordPress' normal update system
 * (Dashboard > Updates, the Plugins screen and background auto-updates).
 *
 * The host folder holds two files, both attached to every GitHub Release:
 *   info.json             — version, download_url, changelog…
 *   karnisha-powerby.zip  — the plugin
 *
 * Optional, in wp-config.php:
 *   define( 'KPB_UPDATE_URL', 'https://…/info.json' ); // use another manifest
 *   define( 'KPB_AUTO_UPDATE', false );                // no background auto-updates on this site
 *
 * @package Karnisha_PowerBy
 */

defined( 'ABSPATH' ) || exit;

final class KPB_Updater {

	const MANIFEST_URL = 'https://cityseo.ir/cloud/karnisha/plugins/powerby/info.json';
	const SLUG         = 'karnisha-powerby';
	const CACHE_KEY    = 'kpb_update_info';
	const CACHE_TTL    = 6 * HOUR_IN_SECONDS;

	/** @var string e.g. karnisha-powerby/karnisha-powerby.php */
	private $basename;

	public function __construct( string $plugin_file ) {
		$this->basename = plugin_basename( $plugin_file );

		// Inject on save *and* on read: if WordPress.org is unreachable from the host,
		// WordPress never saves the update transient, but our update still shows up.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'inject_update' ) );
		add_filter( 'site_transient_update_plugins', array( $this, 'inject_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'auto_update_plugin', array( $this, 'auto_update' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'purge_cache' ), 10, 2 );

		// "Check for updates" link on the Plugins screen.
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
		add_action( 'load-plugins.php', array( $this, 'handle_manual_check' ) );
		add_action( 'admin_notices', array( $this, 'manual_check_notice' ) );
	}

	private function manifest_url(): string {
		$url = defined( 'KPB_UPDATE_URL' ) ? (string) KPB_UPDATE_URL : self::MANIFEST_URL;
		return (string) apply_filters( 'kpb_update_url', $url );
	}

	/**
	 * Latest version info from the manifest (cached). Null when unavailable.
	 */
	public function get_info( bool $force = false ): ?array {
		if ( ! $force ) {
			$cached = get_site_transient( self::CACHE_KEY );
			if ( is_array( $cached ) ) {
				return $cached ? $cached : null;
			}
		}

		// Hourly cache-buster so a CDN/browser cache on the host can't hide a new version for long.
		$url      = add_query_arg( 't', (string) floor( time() / HOUR_IN_SECONDS ), $this->manifest_url() );
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
		$code     = (int) wp_remote_retrieve_response_code( $response );
		$body     = 200 === $code ? (string) wp_remote_retrieve_body( $response ) : '';
		$body     = preg_replace( '/^\xEF\xBB\xBF/', '', $body ); // Strip a BOM added by some editors.
		$data     = '' !== $body ? json_decode( $body, true ) : null;

		if ( ! is_array( $data ) || empty( $data['version'] ) ) {
			// Remember the failure for a short while so sites don't keep retrying.
			set_site_transient( self::CACHE_KEY, array(), HOUR_IN_SECONDS );
			return null;
		}

		$package = (string) ( $data['download_url'] ?? '' );
		if ( '' === $package ) {
			$package = trailingslashit( dirname( $this->manifest_url() ) ) . self::SLUG . '.zip';
		}

		$info = array(
			'version'      => ltrim( (string) $data['version'], 'vV' ),
			'package'      => esc_url_raw( $package ),
			'changelog'    => (string) ( $data['changelog'] ?? '' ),
			'date'         => (string) ( $data['last_updated'] ?? '' ),
			'requires'     => (string) ( $data['requires'] ?? '5.8' ),
			'requires_php' => (string) ( $data['requires_php'] ?? '7.4' ),
			'tested'       => (string) ( $data['tested'] ?? '' ),
		);

		set_site_transient( self::CACHE_KEY, $info, self::CACHE_TTL );
		return $info;
	}

	private function plugin_object( array $info, string $version ): object {
		return (object) array(
			'id'           => self::SLUG,
			'slug'         => self::SLUG,
			'plugin'       => $this->basename,
			'new_version'  => $version,
			'url'          => 'https://github.com/karnishaco/karnisha-powerby',
			'package'      => $info['package'],
			'icons'        => array( 'svg' => KPB_URL . 'assets/images/logo.svg' ),
			'requires'     => $info['requires'],
			'requires_php' => $info['requires_php'],
			'tested'       => $info['tested'],
		);
	}

	public function inject_update( $transient ) {
		$info = $this->get_info();
		if ( ! $info ) {
			return $transient;
		}

		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}
		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = array();
		}
		if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
			$transient->no_update = array();
		}

		if ( '' !== $info['package'] && version_compare( $info['version'], KPB_VERSION, '>' ) ) {
			$transient->response[ $this->basename ] = $this->plugin_object( $info, $info['version'] );
			unset( $transient->no_update[ $this->basename ] );
		} else {
			// Listing it under no_update makes the "Enable auto-updates" toggle appear.
			$transient->no_update[ $this->basename ] = $this->plugin_object( $info, KPB_VERSION );
			unset( $transient->response[ $this->basename ] );
		}

		return $transient;
	}

	/** "View details" popup. */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || self::SLUG !== $args->slug ) {
			return $result;
		}

		$info = $this->get_info();

		return (object) array(
			'name'          => 'Karnisha Power By',
			'slug'          => self::SLUG,
			'version'       => $info ? $info['version'] : KPB_VERSION,
			'author'        => 'Karnisha',
			'homepage'      => 'https://github.com/karnishaco/karnisha-powerby',
			'requires'      => $info ? $info['requires'] : '5.8',
			'requires_php'  => $info ? $info['requires_php'] : '7.4',
			'tested'        => $info ? $info['tested'] : '',
			'last_updated'  => $info ? $info['date'] : '',
			'download_link' => $info ? $info['package'] : '',
			'sections'      => array(
				'description' => esc_html__( 'Karnisha "designed & developed by" footer badge as an Elementor widget and a shortcode.', 'karnisha-powerby' ),
				'changelog'   => $info && '' !== $info['changelog']
					? wpautop( esc_html( $info['changelog'] ) )
					: esc_html__( 'See CHANGELOG.md in the GitHub repository.', 'karnisha-powerby' ),
			),
		);
	}

	/** Auto-update this plugin unless the site opts out. */
	public function auto_update( $update, $item ) {
		if ( isset( $item->plugin ) && $item->plugin === $this->basename ) {
			if ( defined( 'KPB_AUTO_UPDATE' ) ) {
				return (bool) KPB_AUTO_UPDATE;
			}
			return (bool) apply_filters( 'kpb_auto_update', true );
		}
		return $update;
	}

	public function purge_cache( $upgrader, $options ): void {
		if ( isset( $options['type'] ) && 'plugin' === $options['type'] ) {
			delete_site_transient( self::CACHE_KEY );
		}
	}

	/* ---------------- "Check for updates" link ---------------- */

	public function row_meta( $links, $file ) {
		if ( $file === $this->basename && current_user_can( 'update_plugins' ) ) {
			$url     = wp_nonce_url( admin_url( 'plugins.php?kpb_check_update=1' ), 'kpb_check_update' );
			$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Check for updates', 'karnisha-powerby' ) . '</a>';
		}
		return $links;
	}

	public function handle_manual_check(): void {
		if ( empty( $_GET['kpb_check_update'] ) || ! current_user_can( 'update_plugins' ) ) {
			return;
		}
		check_admin_referer( 'kpb_check_update' );

		$info = $this->get_info( true );
		delete_site_transient( 'update_plugins' );
		wp_update_plugins();

		if ( ! $info ) {
			$status = 'error';
		} elseif ( version_compare( $info['version'], KPB_VERSION, '>' ) ) {
			$status = 'available';
		} else {
			$status = 'latest';
		}

		wp_safe_redirect( admin_url( 'plugins.php?kpb_checked=' . $status ) );
		exit;
	}

	public function manual_check_notice(): void {
		$status = isset( $_GET['kpb_checked'] ) ? sanitize_key( wp_unslash( $_GET['kpb_checked'] ) ) : '';
		if ( '' === $status ) {
			return;
		}

		$messages = array(
			'available' => array( 'warning', __( 'Karnisha Power By: a new version is available. Use the "update now" link below.', 'karnisha-powerby' ) ),
			'latest'    => array( 'success', __( 'Karnisha Power By is up to date.', 'karnisha-powerby' ) ),
			'error'     => array( 'error', __( 'Karnisha Power By: could not reach the update server. Try again later.', 'karnisha-powerby' ) ),
		);
		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $messages[ $status ][0] ),
			esc_html( $messages[ $status ][1] )
		);
	}
}
