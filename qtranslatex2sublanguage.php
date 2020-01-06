<?php

/*
Plugin Name: qTranslateX 2 Sublanguage
Version: 1.0
*/


function migrate_post_qtranslatex2sublanguage($result) {
	global $sublanguage_admin;

  $post = get_post($result->ID, ARRAY_A);

  if (isset($sublanguage_admin) && $post){

    $post['post_title'] = preg_replace('#<!--:--><!--:([a-z]{2})-->#', '[:$1]', $post['post_title']); // replace middle legacy syntax <!--:--><!--:en--> into [:en]
    $post['post_title'] = str_replace('<!--:-->', '[:]', $post['post_title']); // replace end legacy syntax <!--:--> into [:]
    $post['post_title'] = preg_replace('#<!--:([a-z]{2})-->#', '[:$1]', $post['post_title']); // replace start legacy syntax <!--:en--> into [:en]
    if ( 3 == strlen($post['post_title']) - strrpos($post['post_title'], "[:]") ) {
        // remove last [:] but remember it exists only if string is translated
        $post['post_title'] = substr($post['post_title'],0, strlen($post['post_title'])-3);
    }
    $exp = preg_split('#\[:([a-z]{2})\]#', $post['post_title']);

    array_shift($exp);
    preg_match_all('#\[:([a-z]{2})\]#',$post['post_title'],$matches);

    $languages = $matches['1'];
    foreach( $languages as $key =>$l ){
        $l = strtolower($l);
        $languages[$key] = $l;
    }
    foreach( $exp as $key => $e ){
        $langs[ $languages[$key] ]['title'] = $e;
    };



    $post['post_content'] = preg_replace('#<!--:--><!--:([a-z]{2})-->#', '[:$1]', $post['post_content']);
    $post['post_content'] = str_replace('<!--:-->', '[:]', $post['post_content']);
    $post['post_content'] = preg_replace('#<!--:([a-z]{2})-->#', '[:$1]', $post['post_content']);
    if ( 3 == strlen($post['post_content']) - strrpos($post['post_content'], "[:]") ) {
        // remove last [:] but remember it exists only if string is translated
        $post['post_content'] = substr($post['post_content'],0, strlen($post['post_content'])-3);
    }
    $exp = preg_split('#\[:([a-z]{2})\]#', $post['post_content']);
    array_shift($exp);
    preg_match_all('#\[:([a-z]{2})\]#',$post['post_content'],$matches);
    $languages = $matches['1'];
    foreach( $languages as $key =>$l ){
        $l = strtolower($l);
        $languages[$key] = $l;
    }
    foreach( $exp as $key => $e ){
        $langs[ $languages[$key] ]['content'] = $e;
        if ($key == 0 && count($exp) > 2) { // if post has <!--more--> tag, add this tag to first language as well
            $langs[ $languages[$key] ]['content'] .= "<!--more-->";
        }
    };

    $post['post_excerpt'] = preg_replace('#<!--:--><!--:([a-z]{2})-->#', '[:$1]', $post['post_excerpt']);
    $post['post_excerpt'] = str_replace('<!--:-->', '[:]', $post['post_excerpt']);
    $post['post_excerpt'] = preg_replace('#<!--:([a-z]{2})-->#', '[:$1]', $post['post_excerpt']);
    if ( 3 == strlen($post['post_excerpt']) - strrpos($post['post_excerpt'], "[:]") ) {
        // remove last [:] but remember it exists only if string is translated
        $post['post_excerpt'] = substr($post['post_excerpt'],0, strlen($post['post_excerpt'])-3);
    }
    $exp = preg_split('#\[:([a-z]{2})\]#', $post['post_excerpt']);
    array_shift($exp);
    preg_match_all('#\[:([a-z]{2})\]#',$post['post_excerpt'],$matches);
    $languages = $matches['1'];
    foreach( $languages as $key =>$l ){
        $l = strtolower($l);
        $languages[$key] = $l;
    }
    foreach( $exp as $key => $e ){
        $langs[ $languages[$key] ]['excerpt'] = $e;
    };



		if (isset($langs) && $langs) {
			foreach($langs as $lang => $fields) {
				$language = $sublanguage_admin->find_language($lang);

				if ($sublanguage_admin->is_main($language)) {
					wp_update_post(array(
						'ID' => $result->ID,
						'post_title' => isset($fields['title']) ? $fields['title'] : '',
						'post_excerpt' => isset($fields['excerpt']) ? $fields['excerpt'] : '',
						'post_content' => isset($fields['content']) ? $fields['content'] : ''
					));
				} else {
					if (isset($fields['title']) && $fields['title']) {
						update_post_meta($result->ID, "_{$lang}_post_title", $fields['title']);
					}
					if (isset($fields['content']) && $fields['content']) {
						update_post_meta($result->ID, "_{$lang}_post_content", $fields['content']);
					}
					if (isset($fields['excerpt']) && $fields['excerpt']) {
						update_post_meta($result->ID, "_{$lang}_post_excerpt", $fields['excerpt']);
					}
				}
			}
		}

  }
}

function migrate_qtranslatex2sublanguage() {

	register_batch_process( array(
		'name'     => 'qtranslateX to Sublanguage',
		'type'     => 'post',
		'callback' => 'migrate_post_qtranslatex2sublanguage',
		'args'     => array(
			'posts_per_page' => 1,
			'post_type'      => 'any',
		),
	) );
}
add_action( 'locomotive_init', 'migrate_qtranslatex2sublanguage' );
