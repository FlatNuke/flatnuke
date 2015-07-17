<?php 

	// Captcha image system for Simple PHP Blog

	if ( !session_id() ) {
		session_start();
	}

	if ( !isset( $_SESSION[ 'security_code' ] ) ) { /* @author Marco Segato - 20051221 - FN adapted */
		exit;
	}

	//require_once('scripts/sb_utility.php'); // for get_capcha

	// Configuration
	$CONFIG[ 'font_id' ] = 5;
	$CONFIG[ 'width' ] = 120;
	$CONFIG[ 'height' ] = 30;

	function rgb_grayscale( $rgb ) {
		$color[ 'r' ] = 0.299 * $rgb[ 'r' ] + 0.587 * $rgb[ 'g' ] + 0.114 * $rgb[ 'b' ];
		$color[ 'g' ] = 0.299 * $rgb[ 'r' ] + 0.587 * $rgb[ 'g' ] + 0.114 * $rgb[ 'b' ];
		$color[ 'b' ] = 0.299 * $rgb[ 'r' ] + 0.587 * $rgb[ 'g' ] + 0.114 * $rgb[ 'b' ];
		return $color;
	}

	function rgb_complementary( $rgb ) {
		$color[ 'r' ] = 255 - $rgb[ 'r' ];
		$color[ 'g' ] = 255 - $rgb[ 'g' ];
		$color[ 'b' ] = 255 - $rgb[ 'b' ];
		return $color;
	}

	function rgb_rand( $min=0, $max=255 ) {
		$color[ 'r' ] = rand( $min, $max );
		$color[ 'g' ] = rand( $min, $max );
		$color[ 'b' ] = rand( $min, $max );
		return $color;
	}

	function rgb_create( $r=0, $g=0, $b=0 ) {
		$color[ 'r' ] = $r;
		$color[ 'g' ] = $g;
		$color[ 'b' ] = $b;
		return $color;
	}

	function rgb_merge( $lhs, $rhs ) {
		$color[ 'r' ] = ( $lhs[ 'r' ] + $rhs[ 'r' ] ) >> 1;
		$color[ 'g' ] = ( $lhs[ 'g' ] + $rhs[ 'g' ] ) >> 1;
		$color[ 'b' ] = ( $lhs[ 'b' ] + $rhs[ 'b' ] ) >> 1;
		return $color;
	}

	$text = $_SESSION[ 'security_code' ];	/* @author Marco Segato - 20051221 - FN adapted */
	srand( ( double ) microtime() * 1000000 );

	// Creates a simple image
	$image = imagecreate($CONFIG[ 'width' ], $CONFIG[ 'height' ]);

	// Create random colors
	$rgb = array();
	$rgb[ 'background' ] = rgb_rand( 0, 255 );
	$rgb[ 'foreground' ] = rgb_grayscale( rgb_complementary( $rgb[ 'background' ] ) );
    if ( $rgb[ 'foreground' ][ 'r' ] > 127 ) {
		$inicio = -127;
		$rgb[ 'foreground' ] = rgb_merge( $rgb[ 'foreground' ], rgb_create( 255, 255, 255 ) );
		$rgb[ 'shadow' ] = rgb_merge( rgb_complementary( $rgb[ 'foreground' ] ), rgb_create( 0, 0, 0 ) );
    } else {
		$inicio = 0;
		$rgb[ 'foreground' ] = rgb_merge( $rgb[ 'foreground' ], rgb_create( 0, 0, 0 ) );
		$rgb[ 'shadow' ] = rgb_merge( rgb_complementary( $rgb[ 'foreground' ] ), rgb_create( 255, 255, 255 ) );
	} // if

	// Allocate color resources
	$color = array();
	foreach($rgb as $name => $value) {
		$color[$name] = imagecolorallocate( $image, $value[ 'r' ], $value[ 'g' ], $value[ 'b' ] );
	} // foreach

	// Draw background
	imagefilledrectangle( $image, 0, 0, 120, 30, $color[ 'background' ] );
	// Write some random text on background
	for ( $i = 0; $i < rand( 5, 9 ); $i++ ) {
		$x = rand( 0, $CONFIG[ 'width' ] );
		$y = rand( 0, $CONFIG[ 'height' ] );
		$f = rand( 0, 5 );
		$c = rgb_grayscale( rgb_rand( 127 - $inicio, 254 - $inicio ) );
		$color[$i] = imagecolorallocate( $image, $c[ 'r' ], $c[ 'g' ], $c[ 'b' ] );
		imagestring( $image, $f, $x, $y, $text, $color[$i] );
	}

	// Center the real captcha text
	$x  = ( $CONFIG[ 'width' ] - ( ImageFontWidth($CONFIG[ 'font_id' ] ) * strlen($text) ) ) >> 1;
	$y  = ( $CONFIG[ 'height' ] - ImageFontHeight($CONFIG[ 'font_id' ] ) ) >> 1;

	// Write the captcha text with shadow (improves the human vision in some cases)
	imagestring( $image, $CONFIG[ 'font_id' ], $x + 1, $y + 1, $text, $color[ 'shadow' ] );
	imagestring( $image, $CONFIG[ 'font_id' ], $x, $y, $text, $color[ 'foreground' ] );

	// Returns the image
	header( 'Content-type: image/png' );
	imagepng( $image );

	// Free resources
	foreach($color as $name => $value) {
		imagecolordeallocate( $image, $value );
	} // foreach
	imagedestroy( $image );
?>
