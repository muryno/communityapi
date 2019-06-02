<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

$container = $app->getContainer();
$container['upload_directory'] = '../public/uploads/';
function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    try {
        $basename = bin2hex(random_bytes(8));
    } catch (Exception $e) {
    } // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return  $filename;
}


//add customer
$app->post("/api/user/register",function ($request, $responses){

    $fname=$request->getParam('fname');
    $lname=$request->getParam('lname');
    $phon=$request->getParam('phone');
    $email=$request->getParam('email');
    $prswrd=$request->getParam('password');
    $password=password_hash($prswrd,PASSWORD_DEFAULT);


    if(empty($fname)){
        $response['error']       = true;
        $response['message']     = 'missing first name!!';
        return $responses->withJson($response);
    }
    if(empty($lname)){
        $response['error']       = true;
        $response['message']     = 'missing last name!!';
        return $responses->withJson($response);

    }
    if(empty($phon)){
        $response['error']       = true;
        $response['message']     = 'missing Phone number!!';
        return $responses->withJson($response);
    }
    if(empty($email)){
        $response['error']       = true;
        $response['message']     = 'missing email address!!';
        return $responses->withJson($response);
    }
    if(empty($password)){
        $response['error']       = true;
        $response['message']     = 'missing password!!';
        return $responses->withJson($response);
    }

    $directory=$this->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();

    if (empty($uploadedFiles)) {
        $response['error']       = true;
        $response['message']     = 'missing profile picture!!';
        return $responses->withJson($response);
    }


    $uploadedFile = $uploadedFiles['image'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK)
        $filename = moveUploadedFile($directory, $uploadedFile);



    $getData="INSERT INTO  users (fname,lname,phone,email,password,image) VALUE (:fname,:lname,:phone,:email,:password,:image)";
    try{
        $con=new db();
        $db=$con->connection();

        $row= $db->prepare($getData);

        $row->bindParam(':fname',$fname);
        $row->bindParam(':lname',$lname);
        $row->bindParam(':phone',$phon);
        $row->bindParam(':email',$email);
        $row->bindParam(':password',$password);
        $row->bindParam(':image',$filename);
//        $row->bindParam(':address',$address);
        $row->execute();

        if ($row->execute()) {
            $uid=                   $db->lastInsertId();
            $response['error']       = false;
            $response['message']     = 'Profile updated successfully!';
            $response['data']         = getUser($uid);
        } else {
            $response['error']       = true;
            $response['message']     = 'Error updating profile!';
        }


        $con=null;
        return $responses->withJson($response);


    }catch (PDOException $ex){

        echo '{"error": {"text" : '.$ex->getMessage().'} ';
    }
});
function getUser($uid){
    $getData="SELECT * FROM  users WHERE id=$uid";
    try {
        $con = new db();
        $db = $con->connection();

        $row = $db->query($getData);
        $customer = $row->fetch(PDO::FETCH_OBJ);
        $customer->image = 'http://community/uploads/' .$customer->image;
        $con = null;
        return $customer;

    }catch (PDOException $ex){

        echo '{"error": {"text" : '.$ex->getMessage().'} ';
    }
};

$app->post("/api/user/login",function ($request, $responses){

    $email=$request->getParam('email');
    $prswrd=$request->getParam('password');
    $password=password_hash($prswrd,PASSWORD_DEFAULT);

    if(empty($email)){
        $response['error']       = true;
        $response['message']     = 'missing email address!!';
        return $responses->withJson($response);
    }
    if(empty($password)){
        $response['error']       = true;
        $response['message']     = 'missing password!!';
        return $responses->withJson($response);
    }


    try {
        $con = new db();
        $db = $con->connection();

        $getData = "SELECT * FROM users WHERE email=:email";
        $row = $db->prepare($getData);
        $row->bindValue(":email", $email);

        if ($row->execute()) {

            while ($getme = $row->fetch(PDO::FETCH_OBJ)) {

              $uid                = $getme->id;
                $response['error'] = false;
                $response['message'] = ' successfully!';
                $response['data'] = getUser($uid);

        }
    }
        else {
            $response['error'] = true;
            $response['message'] = 'Error getting data!';
        }


        $con=null;
        return $responses->withJson($response);


    }catch (PDOException $ex){

        echo '{"error": {"text" : '.$ex->getMessage().'} ';
    }


    /**$search = "%$search%";
    $stmt  = $pdo->prepare("SELECT * FROM table WHERE name LIKE ?");
    $stmt->execute([$search]);
    $data = $stmt->fetchAll();**/

    //https://phpdelusions.net/pdo#array
});




