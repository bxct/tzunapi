/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  anton
 * Created: Sep 22, 2015
 */

SELECT q.* FROM (SELECT 
    v.*, 
    cv.`carrier_id`,
    (v.`stack_size`-v.`completed`-v.`failed`) AS active_jobs
FROM 
    `carriers_vendors` cv
    RIGHT JOIN `vendors` v 
        ON v.`id` = cv.`vendor_id`
WHERE 
    v.disabled IS NULL
    AND v.activated IS NOT NULL
ORDER BY 
    v.`failed` ASC,
    (v.`stack_size`-v.`completed`-v.`failed`) ASC,
    v.`stack_size` ASC) q
GROUP BY q.`carrier_id`;