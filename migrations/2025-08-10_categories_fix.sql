-- Migration: expand ideas.category ENUM to match categories.json
-- Date: 2025-08-10
-- Purpose: Change category column from ENUM to VARCHAR to allow flexible categories.
-- Note: Category is now VARCHAR; values should be validated at the application level using categories.json.
-- How to apply: Import this file in phpMyAdmin (or run via CLI). It is idempotent with respect to
-- existing values; no data loss is expected.

-- Optional safety: ensure any NULL/blank becomes 'Other' before altering
UPDATE ideas SET category = 'Other'
WHERE category IS NULL OR category = '';

-- Change category column from ENUM to VARCHAR(255)
ALTER TABLE ideas
  MODIFY category VARCHAR(255) NOT NULL DEFAULT 'Other';
