ALTER TABLE region
ADD COLUMN latitude DECIMAL(10,7) NULL,
ADD COLUMN longitude DECIMAL(10,7) NULL;


UPDATE region SET latitude = 35.598168, longitude = -82.552386 WHERE region_id = 1;
UPDATE region SET latitude =  35.617261, longitude = -82.323740 WHERE region_id = 2;
UPDATE region SET latitude = 35.535889, longitude = -82.693903 WHERE region_id = 3;
UPDATE region SET latitude = 35.319698, longitude = -82.467376 WHERE region_id = 4;
UPDATE region SET latitude = 35.488744, longitude = -82.991979 WHERE region_id = 5;
UPDATE region SET latitude = 35.232918, longitude = -82.732966 WHERE region_id = 6;
UPDATE region SET latitude = 35.798236, longitude = -82.683124 WHERE region_id = 7;
UPDATE region SET latitude = 35.697186, longitude = -82.563324
 WHERE region_id = 8;



--------------------------------------------------------------

--Market table sample data :


INSERT INTO market_schedule (market_id, market_day, season_id, last_day_of_season) VALUES
-- Marshall (market_id = 7)
(7, 'Saturday', 1, '2024-11-30'),
(7, 'Tuesday', 1, '2024-11-30'),

-- Weaverville (market_id = 8)
(8, 'Wednesday', 2, '2024-12-15'),
(8, 'Saturday', 2, '2024-12-15'),

-- Waynesville (market_id = 5)
(5, 'Thursday', 3, '2024-10-20'),
(5, 'Saturday', 3, '2024-10-20'),

-- Brevard (market_id = 6)
(6, 'Friday', 4, '2024-09-25'),
(6, 'Saturday', 4, '2024-09-25');


--Sample data for vendor_market & vendor_currency:

INSERT INTO vendor_market (vendor_id, market_id, attending_date) VALUES
(1, 8, '2024-03-10'), -- Assign Vendor 1 to Weaverville
(3, 8, '2024-03-17'), -- Assign Vendor 3 to Weaverville
(5, 8, '2024-03-24'); -- Assign Vendor 5 to Weaverville

INSERT INTO vendor_currency (vendor_id, currency_id) VALUES
(1, 8), -- Cash for Vendor 1
(5, 8), -- Cash for Vendor 5
(5, 2); -- Credit for Vendor 5


-- Assign the correct seasons to markets
UPDATE market_schedule 
SET season_id = 3 -- Fall
WHERE market_id = 1;

UPDATE market_schedule 
SET season_id = 2 -- Summer
WHERE market_id = 2;

UPDATE market_schedule 
SET season_id = 2 -- Summer
WHERE market_id = 6;

UPDATE market_schedule 
SET season_id = 2 -- Summer
WHERE market_id = 4;

UPDATE market_schedule 
SET season_id = 3 -- Fall
WHERE market_id = 3;

UPDATE market_schedule 
SET last_day_of_season = '2024-08-31'
WHERE market_id = 8;

UPDATE market_schedule 
SET last_day_of_season = '2024-04-30'
WHERE market_id = 7;

