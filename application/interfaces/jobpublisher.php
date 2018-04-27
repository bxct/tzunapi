<?php
namespace Interfaces;

interface JobPublisher {
    
    function publishJob($esn, $vendorId, $carrierId, $subQueryId);
    
    function processJobs();
    
}

