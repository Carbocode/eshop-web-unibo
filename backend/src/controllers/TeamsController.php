<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use Exception;

/**
 * TeamsController handles operations related to team data retrieval.
 * Provides functionality to fetch teams with optional country filtering.
 */
class TeamsController extends BaseController {
    /**
     * Retrieves a list of teams, optionally filtered by country.
     * 
     * @throws Exception When database query fails
     * @return void
     */
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

    /**
     * Processes incoming HTTP requests and routes them to appropriate handlers.
     * Currently only handles GET requests to retrieve team data.
     * 
     * @return void
     */
    public function processRequest() {
        $handlers = [
            'GET' => [$this, 'getTeams']
        ];

        $this->handleRequest($_SERVER['REQUEST_METHOD'], $handlers);
    }
}