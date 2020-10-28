<?php
   /*
   Plugin Name: Frontendry Like Plugin
   Plugin URI: http://www.frontendry.com
   description: >-
  a plugin to create like counts
   Version: 1.0.0
   Author: Brian Gatarwa
   Author URI: http://www.frontendry.com
   License: GPL2
   */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} 

// Add 'frontendry_likes_votes' Table onto WP DB
register_activation_hook( __FILE__, 'frontendry_like_activator' );
function frontendry_like_activator() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'frontendry_likes_votes';

	$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		unique_content_id varchar(100) NOT NULL,
		vote_up int(11) NOT NULL,
		vote_down int(11) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

// Register Styles and Scripts
add_action('init', 'frontendry_likes_register_script');
function frontendry_likes_register_script() {
    wp_register_script( 'frontendry-likes-js', plugins_url('/js/frontendry-likes.js', __FILE__),array( 'jquery' ), '', true );

    wp_register_style( 'frontendry-likes-css', plugins_url('/css/frontendry-likes.css', __FILE__), array(), '', 'all');
}

// Enqueue Styles and Scripts
add_action('wp_enqueue_scripts', 'frontendry_likes_enqueue_scripts');
function frontendry_likes_enqueue_scripts(){
   wp_enqueue_script('frontendry-likes-js');
   wp_localize_script( 
    'frontendry-likes-js', 
    'frontendry_likes_object', 
    array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
  );
   wp_enqueue_style( 'frontendry-likes-css' );
}


// Create HTML for Voting
if ( ! function_exists( 'create_frontendry_likes_html' ) ) :
function create_frontendry_likes_html($frontendry_likes_unique_id){

   return '<div class="voting_wrapper text-white d-flex align-items-center mt-3" data-id="' . $frontendry_likes_unique_id . '"> <div class="voting_btn"><div class="up_button"><i class="far fa-thumbs-up"></i> </div><span class="up_votes">0</span></div></div>';
}
endif;

add_action("wp_ajax_frontendry_likes_processor", "frontendry_likes_processor");
add_action("wp_ajax_nopriv_frontendry_likes_processor", "frontendry_likes_processor");

function frontendry_likes_processor() {

   global $wpdb;

   //get type of vote from client
   $user_vote_type = trim($_POST["vote"]);

   //get unique content ID and sanitize it (cos we never know).
   $unique_content_id = filter_var(trim($_POST["unique_id"]),FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

   //Convert content ID to MD5 hash (optional)
   $unique_content_id = hash('md5', $unique_content_id);
      
   //check if its an ajax request, exit if not
    if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    } 

    switch ($user_vote_type){
       ##### User liked the content #########
		case 'up': 
			
			//check if user has already voted, determined by unique content cookie
			if (isset($_COOKIE["voted_".$unique_content_id]))
			{
				header('HTTP/1.1 500 Already Voted'); //cookie found, user has already voted
				exit(); //exit script
			}
			
         //get vote_up value from db using unique_content_id
        $results = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."frontendry_likes_votes WHERE `unique_content_id`='$unique_content_id'");
         
         if($results>0) {
            $execute=$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."frontendry_likes_votes SET vote_up=vote_up+1 WHERE unique_content_id='$unique_content_id'"));
       
         }else {
             $wpdb->query("INSERT INTO ".$wpdb->prefix."frontendry_likes_votes (unique_content_id, vote_up) value('$unique_content_id',1)");
         }

         $counter = $wpdb->get_row("SELECT `vote_up` FROM ".$wpdb->prefix."frontendry_likes_votes WHERE unique_content_id='$unique_content_id'", ARRAY_A);
        
         $votes 	= ($counter["vote_up"])?$counter["vote_up"]:0; 

         // set cookie that expires in 24 hours "time()+86400".
         setcookie("voted_".$unique_content_id, 1, time()+86400);
			echo ( $votes); //display total liked votes */

		
         wp_reset_query();
         die();
         break;	
      case 'fetch':
          $results = $wpdb->get_row("SELECT `vote_up`, `vote_down` FROM ".$wpdb->prefix."frontendry_likes_votes WHERE unique_content_id='$unique_content_id'", ARRAY_A);
          

         $vote_up 	= ($results["vote_up"])?$results["vote_up"]:0; 
         $vote_down 	= ($results["vote_down"])?$results["vote_down"]:0;

          //build array for php json
         $send_response = array('vote_up'=>$vote_up, 'vote_down'=>$vote_down);
         echo json_encode($send_response); //display json encoded values

    
         die();
         wp_reset_query();
         break;
    }
}
?>

