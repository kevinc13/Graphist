<?php 

class Home extends BaseModel {

	public function __construct() 
	{
		parent::__construct();
	}

	public function index() 
	{
		if (empty($_SESSION['user_id']) || empty($_COOKIE["access_token"])) Response::to_route("/");
		
		// Get graph client
		$graph = Graph::instance()->getClient();
		
		// Set view name
		$this->set("viewName", "Home");
		
		// Get user label
		$userLabel = $graph->makeLabel("User");
		$user_id = (int)$_SESSION['user_id'];
		
		// Get user node
		$userNodes = $userLabel->getNodes("user_id", $user_id);
		$userNode = $userNodes[0];
		
		// Get new profile image
        if ($_SESSION["profile_image"] != $userNode->getProperty("profile_image"))          
        {
            $_SESSION["profile_image"] = $userNode->getProperty("profile_image");
        }	

        // Get number of snips user has posted
        $statsCypher = "MATCH (user:User {user_id: {$user_id}})
               OPTIONAL MATCH (user)-[:POSTED]->(snip:Snip), 
                              (user)-[:CREATED]->(stack:Stack)<-[:FOLLOWS]-(follower:User) 
                       RETURN count(distinct snip) as snips,
                              count(distinct stack) as stacks,
                              count(distinct follower) as followers";
                              
        $statsQuery = new Everyman\Neo4j\Cypher\Query($graph, $statsCypher);
        $statsResult = $statsQuery->getResultSet();
        
        foreach ($statsResult as $row) {
            $_SESSION['numSnips'] = $numSnips = (int)$row["snips"];
            $_SESSION["numStacks"] = $numStacks = (int)$row["stacks"];
            $_SESSION["numFollowers"] = $numFollowers = (int)$row["followers"];
        }
        
        // Get number of stacks the user follows
        $_SESSION["numFollowing"] = $numFollowing = count($userNode->getRelationships(array("FOLLOWS"), Everyman\Neo4j\Relationship::DirectionOut));                 

        // Retrieve notifications
	    $numNotifications = DB::table("snp_notifications")->where("receiver", "=", $_SESSION['user_id'], "")
	    								->get(array("COUNT(notification_id) as numNotifications"));
		$_SESSION['numNotifications'] = $numNotifications[0]->numNotifications;

		$bundle = array(
			"numSnips" => $_SESSION['numSnips'],
			"numStacks" => $_SESSION['numStacks'],
			"numFollowing" => $_SESSION['numFollowing'],
			"numFollowers" => $_SESSION["numFollowers"],
			"numNotifications" => $_SESSION['numNotifications']
		);

		$this->set($bundle);

		return $this->_vars;
	}
}