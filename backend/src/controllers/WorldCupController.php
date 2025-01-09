<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use Exception;

class WorldCupController extends BaseController {
    public function getGroups() {
        $query = "SELECT * FROM teams WHERE type='national'";
        
        try {
            $groups = $this->fetchAll($query);
            $formatted = [];
            foreach ($groups as $team) {
                $group = $team['group'];
                unset($team['group']);
                $formatted[$group][] = $team;
            }
            ApiResponse::success(['groups' => $formatted]);
        } catch (Exception $e) {
            ApiResponse::error('Failed to fetch World Cup groups', 500);
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