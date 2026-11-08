<?php
/**
 * Sign / verify the release ZIP (used by GitHub Actions).
 *
 *   KPB_SIGNING_KEY=<base64 secret key> php bin/sign.php sign   karnisha-powerby.zip > karnisha-powerby.zip.sig
 *   php bin/sign.php verify karnisha-powerby.zip karnisha-powerby.zip.sig
 *
 * verify uses the public key baked into includes/class-kpb-updater.php, so it
 * also proves the secret on GitHub matches the key shipped in the plugin.
 */

if ( ! function_exists( 'sodium_crypto_sign_detached' ) ) {
	fwrite( STDERR, "PHP sodium extension is required.\n" );
	exit( 1 );
}

$cmd  = $argv[1] ?? '';
$file = $argv[2] ?? '';
$data = is_file( $file ) ? file_get_contents( $file ) : false;
if ( false === $data ) {
	fwrite( STDERR, "Usage: sign.php sign|verify <zip> [<sig>]\n" );
	exit( 1 );
}

if ( 'sign' === $cmd ) {
	$key = base64_decode( (string) getenv( 'KPB_SIGNING_KEY' ), true );
	if ( false === $key || SODIUM_CRYPTO_SIGN_SECRETKEYBYTES !== strlen( $key ) ) {
		fwrite( STDERR, "KPB_SIGNING_KEY is missing or not a valid base64 Ed25519 secret key.\n" );
		exit( 1 );
	}
	echo base64_encode( sodium_crypto_sign_detached( $data, $key ) );
	exit( 0 );
}

if ( 'verify' === $cmd ) {
	$src = file_get_contents( __DIR__ . '/../includes/class-kpb-updater.php' );
	if ( ! preg_match( "/const PUBLIC_KEY = '([^']+)'/", $src, $m ) ) {
		fwrite( STDERR, "PUBLIC_KEY not found in class-kpb-updater.php\n" );
		exit( 1 );
	}
	$sig = base64_decode( trim( (string) file_get_contents( $argv[3] ?? '' ) ), true );
	$ok  = false !== $sig && 64 === strlen( $sig ) && sodium_crypto_sign_verify_detached( $sig, $data, base64_decode( $m[1] ) );
	fwrite( $ok ? STDOUT : STDERR, $ok ? "Signature OK\n" : "Signature does NOT match the plugin's PUBLIC_KEY\n" );
	exit( $ok ? 0 : 1 );
}

fwrite( STDERR, "Unknown command\n" );
exit( 1 );
