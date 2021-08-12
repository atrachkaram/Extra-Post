<?php
/* 
Plugin name: Extra Post 
Plugin URI: https://gkazas.com
Description: A simple plugin to add extra posts. 
Author: KARAM ATRACH
Author URI:  https://gkazas.com 
Version: 0.1 
*/
// Call extra_post_menu function to load plugin menu in dashboard
add_action('admin_menu', 'extra_post_menu');
// Create WordPress admin menu
if (!function_exists("extra_post_menu")) {
    function extra_post_menu() {
        $page_title = 'WordPress Extra Post';
        $menu_title = 'Extra Post';
        $capability = 'manage_options';
        $menu_slug = 'extra-post';
        $function = 'extra_post_page';
        $icon_url = 'dashicons-media-code';
        $position = 4;
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
        // Call update_extra_post function to update database
        add_action('admin_init', 'update_extra_post');
    }
}
// Create function to register plugin settings in the database
if (!function_exists("update_extra_post")) {
    function update_extra_post() {
        register_setting('extra-post-settings', 'extra_post');
    }
}
// Create WordPress plugin page
if (!function_exists("extra_post_page")) {
    function extra_post_page() { ?>   <h1>WordPress Extra Post</h1>   <form method="post" action="options.php">     

<?php settings_fields('extra-post-settings'); ?>    

<?php do_settings_sections('extra-post-settings'); ?>     
<table class="form-table">       <tr valign="top">       <th scope="row">Extra post:</th>       <td><input type="number" max="10" name="extra_post" value="<?php echo get_option('extra_post'); ?>"/></td>       </tr>     </table>   <?php submit_button(); ?>   </form> <?php
    }
}

//Add bootstrap css and js files
function prefix_enqueue() 
{       
    // JS
    wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('prefix_bootstrap');

    // CSS
    wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');
}

// Plugin logic for adding extra posts

    function extra_post($content) {
		prefix_enqueue();
        $extra_info = get_option('extra_post');
		$curl = curl_init();
		$url = "https://gkazas.com/wp-json/wp/v2/posts";
		curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if ($err) {
        //Only show errors while testing
        //echo "cURL Error #:" . $err;
      } else {
		$responseObj = json_decode($response, true); 
		$finalContent .= "<div class='container'>";
		$finalContent .= "<div class='row'>";
		for($i=0;$i<$extra_info;$i++){	
		$content = "";
		$link = $responseObj[$i]['link'] ;
		$title = $responseObj[$i]['title']['rendered'] ;
		$date = $responseObj[$i]['date'] ;
		$image = getImage($responseObj[$i]['_links']['wp:featuredmedia'][0]['href']);
		$category = json_decode(getCategory($responseObj[$i]['_links']['wp:term'][0]['href']),true);
		$category_name = $category['category_name'];
		$category_link = $category['category_link'];
		$details = $responseObj[$i]['excerpt']['rendered'] ;
        
		
		$string = strip_tags($details);
		if (strlen($string) > 100) {

		// truncate string
		$stringCut = substr($string, 0, 100);
		$endPoint = strrpos($stringCut, ' ');

		//if the string doesn't contain any space then it will cut without word basis.
		$string = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
		$string .= "... <a href='$link'>Read More</a>";
		}
		
		$content .= "<div class='col-md-4'>";
        $content .= "<div><a  href='$link'><img style='height:235px' src='$image' class='img-responsive' /></a></div>";
        $content .= "<div style='min-height:50px;margin-top:10px;'><b>".$title."</b></div>";
        $content .= "<div><a href='$category_link'>".$category_name."</a></div>";
        $content .= "<div>".$date."</div>";
        $content .= "<div style='margin-top:10px;margin-bottom:15px;'>".$string."</div>";
		$content .= "</div>";
        $finalContent .=$content;
		
		}
		$finalContent .= "</div>";
		$finalContent .= "</div>";
		echo $finalContent;
      }
    
    }
	
	function getImage($link){
		$curl = curl_init();
		$url = $link;
		curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
      ));
	  $imageSource = "";
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if ($err) {
        //Only show errors while testing
        //echo "cURL Error #:" . $err;
      } else {
		 $responseObj = json_decode($response,true);		 
		 $imageSource = $responseObj['guid']['rendered'];
	  }
	  return $imageSource;
	}
	
	function getCategory($link){
		
		$curl = curl_init();
		$url = $link;
		curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
      ));
	  $result = [];
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if ($err) {
        //Only show errors while testing
        //echo "cURL Error #:" . $err;
      } else {
		 $responseObj = json_decode($response,true);		 
		 $result['category_name'] = $responseObj[0]['name'];
		 $result['category_link'] = $responseObj[0]['link'];
	  }
	  return json_encode($result);
	}

// Apply the extra_post function on our content
add_shortcode('extra_post_plugin', 'extra_post'); ?>