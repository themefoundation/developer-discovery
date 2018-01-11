<?php
/*
Plugin Name: Developer Discovery
Plugin URI: http://themefoundation.com
Description: This plugin displays additional developer information on the front end.
Author: Alex Mansfield
Version: 0.1.0
Author URI: http://themefoundation.com

@see http://jquerymodal.com/
*/
class THMFDN_Developer_Discovery {

	/**
	 * Class constructor
	 *
	 * Adds actions/filters to hooks.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'developer_discovery_div') );

		add_action( 'thmfdn_particle_before', array( $this, 'thmfdn_discovery_particle_before') );
		add_action( 'thmfdn_particle_after', array( $this, 'thmfdn_discovery_after') );
		add_action( 'thmfdn_template_part_before',  array( $this, 'thmfdn_discovery_template_part_before'), 10, 3 );
		add_action( 'thmfdn_template_part_after',  array( $this, 'thmfdn_discovery_after') );
		add_action( 'thmfdn_extend_template_before',  array( $this, 'thmfdn_discovery_extend_template_before'), 10, 1 );

		add_action( 'thmfdn_comments_before',  array( $this, 'thmfdn_discovery_comments_before') );
		add_action( 'thmfdn_comments_after',  array( $this, 'thmfdn_discovery_after') );

		add_action( 'dynamic_sidebar_before', array( $this, 'sidebar_before') );
		add_action( 'dynamic_sidebar_after', array( $this, 'thmfdn_discovery_after') );
	}

	/**
	 * Enqueues styles and scripts
	 */
	public function enqueue() {
		wp_enqueue_style( 'jquery-modal',  plugin_dir_url( __FILE__ ) . 'jquery.modal.css' );
		wp_enqueue_style( 'developer-discovery',  plugin_dir_url( __FILE__ ) . 'developer-discovery.css' );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-modal',  plugin_dir_url( __FILE__ ) . 'jquery.modal.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'developer-discovery',  plugin_dir_url( __FILE__ ) . 'developer-discovery.js', array( 'jquery' ), false, true );
	}

	/**
	 * Creates developer discover div
	 *
	 * The plugin populates this div with content as needed.
	 */
	public function developer_discovery_div() {
		global $thmfdn_developer_discovery;
		if ( empty( $thmfdn_developer_discovery ) ) {
			$thmfdn_developer_discovery = array();
		}

		$details['path'] = $this->get_current_template();
		$details['handle'] = basename( $details['path'] );
		$split_path = explode( 'wp-content/', $details['path'] );
		$details['table']['Path'] = end( $split_path );

		array_unshift( $thmfdn_developer_discovery, $details );
		$json_details =  json_encode( $thmfdn_developer_discovery );
		//echo wpautop( thmfdn_get_file_doc_block(thmfdn_get_current_template()));
		?>
			<div id="dd-data" data-discovery='<?php echo $json_details; ?>'></div>
		<?php
	}

	/**
	 * Gets the path to the current template file, as determined by WordPress
	 *
	 * @see http://wordpress.stackexchange.com/questions/10537/get-name-of-the-current-template-file
	 */
	public function get_current_template() {
		if( !empty( $GLOBALS['current_theme_template'] ) ) {
			$template = $GLOBALS['current_theme_template'];
		} else {
			$template = false;
		}

		return $template;
	}

	/**
	 * Opens particle wrapper div
	 */
	public function thmfdn_discovery_particle_before( $id ) {
		$details = $this->get_particle_details( $id );
		$details_json = json_encode( $details );
		$class = 'thmfdn-discovery dd-definition';
		if ( !empty( $details['display'] ) && 'inline' == $details['display'] ) {
			$class .= ' dd-inline';
		}
		echo '<div class="' . $class . '" data-discovery=\'' . $details_json . '\'>';
		// echo '<a class="thmfdn-discovery-expand" href="#"></a>';
	}

	/**
	 * Opens template part wrapper div
	 */
	public function thmfdn_discovery_template_part_before( $slug, $name = '', $parent = false ) {
		echo '<div class="thmfdn-discovery dd-definition" data-discovery=\'' . $this->get_template_part_details_json( $slug, $name, $parent ) . '\'>';
		// echo '<a class="thmfdn-discovery-expand" href="#"></a>';
	}

	/**
	 * Opens sidebar wrapper div
	 */
	public function sidebar_before( $id ) {
		global $wp_registered_sidebars;

		// Gets sidebar details.
		$details = array();
		$details['title'] = $wp_registered_sidebars[$id]['name'];
		// $details['description'] = 'This function calls a registered sidebar.';
		// $details['path'] = locate_template( $template_name, false );
		$details['table']['Name'] = $wp_registered_sidebars[$id]['name'];
		$details['table']['Description'] = $wp_registered_sidebars[$id]['description'];
 		$details['table']['Type'] = 'WordPress function';
 		$details['table']['Docs'] = '<a href="https://developer.wordpress.org/reference/functions/dynamic_sidebar/">https://developer.wordpress.org/reference/functions/dynamic_sidebar/</a>';
		// $path_array = explode( 'wp-content/', $details['path'] );
 		// $details['table']['Path'] = end( $path_array );
		$details['handle'] = 'dynamic_sidebar()';
		// echo '<pre>';
		// print_r($wp_registered_sidebars[$id]);
		// echo '</pre>';
		// $details = $this->get_particle_details( $id );
		$details_json = json_encode( $details );
		$class = 'thmfdn-discovery dd-definition';
		// if ( !empty( $details['display'] ) && 'inline' == $details['display'] ) {
		// 	$class .= ' dd-inline';
		// }
		echo '<div class="' . $class . '" data-discovery=\'' . $details_json . '\'>';
		// echo '<a class="thmfdn-discovery-expand" href="#"></a>';
	}

	public function thmfdn_discovery_extend_template_before( $template_name ) {
		global $thmfdn_developer_discovery;

		// Gets template details.
		$details = array();
		$details['title'] = 'Template File';
		$details['path'] = locate_template( $template_name, false );
 		$details['table']['Type'] = 'Template';
		$path_array = explode( 'wp-content/', $details['path'] );
 		$details['table']['Path'] = end( $path_array );
		$details['handle'] = basename( $template_name );

		$thmfdn_developer_discovery[] = $details;
	}

	/**
	 * Opens comments wrapper div
	 */
	public function thmfdn_discovery_comments_before() {
		$discovery_details = array(
			'path' => 'comments.php',
			'handle' => 'comments.php',
			'description' => 'This is the comments template. WordPress loads this template using the comments_template() function.',
			'table' => array(
				'Docs' => '<a href="https://developer.wordpress.org/reference/functions/comments_template/">https://developer.wordpress.org/reference/functions/comments_template/</a>',
			),
		);
		echo '<div class="thmfdn-discovery dd-definition" data-discovery=\'' . json_encode( $discovery_details ) . '\'>';
		// echo '<a class="thmfdn-discovery-expand" href="#"></a>';
	}

	/**
	 * Closes wrapper div
	 */
	public function thmfdn_discovery_after() {
		echo '</div>';
	}

	/**
	 * Gets details about a particle
	 */
	public function get_particle_details( $id ) {
		global $thmfdn_particles;

		$defaults = array(
			'id' => '',
			'name' => '',
			'description' => __( '', 'thmfdn_textdomain' ),
			'reflection' => '',
			'display' => '',
		);

		$particle = wp_parse_args( $thmfdn_particles->particles[$id]->particle, $defaults );
		$particle['path'] = $particle['reflection']->getFileName();
		$details['handle'] = basename( $particle['path'] );
		$details['display'] = $particle['display'];

		// Sets up args capture.
		$arg_string = '';
		$arg_first = true;

		// Loops through args.
		if ( !empty(  $particle['args'] ) && is_array(  $particle['args'] ) ) {
			foreach ( $particle['args'] as $arg ) {
				if ( $arg_first ) {
					$arg_string .= '$' . $arg;
					$arg_first = false;
				} else {
					$arg_string .= ', $' . $arg;
				}
			}
		}

		// Gets additional particle details
		$details['table']['Type'] = 'Particle';
		$details['table']['Title'] = $particle['name'];
		$details['table']['Description'] = $particle['description'];
		$path_array = explode( 'wp-content/', $particle['path'] );
 		$details['table']['Path'] = end( $path_array );
		// $details['table']['Function'] = $particle['function'] . '( ' . $arg_string . ' )';
		// $details['table']['Line #'] = $reflection->getStartLine();

		return $details;
	}

	/**
	 * Gets details about a template part
	 */
	public function get_template_part_details_json( $slug, $name = '', $parent = false ) {

		// Gets template part details.
		$details = array();
		$details['title'] = 'Template Part';
		$details['table']['Type'] = 'Template Part';
		$details['table']['Path'] = ( empty( $name ) ) ? $slug . '.php' : $slug . '-' . $name . '.php';
		$details['table']['Slug'] = $slug;
		$details['table']['Name'] = $name;
		$details['table']['Parent'] = $parent;
		$details['handle'] = basename( $details['table']['Path'] );

		return json_encode( $details );
	}


}

/**
 * Initializes the plugin class if conditions are met
 */
function thmfdn_developer_discovery_init() {
	if ( !is_admin() ) {
		if ( current_user_can( 'manage_options' ) ) {
			if ( 'on' == get_user_option( 'developer_discovery_status' ) ) {
				$thmfdn_developer_discovery = new THMFDN_Developer_Discovery;
			}
		}
	}
}
add_action( 'init', 'thmfdn_developer_discovery_init' );

/**
 * Stores the path to the current template file globally for later use
 */
function thmfdn_store_current_template( $template ){
	$GLOBALS['current_theme_template'] = $template;
	return $template;
}
add_filter( 'template_include', 'thmfdn_store_current_template', 1000 );

/**
 * Adds discovery toggle to WP admin bar
 */
function thmfdn_developer_discovery_toggle( $wp_admin_bar ) {
	if ( !is_admin() ) {
		global $wp;

		$current_discovery_status = get_user_option( 'developer_discovery_status' );

		if ( empty( $current_discovery_status ) || 'off' == $current_discovery_status ) {
			$toggled_discovery_status = 'on';
			$button_title = 'Turn Discovery On';
		} else {
			$toggled_discovery_status = 'off';
			$button_title = 'Turn Discovery Off';
		}



		if ( $_SERVER['QUERY_STRING'] ) {
			$prefix = '?';
			$url = home_url( $wp->request ) . '/';
			$query_args_array = array();
			parse_str( $_SERVER['QUERY_STRING'], $query_args_array );

			foreach ( $query_args_array as $key => $value ) {
				if ( $key != 'developer-discovery-status' ) {
					$url .= $prefix;
					$url .= $key . '=' . $value;
					$prefix = '&';
				}
			}

			$url .= $prefix . 'developer-discovery-status=' . $toggled_discovery_status;
		} else {
			$url .= home_url( $wp->request ) . '?developer-discovery-status=' . $toggled_discovery_status;
		}

		$args = array(
			'id' => 'dd-toggle',
			'title' => $button_title,
			'href' =>  $url,
			'parent' => 'top-secondary',
			'meta' => array(
				'class' => 'dd-display-toggle'
			)
		);
		$wp_admin_bar->add_node($args);
	}
}
add_action( 'admin_bar_menu', 'thmfdn_developer_discovery_toggle', 50 );


/**
 * Updates saved discovery toggle state when changed by user
 */
function thmfdn_developer_discovery_update_toggle_state() {
	if ( !empty( $_GET['developer-discovery-status'] ) ) {

		if ( 'on' == $_GET['developer-discovery-status'] ) {
			update_user_option( get_current_user_id(), 'developer_discovery_status', 'on' );
		}

		if ( 'off' == $_GET['developer-discovery-status'] ) {
			update_user_option( get_current_user_id(), 'developer_discovery_status', 'off' );
		}
	}
}
add_action( 'init',  'thmfdn_developer_discovery_update_toggle_state', 1);











// $thmfdn_developer_discovery_count = 0;



// function thmfdn_discovery_template_part_bottom() {
// 	if ( current_user_can( 'manage_options' ) ) {
// 		echo '</div>';
// 	}
// }
// add_action( 'thmfdn_template_part_bottom', 'thmfdn_discovery_template_part_bottom' );


// function thmfdn_get_get_particle_details_json( $id ) {
// 		global $thmfdn_particles;
// 		$details = array();

// 		// Get file path;
// 		$reflection = new ReflectionFunction($thmfdn_particles->particles[$id]['function']);
// 		$full_path = $reflection->getFileName();
// 		$split_path = explode( 'wp-content/', $full_path );
// 		$useful_path = end( $split_path );

// 		$arg_string = '';
// 		$arg_first = true;

// 		foreach ( $thmfdn_particles->particles[$id]['args'] as $arg ) {
// 			if ( $arg_first ) {
// 				$arg_string .= '$' . $arg;
// 				$arg_first = false;
// 			} else {
// 				$arg_string .= ', $' . $arg;
// 			}
// 		}
// // echo '&&' . $thmfdn_particles->particles[$id]['title'] . '&&';
// 		$details['title'] = $thmfdn_particles->particles[$id]['title'];
// 		$details['description'] = $thmfdn_particles->particles[$id]['description'];
// 		$details['path'] = $useful_path;

// 		$json_details =  json_encode( $details );
// // echo '&&' . $json_details . '&&';

// 		return $json_details;
// }



// function thmfdn_get_discovery_particle_details( $id ) {
// 		global $thmfdn_particles;

// 		// print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
// 		// $e = new \Exception;
// 		// var_dump($e->getTraceAsString());
// 		//http://php.net/manual/en/exception.gettrace.php

// 		// $bt = debug_backtrace();
// 		// print_r($bt);
// 		// $caller = array_shift($bt);
// 		// echo $bt[0]['file'];
// 		// wp-content/

// 		$reflection = new ReflectionFunction($thmfdn_particles->particles[$id]['function']);
// 		$full_path = $reflection->getFileName();
// 		$split_path = explode( 'wp-content/', $full_path );
// 		$useful_path = end( $split_path );

// 		$arg_string = '';
// 		$arg_first = true;

// 		foreach ( $thmfdn_particles->particles[$id]['args'] as $arg ) {
// 			if ( $arg_first ) {
// 				$arg_string .= '$' . $arg;
// 				$arg_first = false;
// 			} else {
// 				$arg_string .= ', $' . $arg;
// 			}
// 		}


// 		// echo '<pre>';
// 		// print_r($thmfdn_particles->particles[$id]);
// 		// print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
// 		// $e = new \Exception;
// 		// var_dump($e->getTraceAsString());
// 		//http://php.net/manual/en/exception.gettrace.php

// 		// $bt = debug_backtrace();
// 		// print_r($bt);
// 		// $caller = array_shift($bt);
// 		// echo $bt[0]['file'];
// 		// wp-content/



// 		// $reflection = new ReflectionFunction($thmfdn_particles->particles[$id]['function']);
// 		// echo $reflection->getFileName() . ':' . $reflection->getStartLine();
// 		// echo '</pre>';
// }





/**
 * Gets the first docblock in a file
 *
 * @see http://stackoverflow.com/questions/11504541/get-comments-in-a-php-file
 */
// function thmfdn_get_file_doc_block( $file_path )
// {
// 	$docComments = array_filter(
// 		token_get_all( file_get_contents( $file_path ) ), function($entry) {
// 			return $entry[0] == T_DOC_COMMENT;
// 		}
// 	);
// 	$fileDocComment = array_shift( $docComments );
// 	return $fileDocComment[1];
// }