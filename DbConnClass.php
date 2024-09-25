<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT,GET,POST,DELETE");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Authorization, Origin, X-Requested-With, Content-Type, Accept");
require('constant.php');
class DbConnClass{
	private $db=DB_NAME,$host=DB_HOST,$user=DB_USER,$pass=DB_PASS;
	protected $conn;
	public function __construct()
	{
		$this->conn = new mysqli($this->host,$this->user,$this->pass,$this->db);
		if(!$this->conn)
		{
			die('Connection Failed: '.mysqli_connect_errno());
		}
	}
	public function trimData($val){
		$val1 = trim($val);
		$val2 = stripslashes($val1);
		$val3 =htmlspecialchars($val2);
		return $val3;
	}
	public function validateData($data,$type='string',$minlength=null,$maxlength=null){
		$strlen = strlen($data);
		if($minlength!=null && $strlen > $minlength){
			return false;
		}
		else if($maxlength!=null && $strlen < $maxlength){
			return false;
		}
		else if($type=='E-mail')
		{
			if(!filter_var((string)$data,FILTER_VALIDATE_EMAIL)){
				return false;
			}
			else
			return true;
			
		}
		else if($type=='url')
		{
			if(!filter_var((string)$data,FILTER_VALIDATE_URL)){
				return false;
			}
			else
			return true;
		} 
		else if($type=='Numeric')
		{
			if(!filter_var((integer)$data,FILTER_VALIDATE_INT)){
				return false;
			}
			else
			return true;
		}
		else if($type=='alphanum')
		{
			if(!preg_match("/^[a-zA-Z0-9-' ]*$/",$data)){
				return false;
			}
			else
			return true;
		}
		else if($type=='string')
		{
			if(!preg_match("/^[a-zA-Z-' ]*$/",$data)){
				return false;
			}
			else
			return true;
		}
		else
		{
			return true;
		}

	}
	public function getAllData($table,$fields="*",$where='',$groupBy='',$having='', $orderBy='',$limit='')
	{
		
		$sql='select '.$fields.' from '.$table;
		if($where!='')
		{
			$sql.=' where '.$where;
		}
		if($groupBy!='')
		{
			$sql.=' group by '.$groupBy;
		}
		if($having!='')
		{
			$sql.=' HAVING '.$having;
		}
		if($orderBy!='')
		{
			$sql.=' order by '.$orderBy;
		}
		if($limit!='')
		{
			$sql.=' limit '.$limit;
		}
		$data = $this->getResult($sql);
		return $data;
	}
	public function checkData($table,$field,$where='')
	{
		$sql='select '.$field.' from '.$table;
		if($where!=''){
			$sql.=' where '.$where;
		}	
		$row = $this->getRow($sql);
		return $row;
	}
	private function getRow($sql)
	{
		$qry = $this->conn->query($sql);
		return $tot_row=$qry->num_rows;
	}
	public function getUserFromLogin($table,$field,$where,$args)
	{
		$sql='select '.$field.' from '.$table.' where '.$where;	
		$id = $this->getResultWithAuth($sql,$args);
		return $id;
	}
	public function saveData($table,$data,$id='')
	{
		if(is_array($data))
		{
			$str='';
			foreach($data as $k=>$v)
			{
				$str.= $k.'="'.$v.'",';
			}
			$str_qry = rtrim($str,',');
			if($id!='')
			{
				$sql = "update $table set ".$str_qry." where $id";	
			}
			else
			{
				$sql = "insert into $table set ".$str_qry;	
			}
			return $this->saveResult($sql);
		}
	}
	private function saveResult($sql)
	{
		$qry = $this->conn->query($sql);
		if($qry)
			return true;
		else
		return false;
	}
	private function getResult($sql)
	{
		$qry = $this->conn->query($sql);
		$res = [];
		$tot_row=$qry->num_rows;
		if($tot_row > 0)
		{
			while($row= $qry->fetch_assoc())
			{
				$res[]=$row;
			}
		}
		return $res;
	}
	private function getResultWithAuth($sql,$args)
	{
		$qry = $this->conn->query($sql);
		$res = [];
		$tot_row=$qry->num_rows;
		if($tot_row > 0)
		{
			while($row= $qry->fetch_assoc())
			{
				$res[]=$row;
			}
			$res['authTok'] = $this->createKey($res,'pqr789',3600);
		}
		return $res;
	}
	function deleteData($table,$where){
		$sql = 'DELETE FROM '.$table.' where '.$where;
		$qry = $this->conn->query($sql);
		if($qry){
			return true;
		}
		else
		{
			return false;
		}
	}
	function createKey($args,$key='pqr789',$expire=null){
		// Create token header as a JSON string
		$header = ['typ' => 'JWT', 'alg' => 'HS256'];
		if($expire!=null)
		{
			$header['expire'] = time() + $expire;
		}
		$header = json_encode($header);
		// Create token payload as a JSON string
		$payload = json_encode($args);

		// Encode Header to Base64Url String
		$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

		// Encode Payload to Base64Url String
		$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

		// Create Signature Hash
		$signature = hash_hmac('SHA256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);

		// Encode Signature to Base64Url String
		$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

		// Create JWT
		$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
		return $jwt;
	}
	function verifyTok($token,$key='pqr789')
	{
		$token_parts = explode('.',$token);
		$signature = hash_hmac('SHA256',$token_parts[0] . "." . $token_parts[1],$key,true);
		$base64UrlSignature =str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));;
		if($base64UrlSignature != $token_parts[2])
		{
			return 'Invalid';
		}
		$header= json_decode(base64_decode($token_parts[0]),true);
		if(isset($header['expire']))
		{
			if($header['expire'] < time())
			{
				return 'Expire';
			}
		}
		$payload = json_decode(base64_decode($token_parts[1]),true);
		return $payload; 
	}
	function resizeImage($file,$ext){
		$dir = $_SERVER['DOCUMENT_ROOT'].'/usermanagement/public/profile/';
		$filepath = $dir.$file;
		list($width, $height) = getimagesize($filepath);
		// Define new dimensions (500x500 pixels)
		$newWidth = 500;
		$newHeight = 500;
		if($ext=='jpeg' || $ext=='jpg')
		{
			$source = imagecreatefromjpeg($filepath);
		}
		else if($ext=='gif')
		{
			$source = imagecreatefromgif($filepath);
		}
		else if($ext=='png')
		{
			$source = imagecreatefrompng($filepath);
		}
		else
		{	
			$source='';
		}
		if($source!='')
		{
			// Create a new image
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			// Resize
			imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			
			// Save the resized image
			if($ext=='jpeg' ||$ext=='jpg')
			{imagejpeg($thumb, $filepath, 100);}
			if($ext=='gif')
			{imagegif($thumb, $filepath, 100);}
			if($ext=='jpng')
			{imagepng($thumb, $filepath, 100);}
			return true;
		}
		else
		{
			return false;
		}
		return false;
	}
}
?>