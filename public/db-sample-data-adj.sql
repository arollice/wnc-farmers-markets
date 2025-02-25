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

--Adding Market times columns
ALTER TABLE market
ADD COLUMN market_open TIME NOT NULL DEFAULT '08:00:00',
ADD COLUMN market_close TIME NOT NULL DEFAULT '14:00:00';

UPDATE market SET market_open = '09:30:00', market_close = '15:30:00' WHERE market_id = 2;
UPDATE market SET market_open = '10:00:00', market_close = '16:00:00' WHERE market_id = 4;
UPDATE market SET market_open = '11:00:00', market_close = '15:00:00' WHERE market_id = 6;
UPDATE market SET market_open = '12:00:00', market_close = '15:45:00' WHERE market_id = 8;


---- New vendors --

INSERT INTO vendor (vendor_id, vendor_name, vendor_website) VALUES
(9, 'Green Thumb Gardens', 'https://greenthumbgardens.com'),
(10, 'Highland Cattle Ranch', 'https://highlandcattleranch.com'),
(11, 'Riverwood Bakery', 'https://riverwoodbakery.com'),
(12, 'Appalachian Mushrooms', 'https://appalachianmushrooms.com'),
(13, 'Sunny Fields Flowers', 'https://sunnyfieldsflowers.com');
INSERT INTO vendor (vendor_id, vendor_name, vendor_website) VALUES
(14, 'Oak Ridge Maple Syrup', 'https://oakridgemaple.com'),
(15, 'Smoky Mountain Goat Cheese', 'https://smokymountaingoatcheese.com'),
(16, 'Rolling Hills Coffee Roasters', 'https://rollinghillscoffee.com');



---New vendor market assignments--

INSERT INTO vendor_market (market_id, vendor_id) VALUES
(1, 9), (1, 10),
(2, 6), (2, 11),
(3, 4), (3, 9),
(4, 3), (4, 12),
(5, 2), (5, 11),
(6, 1), (6, 13),
(7, 2), (7, 10), (7, 13),
(8, 9), (8, 12);

INSERT INTO vendor_market (market_id, vendor_id) VALUES
(1, 11), (1, 14), -- Asheville City Market (now has 5)
(2, 5), (2, 15), -- Black Mountain Tailgate Market (now has 5)
(3, 1), (3, 10), -- Candler Farmers Market (now has 5)
(4, 9), (4, 16), -- Hendersonville Farmers Market (now has 5)
(5, 7), (5, 14), -- Waynesville Farmers Market (now has 5)
(6, 4), (6, 15), -- Brevard Farmers Market (now has 5)
(7, 6); -- Marshall Farmers Market (now has 5)

---Add column to vendor table
ALTER TABLE vendor
ADD COLUMN vendor_description VARCHAR(255) NULL;

---add Desc to vendor table 

UPDATE vendor SET vendor_description = CASE vendor_id
    WHEN 1 THEN 'Providing fresh produce and seasonal vegetables grown sustainably.'
    WHEN 2 THEN 'Local organic farm offering fruits, vegetables, and herbs.'
    WHEN 3 THEN 'Family-run farm specializing in heirloom tomatoes and peppers.'
    WHEN 4 THEN 'High-quality, locally-sourced meats and poultry.'
    WHEN 5 THEN 'Pure honey harvested from local Hendersonville apiaries.'
    WHEN 6 THEN 'Fresh wildflowers and seasonal bouquets from Waynesville.'
    WHEN 7 THEN 'Local dairy farm offering artisanal cheeses and fresh milk products.'
    WHEN 9 THEN 'Sustainable farm providing organically grown vegetables and herbs.'
    WHEN 10 THEN 'Ranch offering premium grass-fed beef from the Highlands.'
    WHEN 11 THEN 'Freshly baked artisan breads, pastries, and desserts.'
    WHEN 12 THEN 'Cultivating gourmet mushrooms for culinary enthusiasts.'
    WHEN 13 THEN 'Specializing in vibrant floral arrangements from Sunny Fields.'
    WHEN 14 THEN 'Craft maple syrup produced locally in Oak Ridge.'
    WHEN 15 THEN 'Artisan goat cheeses made in the Smoky Mountains.'
    WHEN 16 THEN 'Local coffee roasters offering freshly roasted specialty coffees.'
    ELSE 'Local vendor offering quality products from Western North Carolina.'
END;
