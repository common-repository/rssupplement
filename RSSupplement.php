<?php
/*
Plugin Name: RSSupplement
Description: Adds WP functions, copyright, and more to your RSS feed items.
Version: 16.07
Author: Jerry Stephens
Author URI: http://wayofthegeek.org/
*/


/*
	Copyright 2007  Jerry Stephens  (email : migo@wayofthegeek.org)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


//Hook for adding admin menus
add_action('admin_menu','add_rssupplement_page');
add_action('admin_init', 'rss_initial' );

//Add Options Page
function add_rssupplement_page(){
        $plugin_page=add_options_page('RSSupplement Options','RSSupplement',10,__FILE__,'supplement_options');
        add_action( 'admin_footer-'. $plugin_page,'jq');
}
function rss_initial(){
        register_setting('rss_settings','rss_settings');
        $inputs = array(
            'author'=>'CHECKED',
            'copyright'=>'',
            'categories'=>'CHECKED',
            'comment_link'=>'CHECKED',
            'freetext'=> '',
            'cr_text'=> ''
        );
        add_option('rss_settings',$inputs);

}
function jq(){
wp_enqueue_script('rssupplement',plugins_url( 'rss.js' , __FILE__ ),array('jquery'),'14.10',true);
wp_enqueue_style('rssupplement',plugins_url( 'rss.css' , __FILE__ ),array(),'14.10');
}

function supplement_options(){

        ?><div class="wrap">
<h2>RSSupplement Options</h2>
                <form method="post" action="options.php">
        <?php wp_nonce_field('update-options');
        settings_fields('rss_settings');
        $options = get_option('rss_settings');
$inputs = array(
            'author'=>'CHECKED',
            'copyright'=>'CHECKED',
            'categories'=>'CHECKED',
            'comment_link'=>'CHECKED',
            'freetext'=> '',
            'cr_text'=> ''
        );
//print_r($options);
echo '<table cellpadding="4" cellspacing="0">
<tr>
<td valign="top" rowspan="2" width="200px"><h4 style="padding: 0; margin: 0;">Click To Add</h4>';
foreach($inputs as $key => $val){
    $checked = get_option('rss_settings');
    switch($key){
        case 'freetext':
        break;
        case 'cr_text':
        break;
        default:
            echo '<input type="checkbox" name="rss_settings['.$key.']" id="'.$key.'" value="CHECKED" '.(in_array($key,array_keys($options))?' CHECKED ':'').'/><label for="rss_settings['.$key.']">'.ucwords(str_replace('_',' ',$key)).'</label><br>';
        break;
    }
}
echo'</td>
<td valign="top" style="border-left: 1px solid #CCC;" id="content-inputs">';
echo '<label for="cr_text">Copyright Text<strong>&nbsp;&amp;copy; = &copy; &amp;reg; = &reg; &amp;trade; = &trade;</strong></label><input id="cr_text" type="text" name="rss_settings[cr_text]" value="'.$checked['cr_text'].'" />';
        echo'<h4 style="padding: 0; margin: 0;">Anything else you would like to add?</h4>
        <label for="set_freetext">HTML, plain-text, WP &amp; PHP functions are supported in this release. Post-specific or Loop-dependent WordPress functions may be used. For PHP code, be sure to include <strong>&lt;?php ?&gt;</strong> tags</label>
        <textarea name="rss_settings[set_freetext]" id="set_freetext" rows="10px" cols="100px">'.(!empty($checked['set_freetext']) ? $checked['set_freetext']:'').'</textarea>';

echo'</td>
</tr>
<tr>
<td style="border-left: 1px solid #CCC;" align="right">';
        echo'<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="rss_settings,set_freetext" />
        <p class="submit">
<input type="submit" name="Submit" value="Update Options » " />
</p>';

echo'</td>
</tr>
</table>
</form>
        </div>';
}


function supplement($notice){
//settings_fields('rss_settings');
$check = get_option('rss_settings');
//print_r($check);
$freetext = nl2br($check['set_freetext']);
$sep = ' ';
    ob_start();
    eval("?>$freetext<?php ");
    $freetext = ob_get_contents();
        $author = ($check['author'] == "CHECKED" ? 'by '.get_the_author():'');

        $copyright = $check['copyright'] == "CHECKED" ? $check['cr_text']:'';
        if($check['categories'] == "CHECKED"){
                $get_cat = get_the_category($post->ID);
                $get_cat_link = get_category_link($get_cat[0]->cat_ID);
                $category = 'Posted in <a href="'.$get_cat_link.'">' .$get_cat[0]->cat_name.'</a> ';
        }
        else{
                $category = "";
        }
        if($check['comment_link'] == "CHECKED"){
                $comment_link = get_permalink();
                $comments = '<a href="'.$comment_link.'#comments">Leave A Comment</a>';
        }
        else{
                $comments = "";
        }
    ob_end_clean();

$notice = '<p>'.$category.$author.$sep.$comments.$sep.$freetext.$sep.$copyright.'</p>';
return $notice;
}

function content_supplement($content){
	global $post;
	$content = $content.supplement($notice);
	return $content;
}
function excerpt_supplement($content){
	global $post;
	$content = $content._e(supplement($notice));
	return $content;
}
switch(get_option('rss_use_excerpt')){
    case(1):
        add_filter('the_excerpt_rss', 'content_supplement');
    break;
    case(0):
	add_filter('the_content_feed', 'content_supplement');
    break;
}
