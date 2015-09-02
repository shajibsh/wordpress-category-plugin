<?php
/* 	@package Display Data 
	@version 1.0.0
*/

/*
Plugin Name: Display Dynamic Data
Plugin URI: http://www.alltechcoder.com/zpt-display-data/
Description: Displays Project Summary from Project Category
Version: 1.0.0
Author: Alltech Coder [Farrukh Ahmad]
Author URI: http://www.alltechcoder.com
License: LGPL2
*/ 

class display_data_widget extends WP_Widget {

	function display_data_widget()	
	{	$widget_ops = array( 'classname' => 'display_data_widget', 'description' => 'Displays site data from database when set' ); 		
		$control_ops = array( 'id_base' => 'display_data_widget' ); 		
		$this->WP_Widget( 'display_data_widget', 'Display Data', $widget_ops, $control_ops ); 	
	} 
	
	function update($new_instance, $old_instance)	
	{		
		$instance = $old_instance;		
		$instance['title'] = $new_instance['title'];				
		$instance['category'] = $new_instance['category'];
		
		return $instance;	
	}
	
	function widget($args, $instance)	
	{		
		extract($args, EXTR_SKIP);		
		echo $before_widget;
               
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		
                if (!empty($title)) 			
		echo $before_title .$title. $after_title;
                
		$selected_parent=$instance['category'];
                $cats_args = array('child_of' => $selected_parent);
                $all_categories = get_categories( $cats_args );
                
                echo '<table width="100%" style="font-size:11px"> ';		
		
		echo '<tr><td>Upozila</td><td>Total</td><td>Done</td><td>On</td></tr>';
                
                foreach ($all_categories as $category) {
                    $project_summary = $this->loadDisplayProjectsData($category->term_id);
                    echo '<tr><td>'.$category->name.'</td><td>'.$project_summary['total'].'</td><td>'.$project_summary['completed'].'</td><td>'.($project_summary['total'] - $project_summary['completed']).'</td></tr>';
                }
                echo '</table>';
                
		echo $after_widget;	
                
	}
        
        function loadDisplayProjectsData($category_id)
        {
            global $wpdb;
            $totat_projects=0;
            $completed_projects=0;
            
            //$all_posts = query_posts( 'cat='.$category_id );
            $all_posts = $wpdb->get_results("
                                                    SELECT * 
                                                    FROM $wpdb->term_relationships
                                                    WHERE term_taxonomy_id = $category_id 
                                                    ");
            
            foreach ( $all_posts as $post )
            {
               $totat_projects++;
               
               $posts_metadata = $wpdb->get_results("
                                                    SELECT * 
                                                    FROM $wpdb->postmeta
                                                    WHERE post_id = $post->object_id 
                                                            AND meta_key = 'heading3'
                                                    ");

             if($posts_metadata[0]->meta_value=='বাস্তবায়িত')
                $completed_projects++;
            }
            
            return array(
                        'total' => $totat_projects,
                        'completed'=>$completed_projects,
                        );
        }
	
	   
	function form($instance)
	{		
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );		
		$title = $instance['title'];	
		$category = $instance['category'];
		// Get the existing categories and build a simple select dropdown for the user.
		$categories = get_categories(array( 'hide_empty' => 0));
 
		$cat_options = array();
		$cat_options[] = '<option value="BLANK">Select one...</option>';
		foreach ($categories as $cat) {
			$selected = $category === $cat->cat_ID ? ' selected="selected"' : '';
			$cat_options[] = '<option value="' . $cat->cat_ID .'"' . $selected . '>' . $cat->name . '</option>';
		}
?>
		<p>		
			<label for="<?php echo $this->get_field_id('title'); ?>">Title: 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
			name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>">
				<?php _e('Choose category:'); ?>
			</label>
			<select id="<?php echo $this->get_field_id('category'); ?>" class="widefat" name="<?php echo $this->get_field_name('category'); ?>">
				<?php echo implode('', $cat_options); ?>
			</select>
		</p>
		

		
<?php	
	}
}
add_action('widgets_init', create_function('', 'return register_widget("display_data_widget");'))
?>
