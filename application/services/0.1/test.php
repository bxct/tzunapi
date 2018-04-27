<?php

/**
 * @ignore
 */

namespace Services_0_1;

/**
 * @ignore
 */
class Test extends \Services_0_1\BaseService {
    
    public function add_vendor($request_method = false) {
        return \Services_0_1\Vendors::add('CLI');
    }
    
    public function enable_vendor($vendor=false, $request_method = false) {
        return \Services_0_1\Vendors::enable($vendor, 'CLI');
    }
    
    public function disable_vendor($vendor=false, $request_method = false) {
        return \Services_0_1\Vendors::disable($vendor, 'CLI');
    }
    
    public function resume_vendor($vendor=false, $request_method = false) {
        return \Services_0_1\Vendors::resume($vendor, 'CLI');
    }

}
