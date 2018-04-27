UPDATE `vendors` SET `stack_size` = FLOOR (1+RAND()*700);
UPDATE `vendors` SET `completed` = FLOOR (RAND()*`stack_size`);
UPDATE `vendors` SET `failed` = FLOOR (RAND()*(`stack_size`-`completed`));


SELECT q.* FROM (SELECT 
    v.`id`, 
    cv.`carrier_id`,
    (v.`stack_size`-v.`completed`-v.`failed`) AS active_jobs, 
    v.`failed` AS failed_jobs,
    v.`hostname` 
FROM 
    `carriers_vendors` cv
    LEFT JOIN `vendors` v 
        ON v.`id` = cv.`vendor_id`
WHERE 1 
ORDER BY 
    v.`failed` ASC,
    (v.`stack_size`-v.`completed`-v.`failed`) ASC, 
    cv.`carrier_id` ASC) q
GROUP BY q.`carrier_id`;


SELECT q.* FROM (SELECT 
    v.`id`, 
    cv.`carrier_id`,
    (v.`stack_size`-v.`completed`-v.`failed`) AS active_jobs, 
    v.`failed` AS failed_jobs,
    v.`hostname` 
FROM 
    `carriers_vendors` cv
    LEFT JOIN `vendors` v 
        ON v.`id` = cv.`vendor_id`
WHERE 1 
ORDER BY 
    v.`failed` ASC,
    (v.`stack_size`-v.`completed`-v.`failed`) ASC) q
GROUP BY q.`carrier_id`;
