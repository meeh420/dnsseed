#!/usr/bin/php

<?php

require("config.php");
require("bitcoin-node.php");
require("global.php");

if ($argc != 2)
	exit;

$arr = explode(":", $argv[1]);
if (count($arr) > 2 || count($arr) == 0)
	exit;

$port = count($arr)==1 ? 9377 : $arr[1];

try {
	$origNode = new Bitcoin\Node($arr[0], $port, $CONFIG['CONNECT_TIMEOUT']);
	$origNode->getAddr();
	$nodes = $origNode->getAddr();
        if (!empty($nodes)) {
		start_db_transaction();
		var_dump($nodes);
        add_node_to_dns($arr[0], $port, $origNode->getVersion());

		foreach ($nodes as &$node) {
			if ($node["services1"] == 1 && $node["services2"] == 0) {
                if ($node["timestamp"] >= time() - $CONFIG['MIN_LAST_SEEN']) echo "claiming old node...\n";
				add_untested_node($node["ipv4"], $node["port"]);
            } else { echo "Not accepted."; }
		}
		commit_db_transaction();
	}else{
		start_db_transaction();
		remove_node($arr[0], $port);
		commit_db_transaction();
	}
} catch (Exception $e) {
	start_db_transaction();
	remove_node($arr[0], $port);
	commit_db_transaction();
    echo $e->getMessage();
}

exit;

/*
$c = new Node('127.0.0.1');
var_dump($c->getVersionStr());
echo "completed, waiting...\n";

var_dump($c->getAddr());

while(true) {
	$pkt = $c->readPacket();
	if ($pkt) {
		echo $pkt['type'].': '.bin2hex($pkt['payload'])."\n";
	} else {
		exit;
	}
}*/
?>
