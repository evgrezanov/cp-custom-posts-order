<?php

/**
*Plugin Name: cp-custom-posts-order
*Plugin URI: -
*Description: Плагин предназначен для произвольной сортировки записей в рамках термина.
*Version: 0.3
*Author: Rezanov Evgenii
*Author URI: http://casepress.org/support
 */

 /*страница сортировки*/
 function cp_cp_podmenu_html(){
	if (!current_user_can('manage_options')){
	  wp_die( __('У вас нет прав для просмотра данной страницы.') );
	}
	

	echo '<h2>'.__('Сортировка записей таксономии').'</h2>';
?>	
	    <select id="selecttaxterm" name="selecttaxterm" >
		    <option value='0'><?php _e('--Выберите термин--'); ?></option>
	        <?php
					      
						  $argsTax=array(
                                'public'   => true,
                                '_builtin' => false
                            );
						  
						  $output = 'objects';
                          $operator = 'or';
						  
						  $argsTerm = array(
                               'number' 		=> 0,
                               'offset' 		=> 0,
                               'orderby' 		=> 'name',
                               'order' 		    => 'ASC',
                               'hide_empty' 	=> true,
                               'fields' 		=> 'all',
                               'slug' 		    => '',
                               'hierarchical'   => false,
                               'name__like' 	=> '',
                               'pad_counts' 	=> false,
                               'get' 			=> '',
                               'child_of' 	    => 0,
                               'parent' 		=> '',
                            );
                           
						  
                          $taxonomies=get_taxonomies($argsTax,$output,$operator);
					     
						  if  ($taxonomies) {
				              foreach ($taxonomies as $taxonomy ) {
                                  echo '<optgroup class="'.$taxonomy->name.'" label="'.$taxonomy->label.'">';
						          $myterms = get_terms($taxonomy->name, $argsTerm);
								  if ($myterms) {
								      foreach ($myterms as $term){
                                          echo '<option class="'.$term->taxonomy.'" value="'.$term->term_id.'">'.$term->name.'</option>';
                                        } 
							        }
                                }
						    }			       
					   ?> 
	    </select>
		<!-- выбор элемента списка -->
		<script type="text/javascript">
	        var dropdown = document.getElementById("selecttaxterm");
			var loc = window.location;
	        function onCatChange() {
				if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
					location.href = "<?php echo home_url(); ?>/wp-admin/edit.php?page=custom_post_order&term_id="+dropdown.options[dropdown.selectedIndex].value;
		        }
	        }
	        dropdown.onchange = onCatChange;
        </script>
		</br>
		

<?php	
    if ( isset($_REQUEST['term_id'])){
      
	  $term_id=$_REQUEST['term_id'];
	  $site_url = get_site_url();
      $our_term_url = $site_url.'/wp-admin/edit.php?page=custom_post_order&term_id='.$term_id;
	  	
	  // если выбрана ли сортировка по алфавиту 
	  if ( isset($_REQUEST['date'])) {
	      $sort_date=$_REQUEST['date'];		  
		}
	  
	  // если выбрана сортировка по дате
	  if ( isset($_REQUEST['abc'])) {
	      $sort_abc=$_REQUEST['abc'];
		}
	  	  
	  		// проверим было ли отсортировано по date\abc
	        $check_type_sort_option = get_option('cp-sort-type-term-id-'.$term_id);
	        ////error_log($check_type_sort_option);
			if (($check_type_sort_option=='date') or ($check_type_sort_option=='')) {
	            $list_title = 'Cортировка записей по дате';
            }
			
	        if ($check_type_sort_option=='abc') {
	            $list_title = 'Cортировка записей по алфавиту';
            }
			
			if ($check_type_sort_option=='custom') {
			    $list_title = 'Произвольная сортировка записей';
			}
			
			/*if ($check_type_sort_option=='') {
			    $list_title = 'Cортировка записей по дате';
			}*/
			
	        // узнаем были ли отсортированы записи в рамках данного термина
	        $option_name = 'cp-sort-term-id-'.$term_id;
	        $term_sort = get_option($option_name);
	        $meta_key = 'cp-term-id-'.$term_id;
	  
	        // узнаем тип сортировки
	        $sort_type = 'cp-sort-type-term-id-'.$term_id;
	  
	        // узнаем таксономию выбранного термина
	        $tax_name = cp_get_tax_name_by_term_id($term_id);
            $tax_obj = get_taxonomy($tax_name);
	        $term_obj = get_term($term_id,$tax_name);
	  
	        // ссылка на страницу термина
	        $term_link = get_term_link((int)$term_id,$tax_name);
	        echo '<h3>'.$tax_obj->label.': <a href="'.$term_link.'">'.$term_obj->name.'</a></h3>';
      
	  // вывод списка постов
	  if ( isset($tax_name)) {						
                
				// если ранее была произвольная сортировка
				if ($term_sort=='true'){
			        $args = array(
	                    'tax_query' => array(
		                    array(
			                    'taxonomy' => $tax_name,
			                    'field' => 'id',
			                    'terms' => array((int)($term_id))
		                    )
	                    ),
	                    'posts_per_page' => -1 ,
						'orderby' => 'meta_value_num',
						'meta_key' => $meta_key,
						'order' => 'ASC'
                    );
			    }
				
				// по умолчанию 
				else {
				    $args = array(
					    'tax_query' => array(
		                    array(
			                    'taxonomy' => $tax_name,
			                    'field' => 'id',
			                    'terms' => array( $term_id )
		                    )
	                    ),
	                    'posts_per_page' => -1,
						'orderby' => 'date',
						'order' => 'DESC'
                    );
					$list_title = 'Cортировка записей по дате';
					$sort_def = 'true';
				}
				
				// по алфавиту
				if ( isset($sort_abc))  {
				    $args = array(
					    'tax_query' => array(
		                    array(
			                    'taxonomy' => $tax_name,
			                    'field' => 'id',
			                    'terms' => array( $term_id )
		                    )
	                    ),
	                    'posts_per_page' => -1,
						'orderby' => 'title',
						'order' => 'ASC'
                    );
					$list_title = 'Cортировка записей по алфавиту';
				}
				
				// по дате
				if ( isset($sort_date) ) {
				    $args = array(
					    'tax_query' => array(
		                    array(
			                    'taxonomy' => $tax_name,
			                    'field' => 'id',
			                    'terms' => array( $term_id )
		                    )
	                    ),
	                    'posts_per_page' => -1,
						'orderby' => 'date',
						'order' => 'DESC'
                    );
					$list_title = 'Cортировка записей по дате';
				}
            
			
			$posts = get_posts( $args );
            
			echo '<a href="'.add_query_arg( 'date', '1',$our_term_url ).'">'.__('сортировать по дате').'</a></br>';
			echo '<a href="'.add_query_arg( 'abc', '1',$our_term_url ).'">'.__('сортировать по алфавиту').'</a>';
			
			echo '<h3>'.__($list_title).':</h3>';
			
			// вывод постов в админке
			echo '<ul id="sortable">';
			
			$i=1;
            foreach($posts as $pst) {
			    /*если была выбрана сортировка по алфавиту или по дате то сразу пропишем мету*/
				$i++;
				if ((isset ($sort_date)) or (isset ($sort_def))){
					update_post_meta($pst->ID, 'cp-term-id-'.$term_id, $i);
					
					// сразу запишем опцию
					update_option('cp-sort-type-term-id-'.$term_id, 'date');
				}
				if (isset ($sort_abc)){
					update_post_meta($pst->ID, 'cp-term-id-'.$term_id, $i);
					
					// сразу запишем опцию 
					update_option('cp-sort-type-term-id-'.$term_id, 'abc');
				}
				/*---закончили---*/
	            
				$sort = get_post_meta($pst->ID, 'cp-term-id-'.$term_id, true);
				echo '<li class="ui-state-default" id="arrayorder_'.$pst->ID.'">'.$pst->post_title.'</li>';
			}
			echo '</ul>';
			wp_reset_query();
			echo '<div id="info"></div>';
			
	    }
	}
	?>
    <?php /* сортировка и передача порядка в функцию */?>
	<script type="text/javascript">
        $(function(){
           $("#sortable").sortable({
		        update : function () {
			        var sort = $("#sortable").sortable('serialize', { key: 'post_id' });
			        console.log(sort);
					tax_name = <?php if (isset ($tax_name)) {echo "'".$tax_name."'";} ?>;
					term_id = <?php if (isset ($term_id)) {echo "'".$term_id."'";} ?>;
					$.ajax({
					    data: {
					        sort : sort,
							tax_name : tax_name,
							term_id : term_id,
							action : 'cp_save_sort'
						},
						url: ajaxurl,
						success: function(data) {
                                }
					});
			    }
		    });
        });
	</script>
	<?php
}

/*вывод произвольной сортировки на страницах сайта*/
add_filter( 'pre_get_posts' , 'cp_my_change_order' );
function cp_my_change_order( $query ) {
	if($query->is_main_query() && !is_admin()) {
		
		/* категория*/
		if (is_category()) {
			$queried_object = get_queried_object();
			$term_id = get_queried_object()->term_id;
			
			if (isset($term_id)) {
				$check_option=get_option('cp-sort-term-id-'.$term_id);
				if ($check_option=='true') {
				    $query->set( 'orderby' , 'meta_value_num' );
					$query->set( 'meta_key', 'cp-term-id-'.$term_id);
					$query->set( 'order', 'ASC');
					$query->set( 'posts_per_page', -1);
				}
			}
		}
		
		
		/* метки */
		if (is_tag()) {
			$queried_object = get_queried_object();
			$term_id = get_queried_object()->term_id;
			if (isset($term_id)) {
			    $check_option=get_option('cp-sort-term-id-'.$term_id);
				if ($check_option=='true') {
					$query->set( 'orderby' , 'meta_value_num' );
					$query->set( 'meta_key', 'cp-term-id-'.$term_id);
					$query->set( 'order', 'ASC');
					$query->set( 'posts_per_page', -1);
				}
			}
		}
		
		// таксономия
		if (is_tax()) {
			$queried_object = get_queried_object();
			$term_id = get_queried_object()->term_id;
			if (isset($term_id)) {
			    $check_option=get_option('cp-sort-term-id-'.$term_id);
				if ($check_option=='true') {
					$query->set( 'orderby' , 'meta_value_num' );
					$query->set( 'meta_key', 'cp-term-id-'.$term_id);
					$query->set( 'order', 'ASC');
					$query->set( 'posts_per_page', -1);
				}
			}
		}
        
		/*архив
		if (is_archive()) {
			////error_log('страница архива');
			//$queried_object = get_queried_object();
			$term_id = get_queried_object()->term_id;
			if (isset($term_id)) {
			    $check_option=get_option('cp-sort-term-id-'.$term_id);
				if ($check_option=='true') {
				    ////error_log('отсортировано cp-term-id-'.$term_id);
					$query->set( 'orderby' , 'meta_value_num' );
					$query->set( 'meta_key', 'cp-term-id-'.$term_id);
					$query->set( 'order', 'ASC');
					$query->set( 'posts_per_page', '-1');
				//print_r($query);	
				}
			}
			
		}*/
	}
	return $query;
	
}

/*при добавлении новой записи, при условии что она находится 
в отсортированной категории. она добавляется в конец списка*/
add_action('publish_post','cp_update_sort_post');
function cp_update_sort_post($post_ID) {
    // узнаем таксономии
	$taxonomies = get_the_taxonomies($post_ID);
	$taxonomies_names = array();
	if (isset($taxonomies)) {
	    foreach ($taxonomies as $key=>$value) {
			$taxonomies_names[]=$key;
		}
	}
	// узнаем термины, проверим есть ли по ним пользовательская сортировка
	$term_ids = wp_get_post_terms( $post_ID, $taxonomies_names, array("fields" => "ids") );
	if (isset($term_ids)) {
	    foreach ($term_ids as $key=>$term_id) {
		    $option_name = 'cp-sort-term-id-'.$term_id;
			$check_option = get_option($option_name);
			
			/*если по данному термину есть произвольная
			  сортировка. добавим новую запись в конец
			  списка*/
			if ($check_option='true') {
				// присвоим номер в зависимости от выбраного ранее типа сортировки
				$check_type = get_option('cp-sort-type-term-id-'.$term_id);
				
				$meta_key = 'cp-term-id-'.$term_id;
				
				$check=get_post_meta($post_ID,$meta_key,true);
				
				if ($check==''){
				
				    switch ($check_type) {
				    
					    case 'custom' :			
				            $args = array(
					            'tax_query' => array(
		                            array(
			                            'taxonomy' => cp_get_tax_name_by_term_id($term_id),
			                            'field' => 'id',
			                            'terms' => array( $term_id )
		                            )
	                            ),
	                            'posts_per_page' => -1,
						        'orderby' => 'title',
						        'order' => 'ASC'
                            );
							$posts = get_posts($args);
							$sort_array=array();
				            foreach ($posts as $post) : setup_postdata($post);							
								$my_post_id=$post->ID;
					            $sort_array[]= $my_post_id;
				            endforeach;		
                            wp_reset_query();
							// находим текущий ключ
                            $i_KeyCurrent =	array_search($post_ID, $sort_array);
							
							if ($i_KeyCurrent==0){
							    // если надо добавить в начало
								$args = array(
	                                'tax_query' => array(
		                                array(
			                                'taxonomy' => cp_get_tax_name_by_term_id($term_id),
			                                'field' => 'id',
			                                'terms' => array($term_id)
		                                )
	                                ),
	                                'posts_per_page' => -1,
					                'orderby' => 'meta_value_num',
					                'meta_key' => 'cp-term-id-'.$term_id,
					                'order' => 'ASC'
                                );
							    $posts = get_posts($args);
								$i=2;
								foreach ($posts as $post) : setup_postdata($post);							
								    $my_post_id=$post->ID;
									update_post_meta($post->ID,$meta_key,$i);
									$i++;
								endforeach;
								wp_reset_query();
								update_post_meta($post_ID, $meta_key, '1');
							}
							
							else {
								// кол-во элементов в массиве
                                $i_CountElement = count($sort_array);
							    // предыдущий ID
	                            $i_PrevID = ( $i_KeyCurrent == 0 ) 
		                        ? $sort_array[$i_CountElement - 1] : $sort_array[$i_KeyCurrent - 1];
							    // предыдущий порядок
							    $i_PrevID_meta = get_post_meta($i_PrevID, $meta_key, true);
							    // порядок для нового поста
							    $new_meta=$i_PrevID_meta+1;
							    
								$args = array(
	                                'tax_query' => array(
		                                array(
			                                'taxonomy' => cp_get_tax_name_by_term_id($term_id),
			                                'field' => 'id',
			                                'terms' => array($term_id)
		                                )
	                                ),
	                                'posts_per_page' => -1,
					                'orderby' => 'meta_value_num',
					                'meta_key' => 'cp-term-id-'.$term_id,
					                'order' => 'ASC'
                                );
							    $posts = get_posts($args);
							    $flag=0;
							    // порядок для нового поста
							    $counter=$new_meta;
								$i=1;
						        foreach ($posts as $post) : setup_postdata($post);							
									if ($flag==1) {
								        $counter++;
									    update_post_meta($post->ID,$meta_key,$counter);
								    }
    								$my_post_id=$post->ID;
					                if ($my_post_id==$i_PrevID) {
								        $flag=1;
								    }

				                endforeach;
							
							    wp_reset_query();
				
     					        update_post_meta($post_ID, $meta_key, $new_meta);
	                             
							}
	    				    break;
						
					    case 'date':
					        $args = array(
					            'tax_query' => array(
		                            array(
			                            'taxonomy' => cp_get_tax_name_by_term_id($term_id),
			                            'field' => 'id',
			                            'terms' => array( $term_id )
		                            )
	                            ),
	                            'posts_per_page' => -1,
						        'orderby' => 'date',
						        'order' => 'DESC'
                            );
						    $posts = get_posts( $args );
						    $i=1;
                            foreach($posts as $pst) {
					            update_post_meta($pst->ID, 'cp-term-id-'.$term_id, $i);
								$i++;
						    }
						    wp_reset_query();
					        break;
					
					    case 'abc':
					        $args = array(
					            'tax_query' => array(
		                            array(
			                            'taxonomy' => cp_get_tax_name_by_term_id($term_id),
			                            'field' => 'id',
			                            'terms' => array( $term_id )
		                            )
	                            ),
	                            'posts_per_page' => -1,
						        'orderby' => 'title',
						        'order' => 'ASC'
                            );
						    $posts = get_posts( $args );
						    $i=1;
                            foreach($posts as $pst) {
					            update_post_meta($pst->ID, 'cp-term-id-'.$term_id, $i);
								$i++;
						    }
						    wp_reset_query();
						    break;
				    }	
				}
			}
		}
	}
}

/*сохраняем в мету порядок поста в определенном термине
   ключ меты формируем по правилу 'cp-term-id-'.$term_id
   установим опцию о том что термин отсортирован, название опции 
   формируем по правилу 'cp-sort-term-id-'.$term_id 
 */
add_action('wp_ajax_cp_save_sort', 'cp_save_sort');
function cp_save_sort() {
   $post_ids = $_REQUEST['sort'];
   $tax_name = $_REQUEST['tax_name'];
   $term_id = $_REQUEST['term_id'];
   $meta_key = 'cp-term-id-'.$term_id;
   // тут какой-то бред
   $post_ids = '&'.$post_ids;
   $post_id_array = explode('&post_id=',$post_ids);

   foreach($post_id_array as $key=>$value) {
        if ($value<>'') {
		    ////error_log('пост ид = '.$value.' meta_key = '.$meta_key.' порядок = '.$key);
		    update_post_meta($value, $meta_key, $key);
        }			
	} 

	$option_name = 'cp-sort-term-id-'.$term_id;
	// тип сортировки
	$type_option_name = 'cp-sort-type-term-id-'.$term_id;
	update_option($type_option_name, 'custom');
	////error_log('обновили опцию = '.$option_name.' в true');
	update_option($option_name, 'true');
   exit;  
}

/* получить Ид тега по имени 
function cp_get_tag_ID($tag_name) {
    $tag = get_term_by('name', $tag_name, 'post_tag');
    if ($tag) {
        return $tag->term_id;
    } 
	else {
        return 0;
    }
}*/

/*функция определяет название таксономии по переданому ид термина*/
function cp_get_tax_name_by_term_id($term_id) {
    $argsTax=array(
        'public'   => true,
        '_builtin' => false
    );
	$output = 'objects';
    $operator = 'or';
	
	$argsTerm = array(
        'number' 		=> 0,
        'offset' 		=> 0,
        'orderby' 		=> 'id',
        'order' 		=> 'ASC',
        'hide_empty' 	=> true,
        'fields' 		=> 'all',
        'slug' 		    => '',
        'hierarchical'  => true,
        'name__like' 	=> '',
        'pad_counts' 	=> false,
        'get' 			=> '',
        'child_of' 	    => 0,
        'parent' 		=> '',
    );
	$taxonomies=get_taxonomies($argsTax,$output,$operator);
	foreach ($taxonomies as $taxonomy ) {
	    $myterms = get_terms($taxonomy->name, $argsTerm);
		if ($myterms) {
	        foreach ($myterms as $term){
				if ($term_id==$term->term_id) {
				    $tax_name=$taxonomy->name;  
				}
			}
		}
	}
	return $tax_name;
}

/*добавим свои скрипты в админку*/
add_action('admin_head', 'cp_custom_admin_js');
function cp_custom_admin_js() {
    global $pagenow;
	$edit_page = 0;
	if ($pagenow=='edit.php'){
	    $tmp='?page=custom_post_order&term_id=';
		$current_url = home_url(add_query_arg(array()));
	    $edit_page = substr_count($current_url, $tmp);
		if ($edit_page==1) {
	        echo '<link rel="stylesheet" href="'.plugin_dir_url(__FILE__).'css/jquery-ui-1.10.4.custom.min.css">';
	        echo '<script type="text/javascript" src="'. plugin_dir_url(__FILE__).'js/jquery-1.10.2.js' . '"></script>';
	        echo '<script type="text/javascript" src="'. plugin_dir_url(__FILE__).'js/jquery-ui-1.10.4.custom.min.js' . '"></script>'; 
		}
	}
}

/*добавляем пункт меню*/
add_action('admin_menu', 'cp_podmenu'); 
function cp_podmenu() {
	/*add_management_page*/
	add_posts_page('Сортировка записей таксономии',
	               'Сортировка',
	               'manage_options',
	               'custom_post_order',
	               'cp_cp_podmenu_html');
}
 
/*добавляем ссылку на переход на строницу сортировки*/ 
add_action('admin_notices', 'cp_example_admin_notice');
function cp_example_admin_notice() {
  global $pagenow;
  global $wp;
  $edit_page=0;  
  if ($pagenow == 'edit-tags.php') {
      $tmp ='?action=edit&taxonomy=';
      $current_url = home_url(add_query_arg(array()));
	  $edit_page = substr_count($current_url, $tmp);
	  if ($edit_page==1) {
	        $tag_ID = $_REQUEST['tag_ID'];
		    $tax_name = $_REQUEST['taxonomy'];
		    $post_count = cp_get_post_count($tax_name,$tag_ID);
		    if ($post_count>0) {
                echo '<div class="updated"><p>';
                printf(__('Для сортировки записей перейдите по <a href="%1$s">ссылке</a>'), home_url().'/wp-admin/edit.php?page=custom_post_order&term_id='.$tag_ID);
                echo "</p></div>";
		    }
        }		 
	}
}

/* получить количество постов термина */
function cp_get_post_count($tax_name, $term_id) {
    $args = array(
		'tax_query' => array(
		    array(
		        'taxonomy' => $tax_name,
		        'field' => 'id',
			    'terms' => array( $term_id )
		    )
	    ),
	    'posts_per_page' => -1,
    );
	$posts = get_posts( $args );
	$i=0;
	foreach($posts as $pst) {
		$i++;
	}
	wp_reset_query();
	return $i;
}
?>