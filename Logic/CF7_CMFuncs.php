<?php
  class CF7_CMFuncs
{
	public function checkPosition($pattern, $tag)
	{
		$pos =  strrpos($pattern,$tag);
		if($pos ===false)
		{
			return false;
		}
		return true;
		
	}
	
	public function GetDefaultFromValue($value,$definition_id,$url=null)
	{
		if($this->checkPosition($value,"[@user@]"))
		{
			$parameter = str_replace("[@user@]","",$value);
			$userData = $this->getUserParam($parameter);
			if($userData!=null)
			{
				$value = $userData;
			}
			else
			{
				$value="";
			}
		}
		if($this->checkPosition($value,"[@bloginfo@]"))
		{
			$parameter =  str_replace("[@bloginfo@]","",$value);  
			$userData = $this->getBlogParam($parameter);
			if($userData!=null)
			{
				$value = $userData;
			}
		}
		
		
		if($this->checkPosition($value,"[@queryParam@]"))
		{
			
			$parameter="field_id_".$definition_id;
			$userData = $this->getQueryParam($parameter,$url);
			if($userData!=null)
			{
				$value = $userData;
			}
			
			
		}

		
		if($this->checkPosition($value,"[@ip@]"))
		{
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];

			}
			$value = $ip;
		}
		
		if($this->checkPosition($value,"[@date@]"))
		{
			$value = date('Y-m-d', time());   
		}
		
		
		return $value;
	}
	
	public function getQueryParam($parameter,$url)
	{
		$parts = parse_url($url);
		parse_str($parts['query'], $query);
		return $query[$parameter];
	}
	
	public function getUserParam($param)
	{
		$user = wp_get_current_user();
		if($user!=null)
		{
			switch($param)
			{
				case "ID": return $user->ID;
					break;
				case "user_login": return $user->user_login ;
					break;
				case "user_email": return $user->user_email  ;
					break;
				case "user_firstname": return $user->user_firstname   ;
					break;
				case "user_lastname": return $user->user_lastname;
					break;
				case "display_name": return $user->display_name;
					break;
				default:
					break;
			}
		}
		return "";
	}
	
	
	public function getBlogParam($param)
	{
		$value = get_bloginfo($param);   
		if(isset($value))
		{
			
			return $value;
		}
		return "";
	}
	
	
	
	public function getResults($query)
	{
		global $wpdb;
		$myrows = $wpdb->get_results( $query,ARRAY_A );
		return $myrows;
	}
	
	public function execute($query)
	{
		global $wpdb;
		global $wp_error;
		if($wpdb->query($query)===false)
		{
			if ( $wp_error ) {
				return false;
			}        
		}
		return true;
	}
	
	public function getLastAddedId()
	{
		global $wpdb;
		return $wpdb->insert_id;
	}
	
	
	public function getLastError()
	{
		global $wpdb;
		return $wpdb->last_error;
	}
	
	
	public function GetTableName($tableName)
	{
		global $wpdb; 
		
		
		if (function_exists('is_multisite') && is_multisite()) {  
			return $wpdb->prefix.$tableName;
		}
		else
		{
			return $wpdb->prefix.$tableName; 
		}

	}
	
	 public function Manage($Request)
	{
		if(isset($Request['formaction']))
		{
			$action=$Request['formaction'];
			switch($action)
			{
				case 'getValuesFromTable':
				break;
				}
			
		}
			
	}
	
	
	public function GetTableNames()
	{
		$prefix=$this->getprefix();
		$query = "select * from information_schema.tables where TABLE_NAME like '".$prefix."%'";
		return $this->getResults($query);
	}
	
	public function GetColumns()
	{
		$prefix=$this->getprefix();
		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS where TABLE_NAME like '".$prefix."%'";
		return $this->getResults($query);
	}
	
	public function getprefix()
	{
		global $wpdb; 
		if (function_exists('is_multisite') && is_multisite()) {  
			return $wpdb->prefix;
		}
		else
		{
			return $wpdb->prefix; 
		} 
	}
	

	
}
?>