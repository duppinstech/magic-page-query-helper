<?php
/**
 * Post-edit hook. Runs php -l on a PHP file named in the tool input.
 * Exits 0 when there is nothing to lint.
 */

$raw  = stream_get_contents( STDIN );
$data = json_decode( $raw ? $raw : '', true );
$path = '';

if ( is_array( $data ) ) {
	$input = isset( $data['tool_input'] ) && is_array( $data['tool_input'] ) ? $data['tool_input'] : array();
	if ( isset( $input['file_path'] ) && is_string( $input['file_path'] ) ) {
		$path = $input['file_path'];
	} elseif ( isset( $input['path'] ) && is_string( $input['path'] ) ) {
		$path = $input['path'];
	}
}

if ( '' === $path || '.php' !== substr( $path, -4 ) || ! is_file( $path ) ) {
	exit( 0 );
}

$command = escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $path );
passthru( $command, $code );
exit( (int) $code );
