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
