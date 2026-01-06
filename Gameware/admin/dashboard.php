<?php

session_start();
if(isset($_SESSION['user_id']))  {
    if($_SESSION['user_role'] == "admin"){

         

    }
    else{
      header("Location: ../dashboard.php");

    }


}else{
     header("Location: ../index.php");
  
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
   
 
        *{  

        margin: 0;
        padding : 0 ;

        }




        .dashboard_sidebar{  
        
        position: fixed;
        top: 0;
        background-color: darkcyan;
        width: 200px;
        height: 100%;
        

        }

        .dashboard_sidebar ul li{
        list-style:none;
        text-align: center;
        padding: 10px;
        }



        .dashboard_sidebar ul li a{

        display: block;
        text-decoration: none;
        color: white;

        }

        .dashboard_sidebar ul li a:hover {

        background-color: black;

        }

        .dashboard_main{
        
            padding: 30px;
            margin-left: 200px;
            
        }

    </style>





</head>
<body> 
       
      




       <div class="dashboard_sidebar">

        <ul>
             
        <li><a href="addproduct.php">Add Product</a></li>
        <li><a href="displayproduct.php">View Products</a></li>
         <li><a href="vieworders.php">View Orders</a></li>
        <li><a href="../logout.php">logout</a></li>

 
        </ul>

        </div>
  
        <div>
        
        <div class="dashboard_main">    
             
        <p>  Welcome to Admin dashboard
        </p>
    
    
    
        </div>


        </div>          



</body>
</html>