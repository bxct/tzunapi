<?php 
session_set_cookie_params(3600*24*7);
session_start();

require_once '../init/common.php';

use \JpGraph\JpGraph;

if(!array_key_exists('auth', $_SESSION)) {
    if($_POST && array_key_exists('codeword', $_POST)) {
        if(in_array($_POST['codeword'], array('justin', 'maria', 'anton'))) {
            $_SESSION['auth'] = $_POST['codeword'];
            header('Location: /client');
            exit;
        }
    }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Tsunami</title>
        <link rel="stylesheet" type="text/css" href="css/view.css" media="all">
        <script type="text/javascript" src="js/view.js"></script>
        <script type="text/javascript" src="js/calendar.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    </head>
    <body id="main_body" >

        <img id="top" src="images/top.png" alt=""/>
        <div id="form_container">

            <h1><a>Please, log in</a></h1>
            <form id="form_986884" class="appnitro"  method="post" action="/client/index.php" enctype="multipart/form-data">
                <div class="form_description">
                    <h2>Tsunami</h2>
                    <p>Login to use test client</p>
                </div>
                <ul >
                    <li id="li_1" >
                        <label class="description" for="public_key">Code Word</label>
                        <div>
                            <input id="element_1" name="codeword" class="element text medium" type="password" maxlength="255" value=""/> 
                        </div> 
                    </li>
                </ul>
            </form>
        </div>
    </body>
</html>
<?php
exit;
}

JpGraph::load();
JpGraph::module('line');

$objects = array(
    'Jobs' => 'jobs',
    'Sub Queries' => 'sub_queries',
    'Queries' => 'queries'
);

$dateRange = array(
    'start' => date('Y-m-d 00:00:00'),
    'end' => date('Y-m-d 23:59:59')
);

if($_POST) {

    $datay1 = array(20, 15, 23, 15);
    $datay2 = array(12, 9, 42, 8);
    $datay3 = array(5, 17, 32, 24);

// Setup the graph
    $graph = new Graph(2000, 400);
    $graph->SetScale("textlin");

    $theme_class = new \UniversalTheme;

    $graph->SetTheme($theme_class);
//$graph->img->SetAntiAliasing(false);
    $graph->title->Set('Filled Y-grid');
    $graph->SetBox(false);

//$graph->img->SetAntiAliasing();

    $graph->yaxis->HideZeroLabel();
    $graph->yaxis->HideLine(false);
    $graph->yaxis->HideTicks(false, false);

    $graph->xgrid->Show();
    $graph->xgrid->SetLineStyle("solid");
    $graph->xaxis->SetTickLabels(array('A', 'B', 'C', 'D'));
    $graph->xgrid->SetColor('#E3E3E3');

// Create the first line
    $p1 = new LinePlot($datay1);
    $graph->Add($p1);
    $p1->SetColor("#6495ED");
    $p1->SetLegend('Line 1');

// Create the second line
    $p2 = new LinePlot($datay2);
    $graph->Add($p2);
    $p2->SetColor("#B22222");
    $p2->SetLegend('Line 2');

// Create the third line
    $p3 = new LinePlot($datay3);
    $graph->Add($p3);
    $p3->SetColor("#FF1493");
    $p3->SetLegend('Line 3');

    $graph->legend->SetFrameWeight(1);

// Output line
    $graph->Stroke();
}

