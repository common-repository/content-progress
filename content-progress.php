<?php
/*
Plugin Name: Content Progress
Plugin URI: http://www.joedolson.com/content-progress/
Description: Adds a column to each post/page or custom post type indicating whether content has been added to the page.
Version: 1.3.13
Text Domain: content-progress
Domain Path: /lang
Author: Joseph Dolson
Author URI: https://www.joedolson.com/
*/
/*  Copyright 2011-2017  Joseph C Dolson  (email : plugins@joedolson.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $cp_version;
$cp_version = '1.3.13';
// Enable internationalisation
add_action( 'plugins_loaded', 'cp_load_textdomain' );
function cp_load_textdomain() {
	load_plugin_textdomain( 'content-progress', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

cp_check_version();

function cp_check_version() {
	global $cp_version;
	$prev_version = ( get_option( 'cp_version' ) ) ? get_option( 'cp_version' ) : '1.2.3';
	if ( version_compare( $prev_version, $cp_version, "<" ) ) {
		cp_activate();
	} 
	if ( version_compare( $cp_version, "1.3.9", "<=" ) ) {
		$statuses = get_option( 'cp_statuses' );
		if ( !isset( $statuses['empty'] ) ) {
			$statuses['empty'] = array( 'description'=>'Document is empty', 'icon'=>plugins_url( 'images/empty.png', __FILE__ ), 'label'=>'Empty' );
			update_option( 'cp_statuses', $statuses );
		}
		return;
	}
}

if ( !function_exists( 'exif_imagetype' ) ) {
    function exif_imagetype ( $filename ) {
        if ( !is_dir( $filename ) && ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) {
            return $type;
        }
		
		return false;
    }
}

function cp_activate() {
	global $cp_version;
	add_option( 'cp_statuses', 
		array( 
			'incomplete'=> array( 'description'=>'Manually marked incomplete','icon'=>plugins_url( 'images/incomplete.png', __FILE__ ),'label'=>'Incomplete' ),
			'complete'=>   array( 'description'=>'Manually marked complete', 'icon'=>plugins_url( 'images/complete.png', __FILE__ ),'label'=>'Complete' ),
			'review'=>     array( 'description'=>'Needs editorial review', 'icon'=>plugins_url( 'images/review.png', __FILE__ ),'label'=>'Needs Review' ),
			'empty'=>      array( 'description'=>'Document is empty', 'icon'=>plugins_url( 'images/empty.png', __FILE__ ),'label'=>'Empty' ) 
		)
	);
	update_option( 'cp_version', $cp_version );
}

function cp_column($cols) {
	$cols['cp'] = __('Flag','content-progress');
	$cols['cp_notes'] = __('Notes','content-progress');
	
	return $cols;
}

function cp_post_empty( $id ) {
	$post    = get_post( $id );
	$content = $post->post_content;
	if ( $content == '' ) {
		return true;
	}
	
	return false;
}

function cp_post_incomplete( $id ) {
	$post    = get_post( $id );
	$content = $post->post_content;
	
	$incomplete_length = apply_filters( 'cp_incomplete_length', 60, $id );	
	if (  strlen( $content ) < $incomplete_length ) {
		return true;
	}
	
	return false;
}

// Echo the ID for the new column
function cp_value( $column_name, $id ) {
	if ($column_name == 'cp') {
		$marked = ( get_post_meta( $id,'_cp_incomplete',true ) ) ? get_post_meta( $id,'_cp_incomplete',true ) : 'default';
		$marked = esc_attr( $marked );
		$statuses = get_option( 'cp_statuses' );
		if ( cp_post_empty( $id ) && $marked == 'default' ) {
			update_post_meta( $id, '_cp_incomplete', 'empty' );
			echo "<img src='".plugins_url( 'images/empty.png', __FILE__ )."' alt='".__('Document is empty','content-progress')."' class='$marked'  data-value='$marked' title='".__('Document is empty','content-progress')."' />";
		} else if ( cp_post_incomplete( $id ) && $marked == 'default' ) {
			update_post_meta( $id, '_cp_incomplete', 'incomplete' );		
			echo "<img src='".plugins_url( 'images/partial.png', __FILE__ )."' alt='".__('Document has less than 60 characters of content.','content-progress')."' class='$marked' title='".__('Document has less than 60 characters of content.','content-progress')."' />";	
		} else {
			if ( in_array( $marked, array_keys( $statuses ) ) ) {
				foreach ( $statuses as $key => $value ) {
					$marked = ( $marked == 'true' ) ? 'incomplete' : $marked; // old data correction.
					if ( $marked == $key ) {
						echo "<img src='$value[icon]' alt='$value[description]' class='$marked cp_status' data-value='$marked' title='$value[description]' />";
					} 
				}
			} else {
				if ( $marked == 'empty' ) {
					echo "<img src='".plugins_url( 'images/empty.png', __FILE__ )."' alt='".__('Document is empty','content-progress')."' class='$marked' data-value='$marked' title='".__('Document is empty','content-progress')."' />";
				} 
				if ( $marked == 'partial' ) {
					echo "<img src='".plugins_url( 'images/partial.png', __FILE__ )."' alt='".__('Document has less than 60 characters of content.','content-progress')."' data-value='$marked' class='$marked' title='".__('Document has less than 60 characters of content.','content-progress')."' />";
				}			
			}
		}
	}
	if ( $column_name == 'cp_notes' ) {
		$notes = get_post_meta( $id, '_cp_notes',true );
		echo esc_html( stripslashes( $notes ) );
	}
}

function cp_return_value($value, $column_name, $id) {
	if ( $column_name == 'cp' || $column_name == 'cp_notes' ) {
		$value = $id;
	}
	
	return $value;
}

// Output CSS for width of new column
function cp_css() {
?>
<style type="text/css">
#cp { width: 50px; } 
#cp_notes { width: 140px; }
.inline-edit-col-left legend { text-transform:uppercase; font-weight: 700; }
.text_input { float: left; width: 50%}
</style>
<?php	
}

// Actions/Filters for various tables and the css output
add_action('admin_init', 'cp_add');
function cp_add() {
	$settings = get_option( 'cp_settings' );
	add_action('admin_head', 'cp_css');
	
	if ( !$settings || in_array( 'post', $settings ) ) {
		add_filter('manage_posts_columns', 'cp_column');
		add_action('manage_posts_custom_column', 'cp_value', 10, 2);
	}
	if ( !$settings || in_array( 'page', $settings ) ) {
		add_filter('manage_pages_columns', 'cp_column');
		add_action('manage_pages_custom_column', 'cp_value', 10, 2);
	}
	$post_types = get_post_types( '','names' );
	foreach ( $post_types as $types ) {
		if ( !$settings || in_array( $types, $settings ) ) {		
			add_action("manage_${types}_columns", 'cp_column');			
			add_filter("manage_${types}_custom_column", 'cp_return_value', 10, 2);
		}
	}

}

function cp_list_empty_pages( $post_type, $group ) {
	$return = '';
	$group = strtolower( trim( $group ) );
	if ( is_user_logged_in() ) {
		$args = array( 
			'post_type'=>$post_type,
			'posts_per_page'=> -1,
			'orderby'=>'title', 
			'meta_key'=>'_cp_incomplete', 
			'meta_value'=>$group 
		);
		$posts = get_posts( $args ); 
		foreach ( $posts as $post ) {
			$return .= "<li><a href='".esc_url(get_permalink( $post->ID ))."'>$post->post_title</a></li>";
		}
		$post_type = get_post_type_object( $post_type );
		$label = $post_type->labels->name;
		$group_string = ucfirst( $group );
		if ( $return == '' ) { return; }
		
		return "<div class='cp_$group'><h2>$group_string $label:</h2> <ul>".$return."</ul></div>";
	}
}

//Shortcodes:  [empty], [partial], and [incomplete]
add_shortcode('list','content_progress');
function content_progress($atts) {
	extract( shortcode_atts(array(
				'type' => 'page',
				'status' => ''
			), $atts) );
	
	return ( !$status ) ? 'Status not specified' : cp_list_empty_pages( $type, $status );
}

add_shortcode('empty','list_empty');
function list_empty($atts) {
	extract(shortcode_atts(array(
				'type' => 'page',
				'group' => 'empty'
			), $atts));
			
	return cp_list_empty_pages($type, $group);
}

add_shortcode('partial','list_partial');
function list_partial($atts) {
	extract(shortcode_atts(array(
				'type' => 'page',
				'group' => 'partial'
			), $atts));
			
	return cp_list_empty_pages($type, $group);
}

add_shortcode('incomplete','list_incomplete');
function list_incomplete($atts) {
	extract(shortcode_atts(array(
				'type' => 'page',
				'group' => 'incomplete'
			), $atts));
			
	return cp_list_empty_pages( $type, $group );
}

add_shortcode('needs_review','list_review');
function list_review($atts) {
	extract(shortcode_atts(array(
				'type' => 'page',
				'group' => 'review'
			), $atts));
			
	return cp_list_empty_pages( $type, $group );
}

add_action('quick_edit_custom_box', 'cp_quickedit_show', 10, 2);
function cp_quickedit_show( $col, $type ) {
	$settings = get_option( 'cp_settings' );
	$statuses = get_option( 'cp_statuses' );
	$fieldset = $label = $field = $close_fieldset = $name = '';
	if ( !$settings || in_array( $type, $settings ) ) {
		if ( $col == 'cp' ) {
			$label = 'Flag';
			$name = '_cp_incomplete';
			$field = "<select name='$name' id='$name'>";
					foreach ( $statuses as $key => $value ) {
						$field .= "<option value='" . esc_attr( $key ) . "'>$value[label]</option>";
					}
			$field .= "
						<option value='default'>Default</option>
					</select>";
			$fieldset = "<fieldset class=\"inline-edit-col-left inline-edit-$type\"><legend>".__('Content Progress','content-progress')."</legend>";
			$close_fieldset = "";		
		} else if ( $col == 'cp_notes' ) {
			$label = 'Notes';
			$name = '_cp_notes';
			$field = "<textarea rows='1' cols='22' name='$name' id='$name'></textarea>";
			$fieldset = '';
			$close_fieldset = "</fieldset>";
		}
	}
?>
<?php echo $fieldset; ?>
<div class="inline-edit-col inline-edit-<?php echo $col; ?>">
<div class="inline-edit-group"><label for="<?php echo $name; ?>"><span class="title"><?php echo $label; ?></span></label>
 <?php echo $field; ?>
</div>
</div><?php echo $close_fieldset; ?>
<?php 
}

add_action('admin_footer-edit.php', 'cp_admin_edit_foot', 11 );
/* load scripts in the footer */
function cp_admin_edit_foot() {
    echo '<script type="text/javascript" src="', plugins_url('scripts/admin_edit.js', __FILE__), '"></script>';
}

add_action( 'save_post', 'cp_post_meta', 10 );
function cp_post_meta( $id ) {
	if ( isset( $_POST['_cp_incomplete'] ) ) {
		$incomplete = $_POST[ '_cp_incomplete' ];
		if ( ( $incomplete == 'empty' && !cp_post_empty( $id ) ) ) {
			delete_post_meta( $id, '_cp_incomplete' );
		} else {
			update_post_meta( $id, '_cp_incomplete', $incomplete );	
		}		
	}
	if ( isset( $_POST['_cp_notes'] ) ) {
		$notes = $_POST[ '_cp_notes' ];
		update_post_meta( $id, '_cp_notes', $notes );
	}
	
	do_action( 'cp_save_post', $id ); 	
}

add_action( 'in_plugin_update_message-content-progress/content-progress.php', 'cp_plugin_update_message' );
function cp_plugin_update_message() {
	global $cp_version;
	define('CP_PLUGIN_README_URL',  'http://svn.wp-plugins.org/content-progress/trunk/readme.txt');
	$response = wp_remote_get( CP_PLUGIN_README_URL, array ('user-agent' => 'WordPress/Content Progress' . $cp_version . '; ' . get_bloginfo( 'url' ) ) );
	if ( ! is_wp_error( $response ) || is_array( $response ) ) {
		$data = $response['body'];
		$bits=explode('== Upgrade Notice ==',$data);
		echo '<div id="mc-upgrade"><p><strong style="color:#c22;">Upgrade Notes:</strong> '.nl2br(trim($bits[1])).'</p></div>';
	} else {
		printf(__('<br /><strong>Note:</strong> Please review the <a class="thickbox" href="%1$s">changelog</a> before upgrading.','content-progress'),'plugin-install.php?tab=plugin-information&amp;plugin=content-progress&amp;TB_iframe=true&amp;width=640&amp;height=594');
	}
}

add_action( 'admin_menu','cp_add_meta_box' );
function cp_add_meta_box() {
	foreach ( get_post_types() as $value) {
		add_meta_box( 'cp_div',__( 'Content Progress', 'content-progress' ), 'cp_meta_box', $value, 'side' );
	}
}

function cp_meta_box() {
	global $post_id;
	$cp      = get_post_meta( $post_id, '_cp_incomplete', true );
	$cp      = ( $cp ) ? $cp : get_option( 'cp_default' );
	$notes   = get_post_meta($post_id, '_cp_notes',true );
	$default = ( $cp == 'default' || !$cp ) ? ' checked="checked"' : ''; 

	$output = "<ul>";
	$statuses = get_option( 'cp_statuses' );
	foreach ( $statuses as $key => $value ) {
		$checked = ( $cp == $key || $key == 'incomplete' && $cp == 'true' ) ? ' checked="checked"' : '';
		$output .= "<li><input type='radio' name='_cp_incomplete' value='" . esc_attr( $key ) . "' id='_cp_incomplete_" . sanitize_title( $key ) . "'$checked /> <label for='_cp_incomplete_" . sanitize_title( $key ) . "'>".stripslashes( esc_html( $value['label'] ) )."</label></li>";
	}		
	$output .= "<li><input type='radio' name='_cp_incomplete' value='default' id='_cp_incomplete_default'$default /> <label for='_cp_incomplete_default'>".__('Default','content-progress')."</label></li>
	</ul>
	<p><label for='_cp_notes'>".__('Notes:','content-progress')."</label><br /><textarea name='_cp_notes' id='_cp_notes'>$notes</textarea></p>";
	
	$output = apply_filters( 'cp_meta_box', $output, $post_id );
	echo $output;
}

function cp_get_support_form() {
	global $cp_version;
	$current_user = wp_get_current_user();

	// send fields for Content Progress
	$version = $cp_version;
	// send fields for all plugins
	$wp_version = get_bloginfo('version');
	$home_url = home_url();
	$wp_url = site_url();
	$language = get_bloginfo('language');
	$charset = get_bloginfo('charset');
	// server
	$php_version = phpversion();

	// theme data
	if ( function_exists( 'wp_get_theme' ) ) {
	$theme = wp_get_theme();
		$theme_name = $theme->Name;
		$theme_uri = $theme->ThemeURI;
		$theme_parent = $theme->Template;
		$theme_version = $theme->Version;	
	} else {
	$theme_path = get_stylesheet_directory().'/style.css';
	$theme = get_theme_data($theme_path);
		$theme_name = $theme['Name'];
		$theme_uri = $theme['ThemeURI'];
		$theme_parent = $theme['Template'];
		$theme_version = $theme['Version'];
	}
	// plugin data
	$plugins = get_plugins();
	$plugins_string = '';
		foreach( array_keys($plugins) as $key ) {
			if ( is_plugin_active( $key ) ) {
				$plugin =& $plugins[$key];
				$plugin_name = $plugin['Name'];
				$plugin_uri = $plugin['PluginURI'];
				$plugin_version = $plugin['Version'];
				$plugins_string .= "$plugin_name: $plugin_version; $plugin_uri\n";
			}
		}
	$data = "
================ Installation Data ====================
==Content Progress:==
Version: $version

==WordPress:==
Version: $wp_version
URL: $home_url
Install: $wp_url
Language: $language
Charset: $charset

==Extra info:==
PHP Version: $php_version
Server Software: $_SERVER[SERVER_SOFTWARE]
User Agent: $_SERVER[HTTP_USER_AGENT]

==Theme:==
Name: $theme_name
URI: $theme_uri
Parent: $theme_parent
Version: $theme_version

==Active Plugins:==
$plugins_string
";
	if ( isset($_POST['cp_support']) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce,'content-progress-nonce') ) die("Security check failed");	
		$request = stripslashes($_POST['support_request']);
		$has_donated = ( $_POST['has_donated'] == 'on')?"Donor":"No donation";
		$has_read_faq = ( $_POST['has_read_faq'] == 'on')?"Read FAQ":true; // has no faq, for now.
		$subject = "Content Progress support request. $has_donated";
		$message = $request ."\n\n". $data;
		$from = "From: \"$current_user->display_name\" <$current_user->user_email>\r\n";

		if ( !$has_read_faq ) {
			echo "<div class='message error'><p>".__('Please read the FAQ and other Help documents before making a support request.','content-progress')."</p></div>";
		} else {
			wp_mail( "plugins@joedolson.com",$subject,$message,$from );
		
			if ( $has_donated == 'Donor' ) {
				echo "<div class='message updated'><p>".__('Thank you for supporting the continuing development of this plug-in! I\'ll get back to you as soon as I can.','content-progress')."</p></div>";		
			} else {
				echo "<div class='message updated'><p>".__('I\'ll get back to you as soon as I can, after dealing with any support requests from plug-in supporters.','content-progress')."</p></div>";				
			}
		}
	} else {
		$request = '';
	}
	echo "
	<form method='post' action='".admin_url('options-general.php?page=content-progress/content-progress.php')."'>
		<div><input type='hidden' name='_wpnonce' value='".wp_create_nonce('content-progress-nonce')."' /></div>
		<div>
		<p>".
		__('Please note: I do keep records of those who have donated, but if your donation came from somebody other than your account at this web site, please note this in your message.','content-progress')
		."<!--<p>
		<input type='checkbox' name='has_read_faq' id='has_read_faq' value='on' /> <label for='has_read_faq'>".__('I have read <a href="http://www.joedolson.com/articles/content-progress/">the FAQ for this plug-in</a>.','content-progress')." <span>(required)</span></label>
		</p>-->
		<p>
		<input type='checkbox' name='has_donated' id='has_donated' value='on' /> <label for='has_donated'>".__('I have <a href="http://www.joedolson.com/donate.php">made a donation to help support this plug-in</a>.','content-progress')."</label>
		</p>
		<p>
		<label for='support_request'>Support Request:</label><br /><textarea name='support_request' id='support_request' required aria-required='true' cols='80' rows='10' class='widefat'>".stripslashes($request)."</textarea>
		</p>
		<p>
		<input type='submit' value='".__('Send Support Request','content-progress')."' name='cp_support' class='button-primary' />
		</p>
		<p>".
		__('The following additional information will be sent with your support request:','content-progress')
		."</p>
		<div class='cp_support'>
		".wpautop($data)."
		</div>
		</div>
	</form>";
}


function cp_settings() {
	if ( isset($_POST['cp_settings']) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce($nonce,'content-progress-nonce') ) {
			die("Security check failed");	
		}
		$settings = $_POST['cp_post_types'];
		update_option( 'cp_settings', $settings );
	}
	cp_build_statuses();
	$settings = get_option( 'cp_settings' );
	$post_types = get_post_types( array( 'public'=>true ), 'objects' );
	$cp_post_types = $settings;
	if ( !is_array( $cp_post_types ) ) { $cp_post_types = array(); }
	$my_post_types = '';
		foreach( $post_types as $type ) {
			if ( in_array( $type->name , $cp_post_types ) || empty($cp_post_types) ) { $selected = ' selected="selected"'; } else { $selected = ''; }
			$my_post_types .= "<option value='$type->name'$selected>$type->label</option>";
		}
	echo "
	<form method='post' action='".admin_url('options-general.php?page=content-progress/content-progress.php')."'>
		<div><input type='hidden' name='_wpnonce' value='".wp_create_nonce('content-progress-nonce')."' /></div>
		<div>
		<p>
			<label for='post_types'>".__('Enabled for these post types:','content-progress')."</label><br />
			<select multiple='multiple' name='cp_post_types[]' id='post_types'>
				$my_post_types
			</select>
		</p>";
		echo cp_setup_statuses();
		echo "<p>
		<input type='submit' value='" . __( 'Update Settings','content-progress' ) . "' name='cp_settings' class='button-primary' />
		</p>
		</div>
	</form>";
}


function cp_setup_statuses() {
	$statuses = get_option( 'cp_statuses' );
	$default  = get_option( 'cp_default' );
	$select   = "<p><label for='cp_default'>" . __( 'Default Status', 'content-progress' ) . "</label> <select name='cp_default' id='cp_default'>
	<option value=''>" . __( 'Default', 'content-progress' ) . "</option>";
	$return   = "
		<h4>".__('Customize Statuses','content-progress')."</h4>
		<table class='widefat fixed'>
		<thead><tr><th scope='col'>Status label</th><th scope='col'>Description</th><th scope='col'>Icon URL</th><th scope='col'>Delete Status</th></tr></thead>
		<tbody>";
	if ( is_array( $statuses ) ) {
		foreach ( $statuses as $key=>$value ) {
			$return .= "
		<tr>
			<th scope='row'>".stripslashes($value['label'])."</th>
			<td>$value[description]</td>			
			<td><img src='$value[icon]' alt='' /></td>			
			<td><label for='cp_status_delete_" . sanitize_title( $key ) . "'>Delete Status</label> <input type='checkbox' id='cp_status_delete_" . sanitize_title( $key ) . "' name='cp_status_delete[]' value='" . esc_attr( $key ) . "' /></td>
		</tr>";
			$select .= "<option value='" . esc_attr( $key ) . "'" . selected( $key, $default, false ) . ">".stripslashes($value['label'])."</option>";
		}
	}
	$return .= "
		<tr>
			<td><label for='cp_status_label'>Status label</label> <input type='text' id='cp_status_label' name='cp_statuses[label]' value='' class='widefat' /></td>
			<td><label for='cp_status_desc'>Description </label> <input type='text' id='cp_status_desc' name='cp_statuses[description]' value='' class='widefat' /></td>			
			<td><label for='cp_status_icon'>Icon URL</label> <input type='text' id='cp_status_icon' name='cp_statuses[icon]' value='' class='widefat' /></td>			
			<td></td>
		</tr>
		</tbody></table>";
	$select .= "</select></p>";
	
	return $select . $return;
}

function cp_dirlist($directory) {
    // create an array to hold directory list
    $results = array();
    // create a handler for the directory
    $handler = opendir($directory);
    // keep going until all files in directory have been read
    while ($file = readdir($handler)) {
        // if $file isn't this directory or its parent, 
        // add it to the results array
		if ( filesize( $directory.'/'.$file ) > 11 ) {
			if ( $file != '.' && $file != '..' && !is_dir($directory.'/'.$file) && (
			exif_imagetype($directory.'/'.$file) == IMAGETYPE_GIF || 
			exif_imagetype($directory.'/'.$file) == IMAGETYPE_PNG ||  
			exif_imagetype($directory.'/'.$file) == IMAGETYPE_JPEG ) ) {
				$results[] = $file;
			}
		}
    }
    // tidy up: close the handler
    closedir( $handler );
	sort( $results, SORT_STRING );
	
    return $results;
}

function cp_build_statuses() {
	$statuses = get_option( 'cp_statuses' );
	$default = get_option( 'cp_default' );
	if ( isset($_POST['cp_status_delete']) ) {
		foreach( $_POST['cp_status_delete'] as $value ) {
			unset($statuses[$value]);
		}
	}
	if ( isset($_POST['cp_statuses']) && $_POST['cp_statuses']['label'] != '' ) {
			$status = $_POST['cp_statuses'];
			$new_status = array( 'description'=>$status['description'],'icon'=>$status['icon'],'label'=>$status['label'] );
			$statuses[ sanitize_title($status['label']) ] = $new_status;
	}
	if ( isset( $_POST['cp_default'] ) && $_POST['cp_default'] != '' ) {
		$default = $_POST['cp_default'];
	}
	update_option( 'cp_default', $default );
	update_option( 'cp_statuses', $statuses );
}

function cp_support_page() {
?>
<div class="wrap" id="content-progress">
	<h1><?php _e('Content Progress','content-progress'); ?></h1>
	<div class='cp-support-me'>
		<p>
			<?php printf(
				__( 'Please, <a href="%s">consider a donation</a> to support Content Progress!', 'content-progress' )
			, "https://www.joedolson.com/donate/" ); ?>
		</p>
	</div>	
	<div id="cp_settings_page" class="jcd-wide postbox-container">
		<div class='metabox-holder'>

			<div class="cp-settings meta-box-sortables">
			<div class="postbox" id="settings">
				<h2 class="hndle"><?php _e('Content Progress Settings','content-progress'); ?></h2>
				<div class="inside">

				<?php cp_settings(); ?>

				<h3><?php _e('Default icon guide:','content-progress'); ?></h3>
				<ul class="icon-guide">
				<?php 
					echo "<li><img src='".plugins_url( 'images/empty.png', __FILE__ )."' alt='" . __('Document is empty','content-progress') . "' /> ".__('Document is empty','content-progress')."</li>
					<li><img src='".plugins_url( 'images/partial.png', __FILE__ )."' alt='" . __('Document has less than 60 characters of content.','content-progress')."' /> ".__('Document has less than 60 characters of content.','content-progress')."</li>
					<li><img src='".plugins_url( 'images/incomplete.png', __FILE__ )."' alt='" . __('Manually marked incomplete.','content-progress')."' /> ".__('Manually marked incomplete.','content-progress')."</li>
					<li><img src='".plugins_url( 'images/complete.png', __FILE__ )."' alt='" . __('Manually marked complete.','content-progress')."' /> ".__('Manually marked complete.','content-progress')."</li>
					<li><img src='".plugins_url( 'images/review.png', __FILE__ )."' alt='" . __('Needs Editorial Review.','content-progress')."' /> ".__('Needs Editorial Review.','content-progress')."</li>";
				?>
				</ul>
				<h4><?php _e('Additional included icons','content-progress'); ?></h4>
				<p>
					<?php _e('You can use any URL to reference an icon of your choice; these are included for your convenience.','content-progress'); ?>
				</p>
				<?php 
				$icons = cp_dirlist( dirname(__FILE__).'/images/' ); 
				$defaults = array( 'complete.png','empty.png','incomplete.png','partial.png','review.png' );
				$icons = array_diff( $icons, $defaults );
				echo '<ul class="icon-guide">';
				foreach( $icons as $value ) {
					echo "<li><img src='".plugins_url( "images/$value", __FILE__ )."' alt='$value' /> <strong>URL:</strong> <code>".plugins_url( "images/$value", __FILE__ ). "</code></li>";
				}
				echo '</ul>';
				?>
				
				<h3><?php _e( 'Content Progress Shortcodes', 'content-progress' ); ?></h3>
				<p>
					<?php _e( 'Type is an optional parameter for all statuses, and will default to "post". The "list" shortcode requires a status type.', 'content-progress' ); ?>
				</p>
				
				<p>
					<textarea readonly='readonly' class="large-text readonly">[list status='' type='post']</textarea>
				</p>				
				<p>
					<textarea readonly='readonly' class="large-text readonly">[empty type='post']</textarea>
				</p>
				<p>
					<textarea readonly='readonly' class="large-text readonly">[partial type='post']</textarea>
				</p>				
				<p>
					<textarea readonly='readonly' class="large-text readonly">[incomplete type='post']</textarea>
				</p>
				<p>				
					<textarea readonly='readonly' class="large-text readonly">[needs_review type='post']</textarea>
				</p>
				
				</div>
			</div>
		</div>
		<div class="cp-support meta-box-sortables">
			<div class="postbox" id="get-support">
				<h2 class="hndle"><?php _e( 'Get Plug-in Support','content-progress' ); ?></h2>
				<div class="inside">
					<?php cp_get_support_form(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php cp_show_support_box(); ?>
</div>

<?php
}

function cp_show_support_box() {
?>
<div id="cp-sidebar" class="jcd-narrow postbox-container">
<div class="metabox-holder">
	<div class="meta-box-sortables">
		<div class="postbox">
		<h2 class="hndle"><?php _e('Support this Plug-in','content-progress'); ?></h2>
		<div id="support" class="inside resources">
		<ul>
			<li>
			<p>
				<a href="https://twitter.com/intent/follow?screen_name=joedolson" class="twitter-follow-button" data-size="small" data-related="joedolson">Follow @joedolson</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			</p>
			</li>
			<li><p><?php _e('<a href="http://www.joedolson.com/donate/">Make a donation today!</a> Donate <strong>any amount</strong> and help me keep this plug-in running!','content-progress'); ?></p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<div>
					<input type="hidden" name="cmd" value="_s-xclick" />
					<input type="hidden" name="hosted_button_id" value="8490399" />
					<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="Donate" />
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					</div>
				</form>
			</li>
			<li><a href="http://wordpress.org/extend/plugins/content-progress/"><?php _e('Rate this plug-in','content-progress'); ?></a></li>	
		</ul>
		</div>
		</div>
	</div>
	<div class="meta-box-sortables">
		<div class="postbox">
		<h2 class="hndle"><?php _e('Try my other plugins','content-progress'); ?></h2>
		<div id="support" class="inside resources">
		<ul>
			<li><span class='dashicons dashicons-twitter' aria-hidden="true"></span> <a href="https://wordpress.org/plugins/wp-to-twitter/">WP to Twitter</a></li>
			<li><span class='dashicons dashicons-calendar-alt' aria-hidden="true"></span> <a href="https://wordpress.org/plugins/my-calendar/">My Calendar</a></li>
			<li><span class='dashicons dashicons-tickets' aria-hidden="true"></span> <a href="https://wordpress.org/plugins/my-tickets/">My Tickets</a></li>
			<li><span class='dashicons dashicons-universal-access-alt' aria-hidden="true"></span> <a href="https://wordpress.org/plugins/wp-accessibility/">WP Accessibility</a></li>
			<li><span class='dashicons dashicons-universal-access' aria-hidden="true"></span> <a href="https://wordpress.org/plugins/access-monitor/">Access Monitor</a></li>
			<li><span class='dashicons dashicons-wordpress' aria-hidden="true"></span> <a href="http://profiles.wordpress.org/users/joedolson/"><?php _e('And even more...','content-progress'); ?></a></li>
		</ul>
		</div>
		</div>
	</div>	
	
</div>
</div>
<?php
}

// Add the administrative settings to the "Settings" menu.
add_action( 'admin_menu', 'cp_add_support_page' );
function cp_add_support_page() {
    if ( function_exists( 'add_submenu_page' ) ) {
		 $plugin_page = add_options_page( 'Content Progress Support', 'Content Progress', 'manage_options', __FILE__, 'cp_support_page' );
		 add_action( 'admin_head-'. $plugin_page, 'cp_styles' );
    }
}

function cp_styles() {
	wp_enqueue_style( 'content-progress-css', plugins_url( 'cp-styles.css', __FILE__ ) );
}

function cp_plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/content-progress.php')) {
		$links[] = "<a href='options-general.php?page=content-progress/content-progress.php'>" . __('Get Support', 'content-progress', 'content-progress') . "</a>";
		$links[] = "<a href='http://www.joedolson.com/donate/'>" . __('Donate', 'content-progress', 'content-progress') . "</a>";
	}
	return $links;
}
//Add Plugin Actions to WordPress

add_filter('plugin_action_links', 'cp_plugin_action', -10, 2);