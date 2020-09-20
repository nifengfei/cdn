<?php /*

**************************************************************************

Plugin Name:  BBCode
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/bbcode/
Description:  Implements <a href="http://en.wikipedia.org/wiki/BBCode">BBCode</a> in posts. Requires WordPress 2.5+ or WPMU 1.5+.
Version:      1.0.1
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Copyright (C) 2008 Viper007Bond

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class BBCode {

	// Plugin initialization
	function BBCode() {
		// This version only supports WP 2.5+ (learn to upgrade please!)
		if ( !function_exists('add_shortcode') ) return;

		// Register the shortcodes
		add_shortcode( 'b' , array(&$this, 'shortcode_bold') );
		add_shortcode( 'i' , array(&$this, 'shortcode_italics') );
		add_shortcode( 'u' , array(&$this, 'shortcode_underline') );
		add_shortcode( 'url' , array(&$this, 'shortcode_url') );
		add_shortcode( 'img' , array(&$this, 'shortcode_image') );
		add_shortcode( 'quote' , array(&$this, 'shortcode_quote') );
	}


	// No-name attribute fixing
	function attributefix( $atts = array() ) {
		if ( empty($atts[0]) ) return $atts;

		if ( 0 !== preg_match( '#=("|\')(.*?)("|\')#', $atts[0], $match ) )
			$atts[0] = $match[2];

		return $atts;
	}


	// Bold shortcode
	function shortcode_bold( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		return '<strong>' . do_shortcode( $content ) . '</strong>';
	}


	// Italics shortcode
	function shortcode_italics( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		return '<em>' . do_shortcode( $content ) . '</em>';
	}


	// Italics shortcode
	function shortcode_underline( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		return '<span style="text-decoration:underline">' . do_shortcode( $content ) . '</span>';
	}


	// Italics shortcode
	function shortcode_url( $atts = array(), $content = NULL ) {
		$atts = $this->attributefix( $atts );

		// [url="http://www.google.com/"]Google[/url]
		if ( isset($atts[0]) ) {
			$url = $atts[0];
			$text = $content;
		}
		// [url]http://www.google.com/[/url]
		else {
			$url = $text = $content;
		}

		if ( empty($url) ) return '';
		if ( empty($text) ) $text = $url;

		return '<a href="' . $url . '">' . do_shortcode( $text ) . '</a>';
	}


	// Italics shortcode
	function shortcode_image( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		return '<img src="' . $content . '" alt="" />';
	}


	// Italics shortcode
	function shortcode_quote( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		return '<blockquote>' . do_shortcode( $content ) . '</blockquote>';
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $BBCode; $BBCode = new BBCode();' ) );

?>