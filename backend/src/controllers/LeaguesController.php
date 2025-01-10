<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use Exception;

class LeaguesController extends BaseController {
    public function getLeagues() {
        $query = "SELECT * FROM leagues";
        
        try {
            $leagues = $this->fetchAll($query);
            ApiResponse::success(['leagues' => $leagues]);
        } catch (Exception $e) {
            ApiResponse::error('Failed to fetch leagues', 500);
        }
    }

    public function getTeamsByLeague() {
        $leagueId = isset($_GET['league_id']) ? $_GET['league_id'] : null;
        
        if (!$leagueId) {
            ApiResponse::error('League ID is required', 400);
            return;
        }

        $query = "SELECT * FROM teams WHERE league_id = ?";
        
        try {
            $teams = $this->fetchAll($query, [$leagueId]);
            ApiResponse::success(['teams' => $teams]);
        } catch (Exception $e) {
            ApiResponse::error('Failed to fetch teams', 500);
        }
    }

    public function processRequest() {
        $handlers = [
            'GET' => [$this, isset($_GET['league_id']) ? 'getTeamsByLeague' : 'getLeagues']
        ];
        $this->handleRequest($_SERVER['REQUEST_METHOD'], $handlers);
    }
}

?>