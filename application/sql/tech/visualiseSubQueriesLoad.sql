-- talentUA15

select x.*
from (SELECT * FROM (
    select 0
    union all select 1 
    union all select 2 
    union all select 3 
    union all select 4 
    union all select 5 
    union all select 6 
    union all select 7 
    union all select 8 
    union all select 9
) as a
cross join (
    select 0
    select 1 union all 
    select 2 union all 
    select 3 union all 
    select 4 union all 
    select 5 union all 
    select 6 union all 
    select 7 union all 
    select 8 union all 
    select 9
) as b
cross join (
    select 0
    select 1 union all 
    select 2 union all 
    select 3 union all 
    select 4 union all 
    select 5 union all 
    select 6 union all 
    select 7 union all 
    select 8 union all 
    select 9
) as c) as x;

---------------------

select '2014-01-26' - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a)) MINUTE as Date
from (
    select 0 as a 
    union all select 1 
    union all select 2 
    union all select 3 
    union all select 4 
    union all select 5 
    union all select 6 
    union all select 7 
    union all select 8 
    union all select 9
) as a
cross join (
    select 0 as a union all s
    select 1 union all 
    select 2 union all 
    select 3 union all 
    select 4 union all 
    select 5 union all 
    select 6 union all 
    select 7 union all 
    select 8 union all 
    select 9
) as b
cross join (
    select 0 as a union all 
    select 1 union all 
    select 2 union all 
    select 3 union all 
    select 4 union all 
    select 5 union all 
    select 6 union all 
    select 7 union all 
    select 8 union all 
    select 9
) as c
cross join (
    select 0 as a union all 
    select 1 union all 
    select 2 union all 
    select 3 union all 
    select 4 union all 
    select 5 union all 
    select 6 union all 
    select 7 union all 
    select 8 union all 
    select 9
) as d;

----------------

-- select a.Date 
-- from (
--     select '2014-01-26' - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a)) MINUTE as Date
--     from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
--     cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
--     cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
-- ) a
-- where a.Date between '2014-01-20' and '2014-01-24' 
-- 
-- ----------------
-- 
-- select minutes.* from (select concat(year(a.Date),lpad(month(a.Date),2,'0'),lpad(day(a.Date),2,'0'),lpad(hour(a.Date),2,'0'),lpad(minute(a.Date),2,'0')) minute
-- from (
--     select '2014-01-26' - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a)) MINUTE as Date
--     from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
--     cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
--     cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
--     cross join (select 0 as a union all select 1 union all select 2) as d
-- ) a
-- where a.Date between '2014-01-20' and '2014-01-24') minutes
-- LEFT JOIN (SELECT count(*) queries_number, concat(year(created),lpad(month(created),2,'0'),lpad(day(created),2,'0'),lpad(hour(created),2,'0'),lpad(minute(created),2,'0')) minute FROM sub_queries WHERE date(created)='2015-09-22' GROUP BY year(created),month(created),day(created),hour(created),minute(created)) queries ON minutes.minute=queries.minute
