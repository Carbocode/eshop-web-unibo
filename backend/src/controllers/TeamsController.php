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
    public function getNationalTeams(){
        $query = "SELECT * FROM teams WHERE type='national'";
        try{
            $teams = $this->fetchAll($query);
            ApiResponse::success(['teams' => $teams]);
        }catch(Exception $e) {
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
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $handlers = [
            '/teams' => [
                'GET' => [$this, 'getTeams'],
            ],
            '/teams/national' => [
                'GET' => [$this, 'getNationalTeams']
            ]
        ];

        $this->handleRequest($_SERVER['REQUEST_METHOD'], $handlers[$uri]);
    }
}