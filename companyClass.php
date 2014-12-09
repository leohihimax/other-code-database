<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
        class Company {

            /* Variables */ 
            public $business_name = array(); 
            public $logo = array();
            public $short_describe = array(); 
            public $goal_amount = array();
            public $last_update_date = array();
            public $capital = array();    // ?？ 
            public $platform_name = array();
            public $date = array(); 
            public $city = array();
            public $state = array();
            public $location = array();


            /* Constructor */ 
            public function Company(){
                //echo '<br>'."Initializing!".'<br>';
                $this->__construct();
            }
            
            /* Constructor */ 
            public function __construct(){
                // echo '<br>'."Constructing:".'<br>';
                // echo '<br>'."Constructing Finished".'<br>';
            }
            
            /* Desctructor */
            public function __destruct(){
            }
            
            /* Functions are used to get an single field; */
            public function get_business_name(){
                // echo $this->business_name;
                return ($this->business_name);
            }
            
            public function get_logo(){
                return ($this->logo);
            }
            
            public function get_short_describe(){
                return ($this->short_describe);
            }
            
            public function get_goal_amount(){
                return ($this->goal_amount);
            }
            
            public function get_last_update_date(){
                return $this->last_update_date;
            }
            
            public function get_current_date(){
                return $this->date;
            }

            public function get_capital(){
                return $this->capital;
            }
            
            public function get_platform_name(){
                return $this->platform_name;
            }

            /* Print out all vars */ 
            public function PrintAllVars() {
                  var_dump(get_object_vars($this));
            }
        }
?>
