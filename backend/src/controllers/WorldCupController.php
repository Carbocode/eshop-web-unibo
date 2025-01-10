<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use Exception;

class WorldCupController extends BaseController {
    public function getGroups() {
        $query = "SELECT * FROM teams WHERE type='national'";
        try{
            $data = $this->fetchAll($query);
            $teams = array();
            $currentGroup = array();
            foreach($data as $row){
                $currentGroup[] = $row;
                if(count($currentGroup) == 4){
                    $teams[] = $currentGroup;
                    $currentGroup = array();
                }
            }
            if(count($currentGroup)!=0){
                $teams[] = $currentGroup;
            }
            ApiResponse::success(['teams' => $teams]);
        }catch(Exception $e) {
            ApiResponse::error('Failed to fetch teams', 500);
        }  
    }

    public function processRequest() {
        $handlers = [
            'GET' => [$this, 'getGroups']
        ];
        $this->handleRequest($_SERVER['REQUEST_METHOD'], $handlers);
    }
}

?>