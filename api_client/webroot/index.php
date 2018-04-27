<?php 

error_reporting(E_ALL);
ini_set('display_errors', 'on');

require_once '../init/common.php';

$callMade = false;

if(!empty($_POST)) {
    $apiClient = new \ApiClient($_POST['credentials']['public_key'], $_POST['credentials']['private_key'], \Config::read('api_endpoint'));
    $response = $apiClient->send($_POST['data'], $_POST['method']);
//    echo $response; exit;
    $callMade = true;
}

$availableOperations = \Config::read('available_operations');

$stableVersion = \Config::read('stable_version');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Tsunami API test client</title>
        <link rel="stylesheet" type="text/css" href="css/view.css" media="all">
        <script type="text/javascript" src="js/view.js"></script>
        <script type="text/javascript" src="js/calendar.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    </head>
    <body id="main_body" >

        <img id="top" src="images/top.png" alt="">
        <div id="form_container">

            <h1><a>Untitled Form</a></h1>
            <form id="form_986884" class="appnitro"  method="post" action="/index.php" enctype="multipart/form-data">
                <div class="form_description">
                    <h2>Tsunami API Test Form</h2>
                    <p>This is a test form to send API requests with your browser</p>
                </div>
                <?php if($callMade): ?>
                <div class="form_description">
                    <pre>
                    <?php var_dump($response);?>
                    </pre>
                </div>
                <?php endif; ?>
                <ul >
                    <li id="li_1" >
                        <label class="description" for="public_key">Public Key </label>
                        <div>
                            <input id="element_1" name="credentials[public_key]" class="element text medium" type="text" maxlength="255" value="<?php echo \Config::read('public_key'); ?>"/> 
                        </div> 
                    </li>		
                    <li id="li_2" >
                        <label class="description" for="signature">Private Key </label>
                        <div>
                            <input id="element_2" name="credentials[private_key]" class="element text medium" type="text" maxlength="255" value="<?php echo \Config::read('private_key'); ?>"/> 
                        </div> 
                    </li>
                    <li id="li_3" >
                        <label class="description" for="format">Request Method </label>
                        <div>
                            <select id="format" name="method" class="element text medium">
                                <option value="get">GET</option>
                                <option value="post">POST</option>
                                <option value="put">PUT</option>
                                <option value="patch">PATCH</option>
                                <option value="delete">DELETE</option>
                            </select>
                        </div> 
                    </li>
                    <li id="li_3" >
                        <label class="description" for="format">Format </label>
                        <div>
                            <select id="format" name="data[format]" class="element text medium">
                                <option value="json">JSON</option>
                                <option value="xml">XML</option>
                                <option value="html">HTML</option>
                                <option value="serialize">Serialized String</option>
                            </select>
                        </div> 
                    </li>
                    <li id="li_4" >
                        <label class="description" for="action">Action  URL</label>
                        <div>
                            <input id="element_3" name="data[action]" class="element text medium" type="text" maxlength="255" value=""/> 
                        </div> 
                    </li>
                    <li id="li_5" >
                        <label class="description" for="service">Service </label>
                        <div>
                            <?php if($availableOperations): ?>
                            <select id="service" name="data[service]" class="element text medium">
                                <option value="">NONE</option>
                                <?php foreach($availableOperations as $service => $operations): ?>
                                <?php $serviceV  = $service.'_'.str_replace('.', '_', $stableVersion);?>
                                <option value="<?php echo $serviceV; ?>"><?php echo $serviceV; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input id="element_3" name="service" class="element text medium" type="text" maxlength="255" value="bundle_apps_0_1"/> 
                            <?php endif; ?>
                        </div> 
                    </li>
                    <li id="li_6" >
                        <label class="description" for="operation">Operation </label>
                        <div>
                            <?php if($availableOperations): ?>
                            <select id="operation" name="data[operation]" class="element text medium">
                                <option value="">NONE</option>
                                <?php foreach($availableOperations as $service => $operations): ?>
                                <?php $serviceV  = $service.'_'.str_replace('.', '_', $stableVersion);?>
                                <?php foreach($operations as $operation => $arguments): ?>
                                <option value="<?php echo $operation; ?>" service="<?php echo $serviceV; ?>"><?php echo $operation; ?></option>
                                <?php endforeach; ?>
                                <?php 
                                break;
                                endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input id="element_3" name="operation" class="element text medium" type="text" maxlength="255" value="index"/> 
                            <?php endif; ?>
                        </div>
                        <div style="display: none;" id="hidden_options">
                            <?php foreach($availableOperations as $service => $operations): ?>
                            <?php $serviceV  = $service.'_'.str_replace('.', '_', $stableVersion);?>
                            <?php foreach($operations as $operation => $arguments): ?>
                            <option value="<?php echo $operation; ?>" service="<?php echo $serviceV; ?>"><?php echo $operation; ?></option>
                            <arguments operation="<?php echo $serviceV; ?>_<?php echo $operation ?>"><?php echo json_encode($arguments); ?></arguments>
                            <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </li>
<!--                    <li id="li_9" >
                        <label class="description" for="file">File </label>
                        <div>
                            <input id="element_9" name="file" class="element text medium" type="file"/> 
                        </div> 
                    </li>-->
                    <li class="buttons">
                        <input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
                    </li>
                </ul>
            </form>	
            <div id="footer"></div>
        </div>
        <img id="bottom" src="images/bottom.png" alt=""/>
            <script type="text/javascript">
                function buildInputs(id, defaultVal){
                    jQuery('.param_input').remove();
                    document.inputs = [];
                    args = jQuery.parseJSON(jQuery('#hidden_options arguments[operation=' + jQuery('#service').val() + '_' + jQuery('#operation').val() + ']').html());
                    if(args) {
                        for(id in args){
                            if(id) {
                                document.inputs.unshift('<li id="li_' + id + '" class="param_input"><label class="description" for="' + id + '">' + id + (args[id]?'(' + args[id] + ')':'') + '</label><div><input id="' + id + '" name="data[' + id + ']" class="element text medium" type="text" maxlength="255" value=""/></div></li>');
                            }
                        }
                    }
                    if(document.inputs.length>0) {
                        jQuery(document.inputs.reverse().join("\n")).insertAfter('#li_6');
                    }
                }
                jQuery(document).ready(function(){
                    jQuery('#service').change(function(){
                        jQuery('#operation').html(jQuery.map(jQuery('#hidden_options option[service=' + jQuery(this).val() + ']'), function(el){
                            return el.outerHTML;
                        }));
                        buildInputs();
                    });
                    jQuery('#operation').change(function(){
                        buildInputs();
                    });
                    buildInputs();
                });
            </script>
    </body>
</html>