<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function admin_page_open(): void
{
    ob_start();
    ?>
    <p>Start parse DB and generate Posts</p>
    <input type="button" class="button button-parse-plugin" name="parse" value="Convert data to post" />
    <input type="button" class="button button-parse-post-plugin" name="parse_post" value="Parse post" />
    <input type="button" class="button button-second-parse-post-plugin" name="second_parse_post" value="Second parse post" />
    <span class="spinner"></span>
    <p class="message-parse hidden">There are still posts to be processed.</p>
    <p class="message-parse-end hidden">All processed.</p>
    <?php
    $output = ob_get_clean();
    echo $output;
}

function get_data_from_db(): array
{
    global $wpdb;
    $sql = "SELECT * FROM jos_content WHERE sectionid <> 0 AND sectionid <> 1
    AND sectionid <> 2 AND sectionid <> 8 AND sectionid <> 9 AND sectionid <> 11 AND sectionid <> 12
    AND sectionid <> 14 AND sectionid <> 17 AND sectionid <> 19 AND sectionid <> 36;";
//    $sql = "SELECT * FROM jos_content WHERE CONVERT(`title` USING utf8mb4) = 'Студенти Рівненського інституту Київського університету права НАН України провели акцію присвячену Дню боротьби зі СНІДом.' LIMIT 50";
    return $wpdb->get_results($sql);
}

function get_category_post($item): array
{
    $category_array = [];
    $string_lower = mb_strtolower($item->title, "UTF-8");

    if ((int) $item->sectionid === 3 || (int) $item->sectionid === 15
        || (int) $item->sectionid === 16 || (int) $item->sectionid === 27) {
	      $category_array[] = get_category_id('studentstvo');
    }

    if ((int) $item->sectionid === 13) {
      if (str_contains($string_lower, 'фоторепортаж') || str_contains($string_lower, 'звіт')) {
	      $category_array[] = get_category_id('studentstvo');
      }
    }

    if ((int) $item->sectionid === 4) {
	    $category_array[] = get_category_id('mayster-klas');
    }

    if ((int) $item->sectionid === 5) {
	    $category_array[] = get_category_id('konferentsii');
    }

    if ((int) $item->sectionid === 7) {
        if (str_contains($string_lower, 'ректор') || str_contains($string_lower, 'універ')
            || str_contains($string_lower, 'куп нан') || str_contains($string_lower, 'аукціон')
            || str_contains($string_lower, 'співпрац') || str_contains($string_lower, 'виставк')
            || str_contains($string_lower, 'план') || str_contains($string_lower, 'угод')
            || str_contains($string_lower, 'часопис') || str_contains($string_lower, 'бакалавр')
            || str_contains($string_lower, 'магістр') || str_contains($string_lower, 'колег')
            || str_contains($string_lower, 'студент') || str_contains($string_lower, 'аспірант')) {
	        $category_array[] = get_category_id('universitet');
        }
    }

    if (str_contains($string_lower, 'круглий стіл')) {
	    $category_array[] = get_category_id('kruhly-stoly');
    }

    if (str_contains($string_lower, 'семінар')) {
	    $category_array[] = get_category_id('seminary');
    }

    if (str_contains($string_lower, 'увага')) {
	    $category_array[] = get_category_id('ogoloshennya');
    }

    if (str_contains($string_lower, 'абітур') || str_contains($string_lower, 'вступ')) {
	    $category_array[] = get_category_id('abituriientu');
    }

    if (str_contains($string_lower, 'студент')) {
	    $category_array[] = get_category_id('studentstvo');
    }

    if (str_contains($string_lower, 'конференц')) {
	    $category_array[] = get_category_id('konferentsii');
    }

    $category_array = array_unique($category_array);
    if (empty($category_array)) {
	    $category_array[] = 1;
      return $category_array;
    }

	return $category_array;
}

function get_category_id($slug): int
{
	return get_category_by_slug($slug)->term_id;
}
function parse_article($old_post_content): string
{
	global $wpdb;

  $new_post_content = str_replace("div", "p", $old_post_content );
	$new_post_content = preg_replace("~<p\s+.*?>~i",'<p>', $new_post_content);
	$new_post_content = preg_replace("~<span\s+.*?>~i",'<span>', $new_post_content);
	$new_post_content = str_replace( "http://rivne.kul.kiev.ua/", "", $new_post_content );
	$new_post_content = str_replace( "http://", "http1", $new_post_content );
	$new_post_content = str_replace( "https://", "http2", $new_post_content );
	$new_post_content = str_replace( "//", "/", $new_post_content );
	$new_post_content = str_replace( "http1", "http://", $new_post_content );
	$new_post_content = str_replace( "http2", "https://", $new_post_content );

	//Table
	$pos1 = stripos($new_post_content, '<table');
	$pos2 = stripos($new_post_content, '</table>');
	if ($pos1) {
		$table = substr($new_post_content, $pos1, $pos2 - $pos1);
		$image_flag = str_contains($table, "<img");
		if ($image_flag) {
			$new_post_content = preg_replace("~<table\s+.*?>~i",'<div class="data">', $new_post_content);
			$new_post_content = str_replace("</table>", "</div>", $new_post_content);
			$new_post_content = str_replace( array( "<tbody>", "</tbody>", "<tr>", "</tr>", "<td>", "</td>", "<tr align=\"center\">", "<td align=\"center\">" ), "", $new_post_content );
		}
	}

    libxml_use_internal_errors(true) && libxml_clear_errors(); // for html5
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->substituteEntities = true;
    $new_post_content = '<div>' . $new_post_content . '</div>';
    $new_post_content = mb_convert_encoding($new_post_content, 'HTML-ENTITIES', 'UTF-8');
    $doc->loadHTML($new_post_content, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
    $elements = $doc->getElementsByTagName('script');
    for ($i = $elements->length; --$i >= 0; ) {
        $script = $elements->item($i);
        if ($script) {
            $script->parentNode->removeChild($script);
        }
    }
    $images = $doc->getElementsByTagName('img');
    for ($i = $images->length; --$i >= 0; ) {
        $image = $images->item($i);
        if ($image && $image->parentNode->tagName === 'a') {
            $image->parentNode->setAttribute('class', 'zoom');
        }
    }

//    Удаление стилей картинок и картинов с _s
    $img_tags = $doc->getElementsByTagName('img');
    foreach ($img_tags as $img) {
        $img->removeAttribute('style');
    }
////////////////////////////////////////////


//	$finder = new DomXPath($doc);
//	$classname="data";
//	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
//	for ($i = $nodes->length; --$i >= 0; ) {
//		$node = $nodes->item($i);
//		$count = getDivChildrenCount($node);
//		if ( $node && $count === 1 ) {
//			$node->setAttribute('class', 'data dv-100');
//		} elseif ( $node && $count === 2 ) {
//			$node->setAttribute('class', 'data dv-50');
//		} elseif ( $node && $count === 3 ) {
//			$node->setAttribute('class', 'data dv-30');
//		} elseif ( $node && $count === 4 ) {
//			$node->setAttribute('class', 'data dv-25');
//		} elseif ( $node && $count > 4 ) {
//			$node->setAttribute('class', 'data dv-auto');
//		}
//	}

//	$tags = $doc->getElementsByTagName('img');
//	foreach ($tags as $tag) {
//	  $table = $wpdb->prefix.'src_images';
//    $path = $tag->getAttribute('src');
//    $path = str_replace('images/', '', $path);
//	  $data = array( 'path' => $path);
//	  $wpdb->insert($table,$data);
//	}

	return $doc->saveHTML((new \DOMXPath($doc))->query('/')->item(0));
}

function aditional_parse($content)
{
    $pattern = '/<p>\s*<\/p>/';
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<(p|span)>\s*<\/\1>/';
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<(p|span)>\s*<\/\1>|<a[^>]*>\s*<\/a>/';
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<span[^>]*>\s*<br\s*\/?>\s*<\/span>/i';
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<span>\s*<br\s*\/?>\s*<br\s*\/?>\s*<\/span>/';
    $content = preg_replace($pattern, '', $content);

    $content = preg_replace('/<h6>\s*<\/h6>/', '', $content);
    $content = preg_replace_callback(
        '/<h6(.*?)>(.*?)<\/h6>/',
        function ($matches) {
            if (trim($matches[2]) !== '') {
                return '<p>' . $matches[2] . '</p>';
            } else {
                return '';
            }
        },
        $content
    );

    $pattern = '/<img\s+[^>]*?\bsrc\s*=\s*["\']([^"\']*_s\.[^"\']*)["\'][^>]*>/i';

// Замена найденных тегов <img> с риставкой '_s' в src на теги без этой риставки
    $content = preg_replace_callback($pattern, function($matches) {
        $src = $matches[1];
        if (strpos($src, '_s') !== false) {
            $src = str_replace('_s.', '.', $src);
            return '<img src="' . $src . '">';
        } else {
            return $matches[0];
        }
    }, $content);

    $content = str_replace("href=\"images", "href=\"/images", $content );

    return $content;
}

function getDivChildrenCount($div)
{
	$count = 0;
	foreach($div->childNodes as $node)
		if(!($node instanceof \DomText))
			$count++;

	return $count;
}
function check_posts_consist_meta(): void
{
	$args = array(
		'meta_key' => 'jos_table',
		'meta_value' => '1',
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => 1
	);
	$posts = get_posts($args);

	if (empty($posts)) {
		echo 1;
	} else {
		echo 2;
	}
}