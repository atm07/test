<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
 
class User extends CI_Model{ 
    function __construct() { 
        // Set table name 
        $this->table = 'users'; 
    } 
     
    /* 
     * Fetch user data from the database 
     * @param array filter data based on the passed parameters 
     */ 
    function getRows($params = array()){ 
        $this->db->select('*'); 
        $this->db->from($this->table); 
         
        if(array_key_exists("conditions", $params)){ 
            foreach($params['conditions'] as $key => $val){ 
                $this->db->where($key, $val); 
            } 
        } 
         
        if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){ 
            $result = $this->db->count_all_results(); 
        }else{ 
            if(array_key_exists("id", $params) || $params['returnType'] == 'single'){ 
                if(!empty($params['id'])){ 
                    $this->db->where('id', $params['id']); 
                } 
                $query = $this->db->get(); 
                $result = $query->row_array(); 
            }else{ 
                $this->db->order_by('id', 'desc'); 
                if(array_key_exists("start",$params) && array_key_exists("limit",$params)){ 
                    $this->db->limit($params['limit'],$params['start']); 
                }elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){ 
                    $this->db->limit($params['limit']); 
                } 
                 
                $query = $this->db->get(); 
                $result = ($query->num_rows() > 0)?$query->result_array():FALSE; 
            } 
        } 
         
        // Return fetched data 
        return $result; 
    } 
     
    /* 
     * Insert user data into the database 
     * @param $data data to be inserted 
     */ 
    public function insert($data = array()) { 
        if(!empty($data)){ 
            // Add created and modified date if not included 
            if(!array_key_exists("created", $data)){ 
                $data['created'] = date("Y-m-d H:i:s"); 
            } 
            if(!array_key_exists("modified", $data)){ 
                $data['modified'] = date("Y-m-d H:i:s"); 
            } 
             
            // Insert member data 
            $insert = $this->db->insert($this->table, $data); 
             
            // Return the status 
            return $insert?$this->db->insert_id():false; 
        } 
        return false; 
    }

    // address datas 
        public function insert_data($table,$data = array()) { 
            if(!empty($data)){ 
                // Add created and modified date if not included 
                if(!array_key_exists("created", $data)){ 
                    $data['created'] = date("Y-m-d H:i:s"); 
                } 
                if(!array_key_exists("modified", $data)){ 
                    $data['modified'] = date("Y-m-d H:i:s"); 
                } 
                 
                // Insert member data 
                $insert = $this->db->insert($table, $data); 
                 
                // Return the status 
                return $insert?$this->db->insert_id():false; 
            } 
            return false; 
        }

        function getRows_data($table,$params = array()){ 
            // print_r($params);exit;
            $this->db->select('*'); 
            $this->db->from($table); 
             
            if(array_key_exists("conditions", $params)){ 
                foreach($params['conditions'] as $key => $val){ 
                    $this->db->where($key, $val); 
                } 
            } 
             
            if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){ 
                $result = $this->db->count_all_results(); 
            }else{ 
                // if(array_key_exists("id", $params) || $params['returnType'] == 'single'){ 
                if(array_key_exists("id", $params)){ 
                    if(!empty($params['id'])){ 
                        $this->db->where('id', $params['id']); 
                    } 
                    $query = $this->db->get(); 
                    $result = $query->row_array(); 
                }else{ 
                    $this->db->order_by('id', 'desc'); 
                    if(array_key_exists("start",$params) && array_key_exists("limit",$params)){ 
                        $this->db->limit($params['limit'],$params['start']); 
                    }elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){ 
                        $this->db->limit($params['limit']); 
                    } 
                     
                    $query = $this->db->get(); 
                    $result = ($query->num_rows() > 0)?$query->result_array():FALSE; 
                } 
            } 
             
            // Return fetched data 
            return $result; 
        }

        function getaddress_data($table,$params = array()){ 
            $this->db->select('*'); 
            $this->db->from($table); 
                foreach($params as $key => $val){ 
                    $this->db->where($key, $val); 
                } 
            $query = $this->db->get(); 
            if($table == "address" || $table == "settings"){
                $result = $query->row_array(); 
            }else{
                $result = $query->result(); 
            }
            return $result; 
        }
    // address datas 

        function blockupdate($count){ 
            $data = array(
                'blockcount' => $count,
                );
            $this->db->set($data);
            $this->db->where('id', 1);
            $this->db->update('settings');
        }
}