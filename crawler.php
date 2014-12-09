<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php

        /* Set up */ 
        define("HOSTNAME", "localhost"); 
        define("USERNAME", "root");
        define("PASSWARD", "root");
        define("DATABASE", "crowdfunding");
        define("TABLE", "tb_company"); // two tables
        define("LINK","http://www.fundable.com/browse/trending");
        
        /* Library */
        include('simple_html_dom.php');
        include('CompanyClass.php');
        include('function.php');
        
        /* URL bases */
        $base = LINK;

        /* Set CURL options*/
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $base);
        curl_setopt($curl, CURLOPT_REFERER, $base);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $str = curl_exec($curl);
        curl_close($curl);

        /* Creat DOM object */
        $html_base = new simple_html_dom();
        
        /* Load HTML from a string */
        $html_base->load($str);
        // echo $str;
        
        /* Creat New Company Object */
        $Fundable = new Company();
        //$Fundable->PrintAllVars();
        
        /* Start a session */
        session_start();
        
        /* Connecte to Server */
        $con = mysqli_connect(HOSTNAME, USERNAME, PASSWARD, DATABASE);
        if (!$con) {
           die('Could not connect: ' . mysql_error());
        }
        echo "Connected successfully ! \n";
        
        /* for getting the Buisness name and saving it in tb_comapny */
        //$i= $_SESSION['count'];
        $count = 0; 
        $i= 0;
        foreach($html_base->find('.company .name') as $e){
            $e= $e->innertext;
            $Fundable->business_name[$i] = $e;
            //echo $Fundable->business_name[$i];
            //print_r($i.'<br>');
            //print_r($Fundable->business_name[$i].'<br>');
            $count++;
            $i++;
        }        
        
        /* for getting the logo and saving it in tb_comapny */
        //$i = $_SESSION['count']; 
        $i= 0;
        foreach($html_base->find('.photo') as $e){
            $e= $e->style;
            $Fundable->logo[$i] = $e;
            // print_r($logo);
            //print_r($i.'<br>');
            $i++;
        }

        /* for getting the short description and saving it in tb_comapny */
        //$i= $_SESSION['count'];
        $i= 0;
        foreach($html_base->find('.elevator-pitch') as $e){
            $e= $e->innertext;
            $Fundable->short_describe[$i] = $e;
            // echo $short_describe;
            $i++;
        }
        
        /* for getting the goal amount and saving it in tb_comapny */
        //$i= $_SESSION['count'];
        $i= 0;
        foreach($html_base->find('.metric .text') as $e){
            $e= $e->innertext;
            // $e= trim(str_replace("$","",$e));
            $Fundable->goal_amount[$i] = '$'.filter_var($e, 
                                             FILTER_SANITIZE_NUMBER_INT).
                                          'K';
            // echo $Fundable->goal_mount[$i];
            $i++;
        }

        /*
         * For updating the last updated date and saving into database location.
         */
        //$i= $_SESSION['count'];
//        $i= 0;
//        foreach($html_base->find('.company') as $e) {
//            
//            // Not avaibale right now 
//            $Fundable->last_update_date[$i] = trim($e);
//            // echo $last_update_date;
//            $i++;
//        }

        /*
         * For updating the current date and saving into database.
         */
        //$i= $_SESSION['count'];
        $i= 0;
        foreach($html_base->find('.company') as $e) {
            $e = date("Y-m-d");
            // echo $e;
            $Fundable->date[$i] =trim($e);
            // echo $date;
            $i++;
        }

        /* for getting the types of capital and saving it in tb_comapny */
        //$i= $_SESSION['count'];
//        $i= 0;
//        foreach($html_base->find('.done_startup .right .event_detail') as $e){
//            $e = $e->innertext;
//            $Fundable->capital[$i] = $Fundable->capital[$i].trim($e);
//            // echo print_t($e[$i]);
//            $i++;
//        }

        /* for getting the platform and saving it in tb_comapny */
        //$i= $_SESSION['count'];
//        $i= 0;
//        foreach($html_base->find('.navbar-inner img') as $e){
//            $e = $e->alt;
//            $Fundable->platform_name[$i] = $e;
//            // echo $platform_name;
//            $i++;
//        }
        
        /* Print out all var */ 
        print "<pre>";
        $Fundable->PrintAllVars();
        print_r(array_values($Fundable->platform_name));
        print "<pre>";
               
        
        /***-----Determine New or Existing Data to Do Insert or Update------***/
       
        /* Get Company Names from Database */
        $DBCompanyNamesArray = Get_DBCompany_Name($con);
        
        /* Get The Last Company ID from DB for Insert */
        $result_Last_BIDs = mysqli_query($con,'SELECT company_id FROM tb_company ORDER BY company_id DESC LIMIT 1');
        $result_Last_BID = mysqli_fetch_assoc($result_Last_BIDs);
        $Last_BID = $result_Last_BID['company_id'];
        
        /* Initial Variables */
        $sql_Insert_Company = "INSERT INTO tb_company (company_id, company_name, company_logo, company_city, company_state, company_bio) VALUES ";
        $sql_Insert_Funding = "INSERT INTO tb_funding_info (company_id, funding_platform, funding_goal_amount, funding_last_update_date) VALUES ";
                
        foreach ($Fundable->business_name as $WebCompany){
            /* Get Array Index */
            $key = array_search($WebCompany, $Fundable->business_name);
            
            /* Determine New or Existing Data*/
            if (isNew($WebCompany,$DBCompanyNamesArray)){
                /* Existing Data */
                echo $key;
                echo 'true';
                echo $WebCompany.'<br>'; 
                
                /* Get Update Data */
                $BAmount = $Fundable->goal_amount[$key];
                
                /* Get Company ID for Update */
                $NWebCompany = str_replace("'","\'",$WebCompany);
                $sql_update_ID = "SELECT company_id FROM tb_company where company_name="."'" .$NWebCompany."'" ;
                $result_Update_BIDs = mysqli_query($con,$sql_update_ID);
                $result_Update_BID = mysqli_fetch_assoc($result_Update_BIDs);
                $Update_BID = $result_Update_BID['company_id'];
                
                /* Update Funding Info */
                $sql_Update_Funding = "UPDATE tb_funding_info SET funding_goal_amount = "."'".$BAmount."'".", funding_last_update_date = now() WHERE company_id = "."'".$Update_BID."'";
                echo $sql_Update_Funding.'<br>';
                mysqli_query($con, $sql_Update_Funding);
                                
            }else{
                /* New Data */
                echo $key;
                echo 'false';
                echo $WebCompany.'<br>';                
                              
                /* Get Insert Data */
                $NWebCompany = str_replace("'","\'",$WebCompany);                   //Replace Proportional Form to Fullwidth Form
                $BLogo = $Fundable->logo[$key];
                $BLogo1 = str_replace("background-image:url(","",$BLogo);           //Remove parts of string in the front
                $BLogo2 = str_replace(");","",$BLogo1);                             //Remove parts of string in the end
                $Bdescribe = $Fundable->short_describe[$key];
                $NBdescribe = str_replace("'","\'",$Bdescribe);
                //$BCity= $Fundable->city[$key];
                //$BState= $Fundable->state[$key];
                $BPlatform = "Fundable";
                $BAmount = $Fundable->goal_amount[$key];
                $Last_BID = $Last_BID+1;
                 
                /* Consist Insert Query */
                $sql_Insert_Company = Consist_Insert_Company_Info($sql_Insert_Company,$Last_BID,$NWebCompany,$BLogo2,$BCity,$BState,$NBdescribe);
                $sql_Insert_Funding = Consist_Insert_Funding_Info($sql_Insert_Funding,$Last_BID,$BPlatform,$BAmount);
                
            }         
        }
        
        /* Insert New Company Info */
        $New_sql_Insert_Company = rtrim($sql_Insert_Company, ",");
        $New_sql_Insert_Funding = rtrim($sql_Insert_Funding, ",");
//        echo $New_sql_Insert_Company.'<br>';
//        echo $New_sql_Insert_Funding.'<br>';
        mysqli_query($con, $New_sql_Insert_Company);
        mysqli_query($con, $New_sql_Insert_Funding);
        
        
        /* End a session */
        mysqli_close($con);
      
        ?>
    </body>
</html>