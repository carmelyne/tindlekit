-- Migration: Add 'token' to pledge_type enum or constraint
-- For MySQL ENUM type:
ALTER TABLE idea_interest
  MODIFY COLUMN pledge_type ENUM('time','mentorship','token') NOT NULL;

-- For PostgreSQL enum type:
-- DO $$
-- BEGIN
--   IF NOT EXISTS (
--     SELECT 1 FROM pg_type t
--     JOIN pg_enum e ON t.oid = e.enumtypid
--     WHERE t.typname = 'pledge_type' AND e.enumlabel = 'token'
--   ) THEN
--     ALTER TYPE pledge_type ADD VALUE 'token';
--   END IF;
-- END$$;

-- For PostgreSQL with TEXT + CHECK constraint:
-- ALTER TABLE idea_interest DROP CONSTRAINT IF EXISTS chk_pledge_type;
-- ALTER TABLE idea_interest
--   ADD CONSTRAINT chk_pledge_type
--   CHECK (pledge_type IN ('time','mentorship','token'));
