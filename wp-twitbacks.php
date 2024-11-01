<?php

/*
Plugin Name: WP Twitter Backlinks
Plugin URI: http://en.tigor.org.ua/wp_twitbacks/
Description: Show Backtweets in sidebar.
Version: 0.4.1
Author: TIgor
Author URI: http://en.tigor.org.ua
License: GPL2
*/


/*  Copyright 2010 Tesliuk Igor  (email : tigoria@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

load_textdomain('twitbacks',ABSPATH.'wp-content/plugins/wp-twitter-backlinks/translation/ru-RU.mo');


function add_twitbacks_stylesheet() 
{
        $myStyleUrl = WP_PLUGIN_URL . '/wp-twitter-backlinks/wp-twitbacks.css';
        $myStyleFile = WP_PLUGIN_DIR . '/wp-twitter-backlinks/wp-twitbacks.css';
        if ( file_exists($myStyleFile) ) {
            wp_register_style('myStyleSheets', $myStyleUrl);
            wp_enqueue_style( 'myStyleSheets');
        }
    }


function get_xml($ti_url)
{

$xmlfile = WP_PLUGIN_DIR. '/wp-twitter-backlinks/backtweets.xml';

$file = file_get_contents($ti_url);
if ($file) file_put_contents($xmlfile, $file);
$xml = simplexml_load_file($xmlfile);
return $xml;  

}

function make_html($ti_url, $htmlfile, $tweets_count)
{



$xml = get_xml($ti_url);

$data ='<div class="backtweets">';
$i = 1;

$data = '<div class="backtweets-total">'.$data.__('Number of links for last two weeks:').' <b>'.$xml->totalresults.'</b></div>';

foreach ($xml->tweets->entry as $entry) 
{
$tweet = $entry->tweet_text;
$avatar = $entry->tweet_profile_image_url;
$tweeple = $entry->tweet_from_user;
$tweet_id = $entry->tweet_id;
$tweet_string = '<div class="twitpost"><img src="'.$avatar.'" class="twitava"><a href="http://twitter.com/'.$tweeple.'" rel="nofollow">@'.$tweeple.'</a><br>'.$tweet.'</div>';
$data = $data. $tweet_string;

$i++;
if ($i > $tweets_count) break;

}

file_put_contents ($htmlfile, $data);
}

function show_html($ti_url, $htmlfile, $cachetime = 3600, $tweets_count = 10)
{

$cachetime = 3600;



if (!file_exists($htmlfile)) 
{make_html($ti_url, $htmlfile, $tweets_count);}
elseif
((time() - filectime($htmlfile) ) > $cachetime)
{make_html($ti_url, $htmlfile, $tweets_count);}



echo file_get_contents($htmlfile);


echo '</div>';


}



function widget_twitbacksWidget($args)
{

global $wpdb;
extract($args);
$title = 'WP Twitter Backlinks';



$options = get_option('widget_twitbacks');
$title = $options ['title'];
$tweets_count = $options['count'];
$path = "";
$promote = $options['promote'];
$key = $options['key'];
$link = $options['link'];
$xmlfile = WP_PLUGIN_DIR. "/wp-twitter-backlinks/backtweets.xml";
$htmlfile = WP_PLUGIN_DIR. "/wp-twitter-backlinks/backtweets.html";

$cachetime = 600;
$ti_url = "http://backtweets.com/search.xml?q=". $link. "&key=". $key ."&itemsperpage=". $tweets_count;

echo $before_widget;

    if (!empty($title)) 
{ 
        echo ($before_title. $title . $after_title);  

    }

show_html($ti_url, $htmlfile, $cachetime, $tweets_count );
if ($promote =='true') {
echo '<div>';
_e('Script by <a href="http://en.tigor.org.ua/wp_twitbacks/">TIgor</a>','twitbacks');
echo '</div>';
}
echo $after_widget;

}

function control_twitbacksWidget()
{

$options = $newoptions = get_option('widget_twitbacks');




if ( $_POST["twitbacks-widget-submit"] ) 
	{
$newoptions['title'] = $_POST["twitbacks_widget_title"];		
$newoptions['link'] = $_POST["twitbacks_widget_link"];
		$newoptions['key'] = $_POST["twitbacks_widget_key"];
		$newoptions['count'] = $_POST["twitbacks_widget_count"];
		$newoptions['promote'] = $_POST["twitbacks_widget_promote"];
	
	}
		$title = $options['title'];
		$link = $options['link'];
		$key = $options['key'];
		$count = $options['count'];
		$promote = $options['promote'];
	
if ( $options != $newoptions )
	{
	$options = $newoptions;
	update_option('widget_twitbacks', $options);
	}

?>
			
<dl>
			<dt><?php _e('Widget title','twitbacks'); ?></dt>
<dd><input name="twitbacks_widget_title" type="text" value="<?php print ($title); ?>" /></dd>
			<dt><?php _e('Link to watch','twitbacks'); ?></dt>
<dd><input name="twitbacks_widget_link" type="text" value="<?php print ($link); ?>" /></dd>
			<dt><?php _e('Key','twitbacks');?> (<a href="http://www.backtype.com/developers"><?php _e('Get key','twitbacks');?></a>) </dt>
			 <dd><input name="twitbacks_widget_key" type="text" value="<?php echo ($key); ?>" /></dd>
			<dt><?php _e('Number of tweets to show','twitbacks');?></dt>
			 <dd><input name="twitbacks_widget_count" type="text" value="<?php echo ($count); ?>" /></dd>

<p><input class="checkbox" id="twitbacks_widget_promote" name="twitbacks_widget_promote" type="checkbox" value="true" <?php if ($promote =='true') echo "CHECKED"?> /><label for="twitbacks_widget_promote"><?php _e('Help promote plugin?','twitbacks');?></label></p>

			
</dl>
			<input type="hidden" id="twitbacks-widget-submit" name="twitbacks-widget-submit" value="1" />
<?php
	
		
}

function twitbacks_init()
{
	register_sidebar_widget(__('TwitBacks Widget'), 'widget_twitbacksWidget');
	register_widget_control(__('TwitBacks Widget'), 'control_twitbacksWidget');



}



add_action("plugins_loaded", "twitbacks_init");
add_action('wp_print_styles', 'add_twitbacks_stylesheet');


?>