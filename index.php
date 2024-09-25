<?php
$method=$_SERVER['REQUEST_METHOD'];
if(is_array($_REQUEST) && count($_REQUEST)!=0)
{
	if($method!='')
	{
		require('DbConnClass.php');
		$db=new DbConnClass();
		if($method == 'POST')
		{
			if($_REQUEST['args']!='')
			{
				if($_REQUEST['args']=='authlogin')
				{
					$data = json_decode(file_get_contents('php://input'),true);
					$username=$db->trimData($data['username']);
					$upassword=$db->trimData($data['upassword']);
					if($username !='' && $upassword!='')
					{
						$args = ['args' => "$username"];
						$user = $db->getUserFromLogin('users_ang_18','uid,username','username="'.$username.'" and upassword="'.$upassword.'" and is_active=1',$args);
						if($user)
						{ echo json_encode(['result'=>true,'data'=>$user[0],'authTok'=>$user['authTok']]); }
						else
						{ echo json_encode(['result'=>false,'error'=>'Invalid Username or Password']); }					
					}
					else
					{
						echo json_encode(['result'=>false,'error'=>'Please fill required fields.(*)']);
					}
				}
				else if($_REQUEST['args']=='signUpUser')
				{
					$data = json_decode(file_get_contents('php://input'),true);
					$username=$db->trimData($data['username']);
					$uremail=$db->trimData($data['uremail']);
					$upassword=$db->trimData($data['upassword']);
					$urole=$db->trimData($data['urole']);
					$status = $db->trimData($data['is_active']);
					if($username!='' && $uremail!='' && $upassword!='' && $urole!='' && $status!='')
					{
						$v_email = $db->validateData($uremail,'E-mail');
						$v_user = $db->validateData($username,'string',3);
						$isUserExist=$db->checkData('users_ang_18','username','username="'.$username.'"');
						$isEmailExist=$db->checkData('users_ang_18','uremail','uremail="'.$uremail.'"');
						if(!$v_email){
							echo json_encode(['result'=>false,'error'=>'Invalid Email Address..']);
						}
						else if(!$v_user){
							echo json_encode(['result'=>false,'error'=>'Invalid UserName..']);
						}
						//check weather username or email exists
						else if($isUserExist==1)
						{
							echo json_encode(['result'=>false,'error'=>'Username already Exists']);
						}
						else if($isEmailExist==1)
						{
							echo json_encode(['result'=>false,'error'=>'E-mail already Exists']);
						}
						else
						{
							$adddata = [
								'ufullname' => "$username",
								'username' => "$username",
								'uremail'=> "$uremail",
								'upassword' => "$upassword",
								'urole' => "$urole",
								'udate_add'=> date('Y-m-d H:i:s'),
								'is_active' => "$status",
							];
							$cid = $db->saveData('users_ang_18',$adddata);
							if($cid){
								echo json_encode(['result'=>true,'message'=>'User Created Successfully']);
							}
							else
						   { echo json_encode(['result'=>false,'error'=>'User not Saved']);}  
						}
					}
					else
					{
						echo json_encode(['result'=>false,'error'=>'Please fill required fields.(*)']);
					}    

				}
				else if($_REQUEST['args']=='createUser')
				{
					$data = json_decode(file_get_contents('php://input'),true);
					$ufullname=$db->trimData($data['ufullname']);
					$username=$db->trimData($data['username']);
					$uremail=$db->trimData($data['uremail']);
					$upassword=$db->trimData($data['upassword']);
					$urole=$db->trimData($data['urole']);
					$status = $db->trimData($data['is_active']);
					if($username!='' && $uremail!='' && $upassword!='' && $urole!='' && $status!='' && $ufullname!='')
					{	
						//check weather username or email exists
						$isUserExist=$db->checkData('users_ang_18','username','username="'.$username.'"');
						$isEmailExist=$db->checkData('users_ang_18','uremail','uremail="'.$uremail.'"');
						//check if role and status is valid or not
						$s_arr = [1,0];
						$validarr = in_array($status,$s_arr);
						$r_arr = ['Admin','User'];
						$validrole = in_array($urole,$r_arr);
						if(!$validarr)
						{
							echo json_encode(['result'=>false,'error'=>'Please Choose Valid Status']);
						}
						else if(!$validrole)
						{
							echo json_encode(['result'=>false,'error'=>'Please Choose Valid Role']);
						}
						else if($isUserExist==1)
						{
							echo json_encode(['result'=>false,'error'=>'Username already Exists']);
						}
						else if($isEmailExist==1)
						{
							echo json_encode(['result'=>false,'error'=>'E-mail already Exists']);
						}
						else
						{
							$adddata = [
								'ufullname' => "$ufullname",
								'username' => "$username",
								'uremail'=> "$uremail",
								'upassword' => "$upassword",
								'urole' => "$urole",
								'udate_add'=> date('Y-m-d H:i:s'),
								'is_active' => "$status",
							];
							$cid = $db->saveData('users_ang_18',$adddata);
							if($cid){
								echo json_encode(['result'=>true,'message'=>'User Created Successfully']);
							}
							else
						   { echo json_encode(['result'=>false,'error'=>'User not Saved']);}   
						}
					}
					else
					{
						echo json_encode(['result'=>false,'error'=>'Please fill required fields.(*)']);
					}    
				}
				else if($_REQUEST['args']=='editUser')
				{
					$data = json_decode(file_get_contents('php://input'),true);
					$uid=$db->trimData($data['uid']);
					$ufullname=$db->trimData($data['ufullname']);
					$username=$db->trimData($data['username']);
					$uremail=$db->trimData($data['uremail']);
					$upassword=$db->trimData($data['upassword']);
					$urole=$db->trimData($data['urole']);
					$status=$db->trimData($data['is_active']);
					if($username!='' && $uremail!='' && $upassword!='' && $urole!='' && $ufullname!='')
					{
						//check weather username or email is changed
						$checkUser = $db->getAllData('users_ang_18','username,uremail,uimage','uid='.$uid);
						if(is_array($checkUser) && count($checkUser) > 0)
						{
			
							$old_user = $checkUser[0]['username'];
							if($old_user!=$username) //check new and old both are same username or not
							{
								$isUserExist=$db->checkData('users_ang_18','username','username="'.$username.'"');//check new username already exist
								if($isUserExist==1)
								{
									echo json_encode(['result'=>false,'error'=>'Username already Exists']);
									exit;
								}
								else
								{
									$newusername = $username;
								}
							}
							else
							{
									$newusername = $old_user;
								
								}
							$old_email = $checkUser[0]['uremail'];//check new and old both are same email or not
							if($old_email!=$uremail)
							{
								$isEmailExist=$db->checkData('users_ang_18','uremail','uremail="'.$uremail.'"');//check new email already exist
								if($isEmailExist==1)
								{
									echo json_encode(['result'=>false,'error'=>'E-mail already Exists']);
									exit;
								}
								else
								{
									$newemail = $uremail;
								}
							}
							else
							{
									$newemail = $old_email;
								
							}
							$editdata = [
								'ufullname' => "$ufullname",
								'username' => "$newusername",
								'uremail'=> "$newemail",
								'upassword' => "$upassword",
								'urole' => "$urole",
								'is_active' => "$status",
								'udate_date'=> date('Y-m-d H:i:s')
							];
							$cid = $db->saveData('users_ang_18',$editdata,'uid='.$uid);
							if($cid){
								echo json_encode(['result'=>true,'message'=>'User Updated Successfully']);
							}
							else
						   { echo json_encode(['result'=>false,'error'=>'User not Update']);} 
						}
						else
						{
							echo json_encode(['result'=>false,'message'=>'User not Found']);
						}
							   
 				
					}
					else
					{
						echo json_encode(['result'=>false,'error'=>'Please fill required fields.(*)']);
					}    
				}
				else if($_REQUEST['args']=='verifytok')
				{
					$token = $_POST['token'];
					$res = $db->verifyTok($token);
					if($res=='Invalid')
					{
						echo json_encode(['result'=>false,'error'=>'Invalid Token']);
					}
					elseif($res=='Expire')
					{
						echo json_encode(['result'=>false,'error'=>'Login Expire']);
					}
					else
					{
						echo json_encode(['result'=>true,'resdata'=>$res]);
					}
				}
			    else if($_REQUEST['args']=='uploadfile')
				{
					$uid = $_POST['uid'];
					$oldimage = $_POST['old_img'];
					$file_size=($_FILES['file']['size']/1024);
					$file_type=$_FILES['file']['type'];
					$path_parts = pathinfo($_FILES["file"]["name"]);
					$extenstion = $path_parts['extension'];
					$filename = $path_parts['basename'];
					if($file_size > 2000)
					{
						echo json_encode(['result'=>false,'error'=>'Maximum File size is 2MB your size is ..']);
					}
					else if(strlen($filename) > 25){
						echo json_encode(['result'=>false,'error'=>'Invalid filename! Maximum 25 characters allowed.']);
					}
					else if(!preg_match('/^[\w|\-\.]+$/', $filename)){
						echo json_encode(['result'=>false,'error'=>'Invalid filename!  a-zA=Z-_ allowed only.']);
					}
					else if($extenstion=='jpeg' ||$extenstion=='jpg' || $extenstion=='gif' || $extenstion=='png')
					{
						$dir = $_SERVER['DOCUMENT_ROOT'].'/usermanagement/public/profile/';
						$file = $_FILES['file']['name'];						
						$newFileName= $uid."_".$filename;
						$file_tmp = $_FILES['file']['tmp_name'];
						$uploadfile = move_uploaded_file($file_tmp,$dir.$newFileName);
						$db->resizeImage($newFileName,$extenstion);
						if($uploadfile)
						{
							//delete old image
							if($oldimage!='') {
								if(file_exists($dir . $oldimage)) //check file exist or not
								{ 
									unlink($dir.$oldimage);
								}
							} 
							$resize= $db->resizeImage($newFileName,$extenstion);
							if($resize)
							{
								$editdata = [
									'uimage'=>"$newFileName",
								];
								$cid = $db->saveData('users_ang_18',$editdata,'uid='.$uid);
							    if($cid){
									echo json_encode(['result'=>true,'message'=>'File Uploaded Successfully','file'=>$newFileName]);
								}
								else
							    {
								 echo json_encode(['result'=>false,'error'=>'File not Found']);
							    } 
							}
							else
							{
								echo json_encode(['result'=>false,'error'=>'File not found']);
							}
						}
						else
						{
							echo json_encode(['result'=>false,'error'=>'File not found']);
						}

					}
					else
					{
						echo json_encode(['result'=>false,'error'=>'Invalid File Type, Only JPG,GIF,PNG Files allowed']);
					}
				}
				else if($_REQUEST['args']=='getuser')
				{
					$uid = $_POST['uid'];
					$user = $db->getAllData('users_ang_18','*','uid='.$uid);
					if($user)
					{ echo json_encode(['result'=>true,'data'=>$user]); }
					else
					{ echo json_encode(['result'=>false,'error'=>'Invalid Request']); }
				}
				else if($_REQUEST['args']=='delUser'){
					$uid = $_POST['uid'];
					$delData = $db->deleteData('users_ang_18','uid='.$uid);
					if($delData){
						echo json_encode(['result'=>true,'message'=>'User Delete Successfully']);
					}
					else
					{
						echo json_encode(['result'=>false,'error'=>'Error.. Delete user Fails!']);
					}
				}
			}		
		}
		else if ($method == 'GET')
		{
				if($_REQUEST['args']!='')
				{
					if($_REQUEST['args']=='alluser')
					{
						if(isset($_REQUEST['page'])){
							$page=$_REQUEST['page'];
						}
						else
						{ $page = 1; }
						$limit=5;
						$offset = ($page -1) * $limit;
						$row = $db->checkData('users_ang_18','*');
						$totalpage=ceil($row/$limit);
						$sendlimit = $offset.', '.$limit;
						$user = $db->getAllData('users_ang_18','*','','','','',$sendlimit);
						if($user)
						{ 
							echo json_encode(['result'=>true,'data'=>$user,'totalRec'=>$row,'page'=>$page,'totalPage'=>$totalpage,'noOfRecPerPage'=>$limit]); 
						}
						else
						{ echo json_encode(['result'=>false,'error'=>'Invalid Request']); } 
					}

				}
		}
		else
		{
			echo json_encode(['result'=>false,'error'=>'Invalid Method']);
		}
	}
	else
	{
		echo json_encode(['result'=>false,'error'=>'Invalid Method Pass']);
	}
}
else
{
    echo json_encode(['result'=>false,'error'=>'Invalid Request']);
}
?>