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
    $sql = "SELECT * 
      FROM qkfup_content 
      WHERE (catid IN (10, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 42, 43, 44, 45, 46, 48, 49, 50, 51, 52, 53, 54, 55))
      AND id NOT IN (744, 911, 1290, 1441, 2632, 254, 624, 916, 1001, 1020)
      AND language != '*' 
      LIMIT 5;";
//    $sql = "SELECT * FROM qkfup_content LIMIT 1;";
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

function setCategory($post_id, $cat_id)
{
    global $wpdb;
    $wpdb->insert(
        $wpdb->term_relationships,
        array(
            'object_id' => $post_id,
            'term_taxonomy_id' => $cat_id,
            'term_order' => '0'
        )
    );
}

function categoryTable($cat_id)
{
    switch ($cat_id) {
        case 20:
            $new_cat_id = 31;
            break;
        case 21:
            $new_cat_id = 32;
            break;
        case 22:
            $new_cat_id = 33;
            break;
        case 23:
            $new_cat_id = 34;
            break;
        case 24:
            $new_cat_id = 35;
            break;
        case 25:
            $new_cat_id = 36;
            break;
        case 26:
            $new_cat_id = 37;
            break;
        case 27:
            $new_cat_id = 38;
            break;
        case 28:
            $new_cat_id = 39;
            break;
        case 29:
            $new_cat_id = 40;
            break;
        default:
            $new_cat_id = 0;
            break;
    }

    return $new_cat_id;
}

function setMetaDescription($post_id, $lang, $post_type, $post_title)
{
  $metaBlogUk = "<title> - Блог</title><meta content='" . $post_title ." бурова компанія ⭐Акваторія⭐ ✅ Понад 15 років досвіду. Телефонуйте: (097)-700-10-10'>";
  $metaBlogRu = "<title>" . $post_title ." - Блог</title><meta name='description' content='" . $post_title ." буровая компания ⭐Акватория⭐ ✅ Более 15 лет опыта. Звоните: (097)-700-10-10'>";
  $metaPortfolioUk ="<title>" . $post_title ." - Портфоліо компанії Акваторія</title><meta  content='Портфоліо компанії Акваторія ✅ " . $post_title ." ✅ Понад 15 років досвіду. Телефонуйте: (097)-700-10-10'>";
  $metaPortfolioRu = "<title>" . $post_title ." - Портфолио компании Акватория</title><meta name='description' content='Портфолио компании Акватория ✅ " . $post_title ." ✅ Более 15 лет опыта. Звоните: (097)-700-10-10'>";

  if ($lang === 'ru') {
    if ($post_type === 'post') {
        update_post_meta( $post_id, '_yoast_wpseo_metadesc', $metaBlogRu);
    } elseif ($post_type === 'portfolio') {
        update_post_meta( $post_id, '_yoast_wpseo_metadesc', $metaPortfolioRu);
    }
  } elseif ($lang === 'uk') {
      if ($post_type === 'post') {
          update_post_meta( $post_id, '_yoast_wpseo_metadesc', $metaBlogUk);
      } elseif ($post_type === 'portfolio') {
          update_post_meta( $post_id, '_yoast_wpseo_metadesc', $metaPortfolioUk);
      }
  }
}

function setRussianTranslation($post_id)
{
    $type = get_post_type( $post_id );
    $trid = apply_filters( 'wpml_element_trid', NULL, $post_id, 'post_' . $type );
    $language_code = 'ru';
    $language_args = [
        'element_id' => $post_id,
        'element_type' => 'post_'.$type,
        'trid' => $trid,
        'language_code' => $language_code,
        'source_language_code' => null,
    ];

    do_action( 'wpml_set_element_language_details', $language_args );
}

function setAcfBannerData($post_id, $image, $title, $description)
{
//    $post_image = json_decode($image);
//    $post_image =  home_url() . "/" . $post_image->image_intro;

    $block_banner_fields = [
        'title' => $title,
        'desc' => $description,
//        'image' => $post_image,
    ];

    update_field('block_banner', $block_banner_fields, $post_id);
}

function setAcfPortfolioData($post_id, $joomla_id)
{
    global $wpdb;
    $sql = "SELECT 
    fv.item_id, 
    f.name AS category_name, 
    fv.value
    FROM 
    qkfup_fields_values fv
    JOIN 
    qkfup_fields f ON fv.field_id = f.id
    WHERE 
    fv.item_id = $joomla_id";
    $fields = $wpdb->get_results($sql);

    $portfolio_fields = [];
    foreach ($fields as $field) {
        if ($field->category_name === 'gorod' || $field->category_name === 'galereya') {
            continue;
        } elseif ($field->category_name === 'lokatsiya') {
            $portfolio_fields['city'] = $field->value;
        } else {
            $portfolio_fields[$field->category_name] = $field->value;
        }
    }
    update_field('portfolio_single_fields', $portfolio_fields, $post_id);
}