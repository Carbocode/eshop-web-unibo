<?php

class TeamsController extends BaseController {
    public function getTeams() {
        $country = isset($_GET['country']) ? $_GET['country'] : null;
        
        $query = "SELECT * FROM teams WHERE 1=1";
        $params = [];
        
        if ($country) {
            $query .= " AND country = ?";
            $params[] = $country;
        }
        
        try {
            $teams = $this->fetchAll($query, $params);
            ApiResponse::success(['teams' => $teams]);
        } catch (Exception $e) {
            ApiResponse::error('Failed to fetch teams', 500);
        }
    }

    public function processRequest() {
        $handlers = [
            'GET' => [$this, 'getTeams']
        ];

        $this->handleRequest($_SERVER['REQUEST_METHOD'], $handlers);
    }
}