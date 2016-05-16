<?php
class GenesisUserTable extends WP_List_Table {
	// Two weeks in seconds
    const TWO_WEEKS = 1209600;
    const ONE_WEEK = 604800;
    
    function __construct(){
          global $status, $page;
                
          //Set parent defaults
          parent::__construct( array(
              'singular'  => 'user',     //singular name of the listed records
              'plural'    => 'users',    //plural name of the listed records
              'ajax'      => false        //does this table support ajax?
          ) );
        
      }
      
      public function wrapRed($data){
          return "<span style='color:red'>" . $data . "</span>";
      }
	  
	  public function column_default($item, $column_name){
         $noValue = '- -';
          
         switch($column_name){
			 case 'gained_more_than_one_kg' :
				 if($item['six_month_benchmark_change_email_check'] >= 1){
					 return '<span class="flag">F</span>';
				 }
				 return "";
             case 'user_email':
                 $email = "<a href='mailto:" . $item[$column_name] . "'>" . $item[$column_name] . "</a>";

                 $email .= $this->row_actions(array(
                     'View'=> '<a href="' . GenesisTracker::getAdminUrl(array("edit_user" => $item['user_id'])) . '">View</a>',
                     'Edit' => '<a href="' . get_edit_user_link($item['user_id']) . '">Edit</a>'
                         
                 ), false);
                 
                 return $email;
             case 'start_weight':
             case 'weight':
                 if(!(float)$item[$column_name]){
                     return $noValue;
                 }
                 return round((float)$item[$column_name], 2);
             case 'weight_change':
                 if(($loss = round((float)$item[$column_name], 2)) >= 0){
                     return $this->wrapRed($loss);
                 }
                 return $loss;
             case 'user_registered_timestamp' :
                 return gmdate('d M Y', strtotime($item['user_registered']));
             case 'unix_timestamp' : 
				 if(!$item['measure_date']){
					 return "- -";
				 }
				 
                 $time = strtotime($item['measure_date']);
                 $date = gmdate('d M Y', strtotime($item['measure_date']));
                 if(time() - $item[$column_name] > self::ONE_WEEK){
                     return $this->wrapRed($date);
                 }
                 return $date;
             case 'user_contacted' :
             case 'account_active' :
                 return (int)$item[$column_name] ? 'Yes' : $this->wrapRed('No');
             case 'withdrawn' : 
                 return $item[$column_name] ? $this->wrapRed('Yes') : 'No';
			 case 'four_weekly_date' :
				 if($item['four_week_required_to_send'] && !$item['four_weekly_date']){
					 return $this->wrapRed('Never');
				 }
				 
				 if(!$item['four_weekly_date']){
					 return '- -';
				 }

				 $date = gmdate('d M Y', strtotime($item['four_weekly_date']));
				 
				 return $item['four_week_required_to_send'] ? $this->wrapRed($date) : $date;
             default:
	         	if(!isset($item[$column_name])){
	            	 return $noValue;
	         	}
                 return $item[$column_name] ? $item[$column_name] : $noValue;
         }
     }
		 
	     function column_title($item){
        
	         //Build row actions
	         $actions = array(
	             'edit'      => sprintf('<a href="?page=%s&action=%s&user=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
	             'delete'    => sprintf('<a href="?page=%s&action=%s&user=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
	         );
        
	         //Return the title contents
	         return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
	             /*$1%s*/ $item['title'],
	             /*$2%s*/ $item['ID'],
	             /*$3%s*/ $this->row_actions($actions)
	         );
	     }
		 
		 function column_cb($item){
		        return sprintf(
		            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
		            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
		            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
		        );
		    }
			
		    function get_columns(){
		        $columns = array(
					'gained_more_than_one_kg' => '',
		            'user_email'            => 'Email Address',
                    'user_name'             => 'Name',
                    'user_registered_timestamp'       => 'Register Date',
                    GenesisTracker::passcodeGroupCol        => 'Passcode Group',
		            'unix_timestamp'        => 'Last Measurement Date',
					'four_weekly_date' => 'Last Four Week Email',
                    'user_contacted'        => 'Contacted',
                    'withdrawn'             => 'Withdrawn',
					 'account_active'       => 'Active',
                     'start_weight'       => 'Start Weight (Kg)',
                     'weight'               => 'Current Weight (Kg)',
                     'weight_change'        => 'Weight Change (Kg)',
		        );
		        return $columns;
		    }
    
		    function get_sortable_columns() {
		           $sortable_columns = array(
                       'measure_date' => array('measure_date', false),
					   'gained_more_than_one_kg' => array('six_month_benchmark_change_email_check', false),
                       'user_name'          => array('user_name', false),
		               'user_email'         => array('user_email',false),
                       'user_registered_timestamp' => array('user_registered_timestamp', false),
                       GenesisTracker::passcodeGroupCol     => array(GenesisTracker::passcodeGroupCol, false),
                            //true means it's already sorted
		               'start_weight'     => array('start_weight',false),
                       'weight'             => array('weight', false),
                       'user_contacted'     => array('user_contacted', false),
                       'withdrawn'          => array('withdrawn', false),
                       'account_active'     => array('account_active', false),
                       'weight_change'      => array('weight_change', false),
                       'unix_timestamp'     => array('unix_timestamp', true),
					   'four_weekly_date' => array('four_week_required_to_send'),
					   'four_week_required_to_send' => array('four_week_required_to_send', true),
                       'six_month_benchmark_change_email_check' => array('six_month_benchmark_change_email_check', true)
		           );
		           return $sortable_columns;
		       }
			   
			   
		       function prepare_items() {
		           global $wpdb; //This is used only if making any database queries

		           /**
		            * First, lets decide how many records per page to show
		            */
		           $per_page = 200;
        
        
		           /**
		            * REQUIRED. Now we need to define our column headers. This includes a complete
		            * array of columns to be displayed (slugs & titles), a list of columns
		            * to keep hidden, and a list of columns that are sortable. Each of these
		            * can be defined in another method (as we've done here) before being
		            * used to build the value for our _column_headers property.
		            */
		           $columns = $this->get_columns();
		           $hidden = array();
		           $sortable = $this->get_sortable_columns();
        
        
		           /**
		            * REQUIRED. Finally, we build an array to be used by the class for column 
		            * headers. The $this->_column_headers property takes an array which contains
		            * 3 other arrays. One for all columns, one for hidden columns, and one
		            * for sortable columns.
		            */
		           $this->_column_headers = array($columns, $hidden, $sortable);
        
        
		           /**
		            * Optional. You can handle your bulk actions however you see fit. In this
		            * case, we'll handle them within our package just to keep things clean.
		            */
		           //$this->process_bulk_action();
        
        
		           /**
		            * Instead of querying a database, we're going to fetch the example data
		            * property we created for use in this plugin. This makes this example 
		            * package slightly different than one you might build on your own. In 
		            * this example, we'll be using array manipulation to sort and paginate 
		            * our data. In a real-world implementation, you will probably want to 
		            * use sort and pagination data to build a custom query instead, as you'll
		            * be able to use your precisely-queried data immediately.
		            */
                     
                   $cols = $this->get_sortable_columns();
                   $orderBy = isset($cols[$_REQUEST['orderby']]) ? $_REQUEST['orderby'] : 'measure_date';
                   $order = strtoupper($_REQUEST['order']) == 'ASC' ? 'ASC' : 'DESC';
                   
                   if($orderBy == 'four_week_required_to_send'){
                       $orderBy .= ' DESC, four_weekly_date';
                   }
                   
                   // There's no point in sorting the other way
                   if($orderBy == 'six_month_benchmark_change_email_check'){
                       $order = 'DESC';
                   }
                   
		           $data = GenesisAdmin::getUserLogDetails($orderBy . " " . $order, null, true);
                   
		           /**
		            * This checks for sorting input and sorts the data in our array accordingly.
		            * 
		            * In a real-world situation involving a database, you would probably want 
		            * to handle sorting by passing the 'orderby' and 'order' values directly 
		            * to a custom query. The returned data will be pre-sorted, and this array
		            * sorting technique would be unnecessary.
		            */
		     
        
        
		           /***********************************************************************
		            * ---------------------------------------------------------------------
		            * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		            * 
		            * In a real-world situation, this is where you would place your query.
		            * 
		            * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		            * ---------------------------------------------------------------------
		            **********************************************************************/
        

		           /**
		            * REQUIRED for pagination. Let's figure out what page the user is currently 
		            * looking at. We'll need this later, so you should always include it in 
		            * your own package classes.
		            */
		           $current_page = $this->get_pagenum();
        
		           /**
		            * REQUIRED for pagination. Let's check how many items are in our data array. 
		            * In real-world use, this would be the total number of items in your database, 
		            * without filtering. We'll need this later, so you should always include it 
		            * in your own package classes.
		            */
		           $total_items = count($data);
        
        
		           /**
		            * The WP_List_Table class does not handle pagination for us, so we need
		            * to ensure that the data is trimmed to only the current page. We can use
		            * array_slice() to 
		            */
		           $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
		           /**
		            * REQUIRED. Now we can add our *sorted* data to the items property, where 
		            * it can be used by the rest of the class.
		            */
		           $this->items = $data;
        
        
		           /**
		            * REQUIRED. We also have to register our pagination options & calculations.
		            */
		           $this->set_pagination_args( array(
		               'total_items' => $total_items,                  //WE have to calculate the total number of items
		               'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
		               'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		           ) );
		       }
}