<?php
/*
 *  display.git_rss.php
 *  gitphp: A PHP git repository browser
 *  Component: Display - RSS feed
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 */

 require_once('defs.constants.php');
 require_once('util.date_str.php');
 require_once('util.script_url.php');
 require_once('gitutil.git_read_head.php');
 require_once('gitutil.git_read_revlist.php');
 require_once('gitutil.git_read_commit.php');
 require_once('gitutil.git_diff_tree.php');

function git_rss($projectroot,$project)
{
	global $tpl;
	$head = git_read_head($projectroot . $project);
	$revlist = git_read_revlist($projectroot . $project, $head, GITPHP_RSS_ITEMS);
	header("Content-type: text/xml; charset=UTF-8");
	$tpl->clear_all_assign();
	$tpl->assign("self",script_url());
	$tpl->assign("project",$project);

	$commitlines = array();
	for ($i = 0; $i <= count($revlist); $i++) {
		$commit = $revlist[$i];
		$co = git_read_commit($projectroot . $project, $commit);
		if (($i >= 20) && ((time() - $co['committer_epoch']) > 48*60*60))
			break;
		$cd = date_str($co['committer_epoch']);
		$difftree = array();
		$diffout = git_diff_tree($projectroot . $project, $co['parent'] . " " . $co['id']);
		$tok = strtok($diffout,"\n");
		while ($tok !== false) {
			if (ereg("^:([0-7]{6}) ([0-7]{6}) ([0-9a-fA-F]{40}) ([0-9a-fA-F]{40}) (.)([0-9]{0,3})\t(.*)$",$tok,$regs))
				$difftree[] = $regs[7];
			$tok = strtok("\n");
		}
		$commitline = array();
		$commitline["cdmday"] = $cd['mday'];
		$commitline["cdmonth"] = $cd['month'];
		$commitline["cdhour"] = $cd['hour'];
		$commitline["cdminute"] = $cd['minute'];
		$commitline["title"] = $co['title'];
		$commitline["author"] = $co['author'];
		$commitline["cdrfc2822"] = $cd['rfc2822'];
		$commitline["commit"] = $commit;
		$commitline["comment"] = $co['comment'];
		$commitline["difftree"] = $difftree;
		$commitlines[] = $commitline;
	}
	$tpl->assign("commitlines",$commitlines);
	$tpl->display("rss.tpl");
}

?>
