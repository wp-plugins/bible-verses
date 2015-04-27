<?php
/*
Plugin Name: Bible Verses
Plugin URI: http://www.joelsays.com/plugins/bible-verses
Description: Shows random Bible verses as widget or using shortcode.
Version: 1.2
Author: Joel James
Author URI: http://www.joelsays.com/about
License: GPL2
*/
function js_bible_verses_stylesheet() {
	wp_register_style( 'js-style', plugins_url('css/js-bible-verses.css', __FILE__) );
	wp_enqueue_style( 'js-style' );
}

add_action( 'wp_enqueue_scripts', 'js_bible_verses_stylesheet' );


function js_random_verse() {
	$languageAdd = '';
	$position = rand(0, 200);
	$jsrandomVerse = get_option('jsrandomVerse_' . $position . $languageAdd);
	$jsrandomVerse_fetch = get_option('jsrandomVerse_fetch' . $languageAdd);
	
	if($jsrandomVerse == "" && $jsrandomVerse_fetch < (date('U') - 3600))
	{
		$url = 'http://dailyverses.net/getrandomverse.ashx?language=en&position=' . $position . '&url=' . $_SERVER['HTTP_HOST'] . '&type=random1_6';
		$result = wp_remote_get($url);

		if(!is_wp_error($result)) 
		{
			$jsrandomVerse = str_replace(',', '&#44;', $result['body']);

			update_option('jsrandomVerse_' . $position . $languageAdd, $jsrandomVerse);
		}
		else
		{
			update_option('jsrandomVerse_fetch' . $languageAdd, date('U'));
		}
	}

	if($jsrandomVerse == "")
	{
		$jsrandomVerse = '<div class="dailyVerses bibleText">For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.</div><div class="dailyVerses bibleVerse">John 3:16</div>';
	}
	
	$html = $jsrandomVerse;
	
	return $html;
}

add_shortcode('js-bible-verses', 'js_random_verse'); 


class JSVerseWidget extends WP_Widget
{
  function JSVerseWidget()
  {
    $widget_ops = array('classname' => 'JSRandomVerse', 'description' => 'Shows good bible verses randomly on each refresh!' );
    $this->WP_Widget('JSVerseWidget', 'JS Bible Verses', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => 'Random bible verse') );
    $title = $instance['title'];
	
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;
	
    echo js_random_verse();
 
    echo $after_widget;
  }
}
add_action( 'widgets_init', create_function('', 'return register_widget("JSVerseWidget");') );
?>