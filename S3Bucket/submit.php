
<?php 
require 'vendor/autoload.php'; 
  
use Aws\S3\S3Client; 
use Aws\Exception\AwsException; 
  
// Instantiate an Amazon S3 client. 
$s3Client = new S3Client([ 
    'version' => 'latest', 
    'region'  => 'ap-south-1', 
    'credentials' => [ 
        'key'    => 'dcbfhksdljhghsdfvhAKIfdsAfdfTOEXfdGfRFAXGZUdfQTGU', // Add your access key here 
        'secret' => 'dsffkxMOxjF22+dfsdWbwRk8HdBdfsdfji4MDzPIwnyzkfdfXD2NiZdf9F' // Add your secret key here 
    ] 
]); 
  
// Check if the form was submitted 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    // Check if file was uploaded without errors 
    if (isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0) { 
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"); 
        $filename = $_FILES["anyfile"]["name"]; 
        $filetype = $_FILES["anyfile"]["type"]; 
        $filesize = $_FILES["anyfile"]["size"]; 
  
        // Validate file extension 
        $ext = pathinfo($filename, PATHINFO_EXTENSION); 
        if (!array_key_exists($ext, $allowed)) { 
            die("Error: Please select a valid file format."); 
        } 
  
        // Validate file size - 10MB maximum 
        $maxsize = 10 * 1024 * 1024; 
        if ($filesize > $maxsize) { 
            die("Error: File size is larger than the allowed limit."); 
        } 
  
        // Validate type of the file 
        if (!in_array($filetype, $allowed)) { 
            die("Error: There was a problem with the file type."); 
        } 
  
        // Check whether file exists before uploading it 
        if (file_exists("uploads/" . $filename)) { 
            die($filename . " already exists."); 
        } 
  
        // Move uploaded file to uploads directory 
        if (!move_uploaded_file($_FILES["anyfile"]["tmp_name"], "uploads/" . $filename)) { 
            die("Error: File was not uploaded."); 
        } 
  
        // Upload file to S3 
        $bucket = 'adicloud'; // Add your bucket name here 
        $file_Path = __DIR__ . '/uploads/' . $filename; 
        $key = basename($file_Path); 
  
        try { 
            $result = $s3Client->putObject([ 
                'Bucket' => $bucket, 
                'Key'    => $key, 
                'Body'   => fopen($file_Path, 'r'), 
                'ACL'    => 'public-read', // make file 'public' 
            ]); 
            $urls3 = $result->get('ObjectURL'); 
            $cfurl = str_replace("https://adicloud.s3.ap-south-1.amazonaws.com", "https://d1g04a21wg9rz.cloudfront.net", $urls3); 
  
            // Database connection 
            $servername = "database-1.cx0iyacc8tzg.ap-south-1.rds.amazonaws.com"; 
            $username = "root"; 
            $password = "Pass1234"; 
            $dbname = "facebook"; 
  
            // Create connection 
            $conn = new mysqli($servername, $username, $password, $dbname); 
            if ($conn->connect_error) { 
                die("Connection failed: " . $conn->connect_error); 
            } 
  
            // Insert record into database 
            $name = $_POST["name"]; 
            $sql = "INSERT INTO posts (name, s3url, cfurl) VALUES ('$name', '$urls3', '$cfurl')"; 
            if ($conn->query($sql) === TRUE) { 
                echo "Image uploaded successfully. Image path is: $urls3 <br>"; 
                echo "CloudFront URL: $cfurl <br>"; 
                echo "Connected successfully to the database. <br>"; 
                echo "New record created successfully. <br>"; 
            } else { 
                echo "Error: " . $sql . "<br>" . $conn->error . "<br>"; 
            } 
  
            // Close database connection 
            $conn->close(); 
        } catch (AwsException $e) { 
            echo "Error: There was an error uploading the file to S3. <br>"; 
            echo $e->getMessage() . "<br>"; 
        } 
    } else { 
        echo "Error: There was a problem uploading your file. Please try again. <br>"; 
    } 
} 
?>
<style>
/* Global Reset and Font */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(to right, #4facfe, #00f2fe);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Glass Card Form */
form {
    background: rgba(255, 255, 255, 0.1);
    padding: 40px 30px;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 420px;
    color: #fff;
}

/* Heading */
form h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #ffffff;
}

/* Labels */
label {
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
    font-weight: 500;
}

/* Inputs */
input[type="text"],
input[type="file"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    margin-bottom: 15px;
    font-size: 14px;
}

input[type="file"]::file-selector-button {
    background-color: #ffffff;
    color: #333;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
}

/* Submit Button */
input[type="submit"] {
    background-color: #ffffff;
    color: #007bff;
    font-weight: bold;
    font-size: 16px;
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s ease;
}

input[type="submit"]:hover {
    background-color: #e0e0e0;
}

/* Message Box (Optional) */
.message {
    margin-top: 25px;
    padding: 20px;
    border-radius: 10px;
    background: #ffffffcc;
    color: #333;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    word-wrap: break-word;
}
</style>