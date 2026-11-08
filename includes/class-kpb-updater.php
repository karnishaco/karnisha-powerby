<?php
/**
 * Self-updater: checks GitHub Releases of karnishaco/karnisha-powerby and
 * hands new versions to WordPress' normal update system (Dashboard > Updates,
 * the Plugins screen and background auto-updates).
 *
 * Public repository: works with no configuration.
 * Private repository: define( 'KPB_GITHUB_TOKEN', 'github_pat_...' ); in wp-config.php
 * (a fine-grained token with read-only "Contents" access to this one repository).
 *
 * Auto-updates are on by default for this plugin. To turn them off on a site:
 * define( 'KPB_AUTO_UPDATE', false ); in wp-config.php
 *
 * @package Karnisha_PowerBy
 */

defined( 'ABSPATH' ) || exit;

final class KPB_Updater {

	const REPO      = 'karnishaco/karnisha-powerby';
	const SLUG      = 'karnisha-powerby';
	const ASSET     = 'karnisha-powerby.zip';
	const CACHE_KEY = 'kpb_github_release';
	const CACHE_TTL = 6 * HOUR_IN_SECONDS;

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
		add_filter( 'upgrader_pre_download', array( $this, 'download_private_asset' ), 10, 3 );
		add_filter( 'http_request_args', array( $this, 'github_auth' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'purge_cache' ), 10, 2 );

		// "Check for updates" link on the Plugins screen.
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
		add_action( 'load-plugins.php', array( $this, 'handle_manual_check' ) );
		add_action( 'admin_notices', array( $this, 'manual_check_notice' ) );
	}

	private function token(): string {
		$token = defined( 'KPB_GITHUB_TOKEN' ) ? (string) KPB_GITHUB_TOKEN : '';
		return (string) apply_filters( 'kpb_github_token', $token );
	}

	/**
	 * Latest published release (cached). Returns null when unavailable.
	 */
	public function get_release( bool $force = false ): ?array {
		if ( ! $force ) {
			$cached = get_site_transient( self::CACHE_KEY );
			if ( is_array( $cached ) ) {
				return $cached ? $cached : null;
			}
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . self::REPO . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = 200 === $code ? json_decode( (string) wp_remote_retrieve_body( $response ), true ) : null;

		if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
			// Remember the failure for a short while so we don't hammer GitHub.
			set_site_transient( self::CACHE_KEY, array(), HOUR_IN_SECONDS );
			return null;
		}

		$package = '';
		foreach ( (array) ( $data['assets'] ?? array() ) as $asset ) {
			if ( isset( $asset['name'] ) && self::ASSET === $asset['name'] ) {
				// Private repos need the API URL (downloaded with the token), public ones the direct URL.
				$package = '' !== $this->token() ? (string) $asset['url'] : (string) $asset['browser_download_url'];
				break;
			}
		}

		$release = array(
			'version'   => ltrim( (string) $data['tag_name'], 'vV' ),
			'package'   => $package,
			'url'       => (string) ( $data['html_url'] ?? 'https://github.com/' . self::REPO ),
			'changelog' => (string) ( $data['body'] ?? '' ),
			'date'      => (string) ( $data['published_at'] ?? '' ),
		);

		set_site_transient( self::CACHE_KEY, $release, self::CACHE_TTL );
		return $release;
	}

	private function plugin_object( array $release ): object {
		return (object) array(
			'id'           => 'github.com/' . self::REPO,
			'slug'         => self::SLUG,
			'plugin'       => $this->basename,
			'new_version'  => $release['version'],
			'url'          => 'https://github.com/' . self::REPO,
			'package'      => $release['package'],
			'icons'        => array( 'svg' => KPB_URL . 'assets/images/logo.svg' ),
			'requires_php' => '7.4',
		);
	}

	public function inject_update( $transient ) {
		$release = $this->get_release();
		if ( ! $release ) {
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

		if ( '' !== $release['package'] && version_compare( $release['version'], KPB_VERSION, '>' ) ) {
			$transient->response[ $this->basename ] = $this->plugin_object( $release );
			unset( $transient->no_update[ $this->basename ] );
		} else {
			// Listing it under no_update makes the "Enable auto-updates" toggle appear.
			$current                                 = $this->plugin_object( array_merge( $release, array( 'version' => KPB_VERSION ) ) );
			$transient->no_update[ $this->basename ] = $current;
			unset( $transient->response[ $this->basename ] );
		}

		return $transient;
	}

	/** "View details" popup. */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || self::SLUG !== $args->slug ) {
			return $result;
		}

		$release = $this->get_release();
		$version = $release ? $release['version'] : KPB_VERSION;

		return (object) array(
			'name'          => 'Karnisha Power By',
			'slug'          => self::SLUG,
			'version'       => $version,
			'author'        => 'Karnisha',
			'homepage'      => 'https://github.com/' . self::REPO,
			'requires'      => '5.8',
			'requires_php'  => '7.4',
			'last_updated'  => $release ? $release['date'] : '',
			'download_link' => $release ? $release['package'] : '',
			'sections'      => array(
				'description' => esc_html__( 'Karnisha "designed & developed by" footer badge as an Elementor widget and a shortcode.', 'karnisha-powerby' ),
				'changelog'   => $release && '' !== $release['changelog']
					? wpautop( esc_html( $release['changelog'] ) )
					: esc_html__( 'See the releases page on GitHub.', 'karnisha-powerby' ),
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

	/** Send the token with requests to this repository's API (private repos). */
	public function github_auth( $args, $url ) {
		$token = $this->token();
		if ( '' === $token || 0 !== strpos( (string) $url, 'https://api.github.com/repos/' . self::REPO . '/' ) ) {
			return $args;
		}
		$args['headers']                  = isset( $args['headers'] ) ? (array) $args['headers'] : array();
		$args['headers']['Authorization'] = 'Bearer ' . $token;
		return $args;
	}

	/**
	 * Private repos: release assets come from the API URL, which redirects to a
	 * signed download URL that must be fetched *without* the Authorization header.
	 */
	public function download_private_asset( $reply, $package, $upgrader ) {
		$prefix = 'https://api.github.com/repos/' . self::REPO . '/releases/assets/';
		if ( false !== $reply || 0 !== strpos( (string) $package, $prefix ) ) {
			return $reply;
		}

		$response = wp_remote_get(
			$package,
			array(
				'timeout'     => 30,
				'redirection' => 0,
				'headers'     => array( 'Accept' => 'application/octet-stream' ),
			)
		);
		$location = wp_remote_retrieve_header( $response, 'location' );

		if ( is_wp_error( $response ) || ! $location ) {
			return new WP_Error( 'kpb_download_failed', __( 'Could not download the update from GitHub. Check KPB_GITHUB_TOKEN.', 'karnisha-powerby' ) );
		}

		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		return download_url( is_array( $location ) ? end( $location ) : $location, 300 );
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

		$release = $this->get_release( true );
		delete_site_transient( 'update_plugins' );
		wp_update_plugins();

		if ( ! $release ) {
			$status = 'error';
		} elseif ( version_compare( $release['version'], KPB_VERSION, '>' ) ) {
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
			'error'     => array( 'error', __( 'Karnisha Power By: could not reach GitHub. Try again later.', 'karnisha-powerby' ) ),
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
