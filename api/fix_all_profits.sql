-- Fix all profits in bet_history table
-- This script recalculates profit based on betting amount and percentage

UPDATE bet_history 
SET profit = 
    CASE 
        -- If red win, calculate from betting_red
        WHEN status = 'red win' THEN FLOOR(betting_red * (percentage / 100))
        -- If blue win, calculate from betting_blue  
        WHEN status = 'blue win' THEN FLOOR(betting_blue * (percentage / 100))
        -- Otherwise, no profit
        ELSE 0
    END
WHERE status IN ('red win', 'blue win');

-- Show the results
SELECT 
    id,
    betting_red,
    betting_blue,
    percentage,
    status,
    profit,
    FLOOR(CASE 
        WHEN status = 'red win' THEN betting_red * (percentage / 100)
        WHEN status = 'blue win' THEN betting_blue * (percentage / 100)
        ELSE 0
    END) as calculated_profit
FROM bet_history 
WHERE status IN ('red win', 'blue win')
ORDER BY id DESC
LIMIT 50;

